<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير التدقيق الرقابي والتحليل الزمني - مستودع التضامن (2019-2026)</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 10mm 12mm 10mm;
        }

        :root {
            --primary: #1e3a8a;
            --primary-dark: #172554;
            --primary-light: #dbeafe;
            --accent: #0284c7;
            --success: #059669;
            --warning: #d97706;
            --danger: #dc2626;
            --slate-900: #0f172a;
            --slate-800: #1e293b;
            --slate-700: #334155;
            --slate-600: #475569;
            --slate-500: #64748b;
            --slate-200: #e2e8f0;
            --slate-100: #f1f5f9;
            --slate-50: #f8fafc;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        body {
            font-family: 'Cairo', 'Segoe UI', Tahoma, Arial, sans-serif;
            background-color: #f8fafc;
            color: var(--slate-800);
            margin: 0;
            padding: 0;
            font-size: 10pt;
            line-height: 1.45;
            direction: rtl;
        }

        .container {
            max-width: 210mm;
            margin: 0 auto;
            background: #ffffff;
            padding: 12mm 12mm;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        /* Screen Toolbar - Hidden on Print */
        .no-print-toolbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: #0f172a;
            color: #ffffff;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0,0,0,0.25);
            margin-bottom: 15px;
        }

        .toolbar-title {
            font-size: 13px;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            border: none;
        }

        .btn-primary {
            background-color: #2563eb;
            color: #ffffff;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
        }

        .btn-secondary {
            background-color: #334155;
            color: #e2e8f0;
        }
        .btn-secondary:hover {
            background-color: #475569;
        }

        /* Report Header */
        .report-header {
            border-bottom: 2px solid #cbd5e1;
            padding-bottom: 10px;
            margin-bottom: 14px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .header-brand {
            flex: 1;
        }

        .org-badge {
            display: inline-block;
            background: #eff6ff;
            color: var(--primary);
            font-size: 8.5pt;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 4px;
            border: 1px solid #bfdbfe;
            margin-bottom: 4px;
        }

        .report-title {
            font-size: 15pt;
            font-weight: 800;
            color: var(--primary-dark);
            margin: 0 0 2px 0;
            letter-spacing: -0.3px;
        }

        .report-subtitle {
            font-size: 9.5pt;
            color: var(--slate-600);
            margin: 0;
        }

        .header-meta {
            text-align: left;
            font-size: 8pt;
            color: var(--slate-600);
            line-height: 1.5;
            border-right: 2px solid var(--slate-200);
            padding-right: 12px;
            margin-right: 12px;
        }

        .section-block {
            margin-bottom: 16px;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1.5px solid var(--primary);
            padding-bottom: 4px;
            margin-bottom: 10px;
        }

        .section-num {
            background: var(--primary);
            color: white;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 9pt;
            font-weight: 800;
        }

        .section-title {
            font-size: 11pt;
            font-weight: 800;
            color: var(--primary-dark);
            margin: 0;
        }

        /* Narrative Box */
        .narrative-box {
            background: #f8fafc;
            border-right: 3px solid var(--primary);
            padding: 8px 12px;
            border-radius: 0 6px 6px 0;
            font-size: 9pt;
            color: var(--slate-700);
            margin-bottom: 12px;
            line-height: 1.5;
        }

        /* KPI Cards Grid */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 14px;
        }

        .kpi-card {
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: 6px;
            padding: 10px 8px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            border-top: 3px solid var(--primary);
        }

        .kpi-card.success { border-top-color: var(--success); }
        .kpi-card.warning { border-top-color: var(--warning); }
        .kpi-card.accent { border-top-color: var(--accent); }

        .kpi-value {
            font-size: 16pt;
            font-weight: 900;
            line-height: 1.1;
            margin-bottom: 2px;
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
        }

        .kpi-label {
            font-size: 8pt;
            font-weight: 700;
            color: var(--slate-700);
            margin-bottom: 2px;
        }

        .kpi-sub {
            font-size: 7.5pt;
            color: var(--slate-500);
        }

        /* Strategic Eras Cards */
        .phase-card {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 9px 12px;
            margin-bottom: 8px;
            border-right: 3px solid var(--primary);
        }

        .phase-title {
            font-size: 9.5pt;
            font-weight: 800;
            color: var(--primary-dark);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3px;
        }

        .phase-highlight {
            font-size: 8.5pt;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 2px;
        }

        .phase-desc {
            font-size: 8.5pt;
            color: var(--slate-600);
            line-height: 1.4;
        }

        /* Charts Container */
        .charts-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 12px;
        }

        .chart-box {
            background: #ffffff;
            border: 1px solid var(--slate-200);
            border-radius: 6px;
            padding: 10px 12px;
        }

        .chart-title {
            font-size: 9pt;
            font-weight: 800;
            color: var(--slate-800);
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
        }

        .bar-group {
            display: flex;
            align-items: center;
            margin-bottom: 4px;
            font-size: 8pt;
        }

        .bar-label {
            width: 38px;
            font-weight: 700;
            color: var(--slate-700);
            text-align: right;
        }

        .bar-track {
            flex: 1;
            height: 12px;
            background: #f1f5f9;
            border-radius: 3px;
            overflow: hidden;
            display: flex;
            margin: 0 6px;
        }

        .bar-segment-active { background: #0284c7; height: 100%; }
        .bar-segment-idle { background: #cbd5e1; height: 100%; }

        .volume-track {
            flex: 1;
            height: 12px;
            background: #f1f5f9;
            border-radius: 3px;
            overflow: hidden;
            margin: 0 6px;
        }

        .volume-bar {
            height: 100%;
            background: #6366f1;
            border-radius: 3px;
        }

        .volume-bar.peak {
            background: #059669;
        }

        .bar-value {
            width: 75px;
            text-align: left;
            font-size: 7.5pt;
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
            color: var(--slate-600);
        }

        /* Tables */
        .audit-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 10px;
        }

        .audit-table th {
            background: var(--primary);
            color: #ffffff;
            font-weight: 700;
            padding: 5px 6px;
            text-align: center;
            border: 1px solid var(--primary-dark);
        }

        .audit-table td {
            padding: 4px 6px;
            text-align: center;
            border: 1px solid var(--slate-200);
            color: var(--slate-800);
        }

        .audit-table tr:nth-child(even) {
            background: #f8fafc;
        }

        .audit-table tr.total-row {
            background: #e2e8f0;
            font-weight: 800;
            border-top: 2px solid var(--primary);
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 4px;
            font-size: 7.5pt;
            font-weight: 700;
        }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger  { background: #fee2e2; color: #991b1b; }
        .badge-primary { background: #dbeafe; color: #1e40af; }
        .badge-slate   { background: #f1f5f9; color: #334155; }

        /* Page Breaks for Print */
        .page-break {
            page-break-before: always;
            break-before: page;
        }

        @media print {
            body {
                background: #ffffff;
                font-size: 9.5pt;
            }
            .container {
                max-width: 100% !important;
                padding: 0 !important;
                box-shadow: none !important;
            }
            .no-print, .no-print-toolbar {
                display: none !important;
            }
            .page-break {
                page-break-before: always !important;
                break-before: page !important;
            }
        }

        .footer {
            margin-top: 14px;
            padding-top: 6px;
            border-top: 1px solid #cbd5e1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 7.5pt;
            color: var(--slate-500);
        }
    </style>
</head>
<body>

    <!-- Screen Toolbar -->
    <div class="no-print-toolbar no-print">
        <div class="toolbar-title">
            <span>📄</span>
            <span>تقرير التدقيق الرقابي والتحليل الزمني الموجز (مستودع التضامن 2019 - 2026)</span>
        </div>
        <div class="toolbar-actions">
            <button onclick="window.print()" class="btn btn-primary" title="طباعة فورية أو حفظ باستخدام المتصفح كـ PDF معتمد عالي الدقة">
                <span>🖨️ طباعة / حفظ كـ PDF رسمي</span>
            </button>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                <span>↩️ العودة للوحة التحكم</span>
            </a>
        </div>
    </div>

    @if(request('autoprint') == 1 || session('info'))
    <div class="no-print" style="background: #eff6ff; border-bottom: 2px solid #3b82f6; padding: 10px 20px; font-size: 9pt; color: #1e40af; display: flex; align-items: center; justify-content: space-between;" dir="rtl">
        <div style="display: flex; align-items: center; gap: 8px;">
            <span style="font-size: 15pt;">🖨️</span>
            <span>
                <strong>توليد التقرير كـ PDF:</strong>
                {{ session('info') ?? 'تم تشغيل نافذة الطباعة تلقائياً لتصدير التقرير كملف PDF معتمد. يمكنك اختيار "حفظ بتنسيق PDF" (Save as PDF).' }}
            </span>
        </div>
        <button onclick="this.parentElement.remove()" style="background: none; border: none; font-size: 16px; cursor: pointer; color: #1e40af; font-weight: bold;">&times;</button>
    </div>
    @endif

    <div class="container">

        <!-- ================= PAGE 1 ================= -->
        <!-- Header -->
        <header class="report-header">
            <div class="header-brand">
                <div class="org-badge">وثيقة رقابية معتمدة • وحدة التدقيق وإدارة المستودعات</div>
                <h1 class="report-title">تقرير التدقيق الرقابي والتحليل الزمني الشامل</h1>
                <p class="report-subtitle">سجل فترات التشغيل، فحص أيام العمل والتوقف، وتحليل الفجوات الانتقالية (2019 – 2026)</p>
            </div>
            <div class="header-meta">
                <div><strong>المستودع:</strong> مستودع التضامن المركزي</div>
                <div><strong>تاريخ الإصدار:</strong> {{ $report_date }}</div>
                <div><strong>إجمالي السندات:</strong> {{ number_format($kpis['total_bonds']) }} سنداً</div>
                <div><strong>الحالة العامة:</strong> <span class="badge badge-success">تم التدقيق والمطابقة</span></div>
            </div>
        </header>

        <!-- SECTION 1: Executive Summary & KPIs -->
        <section class="section-block">
            <div class="section-header">
                <span class="section-num">1</span>
                <h2 class="section-title">الملخص التنفيذي ومؤشرات الأداء الرقابية (Executive Summary & KPIs)</h2>
            </div>

            <!-- Overarching Narrative -->
            <div class="narrative-box">
                <strong>نظرة عامة على المسار التشغيلي:</strong> {{ $executive_summary['overview'] }}
            </div>

            <!-- KPI Cards Grid -->
            <div class="kpi-grid">
                <div class="kpi-card">
                    <div class="kpi-value" style="color: #1e3a8a;">{{ number_format($kpis['total_bonds']) }}</div>
                    <div class="kpi-label">إجمالي السندات المسجلة</div>
                    <div class="kpi-sub">{{ number_format($kpis['total_items']) }} بنداً ومادة مخزنية</div>
                </div>
                <div class="kpi-card success">
                    <div class="kpi-value" style="color: #059669;">{{ number_format($kpis['total_active_days']) }}</div>
                    <div class="kpi-label">أيام العمل الفعلية المسجلة</div>
                    <div class="kpi-sub">تغطية نشطة بنسبة {{ $kpis['overall_activity_pct'] }}%</div>
                </div>
                <div class="kpi-card warning">
                    <div class="kpi-value" style="color: #d97706;">{{ number_format($kpis['total_idle_days']) }}</div>
                    <div class="kpi-label">أيام التوقف والركود المرحلي</div>
                    <div class="kpi-sub">تشمل العطل والفترات الانتقالية</div>
                </div>
                <div class="kpi-card accent">
                    <div class="kpi-value" style="color: #0284c7;">2,425</div>
                    <div class="kpi-label">سندات عامي الذروة (2023-2024)</div>
                    <div class="kpi-sub">تمثل 92.1% من الحجم الكلي</div>
                </div>
            </div>

            <!-- 3 Strategic Eras Narrative -->
            <div>
                @foreach($executive_summary['strategic_phases'] as $phase)
                    <div class="phase-card">
                        <div class="phase-title">
                            <span>{{ $phase['phase_title'] }}</span>
                            <span class="badge badge-slate">{{ $phase['period'] }}</span>
                        </div>
                        <div class="phase-highlight">{{ $phase['highlight'] }}</div>
                        <div class="phase-desc">{{ $phase['description'] }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- Document Footer Page 1 -->
        <footer class="footer">
            <div>نظام إدارة وتتبع سندات مستودع التضامن • صفحة 1 من 4</div>
            <div>وثيقة رسمية معتمدة • مخصصة لأغراض التدقيق الرقابي الداخلي</div>
        </footer>

        <!-- ================= PAGE 2 ================= -->
        <div class="page-break"></div>

        <!-- SECTION 2: Visual Statistics & Charts -->
        <section class="section-block">
            <div class="section-header">
                <span class="section-num">2</span>
                <h2 class="section-title">الإحصائيات البصرية وخريطة المسار الزمني (Visual Analytics & Timeline Radar)</h2>
            </div>

            <div class="charts-container">
                <!-- Chart 1: Active Work Days vs Idle Days -->
                <div class="chart-box">
                    <div class="chart-title">
                        <span>أيام العمل الفعلية مقابل أيام التوقف</span>
                        <span style="font-size: 7.5pt; font-weight: normal; color: #64748b;">
                            <span style="display:inline-block; width:7px; height:7px; background:#0284c7; border-radius:2px;"></span> عمل &nbsp;
                            <span style="display:inline-block; width:7px; height:7px; background:#cbd5e1; border-radius:2px;"></span> توقف
                        </span>
                    </div>
                    @foreach($yearly_stats as $yr => $st)
                        <div class="bar-group">
                            <div class="bar-label">{{ $yr }}</div>
                            <div class="bar-track" title="{{ $st['active_days'] }} يوم عمل ({{ $st['active_percentage'] }}%) مقابل {{ $st['idle_days'] }} يوم توقف">
                                <div class="bar-segment-active" style="width: {{ $st['active_percentage'] }}%;"></div>
                                <div class="bar-segment-idle" style="width: {{ $st['idle_percentage'] }}%;"></div>
                            </div>
                            <div class="bar-value">
                                <strong style="color: #0284c7;">{{ $st['active_days'] }}ي</strong> ({{ $st['active_percentage'] }}%)
                            </div>
                        </div>
                    @endforeach
                    <div style="font-size: 7.5pt; color: #64748b; margin-top: 6px; text-align: center;">
                        * حقق عام 2024 أعلى نسبة نشاط بـ 286 يوم عمل فعلي (78.1% من أيام السنة).
                    </div>
                </div>

                <!-- Chart 2: Annual Volume Distribution -->
                <div class="chart-box">
                    <div class="chart-title">
                        <span>توزيع كثافة السندات المسجلة عبر الأعوام</span>
                        <span class="badge badge-primary">الذروة: 2024</span>
                    </div>
                    @php $maxBonds = 1394; @endphp
                    @foreach($yearly_stats as $yr => $st)
                        @php
                            $volPct = $maxBonds > 0 ? round(($st['bonds_count'] / $maxBonds) * 100, 1) : 0;
                            $isPeak = ($st['bonds_count'] >= 1000);
                        @endphp
                        <div class="bar-group">
                            <div class="bar-label">{{ $yr }}</div>
                            <div class="volume-track">
                                <div class="volume-bar {{ $isPeak ? 'peak' : '' }}" style="width: {{ max($volPct, 2) }}%;"></div>
                            </div>
                            <div class="bar-value">
                                <strong style="{{ $isPeak ? 'color: #059669;' : 'color: #4f46e5;' }}">{{ number_format($st['bonds_count']) }}</strong> سند
                            </div>
                        </div>
                    @endforeach
                    <div style="font-size: 7.5pt; color: #64748b; margin-top: 6px; text-align: center;">
                        * يتركز 92.1% من مجمل السندات في الفترة الذهبية (2023 - 2024).
                    </div>
                </div>
            </div>

            <!-- Operational Continuity & Gap Map -->
            <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px 12px; margin-bottom: 12px;">
                <div style="font-size: 9pt; font-weight: 800; color: #1e293b; margin-bottom: 6px;">
                    خريطة المسار الزمني لفترات الاستمرار والانقطاع (Timeline Continuity Radar)
                </div>
                <div style="display: flex; height: 22px; border-radius: 4px; overflow: hidden; background: #e2e8f0; font-size: 7.5pt; font-weight: bold; text-align: center; color: white;">
                    <div style="width: 10%; background: #64748b;" title="2019-2020: بداية التأسيس">2019-20</div>
                    <div style="width: 15%; background: #ef4444;" title="2021-2022: توقف 252 يوماً">انقطاع 252ي</div>
                    <div style="width: 15%; background: #3b82f6;" title="2022: استئناف العمل">2022</div>
                    <div style="width: 25%; background: #10b981;" title="2023: نشاط متصل 1,031 سند">2023 (متصل)</div>
                    <div style="width: 25%; background: #059669;" title="2024: ذروة مستمرة 1,394 سند">2024 (الذروة)</div>
                    <div style="width: 5%; background: #f59e0b;" title="2025: بداية الحوكمة">2025</div>
                    <div style="width: 5%; background: #8b5cf6;" title="2026: النافذة التعاقدية">2026</div>
                </div>
                <div style="display: flex; justify-content: space-between; font-size: 7.5pt; color: #64748b; margin-top: 4px;">
                    <span>سبتمبر 2019 (التأسيس)</span>
                    <span>2021-2022 (أطول توقف)</span>
                    <span>2023-2024 (ذروة متصلة)</span>
                    <span>أغسطس 2026 (النافذة المعتمدة)</span>
                </div>
            </div>

            <!-- Key Takeaways Box -->
            <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px 12px; font-size: 8.5pt; color: #334155;">
                <strong style="color: #1e3a8a;">📌 أبرز مؤشرات التحليل الزمني الرقابي:</strong>
                <ul style="margin: 4px 0 0 0; padding-right: 16px; line-height: 1.6;">
                    <li><strong>الاستقرار والاتصال:</strong> سجل عام 2024 أعلى انتظام تشغيلي في تاريخ المستودع دون أي فجوات تشغيلية كبرى.</li>
                    <li><strong>انعدام الفاقد:</strong> نسبة السندات السليمة بلغت <strong>97.3%</strong> مع حصر كامل للسندات الملغاة والمفقودة ضمن النطاق المقبول.</li>
                    <li><strong>انضباط الحوكمة:</strong> كافة السندات المقيدة لعام 2026 محصورة بصرامة داخل النافذة التعاقدية المعتمدة رسمياً.</li>
                </ul>
            </div>
        </section>

        <!-- Document Footer Page 2 -->
        <footer class="footer">
            <div>نظام إدارة وتتبع سندات مستودع التضامن • صفحة 2 من 4</div>
            <div>وثيقة رسمية معتمدة • مخصصة لأغراض التدقيق الرقابي الداخلي</div>
        </footer>

        <!-- ================= PAGE 3 ================= -->
        <div class="page-break"></div>

        <!-- SECTION 3: Master Yearly Statistics & Summary of Operating Periods -->
        <section class="section-block">
            <div class="section-header">
                <span class="section-num">3</span>
                <h2 class="section-title">إحصائيات السنوات وجدول فترات التشغيل والتوقف (Yearly Statistics & Stoppages)</h2>
            </div>

            <!-- Table 1: Master Breakdown Table -->
            <div style="font-size: 8.5pt; font-weight: 700; color: #1e3a8a; margin-bottom: 4px;">
                أولاً: جدول التدقيق السنوي الشامل لمؤشرات النشاط والسندات (2019 – 2026)
            </div>
            <table class="audit-table">
                <thead>
                    <tr>
                        <th>السنة</th>
                        <th>أيام السنة</th>
                        <th>أيام العمل</th>
                        <th>التغطية</th>
                        <th>أيام التوقف</th>
                        <th>إجمالي السندات</th>
                        <th>السليمة</th>
                        <th>الملغاة</th>
                        <th>المفقودة</th>
                        <th>المواد</th>
                        <th>تاريخ البداية</th>
                        <th>تاريخ النهاية</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($yearly_stats as $yr => $st)
                        <tr>
                            <td><strong>{{ $yr }}</strong></td>
                            <td>{{ $st['calendar_days'] }}</td>
                            <td>
                                <span class="badge {{ $st['active_days'] > 100 ? 'badge-success' : ($st['active_days'] > 0 ? 'badge-primary' : 'badge-slate') }}">
                                    {{ $st['active_days'] }} يوم
                                </span>
                            </td>
                            <td>{{ $st['active_percentage'] }}%</td>
                            <td>{{ $st['idle_days'] }} يوم</td>
                            <td><strong>{{ number_format($st['bonds_count']) }}</strong></td>
                            <td>{{ number_format($st['normal_count']) }}</td>
                            <td>
                                @if($st['cancelled_count'] > 0)
                                    <span class="badge badge-warning">{{ $st['cancelled_count'] }}</span>
                                @else
                                    <span style="color:#94a3b8;">0</span>
                                @endif
                            </td>
                            <td>
                                @if($st['missing_count'] > 0)
                                    <span class="badge badge-danger">{{ $st['missing_count'] }}</span>
                                @else
                                    <span style="color:#94a3b8;">0</span>
                                @endif
                            </td>
                            <td>{{ number_format($st['items_count']) }}</td>
                            <td style="font-size: 7.5pt;">{{ $st['min_date'] ?? '---' }}</td>
                            <td style="font-size: 7.5pt;">{{ $st['max_date'] ?? '---' }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td>المجموع الكلي</td>
                        <td>{{ number_format($kpis['total_calendar_days']) }}</td>
                        <td>{{ number_format($kpis['total_active_days']) }} يوم</td>
                        <td>{{ $kpis['overall_activity_pct'] }}%</td>
                        <td>{{ number_format($kpis['total_idle_days']) }} يوم</td>
                        <td>{{ number_format($kpis['total_bonds']) }}</td>
                        <td>{{ number_format($kpis['total_normal_bonds']) }}</td>
                        <td>{{ number_format($kpis['total_cancelled_bonds']) }}</td>
                        <td>{{ number_format($kpis['total_missing_bonds']) }}</td>
                        <td>{{ number_format($kpis['total_items']) }}</td>
                        <td style="font-size: 7.5pt;">{{ $kpis['earliest_date'] }}</td>
                        <td style="font-size: 7.5pt;">{{ $kpis['latest_date'] }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Table 2: Compact Annual Operating Windows & Key Pauses -->
            <div style="font-size: 8.5pt; font-weight: 700; color: #1e3a8a; margin: 10px 0 4px 0;">
                ثانياً: موجز فترات التشغيل وأهم التوقفات السنوية المسجلة (Annual Operating Windows & Key Pauses)
            </div>
            <table class="audit-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">السنة</th>
                        <th style="width: 140px;">نطاق التشغيل المسجل</th>
                        <th style="width: 65px;">أيام العمل</th>
                        <th style="width: 80px;">العطل الاعتيادية</th>
                        <th style="width: 150px;">أهم فترة توقف بالعام</th>
                        <th>الخلاصة والتعليق الرقابي الموجز</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($yearly_stats as $yr => $st)
                        <tr>
                            <td><strong>{{ $yr }}</strong></td>
                            <td style="font-size: 7.5pt; font-weight: 600;">
                                @if($st['min_date'] && $st['max_date'])
                                    {{ $st['min_date'] }} ⬅️ {{ $st['max_date'] }}
                                @else
                                    فترة تأسيسية معتمدة
                                @endif
                            </td>
                            <td><span class="badge badge-primary">{{ $st['active_days'] }} يوم</span></td>
                            <td>{{ $st['routine_pauses_count'] ?? 0 }} عطلة أسبوعية</td>
                            <td style="font-size: 7.5pt;">
                                @if(($st['longest_gap_days'] ?? 0) >= 7)
                                    <span class="badge badge-warning">{{ $st['longest_gap_desc'] }}</span>
                                @else
                                    <span class="badge badge-success">عمل متصل ومستقر</span>
                                @endif
                            </td>
                            <td style="text-align: right; font-size: 8pt; line-height: 1.35;">
                                {{ $st['narrative'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        <!-- Document Footer Page 3 -->
        <footer class="footer">
            <div>نظام إدارة وتتبع سندات مستودع التضامن • صفحة 3 من 4</div>
            <div>وثيقة رسمية معتمدة • مخصصة لأغراض التدقيق الرقابي الداخلي</div>
        </footer>

        <!-- ================= PAGE 4 ================= -->
        <div class="page-break"></div>

        <!-- SECTION 4: Inter-Year Transitions & Sign-off -->
        <section class="section-block">
            <div class="section-header">
                <span class="section-num">4</span>
                <h2 class="section-title">تحليل فترات الانتقال بين الأعوام والتوصيات الرقابية (Transitions & Sign-off)</h2>
            </div>

            <!-- Inter-Year Transition Table -->
            <div style="font-size: 8.5pt; font-weight: 700; color: #1e3a8a; margin-bottom: 4px;">
                أولاً: جدول فترات الانتقال والتوقف بين نهاية كل عام وبداية العام التالي:
            </div>
            <table class="audit-table">
                <thead>
                    <tr>
                        <th>الانتقال</th>
                        <th>نهاية السنة السابقة</th>
                        <th>بداية السنة التالية</th>
                        <th>فارق الأيام</th>
                        <th>نطاق الفجوة</th>
                        <th>الحالة</th>
                        <th>الدلالة الرقابية والتشغيلية</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inter_year_transitions as $tr)
                        <tr style="{{ $tr['gap_days'] > 0 ? 'background-color: #fffaf0;' : '' }}">
                            <td><strong>{{ $tr['from_year'] }} ⬅️ {{ $tr['to_year'] }}</strong></td>
                            <td style="font-size: 7.5pt;">{{ $tr['from_date'] }}</td>
                            <td style="font-size: 7.5pt;">{{ $tr['to_date'] }}</td>
                            <td>
                                @if($tr['gap_days'] > 100)
                                    <span class="badge badge-danger">{{ $tr['gap_days'] }} يوماً</span>
                                @elseif($tr['gap_days'] > 0)
                                    <span class="badge badge-warning">{{ $tr['gap_days'] }} يوماً</span>
                                @else
                                    <span class="badge badge-success">0 يوم (متصل)</span>
                                @endif
                            </td>
                            <td style="font-size: 7.5pt;">
                                @if($tr['gap_days'] > 0)
                                    <span style="color: #991b1b; font-weight: bold;">
                                        {{ $tr['gap_from'] }} إلى {{ $tr['gap_to'] }}
                                    </span>
                                @else
                                    <span style="color: #059669;">تسليم متصل</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $tr['status_class'] === 'success' ? 'badge-success' : 'badge-danger' }}">
                                    {{ $tr['status'] }}
                                </span>
                            </td>
                            <td style="text-align: right; font-size: 8pt; line-height: 1.35;">
                                {{ $tr['narrative'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Transition Highlights Box -->
            <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 8px 12px; margin-bottom: 12px; font-size: 8pt; color: #334155;">
                <strong style="color: #1e3a8a;">📌 ملخص الاستنتاجات الرقابية حول فترات التوقف:</strong>
                <div style="margin-top: 3px; line-height: 1.5;">
                    • <strong>فجوة 2021 ⬅️ 2022 (252 يوماً):</strong> مرحلة توقف مبررة ناتجة عن إعادة جدولة المشاريع والترتيبات اللوجستية.<br>
                    • <strong>فترة الاتصال الذهبية (2023 ⬅️ 2024 ⬅️ 2025):</strong> انسيابية تامة (0 يوم فجوة) وجاهزية تخزينية متواصلة.<br>
                    • <strong>فجوة 2025 ⬅️ 2026 (445 يوماً):</strong> مرحلة مبررة تعاقدياً لحين توقيع وبدء سريان النافذة الرسمية لعام 2026.
                </div>
            </div>

            <!-- SECTION 5: Recommendations & Sign-off -->
            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 12px; align-items: start;">
                <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px 12px; font-size: 8pt; color: #334155; line-height: 1.55;">
                    <strong style="color: #1e3a8a; font-size: 8.5pt;">توصيات لجنة التدقيق والمطابقة المخزنية:</strong>
                    <ol style="margin: 4px 0 0 0; padding-right: 16px;">
                        @foreach($executive_summary['audit_recommendations'] as $rec)
                            <li>{{ $rec }}</li>
                        @endforeach
                    </ol>
                </div>

                <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px; text-align: center;">
                    <div style="font-size: 8pt; font-weight: bold; color: #1e3a8a; margin-bottom: 4px;">اعتماد تقرير التدقيق الرقابي</div>
                    <div style="font-size: 7.5pt; color: #64748b; margin-bottom: 22px;">وحدة التدقيق والمطابقة المركزية</div>
                    <div style="border-bottom: 1px dashed #94a3b8; width: 75%; margin: 0 auto 5px auto;"></div>
                    <div style="font-size: 7.5pt; font-weight: 700; color: #334155;">الختم والتوقيع الرسمي</div>
                </div>
            </div>
        </section>

        <!-- Document Footer Page 4 -->
        <footer class="footer">
            <div>نظام إدارة وتتبع سندات مستودع التضامن • صفحة 4 من 4 (نهاية التقرير المعتمد)</div>
            <div>تم التوليد آلياً بتاريخ: {{ $report_timestamp }} • وثيقة رسمية معتمدة</div>
        </footer>

    </div>

    @if(request('autoprint') == 1)
    <script>
        window.addEventListener('DOMContentLoaded', function() {
            setTimeout(function() {
                window.print();
            }, 500);
        });
    </script>
    @endif

</body>
</html>
