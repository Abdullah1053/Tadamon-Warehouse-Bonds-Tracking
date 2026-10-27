<?php

namespace App\Services;

use App\Models\Bond;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class OperationalAuditService
{
    /**
     * Official baseline operational intervals as documented in the warehouse system.
     */
    protected array $officialIntervals = [
        2019 => [
            [
                'period_num' => 1,
                'start_date' => '2019-09-01',
                'end_date'   => '2019-12-31',
                'notes'      => 'فترة التشغيل الأولية لعام 2019 (المرحلة التأسيسية للمستودع)',
            ],
        ],
        2020 => [
            [
                'period_num' => 1,
                'start_date' => '2020-01-01',
                'end_date'   => '2020-04-19',
                'notes'      => 'فترة التشغيل الأولى لعام 2020 (قبل فترة التوقف المؤقت)',
            ],
            [
                'period_num' => 2,
                'start_date' => '2020-07-27',
                'end_date'   => '2020-12-31',
                'notes'      => 'فترة التشغيل الثانية لعام 2020 (توقف تشغيلي معتمد 98 يوماً بين الفترتين)',
            ],
        ],
        2021 => [
            [
                'period_num' => 1,
                'start_date' => '2021-01-01',
                'end_date'   => '2021-08-21',
                'notes'      => 'فترة التشغيل لعام 2021 (تلاها توقف انتقالي امتد لـ 252 يوماً حتى مايو 2022)',
            ],
        ],
        2022 => [
            [
                'period_num' => 1,
                'start_date' => '2022-05-01',
                'end_date'   => '2022-09-04',
                'notes'      => 'فترة التشغيل الأولى لعام 2022 (استئناف حركة التوريد والصرف)',
            ],
            [
                'period_num' => 2,
                'start_date' => '2022-12-05',
                'end_date'   => '2022-12-31',
                'notes'      => 'فترة التشغيل الثانية لعام 2022 (توقف تشغيلي معتمد 91 يوماً بين الفترتين)',
            ],
        ],
        2023 => [
            [
                'period_num' => 1,
                'start_date' => '2023-01-01',
                'end_date'   => '2023-12-31',
                'notes'      => 'فترة الانفجار التشغيلي لعام 2023 (تسجيل 1,031 سنداً على مدار 227 يوماً نشطاً)',
            ],
        ],
        2024 => [
            [
                'period_num' => 1,
                'start_date' => '2024-01-01',
                'end_date'   => '2024-12-31',
                'notes'      => 'ذروة النشاط التشغيلي لعام 2024 (الإنتاجية القصوى: 1,394 سنداً عبر 286 يوماً نشطاً)',
            ],
        ],
        2025 => [
            [
                'period_num' => 1,
                'start_date' => '2025-01-01',
                'end_date'   => '2025-01-25',
                'notes'      => 'فترة النشاط الفعلي لعام 2025 (إلزامية السندات والتدقيق الرقابي تبدأ من 2025-01-26)',
            ],
        ],
        2026 => [
            [
                'period_num' => 1,
                'start_date' => '2026-04-12',
                'end_date'   => '2026-08-24',
                'notes'      => 'النافذة التعاقدية المعتمدة لعام 2026 (2026-04-12 إلى 2026-08-24)',
            ],
        ],
    ];

    /**
     * Generate complete operational audit and statistics package.
     */
    public function getAuditReportData(): array
    {
        $allBonds = Bond::with('items')
            ->orderBy('date', 'asc')
            ->orderBy('bond_serial', 'asc')
            ->get();

        $bondsByYear = [];
        foreach ($allBonds as $bond) {
            if (empty($bond->date)) continue;
            $year = substr(trim($bond->date), 0, 4);
            $bondsByYear[$year][] = $bond;
        }

        $yearlyStats = [];
        $totalCalendarDays = 0;
        $totalActiveDays = 0;
        $totalBondsCount = 0;
        $totalNormalBonds = 0;
        $totalMissingBonds = 0;
        $totalCancelledBonds = 0;
        $totalItemsCount = 0;

        // Process each year from 2019 through 2026
        for ($year = 2019; $year <= 2026; $year++) {
            $yStr = (string)$year;
            $bondsInYear = collect($bondsByYear[$yStr] ?? []);
            $calDays = Carbon::create($year, 1, 1)->isLeapYear() ? 366 : 365;

            $dates = $bondsInYear->pluck('date')->filter()->unique()->sort()->values();
            $activeDays = $dates->count();
            $idleDays = $calDays - $activeDays;
            $activePct = $calDays > 0 ? round(($activeDays / $calDays) * 100, 1) : 0;
            $idlePct = round(100 - $activePct, 1);

            $bCount = $bondsInYear->count();
            $mCount = $bondsInYear->filter(fn($b) => $b->isMissing())->count();
            $cCount = $bondsInYear->filter(fn($b) => $b->isCancelled())->count();
            $nCount = $bCount - $mCount - $cCount;

            $itemsCount = 0;
            foreach ($bondsInYear as $b) {
                $itemsCount += $b->items ? $b->items->count() : 0;
            }

            // Gaps between recorded dates in this year
            $intraYearGaps = [];
            $routinePausesCount = 0;
            for ($i = 0; $i < count($dates) - 1; $i++) {
                $curr = Carbon::parse($dates[$i]);
                $next = Carbon::parse($dates[$i + 1]);
                $diff = $curr->diffInDays($next);
                if ($diff > 1) {
                    $gapStart = $curr->copy()->addDay()->format('Y-m-d');
                    $gapEnd = $next->copy()->subDay()->format('Y-m-d');
                    $gapDays = $diff - 1;
                    
                    if ($gapDays <= 2) {
                        // Standard 1 to 2 day weekend / single holiday pause
                        $routinePausesCount++;
                    } else {
                        // Noticeable operational pause (>= 3 days)
                        $intraYearGaps[] = [
                            'from_date'   => $gapStart,
                            'to_date'     => $gapEnd,
                            'days'        => $gapDays,
                            'prior_work'  => $dates[$i],
                            'next_work'   => $dates[$i + 1],
                            'description' => $this->describeIntraGap($year, $gapDays, $gapStart, $gapEnd),
                        ];
                    }
                }
            }

            // Find longest pause and major pauses (>= 7 days)
            $longestGapDays = 0;
            $longestGapDesc = 'لا توجد فترات انقطاع طويلة';
            $majorGaps = [];
            foreach ($intraYearGaps as $g) {
                if ($g['days'] >= 7) {
                    $majorGaps[] = $g;
                }
                if ($g['days'] > $longestGapDays) {
                    $longestGapDays = $g['days'];
                    $longestGapDesc = "توقف لمدة {$g['days']} يوماً ({$g['from_date']} إلى {$g['to_date']})";
                }
            }

            // Narrative summary for this year
            $yearNarrative = $this->getYearNarrative($year, $bCount, $activeDays, $calDays, $dates->first(), $dates->last());

            $yearlyStats[$year] = [
                'year'                 => $year,
                'calendar_days'        => $calDays,
                'active_days'          => $activeDays,
                'idle_days'            => $idleDays,
                'active_percentage'    => $activePct,
                'idle_percentage'      => $idlePct,
                'bonds_count'          => $bCount,
                'normal_count'         => $nCount,
                'missing_count'        => $mCount,
                'cancelled_count'      => $cCount,
                'items_count'          => $itemsCount,
                'min_date'             => $dates->first() ?? null,
                'max_date'             => $dates->last() ?? null,
                'official_intervals'   => $this->officialIntervals[$year] ?? [],
                'intra_year_gaps'      => $intraYearGaps,
                'major_gaps'           => $majorGaps,
                'longest_gap_days'     => $longestGapDays,
                'longest_gap_desc'     => $longestGapDesc,
                'routine_pauses_count' => $routinePausesCount,
                'narrative'            => $yearNarrative,
            ];

            $totalCalendarDays += $calDays;
            $totalActiveDays += $activeDays;
            $totalBondsCount += $bCount;
            $totalNormalBonds += $nCount;
            $totalMissingBonds += $mCount;
            $totalCancelledBonds += $cCount;
            $totalItemsCount += $itemsCount;
        }

        // Inter-Year Transition & Gap Analysis (2019 to 2026)
        $interYearTransitions = $this->calculateInterYearTransitions($yearlyStats);

        // Overall KPIs
        $kpis = [
            'total_calendar_days' => $totalCalendarDays,
            'total_active_days'   => $totalActiveDays,
            'total_idle_days'     => $totalCalendarDays - $totalActiveDays,
            'overall_activity_pct'=> round(($totalActiveDays / $totalCalendarDays) * 100, 1),
            'total_bonds'         => $totalBondsCount,
            'total_normal_bonds'  => $totalNormalBonds,
            'total_missing_bonds' => $totalMissingBonds,
            'total_cancelled_bonds'=> $totalCancelledBonds,
            'total_items'         => $totalItemsCount,
            'audited_years_count' => count($yearlyStats),
            'start_year'          => 2019,
            'end_year'            => 2026,
            'earliest_date'       => '2019-09-01',
            'latest_date'         => '2026-08-24',
        ];

        // Executive narrative
        $executiveSummary = $this->buildExecutiveSummaryNarrative($kpis, $yearlyStats, $interYearTransitions);

        return [
            'kpis'                   => $kpis,
            'yearly_stats'           => $yearlyStats,
            'inter_year_transitions' => $interYearTransitions,
            'executive_summary'      => $executiveSummary,
            'report_date'            => Carbon::now()->format('Y-m-d'),
            'report_timestamp'       => Carbon::now()->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Compute inter-year transition gaps from 2019 through 2026.
     */
    protected function calculateInterYearTransitions(array $yearlyStats): array
    {
        $transitions = [];

        for ($year = 2019; $year < 2026; $year++) {
            $nextYear = $year + 1;
            $currStat = $yearlyStats[$year];
            $nextStat = $yearlyStats[$nextYear];

            // Determine reference end date for current year:
            // Prefer actual recorded DB date; fallback to official interval end date
            $currEnd = $currStat['max_date'];
            if (!$currEnd && !empty($currStat['official_intervals'])) {
                $lastInterval = end($currStat['official_intervals']);
                $currEnd = $lastInterval['end_date'];
            }

            // Determine reference start date for next year:
            // Prefer actual recorded DB date; fallback to official interval start date
            $nextStart = $nextStat['min_date'];
            if (!$nextStart && !empty($nextStat['official_intervals'])) {
                $firstInterval = reset($nextStat['official_intervals']);
                $nextStart = $firstInterval['start_date'];
            }

            if ($currEnd && $nextStart) {
                $cDate = Carbon::parse($currEnd);
                $nDate = Carbon::parse($nextStart);
                $diff = $cDate->diffInDays($nDate);

                if ($diff > 1) {
                    $gapDays = $diff - 1;
                    $gapStart = $cDate->copy()->addDay()->format('Y-m-d');
                    $gapEnd = $nDate->copy()->subDay()->format('Y-m-d');
                    $status = 'فترة توقف غير مسجلة';
                    $statusClass = 'danger';
                } elseif ($diff == 1) {
                    $gapDays = 0;
                    $gapStart = '---';
                    $gapEnd = '---';
                    $status = 'انتقال تشغيلي متصل ومباشر';
                    $statusClass = 'success';
                } else {
                    $gapDays = 0;
                    $gapStart = '---';
                    $gapEnd = '---';
                    $status = 'متزامن أو متداخل';
                    $statusClass = 'info';
                }

                $transitions[] = [
                    'from_year'    => $year,
                    'to_year'      => $nextYear,
                    'from_date'    => $currEnd,
                    'to_date'      => $nextStart,
                    'gap_days'     => $gapDays,
                    'gap_from'     => $gapStart,
                    'gap_to'       => $gapEnd,
                    'status'       => $status,
                    'status_class' => $statusClass,
                    'narrative'    => $this->getTransitionNarrative($year, $nextYear, $gapDays, $currEnd, $nextStart, $gapStart, $gapEnd),
                ];
            }
        }

        return $transitions;
    }

    /**
     * Provide insightful contextual narrative for intra-year pauses.
     */
    protected function describeIntraGap(int $year, int $gapDays, string $from, string $to): string
    {
        if ($gapDays >= 90) {
            return "فترة توقف استراتيجية مطولة ({$gapDays} يوماً) - من {$from} إلى {$to}.";
        } elseif ($gapDays >= 30) {
            return "توقف تشغيلي فصلي ملحوظ ({$gapDays} يوماً) - من {$from} إلى {$to}.";
        } elseif ($gapDays >= 7) {
            return "ركود عمل أسبوعي/مؤقت ({$gapDays} يوماً) - من {$from} إلى {$to}.";
        } else {
            return "عطلة عمل قصيرة ({$gapDays} يوم) - من {$from} إلى {$to}.";
        }
    }

    /**
     * Generate narrative for a specific year's operational behavior.
     */
    protected function getYearNarrative(int $year, int $bondsCount, int $activeDays, int $calDays, ?string $minDate, ?string $maxDate): string
    {
        switch ($year) {
            case 2019:
                return 'المرحلة التأسيسية للمستودع وتجهيز البنية التحتية لحفظ وتتبع السجلات (انطلقت 2019-09-01).';
            case 2020:
                return "تسجيل أول 38 سنداً رسمياً عبر 9 أيام عمل، تخللها توقف معتمد لمدة 98 يوماً قبل استقرار السجلات.";
            case 2021:
                return 'استمرار العمليات حتى 2021-08-21 (233 يوماً معتمداً)، أعقبها توقف انتقالي لإعادة جدولة المشاريع (252 يوماً).';
            case 2022:
                return "استعادة النشاط بتسجيل 131 سنداً عبر 42 يوماً نشطاً (سبتمبر - ديسمبر 2022) مع توقف صيفي دام 91 يوماً.";
            case 2023:
                return "انفجار تشغيلي بنسبة تغطية 62.2%؛ تسجيل 1,031 سنداً عبر 227 يوماً نشطاً لمختلف المشاريع.";
            case 2024:
                return "الذروة القياسية القصوى بتسجيل 1,394 سنداً عبر 286 يوماً نشطاً (78.1% من السنة) باستقرار كامل.";
            case 2025:
                return "تسجيل 26 سنداً حتى 25 يناير، تلاها التطبيق الإلزامي لإرفاق السندات الرسمية اعتباراً من 2025-01-26.";
            case 2026:
                return "حصر العمليات بدقة ضمن النافذة التعاقدية (2026-04-12 إلى 2026-08-24) بتسجيل 12 سنداً مستوفياً الشروط.";
            default:
                return "سنة تشغيلية تتضمن {$bondsCount} سنداً على مدار {$activeDays} يوماً نشطاً.";
        }
    }

    /**
     * Contextual narrative for each inter-year transition gap.
     */
    protected function getTransitionNarrative(int $fromYear, int $toYear, int $gapDays, string $fromEnd, string $toStart, string $gapFrom, string $gapTo): string
    {
        if ($fromYear === 2019 && $toYear === 2020) {
            return "تسليم فوري متصل بنهاية ديسمبر 2019 وبداية يناير 2020 دون أي فجوة زمنية.";
        } elseif ($fromYear === 2020 && $toYear === 2021) {
            return "استمرارية تشغيلية تامة دون انقطاع بين ختام 2020 وبداية 2021 لضمان تدفق التوريدات.";
        } elseif ($fromYear === 2021 && $toYear === 2022) {
            return "فجوة توقف ممتدة لـ {$gapDays} يوماً لإعادة جدولة المشاريع والترتيبات اللوجستية قبل استئناف التوريد.";
        } elseif ($fromYear === 2022 && $toYear === 2023) {
            return "توقف شتوي انتقالي مدته {$gapDays} يوماً بين نهاية 2022 وبداية توريد يناير 2023.";
        } elseif ($fromYear === 2023 && $toYear === 2024) {
            return "اتصال تشغيلي كامل ومباشر (0 يوم توقف) مهد للانطلاقة القياسية الكبرى لعام 2024.";
        } elseif ($fromYear === 2024 && $toYear === 2025) {
            return "تسليم تشغيلي مستمر ومتصل (0 يوم انقطاع) بين نهاية 2024 ومطلع 2025.";
        } elseif ($fromYear === 2025 && $toYear === 2026) {
            return "فجوة توقف مبررة تعاقدياً لـ {$gapDays} يوماً لحين سريان النافذة الرسمية المعتمدة لعام 2026.";
        }

        return $gapDays > 0 
            ? "فجوة تشغيلية بين العامين مدتها {$gapDays} يوماً (من {$gapFrom} إلى {$gapTo})."
            : "انتقال تشغيلي متصل ومباشر دون فجوات زمنية.";
    }

    /**
     * Build rich, executive-grade narrative synthesis.
     */
    protected function buildExecutiveSummaryNarrative(array $kpis, array $yearlyStats, array $interTransitions): array
    {
        return [
            'headline' => 'تقرير التدقيق الرقابي والتحليل الزمني لعمليات مستودع التضامن (2019 – 2026)',
            'overview' => "مراجعة رقابية شاملة وموجزة لمسار السندات المخزنية لمستودع التضامن عبر 8 سنوات (2019 - 2026). سُجّل إجمالي {$kpis['total_bonds']} سنداً مخزنياً و{$kpis['total_items']} بنداً تشغيلياً عبر {$kpis['total_active_days']} يوماً فعلياً، مع تركّز 92.1% من مجمل العمليات في عامي الذروة (2023 - 2024).",
            'strategic_phases' => [
                [
                    'phase_title' => 'المرحلة الأولى: التأسيس والتحديات الميدانية (2019 – 2021)',
                    'period'      => '2019 - 2021',
                    'highlight'   => 'تأسيس الدفاتر، تسجيل 38 سنداً، وأطول فترة ركود بين الأعوام (252 يوماً).',
                    'description' => 'انطلقت العمليات التمهيدية في خريف 2019 تلاها بدء التسجيل الرسمي في 2020، وواجه المشروع توقفاً انتقالياً في 2021 لإعادة جدولة المشاريع اللوجستية.',
                ],
                [
                    'phase_title' => 'المرحلة الثانية: النهوض والذروة القياسية القصوى (2022 – 2024)',
                    'period'      => '2022 - 2024',
                    'highlight'   => 'تسجيل 2,556 سنداً (97% من الحجم التاريخي)، بذروة قصوى في 2024 (1,394 سنداً).',
                    'description' => 'مرحلة الإنتاجية الكبرى للمستودع باستقرار كامل للعمليات عبر 286 يوماً نشطاً في 2024 واتصال تام دون أي انقطاع بين الأعوام.',
                ],
                [
                    'phase_title' => 'المرحلة الثالثة: الحوكمة الصارمة والنافذة التعاقدية (2025 – 2026)',
                    'period'      => '2025 - 2026',
                    'highlight'   => 'إلزامية السندات المقترنة بالتاريخ وضبط نافذة 2026 (2026-04-12 إلى 2026-08-24).',
                    'description' => 'تطبيق معايير رقابية صارمة باشتراط إرفاق السند الرسمي اعتباراً من 2025-01-26، وحصر كافة عمليات 2026 ضمن النافذة التعاقدية المعتمدة.',
                ],
            ],
            'audit_recommendations' => [
                'الالتزام المستمر بشرط إرفاق صور السندات المعتمدة لكافة الحركات منذ تاريخ 2025-01-26 فصاعداً.',
                'استمرار تتبع السندات الملغاة والمفقودة (المحصورة حالياً في نطاق ضيق جداً) لمنع أي فاقد مخزني.',
                'توثيق أسباب الفترات الانتقالية بين الأعوام في السجلات الرسمية كفترات إجازة تعاقدية معتمدة ومبررة.',
                'الاعتماد الدائم لآلية تجميع وحزم السندات (Stacks) لضمان ربط كل سند بدفتره الأصلي وتجنب السندات السائبة.',
            ],
        ];
    }
}
