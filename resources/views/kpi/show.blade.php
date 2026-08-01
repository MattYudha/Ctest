@extends('layouts.dashboard')

@push('styles')
<style>
    /* ══ ENTERPRISE KPI SHOW PAGE STYLING ══ */
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

    /* Dark Mode Table Fixes */
    body.theme-dark .table th, [data-bs-theme="dark"] .table th {
        background-color: #0f172a !important;
        color: #cbd5e1 !important;
        border-color: #334155 !important;
    }
    body.theme-dark .table td, [data-bs-theme="dark"] .table td {
        border-color: #334155 !important;
        color: #f1f5f9 !important;
    }
    body.theme-dark .card-header, [data-bs-theme="dark"] .card-header {
        background-color: #0f172a !important;
        border-color: #334155 !important;
    }
    body.theme-dark .form-control, [data-bs-theme="dark"] .form-control,
    body.theme-dark .form-select, [data-bs-theme="dark"] .form-select {
        background-color: #0f172a !important;
        border-color: #334155 !important;
        color: #f8fafc !important;
    }
    body.theme-dark .text-dark, [data-bs-theme="dark"] .text-dark {
        color: #f8fafc !important;
    }
    body.theme-dark .modal-content, [data-bs-theme="dark"] .modal-content {
        background-color: #1e293b !important;
        color: #f8fafc !important;
        border-color: #334155 !important;
    }
    body.theme-dark .modal-header, body.theme-dark .modal-footer,
    [data-bs-theme="dark"] .modal-header, [data-bs-theme="dark"] .modal-footer {
        background-color: #0f172a !important;
        border-color: #334155 !important;
    }
</style>
@endpush

@section('content')
<div class="page-heading">
    <div class="row align-items-center mb-3">
        <div class="col-md-7">
            <h3 class="mb-0 text-dark font-weight-bold">KPI Report - {{ $employee->fullname }}</h3>
            <p class="text-muted mb-0">{{ $employee->department->name ?? '-' }} • {{ $employee->role?->title ?? 'Staff' }}</p>
        </div>
        <div class="col-md-5 text-end">
            <div class="d-flex justify-content-end align-items-center gap-2">
                <form action="{{ route('kpi.sync-employee', $employee->id) }}" method="POST" class="d-inline">
                    @csrf
                    <input type="hidden" name="period" value="{{ $period }}">
                    <button type="submit" class="btn btn-sm btn-primary shadow-sm" title="Sinkronkan nilai KPI secara real-time">
                        <i class="bi bi-arrow-repeat me-1"></i> Sync Live Metrics
                    </button>
                </form>
                <button type="button" class="btn btn-sm btn-outline-info shadow-sm" data-bs-toggle="modal" data-bs-target="#auditBreakdownModal">
                    <i class="bi bi-journal-check me-1"></i> Audit Presensi & Log
                </button>
                <a href="{{ route('kpi.trend', $employee->id) }}" class="btn btn-sm btn-outline-success shadow-sm">
                    <i class="bi bi-graph-up me-1"></i> View Trend
                </a>
            </div>
        </div>
    </div>
</div>

