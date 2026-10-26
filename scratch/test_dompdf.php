<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Barryvdh\DomPDF\Facade\Pdf;

$pdf = Pdf::loadHTML('<h1>Test PDF</h1><p>Testing dompdf facade.</p>');
$output = $pdf->output();
file_put_contents(__DIR__ . '/test_dompdf.pdf', $output);
echo "DomPDF success! Size: " . strlen($output) . " bytes\n";
