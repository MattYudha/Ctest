@extends ('layouts.dashboard')

@section ('content')
    <div class="page-heading mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div class="d-flex align-items-center order-2 order-md-1 mt-3 mt-md-0">
                <a href="{{ route('presences.index') }}" class="btn btn-secondary me-3" title="Back">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>

                <div>
                    <h3 class="mb-0">Calendar & Summary</h3>
                    <p class="text-subtitle text-muted mb-0 mt-1">Monthly attendance report and company holiday schedule.</p>
                </div>
            </div>

            <nav aria-label="breadcrumb" class="breadcrumb-header order-1 order-md-2">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('presences.index') }}">Presences</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Calendar & Summary</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="page-content">
        <section class="section">
            <div class="card shadow-sm border-0 rounded-3 mb-4">
                <div class="card-body p-4">
                    <form
                        action="{{ route('presences.calendar') }}"
                        method="GET"
                        class="row g-3 align-items-end"
                        id="filterForm"
                    >
                        @if ($isAdmin)
                            <div class="col-md-5">
                                <label class="form-label fw-semibold text-muted small">Filter Employee</label>
                                <select
                                    name="employee_id"
                                    class="form-select bg-body text-body"
                                    onchange="document.getElementById('filterForm').submit()"
                                >
                                    <option value="">-- All Employees --</option>
                                    @foreach ($employees as $emp)
                                        <option
                                            value="{{ $emp->id }}"
                                            {{ $selectedEmployeeId == $emp->id ? 'selected' : '' }}
                                        >
                                            {{ $emp->fullname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-md-{{ $isAdmin ? '3' : '6' }}">
                            <label class="form-label fw-semibold text-muted small">Month</label>
                            <select
                                name="month"
                                class="form-select bg-body text-body"
                                onchange="document.getElementById('filterForm').submit()"
                            >
                                @foreach (range(1, 12) as $m)
                                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 10)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-{{ $isAdmin ? '4' : '6' }}">
                            <label class="form-label fw-semibold text-muted small">Year</label>
                            <select
                                name="year"
                                class="form-select bg-body text-body"
                                onchange="document.getElementById('filterForm').submit()"
                            >
                                @foreach (range(date('Y') - 2, date('Y') + 2) as $y)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            @if ($selectedEmployeeId)
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="card shadow-sm border-start-0 border-end-0 border-bottom-0 border-top border-4 border-primary rounded-3 h-100">
                            <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                                <h6 class="mb-1 fw-bold text-muted" style="font-size: 0.85rem">Working Days</h6>
                                <h3 class="mb-0 fw-bold text-body">{{ $summary['working_days'] }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="card shadow-sm border-start-0 border-end-0 border-bottom-0 border-top border-4 border-success rounded-3 h-100">
                            <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                                <h6 class="mb-1 fw-bold text-muted" style="font-size: 0.85rem">Present</h6>
                                <h3 class="mb-0 fw-bold text-success">{{ $summary['present'] }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="card shadow-sm border-start-0 border-end-0 border-bottom-0 border-top border-4 border-warning rounded-3 h-100">
                            <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                                <h6 class="mb-1 fw-bold text-muted" style="font-size: 0.85rem">Late</h6>
                                <h3 class="mb-0 fw-bold text-warning">{{ $summary['late'] }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="card shadow-sm border-start-0 border-end-0 border-bottom-0 border-top border-4 border-info rounded-3 h-100">
                            <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                                <h6 class="mb-1 fw-bold text-muted" style="font-size: 0.85rem">Leave</h6>
                                <h3 class="mb-0 fw-bold text-info">{{ $summary['leave'] }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="card shadow-sm border-start-0 border-end-0 border-bottom-0 border-top border-4 border-danger rounded-3 h-100">
                            <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                                <h6 class="mb-1 fw-bold text-muted" style="font-size: 0.85rem">Absent (Alpha)</h6>
                                <h3 class="mb-0 fw-bold text-danger">{{ $summary['absent'] }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-xl-2">
                        <div class="card shadow-sm border-start-0 border-end-0 border-bottom-0 border-top border-4 border-secondary rounded-3 h-100">
                            <div class="card-body p-3 text-center d-flex flex-column justify-content-center">
                                <h6 class="mb-1 fw-bold text-muted" style="font-size: 0.85rem">WFO/WFH/WFA</h6>
                                <h5 class="mb-0 fw-bold text-body mt-1">
                                    {{ $summary['wfo'] }} / {{ $summary['wfh'] }} / {{ $summary['wfa'] }}
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row g-4">
                <div class="col-xl-9">
                    <div class="card shadow-sm border-0 rounded-3 h-100">
                        <div class="card-body p-4">
                            <div
                                class="d-flex flex-wrap gap-3 mb-4 justify-content-center justify-content-md-start small fw-medium text-body"
                            >
                                <div class="d-flex align-items-center">
                                    <span
                                        class="d-inline-block rounded-circle me-2 shadow-sm"
                                        style="width: 14px; height: 14px; background: #198754"
                                    ></span>
                                    On Time
                                </div>
                                <div class="d-flex align-items-center">
                                    <span
                                        class="d-inline-block rounded-circle me-2 shadow-sm"
                                        style="width: 14px; height: 14px; background: #ffc107"
                                    ></span>
                                    Late
                                </div>
                                <div class="d-flex align-items-center">
                                    <span
                                        class="d-inline-block rounded-circle me-2 shadow-sm"
                                        style="width: 14px; height: 14px; background: #dc3545"
                                    ></span>
                                    Absent
                                </div>
                                <div class="d-flex align-items-center">
                                    <span
                                        class="d-inline-block rounded-circle me-2 shadow-sm"
                                        style="width: 14px; height: 14px; background: #0dcaf0"
                                    ></span>
                                    Leave
                                </div>

                                <div class="vr d-none d-md-block mx-1"></div>

                                <div class="d-flex align-items-center">
                                    <span
                                        class="d-inline-block rounded-circle me-2 shadow-sm"
                                        style="width: 14px; height: 14px; background: #0d6efd"
                                    ></span>
                                    WFO
                                </div>
                                <div class="d-flex align-items-center">
                                    <span
                                        class="d-inline-block rounded-circle me-2 shadow-sm"
                                        style="width: 14px; height: 14px; background: #6c757d"
                                    ></span>
                                    WFH
                                </div>
                                <div class="d-flex align-items-center">
                                    <span
                                        class="d-inline-block rounded-circle me-2 shadow-sm"
                                        style="width: 14px; height: 14px; background: #212529"
                                    ></span>
                                    WFA
                                </div>
                            </div>

                            <div id="fullcalendar" class="mt-2"></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3">
                    <div class="card shadow-sm border-0 rounded-3 h-100 position-sticky" style="top: 2rem">
                        <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0">
                            <h6 class="fw-bold mb-0 text-danger">
                                <i class="bi bi-calendar-heart-fill me-2"></i>Public Holidays
                            </h6>
                            <p class="small text-muted mt-1">Holidays this month</p>
                        </div>
                        <div class="card-body px-3 pb-4">
                            @if (count($holidays) > 0)
                                <ul class="list-group list-group-flush">
                                    @foreach ($holidays as $holiday)
                                        <li class="list-group-item bg-transparent px-2 py-3 border-secondary-subtle">
                                            <div class="d-flex w-100 justify-content-between align-items-center mb-1">
                                                <span class="fw-bold text-danger" style="font-size: 0.9rem">
                                                    {{
                                                        \Carbon\Carbon::parse($holiday['date'])->format(
                                                            'd/m/Y',
                                                        )
                                                    }}
                                                </span>
                                                @if ($holiday['is_national_holiday'])
                                                    <span
                                                        class="badge bg-danger-subtle text-danger border border-danger-subtle"
                                                        style="font-size: 0.65rem"
                                                        >National</span
                                                    >
                                                @else
                                                    <span
                                                        class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle"
                                                        style="font-size: 0.65rem"
                                                        >Collective Leave</span
                                                    >
                                                @endif
                                            </div>
                                            <p
                                                class="mb-0 text-body fw-semibold"
                                                style="font-size: 0.85rem; line-height: 1.3"
                                            >{{ $holiday['name'] }}</p>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="text-center py-4 opacity-50">
                                    <i class="bi bi-calendar-x fs-1 text-body"></i>
                                    <p class="mt-2 small fw-medium text-body">No holidays <br />
                                    this month.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <style>
        /* use fullcalendar css variables for perfect theme switching */
        :root {
            --fc-border-color: #e2e8f0;
            --fc-today-bg-color: rgba(68, 192, 181, 0.05);
            --fc-neutral-bg-color: #f8fafc;
        }

        /* when mazer dark mode is active */
        [data-bs-theme='dark'] {
            /* solid gray color that contrasts with mazer background */
            --fc-border-color: #55556a;
            --fc-today-bg-color: rgba(255, 255, 255, 0.03);
            --fc-neutral-bg-color: #1e1e2d;
            --fc-page-bg-color: transparent;
        }

        /* force calendar elements to use the variables above with 1px solid */
        .fc-theme-standard td,
        .fc-theme-standard th,
        .fc-theme-standard .fc-scrollgrid {
            border: 1px solid var(--fc-border-color) !important;
        }

        .fc-col-header-cell {
            background-color: var(--fc-neutral-bg-color);
            padding: 10px 0 !important;
            border-bottom: 2px solid var(--fc-border-color) !important;
            text-align: center !important; /* fix: force center alignment in any mode */
        }

        /* extra fix to make day name links 100% center aligned */
        .fc-col-header-cell-cushion {
            display: inline-block;
            width: 100%;
            text-align: center !important;
        }

        .fc-event {
            border: none !important;
            border-radius: 4px;
            padding: 4px 6px;
            margin-bottom: 3px;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
        }

        /* red cell color for holidays (light mode) */
        .holiday-cell {
            background-color: rgba(220, 53, 69, 0.08) !important;
        }

        /* red cell color for holidays (dark mode) */
        [data-bs-theme='dark'] .holiday-cell {
            background-color: rgba(220, 53, 69, 0.18) !important;
        }
    </style>
@endsection

@push ('scripts')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('fullcalendar');
            var holidayDates = {!! json_encode(array_column($holidays, 'date')) !!};

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                initialDate: '{{ $year }}-{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}-01',
                firstDay: 0, // set sunday as the first day of the week
                headerToolbar: {
                    left: '',
                    center: 'title',
                    right: '',
                },
                height: 'auto',
                events: {!! json_encode($events) !!},

                // logic to color the cell if it is a holiday
                dayCellClassNames: function (arg) {
                    var d = arg.date;
                    // format yyyy-mm-dd
                    var dateString =
                        d.getFullYear() +
                        '-' +
                        String(d.getMonth() + 1).padStart(2, '0') +
                        '-' +
                        String(d.getDate()).padStart(2, '0');

                    if (holidayDates.includes(dateString)) {
                        return ['holiday-cell'];
                    }
                    return [];
                },

                eventClick: function (info) {
                    if (info.event.url) {
                        info.jsEvent.preventDefault();
                        window.location.href = info.event.url;
                    }
                },
            });

            calendar.render();
        });
    </script>
@endpush
