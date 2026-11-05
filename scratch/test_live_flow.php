<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

echo "--- SIMULASI TRANSAKSI & ENDPOINT HTTP LIVE ---\n\n";

// Helper to simulate request
function runRequest($kernel, $method, $uri, $data = [], $headers = []) {
    $req = \Illuminate\Http\Request::create($uri, $method, $data);
    foreach ($headers as $k => $v) {
        $req->headers->set($k, $v);
    }
    $res = $kernel->handle($req);
    $status = $res->getStatusCode();
    echo "[$method] $uri -> Status: $status " . ($status >= 200 && $status < 400 ? "[SUCCESS]" : "[CHECK]") . "\n";
    return $res;
}

// 1. Test Login Page (Web)
runRequest($kernel, 'GET', '/login');

// 2. Test Teller Token & Search Saver (API)
$teller = \App\Models\User::where('role', 'Teller')->first();
if ($teller) {
    $tellerToken = $teller->createToken('test-teller')->plainTextToken;
    echo "Using Teller: {$teller->username} (ID: {$teller->id})\n";
    
    // Search saver
    $res = runRequest($kernel, 'GET', '/api/search-saver?account_number=20260001', [], [
        'Accept' => 'application/json',
        'Authorization' => "Bearer $tellerToken"
    ]);
    $json = json_decode($res->getContent(), true);
    if (!empty($json['data']) || !empty($json['saver']) || !empty($json['account_number'])) {
        echo " - Found Saver Data OK!\n";
    }
}

// 3. Test Mobile App Dashboard (Sanctum)
$sampleSaver = \App\Models\Saver::first();
if ($sampleSaver && $sampleSaver->user_id) {
    $studentUser = \App\Models\User::find($sampleSaver->user_id);
    if ($studentUser) {
        $studentToken = $studentUser->createToken('test-student')->plainTextToken;
        echo "Using Student: {$studentUser->username}\n";
        runRequest($kernel, 'GET', '/api/profile', [], [
            'Accept' => 'application/json',
            'Authorization' => "Bearer $studentToken"
        ]);
    }
}

echo "\n--- SIMULASI SELESAI ---\n";

