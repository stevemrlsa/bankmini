<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Saver;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

class MobileAuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected Saver $saver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');

        $this->saver = Saver::create([
            'account_number'  => '1001002002',
            'student_id'      => 'NIS555',
            'student_name'    => 'Budi Santoso',
            'username'        => 'budisantoso',
            'email'           => 'budi@bankmini.test',
            'grade_class'     => 'XI TKJ 1',
            'gender'          => 'L',
            'status'          => 'Aktif',
            'saldo'           => 500000,
            'password'        => Hash::make('secret123'),
            'security_pin'    => Hash::make('123456'),
        ]);
    }

    public function test_login_successful_with_username_and_password()
    {
        $response = $this->postJson('/api/login', [
            'username' => 'budisantoso',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login berhasil!',
            ])
            ->assertJsonStructure([
                'success',
                'message',
                'token',
                'data' => [
                    'token',
                    'user' => [
                        'id',
                        'name',
                        'username',
                        'account_number',
                    ],
                ]
            ]);

        $this->assertNotEmpty($response->json('token'));
        $this->assertEquals('Budi Santoso', $response->json('data.user.name'));
        $this->assertEquals('1001002002', $response->json('data.user.account_number'));
    }

    public function test_login_successful_with_account_number_and_password()
    {
        $response = $this->postJson('/api/login', [
            'account_number' => '1001002002',
            'password'       => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login berhasil!',
            ]);
        $this->assertNotEmpty($response->json('token'));
    }

    public function test_login_fails_with_invalid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'username' => 'budisantoso',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Kredensial tidak valid',
            ]);
    }

    public function test_login_fails_with_non_existent_user()
    {
        $response = $this->postJson('/api/login', [
            'username' => 'user_tidak_ada',
            'password' => 'secret123',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Kredensial tidak valid',
            ]);
    }

    public function test_logout_successful_revokes_token()
    {
        // Login untuk dapat token
        $loginResponse = $this->postJson('/api/login', [
            'username' => 'budisantoso',
            'password' => 'secret123',
        ]);
        $token = $loginResponse->json('token');

        // Logout membawa Bearer Token
        $logoutResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');

        $logoutResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Berhasil logout',
            ]);

        // Verifikasi token telah terhapus dari tabel personal_access_tokens
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
