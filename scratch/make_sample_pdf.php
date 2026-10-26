<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\OperationalAuditService;
use App\Services\PdfExportService;

$audit = app(OperationalAuditService::class);
$pdf = app(PdfExportService::class);

$html = view('reports.operational_audit_pdf', $audit->getAuditReportData())->render();
$path = $pdf->generatePdfFromHtml($html);
$dest = __DIR__ . '/audit_report_sample.pdf';
copy($path, $dest);
echo "Copied to " . $dest . ", size: " . filesize($dest) . " bytes\n";
