<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Exports\AllStacksExport;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Calculation\Calculation;

class OperationalAuditExportTest extends TestCase
{
    public function test_all_stacks_export_includes_operational_audit_sheet_with_all_requirements()
    {
        // Subclass to inject test bonds without needing active DB connection
        $export = new class extends AllStacksExport {
            public function buildTestSpreadsheet($mockBonds)
            {
                $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

                // 1. Worksheet
                $sheet = $spreadsheet->getActiveSheet();
                $sheet->setTitle('Worksheet');
                $sheet->setRightToLeft(true);

                // 2. Sheet1
                $sheet1 = $spreadsheet->createSheet();
                $sheet1->setTitle('Sheet1');
                $sheet1->setRightToLeft(true);

                $yearPalettes = [
                    '2019' => ['base' => 'ECEFF1'],
                    '2020' => ['base' => 'D9EBF8'],
                    '2021' => ['base' => 'E2F5DC'],
                    '2022' => ['base' => 'FEF3D4'],
                    '2023' => ['base' => 'EDE1FB'],
                    '2024' => ['base' => 'FFE8DD'],
                    '2025' => ['base' => 'D8F3EF'],
                    '2026' => ['base' => 'FBE4E9'],
                ];
                $fallbackPalettes = [
                    ['base' => 'F8F9FA'],
                ];

                // 3. Operational_Audit
                $this->buildOperationalAuditSheet($spreadsheet, $mockBonds, $yearPalettes, $fallbackPalettes);

                $spreadsheet->setActiveSheetIndex(0);
                return $spreadsheet;
            }
        };

        $mockBonds = collect([
            (object)['date' => '2019-10-15', 'bond_serial' => '1001', 'isCancelled' => fn() => false, 'isMissing' => fn() => false],
            (object)['date' => '2020-02-10', 'bond_serial' => '1050', 'isCancelled' => fn() => false, 'isMissing' => fn() => false],
            (object)['date' => '2020-09-15', 'bond_serial' => '1120', 'isCancelled' => fn() => false, 'isMissing' => fn() => false],
            (object)['date' => '2026-05-01', 'bond_serial' => '2001', 'isCancelled' => fn() => false, 'isMissing' => fn() => false],
        ]);

        $spreadsheet = $export->buildTestSpreadsheet($mockBonds);

        // Check sheets existence and names
        $this->assertEquals(['Worksheet', 'Sheet1', 'Operational_Audit'], $spreadsheet->getSheetNames());

        $auditSheet = $spreadsheet->getSheetByName('Operational_Audit');
        $this->assertNotNull($auditSheet);

        // Check Right-to-Left
        $this->assertTrue($auditSheet->getRightToLeft());

        // Check Headers in Row 3 (Columns A to K)
        $this->assertEquals('السنة', $auditSheet->getCell('A3')->getValue());
        $this->assertEquals('رقم الفترة', $auditSheet->getCell('B3')->getValue());
        $this->assertEquals('تاريخ البداية', $auditSheet->getCell('C3')->getValue());
        $this->assertEquals('تاريخ النهاية', $auditSheet->getCell('D3')->getValue());
        $this->assertEquals('أيام العمل الفعلية', $auditSheet->getCell('E3')->getValue());
        $this->assertEquals('أيام التوقف السابقة', $auditSheet->getCell('F3')->getValue());
        $this->assertEquals('عدد السندات الفعالة', $auditSheet->getCell('G3')->getValue());
        $this->assertEquals('سند مطلوب؟', $auditSheet->getCell('H3')->getValue());
        $this->assertEquals('يتوفر سند؟', $auditSheet->getCell('I3')->getValue());
        $this->assertEquals('حالة التدقيق', $auditSheet->getCell('J3')->getValue());
        $this->assertEquals('ملاحظات التشغيل ونطاق السندات', $auditSheet->getCell('K3')->getValue());

        // Check Row 5 (2019)
        $this->assertEquals('2019', $auditSheet->getCell('A5')->getValue());
        $this->assertEquals('1', $auditSheet->getCell('B5')->getValue());
        $this->assertEquals('=D5-C5+1', $auditSheet->getCell('E5')->getValue());
        $this->assertEquals('=IF(ROW()=5, 0, MAX(0, C5-D4-1))', $auditSheet->getCell('F5')->getValue());
        $this->assertEquals('=IF(C5>=DATE(2025,1,26),"Yes","No")', $auditSheet->getCell('H5')->getValue());

        // Check Row 7 (2020 Period 2 with 98-day idle gap)
        $this->assertEquals('2020', $auditSheet->getCell('A7')->getValue());
        $this->assertEquals('2', $auditSheet->getCell('B7')->getValue());

        // Check Data Validation on Column I
        $validation = $auditSheet->getCell('I5')->getDataValidation();
        $this->assertEquals(DataValidation::TYPE_LIST, $validation->getType());
        $this->assertEquals('"Yes,No"', $validation->getFormula1());

        // Check Conditional Formatting
        $styles = $auditSheet->getConditionalStyles('A5:K14');
        $this->assertNotEmpty($styles);
        $this->assertEquals(['=$J5<>"Valid"'], $styles[0]->getConditions());

        // Calculate formulas and test values
        Calculation::getInstance($spreadsheet)->disableCalculationCache();
        $this->assertEquals(122, $auditSheet->getCell('E5')->getCalculatedValue());
        $this->assertEquals(0, $auditSheet->getCell('F5')->getCalculatedValue());
        $this->assertEquals('No', $auditSheet->getCell('H5')->getCalculatedValue());
        $this->assertEquals('Valid', $auditSheet->getCell('J5')->getCalculatedValue());

        // 2020 Period 2: 98 idle days
        $this->assertEquals(98, $auditSheet->getCell('F7')->getCalculatedValue());
        $this->assertEquals('Valid', $auditSheet->getCell('J7')->getCalculatedValue());

        // 2022 Period 2: 91 idle days
        $this->assertEquals(91, $auditSheet->getCell('F10')->getCalculatedValue());
        $this->assertEquals('Valid', $auditSheet->getCell('J10')->getCalculatedValue());

        // 2026: Voucher required = Yes
        $this->assertEquals('Yes', $auditSheet->getCell('H14')->getCalculatedValue());
        $this->assertEquals('Valid', $auditSheet->getCell('J14')->getCalculatedValue());

        // If 2026 has voucher set to No, Audit_Status changes to 'Missing Voucher (سند مطلوب)'
        $auditSheet->setCellValue('I14', 'No');
        $this->assertEquals('Missing Voucher (سند مطلوب)', $auditSheet->getCell('J14')->getCalculatedValue());
    }
}
