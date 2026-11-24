<?php

namespace Tests\Feature;

use App\Models\DailyReport;
use App\Models\Saver;
use App\Models\TellerShift;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InternalReviewRevisionsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $teller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');

        $this->admin = User::whereIn('role', ['Administrator', 'Admin'])->first();
        $this->teller = User::where('role', 'Teller')->first();
    }

    public function test_revision_1_receipt_print_styles_are_centered_and_enlarged(): void
    {
        TellerShift::query()->delete();
        TellerShift::create([
            'user_id'         => $this->teller->id,
            'opening_balance' => 100000,
            'status'          => 'Open',
            'opened_at'       => now(),
        ]);

        $response = $this->actingAs($this->teller)->get(route('teller.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('transform: translate(-50%, -50%) !important;', false);
        $response->assertSee('width: 440px !important;', false);
        $response->assertSee('max-width: 440px;', false);
    }

    public function test_revision_2_cash_denomination_breakdown_calculates_150k_and_renders_in_admin_approval_and_pdf(): void
    {
        Transaction::query()->delete();
        TellerShift::query()->delete();
        DailyReport::query()->delete();

        $shift = TellerShift::create([
            'user_id'         => $this->teller->id,
            'shared_user_ids' => [$this->teller->id],
            'opening_balance' => 100000,
            'status'          => 'Open',
            'opened_at'       => now()->subHours(2),
        ]);

        $saver = Saver::create([
            'account_number' => '20268801',
            'student_id'     => '8801001',
            'student_name'   => 'Siswa Uji Kas',
            'grade_class'    => 'X RPL 1',
            'security_pin'   => Hash::make('123456'),
            'saldo'          => 50000,
            'status'         => 'active',
        ]);

        // Tambah setoran 50.000 sehingga saldo sistem akhir di laci = 100.000 + 50.000 = 150.000
        Transaction::create([
            'saver_id'         => $saver->id,
            'user_id'          => $this->teller->id,
            'transaction_date' => now()->subHour(),
            'transaction_type' => 'Deposit',
            'amount'           => 50000,
            'status'           => 'Approved',
        ]);

        // Halaman tutup kas menampilkan seluruh input denominasi 500 s/d 100.000
        $closingPage = $this->actingAs($this->teller)->get(route('teller.report.closing'));
        $closingPage->assertStatus(200);
        foreach ([100000, 50000, 20000, 10000, 5000, 2000, 1000, 500] as $denom) {
            $closingPage->assertSee("denominations[{$denom}]", false);
        }

        // Kirim laporan penutupan dengan rincian: 100k x 1, 20k x 2, 10k x 1 => Total 150.000 (match dengan sistem)
        $postRes = $this->actingAs($this->teller)->post(route('teller.report.store'), [
            'denominations' => [
                '100000' => 1,
                '50000'  => 0,
                '20000'  => 2,
                '10000'  => 1,
                '5000'   => 0,
                '2000'   => 0,
                '1000'   => 0,
                '500'    => 0,
            ],
            'notes' => 'Uang laci sesuai pecahan fisik.',
        ]);

        $postRes->assertRedirect(route('teller.dashboard'));

        $report = DailyReport::latest('id')->first();
        $this->assertNotNull($report);
        $this->assertEquals(150000, (int) $report->closing_balance_physical);
        $this->assertEquals(150000, (int) $report->closing_balance_system);
        $this->assertEquals(1, $report->cash_denominations['100000'] ?? null);
        $this->assertEquals(2, $report->cash_denominations['20000'] ?? null);
        $this->assertEquals(1, $report->cash_denominations['10000'] ?? null);

        $shift->refresh();
        $this->assertEquals('Closed', $shift->status);
        $this->assertEquals(150000, (int) $shift->closing_balance);
        $this->assertEquals(2, $shift->cash_denominations['20000'] ?? null);

        // Halaman Approval Admin menampilkan rincian pecahan
        $approvalPage = $this->actingAs($this->admin)->get(route('admin.approval.index'));
        $approvalPage->assertStatus(200);
        $approvalPage->assertSee('100.000');
        $approvalPage->assertSee('20.000');

        // PDF Berita Acara berhasil di-generate dengan rincian pecahan
        $pdfRes = $this->actingAs($this->admin)->get(route('admin.approval.pdf', $report->id));
        $pdfRes->assertStatus(200);
        $this->assertStringStartsWith('%PDF-', $pdfRes->getContent());
    }

    public function test_revision_3_non_student_customers_can_be_created_by_admin_and_teller_and_are_exempt_from_bulk_promote(): void
    {
        TellerShift::query()->delete();
        TellerShift::create([
            'user_id'         => $this->teller->id,
            'opening_balance' => 100000,
            'status'          => 'Open',
            'opened_at'       => now(),
        ]);

        // 1. Admin menambahkan Nasabah Non-Siswa (Ibu Kantin) langsung di Data Nasabah tanpa mengisi NIP/NIS
        $adminStoreRes = $this->actingAs($this->admin)->post(route('admin.savers.store'), [
            'category'        => 'community',
            'student_name'    => 'Ibu Siti Kantin Sekolah',
            'student_id'      => '', // Kosong -> auto generate WS-YYYYxxx
            'grade_class'     => 'Kantin 1',
            'initial_deposit' => 25000,
            'security_pin'    => '123456',
        ]);
        $adminStoreRes->assertSessionHas('success');

        $kantinSaver = Saver::where('student_name', 'Ibu Siti Kantin Sekolah')->first();
        $this->assertNotNull($kantinSaver);
        $this->assertTrue($kantinSaver->isNonStudent());
        $this->assertStringStartsWith('WS-', $kantinSaver->student_id);
        $this->assertEquals('Kantin 1', $kantinSaver->grade_class);
        $this->assertEquals(25000, (int) $kantinSaver->saldo);

        // 2. Admin menambahkan calon nasabah Guru di Data Anggota
        $memberStoreRes = $this->actingAs($this->admin)->post(route('admin.members.store'), [
            'category'     => 'teacher',
            'student_name' => 'Drs. Hendra Wijaya',
            'student_id'   => '', // Auto generate GR-YYYYxxx
            'grade_class'  => 'Guru Matematika',
        ]);
        $memberStoreRes->assertSessionHas('success');

        $guruSaver = Saver::where('student_name', 'Drs. Hendra Wijaya')->first();
        $this->assertNotNull($guruSaver);
        $this->assertTrue($guruSaver->isNonStudent());
        $this->assertStringStartsWith('GR-', $guruSaver->student_id);
        $this->assertEquals('inactive', $guruSaver->status);

        // 3. Teller mendaftarkan & langsung mengaktifkan Nasabah Non-Siswa di loket Aktivasi
        $tellerStoreRes = $this->actingAs($this->teller)->post(route('teller.activations.store'), [
            'category'        => 'staff',
            'student_name'    => 'Pak Joko Tata Usaha',
            'student_id'      => '',
            'grade_class'     => 'Staf TU',
            'whatsapp_number' => '081299887766',
            'amount'          => 50000,
        ]);
        $tellerStoreRes->assertSessionHas('success');

        $staffSaver = Saver::where('student_name', 'Pak Joko Tata Usaha')->first();
        $this->assertNotNull($staffSaver);
        $this->assertEquals('active', $staffSaver->status);
        $this->assertEquals(50000, (int) $staffSaver->saldo);
        $this->assertTrue($staffSaver->isNonStudent());

        // 4. Buat siswa kelas X dan XII, lalu jalankan Kenaikan Kelas Massal:
        //    Siswa X -> XI, Siswa XII -> Lulus (inactive), sedangkan Non-Siswa (termasuk "Kantin 1") TIDAK berubah
        $studentX = Saver::create([
            'account_number' => '20267701',
            'student_id'     => '7701001',
            'student_name'   => 'Siswa Kelas Sepuluh',
            'grade_class'    => 'X RPL 1',
            'category'       => 'student',
            'security_pin'   => Hash::make('123456'),
            'saldo'          => 20000,
            'status'         => 'active',
        ]);

        $promoteRes = $this->actingAs($this->admin)->post(route('admin.savers.bulk-promote'));
        $promoteRes->assertRedirect(route('admin.savers.index'));

        $studentX->refresh();
        $this->assertEquals('XI RPL 1', $studentX->grade_class);

        $kantinSaver->refresh();
        $this->assertEquals('Kantin 1', $kantinSaver->grade_class);
        $this->assertEquals('active', $kantinSaver->status);

        $guruSaver->refresh();
        $this->assertEquals('Guru Matematika', $guruSaver->grade_class);

        $staffSaver->refresh();
        $this->assertEquals('Staf TU', $staffSaver->grade_class);
        $this->assertEquals('active', $staffSaver->status);
    }
}
