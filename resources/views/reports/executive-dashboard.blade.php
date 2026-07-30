@extends('layouts.dashboard')

@push('styles')
<style>
    /* High-contrast KPI Score & Performance Styling */
    .kpi-score-badge {
        font-weight: 700 !important;
        font-size: 0.9rem !important;
        padding: 6px 12px !important;
        border-radius: 6px !important;
        display: inline-block !important;
        box-shadow: 0 1px 3px rgba(0,0,0,0.12) !important;
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

    /* Table Contrast Improvements */
    .table td, .table th {
        vertical-align: middle !important;
    }

    /* ══ DARK MODE COMPATIBILITY & ULTRA READABILITY ══ */
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
    body.theme-dark .list-group-item,
    [data-bs-theme="dark"] .list-group-item,
    .theme-dark .list-group-item {
        background-color: #1e293b !important;
        color: #f8fafc !important;
        border-color: #334155 !important;
    }
    body.theme-dark .table,
    [data-bs-theme="dark"] .table {
        color: #f1f5f9 !important;
    }
    body.theme-dark .table th,
    [data-bs-theme="dark"] .table th {
        background-color: #0f172a !important;
        color: #cbd5e1 !important;
        border-color: #334155 !important;
    }
    body.theme-dark .table td,
    [data-bs-theme="dark"] .table td {
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
    body.theme-dark .progress,
    [data-bs-theme="dark"] .progress {
        background-color: #334155 !important;
    }

    /* ══ ENTERPRISE PERFORMER SHOWCASE STYLING ══ */
    .perf-row-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 14px 16px;
        transition: all 0.25s ease;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
    }
    .perf-row-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
    }
    body.theme-dark .perf-row-card, [data-bs-theme="dark"] .perf-row-card {
        background: #0f172a !important;
        border-color: #334155 !important;
    }
    body.theme-dark .perf-row-card:hover, [data-bs-theme="dark"] .perf-row-card:hover {
        background: #1e293b !important;
        border-color: #475569 !important;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3) !important;
    }

    .perf-rank-badge {
        width: 36px;
        height: 36px;
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
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1rem;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25);
    }

    .progress-bar-emerald {
        background: linear-gradient(90deg, #10b981 0%, #059669 100%) !important;
        border-radius: 10px;
    }
    .progress-bar-rose {
        background: linear-gradient(90deg, #f43f5e 0%, #e11d48 100%) !important;
        border-radius: 10px;
    }
</style>
@endpush

@section('content')

<div class="page-heading">
    <div class="row align-items-center mb-3">
        <div class="col-md-6">
            <h3 class="fw-bold text-dark mb-1">Executive KPI Dashboard</h3>
            <p class="text-muted mb-0">Period: {{ \Carbon\Carbon::createFromFormat('Y-m', $period)->format('F Y') }}</p>
        </div>
        <div class="col-md-6 text-right">
            <div class="d-flex justify-content-end align-items-center flex-wrap gap-2">
                <form method="GET" class="form-inline d-flex align-items-center me-2">
                    <label class="me-2 fw-bold text-dark mb-0">Period: </label>
                    <input type="month" name="period" value="{{ $period }}" class="form-control" onchange="this.form.submit()">
                </form>
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
        <!-- Key Metrics -->
        <div class="row mb-4">
            <div class="col-12 col-sm-6 col-md-3 mb-3">
                <div class="card border-0 shadow-sm border-left-primary h-100">
                    <div class="card-body">
                        <h6 class="text-primary font-weight-bold mb-1">Total Employees</h6>
                        <h2 class="mb-0 fw-bold text-dark">{{ $totalEmployees }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3 mb-3">
                <div class="card border-0 shadow-sm border-left-success h-100">
                    <div class="card-body">
                        <h6 class="text-success font-weight-bold mb-1">Excellent</h6>
                        <h2 class="mb-0 fw-bold text-dark">{{ $excellentCount }}<span class="text-sm"> / {{ $totalEmployees }}</span></h2>
                        <small class="text-muted">{{ $totalEmployees > 0 ? round(($excellentCount / $totalEmployees) * 100, 1) : 0 }}%</small>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3 mb-3">
                <div class="card border-0 shadow-sm border-left-info h-100">
                    <div class="card-body">
                        <h6 class="text-info font-weight-bold mb-1">Good Performance</h6>
                        <h2 class="mb-0 fw-bold text-dark">{{ $goodCount }}<span class="text-sm"> / {{ $totalEmployees }}</span></h2>
                        <small class="text-muted">{{ $totalEmployees > 0 ? round(($goodCount / $totalEmployees) * 100, 1) : 0 }}%</small>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3 mb-3">
                <div class="card border-0 shadow-sm border-left-danger h-100">
                    <div class="card-body">
                        <h6 class="text-danger font-weight-bold mb-1">Unresolved Incidents</h6>
                        <h2 class="mb-0 fw-bold text-dark">{{ $recentIncidents->count() }}</h2>
                        <small class="text-muted">Require Attention</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Executive Interactive Charts Row -->
        <div class="row mb-4">
            <!-- Chart 1: Company Performance Tier Breakdown (Donut Chart) -->
            <div class="col-12 col-lg-5 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="bi bi-pie-chart-fill text-primary me-2"></i>Performance Distribution
                        </h5>
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill fw-bold">Executive View</span>
                    </div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <div id="executivePerformanceDonutChart" style="width: 100%; min-height: 280px;"></div>
                    </div>
                </div>
            </div>

            <!-- Chart 2: Department Performance Comparison (Bar Chart) -->
            <div class="col-12 col-lg-7 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="bi bi-bar-chart-line-fill text-success me-2"></i>Department Score Comparison
                        </h5>
                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-1 rounded-pill fw-bold">Avg KPI Score</span>
                    </div>
                    <div class="card-body">
                        <div id="executiveDepartmentBarChart" style="width: 100%; min-height: 280px;"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Top Performers Showcase -->
            <div class="col-12 col-lg-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-trophy-fill text-warning me-2 fs-5"></i>
                            <h5 class="mb-0 fw-bold text-dark">Top 5 Performers</h5>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3 py-1 fw-bold">
                            <i class="bi bi-award-fill me-1"></i> Hall of Fame
                        </span>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-flex flex-column gap-3">
                            @forelse($topPerformers as $index => $record)
                            <div class="perf-row-card d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3" style="min-width: 0;">
                                    <!-- Rank Badge -->
                                    <div class="perf-rank-badge {{ $index === 0 ? 'rank-1' : ($index === 1 ? 'rank-2' : ($index === 2 ? 'rank-3' : 'rank-subtle')) }}">
                                        @if($index === 0)
                                            <i class="bi bi-trophy-fill"></i>
                                        @else
                                            #{{ $index + 1 }}
                                        @endif
                                    </div>

                                    <!-- Employee Avatar -->
                                    @if(!empty($record->employee?->profile_photo))
                                        <img src="{{ asset('storage/' . $record->employee->profile_photo) }}" 
                                             alt="{{ $record->employee?->fullname }}" 
                                             class="rounded-circle border shadow-sm flex-shrink-0" 
                                             style="width: 42px; height: 42px; object-fit: cover;">
                                    @else
                                        <div class="avatar-initials-circle flex-shrink-0">
                                            {{ strtoupper(substr($record->employee?->fullname ?? 'U', 0, 1)) }}
                                        </div>
                                    @endif

                                    <!-- Name & Role -->
                                    <div class="text-truncate">
                                        <h6 class="mb-0 fw-bold text-dark text-truncate">{{ $record->employee?->fullname ?? 'Unknown' }}</h6>
                                        <small class="text-muted text-truncate d-block">
                                            {{ $record->employee?->department?->name ?? 'Unknown' }} • {{ $record->employee?->role?->title ?? 'Unknown' }}
                                        </small>
                                    </div>
                                </div>

                                <!-- Score & Progress Meter -->
                                <div class="d-flex align-items-center gap-3 ms-3 flex-shrink-0">
                                    <div class="d-none d-sm-block" style="width: 90px;">
                                        <div class="progress" style="height: 7px; border-radius: 10px;">
                                            <div class="progress-bar progress-bar-emerald" style="width: {{ min($record->composite_score, 100) }}%;"></div>
                                        </div>
                                    </div>
                                    <span class="badge bg-success text-white px-3 py-2 fs-6 fw-bold shadow-sm" style="min-width: 65px; text-align: center;">
                                        {{ round($record->composite_score, 2) }}
                                    </span>
                                </div>
                            </div>
                            @empty
                            <p class="text-muted text-center py-4">No data available</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Performers Showcase (Development Focus) -->
            <div class="col-12 col-lg-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-graph-down-arrow text-danger me-2 fs-5"></i>
                            <h5 class="mb-0 fw-bold text-dark">Bottom 5 Performers</h5>
                        </div>
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3 py-1 fw-bold">
                            <i class="bi bi-person-exclamation me-1"></i> Development Focus
                        </span>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-flex flex-column gap-3">
                            @forelse($bottomPerformers as $index => $record)
                            <div class="perf-row-card d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-3" style="min-width: 0;">
                                    <!-- Rank Badge -->
                                    <div class="perf-rank-badge rank-subtle">
                                        #{{ $index + 1 }}
                                    </div>

                                    <!-- Employee Avatar -->
                                    @if(!empty($record->employee?->profile_photo))
                                        <img src="{{ asset('storage/' . $record->employee->profile_photo) }}" 
                                             alt="{{ $record->employee?->fullname }}" 
                                             class="rounded-circle border shadow-sm flex-shrink-0" 
                                             style="width: 42px; height: 42px; object-fit: cover;">
                                    @else
                                        <div class="avatar-initials-circle flex-shrink-0" style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);">
                                            {{ strtoupper(substr($record->employee?->fullname ?? 'U', 0, 1)) }}
                                        </div>
                                    @endif

                                    <!-- Name & Role -->
                                    <div class="text-truncate">
                                        <h6 class="mb-0 fw-bold text-dark text-truncate">{{ $record->employee?->fullname ?? 'Unknown' }}</h6>
                                        <small class="text-muted text-truncate d-block">
                                            {{ $record->employee?->department?->name ?? 'Unknown' }} • {{ $record->employee?->role?->title ?? 'Unknown' }}
                                        </small>
                                    </div>
                                </div>

                                <!-- Score & Progress Meter -->
                                <div class="d-flex align-items-center gap-3 ms-3 flex-shrink-0">
                                    <div class="d-none d-sm-block" style="width: 90px;">
                                        <div class="progress" style="height: 7px; border-radius: 10px;">
                                            <div class="progress-bar progress-bar-rose" style="width: {{ min($record->composite_score, 100) }}%;"></div>
                                        </div>
                                    </div>
                                    <span class="badge bg-danger text-white px-3 py-2 fs-6 fw-bold shadow-sm" style="min-width: 65px; text-align: center;">
                                        {{ round($record->composite_score, 2) }}
                                    </span>
                                </div>
                            </div>
                            @empty
                            <p class="text-muted text-center py-4">No data available</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Department Performance -->
        <div class="row mt-4">
            <div class="col-12 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold text-dark"><i class="bi bi-building me-2 text-primary"></i>Department Performance Comparison</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Department</th>
                                        <th>Employees</th>
                                        <th>Avg Score</th>
                                        <th>Score Distribution</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($departments as $dept)
                                    <tr>
                                        <td><strong class="text-dark fs-6">{{ $dept['name'] }}</strong></td>
                                        <td><span class="badge bg-secondary text-white px-2 py-1">{{ $dept['employee_count'] }}</span></td>
                                        <td>
                                            @php
                                                $scoreClass = $dept['avg_score'] >= 90 ? 'score-excellent' : 
                                                            ($dept['avg_score'] >= 75 ? 'score-good' : 
                                                            ($dept['avg_score'] >= 60 ? 'score-satisfactory' : 'score-unsatisfactory'));
                                            @endphp
                                            <span class="kpi-score-badge {{ $scoreClass }}">
                                                {{ round($dept['avg_score'], 2) }}/100
                                            </span>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 22px; border-radius: 6px;">
                                                <div class="progress-bar {{ 
                                                    $dept['avg_score'] >= 90 ? 'bg-success' : 
                                                    ($dept['avg_score'] >= 75 ? 'bg-info' : 
                                                    ($dept['avg_score'] >= 60 ? 'bg-warning text-dark' : 'bg-danger'))
                                                }} font-weight-bold" 
                                                     style="width: {{ min($dept['avg_score'], 100) }}%;">
                                                    {{ round($dept['avg_score'], 1) }}%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted py-3">No data available</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Incidents -->
        @if($recentIncidents->count() > 0)
        <div class="row mt-4">
            <div class="col-12 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-danger text-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-shield-exclamation me-2"></i>Unresolved Incidents</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Employee</th>
                                        <th>Type</th>
                                        <th>Date</th>
                                        <th>Severity</th>
                                        <th>Status</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentIncidents as $incident)
                                    <tr>
                                        <td><strong class="text-dark">{{ $incident->employee?->fullname ?? 'Unknown' }}</strong></td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $incident->type)) }}</td>
                                        <td>{{ $incident->incident_date->format('d M Y') }}</td>
                                        <td>
                                            @switch($incident->severity)
                                                @case('low')
                                                    <span class="badge bg-info text-white px-2 py-1">Low</span>
                                                    @break
                                                @case('medium')
                                                    <span class="badge bg-warning text-dark px-2 py-1">Medium</span>
                                                    @break
                                                @case('high')
                                                    <span class="badge bg-danger text-white px-2 py-1">High</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-dark text-white px-2 py-1">Critical</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $incident->status === 'resolved' ? 'success' : 'warning' }} {{ $incident->status === 'resolved' ? 'text-white' : 'text-dark' }} px-2 py-1">
                                                {{ ucfirst($incident->status) }}
                                            </span>
                                        </td>
                                        <td><small class="text-muted">{{ Str::limit($incident->description, 50) }}</small></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Export Actions -->
        <div class="row mt-4">
            <div class="col-12 mb-3">
                <a href="{{ route('reports.monthly-recap') }}?period={{ $period }}" class="btn btn-info text-white me-2 shadow-sm">
                    <i class="bi bi-list-ul me-1"></i> View Monthly Recap
                </a>
                <a href="{{ route('reports.export-csv') }}?period={{ $period }}" class="btn btn-success shadow-sm">
                    <i class="bi bi-download me-1"></i> Export All Data to CSV
                </a>
            </div>
        </div>
        @endif
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

        // 1. PERFORMANCE DISTRIBUTION DONUT CHART
        @php
            $satisfactoryCount = $topPerformers->merge($bottomPerformers)->unique('employee.id')->where('performance_level', 'satisfactory')->count();
            $needsImprovementCount = $topPerformers->merge($bottomPerformers)->unique('employee.id')->where('performance_level', 'needs_improvement')->count();
            $unsatisfactoryCount = $topPerformers->merge($bottomPerformers)->unique('employee.id')->where('performance_level', 'unsatisfactory')->count();
        @endphp

        const donutOptions = {
            series: [
                {{ $excellentCount }}, 
                {{ $goodCount }}, 
                {{ $satisfactoryCount }}, 
                {{ $needsImprovementCount }}, 
                {{ $unsatisfactoryCount }}
            ],
            labels: ['Excellent (90-100)', 'Good (75-89)', 'Satisfactory (60-74)', 'Needs Improvement (45-59)', 'Unsatisfactory (<45)'],
            chart: {
                type: 'donut',
                height: 290,
                background: 'transparent',
            },
            colors: ['#10b981', '#0284c7', '#f59e0b', '#f97316', '#ef4444'],
            stroke: {
                show: true,
                colors: [isDarkMode ? '#1e293b' : '#ffffff'],
                width: 2
            },
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return Math.round(val) + "%";
                }
            },
            legend: {
                position: 'bottom',
                labels: {
                    colors: labelColor
                }
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '68%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Total Evaluated',
                                color: labelColor,
                                formatter: function (w) {
                                    return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                }
                            }
                        }
                    }
                }
            },
            tooltip: {
                theme: isDarkMode ? 'dark' : 'light'
            }
        };

        const donutChart = new ApexCharts(document.querySelector("#executivePerformanceDonutChart"), donutOptions);
        donutChart.render();

        // 2. DEPARTMENT SCORE COMPARISON BAR CHART
        const deptNames = {!! json_encode(array_column($departments, 'name')) !!};
        const deptScores = {!! json_encode(array_column($departments, 'avg_score')) !!};

        const barOptions = {
            series: [{
                name: 'Average KPI Score',
                data: deptScores
            }],
            chart: {
                type: 'bar',
                height: 290,
                background: 'transparent',
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    horizontal: true,
                    distributed: true,
                    barHeight: '55%',
                    dataLabels: { position: 'top' }
                }
            },
            colors: deptScores.map(score => {
                if (score >= 90) return '#10b981';
                if (score >= 75) return '#0284c7';
                if (score >= 60) return '#f59e0b';
                return '#ef4444';
            }),
            dataLabels: {
                enabled: true,
                textAnchor: 'start',
                style: {
                    colors: ['#ffffff'],
                    fontWeight: 'bold'
                },
                formatter: function (val) {
                    return val + "/100";
                },
                offsetX: 10
            },
            xaxis: {
                categories: deptNames,
                max: 100,
                labels: {
                    style: { colors: labelColor }
                }
            },
            yaxis: {
                labels: {
                    style: { colors: labelColor, fontWeight: 600 }
                }
            },
            grid: {
                borderColor: isDarkMode ? '#334155' : '#e2e8f0',
                strokeDashArray: 4
            },
            tooltip: {
                theme: isDarkMode ? 'dark' : 'light',
                y: {
                    formatter: function (val) {
                        return val + " / 100 Points";
                    }
                }
            },
            legend: { show: false }
        };

        const barChart = new ApexCharts(document.querySelector("#executiveDepartmentBarChart"), barOptions);
        barChart.render();
    });
</script>
@endpush

