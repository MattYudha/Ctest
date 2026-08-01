@extends('layouts.dashboard')

@push('styles')
<style>
    /* Soft Pastel KPI Score & Performance Styling */
    .kpi-score-badge {
        font-weight: 700 !important;
        font-size: 0.9rem !important;
        padding: 5px 12px !important;
        border-radius: 8px !important;
        display: inline-block !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
        transition: all 0.2s ease !important;
    }
    
    .score-excellent {
        background-color: #d1fae5 !important;
        color: #065f46 !important;
        border: 1px solid #a7f3d0 !important;
    }
    .score-good {
        background-color: #e0f2fe !important;
        color: #0369a1 !important;
        border: 1px solid #bae6fd !important;
    }
    .score-satisfactory {
        background-color: #fef3c7 !important;
        color: #92400e !important;
        border: 1px solid #fde68a !important;
    }
    .score-unsatisfactory {
        background-color: #ffe4e6 !important;
        color: #9f1239 !important;
        border: 1px solid #fecdd3 !important;
    }

    /* Soft Pastel Status Pills */
    .perf-pill {
        font-weight: 700 !important;
        font-size: 0.82rem !important;
        padding: 5px 12px !important;
        border-radius: 20px !important;
        display: inline-block !important;
    }
    .perf-excellent {
        background-color: #ecfdf5 !important;
        color: #047857 !important;
        border: 1px solid #a7f3d0 !important;
    }
    .perf-good {
        background-color: #f0f9ff !important;
        color: #0369a1 !important;
        border: 1px solid #bae6fd !important;
    }
    .perf-satisfactory {
        background-color: #fffbeb !important;
        color: #b45309 !important;
        border: 1px solid #fde68a !important;
    }
    .perf-improvement {
        background-color: #fff7ed !important;
        color: #c2410c !important;
        border: 1px solid #ffedd5 !important;
    }
    .perf-unsatisfactory {
        background-color: #fff1f2 !important;
        color: #be123c !important;
        border: 1px solid #fecdd3 !important;
    }

    /* Count Pill Badges - Soft Pastel */
    .count-pill {
        font-weight: 700 !important;
        font-size: 0.82rem !important;
        padding: 4px 10px !important;
        border-radius: 12px !important;
        min-width: 32px !important;
        text-align: center !important;
        display: inline-block !important;
    }
    .count-achieved {
        background-color: #d1fae5 !important;
        color: #065f46 !important;
        border: 1px solid #a7f3d0 !important;
    }
    .count-warning {
        background-color: #fef3c7 !important;
        color: #92400e !important;
        border: 1px solid #fde68a !important;
    }
    .count-critical {
        background-color: #ffe4e6 !important;
        color: #9f1239 !important;
        border: 1px solid #fecdd3 !important;
    }

    /* ══ DARK MODE SOFT PASTEL OVERRIDES ══ */
    body.theme-dark, [data-bs-theme="dark"], .theme-dark {
        color: #f1f5f9 !important;
    }

    body.theme-dark .score-excellent, [data-bs-theme="dark"] .score-excellent {
        background-color: rgba(16, 185, 129, 0.18) !important;
        color: #6ee7b7 !important;
        border-color: rgba(16, 185, 129, 0.35) !important;
    }
    body.theme-dark .score-good, [data-bs-theme="dark"] .score-good {
        background-color: rgba(2, 132, 199, 0.18) !important;
        color: #7dd3fc !important;
        border-color: rgba(2, 132, 199, 0.35) !important;
    }
    body.theme-dark .score-satisfactory, [data-bs-theme="dark"] .score-satisfactory {
        background-color: rgba(245, 158, 11, 0.18) !important;
        color: #fcd34d !important;
        border-color: rgba(245, 158, 11, 0.35) !important;
    }
    body.theme-dark .score-unsatisfactory, [data-bs-theme="dark"] .score-unsatisfactory {
        background-color: rgba(244, 63, 94, 0.18) !important;
        color: #fda4af !important;
        border-color: rgba(244, 63, 94, 0.35) !important;
    }

    body.theme-dark .perf-excellent, [data-bs-theme="dark"] .perf-excellent {
        background-color: rgba(16, 185, 129, 0.18) !important;
        color: #6ee7b7 !important;
        border-color: rgba(16, 185, 129, 0.35) !important;
    }
    body.theme-dark .perf-good, [data-bs-theme="dark"] .perf-good {
        background-color: rgba(2, 132, 199, 0.18) !important;
        color: #7dd3fc !important;
        border-color: rgba(2, 132, 199, 0.35) !important;
    }
    body.theme-dark .perf-satisfactory, [data-bs-theme="dark"] .perf-satisfactory {
        background-color: rgba(245, 158, 11, 0.18) !important;
        color: #fcd34d !important;
        border-color: rgba(245, 158, 11, 0.35) !important;
    }
    body.theme-dark .perf-improvement, [data-bs-theme="dark"] .perf-improvement {
        background-color: rgba(249, 115, 22, 0.18) !important;
        color: #fdba74 !important;
        border-color: rgba(249, 115, 22, 0.35) !important;
    }
    body.theme-dark .perf-unsatisfactory, [data-bs-theme="dark"] .perf-unsatisfactory {
        background-color: rgba(244, 63, 94, 0.18) !important;
        color: #fda4af !important;
        border-color: rgba(244, 63, 94, 0.35) !important;
    }

    body.theme-dark .count-achieved, [data-bs-theme="dark"] .count-achieved {
        background-color: rgba(16, 185, 129, 0.2) !important;
        color: #6ee7b7 !important;
        border: 1px solid rgba(16, 185, 129, 0.3) !important;
    }
    body.theme-dark .count-warning, [data-bs-theme="dark"] .count-warning {
        background-color: rgba(245, 158, 11, 0.2) !important;
        color: #fcd34d !important;
        border: 1px solid rgba(245, 158, 11, 0.3) !important;
    }
    body.theme-dark .count-critical, [data-bs-theme="dark"] .count-critical {
        background-color: rgba(244, 63, 94, 0.2) !important;
        color: #fda4af !important;
        border: 1px solid rgba(244, 63, 94, 0.3) !important;
    }

    body.theme-dark .text-dark,
    [data-bs-theme="dark"] .text-dark,
    .theme-dark .text-dark {
        color: #f1f5f9 !important;
    }
    body.theme-dark .text-muted,
    [data-bs-theme="dark"] .text-muted,
    .theme-dark .text-muted {
        color: #94a3b8 !important;
    }
    body.theme-dark h1, [data-bs-theme="dark"] h1,
    body.theme-dark h2, [data-bs-theme="dark"] h2,
    body.theme-dark h3, [data-bs-theme="dark"] h3,
    body.theme-dark h4, [data-bs-theme="dark"] h4,
    body.theme-dark h5, [data-bs-theme="dark"] h5,
    body.theme-dark h6, [data-bs-theme="dark"] h6 {
        color: #f8fafc !important;
    }
    body.theme-dark .card,
    [data-bs-theme="dark"] .card,
    .theme-dark .card {
        background-color: #1e293b !important;
        border-color: #334155 !important;
    }
    body.theme-dark .card-header.bg-white,
    [data-bs-theme="dark"] .card-header.bg-white,
    .theme-dark .card-header.bg-white,
    body.theme-dark .card-header,
    [data-bs-theme="dark"] .card-header {
        background-color: #0f172a !important;
        color: #f8fafc !important;
        border-bottom: 1px solid #334155 !important;
    }
    body.theme-dark .table,
    [data-bs-theme="dark"] .table {
        color: #f1f5f9 !important;
    }
    body.theme-dark .table th,
    [data-bs-theme="dark"] .table th,
    body.theme-dark #performanceTable th,
    [data-bs-theme="dark"] #performanceTable th {
        background-color: #0f172a !important;
        color: #cbd5e1 !important;
        border-color: #334155 !important;
    }
    body.theme-dark .table td,
    [data-bs-theme="dark"] .table td,
    body.theme-dark #performanceTable td,
    [data-bs-theme="dark"] #performanceTable td {
        border-color: #334155 !important;
        color: #f1f5f9 !important;
    }
    body.theme-dark .table-hover tbody tr:hover,
    [data-bs-theme="dark"] .table-hover tbody tr:hover {
        background-color: rgba(255, 255, 255, 0.05) !important;
    }
    body.theme-dark .form-control,
    [data-bs-theme="dark"] .form-control {
        background-color: #0f172a !important;
        border-color: #334155 !important;
        color: #f8fafc !important;
    }
    body.theme-dark label,
    [data-bs-theme="dark"] label {
        color: #f1f5f9 !important;
    }

    /* ══ ENTERPRISE MONTHLY RECAP SHOWCASE STYLING ══ */
    .icon-pill-blue {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: rgba(37, 99, 235, 0.12);
        color: #2563eb;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }
    .icon-pill-emerald {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: rgba(16, 185, 129, 0.12);
        color: #10b981;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }
    .icon-pill-amber {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: rgba(245, 158, 11, 0.12);
        color: #d97706;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }
    .icon-pill-rose {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        background: rgba(244, 63, 94, 0.12);
        color: #e11d48;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
    }

    .perf-rank-badge {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.85rem;
        flex-shrink: 0;
    }
    .perf-rank-badge.rank-1 {
        background: linear-gradient(135deg, #b45309 0%, #f59e0b 100%);
        color: #ffffff;
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.35);
    }
    .perf-rank-badge.rank-2 {
        background: linear-gradient(135deg, #475569 0%, #94a3b8 100%);
        color: #ffffff;
    }
    .perf-rank-badge.rank-3 {
        background: linear-gradient(135deg, #78350f 0%, #b45309 100%);
        color: #ffffff;
    }
    .perf-rank-badge.rank-subtle {
        background: rgba(148, 163, 184, 0.15);
        color: #64748b;
    }
    body.theme-dark .perf-rank-badge.rank-subtle, [data-bs-theme="dark"] .perf-rank-badge.rank-subtle {
        background: rgba(255, 255, 255, 0.08);
        color: #94a3b8;
    }

    .avatar-initials-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 0.95rem;
        flex-shrink: 0;
        box-shadow: 0 2px 6px rgba(37, 99, 235, 0.2);
    }
</style>
@endpush

@section('content')

<div class="page-heading">
    <div class="row align-items-center mb-3">
        <div class="col-md-5">
            <h3 class="mb-0 text-dark font-weight-bold">Monthly Performance Recap</h3>
            <p class="text-muted mb-0">Overview of team performance and KPI rankings</p>
        </div>
        <div class="col-md-7 text-right">
            <div class="d-flex justify-content-end align-items-center flex-wrap gap-2">
                <form method="GET" class="form-inline d-flex align-items-center me-2">
                    <label class="me-2 fw-bold text-dark mb-0">Period: </label>
                    <input type="month" name="period" value="{{ $period }}" class="form-control" onchange="this.form.submit()">
                </form>

                <a href="{{ route('reports.export-csv') }}?period={{ $period }}" class="btn btn-success btn-md shadow-sm">
                    <i class="bi bi-download me-1"></i> Export CSV
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <div class="container-fluid px-0">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(!($isGenerated ?? true))
            <!-- Empty State Card when KPI has not been generated by HR -->
            <div class="card border-0 shadow-sm my-4">
                <div class="card-body text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-calendar-x text-warning display-4"></i>
                    </div>
                    <h4 class="fw-bold text-dark">KPI Belum Digenerate untuk Periode {{ \Carbon\Carbon::createFromFormat('Y-m', $period)->format('F Y') }}</h4>
                    <p class="text-muted max-w-lg mx-auto mb-4">
                        Laporan KPI pada periode ini belum diterbitkan oleh HR / Master Admin. <br>
                        Penilaian dihitung berdasarkan <strong>Kepatuhan Checkout Presensi</strong> dan <strong>Pengisian Log Kerja Harian</strong>.
                    </p>
                    @if($canGenerate ?? false)
                        <a href="{{ route('kpi.company') }}?period={{ $period }}" class="btn btn-primary btn-lg px-4 shadow">
                            <i class="bi bi-box-arrow-up-right me-2"></i> Buka Company KPI untuk Generate
                        </a>
                    @else
                        <span class="badge bg-secondary px-3 py-2 fs-6">Menunggu HR Administrator memproses KPI bulan ini</span>
                    @endif
                </div>
            </div>
        @else
        <!-- Summary Statistics -->
        <div class="row mb-4">
            <div class="col-12 col-sm-6 col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-left: 5px solid #2563eb !important;">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-primary font-weight-bold mb-1" style="font-size: 0.82rem; text-transform: uppercase;">Total Evaluated</h6>
                            <h2 class="mb-0 fw-extrabold text-dark">{{ count($kpiData) }}</h2>
                            <small class="text-muted">Employees Audited</small>
                        </div>
                        <div class="icon-pill-blue">
                            <i class="bi bi-people-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-left: 5px solid #10b981 !important;">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-success font-weight-bold mb-1" style="font-size: 0.82rem; text-transform: uppercase;">Average Score</h6>
                            <h2 class="mb-0 fw-extrabold text-dark">{{ round(array_sum(array_column($kpiData, 'composite_score')) / (count($kpiData) ?: 1), 2) }}</h2>
                            <small class="text-muted">Overall Team KPI</small>
                        </div>
                        <div class="icon-pill-emerald">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-left: 5px solid #d97706 !important;">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-warning font-weight-bold mb-1" style="font-size: 0.82rem; text-transform: uppercase;">Excellent Performers</h6>
                            <h2 class="mb-0 fw-extrabold text-dark">{{ count(array_filter($kpiData, fn($e) => $e['performance_level'] === 'excellent')) }}</h2>
                            <small class="text-muted">Score ≥ 90 Points</small>
                        </div>
                        <div class="icon-pill-amber">
                            <i class="bi bi-award-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3 mb-3">
                <div class="card border-0 shadow-sm h-100" style="border-left: 5px solid #e11d48 !important;">
                    <div class="card-body p-3 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-danger font-weight-bold mb-1" style="font-size: 0.82rem; text-transform: uppercase;">Below Target</h6>
                            <h2 class="mb-0 fw-extrabold text-dark">{{ count(array_filter($kpiData, fn($e) => $e['performance_level'] === 'needs_improvement' || $e['performance_level'] === 'unsatisfactory')) }}</h2>
                            <small class="text-muted">Require Support</small>
                        </div>
                        <div class="icon-pill-rose">
                            <i class="bi bi-exclamation-octagon-fill"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="bi bi-trophy-fill text-warning me-2"></i>Performance Ranking - {{ \Carbon\Carbon::createFromFormat('Y-m', $period)->format('F Y') }}
                        </h5>
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill fw-bold">Official HR Audit Record</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="performanceTable">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">Rank</th>
                                        <th>Employee</th>
                                        <th>Department</th>
                                        <th style="width: 180px;">Score Meter</th>
                                        <th>Performance</th>
                                        <th class="text-center">Achieved</th>
                                        <th class="text-center">Warning</th>
                                        <th class="text-center">Critical</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($kpiData as $index => $data)
                                    <tr>
                                        <td>
                                            <div class="perf-rank-badge {{ $index === 0 ? 'rank-1' : ($index === 1 ? 'rank-2' : ($index === 2 ? 'rank-3' : 'rank-subtle')) }}">
                                                @if($index === 0)
                                                    <i class="bi bi-trophy-fill"></i>
                                                @else
                                                    #{{ $index + 1 }}
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                @if(!empty($data['employee']->profile_photo))
                                                    <img src="{{ asset('storage/' . $data['employee']->profile_photo) }}" 
                                                         alt="{{ $data['employee']->fullname }}" 
                                                         class="rounded-circle border shadow-sm flex-shrink-0" 
                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                @else
                                                    <div class="avatar-initials-circle flex-shrink-0">
                                                        {{ strtoupper(substr($data['employee']->fullname, 0, 1)) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <strong class="text-dark fs-6 d-block mb-0">{{ $data['employee']->fullname }}</strong>
                                                    <small class="text-muted">{{ $data['employee']->role?->title ?? 'Staff' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary text-white px-2 py-1 fw-semibold">{{ $data['employee']->department->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $scoreClass = $data['composite_score'] >= 90 ? 'score-excellent' : 
                                                            ($data['composite_score'] >= 75 ? 'score-good' : 
                                                            ($data['composite_score'] >= 60 ? 'score-satisfactory' : 'score-unsatisfactory'));
                                                $barClass = $data['composite_score'] >= 90 ? 'bg-success' : 
                                                            ($data['composite_score'] >= 75 ? 'bg-info' : 
                                                            ($data['composite_score'] >= 60 ? 'bg-warning' : 'bg-danger'));
                                            @endphp
                                            <div class="d-flex flex-column gap-1">
                                                <span class="kpi-score-badge {{ $scoreClass }}" style="width: fit-content;">
                                                    {{ round($data['composite_score'], 2) }}/100
                                                </span>
                                                <div class="progress" style="height: 5px; border-radius: 10px;">
                                                    <div class="progress-bar {{ $barClass }}" style="width: {{ min($data['composite_score'], 100) }}%;"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @switch($data['performance_level'])
                                                @case('excellent')
                                                    <span class="perf-pill perf-excellent">Excellent</span>
                                                    @break
                                                @case('good')
                                                    <span class="perf-pill perf-good">Good</span>
                                                    @break
                                                @case('satisfactory')
                                                    <span class="perf-pill perf-satisfactory">Satisfactory</span>
                                                    @break
                                                @case('needs_improvement')
                                                    <span class="perf-pill perf-improvement">Needs Improvement</span>
                                                    @break
                                                @default
                                                    <span class="perf-pill perf-unsatisfactory">Unsatisfactory</span>
                                            @endswitch
                                        </td>
                                        <td class="text-center">
                                            <span class="count-pill count-achieved">{{ $data['achievements'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="count-pill count-warning">{{ $data['warnings'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="count-pill count-critical">{{ $data['critical'] }}</span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('kpi.show', $data['employee']->id) }}?period={{ $period }}" 
                                               class="btn btn-sm btn-outline-info me-1 shadow-sm" title="View Details">
                                                <i class="bi bi-eye-fill me-1"></i> Detail
                                            </a>
                                            <a href="{{ route('reports.export-pdf', $data['employee']->id) }}?period={{ $period }}" 
                                               class="btn btn-sm btn-outline-primary shadow-sm" target="_blank" title="Export PDF">
                                                <i class="bi bi-file-earmark-pdf-fill"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            No KPI data available for this period
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Color Legend -->
        <div class="row mt-4">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-3">Performance Level Guide:</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="perf-pill perf-excellent px-3 py-2">Excellent (90-100)</span>
                            <span class="perf-pill perf-good px-3 py-2">Good (75-89)</span>
                            <span class="perf-pill perf-satisfactory px-3 py-2">Satisfactory (60-74)</span>
                            <span class="perf-pill perf-improvement px-3 py-2">Needs Improvement (45-59)</span>
                            <span class="perf-pill perf-unsatisfactory px-3 py-2">Unsatisfactory (<45)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
    $(document).ready(function() {
        if (!$.fn.DataTable.isDataTable('#performanceTable')) {
            $('#performanceTable').DataTable({
                pageLength: 25,
                ordering: true,
                searching: true,
                columnDefs: [
                    { orderable: false, targets: -1 }
                ]
            });
        }
    });
</script>
@endsection

