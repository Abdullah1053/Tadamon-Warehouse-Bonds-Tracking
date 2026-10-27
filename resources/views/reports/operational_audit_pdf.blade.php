<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير حصر دفاتر السندات (Stacks) والتحليل الزمني - مستودع التضامن</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm 10mm 10mm 10mm;
        }

        :root {
            --primary: #1e3a8a;
            --primary-dark: #172554;
            --primary-light: #eff6ff;
            --accent: #0284c7;
            --success: #059669;
            --warning: #d97706;
            --danger: #dc2626;
            --slate-800: #1e293b;
            --slate-700: #334155;
            --slate-600: #475569;
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
            background-color: #f1f5f9;
            color: var(--slate-800);
            margin: 0;
            padding: 0;
            font-size: 8.5pt;
            line-height: 1.35;
            direction: rtl;
        }

        .container {
            max-width: 210mm;
            margin: 0 auto;
            background: #ffffff;
            padding: 8mm 10mm;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        /* Screen Toolbar - Hidden on Print */
        .no-print-toolbar {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: #0f172a;
            color: #ffffff;
            padding: 8px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 12px rgba(0,0,0,0.25);
            margin-bottom: 12px;
        }

        .toolbar-title {
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .toolbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 6px;
            font-size: 12px;
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

        /* Page Layout & Breaks */
        .report-page {
            position: relative;
            background: #ffffff;
        }

        .page-break {
            page-break-after: always;
            break-after: page;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px dashed #cbd5e1;
        }

        @media print {
            body {
                background: #ffffff;
            }
            .container {
                padding: 0;
                box-shadow: none;
                max-width: 100%;
                width: 100%;
            }
            .no-print-toolbar {
                display: none !important;
            }
            .page-break {
                margin-bottom: 0;
                padding-bottom: 0;
                border-bottom: none;
                page-break-after: always;
                break-after: page;
            }
        }

        /* Header */
        .report-header {
            border-bottom: 2px solid #cbd5e1;
            padding-bottom: 8px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .org-badge {
            display: inline-block;
            background: #eff6ff;
            color: var(--primary);
            font-size: 8pt;
            font-weight: 700;
            padding: 1px 7px;
            border-radius: 4px;
            border: 1px solid #bfdbfe;
            margin-bottom: 3px;
        }

        .report-title {
            font-size: 13.5pt;
            font-weight: 800;
            color: var(--primary-dark);
            margin: 0 0 2px 0;
        }

        .report-subtitle {
            font-size: 8pt;
            color: var(--slate-600);
            margin: 0;
        }

        .meta-card {
            background: var(--slate-50);
            border: 1px solid var(--slate-200);
            border-radius: 6px;
            padding: 6px 10px;
            text-align: left;
            font-size: 7.5pt;
            line-height: 1.4;
        }

        /* Section Title */
        .section-header {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 10px;
            margin-bottom: 6px;
            border-bottom: 1.5px solid #e2e8f0;
            padding-bottom: 3px;
        }

        .section-num {
            background: var(--primary);
            color: #ffffff;
            font-size: 7.5pt;
            font-weight: 800;
            padding: 1px 6px;
            border-radius: 3px;
        }

        .section-title {
            font-size: 9.5pt;
            font-weight: 800;
            color: var(--primary-dark);
            margin: 0;
        }

        /* KPI Cards Grid */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 8px;
            margin-bottom: 10px;
        }

        .kpi-card {
            background: var(--slate-50);
            border: 1px solid var(--slate-200);
            border-radius: 6px;
            padding: 8px 10px;
            text-align: center;
            position: relative;
        }

        .kpi-card.highlight {
            background: #f0fdf4;
            border-color: #86efac;
        }

        .kpi-card.primary {
            background: #eff6ff;
            border-color: #bfdbfe;
        }

        .kpi-label {
            font-size: 7.5pt;
            font-weight: 700;
            color: var(--slate-600);
            margin-bottom: 2px;
        }

        .kpi-val {
            font-size: 15pt;
            font-weight: 900;
            color: var(--primary-dark);
            line-height: 1.1;
        }

        .kpi-card.highlight .kpi-val {
            color: #166534;
        }

        .kpi-sub {
            font-size: 7pt;
            color: #64748b;
            margin-top: 2px;
        }

        /* Visual Distribution Bar */
        .dist-bar-wrapper {
            background: var(--slate-50);
            border: 1px solid var(--slate-200);
            border-radius: 6px;
            padding: 6px 10px;
            margin-bottom: 10px;
        }

        .dist-bar-title {
            display: flex;
            justify-content: space-between;
            font-size: 7.5pt;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 5px;
        }

        .dist-bar {
            display: flex;
            height: 14px;
            border-radius: 4px;
            overflow: hidden;
            background: #e2e8f0;
        }

        .dist-seg {
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 6.5pt;
            font-weight: 800;
            overflow: hidden;
            white-space: nowrap;
        }

        .dist-legend {
            display: flex;
            gap: 12px;
            margin-top: 4px;
            font-size: 7pt;
            color: var(--slate-600);
            flex-wrap: wrap;
        }

        .dist-legend-item {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .legend-color {
            width: 8px;
            height: 8px;
            border-radius: 2px;
        }

        /* Compact Audit Tables */
        .audit-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
            margin-bottom: 8px;
            border: 1px solid #cbd5e1;
        }

        .audit-table th {
            background: #1e3a8a;
            color: #ffffff;
            font-weight: 800;
            text-align: center;
            padding: 4px 6px;
            border: 1px solid #1e3a8a;
            white-space: nowrap;
        }

        .audit-table td {
            padding: 4px 6px;
            border: 1px solid #e2e8f0;
            text-align: center;
            vertical-align: middle;
        }

        .audit-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .audit-table tbody tr.row-highlight {
            background-color: #eff6ff;
            font-weight: 700;
        }

        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 6.8pt;
            font-weight: 700;
            white-space: nowrap;
        }

        .badge-primary { background: #dbeafe; color: #1e40af; }
        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-warning { background: #fef3c7; color: #b45309; }
        .badge-danger  { background: #fee2e2; color: #b91c1c; }
        .badge-slate   { background: #f1f5f9; color: #475569; }

        /* Notes Box */
        .notes-box {
            background: var(--slate-50);
            border: 1px solid var(--slate-200);
            border-radius: 6px;
            padding: 6px 10px;
            font-size: 7.5pt;
            color: var(--slate-700);
            margin-bottom: 8px;
            line-height: 1.4;
        }

        /* Sign-off Block */
        .signoff-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 10px;
            align-items: start;
            margin-top: 8px;
        }

        .signoff-box {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            padding: 8px 10px;
            text-align: center;
        }

        /* Footer */
        .report-footer {
            border-top: 1px solid #cbd5e1;
            padding-top: 4px;
            margin-top: 8px;
            display: flex;
            justify-content: space-between;
            font-size: 6.8pt;
            color: #64748b;
        }
    </style>
</head>
<body>

    <!-- On-Screen Toolbar -->
    <div class="no-print-toolbar">
        <div class="toolbar-title">
            <svg style="width: 18px; height: 18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <span>تقرير حصر وتدقيق دفاتر السندات (Stacks) والتحليل الزمني • نسخة تنفيذية موجزة</span>
        </div>
        <div class="toolbar-actions">
            <a href="javascript:window.history.back()" class="btn btn-secondary">
                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>رجوع للمنظومة</span>
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                </svg>
                <span>طباعة / حفظ كملف PDF</span>
            </button>
        </div>
    </div>

    <div class="container">

        <!-- ================= PAGE 1 ================= -->
        <section class="report-page page-break">
            <!-- Header -->
            <header class="report-header">
                <div class="header-brand">
                    <span class="org-badge">إدارة مستودع التضامن المركزي • نظام تتبع وتدقيق الدفاتر والسندات</span>
                    <h1 class="report-title">تقرير حصر وتدقيق دفاتر السندات (Stacks) والتحليل الزمني</h1>
                    <p class="report-subtitle">حصر إجمالي لدفاتر السندات والفترات التشغيلية وفترات التوقف (2019 – 2026)</p>
                </div>
                <div class="meta-card">
                    <div><strong>تاريخ التقرير:</strong> {{ $report_date }}</div>
                    <div><strong>نطاق التدقيق:</strong> 2019 – 2026</div>
                    <div><strong>طبيعة الوثيقة:</strong> تقرير تنفيذي معتمد</div>
                </div>
            </header>

            <!-- KPI Cards Focused on Stacks -->
            <div class="kpi-grid">
                <div class="kpi-card highlight">
                    <div class="kpi-label">إجمالي دفاتر السندات (Stacks)</div>
                    <div class="kpi-val">{{ $kpis['total_stacks'] }}</div>
                    <div class="kpi-sub">دفتراً مسجلاً بالنظام</div>
                </div>
                <div class="kpi-card primary">
                    <div class="kpi-label">إجمالي السندات المقيدة</div>
                    <div class="kpi-val">{{ number_format($kpis['total_bonds']) }}</div>
                    <div class="kpi-sub">ضمن الدفاتر المعتمدة</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">أيام العمل الفعلية</div>
                    <div class="kpi-val">{{ number_format($kpis['total_active_days']) }}</div>
                    <div class="kpi-sub">يوماً تشغيلياً مقيداً</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">إجمالي المواد والبنود</div>
                    <div class="kpi-val">{{ number_format($kpis['total_items']) }}</div>
                    <div class="kpi-sub">بنداً تفصيلياً محصوراً</div>
                </div>
            </div>

            <!-- Stacks Distribution Visual Bar -->
            <div class="dist-bar-wrapper">
                <div class="dist-bar-title">
                    <span>توزيع دفاتر السندات (Stacks) عبر السنوات التشغيلية:</span>
                    <span>الإجمالي: {{ $kpis['total_stacks'] }} دفتر</span>
                </div>
                <div class="dist-bar">
                    <div class="dist-seg" style="width: 2%; background-color: #94a3b8;" title="2020: 1 دفتر">1</div>
                    <div class="dist-seg" style="width: 8%; background-color: #0284c7;" title="2022: 4 دفاتر">4</div>
                    <div class="dist-seg" style="width: 45%; background-color: #2563eb;" title="2023: 24 دفتر">24 دفتر (45%)</div>
                    <div class="dist-seg" style="width: 55%; background-color: #1e3a8a;" title="2024: 29 دفتر">29 دفتر (55%)</div>
                    <div class="dist-seg" style="width: 4%; background-color: #059669;" title="2025: 2 دفتر">2</div>
                    <div class="dist-seg" style="width: 6%; background-color: #d97706;" title="2026: 3 دفاتر">3</div>
                </div>
                <div class="dist-legend">
                    <span class="dist-legend-item"><span class="legend-color" style="background:#94a3b8;"></span> 2020 (1 دفتر)</span>
                    <span class="dist-legend-item"><span class="legend-color" style="background:#0284c7;"></span> 2022 (4 دفاتر)</span>
                    <span class="dist-legend-item"><span class="legend-color" style="background:#2563eb;"></span> 2023 (24 دفتراً)</span>
                    <span class="dist-legend-item"><span class="legend-color" style="background:#1e3a8a;"></span> 2024 (29 دفتراً)</span>
                    <span class="dist-legend-item"><span class="legend-color" style="background:#059669;"></span> 2025 (دفتران)</span>
                    <span class="dist-legend-item"><span class="legend-color" style="background:#d97706;"></span> 2026 (3 دفاتر)</span>
                </div>
            </div>

            <!-- SECTION 1: Master Stacks Table -->
            <div class="section-header">
                <span class="section-num">1</span>
                <h2 class="section-title">جدول الحصر السنوي لدفاتر السندات (Stacks) والبيانات التشغيلية</h2>
            </div>

            <table class="audit-table">
                <thead>
                    <tr>
                        <th style="width: 8%;">السنة</th>
                        <th style="width: 14%; background: #172554;">عدد الدفاتر (Stacks)</th>
                        <th style="width: 14%;">تسلسل السندات</th>
                        <th style="width: 10%;">عدد السندات</th>
                        <th style="width: 12%;">تاريخ أول قيد</th>
                        <th style="width: 12%;">تاريخ آخر قيد</th>
                        <th style="width: 10%;">أيام العمل</th>
                        <th style="width: 20%;">أطول فترة انقطاع مسجلة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($yearly_stats as $stat)
                        @php
                            $isPeak = in_array($stat['year'], [2023, 2024]);
                            $hasData = $stat['stacks_count'] > 0;
                        @endphp
                        <tr class="{{ $isPeak ? 'row-highlight' : '' }}">
                            <td><strong>{{ $stat['year'] }}</strong></td>
                            <td>
                                @if($hasData)
                                    <span class="badge {{ $isPeak ? 'badge-primary' : 'badge-slate' }}" style="font-size: 7.5pt; font-weight: 800;">
                                        {{ $stat['stacks_count'] }} {{ $stat['stacks_count'] == 1 ? 'دفتر' : ($stat['stacks_count'] == 2 ? 'دفتران' : 'دفتر') }}
                                    </span>
                                @else
                                    <span style="color: #94a3b8;">0</span>
                                @endif
                            </td>
                            <td style="font-size: 7pt; font-family: monospace;">
                                @if($hasData)
                                    {{ $stat['min_serial'] }} ⬅️ {{ $stat['max_serial'] }}
                                @else
                                    ---
                                @endif
                            </td>
                            <td>
                                @if($hasData)
                                    <strong>{{ number_format($stat['bonds_count']) }}</strong>
                                @else
                                    <span style="color: #94a3b8;">0</span>
                                @endif
                            </td>
                            <td style="font-size: 7.2pt;">{{ $stat['min_date'] }}</td>
                            <td style="font-size: 7.2pt;">{{ $stat['max_date'] }}</td>
                            <td>
                                @if($hasData)
                                    <strong>{{ $stat['active_days'] }}</strong> يوماً
                                @else
                                    <span style="color: #94a3b8;">0</span>
                                @endif
                            </td>
                            <td style="font-size: 7pt;">
                                @if($stat['longest_gap_days'] > 0)
                                    <span style="color: #b91c1c; font-weight: bold;">
                                        {{ $stat['longest_gap_days'] }} يوماً
                                    </span>
                                    <span style="color: #64748b; font-size: 6.8pt; display: block;">
                                        {{ $stat['longest_gap_desc'] }}
                                    </span>
                                @else
                                    <span style="color: #94a3b8;">---</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    <tr style="background: #e2e8f0; font-weight: 800; border-top: 2px solid #94a3b8;">
                        <td>الإجمالي</td>
                        <td>{{ $kpis['total_stacks'] }} دفتراً (Stack)</td>
                        <td>---</td>
                        <td>{{ number_format($kpis['total_bonds']) }}</td>
                        <td>{{ $kpis['earliest_date'] }}</td>
                        <td>{{ $kpis['latest_date'] }}</td>
                        <td>{{ number_format($kpis['total_active_days']) }} يوماً</td>
                        <td>---</td>
                    </tr>
                </tbody>
            </table>

            <!-- Quick Factual Summary Note -->
            <div class="notes-box">
                <strong style="color: #1e3a8a;">📌 ملخص حقائق الحصر والتسجيل:</strong>
                <div style="margin-top: 3px; display: flex; flex-direction: column; gap: 2px;">
                    <div>• <strong>إجمالي الدفاتر:</strong> تم حصر وتقييد <strong>53 دفتراً (Stacks)</strong> بإجمالي 2,632 سنداً مخزنياً.</div>
                    <div>• <strong>تركّز الدفاتر:</strong> يحتوي عامي 2023 و 2024 على <strong>53 دفتراً من أصل 53</strong> بنسبة تغطية 94% من إجمالي السجلات التاريخية.</div>
                    <div>• <strong>السندات الملغاة والمفقودة:</strong> بلغت السندات الملغاة والمفقودة مجتمعة <strong>{{ $kpis['total_missing_bonds'] + $kpis['total_cancelled_bonds'] }}</strong> سنداً فقط، وجميعها محصورة داخل دفاترها المعتمدة.</div>
                </div>
            </div>

            <!-- Page 1 Footer -->
            <footer class="report-footer">
                <div>نظام تتبع دفاتر وسندات مستودع التضامن • صفحة 1 من 2</div>
                <div>تاريخ التوليد: {{ $report_timestamp }}</div>
            </footer>
        </section>


        <!-- ================= PAGE 2 ================= -->
        <section class="report-page">
            <!-- Header Repeat -->
            <header class="report-header" style="margin-bottom: 8px;">
                <div class="header-brand">
                    <span class="org-badge">تقرير حصر دفاتر السندات (Stacks) • التحليل الزمني وفترات التوقف</span>
                    <h2 class="report-title" style="font-size: 11.5pt;">فترات الانقطاع التشغيلي بين السنوات وتوصيات الحوكمة</h2>
                </div>
                <div class="meta-card">
                    <div><strong>مستند:</strong> صفحة 2 من 2</div>
                    <div><strong>تاريخ الاعتماد:</strong> {{ $report_date }}</div>
                </div>
            </header>

            <!-- SECTION 2: Inter-Year Transition Gaps (Strictly Factual) -->
            <div class="section-header">
                <span class="section-num">2</span>
                <h2 class="section-title">بيان فترات الانقطاع وعدم التسجيل بين السنوات (Inter-Year Gaps)</h2>
            </div>

            <table class="audit-table">
                <thead>
                    <tr>
                        <th style="width: 14%;">الفترة الانتقالية</th>
                        <th style="width: 14%;">تاريخ آخر قيد</th>
                        <th style="width: 14%;">تاريخ أول قيد بالسنة التالية</th>
                        <th style="width: 12%;">مدة الفارق</th>
                        <th style="width: 22%;">نطاق فترة الانقطاع</th>
                        <th style="width: 24%;">حالة القيد والتسجيل</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($inter_year_transitions as $tr)
                        <tr style="{{ $tr['gap_days'] > 0 ? 'background-color: #fffaf0;' : '' }}">
                            <td><strong>{{ $tr['from_year'] }} ⬅️ {{ $tr['to_year'] }}</strong></td>
                            <td style="font-size: 7.2pt;">{{ $tr['from_date'] }}</td>
                            <td style="font-size: 7.2pt;">{{ $tr['to_date'] }}</td>
                            <td>
                                @if($tr['gap_days'] > 100)
                                    <span class="badge badge-danger">{{ $tr['gap_days'] }} يوماً</span>
                                @elseif($tr['gap_days'] > 0)
                                    <span class="badge badge-warning">{{ $tr['gap_days'] }} يوماً</span>
                                @else
                                    <span class="badge badge-success">0 يوم</span>
                                @endif
                            </td>
                            <td style="font-size: 7.2pt;">
                                @if($tr['gap_days'] > 0)
                                    <span style="color: #b91c1c; font-weight: bold;">
                                        {{ $tr['gap_from'] }} إلى {{ $tr['gap_to'] }}
                                    </span>
                                @else
                                    <span style="color: #059669;">تسجيل متصل دون انقطاع</span>
                                @endif
                            </td>
                            <td style="font-size: 7.2pt;">
                                <span class="badge {{ $tr['status_class'] === 'success' ? 'badge-success' : 'badge-danger' }}">
                                    {{ $tr['status'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- SECTION 3: Major Intra-Year Pauses (>= 7 Days) -->
            <div class="section-header">
                <span class="section-num">3</span>
                <h2 class="section-title">حصر فترات التوقف الرئيسية المسجلة خلال الأعوام (7 أيام فأكثر)</h2>
            </div>

            <table class="audit-table">
                <thead>
                    <tr>
                        <th style="width: 10%;">السنة</th>
                        <th style="width: 12%;">مدة التوقف</th>
                        <th style="width: 16%;">تاريخ بداية التوقف</th>
                        <th style="width: 16%;">تاريخ نهاية التوقف</th>
                        <th style="width: 18%;">تاريخ آخر عمل سابق</th>
                        <th style="width: 18%;">تاريخ أول عمل لاحق</th>
                        <th style="width: 10%;">الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @php $majorGapCount = 0; @endphp
                    @foreach($yearly_stats as $stat)
                        @foreach($stat['major_gaps'] as $gap)
                            @php $majorGapCount++; @endphp
                            <tr>
                                <td><strong>{{ $stat['year'] }}</strong></td>
                                <td>
                                    <span class="badge {{ $gap['days'] >= 30 ? 'badge-danger' : 'badge-warning' }}">
                                        {{ $gap['days'] }} يوماً
                                    </span>
                                </td>
                                <td style="font-size: 7.2pt;">{{ $gap['from_date'] }}</td>
                                <td style="font-size: 7.2pt;">{{ $gap['to_date'] }}</td>
                                <td style="font-size: 7.2pt;">{{ $gap['prior_work'] }}</td>
                                <td style="font-size: 7.2pt;">{{ $gap['next_work'] }}</td>
                                <td>
                                    <span class="badge badge-slate">توقف قيد</span>
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                    @if($majorGapCount === 0)
                        <tr>
                            <td colspan="7" style="color: #64748b;">لا توجد فترات توقف تتجاوز 7 أيام مسجلة بالنظام.</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <!-- SECTION 4: Recommendations & Sign-off Block -->
            <div class="signoff-grid">
                <div class="notes-box" style="margin-bottom: 0;">
                    <strong style="color: #1e3a8a; font-size: 8pt;">توصيات وإجراءات ضبط وحوكمة الدفاتر (Stacks):</strong>
                    <ol style="margin: 3px 0 0 0; padding-right: 14px; font-size: 7.3pt; line-height: 1.5;">
                        @foreach($executive_summary['audit_recommendations'] as $rec)
                            <li>{{ $rec }}</li>
                        @endforeach
                    </ol>
                </div>

                <div class="signoff-box">
                    <div style="font-size: 8pt; font-weight: bold; color: #1e3a8a; margin-bottom: 2px;">اعتماد التقرير الرقابي</div>
                    <div style="font-size: 7pt; color: #64748b; margin-bottom: 24px;">وحدة التدقيق والمطابقة المركزية</div>
                    <div style="border-bottom: 1px dashed #94a3b8; width: 75%; margin: 0 auto 4px auto;"></div>
                    <div style="font-size: 7pt; font-weight: 700; color: #334155;">الختم والتوقيع الرسمي</div>
                </div>
            </div>

            <!-- Page 2 Footer -->
            <footer class="report-footer">
                <div>نظام تتبع دفاتر وسندات مستودع التضامن • صفحة 2 من 2 (نهاية الوثيقة المعتمدة)</div>
                <div>تم التوليد آلياً بتاريخ: {{ $report_timestamp }} • وثيقة إلكترونية رسمية</div>
            </footer>
        </section>

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
