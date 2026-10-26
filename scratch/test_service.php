<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\OperationalAuditService;

$service = new OperationalAuditService();
$data = $service->getAuditReportData();

echo "KPIs:\n";
print_r($data['kpis']);

echo "\nTransitions:\n";
foreach ($data['inter_year_transitions'] as $tr) {
    echo "{$tr['from_year']} -> {$tr['to_year']}: {$tr['gap_days']} days gap ({$tr['gap_from']} to {$tr['gap_to']}) [{$tr['status']}]\n";
}

echo "\nYearly Stats sample (2020):\n";
print_r($data['yearly_stats'][2020]);

echo "\nYearly Stats sample (2026):\n";
print_r($data['yearly_stats'][2026]);
