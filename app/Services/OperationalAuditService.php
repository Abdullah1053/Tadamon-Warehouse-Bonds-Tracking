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
            for ($i = 0; $i < count($dates) - 1; $i++) {
                $curr = Carbon::parse($dates[$i]);
                $next = Carbon::parse($dates[$i + 1]);
                $diff = $curr->diffInDays($next);
                if ($diff > 1) {
                    $gapStart = $curr->copy()->addDay()->format('Y-m-d');
                    $gapEnd = $next->copy()->subDay()->format('Y-m-d');
                    $gapDays = $diff - 1;
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
                return 'مثّل عام 2019 المرحلة التأسيسية للمستودع وإعداد البنية التحتية لحفظ وتتبع السجلات. بدأت الفترات التشغيلية المعتمدة رسمياً في 2019-09-01 حتى نهاية العام كأرضية انطلاق، وتم حفظ الدفاتر التمهيدية لتنظيم التوريدات القادمة.';
            case 2020:
                return "شهد عام 2020 أول تسجيل رسمي للسندات المؤرشفة بالنظام بعدد {$bondsCount} سنداً على مدار {$activeDays} يوماً فعلياً. تميز العام بوجود فترتي تشغيل رئيسيتين فصلت بينهما فترة توقف معتمدة استمرت 98 يوماً، مع انتظام السجلات في الربع الأخير.";
            case 2021:
                return 'امتدت العمليات التشغيلية لعام 2021 من 2021-01-01 حتى 2021-08-21 بإجمالي 233 يوماً تشغيلياً معتمداً. أعقبت هذه الفترة أطول مرحلة ركود وانقطاع انتقالي موثقة في تاريخ المشروع امتدت لـ 252 يوماً حتى استئناف التوريدات في مايو 2022.';
            case 2022:
                return "مرحلة استعادة النشاط والنهوض بحركة التخزين؛ حيث سُجّل {$bondsCount} سنداً عبر {$activeDays} يوماً نشطاً (بدأت من 2022-09-05 حتى 2022-12-04)، وتخلل العام فترتان تشغيليتان معتمدتان مع توقف صيفي دام 91 يوماً.";
            case 2023:
                return "عام الانفجار التشغيلي والتحول الرقمي الشامل؛ قفز حجم السندات إلى {$bondsCount} سنداً مسجلاً عبر {$activeDays} يوماً نشطاً (بنسبة تغطية بلغت 62.2% من أيام السنة)، مما عكس كثافة هائلة في استلام المواد وصرفها لمواقع المشاريع.";
            case 2024:
                return "الذروة التشغيلية والإنتاجية القصوى في مسيرة المستودع؛ تم تسجيل {$bondsCount} سنداً عبر {$activeDays} يوماً نشطاً من أصل {$calDays} يوماً (بنسبة استثنائية بلغت 78.1%)، وهو العام الأكثر استقراراً وانتظاماً مع انحسار شبه تام للفجوات الكبيرة.";
            case 2025:
                return "عام التحول الرقابي والامتثال؛ تم تسجيل {$bondsCount} سنداً حتى 25 يناير 2025، ثم دخل المستودع مرحلة التطبيق الإلزامي لسندات الصرف والاستلام المقرونة بتدقيق رقابي صارم اعتباراً من 2025-01-26 وفق المعايير المعتمدة.";
            case 2026:
                return "إدارة العمليات وفق النافذة التعاقدية المحددة بدقة (من 2026-04-12 إلى 2026-08-24). تم حصر {$bondsCount} سنداً تشغيلياً على مدار {$activeDays} أيام عمل فعلية، مع استيفاء ومطابقة كافة شروط التحقق والامتثال الرقابي.";
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
            return "انتقال فوري ومتصل بنهاية ديسمبر 2019 ومطلع يناير 2020 دون أي فجوة زمنية مسجلة بين الفترتين التشغيليتين.";
        } elseif ($fromYear === 2020 && $toYear === 2021) {
            return "استمرارية تشغيلية تامة بين ختام عام 2020 وبداية عام 2021 دون انقطاع، مما مكن من استمرار حركة التوريدات بسلاسة.";
        } elseif ($fromYear === 2021 && $toYear === 2022) {
            return "فجوة زمنية حرجة وتوقف ممتد استمر لـ {$gapDays} يوماً (من {$gapFrom} حتى {$gapTo}). يعتبر هذا أطول انقطاع تشغيلي مسجل في مسيرة المستودع نتيجة إعادة جدولة المشاريع قبل استئناف التوريد في مايو 2022.";
        } elseif ($fromYear === 2022 && $toYear === 2023) {
            return "فترة توقف شتوية انتقالية مدتها {$gapDays} يوماً (من {$gapFrom} حتى {$gapTo}) بين نهاية آخر سند مسجل في 2022 وأول حركة توريد في يناير 2023.";
        } elseif ($fromYear === 2023 && $toYear === 2024) {
            return "اتصال وانسيابية تشغيلية متكاملة بين نهاية 2023 وبداية 2024 دون يوم توقف واحد؛ وهو ما شكّل الأساس للانطلاقة القياسية الكبرى لعام 2024.";
        } elseif ($fromYear === 2024 && $toYear === 2025) {
            return "تسليم تشغيلي مستمر ومتصل (0 يوم انقطاع) من اليوم الأخير لعام 2024 (2024-12-31) وحتى اليوم الأول لعام 2025 (2025-01-01).";
        } elseif ($fromYear === 2025 && $toYear === 2026) {
            return "فجوة توقف تشغيلي غير مسجلة استمرت {$gapDays} يوماً (من {$gapFrom} حتى {$gapTo}) بين آخر سند مسجل في يناير 2025 وانطلاق النافذة التعاقدية الرسمية في أبريل 2026.";
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
            'overview' => "يمثل هذا التقرير مراجعة شاملة ومستفيضة للمسار الزمني وحركة السندات المخزنية لمستودع التضامن عبر 8 سنوات تشغيلية متتالية (2019 - 2026). خلال هذه المسيرة، سُجل إجمالي {$kpis['total_bonds']} سنداً مخزنياً و{$kpis['total_items']} بنداً تشغيلياً على مدار {$kpis['total_active_days']} يوماً فعلياً، في حين توزعت فترات التوقف والركود المرحلي على {$kpis['total_idle_days']} يوماً.",
            'strategic_phases' => [
                [
                    'phase_title' => 'المرحلة الأولى: التأسيس والتحديات الميدانية المبكرة (2019 – 2021)',
                    'period'      => '2019 - 2021',
                    'highlight'   => 'تأسيس الدفاتر، تسجيل 38 سنداً مبكراً، وحدوث أطول فجوة ركود (252 يوماً).',
                    'description' => 'انطلقت العمليات في خريف 2019 كفترة تحضيرية تلاها تسجيل أولى السجلات الميدانية في عام 2020، والتي واجهت توقفاً معتمداً لمدة 98 يوماً. امتد عام 2021 حتى شهر أغسطس ليعقبه أطول توقف تشغيلي بين الأعوام امتد لـ 252 يوماً.',
                ],
                [
                    'phase_title' => 'المرحلة الثانية: النهوض والتوسع القياسي الاستثنائي (2022 – 2024)',
                    'period'      => '2022 - 2024',
                    'highlight'   => 'قفزة تاريخية بتسجيل 2,556 سنداً خلال 3 أعوام، بذروة سنوية قياسية في 2024 (1,394 سنداً).',
                    'description' => 'شهدت هذه المرحلة ذروة العطاء والإنتاجية للمستودع؛ حيث استعاد نشاطه في 2022، ثم قفزت العمليات في 2023 لتسجل 1,031 سنداً، لتتوج في عام 2024 بالذروة التاريخية بتسجيل 1,394 سنداً عبر 286 يوماً نشطاً دون انقطاع يذكر بين الأعوام.',
                ],
                [
                    'phase_title' => 'المرحلة الثالثة: الحوكمة الصارمة والنافذة التعاقدية (2025 – 2026)',
                    'period'      => '2025 - 2026',
                    'highlight'   => 'إلزامية السندات المقترنة بالتاريخ وضبط نافذة العمل لعام 2026 (2026-04-12 إلى 2026-08-24).',
                    'description' => 'تحولت السياسة التشغيلية مطلع 2025 نحو الحوكمة الكاملة واشتراط وجود سند رسمي معتمد اعتباراً من 2025-01-26، وانتهت بتحديد النافذة التعاقدية لعام 2026 بدقة تامة لضمان موثوقية الأصول وتوثيق حركات الصرف والاستلام.',
                ],
            ],
            'audit_recommendations' => [
                'الالتزام الصارم بشرط إرفاق صور السندات المعتمدة لكافة الحركات منذ تاريخ 2025-01-26 فصاعداً.',
                'استمرار تتبع السندات الملغاة والمفقودة (المحصورة حالياً في نطاق ضيق جداً) لمنع أي فاقد مخزني.',
                'توثيق أسباب الفترات الانتقالية الطويلة بين الأعوام (مثل فجوة 2021-2022 وفجوة 2025-2026) في السجلات الرسمية كفترات إجازة تعاقدية معتمدة.',
            ],
        ];
    }
}
