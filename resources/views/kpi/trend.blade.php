@extends('layouts.dashboard')

@push('styles')
<style>
    /* ══ ENTERPRISE TREND PAGE STYLING ══ */
    .stat-tile-soft {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px 20px;
        transition: all 0.25s ease;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
    }
    body.theme-dark .stat-tile-soft, [data-bs-theme="dark"] .stat-tile-soft {
        background: #0f172a !important;
        border-color: #334155 !important;
    }

    .badge-soft-emerald {
        background: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    body.theme-dark .badge-soft-emerald, [data-bs-theme="dark"] .badge-soft-emerald {
        background: rgba(16, 185, 129, 0.18) !important;
        color: #6ee7b7 !important;
        border-color: rgba(16, 185, 129, 0.35) !important;
    }

    .badge-soft-rose {
        background: #ffe4e6;
        color: #9f1239;
        border: 1px solid #fecdd3;
    }
    body.theme-dark .badge-soft-rose, [data-bs-theme="dark"] .badge-soft-rose {
        background: rgba(244, 63, 94, 0.18) !important;
        color: #fda4af !important;
        border-color: rgba(244, 63, 94, 0.35) !important;
    }

    .badge-soft-sky {
        background: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
    }
    body.theme-dark .badge-soft-sky, [data-bs-theme="dark"] .badge-soft-sky {
        background: rgba(2, 132, 199, 0.18) !important;
        color: #7dd3fc !important;
        border-color: rgba(2, 132, 199, 0.35) !important;
    }

    .badge-soft-amber {
        background: #fef3c7;
        color: #92400e;
        border: 1px solid #fde68a;
    }
    body.theme-dark .badge-soft-amber, [data-bs-theme="dark"] .badge-soft-amber {
        background: rgba(245, 158, 11, 0.18) !important;
        color: #fcd34d !important;
        border-color: rgba(245, 158, 11, 0.35) !important;
    }

    .perf-pill {
        font-weight: 700 !important;
        font-size: 0.82rem !important;
        padding: 5px 12px !important;
        border-radius: 20px !important;
        display: inline-block !important;
    }
    .perf-excellent { background-color: #ecfdf5 !important; color: #047857 !important; border: 1px solid #a7f3d0 !important; }
    .perf-good { background-color: #f0f9ff !important; color: #0369a1 !important; border: 1px solid #bae6fd !important; }
    .perf-satisfactory { background-color: #fffbeb !important; color: #b45309 !important; border: 1px solid #fde68a !important; }
    .perf-improvement { background-color: #fff7ed !important; color: #c2410c !important; border: 1px solid #ffedd5 !important; }
    .perf-unsatisfactory { background-color: #fff1f2 !important; color: #be123c !important; border: 1px solid #fecdd3 !important; }

    body.theme-dark .perf-excellent, [data-bs-theme="dark"] .perf-excellent { background-color: rgba(16, 185, 129, 0.18) !important; color: #6ee7b7 !important; border-color: rgba(16, 185, 129, 0.35) !important; }
    body.theme-dark .perf-good, [data-bs-theme="dark"] .perf-good { background-color: rgba(2, 132, 199, 0.18) !important; color: #7dd3fc !important; border-color: rgba(2, 132, 199, 0.35) !important; }
    body.theme-dark .perf-satisfactory, [data-bs-theme="dark"] .perf-satisfactory { background-color: rgba(245, 158, 11, 0.18) !important; color: #fcd34d !important; border-color: rgba(245, 158, 11, 0.35) !important; }
    body.theme-dark .perf-improvement, [data-bs-theme="dark"] .perf-improvement { background-color: rgba(249, 115, 22, 0.18) !important; color: #fdba74 !important; border-color: rgba(249, 115, 22, 0.35) !important; }
    body.theme-dark .perf-unsatisfactory, [data-bs-theme="dark"] .perf-unsatisfactory { background-color: rgba(244, 63, 94, 0.18) !important; color: #fda4af !important; border-color: rgba(244, 63, 94, 0.35) !important; }

    body.theme-dark .table th, [data-bs-theme="dark"] .table th {
        background-color: #0f172a !important;
        color: #cbd5e1 !important;
        border-color: #334155 !important;
    }
    body.theme-dark .table td, [data-bs-theme="dark"] .table td {
        border-color: #334155 !important;
        color: #f1f5f9 !important;
    }
    body.theme-dark .form-select, [data-bs-theme="dark"] .form-select {
        background-color: #0f172a !important;
        border-color: #334155 !important;
        color: #f8fafc !important;
    }
    @media print {
        /* Hide all navigation, sidebar, header controls, buttons, and footers */
        #sidebar, .sidebar-wrapper, .sidebar-menu, header, .mobile-sticky-topbar, footer, .btn, form, #timeRangeForm, .no-print {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            width: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        /* Reset #app, #main, and layout containers to full width 100% */
        html, body, #app, #main, .page-heading, .page-content, .container-fluid {
            position: static !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            min-width: 100% !important;
            max-width: 100% !important;
            left: 0 !important;
            top: 0 !important;
            background-color: #ffffff !important;
            color: #000000 !important;
        }

        /* Cards and stat tiles formatting for print */
        .card, .stat-tile-soft {
            border: 1px solid #cbd5e1 !important;
            box-shadow: none !important;
            background-color: #ffffff !important;
            color: #000000 !important;
            page-break-inside: avoid !important;
        }

        /* Table styling for print */
        .table th, .table td {
            border-color: #cbd5e1 !important;
            color: #000000 !important;
            background-color: #ffffff !important;
        }

        .text-dark, h3, h5, h2, span, p {
            color: #000000 !important;
        }
    }
</style>
@endpush

@section('content')
<div class="page-heading">
    <div class="row align-items-center mb-3">
        <div class="col-md-7">
            <h3 class="mb-0 text-dark font-weight-bold">KPI Performance Trend - {{ $employee->fullname }}</h3>
            <p class="text-muted mb-0">{{ $employee->department->name ?? '-' }} • {{ $employee->role?->title ?? 'Staff' }}</p>
        </div>
        <div class="col-md-5 text-end">
            <div class="d-flex justify-content-end align-items-center gap-2">
                <form method="GET" class="d-flex align-items-center gap-2" id="timeRangeForm">
                    <label class="fw-bold text-dark mb-0 me-1">Range: </label>
                    <select name="months" class="form-select form-select-sm" style="width: 175px;" onchange="this.form.submit()">
                        <option value="1" {{ $months == 1 ? 'selected' : '' }}>1 Month (Bulan Ini)</option>
                        <option value="3" {{ $months == 3 ? 'selected' : '' }}>Last 3 Months</option>
                        <option value="6" {{ $months == 6 ? 'selected' : '' }}>Last 6 Months</option>
                        <option value="9" {{ $months == 9 ? 'selected' : '' }}>Last 9 Months</option>
                        <option value="12" {{ $months == 12 ? 'selected' : '' }}>Last 12 Months</option>
                    </select>
                </form>
                <a href="{{ route('reports.export-trend-pdf', ['id' => $employee->id, 'months' => $months]) }}" class="btn btn-sm btn-primary shadow-sm" target="_blank">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
                </a>
                <button type="button" class="btn btn-sm btn-outline-secondary shadow-sm" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
                <a href="{{ route('kpi.show', $employee->id) }}" class="btn btn-sm btn-outline-secondary shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to Report
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <div class="container-fluid px-0">
        @php
            $scores = array_column($trendData, 'composite_score');
            $avgScore = count($scores) > 0 ? array_sum($scores) / count($scores) : 0;
            $firstScore = $scores[0] ?? 0;
            $lastScore = end($scores) ?: 0;
            $delta = $lastScore - $firstScore;
            $latestLevel = end($trendData)['performance_level'] ?? 'na';
        @endphp

        <!-- Executive Stat Cards Row -->
        <div class="row mb-4">
            <!-- Avg Score -->
            <div class="col-12 col-md-4 mb-3">
                <div class="stat-tile-soft h-100 d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.78rem;">Rata-Rata Skor Komposisi</small>
                        <h2 class="mb-0 fw-extrabold text-dark mt-1">{{ round($avgScore, 1) }}<span class="fs-6 text-muted"> /100</span></h2>
                        <div class="mt-2">
                            @if($months == 1)
                                <small class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1 fw-bold" style="font-size: 0.73rem;">
                                    <i class="bi bi-calculator me-1"></i> Nilai 1 Bulan Ini (Dibagi 1)
                                </small>
                            @else
                                <small class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1 fw-bold" style="font-size: 0.73rem;">
                                    <i class="bi bi-calculator me-1"></i> Rata-Rata {{ count($scores) }} Bulan (Total ÷ {{ count($scores) }})
                                </small>
                            @endif
                        </div>
                    </div>
                    <div class="badge-soft-sky rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-speedometer2 fs-4"></i>
                    </div>
                </div>
            </div>

            <!-- Trend Delta -->
            <div class="col-12 col-md-4 mb-3">
                <div class="stat-tile-soft h-100 d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.78rem;">Performance Growth</small>
                        <h2 class="mb-0 fw-extrabold {{ $delta >= 0 ? 'text-success' : 'text-danger' }} mt-1">
                            {{ $delta >= 0 ? '+' : '' }}{{ round($delta, 1) }}<span class="fs-6 text-muted"> pts</span>
                        </h2>
                        <small class="text-muted">{{ $delta >= 0 ? 'Positive Trajectory' : 'Needs Development' }}</small>
                    </div>
                    <div class="{{ $delta >= 0 ? 'badge-soft-emerald' : 'badge-soft-rose' }} rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi {{ $delta >= 0 ? 'bi-graph-up-arrow' : 'bi-graph-down-arrow' }} fs-4"></i>
                    </div>
                </div>
            </div>

            <!-- Current Status -->
            <div class="col-12 col-md-4 mb-3">
                <div class="stat-tile-soft h-100 d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.78rem;">Latest Month Status</small>
                        <div class="mt-2">
                            @switch($latestLevel)
                                @case('excellent') <span class="perf-pill perf-excellent">Excellent</span> @break
                                @case('good') <span class="perf-pill perf-good">Good</span> @break
                                @case('satisfactory') <span class="perf-pill perf-satisfactory">Satisfactory</span> @break
                                @case('needs_improvement') <span class="perf-pill perf-improvement">Needs Improvement</span> @break
                                @default <span class="perf-pill perf-unsatisfactory">Unsatisfactory</span>
                            @endswitch
                        </div>
                        <small class="text-muted d-block mt-1">Score: {{ round($lastScore, 1) }} / 100</small>
                    </div>
                    <div class="badge-soft-amber rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="bi bi-award-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- ApexCharts Interactive Trend Charts Row -->
        <div class="row mb-4">
            <!-- Chart 1: Overall Composite Score Trend -->
            <div class="col-12 col-lg-7 mb-4 mb-lg-0">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="bi bi-graph-up text-primary me-2"></i>Overall Composite Score Trend
                        </h5>
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill fw-bold">Spline Trajectory</span>
                    </div>
                    <div class="card-body">
                        <div id="overallTrendChart" style="width: 100%; min-height: 300px;"></div>
                    </div>
                </div>
            </div>

            <!-- Chart 2: Checkout vs Work Log Breakdown -->
            <div class="col-12 col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="bi bi-bar-chart-line-fill text-info me-2"></i>Metric Comparison
                        </h5>
                        <span class="badge bg-info bg-opacity-10 text-info px-3 py-1 rounded-pill fw-bold">Checkout vs Log %</span>
                    </div>
                    <div class="card-body">
                        <div id="metricComparisonChart" style="width: 100%; min-height: 300px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Performance Audit Table -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="bi bi-table me-2 text-warning"></i>Monthly Performance Audit Log
                        </h5>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-1 rounded-pill fw-bold">Historical Record</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Period</th>
                                        <th>Checkout Compliance</th>
                                        <th>Work Log Submission</th>
                                        <th>Composite Score</th>
                                        <th>Performance Level</th>
                                        <th class="text-end">Growth Delta</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($trendData as $index => $data)
                                    <tr>
                                        <td><strong class="text-dark">{{ $data['period_label'] }}</strong></td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fw-bold text-dark">{{ round($data['checkout_pct'], 1) }}%</span>
                                                <div class="progress flex-grow-1" style="height: 5px; max-width: 80px; border-radius: 10px;">
                                                    <div class="progress-bar bg-info" style="width: {{ min($data['checkout_pct'], 100) }}%;"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fw-bold text-dark">{{ round($data['log_pct'], 1) }}%</span>
                                                <div class="progress flex-grow-1" style="height: 5px; max-width: 80px; border-radius: 10px;">
                                                    <div class="progress-bar bg-success" style="width: {{ min($data['log_pct'], 100) }}%;"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $badgeClass = $data['composite_score'] >= 90 ? 'badge-soft-emerald' :
                                                             ($data['composite_score'] >= 75 ? 'badge-soft-sky' :
                                                             ($data['composite_score'] >= 60 ? 'badge-soft-amber' : 'badge-soft-rose'));
                                            @endphp
                                            <span class="badge {{ $badgeClass }} px-3 py-2 fw-bold fs-6">
                                                {{ round($data['composite_score'], 2) }} / 100
                                            </span>
                                        </td>
                                        <td>
                                            @switch($data['performance_level'])
                                                @case('excellent') <span class="perf-pill perf-excellent">Excellent</span> @break
                                                @case('good') <span class="perf-pill perf-good">Good</span> @break
                                                @case('satisfactory') <span class="perf-pill perf-satisfactory">Satisfactory</span> @break
                                                @case('needs_improvement') <span class="perf-pill perf-improvement">Needs Improvement</span> @break
                                                @default <span class="perf-pill perf-unsatisfactory">Unsatisfactory</span>
                                            @endswitch
                                        </td>
                                        <td class="text-end fw-bold">
                                            @if($index > 0)
                                                @php
                                                    $prev = $trendData[$index - 1]['composite_score'];
                                                    $curr = $data['composite_score'];
                                                    $diff = $curr - $prev;
                                                @endphp
                                                @if($diff > 0)
                                                    <span class="text-success"><i class="bi bi-arrow-up-short"></i>+{{ round($diff, 1) }}</span>
                                                @elseif($diff < 0)
                                                    <span class="text-danger"><i class="bi bi-arrow-down-short"></i>{{ round($diff, 1) }}</span>
                                                @else
                                                    <span class="text-muted"><i class="bi bi-dash"></i> 0.0</span>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const isDarkMode = document.body.classList.contains('theme-dark') || 
                           document.documentElement.getAttribute('data-bs-theme') === 'dark';
        const labelColor = isDarkMode ? '#cbd5e1' : '#475569';

        const labels = {!! json_encode(array_column($trendData, 'period_label')) !!};
        const scores = {!! json_encode(array_column($trendData, 'composite_score')) !!};
        const checkoutPcts = {!! json_encode(array_column($trendData, 'checkout_pct')) !!};
        const logPcts = {!! json_encode(array_column($trendData, 'log_pct')) !!};

        // 1. OVERALL COMPOSITE SCORE AREA CHART
        const overallOptions = {
            series: [{
                name: 'Composite Score',
                data: scores
            }],
            chart: {
                type: 'area',
                height: 310,
                background: 'transparent',
                toolbar: { show: false }
            },
            colors: ['#2563eb'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [0, 90, 100]
                }
            },
            stroke: { curve: 'smooth', width: 3 },
            dataLabels: { enabled: false },
            xaxis: {
                categories: labels,
                labels: { style: { colors: labelColor } }
            },
            yaxis: {
                min: 0,
                max: 100,
                labels: { style: { colors: labelColor } }
            },
            grid: {
                borderColor: isDarkMode ? '#334155' : '#e2e8f0',
                strokeDashArray: 4
            },
            tooltip: {
                theme: isDarkMode ? 'dark' : 'light',
                y: { formatter: (val) => val + " / 100 Points" }
            }
        };

        const overallChart = new ApexCharts(document.querySelector("#overallTrendChart"), overallOptions);
        overallChart.render();

        // 2. DUAL METRIC COMPARISON BAR CHART
        const metricOptions = {
            series: [
                { name: 'Checkout Compliance %', data: checkoutPcts },
                { name: 'Work Log Submission %', data: logPcts }
            ],
            chart: {
                type: 'bar',
                height: 310,
                background: 'transparent',
                toolbar: { show: false }
            },
            colors: ['#0284c7', '#10b981'],
            plotOptions: {
                bar: {
                    borderRadius: 5,
                    columnWidth: '50%'
                }
            },
            dataLabels: { enabled: false },
            xaxis: {
                categories: labels,
                labels: { style: { colors: labelColor } }
            },
            yaxis: {
                min: 0,
                max: 100,
                labels: { style: { colors: labelColor } }
            },
            grid: {
                borderColor: isDarkMode ? '#334155' : '#e2e8f0',
                strokeDashArray: 4
            },
            legend: {
                position: 'top',
                labels: { colors: labelColor }
            },
            tooltip: {
                theme: isDarkMode ? 'dark' : 'light',
                y: { formatter: (val) => val + "%" }
            }
        };

        const metricChart = new ApexCharts(document.querySelector("#metricComparisonChart"), metricOptions);
        metricChart.render();
    });
</script>
@endpush
