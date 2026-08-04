@extends('layouts.dashboard')

@section('content')
@push('styles')
<style>
/* ════════════════════════════════════════════════════
   KPI DASHBOARD — Premium Enterprise UI
   ════════════════════════════════════════════════════ */
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

:root {
    --kpi-primary: #0f172a;       /* Slate 900 */
    --kpi-primary-light: #1e293b; /* Slate 800 */
    --kpi-accent: #3b82f6;        /* Blue 500 */
    --kpi-accent-soft: rgba(59, 130, 246, 0.1);
    --kpi-success: #10b981;       /* Emerald 500 */
    --kpi-success-soft: rgba(16, 185, 129, 0.1);
    --kpi-warning: #f59e0b;       /* Amber 500 */
    --kpi-warning-soft: rgba(245, 158, 11, 0.1);
    --kpi-danger: #ef4444;        /* Red 500 */
    --kpi-danger-soft: rgba(239, 68, 68, 0.1);
    --kpi-surface: #ffffff;
    --kpi-bg: #f8fafc;
    --kpi-border: #e2e8f0;
    --kpi-text-main: #0f172a;
    --kpi-text-muted: #64748b;
    --kpi-shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --kpi-shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --kpi-shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    --kpi-radius: 20px;
    --kpi-blue: #3b82f6;
    --kpi-green: #10b981;
    --kpi-purple: #8b5cf6;
    --kpi-amber: #f59e0b;
    --kpi-radius-md: 12px;
    --kpi-radius-sm: 8px;
}

body {
    font-family: 'Plus Jakarta Sans', sans-serif;
    background-color: var(--kpi-bg);
    color: var(--kpi-text-main);
}

/* Page Header */
.kpi-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 2.5rem;
    flex-wrap: wrap;
    gap: 1.5rem;
}
.kpi-page-title {
    font-size: 1.875rem;
    font-weight: 800;
    color: var(--kpi-text-main);
    letter-spacing: -0.025em;
    margin-bottom: 0.25rem;
}
.kpi-page-subtitle {
    font-size: 0.9375rem;
    color: var(--kpi-text-muted);
    font-weight: 500;
}

/* Action Buttons */
.btn-kpi {
    display: inline-flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.75rem 1.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    border-radius: var(--kpi-radius-md);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1.5px solid transparent;
}
.btn-kpi-primary {
    background: var(--kpi-primary);
    color: #fff;
    box-shadow: var(--kpi-shadow-md);
}
.btn-kpi-primary:hover {
    background: var(--kpi-primary-light);
    transform: translateY(-2px);
    box-shadow: var(--kpi-shadow-lg);
    color: #fff;
}
.btn-kpi-outline {
    background: var(--kpi-surface);
    color: var(--kpi-text-main);
    border-color: var(--kpi-border);
}
.btn-kpi-outline:hover {
    background: #f1f5f9;
    border-color: var(--kpi-text-muted);
    transform: translateY(-2px);
}

/* Period Nav Bar */
.kpi-period-nav {
    background: var(--kpi-surface);
    border-radius: var(--kpi-radius-md);
    padding: 0.875rem 1.25rem;
    border: 1px solid var(--kpi-border);
    box-shadow: var(--kpi-shadow-sm);
    margin-bottom: 2rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}
