@extends('layouts.dashboard')

@section('content')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="mb-0">Dashboard</h3>

    {{-- PROFILE DROPDOWN --}}
    <div class="dropdown">
        <a href="#" data-bs-toggle="dropdown" aria-expanded="false"
           class="d-flex align-items-center gap-2 text-decoration-none">
            <div style="
                width:40px; height:40px; border-radius:50%;
                background: linear-gradient(135deg, #0d6efd, #6610f2);
                display:flex; align-items:center; justify-content:center;
                color:#fff; font-weight:700; font-size:0.95rem;
                box-shadow: 0 2px 8px rgba(13,110,253,0.35);
                flex-shrink:0;
            ">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}
            </div>
        </a>

        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 p-0 overflow-hidden"
            style="min-width:240px; border-radius:14px;">

            {{-- User Info Header --}}
            <li class="px-3 pt-3 pb-2" style="background: linear-gradient(135deg, #0d6efd11, #6610f211);">
                <div class="d-flex align-items-center gap-3">
                    <div style="
                        width:44px; height:44px; border-radius:50%;
                        background: linear-gradient(135deg, #0d6efd, #6610f2);
                        display:flex; align-items:center; justify-content:center;
                        color:#fff; font-weight:700; font-size:1rem; flex-shrink:0;
                    ">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 2)) }}</div>
                    <div class="overflow-hidden">
                        <div class="fw-semibold text-truncate" style="font-size:0.9rem; max-width:150px;">
                            {{ auth()->user()->name ?? 'User' }}
                        </div>
                        <div class="text-muted text-truncate" style="font-size:0.75rem; max-width:150px;">
                            {{ session('role') ?? 'Employee' }}
                        </div>
                    </div>
                </div>
            </li>

            <li><hr class="dropdown-divider m-0"></li>

            {{-- DARK MODE --}}
            <li class="px-3 py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-moon-fill" style="color:#6610f2;"></i>
                        <span style="font-size:0.875rem;">Dark Mode</span>
                    </div>
                    <input class="form-check-input m-0" type="checkbox" id="darkToggle">
                </div>
            </li>

            <li><hr class="dropdown-divider m-0"></li>

            {{-- PROFILE --}}
            <li>
                <a class="dropdown-item d-flex align-items-center gap-2 py-2" href="{{ url('/my-profile') }}">
                    <i class="bi bi-person-circle" style="color:#0d6efd;"></i>
                    <span style="font-size:0.875rem;">My Profile</span>
                </a>
            </li>

            {{-- LOGOUT --}}
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger">
                        <i class="bi bi-box-arrow-right"></i>
                        <span style="font-size:0.875rem;">Logout</span>
                    </button>
                </form>
            </li>
        </ul>
    </div>
</div>

{{-- ================= PAGE CONTENT ================= --}}
<div class="page-content">
@include('partials.dashboard-content')
@include('partials.kpi-trend-content')

{{-- ================= SCRIPT ================= --}}
<script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const toggle = document.getElementById("darkToggle");
    const html = document.documentElement;

    function applyTheme(theme){
        html.setAttribute("data-bs-theme", theme);
        toggle.checked = theme === "dark";
    }

    applyTheme(localStorage.getItem("theme") || "light");

    toggle.addEventListener("change", () => {
        const theme = toggle.checked ? "dark" : "light";
        localStorage.setItem("theme", theme);
        applyTheme(theme);
    });
});
</script>



@include('components.employee-of-the-month-modal')

@endsection
