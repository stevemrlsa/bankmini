<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Saver;
use App\Models\DailyReport;
use App\Models\Transaction;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;

class AllApiComprehensiveTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $teller;
    protected Saver $saver;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed default users & records
        $this->artisan('db:seed');

        $this->admin = User::where('role', 'Administrator')->first();
        $this->teller = User::where('role', 'Teller')->first();

        // Siapkan nasabah untuk pengujian
        $this->saver = Saver::updateOrCreate(
            ['student_id' => 'NIS999'],
            [
                'account_number'  => '20269999',
                'student_name'    => 'Santri Pengujian',
                'grade_class'     => '12 RPL',
                'gender'          => 'Laki-laki',
                'username'        => 'santri_test',
                'email'           => 'santri@bankmini.test',
                'whatsapp_number' => '081234567890',
                'security_pin'    => Hash::make('123456'),
                'password'        => Hash::make('password123'),
                'saldo'           => 100000,
                'status'          => 'active',
            ]
        );
    }

    /**
     * 1. PENGUJIAN API MOBILE AUTH (Separation of Credentials)
     * - POST api/mobile/auth/request-otp
     * - POST api/mobile/auth/verify-otp
     * - POST api/mobile/auth/setup-credentials
     * - POST api/mobile/auth/login
     */
    public function test_01_mobile_auth_endpoints()
    {
        // 1.1 Request OTP
        $otpResponse = $this->postJson('/api/mobile/auth/request-otp', [
            'student_id' => 'NIS999',
            'username'   => 'santri_test',
        ]);
        $otpResponse->assertStatus(200)
                    ->assertJson(['success' => true]);

        // Pastikan OTP tersimpan di Cache
        $cachedOtp = Cache::get('otp_NIS999');
        $this->assertNotEmpty($cachedOtp);
        $this->assertEquals(6, strlen($cachedOtp));

        // 1.2 Verify OTP
        $verifyResponse = $this->postJson('/api/mobile/auth/verify-otp', [
            'student_id' => 'NIS999',
            'otp'        => $cachedOtp,
        ]);
        $verifyResponse->assertStatus(200)
                       ->assertJson(['success' => true]);

        // 1.3 Setup Credentials (Password baru & PIN transaksi baru)
        $setupResponse = $this->postJson('/api/mobile/auth/setup-credentials', [
            'student_id'            => 'NIS999',
            'password'              => 'baru1234',
            'password_confirmation' => 'baru1234',
            'pin'                   => '654321',
            'pin_confirmation'      => '654321',
        ]);
        $setupResponse->assertStatus(200)
                      ->assertJson(['success' => true]);
        $this->assertArrayHasKey('token', $setupResponse->json());

        // 1.4 Mobile Login dengan Username & Password baru
        $loginResponse = $this->postJson('/api/mobile/auth/login', [
            'username' => 'santri_test',
            'password' => 'baru1234',
        ]);
        $loginResponse->assertStatus(200)
                      ->assertJson(['success' => true])
                      ->assertJsonPath('data.username', 'santri_test');
    }

    /**
     * 2. PENGUJIAN API MOBILE SAVER (Legacy / Core Saver Profile & Transactions)
     * - POST api/login
     * - GET  api/profile
     * - GET  api/transactions
     * - POST api/logout
     */
    public function test_02_saver_api_endpoints()
    {
        // 2.1 Login via SaverApiController (menggunakan student_id & PIN)
        $loginResponse = $this->postJson('/api/login', [
            'student_id'   => 'NIS999',
            'security_pin' => '123456',
        ]);
        $loginResponse->assertStatus(200)
                      ->assertJson(['success' => true]);
        $token = $loginResponse->json('token');

        // 2.2 Get Profile dengan Sanctum Token
        $profileResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
                                ->getJson('/api/profile');
        $profileResponse->assertStatus(200)
                        ->assertJson(['success' => true])
                        ->assertJsonPath('data.student_id', 'NIS999');

        // 2.3 Get Transactions
        $txResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
                           ->getJson('/api/transactions');
        $txResponse->assertStatus(200)
                   ->assertJson(['success' => true]);

        // 2.4 Logout
        $logoutResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
                               ->postJson('/api/logout');
        $logoutResponse->assertStatus(200)
                       ->assertJson(['success' => true]);
    }

    /**
     * 3. PENGUJIAN ALUR LUPA PIN & RESET PIN MOBILE
     * - POST api/forgot-pin/send-otp
     * - POST api/forgot-pin/verify-otp
     * - POST api/forgot-pin/reset-pin
     */
    public function test_03_forgot_pin_endpoints()
    {
        // 3.1 Send OTP Forgot PIN
        $sendResponse = $this->postJson('/api/forgot-pin/send-otp', [
            'identifier' => 'NIS999',
        ]);
        $sendResponse->assertStatus(200)
                     ->assertJson(['success' => true]);
        $otpToken = $sendResponse->json('otp_token');
        $devOtp   = $sendResponse->json('dev_otp');

        // 3.2 Verify OTP Forgot PIN
        $verifyResponse = $this->postJson('/api/forgot-pin/verify-otp', [
            'identifier' => 'NIS999',
            'otp'        => $devOtp,
            'otp_token'  => $otpToken,
        ]);
        $verifyResponse->assertStatus(200)
                       ->assertJson(['success' => true]);
        $resetToken = $verifyResponse->json('reset_token');

        // 3.3 Reset PIN Baru (Non-sekuensial: 849201)
        $resetResponse = $this->postJson('/api/forgot-pin/reset-pin', [
            'identifier'  => 'NIS999',
            'reset_token' => $resetToken,
            'new_pin'     => '849201',
            'confirm_pin' => '849201',
        ]);
        $resetResponse->assertStatus(200)
                      ->assertJson(['success' => true]);
        $this->assertTrue(Hash::check('849201', $this->saver->fresh()->security_pin));
    }

    /**
     * 4. PENGUJIAN NASABAH PORTAL WEB API
     * - POST api/saver/login
     * - POST api/customer/login
     * - GET  api/nasabah/profile
     * - GET  api/nasabah/mutasi
     */
    public function test_04_nasabah_portal_endpoints()
    {
        // 4.1 Login Customer/Saver
        $loginResponse = $this->postJson('/api/customer/login', [
            'account_number' => '20269999',
            'security_pin'   => '123456',
        ]);
        $loginResponse->assertStatus(200)
                      ->assertJson(['success' => true]);

        // 4.2 Login Alias /saver/login
        $aliasResponse = $this->postJson('/api/saver/login', [
            'account_number' => '20269999',
            'security_pin'   => '123456',
        ]);
        $aliasResponse->assertStatus(200)
                      ->assertJson(['success' => true]);
        $token = $aliasResponse->json('token');

        // 4.3 Profile (auth:saver)
        $profileResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
                                ->getJson('/api/nasabah/profile');
        $profileResponse->assertStatus(200)
                        ->assertJson(['success' => true]);

        // 4.4 Mutasi (auth:saver)
        $mutasiResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
                               ->getJson('/api/nasabah/mutasi');
        $mutasiResponse->assertStatus(200)
                       ->assertJson(['success' => true]);
    }

    /**
     * 5. PENGUJIAN TRANSAKSI & OPERASIONAL TELLER
     * - GET  api/search-saver
     * - GET  api/search-customer
     * - POST api/deposit
     * - POST api/withdrawal
     */
    public function test_05_teller_endpoints()
    {
        // 5.1 Search Saver
        $searchResponse = $this->actingAs($this->teller, 'sanctum')
                               ->getJson('/api/search-saver?q=20269999');
        $searchResponse->assertStatus(200)
                       ->assertJson(['success' => true]);

        // 5.2 Search Customer (alias)
        $searchCust = $this->actingAs($this->teller, 'sanctum')
                           ->getJson('/api/search-customer?q=20269999');
        $searchCust->assertStatus(200)
                   ->assertJson(['success' => true]);

        // 5.3 Deposit
        $depositResponse = $this->actingAs($this->teller, 'sanctum')
                                ->postJson('/api/deposit', [
                                    'account_number' => '20269999',
                                    'amount'         => 25000,
                                ]);
        $depositResponse->assertStatus(200)
                        ->assertJson(['success' => true]);
        $this->assertEquals(125000, $this->saver->fresh()->saldo);

        // 5.4 Withdrawal
        $withdrawResponse = $this->actingAs($this->teller, 'sanctum')
                                 ->postJson('/api/withdrawal', [
                                     'account_number' => '20269999',
                                     'amount'         => 15000,
                                     'security_pin'   => '123456',
                                 ]);
        $withdrawResponse->assertStatus(200)
                         ->assertJson(['success' => true]);
        $this->assertEquals(110000, $this->saver->fresh()->saldo);
    }

    /**
     * 6. PENGUJIAN ADMIN USER MANAGEMENT & AUDIT JOURNALS
     * - GET    api/user
     * - GET    api/admin/users
     * - POST   api/admin/users
     * - PUT    api/admin/users/{id}
     * - DELETE api/admin/users/{id}
     * - GET    api/admin/audit-journals
     */
    public function test_06_admin_user_endpoints()
    {
        // 6.1 Current authenticated user
        $userResponse = $this->actingAs($this->admin, 'sanctum')->getJson('/api/user');
        $userResponse->assertStatus(200)
                     ->assertJsonPath('id', $this->admin->id);

        // 6.2 Index users
        $indexResponse = $this->actingAs($this->admin, 'sanctum')->getJson('/api/admin/users');
        $indexResponse->assertStatus(200)
                      ->assertJson(['success' => true]);

        // 6.3 Store user baru
        $storeResponse = $this->actingAs($this->admin, 'sanctum')->postJson('/api/admin/users', [
            'officer_name'    => 'Teller Baru Test',
            'username'        => 'teller_baru_test',
            'email'           => 'teller.baru@bankmini.test',
            'whatsapp_number' => '081299998888',
            'password'        => 'rahasia123',
            'role'            => 'Teller',
        ]);
        $storeResponse->assertStatus(200)
                      ->assertJson(['success' => true]);
        $newUserId = $storeResponse->json('data.id');

        // 6.4 Update user
        $updateResponse = $this->actingAs($this->admin, 'sanctum')->putJson("/api/admin/users/{$newUserId}", [
            'officer_name' => 'Teller Updated Test',
        ]);
        $updateResponse->assertStatus(200)
                       ->assertJson(['success' => true]);

        // 6.5 Delete user
        $deleteResponse = $this->actingAs($this->admin, 'sanctum')->deleteJson("/api/admin/users/{$newUserId}");
        $deleteResponse->assertStatus(200)
                       ->assertJson(['success' => true]);

        // 6.6 Audit Journals
        $auditResponse = $this->actingAs($this->admin, 'sanctum')->getJson('/api/admin/audit-journals');
        $auditResponse->assertStatus(200)
                      ->assertJson(['success' => true]);
    }

    /**
     * 7. PENGUJIAN ADMIN REPORT APPROVAL & REJECTION
     * - POST api/admin/approve-report
     * - POST api/admin/reject-report
     */
    public function test_07_admin_report_endpoints()
    {
        // Buat DailyReport dummy untuk ditest
        $report1 = DailyReport::create([
            'teller_id'                => $this->teller->id,
            'report_date'              => now()->subDay()->format('Y-m-d'),
            'opening_balance'          => 100000,
            'total_deposits'           => 50000,
            'total_withdrawals'        => 20000,
            'closing_balance_system'   => 130000,
            'closing_balance_physical' => 130000,
            'status'                   => 'pending',
        ]);

        $report2 = DailyReport::create([
            'teller_id'                => $this->teller->id,
            'report_date'              => now()->format('Y-m-d'),
            'opening_balance'          => 50000,
            'total_deposits'           => 10000,
            'total_withdrawals'        => 5000,
            'closing_balance_system'   => 55000,
            'closing_balance_physical' => 50000,
            'status'                   => 'pending',
        ]);

        // 7.1 Approve report
        $approveResponse = $this->actingAs($this->admin, 'sanctum')->postJson('/api/admin/approve-report', [
            'report_id' => $report1->id,
        ]);
        $approveResponse->assertStatus(200)
                        ->assertJson(['success' => true]);
        $this->assertEquals('approved', $report1->fresh()->status);

        // 7.2 Reject report
        $rejectResponse = $this->actingAs($this->admin, 'sanctum')->postJson('/api/admin/reject-report', [
            'report_id' => $report2->id,
            'notes'     => 'Terdapat selisih kas fisik Rp 5.000.',
        ]);
        $rejectResponse->assertStatus(200)
                       ->assertJson(['success' => true]);
        $this->assertEquals('rejected', $report2->fresh()->status);
    }

    /**
     * 8. PENGUJIAN MANAJEMEN NASABAH (SAVER CRUD)
     * - GET    api/saver
     * - GET    api/saver/{id}
     * - POST   api/saver
     * - PUT    api/saver/{id}
     * - DELETE api/saver/{id}
     */
    public function test_08_saver_crud_endpoints()
    {
        // 8.1 Index Saver (Bisa diakses Teller & Admin)
        $indexResponse = $this->actingAs($this->teller, 'sanctum')->getJson('/api/saver');
        $indexResponse->assertStatus(200)
                      ->assertJson(['success' => true]);

        // 8.2 Show Saver
        $showResponse = $this->actingAs($this->teller, 'sanctum')->getJson("/api/saver/{$this->saver->id}");
        $showResponse->assertStatus(200)
                     ->assertJson(['success' => true]);

        // 8.3 Store Saver (Hanya Admin)
        $storeResponse = $this->actingAs($this->admin, 'sanctum')->postJson('/api/saver', [
            'account_number' => '20268888',
            'student_id'     => 'NIS888',
            'student_name'   => 'Siswa Baru Admin',
            'grade_class'    => '10 TKJ',
            'security_pin'   => '123456',
            'saldo'          => 20000,
        ]);
        $storeResponse->assertStatus(201)
                      ->assertJson(['success' => true]);
        $newSaverId = $storeResponse->json('data.id');

        // 8.4 Update Saver (Hanya Admin)
        $updateResponse = $this->actingAs($this->admin, 'sanctum')->putJson("/api/saver/{$newSaverId}", [
            'student_name' => 'Siswa Baru Admin Updated',
            'saldo'        => 25000,
        ]);
        $updateResponse->assertStatus(200)
                       ->assertJson(['success' => true]);

        // 8.5 Delete Saver (Hanya Admin)
        $deleteResponse = $this->actingAs($this->admin, 'sanctum')->deleteJson("/api/saver/{$newSaverId}");
        $deleteResponse->assertStatus(200)
                       ->assertJson(['success' => true]);
    }

    /**
     * 9. PENGUJIAN WEB EMPLOYEE MANAGEMENT DENGAN EMAIL & WHATSAPP
     * - POST route('admin.users.store')
     * - PUT  route('admin.users.update', $id)
     */
    public function test_09_web_employee_management_with_contact_info()
    {
        // 9.1 Store via Web Form
        $response = $this->actingAs($this->admin)->post(route('admin.users.store'), [
            'officer_name'    => 'Pegawai Web Baru',
            'username'        => 'pegawai_web',
            'email'           => 'pegawai.web@bankmini.test',
            'whatsapp_number' => '081234567899',
            'role'            => 'Teller',
            'password'        => 'password123',
        ]);

        $response->assertRedirect(route('admin.users.index'));
        $createdUser = User::where('username', 'pegawai_web')->first();
        $this->assertNotNull($createdUser);
        $this->assertEquals('pegawai.web@bankmini.test', $createdUser->email);
        $this->assertEquals('081234567899', $createdUser->whatsapp_number);

        // 9.2 Update via Web Form
        $updateResponse = $this->actingAs($this->admin)->put(route('admin.users.update', $createdUser->id), [
            'officer_name'    => 'Pegawai Web Terupdate',
            'username'        => 'pegawai_web',
            'email'           => 'pegawai.web.updated@bankmini.test',
            'whatsapp_number' => '089876543210',
            'role'            => 'Teller',
        ]);

        $updateResponse->assertRedirect(route('admin.users.index'));
        $this->assertEquals('pegawai.web.updated@bankmini.test', $createdUser->fresh()->email);
        $this->assertEquals('089876543210', $createdUser->fresh()->whatsapp_number);
    }
}

