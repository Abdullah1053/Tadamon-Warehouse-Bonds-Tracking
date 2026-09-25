<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bond;
use App\Models\BondItem;
use App\Models\Stack;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class BondController extends Controller
{
    public function create(Request $request)
    {
        $type = $request->query('type', Bond::TYPE_RECEIPT);
        if (!in_array($type, [Bond::TYPE_RECEIPT, Bond::TYPE_DISBURSEMENT])) {
            $type = Bond::TYPE_RECEIPT;
        }

        // 1. Fetch the most recent bond OF THIS TYPE to get previous date and serial format
        $lastBond = Bond::where('type', $type)->latest('id')->first();

        // 2. Handle Serial Logic (Padding zeros)
        if ($lastBond) {
            $lastSerial = $lastBond->bond_serial;
            $nextNumber = (int) $lastSerial + 1;
            $nextSerial = str_pad($nextNumber, strlen($lastSerial), '0', STR_PAD_LEFT);
        } else {
            // Default starting serial for new sequence
            $nextSerial = ($type === Bond::TYPE_DISBURSEMENT) ? '0001' : '3000';
        }

        // 3. Handle Date Logic (Persisting previous date)
        $defaultDate = $lastBond ? $lastBond->date : date('Y-m-d');

        // 4. Fetch unique receiver names for the datalist (exclude cancelled & missing placeholders)
        $receivers = Bond::where('received_from', '!=', '--- N/A ---')
            ->where('received_from', '!=', 'مفقود')
            ->where('received_from', '!=', 'ملغي')
            ->where('received_from', '!=', '')
            ->whereNotNull('received_from')
            ->select('received_from')
            ->distinct()
            ->orderBy('received_from', 'asc')
            ->pluck('received_from');

        // 5. Fetch unique item descriptions for suggestions
        $itemSuggestions = BondItem::distinct()
            ->orderBy('item_description', 'asc')
            ->pluck('item_description');

        // 6. Return view with all variables defined
        return view('bonds.create', compact('type', 'nextSerial', 'defaultDate', 'receivers', 'itemSuggestions'));
    }

    public function createDisbursement(Request $request)
    {
        $request->merge(['type' => Bond::TYPE_DISBURSEMENT]);
        return $this->create($request);
    }

    public function nextSerial(Request $request)
    {
        $type = $request->query('type', Bond::TYPE_RECEIPT);
        if (!in_array($type, [Bond::TYPE_RECEIPT, Bond::TYPE_DISBURSEMENT])) {
            $type = Bond::TYPE_RECEIPT;
        }

        $lastBond = Bond::where('type', $type)->latest('id')->first();

        if ($lastBond) {
            $lastSerial = $lastBond->bond_serial;
            $nextNumber = (int) $lastSerial + 1;
            $nextSerial = str_pad($nextNumber, strlen($lastSerial), '0', STR_PAD_LEFT);
            $defaultDate = $lastBond->date;
        } else {
            $nextSerial = ($type === Bond::TYPE_DISBURSEMENT) ? '0001' : '3000';
            $defaultDate = date('Y-m-d');
        }

        return response()->json([
            'success' => true,
            'type' => $type,
            'next_serial' => $nextSerial,
            'default_date' => $defaultDate
        ]);
    }

    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $data = $request->all();

            $type = $request->input('type', Bond::TYPE_RECEIPT);
            if (!in_array($type, [Bond::TYPE_RECEIPT, Bond::TYPE_DISBURSEMENT])) {
                $type = Bond::TYPE_RECEIPT;
            }
            $data['type'] = $type;

            $isCancelled = $request->input('bond_type') === 'cancelled' 
                || $request->input('received_from') === 'ملغي'
                || $request->input('operation_name') === 'ملغي';

            $isMissing = (bool) $request->input('is_missing')
                || $request->input('bond_type') === 'missing'
                || $request->input('received_from') === 'مفقود'
                || $request->input('operation_name') === 'مفقود';

            if ($isCancelled) {
                $data['operation_name'] = 'ملغي';
                $data['received_from'] = 'ملغي';
                $data['note'] = 'ملغي';
                $data['car_number'] = null;
                $data['is_missing'] = 0; // Physical copy is still attached
            } elseif ($isMissing) {
                $data['operation_name'] = 'مفقود';
                $data['received_from'] = 'مفقود';
                $data['note'] = 'مفقود';
                $data['car_number'] = null;
                $data['is_missing'] = 1; // Physical paper is cut off
            } else {
                $data['is_missing'] = 0;
            }

            // Handle file upload for bond_image
            if ($request->hasFile('bond_image')) {
                $file = $request->file('bond_image');
                $extension = $file->getClientOriginalExtension();
                $cleanSerial = preg_replace('/[^0-9a-zA-Z_-]/', '', (string)$request->input('bond_serial', 'bond'));
                $safeFilename = 'bond_' . $type . '_' . $cleanSerial . '_' . time() . '_' . Str::random(4) . '.' . $extension;
                $path = $file->storeAs('bonds', $safeFilename, 'public');
                $data['bond_link'] = '/storage/' . $path;
            }

            $bond = Bond::create($data);

            // Only add items if it's a normal bond (not cancelled and not missing)
            if (!$isCancelled && !$isMissing && $request->has('items')) {
                foreach ($request->items as $item) {
                    if (!empty($item['description'])) {
                        $bond->items()->create([
                            'item_description' => $item['description'],
                            'quantity' => $item['quantity']
                        ]);
                    }
                }
            }
            $currentSerial = $request->bond_serial; // e.g., "03154" or "0001"
            $nextNumber = (int) $currentSerial + 1;
            $formattedNext = str_pad($nextNumber, strlen($currentSerial), '0', STR_PAD_LEFT);

            return response()->json([
                'success' => true, 
                'type' => $type,
                'next_serial' => $formattedNext,
                'saved_date'  => $bond->date // Send the date back to keep the form consistent
            ]);
        });
    }

    public function index()
    {
        return view('bonds.index', [
            // Table 1: Ready for Stacking
            'unassignedBonds' => Bond::whereNull('stack_id')->with('items')->latest()->get(),
            // Table 2: Already Grouped
            'assignedBonds' => Bond::whereNotNull('stack_id')->with(['items', 'stack'])->latest()->get(),
            // For the dropdown
            'stacks' => Stack::all()
        ]);
    }

    // Action to remove a bond from a stack (Unstack)
    public function detach(Bond $bond)
    {
        $bond->update(['stack_id' => null]);
        return back()->with('success', 'Bond unlinked from stack.');
    }

    // Action to completely delete a bond record
    public function destroy(Bond $bond)
    {
        if ($bond->bond_link && str_starts_with($bond->bond_link, '/storage/')) {
            $oldRelative = str_replace('/storage/', '', $bond->bond_link);
            Storage::disk('public')->delete($oldRelative);
        }
        $bond->delete(); // Cascades to items if migration set correctly
        return back()->with('success', 'Bond deleted successfully.');
    }


    public function bulkAssign(Request $request)
    {
        $request->validate([
            'bond_ids' => 'required|array',
            'stack_id' => 'required|exists:stacks,id'
        ]);

        Bond::whereIn('id', $request->bond_ids)
            ->update(['stack_id' => $request->stack_id]);

        return response()->json([
            'success' => true,
            'message' => count($request->bond_ids) . ' bonds assigned successfully.'
        ]);
    }

    /**
     * Perform bulk actions on selected bonds (cancel, missing, normal, delete, unstack, move).
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'bond_ids' => 'required|array|min:1',
            'bond_ids.*' => 'integer|exists:bonds,id',
            'action' => 'required|in:cancel,missing,normal,delete,unstack,move',
            'target_stack_id' => 'nullable|required_if:action,move|exists:stacks,id'
        ]);

        $bondIds = $request->bond_ids;
        $action = $request->action;
        $count = count($bondIds);

        $message = DB::transaction(function () use ($bondIds, $action, $request, $count) {
            switch ($action) {
                case 'cancel':
                    Bond::whereIn('id', $bondIds)->update([
                        'operation_name' => 'ملغي',
                        'received_from' => 'ملغي',
                        'note' => 'ملغي',
                        'car_number' => null,
                        'is_missing' => 0
                    ]);
                    BondItem::whereIn('bond_id', $bondIds)->delete();
                    return "تم تحويل {$count} سند إلى ملغي بنجاح.";

                case 'missing':
                    Bond::whereIn('id', $bondIds)->update([
                        'operation_name' => 'مفقود',
                        'received_from' => 'مفقود',
                        'note' => 'مفقود',
                        'car_number' => null,
                        'is_missing' => 1
                    ]);
                    BondItem::whereIn('bond_id', $bondIds)->delete();
                    return "تم تحويل {$count} سند إلى مفقود بنجاح.";

                case 'normal':
                    $bonds = Bond::whereIn('id', $bondIds)->get();
                    foreach ($bonds as $bond) {
                        $updateData = ['is_missing' => 0];
                        if ($bond->received_from === 'ملغي' || $bond->received_from === 'مفقود') {
                            $updateData['received_from'] = '';
                        }
                        if ($bond->operation_name === 'ملغي' || $bond->operation_name === 'مفقود') {
                            $updateData['operation_name'] = '';
                        }
                        if ($bond->note === 'ملغي' || $bond->note === 'مفقود') {
                            $updateData['note'] = null;
                        }
                        $bond->update($updateData);
                    }
                    return "تمت استعادة {$count} سند إلى الحالة الطبيعية.";

                case 'delete':
                    $bondsToDelete = Bond::whereIn('id', $bondIds)->get();
                    foreach ($bondsToDelete as $b) {
                        if ($b->bond_link && str_starts_with($b->bond_link, '/storage/')) {
                            $oldRelative = str_replace('/storage/', '', $b->bond_link);
                            Storage::disk('public')->delete($oldRelative);
                        }
                    }
                    BondItem::whereIn('bond_id', $bondIds)->delete();
                    Bond::whereIn('id', $bondIds)->delete();
                    return "تم حذف {$count} سند نهائياً بنجاح.";

                case 'unstack':
                    Bond::whereIn('id', $bondIds)->update(['stack_id' => null]);
                    return "تم إلغاء تكديس {$count} سند ونقلها لقائمة غير المخصصة.";

                case 'move':
                    $targetStack = Stack::findOrFail($request->target_stack_id);
                    Bond::whereIn('id', $bondIds)->update(['stack_id' => $targetStack->id]);
                    return "تم نقل {$count} سند إلى دفتر \"{$targetStack->stack_name}\" بنجاح.";

                default:
                    return "تم تنفيذ الإجراء بنجاح.";
            }
        });

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'action' => $action,
                'affected_ids' => $bondIds
            ]);
        }

        return back()->with('success', $message);
    }


    public function show(Bond $bond)
    {
        return view('bonds.show', compact('bond'));
    }

    public function edit(Bond $bond)
    {
        return view('bonds.edit', compact('bond'));
    }

    public function update(Request $request, Bond $bond)
    {
        return DB::transaction(function () use ($request, $bond) {
            // 1. Prepare Data
            $data = $request->only([
                'bond_serial',
                'type',
                'date',
                'operation_name',
                'received_from',
                'car_number',
                'note',
                'bond_link'
            ]);
            if (isset($data['type']) && !in_array($data['type'], [Bond::TYPE_RECEIPT, Bond::TYPE_DISBURSEMENT])) {
                unset($data['type']);
            }

            $isCancelled = $request->input('bond_type') === 'cancelled' 
                || $request->input('received_from') === 'ملغي'
                || $request->input('operation_name') === 'ملغي';

            $isMissing = (bool) $request->input('is_missing')
                || $request->input('bond_type') === 'missing'
                || $request->input('received_from') === 'مفقود'
                || $request->input('operation_name') === 'مفقود';

            if ($isCancelled) {
                $data['operation_name'] = 'ملغي';
                $data['received_from'] = 'ملغي';
                $data['note'] = 'ملغي';
                $data['car_number'] = null;
                $data['is_missing'] = 0; // Physical copy is still attached
            } elseif ($isMissing) {
                $data['operation_name'] = 'مفقود';
                $data['received_from'] = 'مفقود';
                $data['note'] = 'مفقود';
                $data['car_number'] = null;
                $data['is_missing'] = 1; // Physical paper is cut off
            } else {
                $data['is_missing'] = 0;
            }

            // Handle image removal or upload
            if ($request->boolean('remove_image')) {
                if ($bond->bond_link && str_starts_with($bond->bond_link, '/storage/')) {
                    $oldRelative = str_replace('/storage/', '', $bond->bond_link);
                    Storage::disk('public')->delete($oldRelative);
                }
                $data['bond_link'] = null;
            } elseif ($request->hasFile('bond_image')) {
                if ($bond->bond_link && str_starts_with($bond->bond_link, '/storage/')) {
                    $oldRelative = str_replace('/storage/', '', $bond->bond_link);
                    Storage::disk('public')->delete($oldRelative);
                }
                $file = $request->file('bond_image');
                $extension = $file->getClientOriginalExtension();
                $cleanSerial = preg_replace('/[^0-9a-zA-Z_-]/', '', (string)$bond->bond_serial);
                $safeFilename = 'bond_' . $cleanSerial . '_' . time() . '_' . Str::random(4) . '.' . $extension;
                $path = $file->storeAs('bonds', $safeFilename, 'public');
                $data['bond_link'] = '/storage/' . $path;
            }

            // 2. Update Header
            $bond->update($data);

            // 3. Sync Items (Only for normal bonds)
            $bond->items()->delete();

            if (!$isCancelled && !$isMissing && $request->has('items')) {
                foreach ($request->items as $item) {
                    if (!empty($item['description'])) {
                        $bond->items()->create([
                            'item_description' => $item['description'],
                            'quantity' => $item['quantity'],
                        ]);
                    }
                }
            }

            return response()->json(['success' => true, 'redirect' => route('bonds.index')]);
        });
    }

    /**
     * Show the bulk image upload view.
     */
    public function bulkUploadView(Request $request)
    {
        $stacks = Stack::orderBy('stack_name', 'asc')->get();
        $selectedStackId = $request->query('stack_id');
        $selectedStack = $selectedStackId ? Stack::find($selectedStackId) : null;

        return view('bonds.upload', compact('stacks', 'selectedStackId', 'selectedStack'));
    }

    /**
     * Handle bulk upload of bond images and match to serial numbers.
     */
    public function bulkUpload(Request $request)
    {
        @set_time_limit(180);

        $request->validate([
            'images' => 'required|array|min:1',
            'images.*' => 'file|mimes:jpg,jpeg,png,webp,avif,pdf|max:20480',
            'stack_id' => 'nullable|exists:stacks,id'
        ]);

        $stackId = $request->input('stack_id');
        $files = $request->file('images');
        $results = [];
        $matchedCount = 0;
        $unmatchedCount = 0;

        foreach ($files as $file) {
            $originalName = $file->getClientOriginalName();
            $nameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);

            // Extract serial numbers (e.g. "03001", "IMG_3001", "bond_3001")
            preg_match('/(\d+)/', $nameWithoutExt, $matches);
            $extractedSerial = $matches[1] ?? null;

            $bond = null;
            if ($extractedSerial) {
                $query = Bond::query();
                if ($stackId) {
                    $query->where('stack_id', $stackId);
                }
                $query->where(function ($q) use ($extractedSerial) {
                    $q->where('bond_serial', $extractedSerial)
                      ->orWhere('bond_serial', ltrim($extractedSerial, '0'))
                      ->orWhereRaw('CAST(bond_serial AS UNSIGNED) = ?', [(int)$extractedSerial]);
                });

                $bond = $query->first();

                // If not found in stack, try finding globally
                if (!$bond && $stackId) {
                    $bond = Bond::where('bond_serial', $extractedSerial)
                        ->orWhere('bond_serial', ltrim($extractedSerial, '0'))
                        ->orWhereRaw('CAST(bond_serial AS UNSIGNED) = ?', [(int)$extractedSerial])
                        ->first();
                }
            }

            // Save the file
            $extension = $file->getClientOriginalExtension();
            $serialPrefix = $bond ? $bond->bond_serial : ($extractedSerial ?: 'unmatched');
            $safeFilename = 'bond_' . $serialPrefix . '_' . time() . '_' . Str::random(4) . '.' . $extension;
            $path = $file->storeAs('bonds', $safeFilename, 'public');
            $storagePath = '/storage/' . $path;

            if ($bond) {
                if ($bond->bond_link && str_starts_with($bond->bond_link, '/storage/')) {
                    $oldRelative = str_replace('/storage/', '', $bond->bond_link);
                    Storage::disk('public')->delete($oldRelative);
                }
                $bond->update(['bond_link' => $storagePath]);
                $matchedCount++;
                $results[] = [
                    'filename' => $originalName,
                    'matched' => true,
                    'bond_id' => $bond->id,
                    'serial' => $bond->bond_serial,
                    'receiver' => $bond->received_from,
                    'url' => asset($storagePath),
                    'message' => 'تم ربطه بنجاح بالسند #' . $bond->bond_serial
                ];
            } else {
                $unmatchedCount++;
                $results[] = [
                    'filename' => $originalName,
                    'matched' => false,
                    'serial' => $extractedSerial ?: 'غير معروف',
                    'url' => asset($storagePath),
                    'message' => 'تم حفظ الملف، ولكن لم يتم العثور على سند مطابق للرقم (' . ($extractedSerial ?: 'N/A') . ')'
                ];
            }
        }

        return response()->json([
            'success' => true,
            'message' => "تم رفع {$matchedCount} صورة وربطها بالسندات بنجاح!" . ($unmatchedCount > 0 ? " ({$unmatchedCount} ملف غير مطابق)" : ''),
            'matched_count' => $matchedCount,
            'unmatched_count' => $unmatchedCount,
            'total_count' => count($files),
            'details' => $results
        ]);
    }
}
