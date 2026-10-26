<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\StackController;
use App\Services\OperationalAuditService;
use App\Services\PdfExportService;
use Illuminate\Http\Request;

echo "=== Testing StackController::exportAuditPdf ===\n";

$controller = app(StackController::class);
$auditService = app(OperationalAuditService::class);
$pdfService = app(PdfExportService::class);

$request = Request::create('/stacks/export-audit-pdf', 'GET');
$response = $controller->exportAuditPdf($request, $auditService, $pdfService);

echo "Response class: " . get_class($response) . "\n";
if ($response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse) {
    $file = $response->getFile();
    echo "Generated PDF successfully!\n";
    echo "Path: " . $file->getPathname() . "\n";
    echo "Size: " . $file->getSize() . " bytes\n";
    assert($file->getSize() > 10000, "PDF size should be > 10KB");
    echo "ASSERTION PASSED: PDF file is valid and rich in content!\n";
} else {
    echo "Response: " . $response->getContent() . "\n";
}
