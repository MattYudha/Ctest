<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>KPI_Trend_Report_{{ str_replace(' ', '_', $employee->fullname) }}_{{ $months }}M</title>
    <style>
        /* Enterprise DomPDF Design System */
        @page {
            margin: 0;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #1e293b;
            line-height: 1.5;
            font-size: 9.5pt;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        
        .clearfix {
            clear: both;
        }

        /* Header Section */
        .header-banner {
            background-color: #0f172a; /* Slate 900 */
            color: #ffffff;
            padding: 30px 40px;
        }
        .company-title {
            font-size: 16pt;
            font-weight: bold;
            color: #ffffff;
            margin: 0 0 4px 0;
        }
        .company-subtitle {
            font-size: 8.5pt;
            color: #94a3b8;
            margin: 0;
        }
        .report-badge {
            float: right;
            background-color: #2563eb;
            color: #ffffff;
            padding: 6px 14px;
            border-radius: 4px;
            font-size: 9pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Container */
        .content-container {
            padding: 30px 40px;
        }

        /* Section Titles */
        .doc-title {
            font-size: 18pt;
            font-weight: 800;
            color: #0f172a;
            margin: 0 0 4px 0;
            text-transform: uppercase;
        }
        .doc-subtitle {
            font-size: 9.5pt;
            color: #64748b;
            margin: 0 0 20px 0;
        }

        /* Employee Metadata Box */
        .meta-card {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 14px 18px;
            margin-bottom: 25px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-label {
            font-size: 7.5pt;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            padding-bottom: 2px;
        }
        .meta-val {
            font-size: 10pt;
            font-weight: 700;
            color: #0f172a;
        }

        /* Executive Stat Tiles */
        .tiles-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px 0;
            margin-left: -12px;
            margin-right: -12px;
            margin-bottom: 25px;
        }
        .tile-cell {
            width: 33.33%;
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 14px;
            text-align: center;
        }
        .tile-title {
            font-size: 7.5pt;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 6px;
        }
        .tile-value {
            font-size: 18pt;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 4px;
        }
        .tile-sub {
            font-size: 7.5pt;
            color: #64748b;
        }

        /* Status Colors */
        .text-emerald { color: #059669; }
        .text-sky { color: #0284c7; }
        .text-amber { color: #d97706; }
        .text-rose { color: #e11d48; }

        /* Audit Table */
        .audit-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .audit-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 8pt;
            font-weight: 800;
            text-transform: uppercase;
            padding: 10px 12px;
            text-align: left;
            border-bottom: 2px solid #cbd5e1;
        }
        .audit-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9pt;
            color: #1e293b;
        }
        .audit-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        /* Pill Badges */
        .badge-pill {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 8pt;
            font-weight: bold;
            display: inline-block;
        }
        .badge-excellent { background-color: #d1fae5; color: #065f46; }
        .badge-good { background-color: #e0f2fe; color: #0369a1; }
        .badge-satisfactory { background-color: #fef3c7; color: #92400e; }
        .badge-improvement { background-color: #ffedd5; color: #c2410c; }
        .badge-unsatisfactory { background-color: #ffe4e6; color: #9f1239; }

        /* Signatures Section */
        .signatures-table {
            width: 100%;
            margin-top: 40px;
            border-collapse: collapse;
        }
        .sig-cell {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .sig-box {
            height: 60px;
        }
        .sig-name {
            font-weight: bold;
            font-size: 10pt;
            color: #0f172a;
            border-bottom: 1px solid #0f172a;
            display: inline-block;
            padding-bottom: 2px;
            min-width: 180px;
        }
        .sig-title {
            font-size: 8pt;
            color: #64748b;
            margin-top: 4px;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 30px;
            background-color: #f8fafc;
            border-top: 1px solid #e2e8f0;
            padding: 6px 40px;
            font-size: 7.5pt;
            color: #94a3b8;
        }
    </style>
</head>
<body>

    <!-- Header Banner -->
    <div class="header-banner">
        <div style="float: left;">
            <h1 class="company-title">{{ $config->company_name ?? 'ARATECHNOLOGY' }}</h1>
            <p class="company-subtitle">{{ $config->company_address ?? 'Jakarta, Indonesia' }} • Confidential Enterprise Report</p>
        </div>
        <div class="report-badge">
            Official KPI Audit
        </div>
        <div class="clearfix"></div>
    </div>

    <!-- Content Body -->
    <div class="content-container">
        <!-- Document Title -->
        <h2 class="doc-title">KPI Performance Trend Report</h2>
        <p class="doc-subtitle">Historical Performance Analytics & Metric Breakdown for {{ $employee->fullname }}</p>

        <!-- Employee Metadata Card -->
        <div class="meta-card">
            <table class="meta-table">
                <tr>
                    <td style="width: 30%;">
                        <div class="meta-label">Employee Name</div>
                        <div class="meta-val">{{ $employee->fullname }}</div>
                    </td>
                    <td style="width: 25%;">
                        <div class="meta-label">Department</div>
                        <div class="meta-val">{{ $employee->department->name ?? '-' }}</div>
                    </td>
                    <td style="width: 25%;">
                        <div class="meta-label">Position / Role</div>
                        <div class="meta-val">{{ $employee->role?->title ?? 'Staff' }}</div>
                    </td>
                    <td style="width: 20%;">
                        <div class="meta-label">Evaluation Window</div>
                        <div class="meta-val">Last {{ $months }} Months</div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Executive Stat Tiles -->
        <table class="tiles-table">
            <tr>
                <td class="tile-cell">
                    <div class="tile-title">Average Composite Score</div>
                    <div class="tile-value text-sky">{{ round($avgScore, 1) }} <span style="font-size: 10pt; color: #64748b;">/ 100</span></div>
                    <div class="tile-sub">Average over {{ $months }} months</div>
                </td>
                <td class="tile-cell">
                    <div class="tile-title">Performance Growth</div>
                    <div class="tile-value {{ $delta >= 0 ? 'text-emerald' : 'text-rose' }}">
                        {{ $delta >= 0 ? '+' : '' }}{{ round($delta, 1) }} <span style="font-size: 10pt; color: #64748b;">pts</span>
                    </div>
                    <div class="tile-sub">{{ $delta >= 0 ? 'Positive Trajectory' : 'Needs Development' }}</div>
                </td>
                <td class="tile-cell">
                    <div class="tile-title">Latest Month Status</div>
                    <div class="tile-value" style="font-size: 13pt; margin-top: 4px; margin-bottom: 6px;">
                        @switch($latestLevel)
                            @case('excellent') <span class="badge-pill badge-excellent">EXCELLENT</span> @break
                            @case('good') <span class="badge-pill badge-good">GOOD</span> @break
                            @case('satisfactory') <span class="badge-pill badge-satisfactory">SATISFACTORY</span> @break
                            @case('needs_improvement') <span class="badge-pill badge-improvement">NEEDS IMPROVEMENT</span> @break
                            @default <span class="badge-pill badge-unsatisfactory">UNSATISFACTORY</span>
                        @endswitch
                    </div>
                    <div class="tile-sub">Latest Score: {{ round($lastScore, 1) }} / 100</div>
                </td>
            </tr>
        </table>

        <!-- Monthly Performance Audit Table -->
        <h4 style="font-size: 11pt; font-weight: bold; color: #0f172a; margin: 0 0 10px 0; text-transform: uppercase;">
            Monthly Audit Breakdowns & Metrics
        </h4>
        <table class="audit-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Period</th>
                    <th style="width: 20%;">Checkout Compliance</th>
                    <th style="width: 20%;">Work Log Submission</th>
                    <th style="width: 15%;">Composite Score</th>
                    <th style="width: 18%;">Performance Level</th>
                    <th style="width: 12%; text-align: right;">Growth Delta</th>
                </tr>
            </thead>
            <tbody>
                @foreach($trendData as $index => $data)
                <tr>
                    <td><strong>{{ $data['period_label'] }}</strong></td>
                    <td>{{ round($data['checkout_pct'], 1) }}%</td>
                    <td>{{ round($data['log_pct'], 1) }}%</td>
                    <td><strong>{{ round($data['composite_score'], 2) }} / 100</strong></td>
                    <td>
                        @switch($data['performance_level'])
                            @case('excellent') <span class="badge-pill badge-excellent">Excellent</span> @break
                            @case('good') <span class="badge-pill badge-good">Good</span> @break
                            @case('satisfactory') <span class="badge-pill badge-satisfactory">Satisfactory</span> @break
                            @case('needs_improvement') <span class="badge-pill badge-improvement">Needs Improvement</span> @break
                            @default <span class="badge-pill badge-unsatisfactory">Unsatisfactory</span>
                        @endswitch
                    </td>
                    <td style="text-align: right; font-weight: bold;">
                        @if($index > 0)
                            @php
                                $prev = $trendData[$index - 1]['composite_score'];
                                $curr = $data['composite_score'];
                                $diff = $curr - $prev;
                            @endphp
                            @if($diff > 0)
                                <span class="text-emerald">+{{ round($diff, 1) }}</span>
                            @elseif($diff < 0)
                                <span class="text-rose">{{ round($diff, 1) }}</span>
                            @else
                                <span style="color: #94a3b8;">0.0</span>
                            @endif
                        @else
                            <span style="color: #94a3b8;">-</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Signatures & Approvals Section -->
        <table class="signatures-table">
            <tr>
                <td class="sig-cell">
                    <div style="font-size: 8pt; color: #64748b; margin-bottom: 8px;">Prepared & Verified By:</div>
                    <div class="sig-box"></div>
                    <div class="sig-name">HR Administrator</div>
                    <div class="sig-title">Human Capital Department</div>
                </td>
                <td class="sig-cell">
                    <div style="font-size: 8pt; color: #64748b; margin-bottom: 8px;">Approved By:</div>
                    <div class="sig-box"></div>
                    <div class="sig-name">{{ $employee->supervisor ? $employee->supervisor->fullname : 'Department Head' }}</div>
                    <div class="sig-title">Head of Department</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div style="float: left;">Generated on {{ now()->format('d F Y, H:i') }} WIB • COREVO Enterprise Reporting Engine</div>
        <div style="float: right;">Page 1 of 1</div>
        <div class="clearfix"></div>
    </div>
</body>
</html>
