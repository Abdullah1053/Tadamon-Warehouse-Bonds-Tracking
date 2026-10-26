<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Bond;
use Carbon\Carbon;

$bonds = Bond::with('items')->orderBy('date', 'asc')->orderBy('bond_serial', 'asc')->get();

// Let's analyze all gaps and stats
$stats = [];
for ($year = 2019; $year <= 2026; $year++) {
    $yBonds = $bonds->filter(fn($b) => !empty($b->date) && str_starts_with(trim($b->date), (string)$year));
    $dates = $yBonds->pluck('date')->unique()->sort()->values();
    
    $calDays = Carbon::create($year, 1, 1)->isLeapYear() ? 366 : 365;
    $activeCount = $dates->count();
    $bondsCount = $yBonds->count();
    $missingBonds = $yBonds->filter(fn($b) => $b->isMissing())->count();
    $cancelledBonds = $yBonds->filter(fn($b) => $b->isCancelled())->count();
    $normalBonds = $bondsCount - $missingBonds - $cancelledBonds;
    
    // Find gaps between recorded dates in this year
    $intraGaps = [];
    for ($i = 0; $i < count($dates) - 1; $i++) {
        $curr = Carbon::parse($dates[$i]);
        $next = Carbon::parse($dates[$i + 1]);
        $diff = $curr->diffInDays($next);
        if ($diff > 1) {
            $gapStart = $curr->copy()->addDay()->format('Y-m-d');
            $gapEnd = $next->copy()->subDay()->format('Y-m-d');
            $gapDays = $diff - 1;
            $intraGaps[] = [
                'from' => $gapStart,
                'to' => $gapEnd,
                'days' => $gapDays,
                'prior_date' => $dates[$i],
                'next_date' => $dates[$i + 1]
            ];
        }
    }
    
    $stats[$year] = [
        'year' => $year,
        'cal_days' => $calDays,
        'active_days' => $activeCount,
        'idle_days' => $calDays - $activeCount,
        'bonds_count' => $bondsCount,
        'missing_bonds' => $missingBonds,
        'cancelled_bonds' => $cancelledBonds,
        'normal_bonds' => $normalBonds,
        'min_date' => $dates->first() ?? null,
        'max_date' => $dates->last() ?? null,
        'intra_gaps' => $intraGaps
    ];
}

echo json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
