@extends ('layouts.dashboard')

@push('styles')
<style>
.color-navy { color: #1b2a4a; }

.fc-icon-box {
    width: 64px;
    height: 64px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    color: var(--icon-color);
    background: rgba(var(--bs-body-bg-rgb), 1);
    position: relative;
    margin-left: auto;
    margin-right: auto;
    box-shadow: 
        0 10px 20px -10px var(--icon-color),
        0 0 0 1px rgba(0,0,0,0.05),
        inset 0 -4px 8px rgba(0,0,0,0.02);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.fc-icon-box::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 10px;
    background: var(--icon-color);
    opacity: 0.08;
    transition: opacity 0.3s;
}

.card:hover .fc-icon-box {
    transform: translateY(-5px);
    box-shadow: 
        0 15px 30px -12px var(--icon-color),
        0 0 0 1px rgba(0,0,0,0.05);
}

.card:hover .fc-icon-box::before {
    opacity: 0.15;
}

[data-bs-theme='dark'] .fc-icon-box {
    background: #1e1e2d;
    box-shadow: 
        0 10px 25px -10px var(--icon-color),
        0 0 0 1px rgba(255,255,255,0.05);
}

.tracking-wider { letter-spacing: 0.05em; }
.fc-icon-box i {
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
    width: 100%;
    height: 100%;
}
.user-filter-select {
    width: 100%;
}
@media (min-width: 768px) {
    .user-filter-select {
        width: 250px;
    }
}
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
    <div>
        <h3 class="mb-0">CRM Dashboard</h3>
        <nav aria-label="breadcrumb" class="d-none d-md-block mt-2">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">CRM Overview</li>
            </ol>
        </nav>
    </div>
    
    @if(!$isSales && count($salesUsers) > 0)
    <div class="mt-3 mt-md-0 user-filter-select">
        <select class="form-select shadow-sm rounded-2 fw-bold" onchange="window.location.href='?user_id=' + this.value">
            <option value="">All Sales Users</option>
            @foreach($salesUsers as $sUser)
                <option value="{{ $sUser->id }}" {{ $filterUserId == $sUser->id ? 'selected' : '' }}>
                    {{ $sUser->name }}
                </option>
            @endforeach
        </select>
    </div>
    @endif
</div>

<div class="page-content">
    <div class="row g-3 mb-4">
        @foreach ([
            ['Total Contacts', $totalContacts ?? 0, 'bi-people-fill', '#8b5cf6', ''],
            ['Total Deals/Tasks', $totalDeals ?? 0, 'bi-kanban', '#0ea5e9', ''],
            ['Pipeline Value', $totalValue ?? 0, 'bi-cash-stack', '#10b981', 'Rp ']
        ] as [$title, $val, $icon, $color, $prefix])
        <div class="col-12 col-md-4">
            <div class="card shadow-sm h-100 border-0" style="border-radius: 12px; overflow: hidden;">
                <div class="card-body p-4 d-flex flex-column text-center" style="align-items: center; justify-content: center;">
                    <div style="width: 100%; display: flex; justify-content: center; margin-bottom: 12px;">
                        <div class="fc-icon-box" style="--icon-color: {{ $color }};">
                            <i class="bi {{ $icon }}"></i>
                        </div>
                    </div>
                    <h6 class="text-muted mb-1 fw-semibold text-uppercase tracking-wider" style="font-size: 0.65rem;">{{ $title }}</h6>
                    <h3 class="fw-bold mb-0 fs-3 color-navy">
                        {{ $prefix }}{{ is_numeric($val) && $prefix === 'Rp ' ? number_format($val, 0, ',', '.') : $val }}
                    </h3>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="row g-3 mb-4">
        @foreach ([
            ['Won Deals', $dealsByStatus['won'] ?? 0, 'bi-check-circle', '#ef4444', ''],
            ['Closing Value', $wonValue ?? 0, 'bi-trophy-fill', '#f59e0b', 'Rp ']
        ] as [$title, $val, $icon, $color, $prefix])
        <div class="col-12 col-md-6">
            <div class="card shadow-sm h-100 border-0" style="border-radius: 12px; overflow: hidden;">
                <div class="card-body p-4 d-flex flex-column text-center" style="align-items: center; justify-content: center;">
                    <div style="width: 100%; display: flex; justify-content: center; margin-bottom: 12px;">
                        <div class="fc-icon-box" style="--icon-color: {{ $color }};">
                            <i class="bi {{ $icon }}"></i>
                        </div>
                    </div>
                    <h6 class="text-muted mb-1 fw-semibold text-uppercase tracking-wider" style="font-size: 0.65rem;">{{ $title }}</h6>
                    <h3 class="fw-bold mb-0 fs-3 color-navy">
                        {{ $prefix }}{{ is_numeric($val) && $prefix === 'Rp ' ? number_format($val, 0, ',', '.') : $val }}
                    </h3>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <h6 class="fw-bold mb-0 color-navy opacity-75 text-uppercase" style="font-size: 0.75rem;">Pipeline Stages Overview</h6>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-borderless mb-0">
                            <thead>
                                <tr>
                                    <th class="text-muted text-uppercase" style="font-size: 0.8rem;">Stage</th>
                                    <th class="text-muted text-uppercase" style="font-size: 0.8rem;">Count</th>
                                    <th class="text-muted text-uppercase" style="font-size: 0.8rem; min-width: 150px;">Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $stages = [
                                        'lead' => ['label' => 'Lead', 'color' => 'secondary'],
                                        'contacted' => ['label' => 'Contacted', 'color' => 'info'],
                                        'proposal' => ['label' => 'Proposal', 'color' => 'warning'],
                                        'won' => ['label' => 'Won', 'color' => 'success'],
                                        'lost' => ['label' => 'Lost', 'color' => 'danger']
                                    ];
                                @endphp
                                @foreach($stages as $key => $stage)
                                    @php 
                                        $count = $dealsByStatus[$key] ?? 0;
                                        $percentage = $totalDeals > 0 ? ($count / $totalDeals) * 100 : 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="bg-{{ $stage['color'] }} rounded-circle me-2" style="width: 10px; height: 10px;"></div>
                                                <span class="fw-bold">{{ $stage['label'] }}</span>
                                            </div>
                                        </td>
                                        <td class="fw-bold">{{ $count }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress progress-sm flex-grow-1 me-2" style="height: 6px;">
                                                    <div class="progress-bar bg-{{ $stage['color'] }}" style="width: {{ $percentage }}%" role="progressbar"></div>
                                                </div>
                                                <span class="small fw-bold text-muted">{{ round($percentage) }}%</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                <div class="card-header bg-transparent border-0 pt-4 px-4 pb-0">
                    <h6 class="fw-bold mb-0 color-navy opacity-75 text-uppercase" style="font-size: 0.75rem;">Recent Tasks / Deals</h6>
                </div>
                <div class="card-body p-4 d-flex flex-column">
                    <div style="max-height: 320px; overflow-y: auto; padding-right: 5px;" class="custom-scroll">
                        <ul class="list-group list-group-flush">
                            @forelse($recentDeals as $deal)
                                @php
                                    $stageColor = $stages[$deal->status]['color'] ?? 'primary';
                                    $stageLabel = $stages[$deal->status]['label'] ?? ucfirst($deal->status);
                                @endphp
                                <li class="list-group-item bg-transparent d-flex justify-content-between align-items-center px-0" style="border-bottom: 1px solid var(--bs-border-color);">
                                    <div>
                                        <h6 class="mb-1 fw-bold">{{ $deal->title }}</h6>
                                        <div class="text-muted small d-flex align-items-center">
                                            <i class="bi bi-building me-1"></i>
                                            <span>{{ $deal->contact ? $deal->contact->company_name : 'No Contact' }}</span>
                                        </div>
                                        @if(!$isSales && !$filterUserId && $deal->creator)
                                        <div class="text-muted small mt-1 d-flex align-items-center" style="font-size: 0.75rem;">
                                            <div class="avatar avatar-sm bg-light-primary me-2" style="width: 20px; height: 20px;">
                                                <span class="avatar-content text-primary fw-bold" style="font-size: 10px;">
                                                    {{ strtoupper(substr($deal->creator->name, 0, 1)) }}
                                                </span>
                                            </div>
                                            <span class="fw-bold">{{ $deal->creator->name }}</span>
                                        </div>
                                        @endif
                                    </div>
                                    <span class="badge bg-{{ $stageColor }} rounded-pill px-3">{{ $stageLabel }}</span>
                                </li>
                            @empty
                                <li class="list-group-item bg-transparent text-center text-muted px-0 border-0 py-4">
                                    <div class="mb-2"><i class="bi bi-inbox fs-2 opacity-50"></i></div>
                                    No recent activities found.
                                </li>
                            @endforelse
                        </ul>
                    </div>
                    <div class="mt-auto text-center pt-4">
                        <a href="{{ route('crm.board.index') }}" class="btn btn-primary w-100 shadow-sm fw-bold">
                            Open Pipeline Board <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
