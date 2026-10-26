<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\StackExport;
use App\Exports\AllStacksExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Stack;
use App\Models\Bond;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use ZipArchive;
use App\Services\OperationalAuditService;
use App\Services\PdfExportService;


class StackController extends Controller
{

    public function index() {
        $stacks = Stack::withCount([
            'bonds',
            'bonds as cancelled_count' => function ($query) {
                $query->where(function ($q) {
                    $q->where('received_from', 'ملغي')
                      ->orWhere('operation_name', 'ملغي')
                      ->orWhere('note', 'ملغي');
                });
            },
            'bonds as missing_count' => function ($query) {
                $query->where(function ($q) {
                    $q->where('is_missing', 1)
                      ->orWhere('received_from', 'مفقود')
                      ->orWhere('operation_name', 'مفقود');
                });
            }
        ])->latest()->get();

        return view('dashboard', [
            'stacks' => $stacks,
            'pendingBonds' => Bond::whereNull('stack_id')->with('items')->get()
        ]);
    }

    public function show(Stack $stack)
    {
        // Load bonds ordered by bond_serial ascending (using numeric sort when possible)
        $bonds = $stack->bonds()
            ->with('items')
            ->orderByRaw('CAST(bond_serial AS UNSIGNED) ASC')
            ->orderBy('bond_serial', 'asc')
            ->get();

        // Calculate statistics
        $totalCount = $bonds->count();
        $cancelledCount = $bonds->filter(fn($b) => $b->isCancelled())->count();
        $missingCount = $bonds->filter(fn($b) => $b->isMissing())->count();
        $normalCount = $totalCount - $cancelledCount - $missingCount;

        // Fetch other stacks for the move modal / dropdown
        $otherStacks = Stack::where('id', '!=', $stack->id)
            ->orderBy('stack_name', 'asc')
            ->get();

        return view('stacks.show', [
            'stack' => $stack,
            'bonds' => $bonds,
            'otherStacks' => $otherStacks,
            'stats' => [
                'total' => $totalCount,
                'normal' => $normalCount,
                'cancelled' => $cancelledCount,
                'missing' => $missingCount,
            ]
        ]);
    }


    public function createStack(Request $request)
    {
        $request->validate([
            'stack_name'   => 'required|string|max:255',
            'start'        => 'required|numeric',
            'end'          => 'required|numeric',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $stack = Stack::create([
                    'stack_name'   => $request->stack_name,
                    'start_serial' => $request->start,
                    'end_serial'   => $request->end,
                ]);

                Bond::whereBetween('bond_serial', [$request->start, $request->end])
                    ->update(['stack_id' => $stack->id]);

                return back()->with('success', 'تم إنشاء الدفتر وربط السندات بنجاح.');
            });
        } catch (\Throwable $e) {
            Log::error('StackController::createStack failed: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'حدث خطأ أثناء إنشاء الدفتر: ' . $e->getMessage());
        }
    }



    public function bulkAssign(Request $request)
    {
        $request->validate([
            'bond_ids' => 'required|array',
            'stack_id' => 'required|exists:stacks,id'
        ]);

        try {
            Bond::whereIn('id', $request->bond_ids)->update(['stack_id' => $request->stack_id]);
            return response()->json([
                'success' => true,
                'message' => 'تم ربط السندات المحددة بالدفتر بنجاح.'
            ]);
        } catch (\Throwable $e) {
            Log::error('StackController::bulkAssign failed: ' . $e->getMessage(), [
                'bond_ids' => $request->bond_ids,
                'stack_id' => $request->stack_id,
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تخصيص السندات للدفتر: ' . $e->getMessage()
            ], 500);
        }
    }

    public function export(Stack $stack) 
    {
        // The filename includes the stack name for easy office filing
        $fileName = 'Report_' . str_replace(' ', '_', $stack->stack_name) . '.xlsx';
        return Excel::download(new StackExport($stack), $fileName);
    }

