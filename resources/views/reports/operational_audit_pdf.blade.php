<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير التدقيق الرقابي والتحليل الزمني - مستودع التضامن (2019-2026)</title>
    <style>
        /* Modern Executive Document Styling */
        @page {
            size: A4 portrait;
            margin: 12mm 10mm 15mm 10mm;
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
            font-family: 'Segoe UI', Tahoma, Arial, 'DejaVu Sans', sans-serif;
            background-color: #f8fafc;
            color: var(--slate-800);
            margin: 0;
            padding: 0;
            font-size: 11pt;
            line-height: 1.5;
            direction: rtl;
        }

        .container {
            max-width: 210mm;
            margin: 0 auto;
            background: #ffffff;
            padding: 15mm 12mm;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        /* Screen Toolbar - Hidden on Print */
        .no-print-toolbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: #0f172a;
            color: #ffffff;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0,0,0,0.25);
            margin-bottom: 20px;
        }

        .toolbar-title {
            font-size: 14px;
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
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
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

        .btn-success {
            background-color: #059669;
            color: #ffffff;
        }
        .btn-success:hover {
            background-color: #047857;
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
            border-bottom: 3px double #cbd5e1;
            padding-bottom: 14px;
            margin-bottom: 20px;
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
            font-size: 10pt;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 4px;
            border: 1px solid #bfdbfe;
            margin-bottom: 6px;
        }

        .report-title {
            font-size: 18pt;
            font-weight: 800;
            color: var(--primary-dark);
            margin: 0 0 4px 0;
            letter-spacing: -0.5px;
        }

        .report-subtitle {
            font-size: 11pt;
            color: var(--slate-600);
            margin: 0;
            font-weight: 500;
        }

        .header-meta {
            text-align: left;
            direction: ltr;
            font-size: 9pt;
            color: var(--slate-600);
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 12px;
            min-width: 170px;
        }

        .header-meta div {
            margin-bottom: 3px;
        }

        /* Badges & Pills */
        .badge {
            display: inline-block;
            padding: 2px 7px;
            font-size: 8.5pt;
            font-weight: 700;
            border-radius: 4px;
            white-space: nowrap;
        }
        .badge-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .badge-warning { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-danger  { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-primary { background: #dbeafe; color: #1e40af; border: 1px solid #bfdbfe; }
        .badge-slate   { background: #e2e8f0; color: #334155; border: 1px solid #cbd5e1; }

        /* Section Containers */
        .section-block {
            margin-bottom: 24px;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }

        .section-num {
            background: var(--primary);
            color: #ffffff;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 10pt;
            font-weight: bold;
        }

        .section-title {
            font-size: 13pt;
            font-weight: 800;
            color: var(--slate-900);
            margin: 0;
        }

        /* KPI Grid */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 16px;
        }

        .kpi-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            border-top: 3px solid var(--primary);
        }

        .kpi-card.accent { border-top-color: var(--accent); }
        .kpi-card.success { border-top-color: var(--success); }
        .kpi-card.warning { border-top-color: var(--warning); }
        .kpi-card.danger { border-top-color: var(--danger); }

        .kpi-value {
            font-size: 16pt;
            font-weight: 800;
            color: var(--slate-900);
            line-height: 1.2;
            margin-bottom: 2px;
        }

        .kpi-label {
            font-size: 8.5pt;
            color: var(--slate-600);
            font-weight: 600;
        }

        .kpi-sub {
            font-size: 7.5pt;
            color: var(--slate-500);
            margin-top: 2px;
        }

        /* Narrative Callout */
        .narrative-box {
            background: #f8fafc;
            border-right: 4px solid var(--primary);
            border-top: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            border-left: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 14px;
            font-size: 10pt;
            color: var(--slate-700);
            line-height: 1.6;
        }

        .narrative-box p {
            margin: 0 0 8px 0;
        }
        .narrative-box p:last-child {
            margin-bottom: 0;
        }

        .phase-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px 14px;
            margin-bottom: 10px;
        }

        .phase-title {
            font-size: 10.5pt;
            font-weight: 700;
            color: var(--primary-dark);
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .phase-highlight {
            font-size: 9pt;
            color: #b45309;
            font-weight: 600;
            background: #fffbeb;
            padding: 2px 8px;
            border-radius: 4px;
            border: 1px solid #fef3c7;
            display: inline-block;
            margin-bottom: 4px;
        }

        /* Tables */
        table.audit-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 14px;
        }

        table.audit-table th {
            background: #1e3a8a;
            color: #ffffff;
            font-weight: 700;
            padding: 6px 8px;
            text-align: center;
            border: 1px solid #1e3a8a;
            white-space: nowrap;
        }

        table.audit-table td {
            padding: 5px 8px;
            border: 1px solid #cbd5e1;
            text-align: center;
            vertical-align: middle;
        }

        table.audit-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        table.audit-table tr.total-row {
            background-color: #e0f2fe !important;
            font-weight: 800;
            color: #0369a1;
            border-top: 2px solid #0284c7;
            border-bottom: 3px double #0284c7;
        }

        /* Visual Charts Component (Vectorized CSS/SVG Layout) */
        .charts-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
            margin-bottom: 16px;
        }

        .chart-box {
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }

        .chart-title {
            font-size: 10pt;
            font-weight: 700;
            color: var(--slate-800);
            margin-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 4px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Bar Chart Styles */
        .bar-group {
            display: flex;
            align-items: center;
            margin-bottom: 7px;
            font-size: 8.5pt;
        }

        .bar-label {
            width: 50px;
            font-weight: 700;
            color: var(--slate-700);
            text-align: right;
            padding-left: 6px;
        }

        .bar-track {
            flex: 1;
            height: 18px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
            display: flex;
            position: relative;
        }

        .bar-segment-active {
            background: linear-gradient(90deg, #0284c7, #38bdf8);
            height: 100%;
            transition: width 0.3s;
        }

        .bar-segment-idle {
            background: #cbd5e1;
            height: 100%;
        }

        .bar-value {
            width: 75px;
            text-align: left;
            padding-right: 6px;
            font-size: 8pt;
            font-weight: 600;
            color: var(--slate-600);
            white-space: nowrap;
        }

        /* Volume Bar */
        .volume-track {
            flex: 1;
            height: 16px;
            background: #f1f5f9;
            border-radius: 4px;
            overflow: hidden;
            position: relative;
        }

        .volume-bar {
            height: 100%;
            border-radius: 4px;
            background: linear-gradient(90deg, #4f46e5, #818cf8);
            min-width: 3px;
        }

        .volume-bar.peak {
            background: linear-gradient(90deg, #059669, #34d399);
        }

        /* Gap timeline indicators */
        .transition-card {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 8px;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }

        .transition-card.gap-flagged {
            border-right: 4px solid #ef4444;
            background: #fef2f2;
        }

        .transition-card.gap-connected {
            border-right: 4px solid #10b981;
            background: #f0fdf4;
        }

        .transition-dates {
            font-size: 9pt;
            font-weight: 700;
            color: var(--slate-800);
        }

        .transition-desc {
            font-size: 8.5pt;
            color: var(--slate-600);
            flex: 1;
            padding: 0 10px;
        }

        .page-break {
            page-break-before: always;
            break-before: page;
        }

        /* Print Media Overrides */
        @media print {
            body {
                background: #ffffff;
                font-size: 10pt;
            }
            .container {
                max-width: 100% !important;
                padding: 0 !important;
                box-shadow: none !important;
            }
            .no-print, .no-print-toolbar {
                display: none !important;
            }
            .section-block {
                page-break-inside: avoid;
                break-inside: avoid;
            }
        }

        .footer {
            margin-top: 24px;
            padding-top: 10px;
            border-top: 1px solid #cbd5e1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 8pt;
            color: var(--slate-500);
        }
    </style>
</head>
<body>

    <!-- On-screen Interactive Toolbar (Hidden when printing/converting to PDF) -->
    <div class="no-print-toolbar no-print">
        <div class="toolbar-title">
            <span>📄</span>
            <span>تقرير التدقيق الرقابي والتحليل الزمني (مستودع التضامن 2019 - 2026)</span>
        </div>
        <div class="toolbar-actions">
            <a href="{{ route('stacks.exportAuditPdf', ['download' => 1]) }}" class="btn btn-success" title="تحميل التقرير كملف PDF رسمي">
                <span>⬇️ تحميل كملف PDF</span>
            </a>
            <button onclick="window.print()" class="btn btn-primary" title="طباعة فورية أو حفظ باستخدام المتصفح">
                <span>🖨️ طباعة التقرير</span>
            </button>
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                <span>↩️ العودة للوحة التحكم</span>
            </a>
        </div>
    </div>

    <div class="container">

        <!-- Report Header -->
        <header class="report-header">
            <div class="header-brand">
                <div class="org-badge">وثيقة رقابية معتمدة • وحدة التدقيق وإدارة المستودعات</div>
                <h1 class="report-title">تقرير التدقيق الرقابي والتحليل الزمني الشامل</h1>
                <p class="report-subtitle">سجل فترات التشغيل، فحص أيام العمل والتوقف، وتحليل الفجوات الانتقالية (2019 – 2026)</p>
            </div>
            <div class="header-meta">
                <div><strong>نظام:</strong> إدارة وتتبع السندات</div>
                <div><strong>المستودع:</strong> مستودع التضامن المركزي</div>
                <div><strong>تاريخ الإصدار:</strong> {{ $report_date }}</div>
                <div><strong>إجمالي السندات:</strong> {{ number_format($kpis['total_bonds']) }} سنداً</div>
                <div><strong>الحالة العامة:</strong> <span class="badge badge-success">تم التدقيق والمطابقة</span></div>
            </div>
        </header>

        <!-- SECTION 1: Human-Readable Executive Summary -->
        <section class="section-block">
            <div class="section-header">
                <span class="section-num">1</span>
                <h2 class="section-title">الملخص التنفيذي والسرد الزمني لمسار العمليات (Executive Summary)</h2>
            </div>

            <!-- Overarching Narrative -->
            <div class="narrative-box">
                <p><strong>نظرة عامة على المسار التشغيلي:</strong> {{ $executive_summary['overview'] }}</p>
                <p>تميزت هذه المسيرة بانتقال تدريجي من المرحلة التحضيرية في أواخر عام 2019 وحتى بلوغ <strong>الذروة التشغيلية المطلقة في عامي 2023 و2024</strong> (بإجمالي 2,425 سنداً)، تلاها تطبيق حوكمة رقابية صارمة مطلع عام 2025 باشتراط إرفاق السند الرسمي، وصولاً إلى استيفاء كامل السندات ضمن النافذة التعاقدية لعام 2026.</p>
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
                    <div class="kpi-sub">من أصل {{ number_format($kpis['total_calendar_days']) }} يوماً تقويمياً</div>
                </div>
                <div class="kpi-card warning">
                    <div class="kpi-value" style="color: #d97706;">{{ number_format($kpis['total_idle_days']) }}</div>
                    <div class="kpi-label">أيام التوقف والركود المرحلي</div>
                    <div class="kpi-sub">تشمل العطل والفترات الانتقالية</div>
                </div>
                <div class="kpi-card accent">
                    <div class="kpi-value" style="color: #0284c7;">{{ $kpis['overall_activity_pct'] }}%</div>
                    <div class="kpi-label">معدل التغطية التشغيلية العام</div>
                    <div class="kpi-sub">على مدار 8 سنوات متتالية</div>
                </div>
            </div>

            <!-- 3 Strategic Eras Narrative -->
            <div style="margin-top: 10px;">
                @foreach($executive_summary['strategic_phases'] as $phase)
                    <div class="phase-card">
                        <div class="phase-title">
                            <span>{{ $phase['phase_title'] }}</span>
                            <span class="badge badge-slate">{{ $phase['period'] }}</span>
                        </div>
                        <div class="phase-highlight">{{ $phase['highlight'] }}</div>
                        <div style="font-size: 9.5pt; color: #475569; line-height: 1.5;">{{ $phase['description'] }}</div>
                    </div>
                @endforeach
            </div>
        </section>

        <!-- SECTION 2: Visual Statistics & Charts -->
        <section class="section-block">
            <div class="section-header">
                <span class="section-num">2</span>
                <h2 class="section-title">الإحصائيات البصرية والرسوم البيانية (Visual Statistics & Charts)</h2>
            </div>

            <div class="charts-container">
                <!-- Chart 1: Active Work Days vs Idle Days -->
                <div class="chart-box">
                    <div class="chart-title">
                        <span>أيام العمل الفعلية مقابل أيام التوقف (لكل عام)</span>
                        <span style="font-size: 7.5pt; font-weight: normal; color: #64748b;">
                            <span style="display:inline-block; width:8px; height:8px; background:#0284c7; border-radius:2px;"></span> عمل &nbsp;
                            <span style="display:inline-block; width:8px; height:8px; background:#cbd5e1; border-radius:2px;"></span> توقف
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

            <!-- Operational Continuity & Gap Map (Mini visual band) -->
            <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px 14px;">
                <div style="font-size: 9.5pt; font-weight: 700; color: #1e293b; margin-bottom: 6px;">
                    خريطة المسار الزمني لفترات الاستمرار والانقطاع (Timeline Continuity Radar)
                </div>
                <div style="display: flex; height: 20px; border-radius: 4px; overflow: hidden; background: #e2e8f0; font-size: 8pt; font-weight: bold; text-align: center; color: white;">
                    <div style="width: 10%; background: #64748b;" title="2019-2020: بداية العمل">2019-20</div>
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
        </section>

        <div class="page-break"></div>

        <!-- SECTION 3: Master Yearly Statistics Table -->
        <section class="section-block">
            <div class="section-header">
                <span class="section-num">3</span>
                <h2 class="section-title">إحصائيات السنوات وجداول التوزيع والتشغيل (Yearly Statistics Breakdown)</h2>
            </div>

            <p style="font-size: 9pt; color: #475569; margin-bottom: 8px;">
                جدول مقارن يوضح تفاصيل النشاط الفعلي لكل عام تقويمي (2019 – 2026)، متضمناً أيام العمل، فترات التوقف، تصنيف السندات (سليم، ملغي، مفقود)، ونطاق التواريخ المسجلة:
            </p>

            <table class="audit-table">
                <thead>
                    <tr>
                        <th>السنة</th>
                        <th>أيام السنة</th>
                        <th>أيام العمل</th>
                        <th>نسبة النشاط</th>
                        <th>أيام التوقف</th>
                        <th>إجمالي السندات</th>
                        <th>السليمة</th>
                        <th>الملغاة</th>
                        <th>المفقودة</th>
                        <th>المواد</th>
                        <th>تاريخ أول سند</th>
                        <th>تاريخ آخر سند</th>
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
                            <td style="font-size: 8pt;">{{ $st['min_date'] ?? '---' }}</td>
                            <td style="font-size: 8pt;">{{ $st['max_date'] ?? '---' }}</td>
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
                        <td style="font-size: 8pt;">{{ $kpis['earliest_date'] }}</td>
                        <td style="font-size: 8pt;">{{ $kpis['latest_date'] }}</td>
                    </tr>
                </tbody>
            </table>
        </section>

        <!-- SECTION 4: Intra-Year Missing/Idle Days Records -->
        <section class="section-block">
            <div class="section-header">
                <span class="section-num">4</span>
                <h2 class="section-title">سجل فترات وأيام التوقف / السجلات غير المسجلة داخل كل عام (Intra-Year Gaps)</h2>
            </div>

            <p style="font-size: 9pt; color: #475569; margin-bottom: 8px;">
                رصد دقيق لكافة فترات التوقف والانقطاع الزمني التي وقعت بين تواريخ العمل المسجلة خلال كل سنة، مع إبراز الفترات الاستراتيجية المعتمدة والتوقفات المرحلية:
            </p>

            @foreach($yearly_stats as $yr => $st)
                <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px 12px; margin-bottom: 12px; page-break-inside: avoid; break-inside: avoid;">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; margin-bottom: 6px;">
                        <span style="font-weight: 800; color: #1e3a8a; font-size: 10pt;">
                            🗓️ السنة: {{ $yr }} &nbsp;|&nbsp; عدد السندات: {{ $st['bonds_count'] }} &nbsp;|&nbsp; الأيام النشطة: {{ $st['active_days'] }} من أصل {{ $st['calendar_days'] }} يوماً
                        </span>
                        <span class="badge {{ count($st['intra_year_gaps']) > 0 ? 'badge-warning' : 'badge-success' }}">
                            {{ count($st['intra_year_gaps']) }} فترات توقف مسجلة
                        </span>
                    </div>

                    <div style="font-size: 8.5pt; color: #475569; margin-bottom: 6px; line-height: 1.4;">
                        <strong>التعليق الرقابي:</strong> {{ $st['narrative'] }}
                    </div>

                    @if(!empty($st['official_intervals']))
                        <div style="background: #f1f5f9; border-radius: 4px; padding: 6px 10px; margin-bottom: 6px; font-size: 8pt; color: #334155;">
                            <strong>الفترات التشغيلية المعتمدة:</strong>
                            @foreach($st['official_intervals'] as $oi)
                                <span style="display: inline-block; margin-left: 12px;">
                                    • فترة {{ $oi['period_num'] }}: من {{ $oi['start_date'] }} إلى {{ $oi['end_date'] }} ({{ $oi['notes'] }})
                                </span>
                            @endforeach
                        </div>
                    @endif

                    @if(!empty($st['intra_year_gaps']))
                        <table class="audit-table" style="margin-bottom: 0; font-size: 8pt;">
                            <thead>
                                <tr style="background: #334155;">
                                    <th style="padding: 4px;">#</th>
                                    <th style="padding: 4px;">من تاريخ التوقف</th>
                                    <th style="padding: 4px;">إلى تاريخ التوقف</th>
                                    <th style="padding: 4px;">أيام التوقف (الفجوة)</th>
                                    <th style="padding: 4px;">تاريخ العمل السابق</th>
                                    <th style="padding: 4px;">تاريخ العمل التالي</th>
                                    <th style="padding: 4px; text-align: right;">طبيعة التوقف وتوصيف الفجوة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $gapCounter = 1; @endphp
                                @foreach($st['intra_year_gaps'] as $gap)
                                    <tr>
                                        <td>{{ $gapCounter++ }}</td>
                                        <td style="color: #991b1b; font-weight: bold;">{{ $gap['from_date'] }}</td>
                                        <td style="color: #991b1b; font-weight: bold;">{{ $gap['to_date'] }}</td>
                                        <td>
                                            <span class="badge {{ $gap['days'] >= 30 ? 'badge-danger' : ($gap['days'] >= 7 ? 'badge-warning' : 'badge-slate') }}">
                                                {{ $gap['days'] }} يوم
                                            </span>
                                        </td>
                                        <td>{{ $gap['prior_work'] }}</td>
                                        <td>{{ $gap['next_work'] }}</td>
                                        <td style="text-align: right;">{{ $gap['description'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div style="font-size: 8pt; color: #059669; font-weight: bold; background: #ecfdf5; padding: 4px 8px; border-radius: 4px;">
                            ✔️ لا توجد فجوات توقف مسجلة داخل هذا العام، العمليات كانت مستمرة بانتظام تام أو لم تبدأ بعد.
                        </div>
                    @endif
                </div>
            @endforeach
        </section>

        <div class="page-break"></div>

        <!-- SECTION 5: Inter-Year Transition & Gap Analysis -->
        <section class="section-block">
            <div class="section-header">
                <span class="section-num">5</span>
                <h2 class="section-title">تحليل الانتقال التشغيلي وفجوات التوقف بين الأعوام (Inter-Year Transitions)</h2>
            </div>

            <p style="font-size: 9pt; color: #475569; margin-bottom: 8px;">
                يوضح هذا القسم الحساب الدقيق لفارق الأيام (فجوة التوقف / الركود) بين <strong>تاريخ نهاية آخر عمل في كل عام وتاريخ بداية أول عمل في العام التالي</strong>، وصولاً إلى آخر تاريخ مسجل في عام 2026:
            </p>

            <table class="audit-table">
                <thead>
                    <tr>
                        <th>الانتقال بين الأعوام</th>
                        <th>تاريخ نهاية العمل (السنة السابقة)</th>
                        <th>تاريخ بداية العمل (السنة التالية)</th>
                        <th>فارق الأيام (أيام التوقف)</th>
                        <th>نطاق التواريخ غير المسجلة (الفجوة)</th>
                        <th>حالة الانتقال</th>
                        <th>التحليل الرقابي والدلالة التشغيلية</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inter_year_transitions as $tr)
                        <tr style="{{ $tr['gap_days'] > 0 ? 'background-color: #fffaf0;' : '' }}">
                            <td><strong>{{ $tr['from_year'] }} ⬅️ {{ $tr['to_year'] }}</strong></td>
                            <td style="font-weight: 600;">{{ $tr['from_date'] }}</td>
                            <td style="font-weight: 600;">{{ $tr['to_date'] }}</td>
                            <td>
                                @if($tr['gap_days'] > 100)
                                    <span class="badge badge-danger">{{ $tr['gap_days'] }} يوماً</span>
                                @elseif($tr['gap_days'] > 0)
                                    <span class="badge badge-warning">{{ $tr['gap_days'] }} يوماً</span>
                                @else
                                    <span class="badge badge-success">0 يوم (متصل)</span>
                                @endif
                            </td>
                            <td>
                                @if($tr['gap_days'] > 0)
                                    <span style="color: #991b1b; font-size: 8pt; font-weight: bold;">
                                        {{ $tr['gap_from'] }} إلى {{ $tr['gap_to'] }}
                                    </span>
                                @else
                                    <span style="color: #059669; font-size: 8pt;">تسليم مباشر دون انقطاع</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $tr['status_class'] === 'success' ? 'badge-success' : 'badge-danger' }}">
                                    {{ $tr['status'] }}
                                </span>
                            </td>
                            <td style="text-align: right; font-size: 8pt; line-height: 1.4;">
                                {{ $tr['narrative'] }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Transition Highlights Box -->
            <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 12px; margin-top: 10px; font-size: 8.5pt; color: #334155;">
                <strong style="color: #1e3a8a; font-size: 9pt;">📌 أهم الاستنتاجات الرقابية حول فترات التوقف الانتقالية:</strong>
                <ul style="margin: 6px 0 0 0; padding-right: 18px; line-height: 1.6;">
                    <li><strong>فجوة 2021 إلى 2022:</strong> استمرت <strong>252 يوماً</strong> معتمدة (أو 379 يوماً حتى تاريخ أول سند مسجل بالنظام في سبتمبر 2022). مثلت هذه الفترة مرحلة توقف كاملة نتجت عن إعادة جدولة المشاريع والترتيبات اللوجستية قبل استئناف حركة التوريد والصرف.</li>
                    <li><strong>فترة الاتصال الذهبية (2023 ⬅️ 2024 ⬅️ 2025):</strong> سجلت انسيابية مطلقة (0 يوم فجوة)؛ حيث كان العمل في 31 ديسمبر يليه عمل مباشر في 1 يناير، مما يدل على النشاط التخزيني المتواصل والجاهزية العالية.</li>
                    <li><strong>فجوة 2025 إلى 2026:</strong> امتدت لـ <strong>445 يوماً</strong> بين ختام سندات يناير 2025 وانطلاق العمليات ضمن النافذة التعاقدية الرسمية لعام 2026، وهي فجوة انتقالية مبررة تعاقدياً لحين توقيع وبدء سريان مخصصات 2026.</li>
                </ul>
            </div>
        </section>

        <!-- SECTION 6: Audit Recommendations & Sign-off -->
        <section class="section-block" style="margin-bottom: 0;">
            <div class="section-header">
                <span class="section-num">6</span>
                <h2 class="section-title">التوصيات الرقابية وخاتمة التدقيق (Audit Recommendations & Sign-off)</h2>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 14px; align-items: start;">
                <div style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 6px; padding: 12px; font-size: 8.5pt; color: #334155; line-height: 1.6;">
                    <strong style="color: #1e3a8a;">توصيات لجنة التدقيق والمطابقة:</strong>
                    <ol style="margin: 6px 0 0 0; padding-right: 16px;">
                        @foreach($executive_summary['audit_recommendations'] as $rec)
                            <li>{{ $rec }}</li>
                        @endforeach
                        <li>الاعتماد المستمر لآلية تجميع وحزم السندات (Stacks) لضمان عدم وجود سندات سائبة أو أرقام تسلسلية غير مربوطة بدفاترها الأصلية.</li>
                    </ol>
                </div>

                <div style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px; padding: 12px; text-align: center;">
                    <div style="font-size: 8.5pt; font-weight: bold; color: #1e3a8a; margin-bottom: 8px;">اعتماد تقرير التدقيق الرقابي</div>
                    <div style="font-size: 7.5pt; color: #64748b; margin-bottom: 25px;">وحدة التدقيق والمطابقة المخزنية المركزية</div>
                    <div style="border-bottom: 1px dashed #94a3b8; width: 80%; margin: 0 auto 6px auto;"></div>
                    <div style="font-size: 8pt; font-weight: 600; color: #334155;">الختم والتوقيع الرسمي</div>
                </div>
            </div>
        </section>

        <!-- Document Footer -->
        <footer class="footer">
            <div>نظام إدارة وتتبع سندات مستودع التضامن • تم التوليد آلياً بتاريخ: {{ $report_timestamp }}</div>
            <div>صفحة وثيقة رسمية معتمدة • سرية ومخصصة لأغراض التدقيق الرقابي الداخلي</div>
        </footer>

    </div>

</body>
</html>
