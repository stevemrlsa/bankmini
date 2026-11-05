<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Saver;
use App\Models\User;
use App\Models\Transaction;
use Laravel\Sanctum\Sanctum;

class MobileDashboardApiTest extends TestCase
{
    use RefreshDatabase;

    protected Saver $saver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed');

        $this->saver = Saver::create([
            'account_number'  => '1001001001',
            'student_id'      => '12345',
            'student_name'    => 'Ahmad Fauzi',
            'grade_class'     => 'XII RPL 1',
            'gender'          => 'L',
            'status'          => 'Aktif',
            'saldo'           => 1500000,
            'security_pin'    => bcrypt('123456'),
        ]);
    }

    public function test_unauthenticated_user_cannot_access_dashboard()
    {
        $response = $this->getJson('/api/dashboard');
        $response->assertStatus(401);
    }

    public function test_authenticated_saver_can_fetch_dashboard_data()
    {
        $teller = User::where('role', 'Teller')->first();

        // Buat beberapa transaksi (Deposit dan Withdrawal)
        Transaction::create([
            'saver_id'         => $this->saver->id,
            'user_id'          => $teller ? $teller->id : 1,
            'transaction_date' => now()->subDays(2),
            'transaction_type' => 'Deposit',
            'amount'           => 1000000,
            'status'           => 'Approved',
        ]);

        Transaction::create([
            'saver_id'         => $this->saver->id,
            'user_id'          => $teller ? $teller->id : 1,
            'transaction_date' => now()->subDay(),
            'transaction_type' => 'Withdrawal',
            'amount'           => 200000,
            'status'           => 'Approved',
        ]);

        Transaction::create([
            'saver_id'         => $this->saver->id,
            'user_id'          => $teller ? $teller->id : 1,
            'transaction_date' => now(),
            'transaction_type' => 'Deposit',
            'amount'           => 700000,
            'status'           => 'Approved',
        ]);

        Sanctum::actingAs($this->saver);

        $response = $this->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'name',
                        'account_number',
                    ],
                    'balance',
                    'recent_transactions' => [
                        '*' => [
                            'id',
                            'transaction_type',
                            'type',
                            'type_label',
                            'amount',
                            'transaction_date',
                            'status',
                        ]
                    ],
                ]
            ]);

        $responseData = $response->json('data');
        $this->assertEquals('Ahmad Fauzi', $responseData['user']['name']);
        $this->assertEquals('1001001001', $responseData['user']['account_number']);
        $this->assertEquals(1500000, $responseData['balance']);
        $this->assertCount(3, $responseData['recent_transactions']);

        // Transaksi terbaru harus yang paling atas
        $firstTx = $responseData['recent_transactions'][0];
        $this->assertEquals('Deposit', $firstTx['type']);
        $this->assertEquals('Setoran Tunai', $firstTx['type_label']);
        $this->assertEquals(700000, $firstTx['amount']);

        $secondTx = $responseData['recent_transactions'][1];
        $this->assertEquals('Withdrawal', $secondTx['type']);
        $this->assertEquals('Penarikan', $secondTx['type_label']);
        $this->assertEquals(200000, $secondTx['amount']);
    }
}