    public function exportAll()
    {
        set_time_limit(300);
        $stacks = Stack::all();

        if ($stacks->isEmpty()) {
            return back()->with('error', 'No stacks available to export.');
        }

        $zip = new ZipArchive();
        $zipFileName = 'All_Stacks_Reports_' . date('Y-m-d_His') . '.zip';
        $tempZipPath = tempnam(sys_get_temp_dir(), 'all_stacks_');

        if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return back()->with('error', 'Unable to create zip file.');
        }

        $addedNames = [];
        foreach ($stacks as $stack) {
            $rawExcel = Excel::raw(new StackExport($stack), \Maatwebsite\Excel\Excel::XLSX);

            $stackName = !empty($stack->stack_name) ? $stack->stack_name : 'Stack_' . $stack->id;
            $cleanName = preg_replace('/[\\\\\/:*?"<>|]/', '_', $stackName);
            $fileName = 'Report_' . str_replace(' ', '_', $cleanName) . '.xlsx';

            if (isset($addedNames[$fileName])) {
                $addedNames[$fileName]++;
                $fileName = 'Report_' . str_replace(' ', '_', $cleanName) . "_{$addedNames[$fileName]}.xlsx";
            } else {
                $addedNames[$fileName] = 1;
            }

            $zip->addFromString($fileName, $rawExcel);
        }

        $zip->close();

        return response()->download($tempZipPath, $zipFileName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Export all stacks in a single Excel file arranged by date (oldest to newest),
     * following the exact design pattern of 'مجموع تقارير السندات.xlsx'.
     */
    public function exportAllSingleExcel()
    {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        $stacksCount = Stack::has('bonds')->count();
        if ($stacksCount === 0) {
            return back()->with('error', 'لا توجد دفاتر سندات للتصدير.');
        }

        $export = new AllStacksExport();
        $tempFilePath = $export->exportToTempFile();

        $fileName = 'مجموع تقارير السندات.xlsx';

        return response()->download($tempFilePath, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Preview the full operational audit report in browser HTML format.
     */
    public function previewAuditReport(OperationalAuditService $auditService)
    {
        $data = $auditService->getAuditReportData();
        return view('reports.operational_audit_pdf', $data);
    }

    /**
     * Generate and download the professional Operational Audit Report PDF.
     */
    public function exportAuditPdf(
        Request $request,
        OperationalAuditService $auditService,
        PdfExportService $pdfService
    ) {
        set_time_limit(300);
        ini_set('memory_limit', '512M');

        try {
            $data = $auditService->getAuditReportData();
            $html = view('reports.operational_audit_pdf', $data)->render();

            $preferredEngine = $request->query('engine', 'auto');
            $pdfPath = $pdfService->generatePdfFromHtml($html, $preferredEngine);

            if ($pdfPath && file_exists($pdfPath) && filesize($pdfPath) > 500) {
                $fileName = 'تقرير_التدقيق_الرقابي_مستودع_التضامن_2019_2026.pdf';
                return response()->download($pdfPath, $fileName, [
                    'Content-Type' => 'application/pdf',
                ])->deleteFileAfterSend(true);
            }

            // If server-side generation is not available (e.g. headless browser or DomPDF missing on remote server),
            // seamlessly open the report in the browser with the native PDF print dialog triggered automatically!
            return redirect()->route('stacks.previewAuditReport', ['autoprint' => 1])
                ->with('info', 'تم تحويلك إلى نافذة التقرير والطباعة كـ PDF تلقائياً.');
        } catch (\Throwable $e) {
            Log::error('StackController::exportAuditPdf failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            // Fallback to client-side native PDF rendering instead of silent failure/reload
            return redirect()->route('stacks.previewAuditReport', ['autoprint' => 1])
                ->with('info', 'تم تحويلك إلى نافذة التقرير والطباعة كـ PDF تلقائياً.');
        }
    }
}

