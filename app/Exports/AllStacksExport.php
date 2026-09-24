<?php

namespace App\Exports;

use App\Models\Stack;
use App\Models\Bond;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class AllStacksExport
{
    /**
     * Generate the complete Spreadsheet following the exact design pattern of
     * 'مجموع تقارير السندات.xlsx', sorted chronologically from oldest to newest,
     * with color-coded year timestamps and dedicated image link column.
     */
    public function buildSpreadsheet(): Spreadsheet
    {
        // 1. Fetch all stacks with bonds and items
        $stacks = Stack::with(['bonds.items'])->get();

        // 2. Sort stacks by earliest non-empty date (oldest to newest)
        $sortedStacks = $stacks->sortBy(function ($stack) {
            $earliestDate = $stack->bonds
                ->whereNotNull('date')
                ->filter(fn($b) => trim($b->date) !== '' && $b->date !== '0000-00-00')
                ->min('date');

            return $earliestDate ?? '9999-12-31';
        })->values();

        $spreadsheet = new Spreadsheet();
        
        // -------------------------------------------------------------
        // Main Sheet: Worksheet (Exact copy of reference layout & styles)
        // -------------------------------------------------------------
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Worksheet');
        $sheet->setRightToLeft(true);

        // Column Widths matching 'مجموع تقارير السندات.xlsx' + Col I for Image Link
        $colWidths = [
            'A' => 15.11,
            'B' => 14.00,
            'C' => 17.66,
            'D' => 29.22,
            'E' => 50.00,
            'F' => 12.11,
            'G' => 9.66,
            'H' => 49.33,
            'I' => 45.00,
        ];
        foreach ($colWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // Row 2: Title Row (A2:I2 Merged)
        $sheet->mergeCells('A2:I2');
        $sheet->setCellValue('A2', 'مجموع تقارير السندات');
        $sheet->getRowDimension(2)->setRowHeight(40);
        $sheet->getStyle('A2:I2')->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '1F4E78'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9E1F2'],
            ],
        ]);

        // Row 3: Table Headings
        $headers = [
            'A' => 'التاريخ',
            'B' => 'رقم السند',
            'C' => 'العملية / المشروع',
            'D' => 'وارد من العميل',
            'E' => 'المواد والأدوات',
            'F' => 'الصنف',
            'G' => 'الكمية',
            'H' => 'ملاحظات',
            'I' => 'رابط السند',
        ];
        $sheet->getRowDimension(3)->setRowHeight(25);
        foreach ($headers as $col => $title) {
            $sheet->setCellValue("{$col}3", $title);
        }

        $sheet->getStyle('A3:I3')->applyFromArray([
            'font' => [
                'name' => 'Calibri',
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);

        // Row 4: Empty separator row
        $sheet->getRowDimension(4)->setRowHeight(25);

        // Year color palette for timestamp cells (Column A)
        $yearColors = [
            '2020' => 'D9E1F2', // Light Blue (explicitly requested)
            '2021' => 'E0F7FA', // Soft Cyan / Teal
            '2022' => 'E2EFDA', // Soft Sage Green
            '2023' => 'FFF2CC', // Soft Warm Gold / Amber
            '2024' => 'EDE2FE', // Soft Lavender / Purple
            '2025' => 'FCE4D6', // Soft Peach / Rose
            '2026' => 'D5F5E3', // Soft Mint Green
        ];

        // Data Rows & Styling Tokens
        $currentRow = 5;
        $dataBorders = [
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'font' => [
                'name' => 'Calibri',
                'size' => 11,
                'color' => ['rgb' => '000000'],
            ],
        ];

        $hyperlinkStyle = [
            'font' => [
                'name' => 'Calibri',
                'size' => 11,
                'color' => ['rgb' => '0563C1'],
                'underline' => true,
            ],
        ];

        // Summary data for Sheet1
        $summaryData = [];

        foreach ($sortedStacks as $stackIdx => $stack) {
            // Sort bonds inside the stack by ID/serial
            $bonds = $stack->bonds->sortBy('id')->values();

            if ($bonds->isEmpty()) {
                continue;
            }

            $stackStartRow = $currentRow;

            // Track min/max for Sheet1 index
            $firstBond = $bonds->first();
            $lastBond = $bonds->last();
            $summaryData[] = [
                'first_date' => $firstBond->date,
                'first_serial' => (string)$firstBond->bond_serial,
                'last_date' => $lastBond->date,
                'last_serial' => (string)$lastBond->bond_serial,
            ];

            foreach ($bonds as $bond) {
                $r = $currentRow;

                // Handle missing / cancelled items description
                if ($bond->received_from === 'ملغي' || $bond->operation_name === 'ملغي') {
                    $itemsString = "--- سند ملغي ---";
                } elseif ($bond->is_missing || $bond->received_from === 'مفقود') {
                    $itemsString = "--- سند مفقود ---";
                } else {
                    $itemsString = $bond->items->map(fn($i) => "{$i->item_description} × {$i->quantity}")->implode("\n");
                }

                $fullUrl = $bond->image_url ?? '';

                // Explicit text writing to preserve leading zeros in serials
                $sheet->setCellValueExplicit("A{$r}", $bond->date ?? '', DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("B{$r}", (string)$bond->bond_serial, DataType::TYPE_STRING);
                $sheet->setCellValue("C{$r}", $bond->operation_name ?? '');
                $sheet->setCellValue("D{$r}", $bond->received_from ?? '');
                $sheet->setCellValue("E{$r}", $itemsString);
                $sheet->setCellValue("F{$r}", ''); // Category placeholder matching template
                $sheet->setCellValue("G{$r}", ''); // Quantity placeholder matching template
                $sheet->setCellValue("H{$r}", $bond->note ?? '');
                $sheet->setCellValue("I{$r}", !empty($fullUrl) ? $fullUrl : '---');

                // Color-code timestamp cell in Column A by year
                $year = !empty($bond->date) ? substr(trim($bond->date), 0, 4) : '';
                if (!empty($year) && isset($yearColors[$year])) {
                    $sheet->getStyle("A{$r}")->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => $yearColors[$year]],
                        ],
                        'font' => [
                            'name' => 'Calibri',
                            'size' => 11,
                            'bold' => true,
                            'color' => ['rgb' => '1F3864'],
                        ],
                    ]);
                }

                // Clickable bond image hyperlinks on both Serial (Col B) and Bond Link (Col I)
                if (!empty($fullUrl)) {
                    // Serial number hyperlink
                    $cellB = "B{$r}";
                    $sheet->getCell($cellB)->getHyperlink()->setUrl($fullUrl);
                    $sheet->getCell($cellB)->getHyperlink()->setTooltip('عرض صورة السند: ' . $bond->bond_serial);
                    $sheet->getStyle($cellB)->applyFromArray($hyperlinkStyle);

                    // Image Link column hyperlink
                    $cellI = "I{$r}";
                    $sheet->getCell($cellI)->getHyperlink()->setUrl($fullUrl);
                    $sheet->getCell($cellI)->getHyperlink()->setTooltip('فتح صورة السند');
                    $sheet->getStyle($cellI)->applyFromArray($hyperlinkStyle);
                }

                $currentRow++;
            }

            // Apply data borders and alignment in bulk for performance
            $stackEndRow = $currentRow - 1;
            if ($stackEndRow >= $stackStartRow) {
                $sheet->getStyle("A{$stackStartRow}:I{$stackEndRow}")->applyFromArray($dataBorders);
            }

            // Empty separator row between stacks (matching reference file)
            if ($stackIdx < count($sortedStacks) - 1) {
                $currentRow++;
            }
        }

        // -------------------------------------------------------------
        // Secondary Sheet: Sheet1 (Chronological Index matching reference)
        // -------------------------------------------------------------
        $sheet1 = $spreadsheet->createSheet();
        $sheet1->setTitle('Sheet1');
        $sheet1->setRightToLeft(true);

        $s1Row = 3;
        foreach ($summaryData as $sum) {
            $sheet1->setCellValueExplicit("C{$s1Row}", $sum['first_date'] ?? '', DataType::TYPE_STRING);
            $sheet1->setCellValueExplicit("D{$s1Row}", $sum['first_serial'] ?? '', DataType::TYPE_STRING);
            $s1Row++;
            $sheet1->setCellValueExplicit("C{$s1Row}", $sum['last_date'] ?? '', DataType::TYPE_STRING);
            $sheet1->setCellValueExplicit("D{$s1Row}", $sum['last_serial'] ?? '', DataType::TYPE_STRING);
            $s1Row += 5; // Spacing matching the reference Sheet1 pattern
        }

        // Set Worksheet as active sheet
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * Write spreadsheet to a temporary file and return the file path.
     */
    public function exportToTempFile(): string
    {
        $spreadsheet = $this->buildSpreadsheet();
        $tempPath = tempnam(sys_get_temp_dir(), 'all_stacks_excel_') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);
        return $tempPath;
    }
}
