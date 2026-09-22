<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bond;
use App\Services\ReceiverNormalizationService;

class ReceiverNormalizationController extends Controller
{
    protected ReceiverNormalizationService $normalizationService;

    public function __construct(ReceiverNormalizationService $normalizationService)
    {
        $this->normalizationService = $normalizationService;
    }

    /**
     * Show the Receiver Normalization & Deduplication Dashboard.
     */
    public function index(Request $request)
    {
        $threshold = (float) $request->input('threshold', 80.0);
        $search = $request->input('search');

        $clusterData = $this->normalizationService->getClusters($threshold, $search);
        $allReceivers = $this->normalizationService->getUniqueReceiversWithCounts();

        return view('receivers.normalize', [
            'stats' => $clusterData['stats'],
            'clusters' => $clusterData['clusters'],
            'threshold' => $threshold,
            'search' => $search,
            'allReceivers' => $allReceivers,
        ]);
    }

    /**
     * API endpoint to dynamically retrieve clusters when threshold or search changes.
     */
    public function clustersApi(Request $request)
    {
        $threshold = (float) $request->input('threshold', 80.0);
        $search = $request->input('search');

        $data = $this->normalizationService->getClusters($threshold, $search);

        return response()->json($data);
    }

    /**
     * API endpoint to preview bonds matching the given variant names.
     */
    public function preview(Request $request)
    {
        $variants = (array) $request->input('variants', []);
        if (empty($variants)) {
            return response()->json(['bonds' => [], 'total' => 0]);
        }

        $bonds = Bond::whereIn('received_from', $variants)
            ->select('id', 'bond_serial', 'date', 'received_from', 'operation_name', 'note')
            ->orderBy('date', 'desc')
            ->limit(25)
            ->get();

        $totalCount = Bond::whereIn('received_from', $variants)->count();

        return response()->json([
            'bonds' => $bonds,
            'total' => $totalCount,
        ]);
    }

    /**
     * Merge selected variant names into the target canonical name.
     */
    public function merge(Request $request)
    {
        $request->validate([
            'target_name' => 'required|string|max:190',
            'variants' => 'required|array|min:1',
            'variants.*' => 'required|string',
        ]);

        $targetName = trim($request->input('target_name'));
        $variants = $request->input('variants');

        $updatedCount = $this->normalizationService->mergeVariants($targetName, $variants);

        return response()->json([
            'success' => true,
            'target_name' => $targetName,
            'updated_count' => $updatedCount,
            'message' => "تم توحيد {$updatedCount} سند بنجاح تحت الاسم: \"{$targetName}\"",
        ]);
    }

    /**
     * Merge arbitrary selected names from the manual tool.
     */
    public function mergeCustom(Request $request)
    {
        $request->validate([
            'target_name' => 'required|string|max:190',
            'selected_names' => 'required|array|min:1',
            'selected_names.*' => 'required|string',
        ]);

        $targetName = trim($request->input('target_name'));
        $selectedNames = $request->input('selected_names');

        $updatedCount = $this->normalizationService->mergeVariants($targetName, $selectedNames);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'target_name' => $targetName,
                'updated_count' => $updatedCount,
                'message' => "تم توحيد {$updatedCount} سند بنجاح تحت الاسم: \"{$targetName}\"",
            ]);
        }

        return redirect()->route('receivers.normalize')->with('success', "تم توحيد {$updatedCount} سند بنجاح تحت الاسم: \"{$targetName}\"");
    }
}
