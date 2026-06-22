<?php

namespace App\Exports;

use App\Models\Stack;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StackExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents, WithCustomStartCell
{
    protected $stack;

    public function __construct(Stack $stack)
    {
        $this->stack = $stack;
    }

    /**
     * Start the table at A2 to leave Row 1 for the main Title
     */
    public function startCell(): string
    {
        return 'A2';
    }

    public function collection()
    {
        return $this->stack->bonds()->with('items')->get();
    }

    public function headings(): array
    {
        return [
            'التاريخ',
            'رقم السند',
            'العملية / المشروع',
            'المستلم',
            'المواد والأدوات',
            'ملاحظات',
        ];
    }

    public function map($bond): array
    {
        $itemsString = $bond->is_missing 
            ? "--- سند مفقود ---" 
            : $bond->items->map(fn($i) => "{$i->item_description} × {$i->quantity}")->implode("\n");

        return [
            $bond->date,
            $bond->bond_serial,
            $bond->operation_name,
            $bond->received_from,
            $itemsString,
            $bond->note ?? '---',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $rowCount = $this->stack->bonds()->count() + 2; // +2 because we start at row 2

        return [
            // Row 2: Table Headers
            2 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            ],
            // Data Range styling
            "A2:F{$rowCount}" => [
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'wrapText' => true,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // 1. RTL Support
                $sheet->setRightToLeft(true);

                // 2. Insert Main Header at Row 1
                $sheet->mergeCells('A1:F1');
                $sheet->setCellValue('A1', "تقرير دفتر سندات: " . $this->stack->stack_name);
                
                // 3. Style Main Header
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => '1F4E78']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'D9E1F2']
                    ]
                ]);

                // 4. Set Row Heights
                $sheet->getRowDimension(1)->setRowHeight(40); // Title row
                $sheet->getRowDimension(2)->setRowHeight(25); // Header row
                
                // 5. Adjust Column Width for Items
                $sheet->getColumnDimension('E')->setAutoSize(false)->setWidth(50);
            },
        ];
    }
}