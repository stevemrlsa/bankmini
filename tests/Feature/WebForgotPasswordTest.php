<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class WebForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();

        $this->user = User::create([
            'username'        => 'pegawai_test',
            'email'           => 'pegawai@bankmini.test',
            'whatsapp_number' => '081234567890',
            'password'        => Hash::make('oldpassword123'),
            'officer_name'    => 'Pegawai Test',
            'role'            => 'Teller',
            'status'          => 'Aktif',
        ]);
    }

    public function test_forgot_password_full_flow()
    {
        // 1. Tampilkan form email
        $response = $this->get(route('password.request'));
        $response->assertStatus(200);
        $response->assertSee('Lupa Kata Sandi?');

        // 2. Kirim OTP via email
        $postEmail = $this->post(route('password.email'), [
            'email' => 'pegawai@bankmini.test',
        ]);
        $postEmail->assertRedirect(route('password.otp.form'));
        $this->assertTrue(session()->has('reset_otp'));
        $otp = session('reset_otp');

        // 3. Tampilkan form OTP
        $responseOtp = $this->get(route('password.otp.form'));
        $responseOtp->assertStatus(200);
        $responseOtp->assertSee('Verifikasi OTP');

        // 4. Verifikasi OTP
        $postOtp = $this->post(route('password.otp.verify'), [
            'otp' => $otp,
        ]);
        $postOtp->assertRedirect(route('password.reset.form'));
        $this->assertTrue(session('otp_verified'));

        // 5. Tampilkan form Reset Password
        $responseReset = $this->get(route('password.reset.form'));
        $responseReset->assertStatus(200);
        $responseReset->assertSee('Buat Sandi Baru');

        // 6. Simpan password baru
        $postReset = $this->post(route('password.reset.submit'), [
            'password'              => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);
        $postReset->assertRedirect(route('login'));
        $this->assertTrue(Hash::check('newpassword123', $this->user->fresh()->password));
    }
}