.kpi-period-nav .period-label {
    font-size: 1.0625rem;
    font-weight: 700;
    color: var(--kpi-text-main);
    letter-spacing: -0.01em;
    flex: 1;
    text-align: center;
}
.btn-period-nav {
    width: 36px; height: 36px;
    border-radius: 10px;
    border: 1.5px solid var(--kpi-border);
    background: var(--kpi-surface);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    color: var(--kpi-text-main);
    text-decoration: none;
    flex-shrink: 0;
}
.btn-period-nav:hover { background: #f1f5f9; border-color: var(--kpi-accent); color: var(--kpi-accent); }
.btn-period-nav.disabled { opacity: 0.4; pointer-events: none; }

/* Summary Cards */
.kpi-summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 1.25rem;
    margin-bottom: 2.5rem;
}
.kpi-summary-card {
    background: var(--kpi-surface);
    border-radius: var(--kpi-radius);
    padding: 1.5rem 1.75rem;
    box-shadow: var(--kpi-shadow-sm);
    position: relative;
    border: 1px solid var(--kpi-border);
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    overflow: hidden;
    animation: cardFadeUp 0.5s ease both;
}
.kpi-summary-card:nth-child(1) { animation-delay: 0.05s; }
.kpi-summary-card:nth-child(2) { animation-delay: 0.10s; }
.kpi-summary-card:nth-child(3) { animation-delay: 0.15s; }
.kpi-summary-card:nth-child(4) { animation-delay: 0.20s; }
@keyframes cardFadeUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
}
.kpi-summary-card:hover { transform: translateY(-4px); box-shadow: var(--kpi-shadow-lg); }
.kpi-summary-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    border-radius: var(--kpi-radius) var(--kpi-radius) 0 0;
}
.card-blue::before   { background: linear-gradient(90deg, #3b82f6, #6366f1); }
.card-green::before  { background: linear-gradient(90deg, #10b981, #34d399); }
.card-purple::before { background: linear-gradient(90deg, #8b5cf6, #a78bfa); }
.card-amber::before  { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
.kpi-card-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem;
    margin-bottom: 1rem;
}
.icon-blue   { background: rgba(59,130,246,0.1);  color: #3b82f6; }
.icon-green  { background: rgba(16,185,129,0.1);  color: #10b981; }
.icon-purple { background: rgba(139,92,246,0.1);  color: #8b5cf6; }
.icon-amber  { background: rgba(245,158,11,0.1);  color: #f59e0b; }

.kpi-summary-title {
    font-size: 0.8125rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--kpi-text-muted);
    font-weight: 700;
    margin-bottom: 0.75rem;
    z-index: 1;
}
.kpi-summary-value {
    font-size: 2.25rem;
    font-weight: 800;
    color: var(--kpi-text-main);
    line-height: 1;
    margin-bottom: 0.5rem;
    letter-spacing: -0.025em;
    z-index: 1;
}
.kpi-summary-sub {
    font-size: 0.875rem;
    color: var(--kpi-text-muted);
    font-weight: 500;
    z-index: 1;
}

/* Badges */
.kpi-badge {
    padding: 0.4rem 0.875rem;
    border-radius: 10px;
    font-size: 0.75rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
}
.badge-soft-success { background: var(--kpi-success-soft); color: var(--kpi-success); }
.badge-soft-info { background: var(--kpi-accent-soft); color: var(--kpi-accent); }
.badge-soft-warning { background: var(--kpi-warning-soft); color: var(--kpi-warning); }
.badge-soft-danger { background: var(--kpi-danger-soft); color: var(--kpi-danger); }

/* Status Banner */
.kpi-status-banner {
    border-radius: var(--kpi-radius);
    padding: 1.5rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 2.5rem;
    border: 1px solid var(--kpi-border);
    background: var(--kpi-surface);
    position: relative;
    overflow: hidden;
}
.kpi-status-banner::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 6px;
}
.status-draft::before { background: var(--kpi-text-muted); }
.status-submitted::before { background: var(--kpi-accent); }
.status-approved::before { background: var(--kpi-success); }
.status-rejected::before { background: var(--kpi-danger); }

.kpi-status-icon {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

/* Data Cards & Tables */
.kpi-data-card {
    background: var(--kpi-surface);
    border-radius: var(--kpi-radius);
    box-shadow: var(--kpi-shadow-sm);
    margin-bottom: 2.5rem;
    border: 1px solid var(--kpi-border);
}
.kpi-data-header {
    padding: 1.5rem 1.75rem;
    border-bottom: 1px solid var(--kpi-border);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.kpi-data-title {
    font-size: 1.125rem;
    font-weight: 700;
    color: var(--kpi-text-main);
}
.kpi-table th {
    background: #f8fafc;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--kpi-text-muted);
    letter-spacing: 0.05em;
    padding: 1rem 1.75rem;
}
.kpi-table td {
    padding: 1.5rem 1.75rem;
    border-bottom: 1px solid var(--kpi-border);
    vertical-align: middle;
}
.kpi-table tr:hover { background-color: #fbfcfd; }

/* Progress Bar */
.kpi-progress {
    height: 10px;
    border-radius: 999px;
    background: #f1f5f9;
    overflow: hidden;
    position: relative;
}
.kpi-progress-bar {
    height: 100%;
    border-radius: 999px;
    transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Premium Alerts */
.kpi-alert {
    border: none;
    border-radius: 16px;
    padding: 1.125rem 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.02);
    position: relative;
    overflow: hidden;
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    animation: alertSlideIn 0.5s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes alertSlideIn {
    from { transform: translateY(-20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.kpi-alert-success {
    background: rgba(240, 253, 244, 0.9);
    border: 1px solid rgba(187, 247, 208, 0.5);
    color: #166534;
}

.kpi-alert-error {
    background: rgba(254, 242, 242, 0.9);
    border: 1px solid rgba(FECACA, 0.5);
    color: #991B1B;
}

.kpi-alert-icon {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}

.kpi-alert-success .kpi-alert-icon {
    background: #DCFCE7;
    color: #16A34A;
}

.kpi-alert-error .kpi-alert-icon {
    background: #FEE2E2;
    color: #DC2626;
}

.kpi-alert-message {
    font-weight: 600;
    font-size: 0.9375rem;
    letter-spacing: -0.01em;
}

/* Modals & Backdrop Fixes */
.modal-backdrop {
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    z-index: 2000 !important; /* Ensure it is above sidebar (1050) and header */
    background-color: rgba(0, 0, 0, 0.6) !important;
    backdrop-filter: blur(4px);
}

.modal {
    z-index: 2001 !important; /* Above backdrop */
}

.modal-content {
    border: none;
    border-radius: 24px;
    box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.5);
    overflow: hidden;
}
.modal-header {
    padding: 2rem 2rem 1.5rem;
    border-bottom: none;
}
.modal-body {
    padding: 0 2rem 2rem;
}
.modal-footer {
    padding: 1.5rem 2rem 2rem;
    border-top: 1px solid var(--kpi-border);
    background: #f8fafc;
    border-bottom-left-radius: 24px;
    border-bottom-right-radius: 24px;
}
.form-label {
    font-size: 0.8125rem;
    font-weight: 700;
    color: var(--kpi-text-main);
    margin-bottom: 0.5rem;
}
.form-control, .form-select {
    padding: 0.75rem 1rem;
    border-radius: 12px;
    border: 1.5px solid var(--kpi-border);
    font-weight: 500;
}
.form-control:focus, .form-select:focus {
    border-color: var(--kpi-accent);
    box-shadow: 0 0 0 4px var(--kpi-accent-soft);
}

/* Mobile Adjustments */
@media (max-width: 1024px) {
    .kpi-summary-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 640px) {
    .kpi-page-header { flex-direction: column; align-items: stretch; }
    .kpi-summary-grid { grid-template-columns: 1fr; }
    .kpi-status-banner { flex-direction: column; gap: 1.5rem; align-items: flex-start; }
    .kpi-status-banner .btn-kpi { width: 100%; justify-content: center; }
    .kpi-table th:nth-child(2), .kpi-table td:nth-child(2),
    .kpi-table th:nth-child(5), .kpi-table td:nth-child(5) { display: none; }
}

/* ═══════════════════════════════════════════════════════
   MODAL BACKDROP — Premium Full Dark Overlay
   Fixed to cover 100% screen even when Mazer body zoom is active
   ═══════════════════════════════════════════════════════ */
.modal-backdrop {
    position: fixed !important;
    top: -20vh !important;
    left: -20vw !important;
    width: 140vw !important;
    height: 140vh !important;
    z-index: 9998 !important;
    background-color: rgba(15, 23, 42, 0.85) !important; /* Premium slate-900 */
    backdrop-filter: blur(5px) !important;
    -webkit-backdrop-filter: blur(5px) !important;
}
.modal {
    z-index: 9999 !important;
}
body.modal-open #sidebar,
body.modal-open .sidebar-wrapper,
body.modal-open .mobile-nav-header {
    z-index: 1 !important;
}
body.modal-open {
    overflow: hidden !important;
}

/* Modal Content Refinement */
.modal-content {
    border: none;
    border-radius: 24px;
    box-shadow: 0 32px 64px -12px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.05);
    overflow: hidden;
}
.modal-header {
    padding: 2rem 2rem 1.25rem;
    border-bottom: none;
    background: #fff;
}
.modal-body {
    padding: 0 2rem 1.5rem;
    background: #fff;
}
.modal-footer {
    padding: 1.25rem 2rem 1.75rem;
    border-top: 1px solid var(--kpi-border);
    background: #f8fafc;
    border-radius: 0 0 24px 24px;
}
</style>
@endpush

<div class="kpi-page-header">
    <div>
        <h1 class="kpi-page-title">My KPI Dashboard</h1>
        <p class="kpi-page-subtitle">Monitoring and managing your performance metrics for this period</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('reports.export-pdf', $employee->id) }}?period={{ $period }}" class="btn-kpi btn-kpi-outline" target="_blank">
            <i class="bi bi-file-earmark-pdf-fill text-danger"></i> Export PDF
        </a>
        <a href="{{ route('kpi.dashboard') }}" class="btn-kpi btn-kpi-outline" title="Refresh">
            <i class="bi bi-arrow-clockwise"></i>
        </a>
    </div>
</div>

{{-- ═══ MAZER PAGE HEADING WRAPPER ═══ --}}
<div class="page-heading">
<div class="page-title">
    <div class="row">
        <div class="col-12 col-md-6 order-md-1 order-last">
            <h3><i class="bi bi-graph-up-arrow"></i> KPI Dashboard</h3>
            <p class="text-subtitle text-muted">Monitor kinerja &amp; capaian target Anda</p>
        </div>
        <div class="col-12 col-md-6 order-md-2 order-first">
            <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">KPI</li>
                </ol>
            </nav>
        </div>
    </div>
</div>
<section class="section">

{{-- ═══ PERIOD NAVIGATION ═══ --}}
<div class="kpi-period-nav">
    <a href="{{ route('kpi.dashboard') }}?period={{ $prevPeriod }}" class="btn-period-nav" title="Bulan Sebelumnya">
        <i class="bi bi-chevron-left"></i>
    </a>
    <div class="period-label">
        <i class="bi bi-calendar3 text-primary me-2"></i>
        {{ $periodCarbon->translatedFormat('F Y') ?: \Carbon\Carbon::createFromFormat('Y-m', $period)->format('F Y') }}
        @if($isCurrentMonth)
            <span class="badge bg-primary bg-opacity-10 text-primary ms-2" style="font-size:0.7rem;font-weight:700;">Bulan Ini</span>
        @endif
    </div>
    <a href="{{ route('kpi.dashboard') }}?period={{ $nextPeriod }}" class="btn-period-nav {{ $isCurrentMonth ? 'disabled' : '' }}" title="Bulan Berikutnya">
        <i class="bi bi-chevron-right"></i>
    </a>
    @if(!$isCurrentMonth)
    <a href="{{ route('kpi.dashboard') }}" class="btn btn-sm btn-outline-primary ms-2" style="border-radius:10px;font-size:0.8rem;font-weight:600;">
        <i class="bi bi-arrow-counterclockwise"></i> Kembali ke Sekarang
    </a>
    @endif
</div>

<!-- Welcome / Info Banner -->
<div class="alert alert-primary alert-dismissible fade show border-0 shadow-sm rounded-4 mb-4" role="alert" style="background-color: #f0f5ff; border-left: 5px solid var(--kpi-accent) !important;">
    <div class="d-flex align-items-start gap-3">
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px;">
            <i class="bi bi-lightbulb-fill fs-5"></i>
        </div>
        <div>
            <h5 class="alert-heading fw-bold mb-1" style="color: var(--kpi-primary-light);">Panduan Membaca Dashboard KPI</h5>
            <p class="mb-0 text-secondary" style="font-size: 0.95rem;">
                Nilai performa di bawah ini diperbarui secara otomatis berdasarkan tingkat disiplin Anda dalam melakukan <strong>Checkout Presensi</strong> dan produktivitas dalam <strong>Mengisi Log Pekerjaan</strong> harian.
            </p>
        </div>
    </div>
    <button type="button" class="btn-close shadow-none" data-bs-dismiss="alert" aria-label="Close" style="top: 10px; right: 10px;"></button>
</div>

@if(session('success'))
<div class="kpi-alert kpi-alert-success alert-dismissible fade show" role="alert">
    <div class="kpi-alert-icon">
        <i class="bi bi-check-circle-fill"></i>
    </div>
    <div class="kpi-alert-message">{{ session('success') }}</div>
    <button type="button" class="btn-close ms-auto shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

@if(session('error'))
<div class="kpi-alert kpi-alert-error alert-dismissible fade show" role="alert">
    <div class="kpi-alert-icon">
        <i class="bi bi-exclamation-octagon-fill"></i>
    </div>
    <div class="kpi-alert-message">{{ session('error') }}</div>
    <button type="button" class="btn-close ms-auto shadow-none" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
@endif

<!-- Summary Grid -->
<div class="kpi-summary-grid">
    <!-- Composite Score -->
    <div class="kpi-summary-card card-blue">
        <div class="kpi-card-icon icon-blue"><i class="bi bi-bar-chart-fill"></i></div>
        <div>
            <div class="kpi-summary-title">
                Composite Score
                <i class="bi bi-info-circle ms-1 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="Nilai rata-rata dari seluruh metrik Anda (50% Kepatuhan Checkout + 50% Pengisian Log)."></i>
            </div>
            <div class="kpi-summary-value">{{ round($compositeScore, 2) }}<span class="fs-6 text-muted fw-medium">/100</span></div>
            <div class="kpi-summary-sub">Overall performance score</div>
        </div>
    </div>

    <!-- Performance Level -->
    @php
        $lvlClass = match($performanceLevel) {
            'excellent' => 'card-green', 'good' => 'card-blue',
            'satisfactory' => 'card-amber', default => 'card-amber'
        };
        $iconClass = match($performanceLevel) {
            'excellent' => 'icon-green', 'good' => 'icon-blue',
            'satisfactory' => 'icon-amber', default => 'icon-amber'
        };
    @endphp
    <div class="kpi-summary-card {{ $lvlClass }}">
        <div class="kpi-card-icon {{ $iconClass }}"><i class="bi bi-trophy-fill"></i></div>
        <div>
            <div class="kpi-summary-title">
                Performance Level
                <i class="bi bi-info-circle ms-1 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="Status capaian performa Anda. Skala: Excellent (>=90), Good (75-89), Satisfactory (60-74), Needs Improvement (45-59), Unsatisfactory (<45)."></i>
            </div>
            <div class="mt-1">
                @switch($performanceLevel)
                    @case('excellent')   <span class="kpi-badge badge-soft-success"><i class="bi bi-stars"></i> Excellent</span> @break
                    @case('good')        <span class="kpi-badge badge-soft-info"><i class="bi bi-hand-thumbs-up-fill"></i> Good</span> @break
                    @case('satisfactory')<span class="kpi-badge badge-soft-warning"><i class="bi bi-check-circle-fill"></i> Satisfactory</span> @break
                    @case('needs_improvement') <span class="kpi-badge badge-soft-warning"><i class="bi bi-exclamation-triangle-fill"></i> Needs Improvement</span> @break
                    @default             <span class="kpi-badge badge-soft-danger"><i class="bi bi-x-circle-fill"></i> Unsatisfactory</span>
                @endswitch
            </div>
        </div>
    </div>

    <!-- KPIs Achieved -->
    <div class="kpi-summary-card card-green">
        <div class="kpi-card-icon icon-green"><i class="bi bi-check-all"></i></div>
        <div>
            <div class="kpi-summary-title">
                KPIs Achieved
                <i class="bi bi-info-circle ms-1 text-muted" data-bs-toggle="tooltip" data-bs-placement="top" title="Jumlah metrik di mana Anda berhasil mencapai target 100% secara penuh."></i>
            </div>
            <div class="kpi-summary-value">{{ $kpiRecords->where('status', 'achieved')->count() }}<span class="fs-6 text-muted fw-medium">/{{ $kpiRecords->count() }}</span></div>
            <div class="kpi-summary-sub">Metrics hitting the target</div>
        </div>
    </div>

    <!-- Period -->
    <div class="kpi-summary-card card-purple">
        <div class="kpi-card-icon icon-purple"><i class="bi bi-calendar3"></i></div>
        <div>
            <div class="kpi-summary-title">Review Period</div>
            <div class="kpi-summary-value fs-3">{{ \Carbon\Carbon::createFromFormat('Y-m', $period)->format('M Y') }}</div>
            <div class="kpi-summary-sub">Period code: {{ $period }}</div>
        </div>
    </div>
</div>

<!-- Submission Status Banner -->
@php
    $firstRecord = $kpiRecords->first();
    $submissionStatus = $firstRecord->submission_status ?? 'draft';
    $reviewerNotes = $firstRecord->reviewer_notes ?? null;
    $isMasterAdmin = \App\Constants\Roles::isAdmin(session('role'));
@endphp

@if($isOwner || $isExecutive)
<div class="kpi-status-banner status-{{ $submissionStatus }}">
    <div class="d-flex align-items-center gap-4">
        <div class="kpi-status-icon 
            @if($submissionStatus === 'draft') bg-light text-secondary 
            @elseif($submissionStatus === 'submitted') bg-primary text-white 
            @elseif($submissionStatus === 'approved') bg-success text-white 
            @else bg-danger text-white @endif">
            @switch($submissionStatus)
                @case('draft') <i class="bi bi-pencil-fill"></i> @break
                @case('submitted') <i class="bi bi-send-check-fill"></i> @break
                @case('approved') <i class="bi bi-shield-check"></i> @break
                @case('rejected') <i class="bi bi-exclamation-octagon-fill"></i> @break
            @endswitch
        </div>
        <div>
            <h6 class="mb-1 fw-bold text-dark">Status Pengajuan: <span class="text-uppercase text-primary">{{ $submissionStatus }}</span></h6>
            <p class="mb-0 text-muted small">
                @if($submissionStatus === 'draft') Lengapi nilai aktual Anda dan ajukan untuk peninjauan.
                @elseif($submissionStatus === 'submitted') KPI Anda sedang dalam tahap peninjauan oleh manajemen.
                @elseif($submissionStatus === 'approved') KPI periode ini telah disetujui secara resmi.
                @elseif($submissionStatus === 'rejected') Pengajuan ditolak. Silakan perbaiki berdasarkan catatan di bawah.
                @endif
            </p>
            @if($submissionStatus === 'rejected' && $reviewerNotes)
                <div class="mt-2 p-2 bg-danger bg-opacity-10 rounded border border-danger border-opacity-10">
                    <span class="text-danger fw-bold small"><i class="bi bi-chat-dots-fill me-1"></i> Note: {{ $reviewerNotes }}</span>
                </div>
            @endif
        </div>
    </div>
    <div class="mt-3 mt-md-0">
        @if($isOwner && ($submissionStatus === 'draft' || $submissionStatus === 'rejected'))
            @if($employee->supervisor_id)
            <form action="{{ route('kpi.submit', $employee->id) }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="period" value="{{ $period }}">
                <button type="submit" class="btn-kpi btn-kpi-primary submit-confirm" data-message="Submit KPI untuk review oleh atasan?">
                    <i class="bi bi-rocket-takeoff-fill"></i> Ajukan Sekarang
                </button>
            </form>
            @else
            <span class="kpi-badge badge-soft-secondary"><i class="bi bi-person-x-fill"></i> Supervisor belum diatur</span>
            @endif
        @endif
    </div>
</div>
@endif

<!-- KPI Metrics Tables -->
@forelse($kpisByCategory as $category => $records)
<div class="kpi-data-card shadow-sm border-0 mb-5">
    <div class="kpi-data-header bg-white py-3 px-4 rounded-top border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <div class="d-flex align-items-center gap-2">
                <div style="width: 4px; height: 24px; background: var(--kpi-accent); border-radius: 2px;"></div>
                <h3 class="kpi-data-title mb-0 fs-5 fw-800 text-dark">{{ $category }} Metrics</h3>
            </div>
            @if(trim($category) === 'Attendance')
                <p class="text-muted small mb-0 mt-1" style="margin-left: 12px;"><i class="bi bi-info-circle"></i> Target: Melakukan <strong>Checkout</strong> presensi setiap hari kerja (100%).</p>
            @elseif(trim($category) === 'Productivity')
                <p class="text-muted small mb-0 mt-1" style="margin-left: 12px;"><i class="bi bi-info-circle"></i> Target: Mengisi minimal <strong>1 Log Pekerjaan</strong> setiap hari kerja (100%).</p>
            @endif
        </div>
        <span class="badge bg-light text-primary fw-bold">{{ $records->count() }} Metrics</span>
    </div>
    <div class="table-responsive">
        <table class="kpi-table table table-hover mb-0">
            <thead>
                <tr>
                    <th class="border-0 text-muted small fw-800">METRIC DETAILS</th>
                    <th class="border-0 text-muted small fw-800 text-center">TARGET</th>
                    <th class="border-0 text-muted small fw-800 text-center">ACTUAL</th>
                    <th class="border-0 text-muted small fw-800">ACHIEVEMENT</th>
                    <th class="border-0 text-muted small fw-800 text-center">STATUS</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                @foreach($records as $record)
                <tr>
                    <td>
                        <div class="fw-bold text-dark mb-1">{{ $record->kpi->name }}</div>
                        <div class="text-muted small lh-sm" style="max-width: 300px;">
                            {{ $record->kpi->description ?: 'No description provided' }}
                            @if(str_contains(strtolower($record->kpi->name), 'pengisian log'))
                                <br><strong class="text-primary d-block mt-1" style="font-size: 0.75rem;"><i class="bi bi-info-circle-fill"></i> Info Perhitungan:</strong>
                                <span style="font-size: 0.75rem;">Nilai ini diambil dari total log pekerjaan yang sudah Anda isi, dibandingkan dengan total hari kerja aktif di bulan ini.</span>
                            @elseif(str_contains(strtolower($record->kpi->name), 'kepatuhan checkout'))
                                <br><strong class="text-primary d-block mt-1" style="font-size: 0.75rem;"><i class="bi bi-info-circle-fill"></i> Info Perhitungan:</strong>
                                <span style="font-size: 0.75rem;">Nilai ini diambil dari total presensi yang memiliki catatan check-out valid, dibandingkan dengan total hari Anda hadir bekerja.</span>
                            @endif
                        </div>
                    </td>
                    <td class="text-center">
                        <div class="fw-800 text-dark">{{ $record->target_value }}</div>
                        <div class="extra-small text-muted text-uppercase fw-bold">{{ $record->kpi->unit }}</div>
                    </td>
                    <td class="text-center">
                        <div class="fw-800 text-primary">{{ $record->actual_value }}</div>
                        <div class="extra-small text-muted text-uppercase fw-bold">{{ $record->kpi->unit }}</div>
                    </td>
                    <td style="min-width: 200px;">
                        @php $achievement = $record->getAchievementPercentage(); @endphp
                        <div class="d-flex align-items-center gap-3">
                            <div class="flex-grow-1">
                                <div class="kpi-progress shadow-none" style="height: 6px; background: #f1f5f9;">
                                    <div class="kpi-progress-bar @if($achievement >= 100) bg-success @elseif($achievement >= 80) bg-primary @else bg-danger @endif" 
                                         style="width: {{ min($achievement, 100) }}%"></div>
                                </div>
                            </div>
                            <span class="fw-800 small @if($achievement >= 100) text-success @elseif($achievement >= 80) text-primary @else text-danger @endif">
                                {{ round($achievement, 1) }}%
                            </span>
                        </div>
                    </td>
                    <td class="text-center">
                        @switch($record->status)
                            @case('achieved')
                                <span class="kpi-badge badge-soft-success small fw-bold">Achieved</span>
                                @break
                            @case('warning')
                                <span class="kpi-badge badge-soft-warning small fw-bold">Warning</span>
                                @break
                            @default
                                <span class="kpi-badge badge-soft-danger small fw-bold">Critical</span>
                        @endswitch
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@empty
<div class="text-center py-5 bg-white rounded-3 border shadow-sm">
    <div class="mb-4">
        <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width:90px;height:90px;background:rgba(59,130,246,0.08);">
            <i class="bi bi-clipboard2-data text-primary" style="font-size:2.75rem;"></i>
        </div>
    </div>
    <h4 class="fw-800 text-dark mb-2">Belum Ada Metrik KPI</h4>
    <p class="text-muted mb-4" style="max-width:380px;margin:0 auto;">Tidak ada KPI terdaftar untuk periode <strong>{{ \Carbon\Carbon::createFromFormat('Y-m',$period)->format('F Y') }}</strong>.</p>
</div>
@endforelse

<!-- Incidents -->
@if($incidents->count() > 0)
<div class="kpi-data-card border border-danger border-opacity-25 shadow-none">
    <div class="kpi-data-header bg-danger bg-opacity-10 border-danger border-opacity-25">
        <h3 class="kpi-data-title text-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i> Active Incidents / SP</h3>
    </div>
    <div class="table-responsive">
        <table class="kpi-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Date</th>
                    <th>Severity</th>
                    <th>Status</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach($incidents as $incident)
                <tr>
                    <td class="fw-bold">{{ ucfirst(str_replace('_', ' ', $incident->type)) }}</td>
                    <td>{{ $incident->incident_date->format('d M Y') }}</td>
                    <td>
                        @switch($incident->severity)
                            @case('low') <span class="kpi-badge badge-soft-info">Low</span> @break
                            @case('medium') <span class="kpi-badge badge-soft-warning">Medium</span> @break
                            @case('high') <span class="kpi-badge badge-soft-danger">High</span> @break
                            @default <span class="kpi-badge badge-soft-danger bg-danger text-white">Critical</span>
                        @endswitch
                    </td>
                    <td>
                        <span class="kpi-badge {{ $incident->status === 'resolved' ? 'badge-soft-success' : 'badge-soft-warning' }}">
                            {{ ucfirst($incident->status) }}
                        </span>
                    </td>
                    <td class="text-muted" style="font-size:0.8rem">{{ $incident->description }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

</section>
</div>
{{-- ═══ END MAZER WRAPPER ═══ --}}

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>
@endpush

@endsection
