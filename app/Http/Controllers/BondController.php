<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bond;
use App\Models\BondItem;
use App\Models\Stack;
use Illuminate\Support\Facades\DB;


class BondController extends Controller
{
    public function create()
    {
        // 1. Fetch the most recent bond to get the previous date and serial format
        $lastBond = Bond::latest('id')->first();

        // 2. Handle Serial Logic (Padding zeros)
        $lastSerial = $lastBond ? $lastBond->bond_serial : '3000';
        $nextNumber = (int) $lastSerial + 1;
        // We use the length of the previous serial to keep the padding consistent (e.g., 03154 -> 5 digits)
        $nextSerial = str_pad($nextNumber, strlen($lastSerial), '0', STR_PAD_LEFT);

        // 3. Handle Date Logic (Persisting previous date)
        // Fix: Define the missing variable here
        $defaultDate = $lastBond ? $lastBond->date : date('Y-m-d');

        // dd($nextSerial);
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

        // 2. Fetch unique item descriptions for suggestions
        $itemSuggestions = BondItem::distinct()
            ->orderBy('item_description', 'asc')
            ->pluck('item_description');

        // 5. Return view with all variables defined
        return view('bonds.create', compact('nextSerial', 'defaultDate', 'receivers', 'itemSuggestions'));
    }


    public function store(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $data = $request->all();

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
            $currentSerial = $request->bond_serial; // e.g., "03154"
            $nextNumber = (int) $currentSerial + 1;
            $formattedNext = str_pad($nextNumber, strlen($currentSerial), '0', STR_PAD_LEFT);

            return response()->json([
                'success' => true, 
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
                'date',
                'operation_name',
                'received_from',
                'car_number',
                'note',
                'bond_link'
            ]);

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
}
