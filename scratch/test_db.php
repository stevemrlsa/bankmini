<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing DB connection...\n";
try {
    \DB::connection()->getPdo();
    echo "DB Connection SUCCESS: " . \DB::connection()->getDatabaseName() . "\n";
} catch (\Exception $e) {
    echo "DB Connection FAILED: " . $e->getMessage() . "\n";
}

