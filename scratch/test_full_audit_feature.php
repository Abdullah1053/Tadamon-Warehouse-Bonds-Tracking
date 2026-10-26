<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\StackController;
use App\Services\OperationalAuditService;
use App\Services\PdfExportService;
use Illuminate\Http\Request;

echo "========================================================\n";
echo "   RUNNING OPERATIONAL AUDIT REPORT COMPREHENSIVE TESTS\n";
echo "========================================================\n\n";

$controller = app(StackController::class);
$auditService = app(OperationalAuditService::class);
$pdfService = app(PdfExportService::class);

// TEST 1: Service Data Integrity
echo "1. Testing OperationalAuditService::getAuditReportData()...\n";
$data = $auditService->getAuditReportData();

assert(!empty($data['kpis']), "KPIs must not be empty");
assert($data['kpis']['total_bonds'] === 2632, "Total bonds count should match DB exactly (2632)");
assert($data['kpis']['total_calendar_days'] === 2922, "Total calendar days for 2019-2026 must be 2922");
assert($data['kpis']['total_active_days'] === 582, "Total active work days should be 582");
assert(count($data['yearly_stats']) === 8, "Yearly stats must contain 8 years (2019-2026)");
assert(count($data['inter_year_transitions']) === 7, "Inter-year transitions must contain 7 transitions");
echo "   -> PASS: KPIs, yearly stats, and inter-year transitions verified successfully!\n";

// TEST 2: HTML View Render Test
echo "\n2. Testing Blade View Render ('reports.operational_audit_pdf')...\n";
$html = view('reports.operational_audit_pdf', $data)->render();
assert(strlen($html) > 10000, "HTML output should be > 10KB");
assert(str_contains($html, 'تقرير التدقيق الرقابي والتحليل الزمني'), "HTML should contain main title");
assert(str_contains($html, 'الملخص التنفيذي'), "HTML should contain executive summary");
assert(str_contains($html, 'الإحصائيات البصرية والرسوم البيانية'), "HTML should contain visual charts section");
assert(str_contains($html, 'أيام العمل الفعلية مقابل أيام التوقف'), "HTML should contain chart 1");
assert(str_contains($html, 'توزيع كثافة السندات المسجلة عبر الأعوام'), "HTML should contain chart 2");
assert(str_contains($html, 'تحليل الانتقال التشغيلي وفجوات التوقف بين الأعوام'), "HTML should contain inter-year section");
assert(str_contains($html, '252 يوماً'), "HTML should mention the critical 252-day pause in 2021-2022");
echo "   -> PASS: Blade template rendered successfully (" . strlen($html) . " bytes)!\n";

// TEST 3: Controller previewAuditReport
echo "\n3. Testing StackController::previewAuditReport...\n";
$previewView = $controller->previewAuditReport($auditService);
assert($previewView instanceof \Illuminate\View\View, "Preview response must be a Blade View instance");
echo "   -> PASS: Preview action returned valid View instance!\n";

// TEST 4: Controller exportAuditPdf with Primary Headless Engine
echo "\n4. Testing StackController::exportAuditPdf (Primary Engine)...\n";
$req = Request::create('/stacks/export-audit-pdf', 'GET');
$resp = $controller->exportAuditPdf($req, $auditService, $pdfService);
assert($resp instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse, "Response must be BinaryFileResponse");
assert($resp->headers->get('Content-Type') === 'application/pdf', "Content-Type must be application/pdf");
$pdfFile = $resp->getFile();
assert($pdfFile->getSize() > 20000, "PDF size must be substantial (>20KB)");
echo "   -> PASS: Primary PDF generated successfully (" . number_format($pdfFile->getSize()) . " bytes)!\n";

// TEST 5: DomPDF Fallback Engine
echo "\n5. Testing DomPDF Fallback Engine...\n";
$domPdfPath = $pdfService->generatePdfFromHtml($html, 'dompdf');
assert(file_exists($domPdfPath), "DomPDF output file must exist");
$domPdfSize = filesize($domPdfPath);
assert($domPdfSize > 5000, "DomPDF size must be > 5KB");
@unlink($domPdfPath);
echo "   -> PASS: DomPDF fallback engine verified successfully (" . number_format($domPdfSize) . " bytes)!\n";

echo "\n========================================================\n";
echo "   ALL OPERATIONAL AUDIT FEATURE TESTS PASSED 100%!\n";
echo "========================================================\n";
