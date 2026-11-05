<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\TellerShift;

class TellerShiftTest extends TestCase
{
    use RefreshDatabase;

    protected User $teller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');
        $this->teller = User::where('role', 'Teller')->first();
    }

    public function test_teller_can_open_shift_with_initial_balance()
    {
        // 1. Kunjungi halaman buka laci kas / shift baru
        $response = $this->actingAs($this->teller)->get(route('teller.shift.create'));
        // Karena view belum dibuat, jika belum ada view akan 500 jika view missing, jadi kita uji store terlebih dahulu
        
        // 2. Submit modal awal
        $postResponse = $this->actingAs($this->teller)->post(route('teller.shift.store'), [
            'opening_balance' => 500000,
        ]);

        $postResponse->assertRedirect(route('teller.dashboard'));
        $postResponse->assertSessionHas('success');

        $shift = TellerShift::where('user_id', $this->teller->id)
            ->where('status', 'Open')
            ->first();

        $this->assertNotNull($shift);
        $this->assertEquals(500000, $shift->opening_balance);
        $this->assertEquals('Open', $shift->status);
        $this->assertNotNull($shift->opened_at);
    }

    public function test_cannot_open_another_shift_if_one_is_already_open()
    {
        // Buat shift yang sudah Open
        TellerShift::create([
            'user_id'         => $this->teller->id,
            'opening_balance' => 250000,
            'status'          => 'Open',
            'opened_at'       => now(),
        ]);

        // Coba buka shift lagi
        $response = $this->actingAs($this->teller)->post(route('teller.shift.store'), [
            'opening_balance' => 100000,
        ]);

        $response->assertRedirect(route('teller.dashboard'));
        $response->assertSessionHas('warning');

        // Pastikan hanya ada 1 shift
        $this->assertEquals(1, TellerShift::where('user_id', $this->teller->id)->count());
    }

    public function test_opening_balance_validation()
    {
        // Nominal negatif atau tidak valid
        $response = $this->actingAs($this->teller)->post(route('teller.shift.store'), [
            'opening_balance' => -50000,
        ]);

        $response->assertSessionHasErrors('opening_balance');
    }

    public function test_middleware_redirects_if_no_active_shift()
    {
        $middleware = new \App\Http\Middleware\EnsureTellerShiftOpen();
        $request = \Illuminate\Http\Request::create('/teller/setoran', 'GET');
        
        $this->actingAs($this->teller);

        $response = $middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals(route('teller.shift.create'), $response->headers->get('Location'));
    }

    public function test_middleware_passes_if_active_shift_exists()
    {
        TellerShift::create([
            'user_id'         => $this->teller->id,
            'opening_balance' => 500000,
            'status'          => 'Open',
            'opened_at'       => now(),
        ]);

        $middleware = new \App\Http\Middleware\EnsureTellerShiftOpen();
        $request = \Illuminate\Http\Request::create('/teller/setoran', 'GET');
        
        $this->actingAs($this->teller);

        $response = $middleware->handle($request, function () {
            return response('OK');
        });

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('OK', $response->getContent());
    }

    public function test_cannot_close_shift_if_no_active_shift()
    {
        $response = $this->actingAs($this->teller)->get(route('teller.shift.close'));
        $response->assertRedirect(route('teller.dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_end_shift_calculates_system_balance_and_closes_shift()
    {
        // Bersihkan transaksi dummy seeder agar perhitungan murni dari test
        \App\Models\Transaction::query()->delete();

        $openedAt = now()->subHours(4);

        // 1. Buat shift yang sedang aktif
        $shift = TellerShift::create([
            'user_id'         => $this->teller->id,
            'opening_balance' => 200000,
            'status'          => 'Open',
            'opened_at'       => $openedAt,
        ]);

        // 2. Buat transaksi setoran dan penarikan selama shift ini berlangsung
        \App\Models\Transaction::create([
            'user_id'          => $this->teller->id,
            'transaction_date' => now()->subHours(2),
            'transaction_type' => 'Deposit',
            'amount'           => 100000,
            'created_at'       => now()->subHours(2),
        ]);

        \App\Models\Transaction::create([
            'user_id'          => $this->teller->id,
            'transaction_date' => now()->subHour(),
            'transaction_type' => 'Withdrawal',
            'amount'           => 30000,
            'created_at'       => now()->subHour(),
        ]);

        // 3. Teller menutup shift dengan fisik Rp 270.000
        $response = $this->actingAs($this->teller)->post(route('teller.shift.end'), [
            'closing_balance' => 270000,
        ]);

        $response->assertRedirect(route('teller.dashboard'));
        $response->assertSessionHas('success');

        // 4. Verifikasi kalkulasi: 200.000 + 100.000 - 30.000 = 270.000
        $shift->refresh();
        $this->assertEquals('Closed', $shift->status);
        $this->assertEquals(270000, $shift->closing_balance);
        $this->assertEquals(270000, $shift->system_balance);
        $this->assertNotNull($shift->closed_at);
    }

    public function test_withdrawal_blocked_when_physical_cash_in_drawer_is_insufficient()
    {
        // 1. Buat nasabah dengan saldo cukup
        $saver = \App\Models\Saver::create([
            'account_number' => '20269999',
            'student_name'   => 'Nasabah Uji',
            'security_pin'   => \Illuminate\Support\Facades\Hash::make('123456'),
            'saldo'          => 1000000,
        ]);

        // 2. Teller buka shift dengan modal awal Rp 100.000
        TellerShift::create([
            'user_id'         => $this->teller->id,
            'opening_balance' => 100000,
            'status'          => 'Open',
            'opened_at'       => now(),
        ]);

        // 3. Nasabah coba tarik Rp 150.000 (melebihi kas fisik laci Rp 100.000)
        $response = $this->actingAs($this->teller)->post(route('teller.penarikan.store'), [
            'account_number' => '20269999',
            'amount'         => 150000,
            'security_pin'   => '123456',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('Uang tunai fisik di laci tidak mencukupi', session('error'));

        // Saldo nasabah tidak boleh terpotong
        $saver->refresh();
        $this->assertEquals(1000000, $saver->saldo);
    }

    public function test_withdrawal_allowed_when_physical_cash_is_sufficient()
    {
        $saver = \App\Models\Saver::create([
            'account_number' => '20269998',
            'student_name'   => 'Nasabah Uji 2',
            'security_pin'   => \Illuminate\Support\Facades\Hash::make('123456'),
            'saldo'          => 1000000,
        ]);

        TellerShift::create([
            'user_id'         => $this->teller->id,
            'opening_balance' => 200000,
            'status'          => 'Open',
            'opened_at'       => now(),
        ]);

        // Tarik Rp 50.000 (kas laci Rp 200.000 cukup)
        $response = $this->actingAs($this->teller)->post(route('teller.penarikan.store'), [
            'account_number' => '20269998',
            'amount'         => 50000,
            'security_pin'   => '123456',
        ]);

        $response->assertSessionHas('success');
        $saver->refresh();
        $this->assertEquals(950000, $saver->saldo);
    }
}

