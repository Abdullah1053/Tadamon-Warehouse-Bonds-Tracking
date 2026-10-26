<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Bond;
use Carbon\Carbon;

$bonds = Bond::with(['items'])->orderBy('date', 'asc')->orderBy('bond_serial', 'asc')->get();

$bondsByYear = [];
foreach ($bonds as $b) {
    if (empty($b->date)) continue;
    $y = substr(trim($b->date), 0, 4);
    $bondsByYear[$y][] = $b;
}

echo "Years with bonds in DB: " . implode(', ', array_keys($bondsByYear)) . "\n";

// Let's check each year
$historicalIntervals = [
    2019 => [
        ['start' => '2019-09-01', 'end' => '2019-12-31', 'note' => 'فترة التشغيل الأولية لعام 2019']
    ],
    2020 => [
        ['start' => '2020-01-01', 'end' => '2020-04-19', 'note' => 'فترة التشغيل الأولى لعام 2020'],
        ['start' => '2020-07-27', 'end' => '2020-12-31', 'note' => 'فترة التشغيل الثانية لعام 2020']
    ],
    2021 => [
        ['start' => '2021-01-01', 'end' => '2021-08-21', 'note' => 'فترة التشغيل لعام 2021']
    ],
    2022 => [
        ['start' => '2022-05-01', 'end' => '2022-09-04', 'note' => 'فترة التشغيل الأولى لعام 2022'],
        ['start' => '2022-12-05', 'end' => '2022-12-31', 'note' => 'فترة التشغيل الثانية لعام 2022']
    ],
    2023 => [
        ['start' => '2023-01-01', 'end' => '2023-08-20', 'note' => 'فترة التشغيل لعام 2023']
    ]
];

for ($y = 2019; $y <= 2026; $y++) {
    $yBonds = collect($bondsByYear[(string)$y] ?? []);
    $dates = $yBonds->pluck('date')->unique()->sort()->values()->all();
    $calDays = Carbon::create($y, 1, 1)->isLeapYear() ? 366 : 365;
    echo "\n=== Year {$y} (Total Calendar Days: {$calDays}) ===\n";
    echo "Total Bonds in DB: " . count($yBonds) . "\n";
    echo "Unique Active Work Dates with Recorded Bonds: " . count($dates) . "\n";
    if (!empty($dates)) {
        echo "First DB Bond Date: " . reset($dates) . "\n";
        echo "Last DB Bond Date: " . end($dates) . "\n";
    }
}
