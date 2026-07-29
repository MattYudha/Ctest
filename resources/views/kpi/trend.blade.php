@extends('layouts.dashboard')

@section('content')
<div class="page-heading">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h3>Performance Trend - {{ $employee->fullname }}</h3>
            <p class="text-muted">{{ $employee->department->name ?? '-' }} • {{ $employee->role?->title }}</p>
        </div>
        <div>
            <a href="{{ route('kpi.show', $employee->id) }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left"></i> Back to KPI Report
            </a>
        </div>
    </div>
</div>

<div class="page-content">
    <div class="container-fluid">
        <div class="row mb-4">
    <div class="col-md-12">
        <h4 class="mb-3">Overview</h4>
        @include('partials.dashboard-content')
    </div>
</div>

<!-- Filter Controls -->
<div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label for="monthsSelect" class="form-label">Time Range</label>
                                <select id="monthsSelect" class="form-select" onchange="changeMonths()">
                                    <option value="3" {{ $months == 3 ? 'selected' : '' }}>Last 3 Months</option>
                                    <option value="6" {{ $months == 6 ? 'selected' : '' }}>Last 6 Months</option>
                                    <option value="9" {{ $months == 9 ? 'selected' : '' }}>Last 9 Months</option>
                                    <option value="12" {{ $months == 12 ? 'selected' : '' }}>Last 12 Months</option>
                                </select>
                            </div>
                            <div class="col-md-9 text-end">
                                <div class="d-inline-flex gap-2">
                                    @foreach(['excellent' => 'success', 'good' => 'info', 'satisfactory' => 'warning', 'needs_improvement' => 'warning', 'unsatisfactory' => 'danger'] as $level => $color)
                                        <span class="badge bg-{{ $color }}">{{ ucfirst(str_replace('_', ' ', $level)) }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Partial -->
        @include('partials.kpi-trend-content')
    </div>
</div>

<!-- Chart.js -->
<script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>

<script>
    function changeMonths() {
        const months = document.getElementById('monthsSelect').value;
        const url = new URL(window.location.href);
        url.searchParams.set('months', months);
        window.location.href = url.toString();
    }
</script>
@endsection
