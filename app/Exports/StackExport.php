<?php

namespace App\Exports;

use App\Models\Stack;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StackExport implements FromCollection, WithHeadings, WithMapping
{
    protected $stack;

    public function __construct(Stack $stack) {
        $this->stack = $stack;
    }

    public function collection() {
        // Get all bonds belonging to this stack
        return $this->stack->bonds()->with('items')->get();
    }

    public function headings(): array {
        // Matching the headers in Image 2
        return [
            'رقم السند',     // Bond No
            'التاريخ',       // Date
            'العميل/المستلم', // Client/Receiver
            'إجمالي الكمية',  // Total Quantity
            'العملية',       // Operation
            'رقم السيارة',   // Car Number
        ];
    }

    public function map($bond): array {
        return [
            $bond->bond_serial,
            $bond->date,
            $bond->received_from,
            $bond->items->sum('quantity'), // Calculated total
            $bond->operation_name,
            $bond->car_number,
        ];
    }
}