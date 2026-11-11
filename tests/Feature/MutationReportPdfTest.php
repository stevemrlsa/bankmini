<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Saver;
use App\Models\TellerShift;
use App\Models\Transaction;
use App\Models\User;
use App\Services\MutationReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MutationReportPdfTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $tellerA;
    protected User $tellerB;
    protected Saver $saverOne;
    protected Saver $saverTwo;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 11, 15, 14, 0, 0, 'Asia/Jakarta'));
        $this->artisan('db:seed');

        Transaction::query()->delete();
        TellerShift::query()->delete();

        $this->admin = User::whereIn('role', ['Administrator', 'Admin'])->first();
        $this->tellerA = User::where('role', 'Teller')->first();

        $this->tellerB = User::create([
            'username'     => 'teller_partner_test',
            'officer_name' => 'Teller Rekan Uji',
            'email'        => 'teller.partner@test.local',
            'password'     => Hash::make('password'),
            'role'         => 'Teller',
            'status'       => 'Aktif',
        ]);

        $this->saverOne = Saver::create([
            'account_number' => '20269901',
            'student_id'     => '9901001',
            'student_name'   => 'Siswa Mutasi Satu',
            'grade_class'    => 'XI RPL 1',
            'security_pin'   => Hash::make('123456'),
            'saldo'          => 150000,
            'status'         => 'active',
        ]);

        $this->saverTwo = Saver::create([
            'account_number' => '20269902',
            'student_id'     => '9901002',
            'student_name'   => 'Siswa Mutasi Dua',
            'grade_class'    => 'X AKL 2',
            'security_pin'   => Hash::make('123456'),
            'saldo'          => 80000,
            'status'         => 'active',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_teller_can_access_mutation_report_without_active_shift_and_filter_presets(): void
    {
        // Pastikan tidak ada shift aktif sama sekali
        TellerShift::query()->delete();

        $now = Carbon::now('Asia/Jakarta');

        // Hari ini oleh Teller A (Setoran)
        Transaction::create([
            'saver_id'         => $this->saverOne->id,
            'user_id'          => $this->tellerA->id,
            'transaction_date' => $now->copy()->setTime(9, 15),
            'transaction_type' => 'Deposit',
            'amount'           => 50000,
            'status'           => 'Approved',
        ]);

        // Kemarin oleh Teller A (Penarikan)
        Transaction::create([
            'saver_id'         => $this->saverOne->id,
            'user_id'          => $this->tellerA->id,
            'transaction_date' => $now->copy()->subDay()->setTime(10, 30),
            'transaction_type' => 'Withdrawal',
            'amount'           => 25000,
            'status'           => 'Approved',
        ]);

        // Awal bulan ini (10 hari lalu) oleh Teller A
        Transaction::create([
            'saver_id'         => $this->saverTwo->id,
            'user_id'          => $this->tellerA->id,
            'transaction_date' => $now->copy()->startOfMonth()->addDays(2)->setTime(11, 0),
            'transaction_type' => 'Deposit',
            'amount'           => 80000,
            'status'           => 'Approved',
        ]);

        // Transaksi internal brankas tidak boleh muncul di laporan mutasi nasabah
        Transaction::create([
            'saver_id'         => null,
            'user_id'          => $this->tellerA->id,
            'transaction_date' => $now->copy()->setTime(15, 0),
            'transaction_type' => 'Vault_Transfer',
            'amount'           => 500000,
            'status'           => 'Approved',
        ]);

        // Transaksi milik Teller B tidak boleh muncul di mode 'mine'
        Transaction::create([
            'saver_id'         => $this->saverTwo->id,
            'user_id'          => $this->tellerB->id,
            'transaction_date' => $now->copy()->setTime(10, 0),
            'transaction_type' => 'Deposit',
            'amount'           => 99000,
            'status'           => 'Approved',
        ]);

        // 1. Default Hari Ini (tanpa shift terbuka tetap HTTP 200)
        $resToday = $this->actingAs($this->tellerA)->get(route('teller.reports.mutations', ['period' => 'today']));
        $resToday->assertStatus(200);
        $resToday->assertSee('Siswa Mutasi Satu');
        $resToday->assertSee('Rp 50.000');
        $resToday->assertDontSee('Siswa Mutasi Dua');
        $resToday->assertDontSee('500.000');

        // 2. Preset Kemarin
        $resYesterday = $this->actingAs($this->tellerA)->get(route('teller.reports.mutations', ['period' => 'yesterday']));
        $resYesterday->assertStatus(200);
        $resYesterday->assertSee('Rp 25.000');
        $resYesterday->assertDontSee('Rp 50.000');

        // 3. Preset Bulan Ini
        $resMonth = $this->actingAs($this->tellerA)->get(route('teller.reports.mutations', ['period' => 'this_month']));
        $resMonth->assertStatus(200);
        $resMonth->assertSee('Siswa Mutasi Satu');
        $resMonth->assertSee('Siswa Mutasi Dua');
        $resMonth->assertDontSee('99.000');

        // 3b. Filter Jenis Transaksi + Rentang Kustom
        $resCustomWithdraw = $this->actingAs($this->tellerA)->get(route('teller.reports.mutations', [
            'period'     => 'custom',
            'start_date' => $now->copy()->subDay()->toDateString(),
            'end_date'   => $now->toDateString(),
            'type'       => 'withdrawal',
        ]));
        $resCustomWithdraw->assertStatus(200);
        $resCustomWithdraw->assertSee('Rp 25.000');
        $resCustomWithdraw->assertDontSee('Rp 50.000');

        // 4. Ekspor PDF Hari Ini (inline stream) & Download mode
        $pdfStream = $this->actingAs($this->tellerA)->get(route('teller.reports.mutations.pdf', ['period' => 'today']));
        $pdfStream->assertStatus(200);
        $this->assertStringContainsString('application/pdf', (string) $pdfStream->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF-', $pdfStream->getContent());

        $pdfDownload = $this->actingAs($this->tellerA)->get(route('teller.reports.mutations.pdf', [
            'period'   => 'this_month',
            'download' => 1,
        ]));
        $pdfDownload->assertStatus(200);
        $this->assertStringContainsString('attachment;', (string) $pdfDownload->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF-', $pdfDownload->getContent());

        // Tercatat ke AuditLog
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->tellerA->id,
            'action'  => 'export_pdf',
            'module'  => 'Laporan Mutasi Teller',
        ]);
    }

    public function test_teller_shared_shift_scope_includes_partner_only_within_shared_shift(): void
    {
        $now = Carbon::now('Asia/Jakarta');

        $tellerC = User::create([
            'username'     => 'teller_luar_shift',
            'officer_name' => 'Teller Luar Shift',
            'email'        => 'teller.luar@test.local',
            'password'     => Hash::make('password'),
            'role'         => 'Teller',
            'status'       => 'Aktif',
        ]);

        // Shift bersama kemarin diikuti Teller A dan Teller B (sudah Closed)
        TellerShift::create([
            'user_id'         => $this->tellerA->id,
            'shared_user_ids' => [$this->tellerB->id],
            'opening_balance' => 100000,
            'closing_balance' => 200000,
            'system_balance'  => 200000,
            'status'          => 'Closed',
            'opened_at'       => $now->copy()->subDay()->setTime(7, 0),
            'closed_at'       => $now->copy()->subDay()->setTime(15, 0),
        ]);

        // Transaksi oleh Teller B di dalam shift kemarin -> harus masuk saat scope=shift
        Transaction::create([
            'saver_id'         => $this->saverTwo->id,
            'user_id'          => $this->tellerB->id,
            'transaction_date' => $now->copy()->subDay()->setTime(10, 0),
            'transaction_type' => 'Deposit',
            'amount'           => 77000,
            'status'           => 'Approved',
        ]);

        // Transaksi oleh Teller C kemarin -> tidak boleh muncul walau scope=shift
        Transaction::create([
            'saver_id'         => $this->saverTwo->id,
            'user_id'          => $tellerC->id,
            'transaction_date' => $now->copy()->subDay()->setTime(11, 0),
            'transaction_type' => 'Deposit',
            'amount'           => 88000,
            'status'           => 'Approved',
        ]);

        $mineResponse = $this->actingAs($this->tellerA)->get(route('teller.reports.mutations', [
            'period' => 'yesterday',
            'scope'  => 'mine',
        ]));
        $mineResponse->assertStatus(200);
        $mineResponse->assertDontSee('77.000');
        $mineResponse->assertDontSee('88.000');

        $shiftResponse = $this->actingAs($this->tellerA)->get(route('teller.reports.mutations', [
            'period' => 'yesterday',
            'scope'  => 'shift',
        ]));
        $shiftResponse->assertStatus(200);
        $shiftResponse->assertSee('77.000');
        $shiftResponse->assertSee('Teller Rekan Uji');
        $shiftResponse->assertDontSee('88.000');
    }

    public function test_teller_empty_report_still_exports_valid_pdf(): void
    {
        $pdfRes = $this->actingAs($this->tellerA)->get(route('teller.reports.mutations.pdf', [
            'period'     => 'custom',
            'start_date' => '2026-01-01',
            'end_date'   => '2026-01-05',
        ]));

        $pdfRes->assertStatus(200);
        $this->assertStringStartsWith('%PDF-', $pdfRes->getContent());
    }

    public function test_admin_can_view_filtered_customer_history_and_export_rekening_koran_pdf(): void
    {
        // Nasabah punya saldo terkini 150.000 dengan riwayat Oktober + November
        Transaction::create([
            'saver_id'         => $this->saverOne->id,
            'user_id'          => $this->tellerA->id,
            'transaction_date' => Carbon::create(2026, 10, 5, 9, 0, 0, 'Asia/Jakarta'),
            'transaction_type' => 'Deposit',
            'amount'           => 100000,
            'status'           => 'Approved',
        ]);
        Transaction::create([
            'saver_id'         => $this->saverOne->id,
            'user_id'          => $this->tellerB->id,
            'transaction_date' => Carbon::create(2026, 11, 5, 10, 0, 0, 'Asia/Jakarta'),
            'transaction_type' => 'Deposit',
            'amount'           => 75000,
            'status'           => 'Approved',
        ]);
        Transaction::create([
            'saver_id'         => $this->saverOne->id,
            'user_id'          => $this->tellerA->id,
            'transaction_date' => Carbon::create(2026, 11, 10, 11, 0, 0, 'Asia/Jakarta'),
            'transaction_type' => 'Withdrawal',
            'amount'           => 25000,
            'status'           => 'Approved',
        ]);

        // Pastikan bug relasi petugas lama sudah teratasi di web view (menampilkan officer_name Teller B)
        $historyPage = $this->actingAs($this->admin)->get(route('admin.savers.history', $this->saverOne->id));
        $historyPage->assertStatus(200);
        $historyPage->assertSee('Teller Rekan Uji');
        $historyPage->assertSee('Preview / Cetak PDF');

        // Verifikasi logika saldo awal & saldo berjalan Rekening Koran pada sub-periode bulan ini
        /** @var MutationReportService $service */
        $service = app(MutationReportService::class);
        $monthPeriod = $service->resolveCustomerPeriod('this_month', null, null);
        $openingNovember = $service->calculateOpeningBalanceForCustomer(
            $this->saverOne->id,
            (float) $this->saverOne->saldo,
            $monthPeriod['start_at']
        );
        $this->assertEquals(100000.0, $openingNovember);

        $monthTransactions = $service
            ->buildCustomerMutationQuery($this->saverOne->id, $monthPeriod, 'all')
            ->orderByRaw($service->effectiveTimestampExpression() . ' asc, id asc')
            ->get();

        $ledger = $service->attachRunningBalance($monthTransactions, $openingNovember);
        $this->assertCount(2, $ledger['transactions']);
        $this->assertEquals(175000.0, $ledger['transactions'][0]->running_balance);
        $this->assertEquals(150000.0, $ledger['transactions'][1]->running_balance);
        $this->assertEquals(150000.0, $ledger['closing_balance']);

        // Ekspor PDF Per Nasabah (default inline stream & download)
        $pdfStream = $this->actingAs($this->admin)->get(route('admin.savers.history.pdf', [
            'id'     => $this->saverOne->id,
            'period' => 'this_month',
        ]));
        $pdfStream->assertStatus(200);
        $this->assertStringContainsString('application/pdf', (string) $pdfStream->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF-', $pdfStream->getContent());

        // Soft-deleted nasabah masih bisa dicetak PDF oleh Admin
        $this->saverTwo->delete();
        $pdfArchived = $this->actingAs($this->admin)->get(route('admin.savers.history.pdf', [
            'id'       => $this->saverTwo->id,
            'download' => 1,
        ]));
        $pdfArchived->assertStatus(200);
        $this->assertStringContainsString('attachment;', (string) $pdfArchived->headers->get('content-disposition'));
        $this->assertStringStartsWith('%PDF-', $pdfArchived->getContent());

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->admin->id,
            'action'  => 'export_pdf',
            'module'  => 'Mutasi Per Nasabah',
        ]);
    }

    public function test_role_authorization_is_strictly_enforced(): void
    {
        // Teller dilarang mengakses PDF per nasabah milik Admin
        $tellerToAdminPdf = $this->actingAs($this->tellerA)->get(route('admin.savers.history.pdf', $this->saverOne->id));
        $this->assertTrue(in_array($tellerToAdminPdf->status(), [302, 403]));

        // Admin dilarang mengakses endpoint khusus role Teller
        $adminToTellerReport = $this->actingAs($this->admin)->get(route('teller.reports.mutations'));
        $this->assertTrue(in_array($adminToTellerReport->status(), [302, 403]));

        $adminToTellerPdf = $this->actingAs($this->admin)->get(route('teller.reports.mutations.pdf'));
        $this->assertTrue(in_array($adminToTellerPdf->status(), [302, 403]));
    }

    public function test_entry_point_links_render_cleanly_in_existing_views(): void
    {
        // Admin Data Nasabah memuat link cetak PDF per nasabah
        $adminSavers = $this->actingAs($this->admin)->get(route('admin.savers.index'));
        $adminSavers->assertStatus(200);
        $adminSavers->assertSee(route('admin.savers.history.pdf', $this->saverOne->id));

        // Teller Dashboard (dengan shift aktif) memuat menu sidebar & shortcut Laporan Mutasi
        TellerShift::create([
            'user_id'         => $this->tellerA->id,
            'opening_balance' => 100000,
            'status'          => 'Open',
            'opened_at'       => now(),
        ]);

        $tellerDash = $this->actingAs($this->tellerA)->get(route('teller.dashboard'));
        $tellerDash->assertStatus(200);
        $tellerDash->assertSee('Laporan Mutasi');
        $tellerDash->assertSee(route('teller.reports.mutations'));
    }
}
