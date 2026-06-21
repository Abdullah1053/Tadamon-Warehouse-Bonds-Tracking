<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bond;
use App\Models\Stack;
use DB;

class BondController extends Controller
{
    public function create() {
        $nextSerial = (Bond::max('bond_serial') ?? 3000) + 1;
        return view('bonds.create', compact('nextSerial'));
    }

    public function store(Request $request) {
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
            return response()->json(['success' => true, 'next_serial' => $bond->bond_serial + 1]);
        });
    }

    public function index() {
        return view('bonds.index', [
            // Table 1: Ready for Stacking
            'unassignedBonds' => Bond::whereNull('stack_id')->with('items')->latest()->get(),
            // Table 2: Already Grouped
            'assignedBonds'   => Bond::whereNotNull('stack_id')->with(['items', 'stack'])->latest()->get(),
            // For the dropdown
            'stacks'          => Stack::all()
        ]);
    }

    // Action to remove a bond from a stack (Unstack)
    public function detach(Bond $bond) {
        $bond->update(['stack_id' => null]);
        return back()->with('success', 'Bond unlinked from stack.');
    }

// Action to completely delete a bond record
public function destroy(Bond $bond) {
    $bond->delete(); // Cascades to items if migration set correctly
    return back()->with('success', 'Bond deleted successfully.');
}


  public function bulkAssign(Request $request) {
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
public function show(Bond $bond) {
    return view('bonds.show', compact('bond'));
}

public function edit(Bond $bond) {
    return view('bonds.edit', compact('bond'));
}

public function update(Request $request, Bond $bond) {
    return DB::transaction(function () use ($request, $bond) {
        // 1. Update Header
        $bond->update($request->only(['bond_serial', 'date', 'operation_name', 'received_from', 'car_number']));

        // 2. Sync Items (Remove old, add current)
        $bond->items()->delete();
        foreach ($request->items as $item) {
            if (!empty($item['description'])) {
                $bond->items()->create([
                    'item_description' => $item['description'],
                    'quantity'         => $item['quantity'],
                ]);
            }
        }

        return response()->json(['success' => true, 'redirect' => route('bonds.index')]);
    });
}

    
}