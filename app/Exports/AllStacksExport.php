<?php

namespace App\Exports;

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
     * 'مجموع تقارير السندات.xlsx', with ALL bonds strictly arranged chronologically
     * by bond date from oldest to newest across all stacks:
     * - Background color covers the entire row based on year.
     * - Items and Quantities each have their own dedicated column.
     * - Multi-item bonds use merged parent cells creating an inner table structure.
     * - Dedicated bond image link column.
     * - Sheet1 provides a chronological summary by year.
     */
    public function buildSpreadsheet(): Spreadsheet
    {
        // 1. Fetch all bonds directly ordered by bond date (oldest to newest)
        $bonds = Bond::with(['items', 'stack'])
            ->orderBy('date', 'asc')
            ->orderBy('bond_serial', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $spreadsheet = new Spreadsheet();
        
        // -------------------------------------------------------------
        // Main Sheet: Worksheet (Strictly ordered by bond date)
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
        $sheet->setCellValue('A2', 'مجموع تقارير السندات (مرتبة حسب تاريخ السند)');
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

        // Grouping stats for Sheet1
        $yearStats = [];

        foreach ($bonds as $bond) {
            $year = !empty($bond->date) ? substr(trim($bond->date), 0, 4) : '';
            $bgYearColor = $yearColors[$year] ?? 'F8F9FA';

            // Track year stats for Sheet1
            if ($year !== '') {
                if (!isset($yearStats[$year])) {
                    $yearStats[$year] = [
                        'count' => 0,
                        'min_date' => $bond->date,
                        'max_date' => $bond->date,
                    ];
                }
                $yearStats[$year]['count']++;
                $yearStats[$year]['max_date'] = $bond->date;
            }

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

            // 3. If multiple items, merge common fields vertically (creates inner table effect)
            if ($rowCount > 1) {
                $sheet->mergeCells("A{$bondStartRow}:A{$bondEndRow}");
                $sheet->mergeCells("B{$bondStartRow}:B{$bondEndRow}");
                $sheet->mergeCells("C{$bondStartRow}:C{$bondEndRow}");
                $sheet->mergeCells("D{$bondStartRow}:D{$bondEndRow}");
                $sheet->mergeCells("G{$bondStartRow}:G{$bondEndRow}");
                $sheet->mergeCells("H{$bondStartRow}:H{$bondEndRow}");
            }

            // 4. Background color covers the ENTIRE row (all columns A..H)
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

        // Apply data borders and alignment across the entire table
        $totalDataEndRow = $currentRow - 1;
        if ($totalDataEndRow >= 5) {
            $sheet->getStyle("A5:H{$totalDataEndRow}")->applyFromArray($dataBorders);
        }

        // -------------------------------------------------------------
        // Secondary Sheet: Sheet1 (Chronological Summary by Year)
        // -------------------------------------------------------------
        $sheet1 = $spreadsheet->createSheet();
        $sheet1->setTitle('Sheet1');
        $sheet1->setRightToLeft(true);

        $sheet1->getColumnDimension('A')->setWidth(15);
        $sheet1->getColumnDimension('B')->setWidth(20);
        $sheet1->getColumnDimension('C')->setWidth(20);
        $sheet1->getColumnDimension('D')->setWidth(18);

        $sheet1->setCellValue('A2', 'السنة');
        $sheet1->setCellValue('B2', 'تاريخ أول سند');
        $sheet1->setCellValue('C2', 'تاريخ آخر سند');
        $sheet1->setCellValue('D2', 'عدد السندات');

        $sheet1->getStyle('A2:D2')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        $sRow = 3;
        ksort($yearStats);
        foreach ($yearStats as $yr => $st) {
            $sheet1->setCellValue("A{$sRow}", $yr);
            $sheet1->setCellValue("B{$sRow}", $st['min_date']);
            $sheet1->setCellValue("C{$sRow}", $st['max_date']);
            $sheet1->setCellValue("D{$sRow}", $st['count']);

            $yrColor = $yearColors[$yr] ?? 'F8F9FA';
            $sheet1->getStyle("A{$sRow}:D{$sRow}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $yrColor]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
            ]);
            $sRow++;
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
