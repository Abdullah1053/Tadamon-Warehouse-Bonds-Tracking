<?php

namespace App\Exports;

use App\Models\Bond;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Conditional;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

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
            'H' => 45.00, // رابط السند
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

        // Year color families with alternating shades (shade_1: lighter, shade_2: slightly darker)
        $yearPalettes = [
            '2019' => [
                'name'    => 'Slate Silver / Neutral',
                'shade_1' => 'F4F6F8', // Light slate silver
                'shade_2' => 'E1E6EB', // Slightly darker slate silver
                'base'    => 'ECEFF1', // Summary sheet base
            ],
            '2020' => [
                'name'    => 'Sky Blue',
                'shade_1' => 'E8F4FD', // Light sky-blue
                'shade_2' => 'D0E8F9', // Slightly darker sky-blue
                'base'    => 'D9EBF8', // Summary sheet base
            ],
            '2021' => [
                'name'    => 'Apple Green',
                'shade_1' => 'EEF9EB', // Light apple-green
                'shade_2' => 'D6F2CF', // Slightly darker apple-green
                'base'    => 'E2F5DC',
            ],
            '2022' => [
                'name'    => 'Warm Amber / Gold',
                'shade_1' => 'FFF9E6', // Light warm amber/gold
                'shade_2' => 'FEEEC2', // Slightly darker amber/gold
                'base'    => 'FEF3D4',
            ],
            '2023' => [
                'name'    => 'Lavender / Purple',
                'shade_1' => 'F5EEFD', // Light lavender
                'shade_2' => 'E5D4FA', // Slightly darker lavender
                'base'    => 'EDE1FB',
            ],
            '2024' => [
                'name'    => 'Peach / Coral',
                'shade_1' => 'FFF2EB', // Light peach
                'shade_2' => 'FFDFD0', // Slightly darker peach/coral
                'base'    => 'FFE8DD',
            ],
            '2025' => [
                'name'    => 'Mint / Soft Teal',
                'shade_1' => 'E6F8F5', // Light mint/teal
                'shade_2' => 'C9EFE9', // Slightly darker mint/teal
                'base'    => 'D8F3EF',
            ],
            '2026' => [
                'name'    => 'Soft Rose / Mauve',
                'shade_1' => 'FDF0F3', // Light rose blush
                'shade_2' => 'F8D8DF', // Slightly darker rose
                'base'    => 'FBE4E9',
            ],
        ];

        // Fallback palette rotation for any unexpected year
        $fallbackPalettes = [
            ['shade_1' => 'F8F9FA', 'shade_2' => 'EDF2F7', 'base' => 'E2E8F0'],
            ['shade_1' => 'F0F4F8', 'shade_2' => 'D9E2EC', 'base' => 'BCCCDC'],
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

        // Missing document font styling (bold dark red warning text)
        $missingFont = [
            'font' => [
                'name' => 'Calibri',
                'bold' => true,
                'size' => 11,
                'color' => ['rgb' => '9C0006'],
            ],
        ];

        $hyperlinkRanges = [];
        $missingRanges = [];
        $yearDataBlocks = [];

        $previousYear = null;
        $bondIndexInYear = 0;
        $yearStartRow = 5;

        // Grouping stats for Sheet1
        $yearStats = [];

        foreach ($bonds as $bond) {
            $year = !empty($bond->date) ? substr(trim($bond->date), 0, 4) : 'Unknown';

            // 1. Visual separation between years: insert a blank spacer row when year changes
            if ($previousYear !== null && $year !== $previousYear) {
                $yearDataBlocks[] = [
                    'start' => $yearStartRow,
                    'end'   => $currentRow - 1,
                    'year'  => $previousYear,
                ];

                // Clear blank spacer row between the two years (no fill, no borders)
                $spacerRow = $currentRow;
                $sheet->getRowDimension($spacerRow)->setRowHeight(20);
                $currentRow++;

                // Reset year start row and alternating bond index for the new year
                $yearStartRow = $currentRow;
                $bondIndexInYear = 0;
            }

            $previousYear = $year;

            // Track stats for Sheet1
            if (!isset($yearStats[$year])) {
                $yearStats[$year] = [
                    'count'     => 0,
                    'missing'   => 0,
                    'cancelled' => 0,
                    'normal'    => 0,
                    'min_date'  => $bond->date,
                    'max_date'  => $bond->date,
                ];
            }
            $yearStats[$year]['count']++;
            $yearStats[$year]['max_date'] = $bond->date;

            $isMissing = $bond->isMissing();
            $isCancelled = $bond->isCancelled();

            if ($isMissing) {
                $yearStats[$year]['missing']++;
            } elseif ($isCancelled) {
                $yearStats[$year]['cancelled']++;
            } else {
                $yearStats[$year]['normal']++;
            }

            // Determine row background color:
            // If missing, use high-visibility warning highlight (Soft Alert Red)
            // Otherwise, alternate between shade_1 and shade_2 of the current year's palette
            $palette = $yearPalettes[$year] ?? $fallbackPalettes[abs(crc32($year)) % count($fallbackPalettes)];
            if ($isMissing) {
                $rowBgColor = 'FFC7CE'; // High-visibility Warning Red
            } else {
                $rowBgColor = ($bondIndexInYear % 2 === 0) ? $palette['shade_1'] : $palette['shade_2'];
            }

            $fullUrl = $bond->image_url ?? '';
            $items = $bond->items;

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

            if (!empty($fullUrl)) {
                $escapedUrl = str_replace('"', '""', $fullUrl);
                // Set formula =HYPERLINK("url", "url") so Excel immediately recognizes it as an active clickable hyperlink
                $sheet->setCellValue("H{$bondStartRow}", '=HYPERLINK("' . $escapedUrl . '", "' . $escapedUrl . '")');
            } else {
                $sheet->setCellValue("H{$bondStartRow}", '---');
            }

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
                    'startColor' => ['rgb' => $rowBgColor],
                ]
            ]);

            // Track missing records for special font styling (bold dark red text)
            if ($isMissing) {
                $missingRanges[] = "A{$bondStartRow}:H{$bondEndRow}";
            }

            // 5. Setup clickable hyperlinks on Link (Col H)
            if (!empty($fullUrl)) {
                for ($r = $bondStartRow; $r <= $bondEndRow; $r++) {
                    $sheet->getCell("H{$r}")->getHyperlink()->setUrl($fullUrl);
                    $sheet->getCell("H{$r}")->getHyperlink()->setTooltip('عرض صورة السند: ' . $fullUrl);
                }
                $hyperlinkRanges[] = "H{$bondStartRow}:H{$bondEndRow}";
            }

            $bondIndexInYear++;
            $currentRow = $bondEndRow + 1;
        }

        // Record the last year data block
        if ($previousYear !== null) {
            $yearDataBlocks[] = [
                'start' => $yearStartRow,
                'end'   => $currentRow - 1,
                'year'  => $previousYear,
            ];
        }

        // Apply data borders to each year block separately (keeps spacer rows cleanly blank and unbordered)
        foreach ($yearDataBlocks as $block) {
            if ($block['end'] >= $block['start']) {
                $sheet->getStyle("A{$block['start']}:H{$block['end']}")->applyFromArray($dataBorders);
            }
        }

        // Apply missing document special font styling (bold dark red text)
        foreach ($missingRanges as $mRange) {
            $sheet->getStyle($mRange)->applyFromArray($missingFont);
        }

        // Apply Hyperlink styles AFTER dataBorders so font color (#0563C1) and underline are preserved
        foreach ($hyperlinkRanges as $hRange) {
            $sheet->getStyle($hRange)->applyFromArray($hyperlinkStyle);
        }

        // -------------------------------------------------------------
        // Secondary Sheet: Sheet1 (Chronological Summary by Year)
        // -------------------------------------------------------------
        $sheet1 = $spreadsheet->createSheet();
        $sheet1->setTitle('Sheet1');
        $sheet1->setRightToLeft(true);

        $sheet1->getColumnDimension('A')->setWidth(15);
        $sheet1->getColumnDimension('B')->setWidth(18);
        $sheet1->getColumnDimension('C')->setWidth(18);
        $sheet1->getColumnDimension('D')->setWidth(16);
        $sheet1->getColumnDimension('E')->setWidth(18);
        $sheet1->getColumnDimension('F')->setWidth(18);
        $sheet1->getColumnDimension('G')->setWidth(18);

        $sheet1Headers = [
            'A' => 'السنة',
            'B' => 'تاريخ أول سند',
            'C' => 'تاريخ آخر سند',
            'D' => 'إجمالي السندات',
            'E' => 'السندات المفقودة',
            'F' => 'السندات الملغية',
            'G' => 'السندات السليمة',
        ];
        $sheet1->getRowDimension(2)->setRowHeight(25);
        foreach ($sheet1Headers as $col => $title) {
            $sheet1->setCellValue("{$col}2", $title);
        }

        $sheet1->getStyle('A2:G2')->applyFromArray([
            'font' => ['name' => 'Calibri', 'bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        $sRow = 3;
        ksort($yearStats);
        $totalBonds = 0;
        $totalMissing = 0;
        $totalCancelled = 0;
        $totalNormal = 0;
        $globalMinDate = null;
        $globalMaxDate = null;

        foreach ($yearStats as $yr => $st) {
            $sheet1->setCellValue("A{$sRow}", $yr);
            $sheet1->setCellValue("B{$sRow}", $st['min_date']);
            $sheet1->setCellValue("C{$sRow}", $st['max_date']);
            $sheet1->setCellValue("D{$sRow}", $st['count']);
            $sheet1->setCellValue("E{$sRow}", $st['missing']);
            $sheet1->setCellValue("F{$sRow}", $st['cancelled']);
            $sheet1->setCellValue("G{$sRow}", $st['normal']);

            $totalBonds += $st['count'];
            $totalMissing += $st['missing'];
            $totalCancelled += $st['cancelled'];
            $totalNormal += $st['normal'];
            if ($globalMinDate === null || $st['min_date'] < $globalMinDate) $globalMinDate = $st['min_date'];
            if ($globalMaxDate === null || $st['max_date'] > $globalMaxDate) $globalMaxDate = $st['max_date'];

            $p = $yearPalettes[$yr] ?? $fallbackPalettes[0];
            $baseColor = $p['base'] ?? 'F8F9FA';

            $sheet1->getStyle("A{$sRow}:G{$sRow}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $baseColor]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'font' => ['name' => 'Calibri', 'size' => 11],
            ]);

            // If year has missing bonds, highlight the missing count cell in red
            if ($st['missing'] > 0) {
                $sheet1->getStyle("E{$sRow}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC7CE']],
                    'font' => ['bold' => true, 'color' => ['rgb' => '9C0006']],
                ]);
            }

            $sRow++;
        }

        // Grand Total row at the bottom of Sheet1
        $sheet1->setCellValue("A{$sRow}", 'المجموع الكلي');
        $sheet1->setCellValue("B{$sRow}", $globalMinDate);
        $sheet1->setCellValue("C{$sRow}", $globalMaxDate);
        $sheet1->setCellValue("D{$sRow}", $totalBonds);
        $sheet1->setCellValue("E{$sRow}", $totalMissing);
        $sheet1->setCellValue("F{$sRow}", $totalCancelled);
        $sheet1->setCellValue("G{$sRow}", $totalNormal);

        $sheet1->getStyle("A{$sRow}:G{$sRow}")->applyFromArray([
            'font' => ['name' => 'Calibri', 'bold' => true, 'size' => 11, 'color' => ['rgb' => '1F4E78']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => [
                'top' => ['borderStyle' => Border::BORDER_THIN],
                'bottom' => ['borderStyle' => Border::BORDER_DOUBLE],
                'left' => ['borderStyle' => Border::BORDER_THIN],
                'right' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);

        if ($totalMissing > 0) {
            $sheet1->getStyle("E{$sRow}")->applyFromArray([
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFC7CE']],
                'font' => ['bold' => true, 'color' => ['rgb' => '9C0006']],
            ]);
        }

        // -------------------------------------------------------------
        // 3rd Sheet: Operational_Audit (Operational Audit & Work Log)
        // -------------------------------------------------------------
        $this->buildOperationalAuditSheet($spreadsheet, $bonds, $yearPalettes, $fallbackPalettes);

        // Set Worksheet as active sheet
        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    /**
     * Build the 3rd sheet: Operational_Audit (سجل فترات التشغيل والتدقيق الرقابي)
     * Includes chronological operational intervals, native Excel calculation formulas,
     * user dropdown data validation for voucher availability, master audit status validation,
     * and conditional formatting highlighting non-valid rows.
     */
    protected function buildOperationalAuditSheet(
        Spreadsheet $spreadsheet,
        $bonds,
        array $yearPalettes,
        array $fallbackPalettes
    ): void {
        $sheetAudit = $spreadsheet->createSheet();
        $sheetAudit->setTitle('Operational_Audit');
        $sheetAudit->setRightToLeft(true);

        // Column widths for A to K
        $auditColWidths = [
            'A' => 13.00, // Year (السنة)
            'B' => 15.00, // Period_Number (رقم الفترة)
            'C' => 17.00, // Start_Date (تاريخ البداية)
            'D' => 17.00, // End_Date (تاريخ النهاية)
            'E' => 18.00, // Active_Days (أيام العمل الفعلية)
            'F' => 20.00, // Idle_Days_Before (أيام التوقف السابقة)
            'G' => 22.00, // DB_Bonds_Count (عدد السندات الفعالة في النظام ضمن النطاق)
            'H' => 17.00, // Voucher_Required (سند مطلوب؟)
            'I' => 16.00, // Has_Voucher (يتوفر سند؟)
            'J' => 32.00, // Audit_Status (حالة التدقيق)
            'K' => 50.00, // Operational_Notes (ملاحظات التشغيل ونطاق السندات)
        ];
        foreach ($auditColWidths as $col => $w) {
            $sheetAudit->getColumnDimension($col)->setWidth($w);
        }

        // Row 2: Title Row (A2:K2 Merged)
        $sheetAudit->mergeCells('A2:K2');
        $sheetAudit->setCellValue('A2', 'سجل فترات التشغيل والتدقيق الرقابي (Operational Audit & Work Log)');
        $sheetAudit->getRowDimension(2)->setRowHeight(40);
        $sheetAudit->getStyle('A2:K2')->applyFromArray([
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
            'A' => 'السنة',
            'B' => 'رقم الفترة',
            'C' => 'تاريخ البداية',
            'D' => 'تاريخ النهاية',
            'E' => 'أيام العمل الفعلية',
            'F' => 'أيام التوقف السابقة',
            'G' => 'عدد السندات الفعالة',
            'H' => 'سند مطلوب؟',
            'I' => 'يتوفر سند؟',
            'J' => 'حالة التدقيق',
            'K' => 'ملاحظات التشغيل ونطاق السندات',
        ];
        $sheetAudit->getRowDimension(3)->setRowHeight(28);
        foreach ($headers as $col => $title) {
            $sheetAudit->setCellValue("{$col}3", $title);
        }
        $sheetAudit->getStyle('A3:K3')->applyFromArray([
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
        $sheetAudit->getRowDimension(4)->setRowHeight(20);

        // 1. Baseline Historical Intervals
        $historicalIntervals = [
            [
                'year' => 2019,
                'period_num' => 1,
                'start_date' => '2019-09-01',
                'end_date' => '2019-12-31',
                'notes' => 'فترة التشغيل الأولية لعام 2019',
            ],
            [
                'year' => 2020,
                'period_num' => 1,
                'start_date' => '2020-01-01',
                'end_date' => '2020-04-19',
                'notes' => 'فترة التشغيل الأولى لعام 2020 (قبل فترة التوقف)',
            ],
            [
                'year' => 2020,
                'period_num' => 2,
                'start_date' => '2020-07-27',
                'end_date' => '2020-12-31',
                'notes' => 'فترة التشغيل الثانية لعام 2020 (توقف تشغيلي معتمد 98 يوماً بين الفترتين)',
            ],
            [
                'year' => 2021,
                'period_num' => 1,
                'start_date' => '2021-01-01',
                'end_date' => '2021-08-21',
                'notes' => 'فترة التشغيل لعام 2021',
            ],
            [
                'year' => 2022,
                'period_num' => 1,
                'start_date' => '2022-05-01',
                'end_date' => '2022-09-04',
                'notes' => 'فترة التشغيل الأولى لعام 2022',
            ],
            [
                'year' => 2022,
                'period_num' => 2,
                'start_date' => '2022-12-05',
                'end_date' => '2022-12-31',
                'notes' => 'فترة التشغيل الثانية لعام 2022 (توقف تشغيلي معتمد 91 يوماً بين الفترتين)',
            ],
            [
                'year' => 2023,
                'period_num' => 1,
                'start_date' => '2023-01-01',
                'end_date' => '2023-08-20',
                'notes' => 'فترة التشغيل لعام 2023',
            ],
        ];

        $allPeriods = $historicalIntervals;

        // 2. Dynamic Intervals for 2024–2026 (dynamically aligned based on database bond dates)
        $dynamicYears = [2024, 2025, 2026];
        foreach ($dynamicYears as $dYear) {
            $yearBonds = $bonds->filter(function ($b) use ($dYear) {
                $date = $b->date ?? '';
                return !empty($date) && str_starts_with(trim($date), (string)$dYear);
            })->sortBy('date')->values();

            if ($dYear === 2026) {
                // 2026 specifically enforces the strict contract window between 2026-04-12 and 2026-08-24
                if ($yearBonds->isNotEmpty()) {
                    $minDate = $yearBonds->first()->date;
                    $maxDate = $yearBonds->last()->date;
                    $allPeriods[] = [
                        'year' => 2026,
                        'period_num' => 1,
                        'start_date' => $minDate,
                        'end_date' => $maxDate,
                        'notes' => 'سندات 2026 الفعلية في النظام (النافذة التعاقدية المعتمدة: 2026-04-12 إلى 2026-08-24)',
                    ];
                } else {
                    $allPeriods[] = [
                        'year' => 2026,
                        'period_num' => 1,
                        'start_date' => '2026-04-12',
                        'end_date' => '2026-08-24',
                        'notes' => 'نافذة العمل التعاقدية المعتمدة لعام 2026 (2026-04-12 إلى 2026-08-24)',
                    ];
                }
            } elseif ($dYear === 2025) {
                if ($yearBonds->isNotEmpty()) {
                    $minDate = $yearBonds->first()->date;
                    $maxDate = $yearBonds->last()->date;
                    $allPeriods[] = [
                        'year' => 2025,
                        'period_num' => 1,
                        'start_date' => $minDate,
                        'end_date' => $maxDate,
                        'notes' => 'فترة التشغيل الفعلية لعام 2025 (إلزامية السند من تاريخ 2025-01-26)',
                    ];
                } else {
                    $allPeriods[] = [
                        'year' => 2025,
                        'period_num' => 1,
                        'start_date' => '2025-01-26',
                        'end_date' => '2025-12-31',
                        'notes' => 'فترة التشغيل لعام 2025 (إلزامية السند من تاريخ 2025-01-26)',
                    ];
                }
            } elseif ($dYear === 2024) {
                if ($yearBonds->isNotEmpty()) {
                    $minDate = $yearBonds->first()->date;
                    $maxDate = $yearBonds->last()->date;
                    $allPeriods[] = [
                        'year' => 2024,
                        'period_num' => 1,
                        'start_date' => $minDate,
                        'end_date' => $maxDate,
                        'notes' => 'فترة التشغيل الفعلية لعام 2024',
                    ];
                } else {
                    $allPeriods[] = [
                        'year' => 2024,
                        'period_num' => 1,
                        'start_date' => '2024-01-01',
                        'end_date' => '2024-12-31',
                        'notes' => 'فترة التشغيل لعام 2024',
                    ];
                }
            }
        }

        // 3. Populate Data Rows starting at Row 5
        $r = 5;
        foreach ($allPeriods as $p) {
            $prevRow = $r - 1;

            // [A] Year (السنة)
            $sheetAudit->setCellValueExplicit("A{$r}", (string)$p['year'], DataType::TYPE_NUMERIC);

            // [B] Period_Number (رقم الفترة)
            $sheetAudit->setCellValueExplicit("B{$r}", (string)$p['period_num'], DataType::TYPE_NUMERIC);

            // [C] Start_Date & [D] End_Date (formatted YYYY-MM-DD)
            $sheetAudit->setCellValue("C{$r}", Date::stringToExcel($p['start_date']));
            $sheetAudit->setCellValue("D{$r}", Date::stringToExcel($p['end_date']));
            $sheetAudit->getStyle("C{$r}:D{$r}")->getNumberFormat()->setFormatCode('yyyy-mm-dd');

            // [E] Active_Days (أيام العمل الفعلية - Formula: =D{row}-C{row}+1)
            $sheetAudit->setCellValue("E{$r}", "=D{$r}-C{$r}+1");

            // [F] Idle_Days_Before (أيام التوقف السابقة - Formula: =IF(ROW()=5, 0, MAX(0, C{row}-D{prevRow}-1)))
            $sheetAudit->setCellValue("F{$r}", "=IF(ROW()=5, 0, MAX(0, C{$r}-D{$prevRow}-1))");

            // [G] DB_Bonds_Count (عدد السندات الفعالة في النظام ضمن النطاق)
            $activeBondsInPeriod = $bonds->filter(function ($b) use ($p) {
                if (empty($b->date)) return false;
                $d = trim($b->date);
                if ($d < $p['start_date'] || $d > $p['end_date']) return false;
                $cancelled = method_exists($b, 'isCancelled') ? $b->isCancelled() : false;
                $missing = method_exists($b, 'isMissing') ? $b->isMissing() : false;
                return !$cancelled && !$missing;
            });
            $bondsCount = $activeBondsInPeriod->count();
            $sheetAudit->setCellValueExplicit("G{$r}", (string)$bondsCount, DataType::TYPE_NUMERIC);

            $sheetAudit->getStyle("E{$r}:G{$r}")->getNumberFormat()->setFormatCode('#,##0');

            // [H] Voucher_Required (سند مطلوب؟ - Formula: =IF(C{row}>=DATE(2025,1,26),"Yes","No"))
            $sheetAudit->setCellValue("H{$r}", "=IF(C{$r}>=DATE(2025,1,26),\"Yes\",\"No\")");

            // [I] Has_Voucher (يتوفر سند؟ - User input/dropdown: "Yes" or "No")
            $hasVoucherDefault = ($p['start_date'] >= '2025-01-26') ? 'Yes' : 'No';
            $sheetAudit->setCellValue("I{$r}", $hasVoucherDefault);

            $validation = $sheetAudit->getCell("I{$r}")->getDataValidation();
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('قيمة غير صحيحة');
            $validation->setError('يرجى اختيار Yes أو No من القائمة المنسدلة.');
            $validation->setPromptTitle('توفر السند');
            $validation->setPrompt('اختر Yes في حال توفر السند أو No في حال عدم توفره.');
            $validation->setFormula1('"Yes,No"');

            // [J] Audit_Status (حالة التدقيق - Master formula combining gap checks, 2025 voucher requirements, and 2026 contract window rules)
            $auditFormula = "=IF(AND(OR(A{$r}=2026,A{$r}=\"2026\",YEAR(C{$r})=2026),C{$r}<DATE(2026,4,12)),\"Out of Bounds: Early Work\","
                . "IF(AND(OR(A{$r}=2026,A{$r}=\"2026\",YEAR(C{$r})=2026),D{$r}>DATE(2026,8,24)),\"Out of Bounds: Late Work\","
                . "IF(AND(H{$r}=\"Yes\",I{$r}<>\"Yes\"),\"Missing Voucher (سند مطلوب)\","
                . "IF(OR(C{$r}>D{$r},AND(ROW()>5,OR(C{$r}<=D{$prevRow},AND(F{$r}>0,K{$r}=\"\")))),\"Review: Unaccounted Gap\",\"Valid\"))))";
            $sheetAudit->setCellValue("J{$r}", $auditFormula);

            // [K] Operational_Notes (ملاحظات التشغيل ونطاق السندات)
            $notesText = $p['notes'];
            if ($bondsCount > 0) {
                $minSerial = $activeBondsInPeriod->min('bond_serial');
                $maxSerial = $activeBondsInPeriod->max('bond_serial');
                $notesText .= " (السندات: #{$minSerial} إلى #{$maxSerial})";
            }
            $sheetAudit->setCellValue("K{$r}", $notesText);

            // Row styling based on year palette
            $yrKey = (string)$p['year'];
            $palette = $yearPalettes[$yrKey] ?? $fallbackPalettes[abs(crc32($yrKey)) % count($fallbackPalettes)];
            $rowBgColor = $palette['base'] ?? 'F8F9FA';

            $sheetAudit->getStyle("A{$r}:K{$r}")->applyFromArray([
                'font' => ['name' => 'Calibri', 'size' => 11],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rowBgColor]],
            ]);
            // Arabic notes aligned right for comfortable readability
            $sheetAudit->getStyle("K{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheetAudit->getRowDimension($r)->setRowHeight(24);

            $r++;
        }

        $lastDataRow = $r - 1;

        // Grand Totals Summary Row
        $sheetAudit->setCellValue("A{$r}", 'المجموع الكلي');
        $sheetAudit->mergeCells("A{$r}:D{$r}");
        $sheetAudit->setCellValue("E{$r}", "=SUM(E5:E{$lastDataRow})");
        $sheetAudit->setCellValue("F{$r}", "=SUM(F5:F{$lastDataRow})");
        $sheetAudit->setCellValue("G{$r}", "=SUM(G5:G{$lastDataRow})");
        $sheetAudit->setCellValue("H{$r}", '---');
        $sheetAudit->setCellValue("I{$r}", '---');
        $sheetAudit->setCellValue("J{$r}", '---');
        $sheetAudit->setCellValue("K{$r}", 'ملخص إجمالي أيام العمل وأيام التوقف وعدد السندات لكافة الفترات التشغيلية');

        $sheetAudit->getStyle("A{$r}:K{$r}")->applyFromArray([
            'font' => ['name' => 'Calibri', 'bold' => true, 'size' => 11, 'color' => ['rgb' => '1F4E78']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D9E1F2']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => [
                'top' => ['borderStyle' => Border::BORDER_THIN],
                'bottom' => ['borderStyle' => Border::BORDER_DOUBLE],
                'left' => ['borderStyle' => Border::BORDER_THIN],
                'right' => ['borderStyle' => Border::BORDER_THIN],
            ],
        ]);
        $sheetAudit->getStyle("E{$r}:G{$r}")->getNumberFormat()->setFormatCode('#,##0');
        $sheetAudit->getRowDimension($r)->setRowHeight(26);

        // Conditional Formatting: highlight entire row in light red (#FFC7CE) with dark red text (#9C0006) when [Audit_Status] <> "Valid"
        $conditional = new Conditional();
        $conditional->setConditionType(Conditional::CONDITION_EXPRESSION);
        $conditional->setConditions(['=$J5<>"Valid"']);
        $conditional->getStyle()->getFill()->setFillType(Fill::FILL_SOLID);
        $conditional->getStyle()->getFill()->getStartColor()->setARGB('FFFFC7CE');
        $conditional->getStyle()->getFill()->getEndColor()->setARGB('FFFFC7CE');
        $conditional->getStyle()->getFont()->getColor()->setARGB('FF9C0006');
        $conditional->getStyle()->getFont()->setBold(true);

        $conditionalStyles = $sheetAudit->getConditionalStyles("A5:K{$lastDataRow}");
        $conditionalStyles[] = $conditional;
        $sheetAudit->setConditionalStyles("A5:K{$lastDataRow}", $conditionalStyles);
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
