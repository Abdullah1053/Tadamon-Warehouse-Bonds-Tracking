<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\StackExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Stack;
use App\Models\Bond;


class StackController extends Controller
{

    public function index() {
        return view('dashboard', [
            'stacks' => Stack::latest()->get(),
            'pendingBonds' => Bond::whereNull('stack_id')->with('items')->get()
        ]);
    }


    public function createStack(Request $request) {
        $stack = Stack::create([
            'stack_name'   => $request->stack_name,
            'start_serial' => $request->start,
            'end_serial'   => $request->end,
        ]);

        Bond::whereBetween('bond_serial', [$request->start, $request->end])
            ->update(['stack_id' => $stack->id]);

        return back()->with('success', 'Stack created by range.');
    }



    public function bulkAssign(Request $request) {
        $request->validate(['bond_ids' => 'required|array', 'stack_id' => 'required']);
        Bond::whereIn('id', $request->bond_ids)->update(['stack_id' => $request->stack_id]);
        return response()->json(['success' => true, 'message' => 'Bonds assigned successfully.']);
    }

    public function export(Stack $stack) 
    {
        // The filename includes the stack name for easy office filing
        $fileName = 'Report_' . str_replace(' ', '_', $stack->stack_name) . '.xlsx';
        return Excel::download(new StackExport($stack), $fileName);
    }

}
