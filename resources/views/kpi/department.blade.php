@extends('layouts.dashboard')

@push('styles')
<style>
    /* ══ DARK MODE COMPATIBILITY & ULTRA READABILITY FOR DEPARTMENT KPI ══ */
    body.theme-dark, [data-bs-theme="dark"], .theme-dark {
        color: #f1f5f9 !important;
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
    body.theme-dark h6, [data-bs-theme="dark"] h6,
    body.theme-dark .page-heading h3,
    [data-bs-theme="dark"] .page-heading h3 {
        color: #f8fafc !important;
    }

    body.theme-dark .card,
    [data-bs-theme="dark"] .card,
    .theme-dark .card {
        background-color: #1e293b !important;
        border-color: #334155 !important;
        color: #f8fafc !important;
    }

    body.theme-dark .card-header,
    [data-bs-theme="dark"] .card-header,
    .theme-dark .card-header,
    body.theme-dark .card-header.bg-white,
    [data-bs-theme="dark"] .card-header.bg-white {
        background-color: #0f172a !important;
        color: #f8fafc !important;
        border-bottom: 1px solid #334155 !important;
    }

    body.theme-dark .card-title,
    [data-bs-theme="dark"] .card-title {
        color: #f8fafc !important;
    }

    body.theme-dark .table,
    [data-bs-theme="dark"] .table {
        color: #f1f5f9 !important;
    }

    body.theme-dark .table thead,
    [data-bs-theme="dark"] .table thead,
    body.theme-dark .table-light,
    [data-bs-theme="dark"] .table-light,
    body.theme-dark .table-light th,
    [data-bs-theme="dark"] .table-light th,
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
        color: #ffffff !important;
    }

    body.theme-dark .form-control,
    [data-bs-theme="dark"] .form-control,
    body.theme-dark .form-select,
    [data-bs-theme="dark"] .form-select,
    body.theme-dark input[type="month"],
    [data-bs-theme="dark"] input[type="month"] {
        background-color: #0f172a !important;
        border-color: #334155 !important;
        color: #f8fafc !important;
    }

    body.theme-dark label,
    [data-bs-theme="dark"] label {
        color: #f1f5f9 !important;
    }

    body.theme-dark .modal-content,
    [data-bs-theme="dark"] .modal-content {
        background-color: #1e293b !important;
        color: #f8fafc !important;
        border-color: #334155 !important;
    }

    body.theme-dark .modal-header,
    [data-bs-theme="dark"] .modal-header,
    body.theme-dark .modal-footer,
    [data-bs-theme="dark"] .modal-footer {
        background-color: #0f172a !important;
        border-color: #334155 !important;
    }

    body.theme-dark .modal-body label,
    [data-bs-theme="dark"] .modal-body label,
    body.theme-dark .modal-body small,
    [data-bs-theme="dark"] .modal-body small {
        color: #e2e8f0 !important;
    }

    body.theme-dark .progress,
    [data-bs-theme="dark"] .progress {
        background-color: #334155 !important;
    }

    body.theme-dark .list-group-item,
    [data-bs-theme="dark"] .list-group-item {
        background-color: #1e293b !important;
        color: #f8fafc !important;
        border-color: #334155 !important;
    }

    /* ══ ENTERPRISE SOFT PASTEL DEPARTMENT KPI STYLING ══ */
    .stat-tile-soft {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 14px 16px;
        transition: all 0.2s ease;
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
</style>
@endpush

@section('content')
<div class="page-heading">
    <div class="row align-items-center mb-3">
        <div class="col-md-4">
            <h3 class="fw-bold text-dark mb-1">Department Performance Analysis</h3>
            <p class="text-muted mb-0">
                <strong>{{ $selectedDepartment ? $selectedDepartment->name : 'Semua Departemen' }}</strong> • {{ \Carbon\Carbon::createFromFormat('Y-m', $period)->format('F Y') }}
            </p>
        </div>
        <div class="col-md-8 text-right">
            <div class="d-flex justify-content-end align-items-center flex-wrap gap-2">
                <form method="GET" class="d-flex align-items-center gap-1 me-1" id="deptFilterForm">
                    <input type="hidden" name="period" value="{{ $period }}">
                    <label class="fw-bold text-dark mb-0 me-1">Dept: </label>
                    <select name="department_id" class="form-select form-control me-2" style="width: 170px;" onchange="this.form.submit()">
                        <option value="all" {{ ($selectedDeptId ?? 'all') == 'all' ? 'selected' : '' }}>Semua Departemen</option>
                        @foreach($departments ?? [] as $dept)
                            <option value="{{ $dept->id }}" {{ ($selectedDeptId ?? '') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </form>

                @if(($canGenerate ?? false) && ($selectedDeptId ?? 'all') !== 'all')
                <form action="{{ route('kpi.department.delete', $selectedDeptId) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus {{ $selectedDepartment ? "Departemen {$selectedDepartment->name}" : "Departemen ini" }}? Seluruh anggota departemen akan dilepas.')">
                    @csrf
                    <input type="hidden" name="period" value="{{ $period }}">
                    <button type="submit" class="btn btn-outline-danger btn-md shadow-sm me-1" title="Hapus Departemen Ini">
                        <i class="bi bi-trash-fill me-1"></i> Hapus Dept
                    </button>
                </form>
                @endif

                <div class="d-flex align-items-center me-2">
                    <label class="me-2 fw-bold text-dark mb-0">Periode: </label>
                    <input type="month" id="periodSelect" class="form-control" value="{{ $period }}" onchange="changePeriod()">
                </div>

                @if($canGenerate ?? false)
                <button type="button" class="btn btn-success btn-md shadow-sm me-1" data-bs-toggle="modal" data-bs-target="#manageDepartmentModal">
                    <i class="bi bi-building-add me-1"></i> + Buat / Kelola Dept
                </button>
                @endif
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
                    <h4 class="fw-bold text-dark">KPI Departemen Belum Digenerate untuk Periode {{ \Carbon\Carbon::createFromFormat('Y-m', $period)->format('F Y') }}</h4>
                    <p class="text-muted max-w-lg mx-auto mb-4">
                        Laporan KPI departemen pada periode ini belum diterbitkan oleh HR / Master Admin. <br>
                        Penilaian dihitung transparan dari <strong>50% Kepatuhan Checkout Presensi</strong> dan <strong>50% Pengisian Log Kerja Harian</strong>.
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

        <!-- Objective Scoring Guidance Banner Card (Dark Mode Compatible) -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm border-start border-info border-4 bg-info bg-opacity-10">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-start gap-3">
                            <div class="rounded-circle bg-info bg-opacity-25 text-info p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
                                <i class="bi bi-info-circle-fill fs-5"></i>
                            </div>
                            <div class="w-100">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <h6 class="fw-bold mb-0">Panduan Transparansi & Perhitungan Objektif KPI Divisi</h6>
                                    <span class="badge bg-info text-white fw-bold">100% Objektif & Otomatis</span>
                                </div>
                                <p class="text-muted fs-7 mb-2">
                                    Penilaian kinerja divisi dihitung secara objektif berdasarkan gabungan <strong>2 indikator riil (bobot 50:50)</strong> dari seluruh karyawan dalam divisi terkait:
                                </p>
                                <div class="row g-2 fs-7">
                                    <div class="col-12 col-md-4">
                                        <div class="p-2 rounded border bg-body-tertiary bg-opacity-75 d-flex align-items-center gap-2">
                                            <span class="badge bg-success">50%</span>
                                            <span><strong>Presensi (Checkout):</strong> Kehadiran fisik & kepatuhan jam kerja.</span>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="p-2 rounded border bg-body-tertiary bg-opacity-75 d-flex align-items-center gap-2">
                                            <span class="badge bg-primary">50%</span>
                                            <span><strong>Log Kerja Harian:</strong> Pengisian & verifikasi laporan aktivitas kerja.</span>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="p-2 rounded border bg-body-tertiary bg-opacity-75 d-flex align-items-center gap-2">
                                            <span class="badge bg-warning text-dark">% Persentase</span>
                                            <span><strong>Sebaran Staf:</strong> Proporsi staf pada kategori (contoh: <code>1/11 (9.1%)</code> = 1 staf dari total 11 staf).</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($selectedDepartment)
        <!-- Department Banner Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm bg-info bg-opacity-10 border-start border-info border-4">
                    <div class="card-body py-3 d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-info text-white fw-bold d-flex align-items-center justify-content-center shadow-sm" style="width: 48px; height: 48px; font-size: 1.2rem;">
                                <i class="bi bi-building"></i>
                            </div>
                            <div>
                                <h5 class="fw-extrabold text-dark mb-0"><i class="bi bi-diagram-3-fill text-info me-2"></i>Divisi / Departemen: {{ $selectedDepartment->name }}</h5>
                                <small class="text-muted fw-bold">Penilaian Performa Seluruh Karyawan Divisi {{ $selectedDepartment->name }}</small>
                            </div>
                        </div>
                        <div>
                            <span class="badge badge-soft-sky px-3 py-2 fw-bold fs-6">
                                <i class="bi bi-people-fill me-1"></i> {{ count($deptEmployees) }} Karyawan Divisi
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <!-- Department Comparative Overview Cards Grid (Ranked Sequentially) -->
        <div class="row mb-4">
            <div class="col-12 mb-3">
                <div class="d-flex align-items-center justify-content-between">
                    <h6 class="fw-extrabold text-uppercase mb-0" style="font-size: 0.85rem; letter-spacing: 0.5px;">
                        <i class="bi bi-trophy-fill text-warning me-2"></i>Peringkat Performa Divisi (Berdasarkan Departemen)
                    </h6>
                    <span class="badge bg-secondary bg-opacity-10 text-body fw-bold border px-3 py-1">Diurutkan dari Rata-Rata Skor Divisi Tertinggi</span>
                </div>
            </div>
            @forelse($departmentSummaries ?? [] as $index => $dSummary)
            <div class="col-12 col-md-6 col-lg-3 mb-3">
                <div class="stat-tile-soft h-100 border-start border-3 {{ $dSummary['is_stable'] ? 'border-info' : 'border-warning' }} shadow-sm">
                    <!-- Top Bar: Rank Badge + Sample Pill -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            @if($index === 0)
                                <span class="badge badge-soft-amber border border-warning px-2 py-1 fw-extrabold" style="font-size: 0.85rem;">
                                    🥇 Juara #1
                                </span>
                            @elseif($index === 1)
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1 fw-extrabold" style="font-size: 0.85rem;">
                                    🥈 Peringkat #2
                                </span>
                            @elseif($index === 2)
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1 fw-extrabold" style="font-size: 0.85rem;">
                                    🥉 Peringkat #3
                                </span>
                            @else
                                <span class="badge bg-light text-secondary border px-2 py-1 fw-bold" style="font-size: 0.85rem;">
                                    Peringkat #{{ $index + 1 }}
                                </span>
                            @endif
                        </div>
                        <span class="badge {{ $dSummary['is_stable'] ? 'badge-soft-emerald' : 'badge-soft-amber' }} fw-bold" style="font-size: 0.75rem;">
                            {{ $dSummary['badge_label'] }}
                        </span>
                    </div>

                    <!-- Department Name & Staff Link -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-bold text-dark mb-0 text-truncate" style="max-width: 140px;">{{ $dSummary['department']->name }}</h6>
                        <a href="{{ route('kpi.department') }}?period={{ $period }}&department_id={{ $dSummary['department']->id }}" class="badge bg-info bg-opacity-10 text-info fw-bold text-decoration-none">
                            {{ $dSummary['member_count'] }} Staff <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>

                    <!-- Score Metrics -->
                    <div class="d-flex align-items-baseline gap-2 mb-2">
                        <h3 class="fw-extrabold mb-0 {{ $dSummary['avg_score'] >= 75 ? 'text-success' : ($dSummary['avg_score'] >= 60 ? 'text-warning' : 'text-danger') }}">{{ $dSummary['avg_score'] }}</h3>
                        <small class="text-muted fs-7">Rata-Rata Divisi</small>
                    </div>
                    <div class="progress" style="height: 6px; border-radius: 6px;">
                        <div class="progress-bar {{ $dSummary['avg_score'] >= 75 ? 'bg-success' : ($dSummary['avg_score'] >= 60 ? 'bg-warning' : 'bg-danger') }}" style="width: {{ min($dSummary['avg_score'], 100) }}%;"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                        <small class="text-muted">Skor Tertinggi:</small>
                        <small class="fw-bold text-dark">{{ $dSummary['max_score'] }}/100</small>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 mb-3">
                <div class="p-4 text-muted text-center border rounded bg-light">Belum ada divisi yang terdaftar.</div>
            </div>
            @endforelse
        </div>
        @endif

        <!-- Summary Stats Tiles -->
        <div class="row mb-4">
            <div class="col-6 col-md-3 mb-3 mb-md-0">
                <div class="stat-tile-soft h-100 d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.75rem;">Total Staf Divisi</small>
                        <h2 class="mb-0 fw-extrabold text-dark mt-1">{{ count($deptEmployees) }}</h2>
                        <small class="text-muted">Karyawan Aktif Divisi</small>
                    </div>
                    <div class="badge-soft-sky rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-people-fill fs-5"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3 mb-md-0">
                <div class="stat-tile-soft h-100 d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.75rem;">Performa Sangat Baik</small>
                        <h2 class="mb-0 fw-extrabold text-success mt-1">{{ collect($deptKPIs)->where('performance_level', 'excellent')->count() }}</h2>
                        <small class="text-muted">Skor ≥ 90.0</small>
                    </div>
                    <div class="badge-soft-emerald rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-star-fill fs-5"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3 mb-md-0">
                <div class="stat-tile-soft h-100 d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.75rem;">Performa Baik</small>
                        <h2 class="mb-0 fw-extrabold text-info mt-1">{{ collect($deptKPIs)->where('performance_level', 'good')->count() }}</h2>
                        <small class="text-muted">Skor 75.0 - 89.9</small>
                    </div>
                    <div class="badge-soft-sky rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-hand-thumbs-up-fill fs-5"></i>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3 mb-md-0">
                <div class="stat-tile-soft h-100 d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.75rem;">Rata-Rata Skor Divisi</small>
                        <h2 class="mb-0 fw-extrabold text-warning mt-1">{{ round($avgScore, 2) }}<span class="fs-6 text-muted">/100</span></h2>
                        <small class="text-muted">Nilai Rata-Rata Divisi</small>
                    </div>
                    <div class="badge-soft-amber rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <i class="bi bi-speedometer2 fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Performance Distribution & Department Statistics (MOVED TO TOP) -->
        <div class="row mb-4">
            <!-- Card 1: Performance Distribution -->
            <div class="col-12 col-lg-6 mb-4 mb-lg-0">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="bi bi-pie-chart-fill text-primary me-2"></i>Distribusi Performa Divisi
                        </h5>
                        <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill fw-bold">Sebaran Kategori Staf</span>
                    </div>
                    <div class="card-body p-3">
                        @php
                            $excellent = collect($deptKPIs)->where('performance_level', 'excellent')->count();
                            $good = collect($deptKPIs)->where('performance_level', 'good')->count();
                            $satisfactory = collect($deptKPIs)->where('performance_level', 'satisfactory')->count();
                            $needsImprovement = collect($deptKPIs)->where('performance_level', 'needs_improvement')->count();
                            $unsatisfactory = collect($deptKPIs)->where('performance_level', 'unsatisfactory')->count();
                            $total = count($deptKPIs);
                        @endphp

                        <div class="d-flex flex-column gap-2">
                            <!-- Excellent -->
                            <div class="dist-row-item">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-dark fs-6"><i class="bi bi-star-fill text-success me-2"></i> Sangat Baik (90-100)</span>
                                    <span class="badge badge-soft-emerald fw-bold px-2 py-1">{{ $excellent }}/{{ $total }} ({{ $total > 0 ? round(($excellent/$total)*100, 1) : 0 }}%)</span>
                                </div>
                                <div class="progress" style="height: 6px; border-radius: 10px;">
                                    <div class="progress-bar" style="width: {{ $total > 0 ? ($excellent/$total)*100 : 0 }}%; background: linear-gradient(90deg, #34d399, #059669);"></div>
                                </div>
                            </div>

                            <!-- Good -->
                            <div class="dist-row-item">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-dark fs-6"><i class="bi bi-hand-thumbs-up-fill text-info me-2"></i> Baik (75-89)</span>
                                    <span class="badge badge-soft-sky fw-bold px-2 py-1">{{ $good }}/{{ $total }} ({{ $total > 0 ? round(($good/$total)*100, 1) : 0 }}%)</span>
                                </div>
                                <div class="progress" style="height: 6px; border-radius: 10px;">
                                    <div class="progress-bar" style="width: {{ $total > 0 ? ($good/$total)*100 : 0 }}%; background: linear-gradient(90deg, #38bdf8, #0284c7);"></div>
                                </div>
                            </div>

                            <!-- Satisfactory -->
                            <div class="dist-row-item">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-dark fs-6"><i class="bi bi-check-circle-fill text-warning me-2"></i> Cukup (60-74)</span>
                                    <span class="badge badge-soft-amber fw-bold px-2 py-1">{{ $satisfactory }}/{{ $total }} ({{ $total > 0 ? round(($satisfactory/$total)*100, 1) : 0 }}%)</span>
                                </div>
                                <div class="progress" style="height: 6px; border-radius: 10px;">
                                    <div class="progress-bar" style="width: {{ $total > 0 ? ($satisfactory/$total)*100 : 0 }}%; background: linear-gradient(90deg, #fbbf24, #d97706);"></div>
                                </div>
                            </div>

                            <!-- Needs Improvement -->
                            <div class="dist-row-item">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-dark fs-6"><i class="bi bi-arrow-up-circle-fill text-warning me-2"></i> Perlu Pembinaan (50-59)</span>
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 fw-bold px-2 py-1">{{ $needsImprovement }}/{{ $total }} ({{ $total > 0 ? round(($needsImprovement/$total)*100, 1) : 0 }}%)</span>
                                </div>
                                <div class="progress" style="height: 6px; border-radius: 10px;">
                                    <div class="progress-bar" style="width: {{ $total > 0 ? ($needsImprovement/$total)*100 : 0 }}%; background: linear-gradient(90deg, #fb923c, #ea580c);"></div>
                                </div>
                            </div>

                            <!-- Unsatisfactory -->
                            <div class="dist-row-item">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-dark fs-6"><i class="bi bi-x-circle-fill text-danger me-2"></i> Kurang Baik (<50)</span>
                                    <span class="badge badge-soft-rose fw-bold px-2 py-1">{{ $unsatisfactory }}/{{ $total }} ({{ $total > 0 ? round(($unsatisfactory/$total)*100, 1) : 0 }}%)</span>
                                </div>
                                <div class="progress" style="height: 6px; border-radius: 10px;">
                                    <div class="progress-bar" style="width: {{ $total > 0 ? ($unsatisfactory/$total)*100 : 0 }}%; background: linear-gradient(90deg, #f87171, #dc2626);"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Department Statistics -->
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="bi bi-bar-chart-line-fill text-info me-2"></i>Statistik Skor Divisi
                        </h5>
                        <span class="badge bg-info bg-opacity-10 text-info px-3 py-1 rounded-pill fw-bold">Metrik Statistik</span>
                    </div>
                    <div class="card-body p-3">
                        @php
                            $scores = collect($deptKPIs)->pluck('composite_score');
                            $mean = $scores->avg() ?? 0;
                            $variance = $scores->map(fn($x) => pow($x - $mean, 2))->avg() ?? 0;
                            $stdDev = sqrt($variance);
                        @endphp

                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <div class="stat-tile-soft h-100 d-flex flex-column justify-content-between">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.75rem;">Skor Staf Tertinggi</small>
                                        <div class="badge-soft-emerald rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <i class="bi bi-arrow-up-right-circle-fill"></i>
                                        </div>
                                    </div>
                                    <h3 class="mb-0 fw-extrabold text-dark">{{ round(collect($deptKPIs)->max('composite_score'), 2) }}<span class="fs-7 text-muted"> /100</span></h3>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-tile-soft h-100 d-flex flex-column justify-content-between">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.75rem;">Skor Staf Terendah</small>
                                        <div class="badge-soft-rose rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <i class="bi bi-arrow-down-right-circle-fill"></i>
                                        </div>
                                    </div>
                                    <h3 class="mb-0 fw-extrabold text-dark">{{ round(collect($deptKPIs)->min('composite_score'), 2) }}<span class="fs-7 text-muted"> /100</span></h3>
                                </div>
                            </div>
                        </div>

                        <div class="row g-2 text-sm">
                            <div class="col-6 d-flex justify-content-between p-2 border-bottom">
                                <span class="text-muted fw-bold">Rata-Rata Divisi:</span>
                                <span class="fw-bold text-dark">{{ round($avgScore, 2) }}/100</span>
                            </div>
                            <div class="col-6 d-flex justify-content-between p-2 border-bottom">
                                <span class="text-muted fw-bold">Variasi Skor (StdDev):</span>
                                <span class="fw-bold text-dark">{{ round($stdDev, 2) }}</span>
                            </div>
                            <div class="col-6 d-flex justify-content-between p-2 border-bottom">
                                <span class="text-muted fw-bold">Total Staf Divisi:</span>
                                <span class="fw-bold text-dark">{{ count($deptEmployees) }} Karyawan</span>
                            </div>
                            <div class="col-6 d-flex justify-content-between p-2 border-bottom">
                                <span class="text-muted fw-bold">Di Atas Rata-Rata:</span>
                                <span class="fw-bold text-success">{{ collect($deptKPIs)->where('composite_score', '>', $avgScore)->count() }} Staf</span>
                            </div>
                            <div class="col-6 d-flex justify-content-between p-2">
                                <span class="text-muted fw-bold">Di Bawah Rata-Rata:</span>
                                <span class="fw-bold text-danger">{{ collect($deptKPIs)->where('composite_score', '<', $avgScore)->count() }} Staf</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Employee Performance Rankings Table (RE-DESIGNED FOR MAX UX & READABILITY) -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center border-bottom">
                        <h5 class="card-title mb-0 fw-bold">
                            <i class="bi bi-trophy-fill text-warning me-2"></i>Peringkat Performa Karyawan Divisi
                        </h5>
                        <span class="badge bg-secondary bg-opacity-10 text-body fw-bold border px-3 py-2">
                            Diurutkan dari Skor Komposisi Tertinggi
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-center py-3" style="width: 90px;">Peringkat</th>
                                        <th class="px-4">Nama Karyawan</th>
                                        <th>Jabatan / Posisi</th>
                                        <th class="text-center" style="width: 180px;">Skor Komposisi</th>
                                        <th class="text-center" style="width: 170px;">Tingkat Performa</th>
                                        <th class="text-center" style="width: 160px;">Aksi Detail</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($deptKPIs as $index => $kpi)
                                    <tr>
                                        <td class="text-center">
                                            @if($index === 0)
                                                <span class="badge badge-soft-amber px-3 py-2 fw-bold" style="font-size: 0.85rem;">
                                                    🥇 #1 Top
                                                </span>
                                            @elseif($index === 1)
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-3 py-2 fw-bold" style="font-size: 0.85rem;">
                                                    🥈 #2
                                                </span>
                                            @elseif($index === 2)
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 fw-bold" style="font-size: 0.85rem;">
                                                    🥉 #3
                                                </span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border px-3 py-2 fw-bold" style="font-size: 0.85rem;">
                                                    #{{ $index + 1 }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4">
                                            <div class="d-flex align-items-center gap-3">
                                                @if(!empty($kpi['employee']->profile_photo))
                                                    <img src="{{ asset('storage/' . $kpi['employee']->profile_photo) }}" 
                                                         alt="{{ $kpi['employee']->fullname }}" 
                                                         class="rounded-circle border shadow-sm" 
                                                         style="width: 40px; height: 40px; object-fit: cover;">
                                                @else
                                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center border" style="width: 40px; height: 40px; font-size: 0.95rem;">
                                                        {{ strtoupper(substr($kpi['employee']->fullname, 0, 2)) }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <h6 class="mb-0 fw-bold">{{ $kpi['employee']->fullname }}</h6>
                                                    <small class="text-muted">{{ $kpi['employee']->department->name ?? 'No Dept' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-10 text-body border fw-semibold px-2 py-1">
                                                {{ $kpi['employee']->role?->title ?? 'Staff' }}
                                            </span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <div class="d-flex align-items-center justify-content-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 8px; border-radius: 4px; max-width: 90px; background-color: #e2e8f0;">
                                                    <div class="progress-bar {{ 
                                                        $kpi['composite_score'] >= 90 ? 'bg-success' : 
                                                        ($kpi['composite_score'] >= 75 ? 'bg-info' : 
                                                        ($kpi['composite_score'] >= 60 ? 'bg-warning' : 'bg-danger'))
                                                    }}" style="width: {{ min($kpi['composite_score'], 100) }}%; border-radius: 4px;"></div>
                                                </div>
                                                <span class="fw-bold {{ 
                                                    $kpi['composite_score'] >= 90 ? 'text-success' : 
                                                    ($kpi['composite_score'] >= 75 ? 'text-info' : 
                                                    ($kpi['composite_score'] >= 60 ? 'text-warning' : 'text-danger'))
                                                }}">
                                                    {{ round($kpi['composite_score'], 2) }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @switch($kpi['performance_level'])
                                                @case('excellent')
                                                    <span class="badge badge-soft-emerald px-3 py-2 fw-bold">Excellent</span>
                                                    @break
                                                @case('good')
                                                    <span class="badge badge-soft-sky px-3 py-2 fw-bold">Good</span>
                                                    @break
                                                @case('satisfactory')
                                                    <span class="badge badge-soft-amber px-3 py-2 fw-bold">Satisfactory</span>
                                                    @break
                                                @case('needs_improvement')
                                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2 fw-bold">Needs Improvement</span>
                                                    @break
                                                @default
                                                    <span class="badge badge-soft-rose px-3 py-2 fw-bold">Unsatisfactory</span>
                                            @endswitch
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="{{ route('kpi.show', $kpi['employee']->id) }}?period={{ $period }}" 
                                                   class="btn btn-xs btn-outline-primary shadow-sm"
                                                   title="View Employee Detail">
                                                    <i class="bi bi-eye-fill me-1"></i> Details
                                                </a>
                                                <a href="{{ route('kpi.trend', $kpi['employee']->id) }}" 
                                                   class="btn btn-xs btn-outline-success shadow-sm"
                                                   title="View Employee Trend">
                                                    <i class="bi bi-graph-up me-1"></i> Trend
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">
                                            <i class="bi bi-inbox fs-2 text-muted"></i>
                                            <p class="mb-0 mt-2 fw-bold">No employees found in this department for this period.</p>
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

        <div class="row">
            <div class="col-md-12">
                <a href="{{ route('reports.export-csv') }}?period={{ $period }}" class="btn btn-outline-success">
                    <i class="bi bi-download"></i> Export Department Data
                </a>
                <a href="{{ route('kpi.dashboard') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
    function changePeriod() {
        const period = document.getElementById('periodSelect').value;
        const deptId = "{{ $selectedDeptId ?? 'all' }}";
        window.location.href = `{{ route('kpi.department') }}?period=${period}&department_id=${deptId}`;
    }
</script>

@if($canGenerate ?? false)
<!-- Modal Manage Department -->
<div class="modal fade" id="manageDepartmentModal" tabindex="-1" aria-labelledby="manageDepartmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('kpi.department.assign') }}" method="POST">
                @csrf
                <input type="hidden" name="period" value="{{ $period }}">
                
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title fw-bold text-white mb-0" id="manageDepartmentModalLabel">
                        <i class="bi bi-building-gear me-2"></i> Formasi Structure Departemen
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 mb-4" style="background-color: #e2e8f0; color: #0f172a !important;">
                        <i class="bi bi-info-circle-fill text-primary me-2"></i>
                        <span style="color: #0f172a !important;">Pilih <strong style="color: #0f172a !important;">Departemen Eksisting</strong> atau ketik <strong style="color: #0f172a !important;">Nama Departemen Baru</strong>, lalu centang karyawan untuk dimasukkan ke departemen tersebut.</span>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <label class="form-label fw-bold text-dark" style="color: #0f172a !important;">Pilih Departemen Eksisting</label>
                            <select name="department_id" id="modalDeptSelect" class="form-select form-control text-dark" style="color: #0f172a !important; font-weight: 600;" onchange="onModalDeptChange()">
                                <option value="" selected>-- Pilih Departemen Eksisting --</option>
                                @foreach($departments ?? [] as $dept)
                                    <option value="{{ $dept->id }}" {{ ($selectedDeptId != 'all' && $selectedDeptId == $dept->id) ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark" style="color: #0f172a !important;">Atau Ketik Nama Departemen Baru</label>
                            <input type="text" name="new_department_name" id="modalNewDeptInput" class="form-control text-dark" style="color: #0f172a !important; font-weight: 600;" placeholder="Contoh: Research & Development" oninput="onNewDeptInput()">
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label fw-bold mb-0">Pilih Anggota Departemen</label>
                            <span class="text-muted small">Centang karyawan yang merupakan bagian dari departemen ini</span>
                        </div>
                        
                        <div class="border rounded p-3" style="max-height: 250px; overflow-y: auto;">
                            @foreach($allEmployees ?? [] as $member)
                                <div class="form-check py-1 border-bottom dept-member-checkbox-item" data-dept-id="{{ $member->department_id }}">
                                    <input class="form-check-input dept-member-checkbox" type="checkbox" name="member_ids[]" value="{{ $member->id }}" id="dept_member_{{ $member->id }}">
                                    <label class="form-check-label fw-bold ms-2" for="dept_member_{{ $member->id }}">
                                        {{ $member->fullname }}
                                        <small class="font-weight-normal text-muted ms-2">({{ $member->department->name ?? 'Belum Ada Dept' }} • {{ $member->position->name ?? ($member->role->title ?? '-') }})</small>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer bg-light px-4 py-3 justify-content-between">
                    <div>
                        <button type="button" class="btn btn-outline-danger rounded-pill px-3" id="btnDissolveDept" onclick="dissolveSelectedDept()" style="display: none;">
                            <i class="bi bi-trash-fill me-1"></i> Hapus / Bubarkan Departemen Ini
                        </button>
                    </div>
                    <div>
                        <button type="button" class="btn btn-secondary rounded-pill px-4 me-2" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm">
                            <i class="bi bi-check-circle-fill me-1"></i> Simpan Formasi Departemen
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function onNewDeptInput() {
        const input = document.getElementById('modalNewDeptInput');
        const select = document.getElementById('modalDeptSelect');
        const dissolveBtn = document.getElementById('btnDissolveDept');
        if (input.value.trim() !== '') {
            select.value = '';
            if (dissolveBtn) dissolveBtn.style.display = 'none';
        }
    }

    function dissolveSelectedDept() {
        const select = document.getElementById('modalDeptSelect');
        const selectedDeptId = select.value;
        if (!selectedDeptId) return;
        if (confirm('Apakah Anda yakin ingin menghapus departemen ini? Seluruh anggota departemen akan dilepas.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `{{ url('/kpi/department/delete') }}/${selectedDeptId}`;
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);

            document.body.appendChild(form);
            form.submit();
        }
    }

    function onModalDeptChange() {
        const select = document.getElementById('modalDeptSelect');
        const selectedDeptId = select.value;
        const input = document.getElementById('modalNewDeptInput');
        const dissolveBtn = document.getElementById('btnDissolveDept');

        if (selectedDeptId) {
            input.value = '';
            if (dissolveBtn) dissolveBtn.style.display = 'inline-block';
        } else {
            if (dissolveBtn) dissolveBtn.style.display = 'none';
        }

        const items = document.querySelectorAll('.dept-member-checkbox-item');
        items.forEach(item => {
            const currentDeptId = item.getAttribute('data-dept-id');
            const checkbox = item.querySelector('.dept-member-checkbox');
            
            if (selectedDeptId && currentDeptId == selectedDeptId) {
                checkbox.checked = true;
            } else if (selectedDeptId) {
                checkbox.checked = false;
            }
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
        if (document.getElementById('modalDeptSelect') && document.getElementById('modalDeptSelect').value) {
            onModalDeptChange();
        }
    });
</script>
@endif
@endsection
