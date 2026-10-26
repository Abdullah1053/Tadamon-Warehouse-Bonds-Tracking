<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Bond;

$bonds = Bond::orderBy('date', 'asc')->get(['id', 'bond_serial', 'date', 'operation_name']);
echo "Total bonds: " . $bonds->count() . "\n";

$byYear = [];
foreach ($bonds as $b) {
    $y = !empty($b->date) ? substr(trim($b->date), 0, 4) : 'Unknown';
    $byYear[$y][] = $b;
}

foreach ($byYear as $y => $list) {
    $dates = array_unique(array_filter(array_map(fn($b) => trim($b->date), $list)));
    sort($dates);
    $min = reset($dates);
    $max = end($dates);
    echo "Year {$y}: " . count($list) . " bonds, " . count($dates) . " unique active days. Min: {$min}, Max: {$max}\n";
}
