<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Bond;
use App\Models\BondItem;
use Illuminate\Http\Request;
use App\Http\Controllers\BondController;

echo "=== Testing Disbursement Bond Feature ===\n";

$controller = app(BondController::class);

// Test 1: Receipt create endpoint
$reqReceipt = Request::create('/bonds/create', 'GET', ['type' => 'receipt']);
$viewReceipt = $controller->create($reqReceipt);
$dataReceipt = $viewReceipt->getData();
echo "Receipt type: " . $dataReceipt['type'] . "\n";
echo "Receipt next serial: " . $dataReceipt['nextSerial'] . "\n";
assert($dataReceipt['type'] === 'receipt');

// Test 2: Disbursement create endpoint
$reqDisb = Request::create('/bonds/create', 'GET', ['type' => 'disbursement']);
$viewDisb = $controller->create($reqDisb);
$dataDisb = $viewDisb->getData();
echo "Disbursement type: " . $dataDisb['type'] . "\n";
echo "Disbursement next serial: " . $dataDisb['nextSerial'] . "\n";
assert($dataDisb['type'] === 'disbursement');
assert($dataDisb['nextSerial'] === '0001');

// Test 3: Storing a disbursement bond
echo "\n--- Saving Disbursement Bond #0001 ---\n";
$reqStore = Request::create('/bonds/store', 'POST', [
    'type' => 'disbursement',
    'bond_serial' => '0001',
    'date' => '2026-09-25',
    'operation_name' => 'مشروع تعبيد طريق صنعاء',
    'received_from' => 'المهندس أحمد صالح',
    'car_number' => '77211/4',
    'note' => 'رقم إذن الصرف: 4501',
    'bond_type' => 'normal',
    'is_missing' => 0,
    'items' => [
        ['description' => 'أكياس إسمنت بورتلاندي', 'quantity' => 50],
        ['description' => 'حديد تسليح 12 ملم', 'quantity' => 20]
    ]
]);

$responseStore = $controller->store($reqStore);
$content = json_decode($responseStore->getContent(), true);
echo "Store response: " . json_encode($content, JSON_UNESCAPED_UNICODE) . "\n";
assert($content['success'] === true);
assert($content['type'] === 'disbursement');
assert($content['next_serial'] === '0002');

$savedBond = Bond::where('type', 'disbursement')->where('bond_serial', '0001')->with('items')->first();
assert($savedBond !== null);
echo "Saved Bond ID: " . $savedBond->id . " | Type: " . $savedBond->type . " | Label: " . $savedBond->type_label . "\n";
echo "Items count: " . $savedBond->items->count() . "\n";
assert($savedBond->isDisbursement() === true);
assert($savedBond->isReceipt() === false);
assert($savedBond->items->count() === 2);

// Test 4: Verify next serial for disbursement now increments to 0002
$viewDisb2 = $controller->create($reqDisb);
$dataDisb2 = $viewDisb2->getData();
echo "Disbursement next serial after 1st bond: " . $dataDisb2['nextSerial'] . "\n";
assert($dataDisb2['nextSerial'] === '0002');

// Test 5: Verify Cancelled Disbursement Bond
echo "\n--- Saving Cancelled Disbursement Bond #0002 ---\n";
$reqCancel = Request::create('/bonds/store', 'POST', [
    'type' => 'disbursement',
    'bond_serial' => '0002',
    'date' => '2026-09-25',
    'operation_name' => 'ملغي',
    'received_from' => 'ملغي',
    'note' => 'ملغي',
    'bond_type' => 'cancelled',
    'is_missing' => 0
]);
$resCancel = $controller->store($reqCancel);
$contentCancel = json_decode($resCancel->getContent(), true);
echo "Cancelled store response: " . json_encode($contentCancel, JSON_UNESCAPED_UNICODE) . "\n";
assert($contentCancel['next_serial'] === '0003');

$cancelledBond = Bond::where('type', 'disbursement')->where('bond_serial', '0002')->first();
assert($cancelledBond->isCancelled() === true);
assert($cancelledBond->items()->count() === 0);
echo "Cancelled Bond verified successfully.\n";

// Test 6: Verify Missing Disbursement Bond
echo "\n--- Saving Missing Disbursement Bond #0003 ---\n";
$reqMissing = Request::create('/bonds/store', 'POST', [
    'type' => 'disbursement',
    'bond_serial' => '0003',
    'date' => '2026-09-25',
    'operation_name' => 'مفقود',
    'received_from' => 'مفقود',
    'note' => 'مفقود',
    'bond_type' => 'missing',
    'is_missing' => 1
]);
$resMissing = $controller->store($reqMissing);
$contentMissing = json_decode($resMissing->getContent(), true);
echo "Missing store response: " . json_encode($contentMissing, JSON_UNESCAPED_UNICODE) . "\n";
assert($contentMissing['next_serial'] === '0004');

$missingBond = Bond::where('type', 'disbursement')->where('bond_serial', '0003')->first();
assert($missingBond->isMissing() === true);
echo "Missing Bond verified successfully.\n";

// Test 7: Verify Composite Unique Index (both receipt and disbursement can have serial 0001)
echo "\n--- Testing Composite Unique Index on (type, bond_serial) ---\n";
$receiptBondSameSerial = Bond::create([
    'type' => 'receipt',
    'bond_serial' => '0001',
    'date' => '2026-09-25',
    'operation_name' => 'فحص تكرار الرقم',
    'received_from' => 'شركة التضامن للتوريدات',
    'is_missing' => 0
]);
echo "Successfully created Receipt Bond with serial '0001' (same serial as Disbursement bond #0001)!\n";
assert($receiptBondSameSerial->id > 0);

// Cleanup test records
echo "\nCleaning up test records...\n";
$savedBond->items()->delete();
$savedBond->delete();
$cancelledBond->delete();
$missingBond->delete();
$receiptBondSameSerial->delete();
echo "Cleanup complete. All tests PASSED successfully!\n";
