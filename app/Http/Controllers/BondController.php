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
        // 4. Fetch unique receiver names for the datalist
        $receivers = Bond::where('received_from', '!=', '--- N/A ---')
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
            $bond = Bond::create($request->all());

            // Only add items if it's not a missing bond and items exist
            if (!$request->is_missing && $request->has('items')) {
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
                'note', // Added
                'bond_link'
            ]);

            // 2. Handle Checkbox logic (is_missing)
            // If the checkbox is in the request, it's true (1), otherwise false (0)
            $data['is_missing'] = $request->has('is_missing') ? 1 : 0;

            // 3. Update Header
            $bond->update($data);

            // 4. Sync Items
            // We only sync items if the bond is NOT marked as missing
            $bond->items()->delete();

            if (!$data['is_missing'] && $request->has('items')) {
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