<div class="page-content">
    <div class="container-fluid px-0">

        <!-- Executive Stat Tiles Row -->
        <div class="row mb-4">
            <!-- Period Selector Tile -->
            <div class="col-12 col-md-3 mb-3">
                <div class="stat-tile-soft h-100 d-flex flex-column justify-content-between">
                    <div>
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.78rem;">Evaluation Period</small>
                        <div class="mt-2">
                            <input type="month" id="periodSelect" class="form-control form-control-sm fw-bold" value="{{ $period }}" onchange="changePeriod()">
                        </div>
                    </div>
                    <small class="text-muted mt-2">Select month for detailed report</small>
                </div>
            </div>

            <!-- Composite Score Tile -->
            <div class="col-12 col-md-3 mb-3">
                <div class="stat-tile-soft h-100 d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.78rem;">Composite Score</small>
                        @php
                            $scoreColorClass = $compositeScore >= 90 ? 'text-success' :
                                              ($compositeScore >= 75 ? 'text-info' :
                                              ($compositeScore >= 60 ? 'text-warning' : 'text-danger'));
                        @endphp
                        <h2 class="mb-0 fw-extrabold {{ $scoreColorClass }} mt-1">
                            {{ round($compositeScore, 2) }}<span class="fs-6 text-muted"> /100</span>
                        </h2>
                        <small class="text-muted">Weighted performance score</small>
                    </div>
                    <div class="badge-soft-sky rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-speedometer2 fs-4"></i>
                    </div>
                </div>
            </div>

            <!-- Performance Level Tile -->
            <div class="col-12 col-md-3 mb-3">
                <div class="stat-tile-soft h-100 d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.78rem;">Performance Level</small>
                        <div class="mt-2">
                            @switch($performanceLevel)
                                @case('excellent') <span class="perf-pill perf-excellent">Excellent</span> @break
                                @case('good') <span class="perf-pill perf-good">Good</span> @break
                                @case('satisfactory') <span class="perf-pill perf-satisfactory">Satisfactory</span> @break
                                @case('needs_improvement') <span class="perf-pill perf-improvement">Needs Improvement</span> @break
                                @default <span class="perf-pill perf-unsatisfactory">Unsatisfactory</span>
                            @endswitch
                        </div>
                        <small class="text-muted d-block mt-1">Official Evaluation Grade</small>
                    </div>
                    <div class="badge-soft-amber rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-award-fill fs-4"></i>
                    </div>
                </div>
            </div>

            <!-- KPIs Achieved & Warnings Tile -->
            <div class="col-12 col-md-3 mb-3">
                <div class="stat-tile-soft h-100 d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.78rem;">Metrics & Warnings</small>
                        <h4 class="mb-0 fw-extrabold text-dark mt-1">
                            {{ $kpiRecords->where('status', 'achieved')->count() }}/{{ $kpiRecords->count() }} <span class="fs-6 text-muted">Achieved</span>
                        </h4>
                        <small class="text-warning fw-bold d-block mt-1">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $kpiRecords->where('status', 'warning')->count() }} Active Warnings
                        </small>
                    </div>
                    <div class="badge-soft-emerald rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                        <i class="bi bi-check-circle-fill fs-4"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Category Breakdown Tables -->
        @foreach($kpisByCategory as $category => $records)
        @php
            $avgScore = $records->avg(function($r) { return $r->getAchievementPercentage(); });
            $cardBorderColor = $avgScore >= 90 ? 'success' : ($avgScore >= 75 ? 'info' : ($avgScore >= 60 ? 'warning' : 'danger'));
        @endphp
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center py-3 border-bottom">
                        <h5 class="card-title mb-0 text-dark fw-bold">
                            <i class="bi bi-folder-fill me-2 text-primary"></i>{{ $category }}
                        </h5>
                        <div class="d-flex align-items-center">
                            <span class="text-muted me-2 small text-uppercase fw-bold">Average Achievement</span>
                            <span class="badge bg-{{ $cardBorderColor }} rounded-pill px-3 py-2 fw-bold">
                                {{ round($avgScore, 1) }}%
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3">Metric Name</th>
                                        <th class="text-center" style="width: 110px;">Target</th>
                                        <th class="text-center" style="width: 110px;">Actual</th>
                                        <th class="text-center" style="width: 220px;">Achievement</th>
                                        <th class="text-center" style="width: 130px;">Status</th>
                                        <th class="text-end px-4" style="width: 110px;">Variance</th>
                                        @if(in_array(session('role'), [\App\Constants\Roles::MASTER_ADMIN, \App\Constants\Roles::HR_ADMINISTRATOR]))
                                        <th class="text-center" style="width: 90px;">Actions</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($records as $record)
                                    <tr>
                                        <td class="px-4">
                                            <div class="d-flex flex-column">
                                                <span class="mb-1 text-dark fw-bold">{{ $record->kpi->name }}</span>
                                                <span class="text-muted small">{{ $record->kpi->unit }}</span>
                                                @if($record->notes)
                                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 fw-bold mt-1 text-wrap text-start p-2">
                                                        <i class="bi bi-chat-left-text me-1"></i>{{ $record->notes }}
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-center fw-bold text-dark">
                                            {{ round($record->target_value, 2) }}
                                        </td>
                                        <td class="text-center fw-bold text-dark">
                                            {{ round($record->actual_value, 2) }}
                                        </td>
                                        <td class="align-middle">
                                            @php
                                                $achievement = $record->getAchievementPercentage();
                                            @endphp
                                            <div class="d-flex align-items-center px-2">
                                                <div class="progress flex-grow-1 me-2" style="height: 8px; border-radius: 4px; background-color: #e2e8f0;">
                                                    <div class="progress-bar {{ $achievement >= 100 ? 'bg-success' : ($achievement >= 80 ? 'bg-warning' : 'bg-danger') }}" 
                                                         role="progressbar"
                                                         style="width: {{ min($achievement, 100) }}%; border-radius: 4px;">
                                                    </div>
                                                </div>
                                                <span class="small fw-bold {{ $achievement >= 100 ? 'text-success' : ($achievement >= 80 ? 'text-warning' : 'text-danger') }}">
                                                    {{ round($achievement, 1) }}%
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @switch($record->status)
                                                @case('achieved')
                                                    <span class="badge badge-soft-emerald px-3 py-2 fw-bold">Achieved</span>
                                                    @break
                                                @case('warning')
                                                    <span class="badge badge-soft-amber px-3 py-2 fw-bold">Warning</span>
                                                    @break
                                                @default
                                                    <span class="badge badge-soft-rose px-3 py-2 fw-bold">Critical</span>
                                            @endswitch
                                        </td>
                                        <td class="text-end px-4 fw-bold">
                                            @php
                                                $variance = $record->getVariance();
                                            @endphp
                                            <span class="{{ $variance >= 0 ? 'text-success' : 'text-danger' }}">
                                                {{ $variance >= 0 ? '+' : '' }}{{ round($variance, 2) }}
                                            </span>
                                        </td>
                                        @if(in_array(session('role'), [\App\Constants\Roles::MASTER_ADMIN, \App\Constants\Roles::HR_ADMINISTRATOR]))
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <button type="button" class="btn btn-xs btn-outline-info py-0 px-2"
                                                        data-bs-toggle="modal" data-bs-target="#hrNoteModal-{{ $record->record->id ?? $record->id }}"
                                                        title="Tambah / Edit Catatan HR">
                                                    <i class="bi bi-chat-left-text-fill" style="font-size:0.75rem;"></i>
                                                </button>
                                                <form action="{{ route('kpi.destroy-record', $record->record->id ?? $record->id) }}" method="POST" class="delete-kpi-form d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-xs btn-outline-danger py-0 px-2 delete-kpi" title="Hapus Metrik">
                                                        <i class="bi bi-trash-fill" style="font-size:0.75rem;"></i>
                                                    </button>
                                                </form>
                                            </div>

                                            <!-- HR Note Modal -->
                                            <div class="modal fade text-start" id="hrNoteModal-{{ $record->record->id ?? $record->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content border-0 shadow-lg">
                                                        <form action="{{ route('kpi.hr-note', $record->record->id ?? $record->id) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-header border-bottom py-3">
                                                                <h5 class="modal-title fw-bold text-dark mb-0">Catatan & Dispensasi HR</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body py-4">
                                                                <p class="small text-muted mb-3">Metrik: <strong>{{ $record->kpi->name }}</strong> ({{ $record->period }})</p>
                                                                <div class="mb-3">
                                                                    <label for="notes-{{ $record->id }}" class="form-label text-muted small fw-bold">CATATAN DISPENSASI / KETERANGAN ADMIN</label>
                                                                    <textarea class="form-control" id="notes-{{ $record->id }}" name="notes" rows="4" placeholder="Tuliskan catatan dispensasi resmi (misal: Tugas luar kota disetujui HR)..." required>{{ $record->notes }}</textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer border-top py-3">
                                                                <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Batal</button>
                                                                <button type="submit" class="btn btn-primary px-4 fw-bold">Simpan Catatan HR</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        @endif
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach

        <!-- Performance Review Section -->
        @if($performanceReview)
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                        <h5 class="card-title mb-0 fw-bold text-dark">
                            <i class="bi bi-chat-square-quote-fill me-2 text-info"></i>Performance Review — {{ $performanceReview->reviewed_by }}
                        </h5>
                        <span class="badge bg-{{ $performanceReview->status === 'approved' ? 'success' : 'warning' }} px-3 py-2 fw-bold">
                            {{ ucfirst($performanceReview->status) }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-check-circle me-1 text-success"></i>Strengths</h6>
                                <p class="text-secondary mb-0">{{ $performanceReview->strengths ?? 'No strengths recorded' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-arrow-up-circle me-1 text-warning"></i>Areas for Improvement</h6>
                                <p class="text-secondary mb-0">{{ $performanceReview->areas_for_improvement ?? 'No improvement areas recorded' }}</p>
                            </div>
                        </div>
                        <hr class="my-3">
                        <div class="row align-items-center">
                            <div class="col-md-8 mb-2 mb-md-0">
                                <h6 class="fw-bold text-dark mb-1">Review Comments</h6>
                                <p class="text-secondary mb-0">{{ $performanceReview->comments ?? 'No comments recorded' }}</p>
                            </div>
                            <div class="col-md-4 text-md-end">
                                <small class="text-muted"><i class="bi bi-calendar-event me-1"></i>Reviewed Date: {{ $performanceReview->reviewed_date->format('d M Y') }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm bg-light bg-opacity-50">
                    <div class="card-body py-3 d-flex align-items-center text-muted">
                        <i class="bi bi-info-circle fs-4 me-3 text-info"></i>
                        <span>No performance review available for this period.</span>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="row mb-4">
            <div class="col-12">
                <a href="{{ route('kpi.dashboard') }}" class="btn btn-outline-secondary shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Audit Breakdown Modal -->
<div class="modal fade" id="auditBreakdownModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-bottom py-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 d-flex align-items-center justify-content-center bg-info bg-opacity-10 p-3" style="width: 48px; height: 48px;">
                        <i class="bi bi-journal-check fs-4 text-info"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Audit Detail Presensi & Log Kerja</h5>
                        <small class="text-muted">Periode Evaluation: {{ \Carbon\Carbon::createFromFormat('Y-m', $period)->format('F Y') }}</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body py-4">
                <div class="alert alert-light border d-flex align-items-center mb-4">
                    <i class="bi bi-info-circle-fill text-info fs-4 me-3"></i>
                    <div class="small">
                        Skor komposit dihitung <strong>100% otomatis by system</strong> dari 2 indikator utama: <strong>Kepatuhan Checkout Presensi (Bobot 50%)</strong> dan <strong>Pengisian Log Kerja (Bobot 50%)</strong>.
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <!-- Hari Kerja Efektif -->
                    <div class="col-md-4">
                        <div class="stat-tile-soft text-center p-3">
                            <small class="text-muted fw-bold text-uppercase" style="font-size: 0.75rem;">Hari Kerja Efektif</small>
                            <h2 class="fw-extrabold text-dark my-1">{{ $dual['working_days'] ?? 0 }}</h2>
                            <small class="text-muted">Hari (Eks. Akhir Pekan & Libur)</small>
                        </div>
                    </div>
                    <!-- Checkout Presensi -->
                    <div class="col-md-4">
                        <div class="stat-tile-soft text-center p-3">
                            <small class="text-muted fw-bold text-uppercase" style="font-size: 0.75rem;">Presensi Checkout</small>
                            <h2 class="fw-extrabold text-sky my-1">{{ $dual['checkout_count'] ?? 0 }} <span class="fs-6 text-muted">/ {{ $dual['working_days'] ?? 0 }}</span></h2>
                            <span class="badge badge-soft-sky fw-bold">{{ round($dual['checkout_pct'] ?? 0, 1) }}% Compliance</span>
                        </div>
                    </div>
                    <!-- Pengisian Log Kerja -->
                    <div class="col-md-4">
                        <div class="stat-tile-soft text-center p-3">
                            <small class="text-muted fw-bold text-uppercase" style="font-size: 0.75rem;">Log Kerja Unik</small>
                            <h2 class="fw-extrabold text-emerald my-1">{{ $dual['unique_log_days'] ?? 0 }} <span class="fs-6 text-muted">/ {{ $dual['working_days'] ?? 0 }}</span></h2>
                            <span class="badge badge-soft-emerald fw-bold">{{ round($dual['log_pct'] ?? 0, 1) }}% Submission</span>
                        </div>
                    </div>
                </div>

                <!-- Rumus Skor Komposit -->
                <div class="card border bg-light bg-opacity-50">
                    <div class="card-body">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-calculator me-2 text-primary"></i>Kalkulasi Skor Komposit Akhir</h6>
                        <div class="p-3 bg-white border rounded text-center font-monospace text-dark fw-bold">
                            ({{ round($dual['checkout_pct'] ?? 0, 1) }}% + {{ round($dual['log_pct'] ?? 0, 1) }}%) / 2 = <span class="text-primary fs-5">{{ round($dual['score'] ?? 0, 2) }} / 100</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top py-3">
                <button type="button" class="btn btn-secondary px-4 fw-bold" data-bs-dismiss="modal">Tutup Audit</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function changePeriod() {
        const period = document.getElementById('periodSelect').value;
        if(period) {
            window.location.href = `{{ route('kpi.show', $employee->id) }}?period=${period}`;
        }
    }
    
    $(function() {
        $('#addKPIModal').appendTo('body');
        
        // Delete KPI confirmation
        $('.delete-kpi').on('click', function(e) {
            e.preventDefault();
            const form = $(this).closest('form');
            
            Swal.fire({
                title: 'Hapus Metrik KPI?',
                text: "Data metrik ini akan dihapus permanen dari periode ini.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                customClass: {
                    confirmButton: 'btn btn-danger px-4',
                    cancelButton: 'btn btn-light px-4'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
        
        $('#kpi_id').on('change', function() {
            var selected = $(this).find('option:selected');
            if (selected.data('auto') === true || selected.data('auto') === 'true') {
                $('#add_target_value').val(selected.data('target'));
            } else {
                $('#add_target_value').val(selected.data('target'));
            }
        });
    });
</script>
@endpush
@endsection
