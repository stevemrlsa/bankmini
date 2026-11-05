<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Member;
use App\Models\Saver;
use App\Models\Transaction;
use App\Models\AccountingJournal;
use App\Models\DailyReport;
use Illuminate\Support\Facades\Hash;

class BankMiniPRDTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
    }

    public function test_admin_can_toggle_user_status()
    {
        $admin = User::where('role', 'Administrator')->first();
        $teller = User::where('role', 'Teller')->first();

        $response = $this->actingAs($admin)->patch(route('admin.users.toggle-status', $teller->id));
        $response->assertRedirect();
        
        $this->assertEquals('Blokir', $teller->fresh()->status);
    }

    public function test_admin_can_activate_member_with_6_digit_pin()
    {
        $admin = User::where('role', 'Administrator')->first();
        $inactiveMember = Member::where('status', 'inactive')->first();

        $response = $this->actingAs($admin)->post(route('admin.members.activate', $inactiveMember->user_id), [
            'security_pin' => '654321',
            'security_pin_confirmation' => '654321',
        ]);

        $response->assertRedirect();
        $this->assertEquals('active', $inactiveMember->fresh()->status);
        $this->assertTrue(Hash::check('654321', $inactiveMember->fresh()->security_pin));

        // Verify initial balance of Rp 10,000 and initial Deposit transaction
        $saver = Saver::where('student_id', $inactiveMember->student_id)->first();
        $this->assertNotNull($saver);
        $this->assertEquals(10000, $saver->saldo);

        $transaction = Transaction::where('saver_id', $saver->id)->first();
        $this->assertNotNull($transaction);
        $this->assertEquals('Deposit', $transaction->transaction_type);
        $this->assertEquals(10000, $transaction->amount);
        $this->assertEquals('Approved', $transaction->status);
    }

    public function test_first_deposit_automatically_migrates_active_member_to_saver()
    {
        $teller = User::where('role', 'Teller')->first();
        $activeMember = Member::where('status', 'active')->first();

        // Ensure this student is not in savers yet
        Saver::where('student_id', $activeMember->student_id)->forceDelete();

        $response = $this->actingAs($teller)->post(route('teller.setoran.store'), [
            'account_number' => $activeMember->student_id, // Search by NIS/Student ID
            'amount' => 25000,
        ]);

        $response->assertRedirect();

        // Saver record should be created automatically
        $saver = Saver::where('student_id', $activeMember->student_id)->first();
        $this->assertNotNull($saver);
        $this->assertEquals(25000, $saver->saldo);

        // Transaction and double entry journal should exist
        $transaction = Transaction::where('saver_id', $saver->id)->first();
        $this->assertNotNull($transaction);
        $this->assertEquals(25000, $transaction->amount);
    }

    public function test_withdrawal_fails_without_correct_pin()
    {
        $teller = User::where('role', 'Teller')->first();
        $saver = Saver::first();

        // Wrong PIN
        $response = $this->actingAs($teller)->post(route('teller.penarikan.store'), [
            'account_number' => $saver->account_number,
            'amount' => 10000,
            'security_pin' => '999999',
        ]);

        $response->assertSessionHasErrors('security_pin');
    }

    public function test_withdrawal_fails_if_retained_balance_under_10000()
    {
        $teller = User::where('role', 'Teller')->first();
        $saver = Saver::first();
        $saver->update(['saldo' => 15000, 'security_pin' => Hash::make('123456')]);

        // Attempting to withdraw 10,000 would leave 5,000 which is < 10,000
        $response = $this->actingAs($teller)->post(route('teller.penarikan.store'), [
            'account_number' => $saver->account_number,
            'amount' => 10000,
            'security_pin' => '123456',
        ]);

        $response->assertSessionHasErrors('amount');
    }

    public function test_forgot_pin_resets_pin_to_default()
    {
        $teller = User::where('role', 'Teller')->first();
        $saver = Saver::first();

        $response = $this->actingAs($teller)->post(route('teller.savers.reset-pin', $saver->id));
        $response->assertRedirect();

        $this->assertTrue(Hash::check('123456', $saver->fresh()->security_pin));
    }

    public function test_admin_can_reset_customer_password_to_default()
    {
        $admin = User::where('role', 'Administrator')->first();
        $saver = Saver::first();
        $saver->update(['password' => Hash::make('customPassword999')]);

        $response = $this->actingAs($admin)->post(route('admin.savers.reset-password', $saver->id));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertTrue(Hash::check('123456', $saver->fresh()->password));
    }

    public function test_deleted_saver_preserves_transaction_history()
    {
        $admin = User::where('role', 'Administrator')->first();
        $saver = Saver::first();
        $transactionId = Transaction::where('saver_id', $saver->id)->first()->id;

        // Admin destroys saver (soft delete)
        $response = $this->actingAs($admin)->delete(route('admin.savers.destroy', $saver->id));
        $response->assertRedirect();

        $this->assertSoftDeleted('savers', ['id' => $saver->id]);
        
        // Transaction still exists in database
        $transaction = Transaction::find($transactionId);
        $this->assertNotNull($transaction);
        // And transaction relation can still load soft-deleted saver
        $this->assertNotNull($transaction->saver);
    }

    public function test_admin_can_approve_cash_report_with_positive_amount()
    {
        $admin = User::where('role', 'Administrator')->first();
        $teller = User::where('role', 'Teller')->first();

        $report = DailyReport::create([
            'teller_id' => $teller->id,
            'report_date' => now()->toDateString(),
            'opening_balance' => 0,
            'total_deposit' => 100000,
            'total_withdrawal' => 20000,
            'closing_balance_system' => 80000,
            'closing_balance_physical' => 80000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.approval.approve', $report->id));
        $response->assertRedirect();

        $this->assertEquals('approved', $report->fresh()->status);
        $vaultTransaction = Transaction::where('transaction_type', 'Vault_Transfer')->first();
        $this->assertNotNull($vaultTransaction);
        $this->assertEquals(80000, $vaultTransaction->amount);
    }

    public function test_admin_can_approve_cash_report_with_negative_amount_without_sql_error()
    {
        $admin = User::where('role', 'Administrator')->first();
        $teller = User::where('role', 'Teller')->first();

        // Report with negative physical balance (e.g. -70,000)
        $report = DailyReport::create([
            'teller_id' => $teller->id,
            'report_date' => now()->toDateString(),
            'opening_balance' => 0,
            'total_deposit' => 10000,
            'total_withdrawal' => 80000,
            'closing_balance_system' => -70000,
            'closing_balance_physical' => -70000,
            'status' => 'pending',
        ]);

        $initialVaultCount = Transaction::where('transaction_type', 'Vault_Transfer')->count();

        $response = $this->actingAs($admin)->post(route('admin.approval.approve', $report->id));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Status updated to approved without creating negative Vault_Transfer
        $this->assertEquals('approved', $report->fresh()->status);
        $this->assertEquals($initialVaultCount, Transaction::where('transaction_type', 'Vault_Transfer')->count());
    }

    public function test_admin_can_unblock_employee_and_access_settings_without_logout()
    {
        $admin = User::where('role', 'Administrator')->first();
        $teller = User::where('role', 'Teller')->first();
        $teller->update(['status' => 'Blokir']);

        // 1. Unblock via employee.unblock route
        $response = $this->actingAs($admin)->post(route('employee.unblock', $teller->id));
        $response->assertRedirect();
        $this->assertEquals('Aktif', $teller->fresh()->status);
        $this->assertAuthenticatedAs($admin);

        // 2. Access settings page
        $settingsResponse = $this->actingAs($admin)->get(route('admin.settings.index'));
        $settingsResponse->assertStatus(200);
        $this->assertAuthenticatedAs($admin);

        // 3. Access settings via alias route
        $aliasResponse = $this->actingAs($admin)->get(route('settings.index'));
        $aliasResponse->assertStatus(200);
        $this->assertAuthenticatedAs($admin);
    }

    public function test_teller_one_stop_activation_feature()
    {
        $teller = User::where('role', 'Teller')->first();

        // 1. Create an inactive student saver
        $inactiveSaver = Saver::create([
            'student_id'   => '999888',
            'student_name' => 'Budi Santoso',
            'grade_class'  => 'X-RPL-1',
            'security_pin' => Hash::make('123456'),
            'status'       => 'inactive',
            'saldo'        => 0,
        ]);

        // 2. Teller accesses activation index page
        $indexResponse = $this->actingAs($teller)->get(route('teller.activations.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertSee('Budi Santoso');

        // 3. Teller attempts activation with amount < 10,000 (must be rejected)
        $invalidAmountResponse = $this->actingAs($teller)->post(route('teller.activations.activate', $inactiveSaver->id), [
            'username'        => 'budisantoso99',
            'email'           => 'budi@bankmini.sch.id',
            'whatsapp_number' => '081234567890',
            'amount'          => 5000, // Below minimum 10,000
        ]);
        $invalidAmountResponse->assertSessionHasErrors(['amount']);
        $this->assertEquals('inactive', $inactiveSaver->fresh()->status);

        // 4. Teller attempts activation with missing required fields
        $missingFieldsResponse = $this->actingAs($teller)->post(route('teller.activations.activate', $inactiveSaver->id), [
            'amount' => 15000,
        ]);
        $missingFieldsResponse->assertSessionHasErrors(['username', 'email', 'whatsapp_number']);

        // 5. Successful One-Stop Activation with valid data and initial deposit of 25,000
        $validResponse = $this->actingAs($teller)->post(route('teller.activations.activate', $inactiveSaver->id), [
            'username'        => 'budisantoso99',
            'email'           => 'budi@bankmini.sch.id',
            'whatsapp_number' => '081234567890',
            'amount'          => 25000,
        ]);

        $validResponse->assertRedirect();
        $validResponse->assertSessionHas('success');

        // Verify Saver is now active with generated account number and updated balance
        $refreshedSaver = $inactiveSaver->fresh();
        $this->assertEquals('active', $refreshedSaver->status);
        $this->assertNotNull($refreshedSaver->account_number);
        $this->assertEquals(25000, $refreshedSaver->saldo);

        // Verify User account created with contact details
        $user = User::where('username', 'budisantoso99')->first();
        $this->assertNotNull($user);
        $this->assertEquals('budi@bankmini.sch.id', $user->email);
        $this->assertEquals('081234567890', $user->whatsapp_number);
        $this->assertTrue(Hash::check('123456', $user->password));

        // Verify Transaction record
        $transaction = Transaction::where('saver_id', $refreshedSaver->id)
            ->where('transaction_type', 'Deposit')
            ->first();
        $this->assertNotNull($transaction);
        $this->assertEquals(25000, $transaction->amount);
        $this->assertEquals('Approved', $transaction->status);

        // Verify Double-Entry Accounting Journals
        $debitKas = AccountingJournal::where('transaction_id', $transaction->id)
            ->where('account_code', '101')
            ->where('position', 'Debit')
            ->first();
        $creditTabungan = AccountingJournal::where('transaction_id', $transaction->id)
            ->where('account_code', '201')
            ->where('position', 'Credit')
            ->first();

        $this->assertNotNull($debitKas);
        $this->assertEquals(25000, $debitKas->amount);
        $this->assertNotNull($creditTabungan);
        $this->assertEquals(25000, $creditTabungan->amount);
    }
}

