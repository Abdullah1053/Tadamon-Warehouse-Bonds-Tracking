<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\ReceiverNormalizationService;

class ReceiverNormalizationTest extends TestCase
{
    private ReceiverNormalizationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ReceiverNormalizationService();
    }

    public function test_arabic_normalization_handles_variations()
    {
        // Alef variations
        $this->assertEquals('احمد', $this->service->normalizeArabic('أحمد'));
        $this->assertEquals('احمد', $this->service->normalizeArabic('إحمد'));
        $this->assertEquals('احمد', $this->service->normalizeArabic('آحمد'));

        // Ta Marbuta vs Haa
        $this->assertEquals('محطه البدر', $this->service->normalizeArabic('محطة البدر'));

        // Alef Maqsura vs Yaa
        $this->assertEquals('مهدي', $this->service->normalizeArabic('مهدى'));

        // Extra whitespace & diacritics
        $this->assertEquals('محطة البدر', $this->service->normalizeArabic('  مَحَطَّةُ   البَدْرِ  '));
    }

    public function test_core_entity_stripping()
    {
        $this->assertEquals('بدر', $this->service->getCoreEntityName('محطه البدر'));
        $this->assertEquals('سعيد الشوافي', $this->service->getCoreEntityName('محلات سعيد الشوافي'));
        $this->assertEquals('الامل', $this->service->getCoreEntityName('شركه الامل'));
    }

    public function test_similarity_calculation()
    {
        // Exact normalized match (100%)
        $sim1 = $this->service->calculateSimilarity('محطة البدر', 'محطه البدر');
        $this->assertEquals(100.0, $sim1);

        $sim2 = $this->service->calculateSimilarity('أحمد مرشد', 'احمد مرشد');
        $this->assertEquals(100.0, $sim2);

        // Prefix difference match (>= 90%)
        $sim3 = $this->service->calculateSimilarity('محلات القعود', 'القعود');
        $this->assertGreaterThanOrEqual(90.0, $sim3);

        // Minor typo match (>= 75%)
        $sim4 = $this->service->calculateSimilarity('محمد راشد', 'محمد راشد.');
        $this->assertGreaterThanOrEqual(90.0, $sim4);

        // Completely different names should have low similarity (< 50%)
        $sim5 = $this->service->calculateSimilarity('محطة البدر', 'شركة النصر');
        $this->assertLessThan(50.0, $sim5);
    }
}
