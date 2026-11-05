<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Helpers
function testRoute($kernel, $method, $uri, $data = [], $headers = []) {
    $request = Illuminate\Http\Request::create($uri, $method, $data);
    foreach ($headers as $k => $v) {
        $request->headers->set($k, $v);
    }
    $response = $kernel->handle($request);
    echo "[$method] $uri -> " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() >= 400) {
        echo "ERROR: " . $response->getContent() . "\n";
    }
    return $response;
}

$admin = \App\Models\User::where('role', 'Administrator')->first();
$adminToken = $admin ? $admin->createToken('admin-token')->plainTextToken : '';

$teller = \App\Models\User::where('role', 'Teller')->first();
$tellerToken = $teller ? $teller->createToken('teller-token')->plainTextToken : '';

echo "Admin Token: $adminToken\n";
echo "Teller Token: $tellerToken\n";

testRoute($kernel, 'GET', '/api/search-customer?account_number=123', [], [
    'Accept' => 'application/json',
    'Authorization' => "Bearer $tellerToken"
]);

testRoute($kernel, 'POST', '/api/deposit', [
    'account_number' => '123',
    'amount' => 1000
], [
    'Accept' => 'application/json',
    'Authorization' => "Bearer $tellerToken"
]);

testRoute($kernel, 'POST', '/api/admin/approve-report', [
    'report_id' => 1
], [
    'Accept' => 'application/json',
    'Authorization' => "Bearer $adminToken"
]);

testRoute($kernel, 'GET', '/api/admin/audit-journals', [], [
    'Accept' => 'application/json',
    'Authorization' => "Bearer $adminToken"
]);

testRoute($kernel, 'GET', '/api/nasabah/mutasi', [], [
    'Accept' => 'application/json',
    // Needs customer token
]);
