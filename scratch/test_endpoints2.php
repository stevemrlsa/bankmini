<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

function testRoute($kernel, $method, $uri, $data = [], $headers = []) {
    $request = Illuminate\Http\Request::create($uri, $method, $data);
    foreach ($headers as $k => $v) {
        $request->headers->set($k, $v);
    }
    $response = $kernel->handle($request);
    echo "[$method] $uri -> " . $response->getStatusCode() . "\n";
    if ($response->getStatusCode() >= 400) {
        echo "RESPONSE BODY: " . $response->getContent() . "\n";
    }
    return $response;
}

$teller = \App\Models\User::where('role', 'Teller')->first();
$tellerToken = $teller ? $teller->createToken('teller-token')->plainTextToken : '';

testRoute($kernel, 'GET', '/api/search-customer?q=123', [], [
    'Accept' => 'application/json',
    'Authorization' => "Bearer $tellerToken"
]);

testRoute($kernel, 'GET', '/api/customer', [], [
    'Accept' => 'application/json',
    'Authorization' => "Bearer $tellerToken"
]);
