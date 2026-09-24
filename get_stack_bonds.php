<?php
/**
 * Helper to export stack metadata and its ordered bonds as JSON for renaming scripts.
 */
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Stack;

$identifier = $argv[1] ?? '';
if ($identifier === '') {
    echo json_encode(['error' => 'No stack identifier provided']);
    exit(1);
}

$stack = null;
if (is_numeric($identifier)) {
    $stack = Stack::find((int)$identifier);
}
if (!$stack) {
    $stack = Stack::where('stack_name', $identifier)->first();
}

if (!$stack) {
    echo json_encode(['error' => "Stack not found: $identifier"]);
    exit(1);
}

$bonds = $stack->bonds()->orderBy('id')->get()->map(function($b) {
    return [
        'id' => $b->id,
        'serial' => is_numeric($b->bond_serial) ? (int)$b->bond_serial : $b->bond_serial,
        'op' => $b->operation_name,
        'recv' => $b->received_from,
        'note' => $b->note,
        'is_missing' => (int)$b->is_missing,
    ];
});

echo json_encode([
    'stack' => [
        'id' => $stack->id,
        'name' => $stack->stack_name,
        'start_serial' => (int)$stack->start_serial,
        'end_serial' => (int)$stack->end_serial,
    ],
    'bonds' => $bonds
], JSON_UNESCAPED_UNICODE);
