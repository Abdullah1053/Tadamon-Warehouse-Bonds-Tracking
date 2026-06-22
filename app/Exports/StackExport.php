<?php

namespace App\Exports;

use App\Models\Stack;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class StackExport implements FromCollection, ShouldAutoSize, WithEvents
{
    protected $stack;

    public function __construct(Stack $stack)
    {
        // Load hierarchical data: Stack -> Bonds -> Items
        $this->stack = $stack->load('bonds.items');
    }

    /**
     * We return an empty collection because we will manually 
     * write the data using coordinates in the AfterSheet event.
     */
    public function collection()
    {
        return collect([]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // 1. Set Right-to-Left (RTL) for Arabic context
                $sheet->setRightToLeft(true);

                // 2. Global Font Setup
                $sheet->getParent()->getDefaultStyle()->getFont()->setName('Arial');
                $sheet->getParent()->getDefaultStyle()->getFont()->setSize(11);

                // 3. Main Report Title
                $sheet->mergeCells('A1:F1');
                $sheet->setCellValue('A1', "تقرير ستاتك: " . $this->stack->stack_name);
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(18);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $currentRow = 3;

                // 4. Iterate through every Bond
                foreach ($this->stack->bonds as $bond) {
                    
                    // --- BOND HEADER SECTION ---
                    // Title Row: "Bond #3151"
                    $sheet->mergeCells("A{$currentRow}:F{$currentRow}");
                    $sheet->setCellValue("A{$currentRow}", "سند استلام مواد رقم: " . $bond->bond_serial);
                    $sheet->getStyle("A{$currentRow}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                    ]);
                    $currentRow++;

                    // Metadata Row 1: Date & Receiver
                    $sheet->setCellValue("A{$currentRow}", "التاريخ:");
                    $sheet->setCellValue("B{$currentRow}", $bond->date);
                    $sheet->setCellValue("D{$currentRow}", "المستلم:");
                    $sheet->mergeCells("E{$currentRow}:F{$currentRow}");
                    $sheet->setCellValue("E{$currentRow}", $bond->received_from);
                    $sheet->getStyle("A{$currentRow}:F{$currentRow}")->getFont()->setBold(true);
                    $currentRow++;

                    // Metadata Row 2: Operation & Car
                    $sheet->setCellValue("A{$currentRow}", "العملية:");
                    $sheet->setCellValue("B{$currentRow}", $bond->operation_name);
                    $sheet->setCellValue("D{$currentRow}", "رقم السيارة:");
                    $sheet->setCellValue("E{$currentRow}", $bond->car_number ?? '---');
                    $sheet->getStyle("A{$currentRow}:F{$currentRow}")->getFont()->setBold(true);
                    $currentRow++;

                    // Note Row (If exists)
                    if ($bond->note) {
                        $sheet->mergeCells("A{$currentRow}:F{$currentRow}");
                        $sheet->setCellValue("A{$currentRow}", "ملاحظات: " . $bond->note);
                        $sheet->getStyle("A{$currentRow}")->getFont()->setItalic(true);
                        $currentRow++;
                    }

                    // --- ITEMS TABLE SECTION ---
                    $currentRow++; // Space before table
                    $tableStart = $currentRow;
                    
                    // Table Header
                    $sheet->setCellValue("A{$currentRow}", "م"); // Index
                    $sheet->mergeCells("B{$currentRow}:E{$currentRow}");
                    $sheet->setCellValue("B{$currentRow}", "وصف المادة / الأداة");
                    $sheet->setCellValue("F{$currentRow}", "الكمية");
                    
                    $sheet->getStyle("A{$currentRow}:F{$currentRow}")->applyFromArray([
                        'font' => ['bold' => true],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9D9D9']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
                    ]);
                    $currentRow++;

                    // Table Body (Items)
                    if($bond->is_missing) {
                        $sheet->mergeCells("A{$currentRow}:F{$currentRow}");
                        $sheet->setCellValue("A{$currentRow}", "--- هذا السند مفقود من التسلسل الرقمي ---");
                        $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("A{$currentRow}")->getFont()->getColor()->setRGB('FF0000');
                        $currentRow++;
                    } else {
                        foreach ($bond->items as $index => $item) {
                            $sheet->setCellValue("A{$currentRow}", $index + 1);
                            $sheet->mergeCells("B{$currentRow}:E{$currentRow}");
                            $sheet->setCellValue("B{$currentRow}", $item->item_description);
                            $sheet->setCellValue("F{$currentRow}", $item->quantity);
                            
                            // Center the quantity and index
                            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                            $sheet->getStyle("F{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                            
                            // Apply borders to row
                            $sheet->getStyle("A{$currentRow}:F{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                            $currentRow++;
                        }
                    }

                    // 5. Add spacing between bonds
                    $currentRow += 2;
                }

                // Auto-size all columns at the end
                foreach (range('A', 'F') as $col) {
                    $event->sheet->getDelegate()->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }
}