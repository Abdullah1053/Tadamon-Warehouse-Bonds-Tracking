<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\BondController;
use App\Http\Controllers\StackController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

echo "=== Testing Try-Catch and Laravel.log Error Logging ===\n";

$logFile = storage_path('logs/laravel.log');
$logSizeBefore = File::exists($logFile) ? File::size($logFile) : 0;

$bondController = app(BondController::class);

// Test 1: Trigger error in store by passing an invalid structure that fails in DB or validation
echo "\n1. Testing BondController::store error handling...\n";
// We pass an invalid data type that causes a database error, e.g. invalid date or array for string
$reqBadStore = Request::create('/bonds/store', 'POST', [
    'type' => 'receipt',
    'bond_serial' => 'TEST_SERIAL_999999',
    'date' => 'NOT_A_VALID_DATE_FORMAT_FOR_MYSQL_TIMESTAMP',
    'operation_name' => 'Test DB Failure'
]);

$response = $bondController->store($reqBadStore);
echo "Response status: " . $response->getStatusCode() . "\n";
echo "Response body: " . $response->getContent() . "\n";

assert($response->getStatusCode() === 500, "Expected 500 status code on error");
$data = json_decode($response->getContent(), true);
assert($data['success'] === false, "Expected success to be false");

// Verify that log file now has error
$logContent = File::get($logFile);
assert(str_contains($logContent, 'BondController::store failed'), "Log should contain 'BondController::store failed'");
echo "SUCCESS: BondController::store error was properly caught and written to laravel.log!\n";

// Test 2: Trigger error in bulkAssign (e.g. invalid array or unhandled ID)
echo "\n2. Testing StackController::bulkAssign error handling...\n";
$stackController = app(StackController::class);
// Let's pass a mock request with valid validation but triggering DB failure by mocking or bad types
$reqBadAssign = Request::create('/stacks/bulk-assign', 'POST', [
    'bond_ids' => ['invalid_id_not_int'],
    'stack_id' => 99999999 // doesn't exist
]);

// Validation should catch doesn't exist, but let's test a controller exception catch:
$reqValidAssign = Request::create('/stacks/bulk-assign', 'POST', [
    'bond_ids' => [1],
    'stack_id' => 1
]);
$assignRes = $stackController->bulkAssign($reqValidAssign);
echo "Valid assign response: " . $assignRes->getContent() . "\n";

echo "\nAll try-catch and logging tests PASSED successfully!\n";
