<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\TellerShift;
use App\Models\AccountingJournal;

class AdminShiftTest extends TestCase
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

    public function test_admin_can_view_shifts_index()
    {
        // Buat shift closed dan approved
        TellerShift::create([
            'user_id'         => $this->teller->id,
            'opening_balance' => 100000,
            'closing_balance' => 250000,
            'system_balance'  => 250000,
            'status'          => 'Closed',
            'opened_at'       => now()->subHours(8),
            'closed_at'       => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.shifts.index'));
        $response->assertStatus(200);
        $response->assertSee('Daftar Penutupan Shift Teller');
        $response->assertSee('Pindah ke Brankas');
    }

    public function test_teller_cannot_access_admin_shifts()
    {
        $response = $this->actingAs($this->teller)->get(route('admin.shifts.index'));
        // Role middleware restricts to Administrator, Admin
        $this->assertTrue(in_array($response->status(), [403, 302]));
    }

    public function test_admin_can_approve_closed_shift_and_generate_accounting_journals()
    {
        $shift = TellerShift::create([
            'user_id'         => $this->teller->id,
            'opening_balance' => 500000,
            'closing_balance' => 750000,
            'system_balance'  => 750000,
            'status'          => 'Closed',
            'opened_at'       => now()->subHours(8),
            'closed_at'       => now(),
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.shifts.approve', $shift->id));

        $response->assertRedirect(route('admin.shifts.index'));
        $response->assertSessionHas('success');

        // Verifikasi shift berstatus Approved dan approved_by terisi
        $shift->refresh();
        $this->assertEquals('Approved', $shift->status);
        $this->assertEquals($this->admin->id, $shift->approved_by);
        $this->assertNotNull($shift->approved_at);

        // Verifikasi Jurnal Double-Entry (Akun 102 Kas Utama / Brankas Debit, Akun 101 Kas Teller Credit)
        $debitJournal = AccountingJournal::where('account_code', '102')
            ->where('position', 'Debit')
            ->where('amount', 750000)
            ->first();

        $creditJournal = AccountingJournal::where('account_code', '101')
            ->where('position', 'Credit')
            ->where('amount', 750000)
            ->first();

        $this->assertNotNull($debitJournal, 'Jurnal Debit Kas Utama (102) harus terbentuk');
        $this->assertNotNull($creditJournal, 'Jurnal Kredit Kas Teller (101) harus terbentuk');
        $this->assertEquals($debitJournal->transaction_id, $creditJournal->transaction_id);
    }
}
