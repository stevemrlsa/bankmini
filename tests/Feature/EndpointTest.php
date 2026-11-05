<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Saver;
use App\Models\DailyReport;
use App\Models\Transaction;
use Illuminate\Support\Facades\Hash;

class EndpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Run seeders so we have admin, teller
        $this->artisan('db:seed');
        
        // Buat atau reset nasabah untuk test
        Saver::updateOrCreate(
            ['account_number' => '20260001'],
            [
                'student_id'     => 'NIS123',
                'student_name'   => 'Siswa Test',
                'grade_class'    => '10A',
                'security_pin'   => Hash::make('123456'),
                'saldo'          => 50000,
            ]
        );
    }

    public function test_api_customer_login()
    {
        $response = $this->postJson('/api/customer/login', [
            'account_number' => '20260001',
            'security_pin'   => '123456'
        ]);

        $response->assertStatus(200);
        $this->assertArrayHasKey('token', $response->json());
    }

    public function test_api_search_customer_by_teller()
    {
        $teller = User::where('role', 'Teller')->first();
        $response = $this->actingAs($teller, 'sanctum')->getJson('/api/search-customer?q=20260001');

        $response->assertStatus(200);
        $this->assertEquals('20260001', $response->json('data.account_number'));
    }

    public function test_api_deposit_by_teller()
    {
        $teller = User::where('role', 'Teller')->first();
        $response = $this->actingAs($teller, 'sanctum')->postJson('/api/deposit', [
            'account_number' => '20260001',
            'amount'         => 10000
        ]);

        $response->assertStatus(200);
        
        $customer = Saver::where('account_number', '20260001')->first();
        $this->assertEquals(60000, $customer->saldo);
    }

    public function test_api_withdrawal_by_teller()
    {
        $teller = User::where('role', 'Teller')->first();
        $response = $this->actingAs($teller, 'sanctum')->postJson('/api/withdrawal', [
            'account_number' => '20260001',
            'amount'         => 20000,
            'security_pin'   => '123456'
        ]);

        $response->assertStatus(200);

        $customer = Saver::where('account_number', '20260001')->first();
        $this->assertEquals(30000, $customer->saldo);
    }

    public function test_api_admin_users()
    {
        $admin = User::where('role', 'Administrator')->first();
        $response = $this->actingAs($admin, 'sanctum')->getJson('/api/admin/users');

        $response->assertStatus(200);
    }

    public function test_web_teller_search_saver_by_account_number()
    {
        $teller = User::where('role', 'Teller')->first();
        $response = $this->actingAs($teller)->getJson('/teller/search-saver?q=20260001');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'account_number'  => '20260001',
                'student_id'      => 'NIS123',
                'student_name'    => 'Siswa Test',
                'saldo'           => 50000,
            ]
        ]);
    }

    public function test_web_teller_search_saver_by_nis()
    {
        $teller = User::where('role', 'Teller')->first();
        $response = $this->actingAs($teller)->getJson('/teller/search-saver?q=NIS123');

        $response->assertStatus(200);
        $this->assertEquals('20260001', $response->json('data.account_number'));
        $this->assertEquals('Siswa Test', $response->json('data.student_name'));
    }

    public function test_web_teller_ajax_deposit()
    {
        $teller = User::where('role', 'Teller')->first();
        $response = $this->actingAs($teller)->postJson('/teller/deposit', [
            'account_number' => '20260001',
            'amount'         => 25000
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
        
        $customer = Saver::where('account_number', '20260001')->first();
        $this->assertEquals(75000, $customer->saldo);
    }

    public function test_web_teller_ajax_withdrawal()
    {
        $teller = User::where('role', 'Teller')->first();
        $response = $this->actingAs($teller)->postJson('/teller/withdrawal', [
            'account_number' => '20260001',
            'amount'         => 15000,
            'security_pin'   => '123456'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true
        ]);
    }
}
