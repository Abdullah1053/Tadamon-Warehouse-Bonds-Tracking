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
     * Generate the complete Spreadsheet following the design pattern of
     * 'مجموع تقارير السندات.xlsx', sorted chronologically from oldest to newest:
     * - Background color covers the entire row based on year.
     * - Items and Quantities each have their own dedicated column.
     * - Multi-item bonds use merged parent cells creating an inner table structure.
     * - Dedicated bond image link column.
     */
    public function buildSpreadsheet(): Spreadsheet
    {
        // 1. Fetch all stacks with bonds and items
        $stacks = Stack::with(['bonds.items'])->get();

        // 2. Sort stacks chronologically by earliest non-empty date (oldest to newest)
        $sortedStacks = $stacks->sortBy(function ($stack) {
            $earliestDate = $stack->bonds
                ->whereNotNull('date')
                ->filter(fn($b) => trim($b->date) !== '' && $b->date !== '0000-00-00')
                ->min('date');

            return $earliestDate ?? '9999-12-31';
        })->values();

        $spreadsheet = new Spreadsheet();
        
        // -------------------------------------------------------------
        // Main Sheet: Worksheet
        // -------------------------------------------------------------
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Worksheet');
        $sheet->setRightToLeft(true);

        // Column Widths
        $colWidths = [
            'A' => 15.11, // التاريخ
            'B' => 14.00, // رقم السند
            'C' => 18.00, // العملية / المشروع
            'D' => 29.22, // وارد من العميل
            'E' => 50.00, // المواد والأدوات (اسم الصنف / المادة)
            'F' => 14.00, // الكمية
            'G' => 45.00, // ملاحظات
            'H' => 40.00, // رابط السند
        ];
        foreach ($colWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        // Row 2: Title Row (A2:H2 Merged)
        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', 'مجموع تقارير السندات');
        $sheet->getRowDimension(2)->setRowHeight(40);
        $sheet->getStyle('A2:H2')->applyFromArray([
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
            'F' => 'الكمية',
            'G' => 'ملاحظات',
            'H' => 'رابط السند',
        ];
        $sheet->getRowDimension(3)->setRowHeight(25);
        foreach ($headers as $col => $title) {
            $sheet->setCellValue("{$col}3", $title);
        }

        $sheet->getStyle('A3:H3')->applyFromArray([
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

        // Year color palette for entire row background
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
                $year = !empty($bond->date) ? substr(trim($bond->date), 0, 4) : '';
                $bgYearColor = $yearColors[$year] ?? 'F8F9FA';

                $fullUrl = $bond->image_url ?? '';
                $items = $bond->items;

                $isCancelled = ($bond->received_from === 'ملغي' || $bond->operation_name === 'ملغي');
                $isMissing = ($bond->is_missing || $bond->received_from === 'مفقود');

                if ($isCancelled) {
                    $itemRows = [['desc' => '--- سند ملغي ---', 'qty' => '---']];
                } elseif ($isMissing) {
                    $itemRows = [['desc' => '--- سند مفقود ---', 'qty' => '---']];
                } elseif ($items->isEmpty()) {
                    $itemRows = [['desc' => '---', 'qty' => '']];
                } else {
                    $itemRows = [];
                    foreach ($items as $it) {
                        $itemRows[] = [
                            'desc' => $it->item_description ?? '',
                            'qty' => $it->quantity ?? ''
                        ];
                    }
                }

                $rowCount = count($itemRows);
                $bondStartRow = $currentRow;
                $bondEndRow = $currentRow + $rowCount - 1;

                // 1. Populate item descriptions & quantities into their dedicated columns E and F
                foreach ($itemRows as $idx => $it) {
                    $r = $bondStartRow + $idx;
                    $sheet->setCellValue("E{$r}", $it['desc']);
                    $sheet->setCellValueExplicit("F{$r}", (string)$it['qty'], DataType::TYPE_STRING);
                }

                // 2. Populate common bond fields on bondStartRow
                $sheet->setCellValueExplicit("A{$bondStartRow}", $bond->date ?? '', DataType::TYPE_STRING);
                $sheet->setCellValueExplicit("B{$bondStartRow}", (string)$bond->bond_serial, DataType::TYPE_STRING);
                $sheet->setCellValue("C{$bondStartRow}", $bond->operation_name ?? '');
                $sheet->setCellValue("D{$bondStartRow}", $bond->received_from ?? '');
                $sheet->setCellValue("G{$bondStartRow}", $bond->note ?? '');
                $sheet->setCellValue("H{$bondStartRow}", !empty($fullUrl) ? $fullUrl : '---');

                // 3. If multiple items, merge common fields vertically (creates the inner table effect)
                if ($rowCount > 1) {
                    $sheet->mergeCells("A{$bondStartRow}:A{$bondEndRow}");
                    $sheet->mergeCells("B{$bondStartRow}:B{$bondEndRow}");
                    $sheet->mergeCells("C{$bondStartRow}:C{$bondEndRow}");
                    $sheet->mergeCells("D{$bondStartRow}:D{$bondEndRow}");
                    $sheet->mergeCells("G{$bondStartRow}:G{$bondEndRow}");
                    $sheet->mergeCells("H{$bondStartRow}:H{$bondEndRow}");
                }

                // 4. Background color covers the ENTIRE row/block (all rows & all columns A..H)
                $sheet->getStyle("A{$bondStartRow}:H{$bondEndRow}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $bgYearColor],
                    ]
                ]);

                // 5. Clickable hyperlinks on Serial (Col B) and Link (Col H)
                if (!empty($fullUrl)) {
                    $cellB = "B{$bondStartRow}";
                    $sheet->getCell($cellB)->getHyperlink()->setUrl($fullUrl);
                    $sheet->getCell($cellB)->getHyperlink()->setTooltip('عرض صورة السند: ' . $bond->bond_serial);
                    $sheet->getStyle($cellB)->applyFromArray($hyperlinkStyle);

                    $cellH = "H{$bondStartRow}";
                    $sheet->getCell($cellH)->getHyperlink()->setUrl($fullUrl);
                    $sheet->getCell($cellH)->getHyperlink()->setTooltip('فتح صورة السند');
                    $sheet->getStyle($cellH)->applyFromArray($hyperlinkStyle);
                }

                $currentRow = $bondEndRow + 1;
            }

            // Apply data borders and alignment in bulk for performance
            $stackEndRow = $currentRow - 1;
            if ($stackEndRow >= $stackStartRow) {
                $sheet->getStyle("A{$stackStartRow}:H{$stackEndRow}")->applyFromArray($dataBorders);
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
