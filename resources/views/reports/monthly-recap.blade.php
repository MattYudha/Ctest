@extends('layouts.dashboard')

@push('styles')
<style>
    /* High-contrast KPI Score & Performance Styling */
    .kpi-score-badge {
        font-weight: 700 !important;
        font-size: 0.95rem !important;
        padding: 6px 14px !important;
        border-radius: 8px !important;
        display: inline-block !important;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
    }
    
    .score-excellent {
        background-color: #059669 !important;
        color: #ffffff !important;
    }
    .score-good {
        background-color: #0284c7 !important;
        color: #ffffff !important;
    }
    .score-satisfactory {
        background-color: #d97706 !important;
        color: #ffffff !important;
    }
    .score-unsatisfactory {
        background-color: #dc2626 !important;
        color: #ffffff !important;
    }

    /* Status Pills */
    .perf-pill {
        font-weight: 700 !important;
        font-size: 0.85rem !important;
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
        background-color: #fef2f2 !important;
        color: #b91c1c !important;
        border: 1px solid #fecaca !important;
    }

    /* Count Pill Badges */
    .count-pill {
        font-weight: 700 !important;
        font-size: 0.85rem !important;
        padding: 4px 10px !important;
        border-radius: 12px !important;
        min-width: 32px !important;
        text-align: center !important;
        display: inline-block !important;
    }
    .count-achieved {
        background-color: #10b981 !important;
        color: #ffffff !important;
    }
    .count-warning {
        background-color: #f59e0b !important;
        color: #ffffff !important;
    }
    .count-critical {
        background-color: #ef4444 !important;
        color: #ffffff !important;
    }

    /* Dark Mode Compatibility */
    [data-bs-theme="dark"] .perf-excellent {
        background-color: #064e3b !important;
        color: #6ee7b7 !important;
        border-color: #047857 !important;
    }
    [data-bs-theme="dark"] .perf-good {
        background-color: #0c4a6e !important;
        color: #7dd3fc !important;
        border-color: #0369a1 !important;
    }
    [data-bs-theme="dark"] .perf-satisfactory {
        background-color: #451a03 !important;
        color: #fcd34d !important;
        border-color: #b45309 !important;
    }
    [data-bs-theme="dark"] .perf-improvement {
        background-color: #431407 !important;
        color: #fdba74 !important;
        border-color: #c2410c !important;
    }
    [data-bs-theme="dark"] .perf-unsatisfactory {
        background-color: #450a0a !important;
        color: #fca5a5 !important;
        border-color: #b91c1c !important;
    }
    [data-bs-theme="dark"] #performanceTable td,
    [data-bs-theme="dark"] #performanceTable th {
        color: #f1f5f9 !important;
    }

    /* Table text contrast */
    #performanceTable td {
        vertical-align: middle !important;
        color: #1e293b;
    }
    #performanceTable th {
        color: #334155;
        font-weight: 700;
        background-color: #f8fafc;
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
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-left-primary h-100">
                    <div class="card-body">
                        <h6 class="text-primary font-weight-bold mb-1">Total Employees</h6>
                        <h2 class="mb-0 fw-bold text-dark">{{ count($kpiData) }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-left-success h-100">
                    <div class="card-body">
                        <h6 class="text-success font-weight-bold mb-1">Average Score</h6>
                        <h2 class="mb-0 fw-bold text-dark">{{ round(array_sum(array_column($kpiData, 'composite_score')) / (count($kpiData) ?: 1), 2) }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-left-info h-100">
                    <div class="card-body">
                        <h6 class="text-info font-weight-bold mb-1">Excellent</h6>
                        <h2 class="mb-0 fw-bold text-dark">{{ count(array_filter($kpiData, fn($e) => $e['performance_level'] === 'excellent')) }}</h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-left-warning h-100">
                    <div class="card-body">
                        <h6 class="text-warning font-weight-bold mb-1">Below Target</h6>
                        <h2 class="mb-0 fw-bold text-dark">{{ count(array_filter($kpiData, fn($e) => $e['performance_level'] === 'needs_improvement' || $e['performance_level'] === 'unsatisfactory')) }}</h2>
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
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="performanceTable">
                                <thead>
                                    <tr>
                                        <th style="width: 5%">#</th>
                                        <th>Employee</th>
                                        <th>Department</th>
                                        <th>Score</th>
                                        <th>Performance</th>
                                        <th>Achieved</th>
                                        <th>Warning</th>
                                        <th>Critical</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($kpiData as $index => $data)
                                    <tr>
                                        <td><strong class="text-dark">{{ $index + 1 }}</strong></td>
                                        <td>
                                            <strong class="text-dark fs-6">{{ $data['employee']->fullname }}</strong><br>
                                            <small class="text-muted">{{ $data['employee']->role?->title ?? 'N/A' }}</small>
                                        </td>
                                        <td><span class="badge bg-secondary text-white px-2 py-1">{{ $data['employee']->department->name ?? 'N/A' }}</span></td>
                                        <td>
                                            @php
                                                $scoreClass = $data['composite_score'] >= 90 ? 'score-excellent' : 
                                                            ($data['composite_score'] >= 75 ? 'score-good' : 
                                                            ($data['composite_score'] >= 60 ? 'score-satisfactory' : 'score-unsatisfactory'));
                                            @endphp
                                            <span class="kpi-score-badge {{ $scoreClass }}">
                                                {{ round($data['composite_score'], 2) }}/100
                                            </span>
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
                                        <td>
                                            <span class="count-pill count-achieved">{{ $data['achievements'] }}</span>
                                        </td>
                                        <td>
                                            <span class="count-pill count-warning">{{ $data['warnings'] }}</span>
                                        </td>
                                        <td>
                                            <span class="count-pill count-critical">{{ $data['critical'] }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('kpi.show', $data['employee']->id) }}?period={{ $period }}" 
                                               class="btn btn-sm btn-info text-white me-1" title="View Details">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="{{ route('reports.export-pdf', $data['employee']->id) }}?period={{ $period }}" 
                                               class="btn btn-sm btn-primary" target="_blank" title="Export PDF">
                                                <i class="bi bi-file-earmark-pdf"></i>
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

