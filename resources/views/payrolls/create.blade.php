@extends ('layouts.dashboard')

@section ('content')
    <div class="page-heading mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div class="d-flex align-items-center order-2 order-md-1 mt-3 mt-md-0">
                <a href="{{ route('payrolls.index') }}" class="btn btn-secondary me-3" title="Kembali">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>

                <div>
                    <h3 class="mb-0">Create New Payroll</h3>
                    <p class="text-subtitle text-muted mb-0 mt-1">Calculate employee salary with complete details.</p>
                </div>
            </div>

            <nav aria-label="breadcrumb" class="breadcrumb-header order-1 order-md-2">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('payrolls.index') }}">Payrolls</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create New Payroll</li>
                </ol>
            </nav>
        </div>
    </div>
    @if ($errors->any())
        <div class="alert alert-danger shadow-sm border-0 rounded-3 mt-4">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('payrolls.store') }}" method="POST" id="payroll-form">
        @csrf

        {{-- section 1: employee info & period --}}
        <div class="card shadow-sm border-0 rounded-3 mb-4">
            <div class="card-header bg-transparent border-bottom-0 pt-4 pb-2 px-4">
                <h5 class="mb-0 fw-bold">
                    <i class="bi bi-person-badge text-primary me-2"></i> Employee & Period Information
                </h5>
            </div>
            <div class="card-body px-4 pb-4">
                <div class="row row-gap-3 align-items-end">
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label for="employee_id" class="form-label fw-semibold text-secondary"
                                >Employee <span class="text-danger">*</span></label
                            >
                            <select name="employee_id" id="employee_id" class="form-select" required>
                                <option value="">-- Select Employee --</option>
                                @foreach ($employees as $emp)
                                    <option
                                        value="{{ $emp->id }}"
                                        data-salary="{{ $emp->salary }}"
                                        {{ old('employee_id') == $emp->id ? 'selected' : '' }}
                                    >
                                        {{ $emp->fullname }} {{ $emp->emp_code ? '(' . $emp->emp_code . ')' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @php
                        // get jakarta time for default month & year
                        $now = \Carbon\Carbon::now('Asia/Jakarta');
                        $currentMonth = $now->format('n');
                        $currentYear = $now->format('Y');
                    @endphp

                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <label for="period_month" class="form-label fw-semibold text-secondary"
                                >Month <span class="text-danger">*</span></label
                            >
                            <select name="period_month" id="period_month" class="form-select" required>
                                @php
                                    $months = [
                                        'January',
                                        'February',
                                        'March',
                                        'April',
                                        'May',
                                        'June',
                                        'July',
                                        'August',
                                        'September',
                                        'October',
                                        'November',
                                        'December',
                                    ];
                                @endphp
                                @foreach ($months as $i => $m)
                                    <option
                                        value="{{ $i+1 }}"
                                        {{
                                            old('period_month', $currentMonth) == $i + 1
                                                ? 'selected'
                                                : ''
                                        }}
                                        >{{ $m }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group mb-0">
                            <label for="period_year" class="form-label fw-semibold text-secondary"
                                >Year <span class="text-danger">*</span></label
                            >
                            <select name="period_year" id="period_year" class="form-select" required>
                                @for ($y = $currentYear - 1; $y <= $currentYear + 1; $y++)
                                    <option
                                        value="{{ $y }}"
                                        {{
                                            old('period_year', $currentYear) == $y
                                                ? 'selected'
                                                : ''
                                        }}
                                        >{{ $y }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="form-group mb-0">
                            <button
                                type="button"
                                id="btn-fetch-attendance"
                                class="btn btn-outline-primary d-block w-100 fw-semibold"
                                disabled
                            >
                                <i class="bi bi-cloud-download me-1"></i> Calculate Attendance
                            </button>
                        </div>
                    </div>
                </div>

                <div id="attendance-info" class="alert alert-info border-0 shadow-sm d-none mt-3 mb-0 rounded-3">
                    <i class="bi bi-info-circle-fill me-2"></i> <span id="attendance-info-text"></span>
                </div>
            </div>
        </div>

        <div id="payroll-details" style="display: none">
            {{-- section 2: earnings --}}
            <div class="card shadow-sm border-start border-success border-4 rounded-3 mb-4">
                <div class="card-header bg-success-subtle border-bottom-0 py-3 rounded-top-4">
                    <h5 class="mb-0 text-success fw-bold"><i class="bi bi-wallet2 me-2"></i> Earnings</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row row-gap-3">
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label for="salary" class="form-label fw-semibold text-secondary"
                                    >Basic Salary <span class="text-danger">*</span></label
                                >
                                <div class="input-group">
                                    <span class="input-group-text border-end-0 text-muted">Rp</span>
                                    <input
                                        type="text"
                                        inputmode="numeric"
                                        name="salary"
                                        id="salary"
                                        class="form-control border-start-0 calc-earning format-rupiah"
                                        value="{{ old('salary', 0) }}"
                                        required
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label for="transport_allowance" class="form-label fw-semibold text-secondary"
                                    >Transport Allowance</label
                                >
                                <div class="input-group">
                                    <span class="input-group-text border-end-0 text-muted">Rp</span>
                                    <input
                                        type="text"
                                        inputmode="numeric"
                                        name="transport_allowance"
                                        id="transport_allowance"
                                        class="form-control border-start-0 calc-earning format-rupiah"
                                        value="{{ old('transport_allowance', 0) }}"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label for="meal_allowance" class="form-label fw-semibold text-secondary"
                                    >Meal Allowance</label
                                >
                                <div class="input-group">
                                    <span class="input-group-text border-end-0 text-muted">Rp</span>
                                    <input
                                        type="text"
                                        inputmode="numeric"
                                        name="meal_allowance"
                                        id="meal_allowance"
                                        class="form-control border-start-0 calc-earning format-rupiah"
                                        value="{{ old('meal_allowance', 0) }}"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label for="total_salary" class="form-label fw-semibold text-secondary"
                                    >Total Salary</label
                                >
                                <div class="input-group">
                                    <span class="input-group-text border-end-0 text-muted">Rp</span>
                                    <input
                                        type="text"
                                        inputmode="numeric"
                                        name="total_salary"
                                        id="total_salary"
                                        class="form-control border-start-0 calc-earning format-rupiah"
                                        value="{{ old('total_salary', 0) }}"
                                        readonly
                                    />
                                </div>
                                <small class="text-muted">Calculated automatically</small>
                            </div>
                        </div>
                    </div>

                    <div class="row row-gap-3 mt-3">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label for="position_allowance" class="form-label fw-semibold text-secondary"
                                    >Position Allowance</label
                                >
                                <div class="input-group">
                                    <span class="input-group-text border-end-0 text-muted">Rp</span>
                                    <input
                                        type="text"
                                        inputmode="numeric"
                                        name="position_allowance"
                                        id="position_allowance"
                                        class="form-control border-start-0 calc-earning format-rupiah"
                                        value="{{ old('position_allowance', 0) }}"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label for="overtime_hours" class="form-label fw-semibold text-secondary"
                                    >Overtime Hours</label
                                >
                                <div class="input-group">
                                    <input
                                        type="number"
                                        name="overtime_hours"
                                        id="overtime_hours"
                                        class="form-control border-end-0"
                                        value="{{ old('overtime_hours', 0) }}"
                                        min="0"
                                        step="0.5"
                                    />
                                    <span class="input-group-text text-muted">Hours</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label for="overtime_amount" class="form-label fw-semibold text-secondary"
                                    >Overtime Pay</label
                                >
                                <div class="input-group">
                                    <span class="input-group-text border-end-0 text-muted">Rp</span>
                                    <input
                                        type="text"
                                        inputmode="numeric"
                                        name="overtime_amount"
                                        id="overtime_amount"
                                        class="form-control border-start-0 calc-earning format-rupiah"
                                        value="{{ old('overtime_amount', 0) }}"
                                    />
                                </div>
                                <small class="text-muted"
                                    >Rate: Rp. {{ $config['overtime_rate_per_hour'] }} / hours</small
                                >
                            </div>
                        </div>
                    </div>

                    <hr class="text-muted my-4" />

                    <h6 class="text-success fw-bold mb-3"><i class="bi bi-cash-stack me-2"></i> Reimbursement</h6>
                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary">Approved Reimbursement</label>
                            <div class="input-group">
                                <span class="input-group-text border-end-0 text-muted">Rp</span>
                                <input
                                    type="text"
                                    inputmode="numeric"
                                    name="reimbursement"
                                    id="reimbursement"
                                    class="form-control border-start-0 calc-earning format-rupiah"
                                    value="{{ old('reimbursement', 0) }}"
                                    readonly
                                />
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#reimbursementModal">
                                    <i class="bi bi-eye"></i> View Details
                                </button>
                            </div>
                        </div>
                    </div>

                    <hr class="text-muted my-4" />

                    <h6 class="text-success fw-bold mb-3"><i class="bi bi-star-fill me-2"></i> Bonus</h6>
                    <div class="row row-gap-3">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label for="performance_bonus" class="form-label fw-semibold text-secondary"
                                    >Performance Bonus</label
                                >
                                <div class="input-group">
                                    <span class="input-group-text border-end-0 text-muted">Rp</span>
                                    <input
                                        type="text"
                                        inputmode="numeric"
                                        name="performance_bonus"
                                        id="performance_bonus"
                                        class="form-control border-start-0 calc-earning format-rupiah"
                                        value="{{ old('performance_bonus', 0) }}"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label for="attendance_bonus" class="form-label fw-semibold text-secondary"
                                    >Attendance Bonus</label
                                >
                                <div class="input-group">
                                    <span class="input-group-text border-end-0 text-muted">Rp</span>
                                    <input
                                        type="text"
                                        inputmode="numeric"
                                        name="attendance_bonus"
                                        id="attendance_bonus"
                                        class="form-control border-start-0 calc-earning format-rupiah"
                                        value="{{ old('attendance_bonus', 0) }}"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label for="other_bonus" class="form-label fw-semibold text-secondary"
                                    >Other Bonus</label
                                >
                                <div class="input-group">
                                    <span class="input-group-text border-end-0 text-muted">Rp</span>
                                    <input
                                        type="text"
                                        inputmode="numeric"
                                        name="other_bonus"
                                        id="other_bonus"
                                        class="form-control border-start-0 calc-earning format-rupiah"
                                        value="{{ old('other_bonus', 0) }}"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4 mt-3">
                        <label for="bonus_notes" class="form-label fw-semibold text-secondary">Bonus Notes</label>
                        <input
                            type="text"
                            name="bonus_notes"
                            id="bonus_notes"
                            class="form-control"
                            value="{{ old('bonus_notes') }}"
                            placeholder="Bonus details (optional)"
                        />
                    </div>

                    <div
                        class="alert bg-success-subtle border border-success border-opacity-50 mb-0 py-3 rounded-3 shadow-sm"
                    >
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-success"
                                ><i class="bi bi-calculator me-2"></i> Earnings Subtotal</span
                            >
                            <span class="fs-4 fw-bolder text-success" id="display-total-earnings">Rp 0</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- section 3: deductions --}}
            <div class="card shadow-sm border-start border-danger border-4 rounded-3 mb-4">
                <div class="card-header bg-danger-subtle border-bottom-0 py-3 rounded-top-4">
                    <h5 class="mb-0 text-danger fw-bold"><i class="bi bi-scissors me-2"></i> Deductions</h5>
                </div>
                <div class="card-body p-4">
                    <h6 class="text-danger fw-bold mb-3"><i class="bi bi-clock-history me-2"></i> Attendance</h6>
                    <div class="row row-gap-3">
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label for="working_days" class="form-label fw-semibold text-secondary"
                                    >Working Days</label
                                >
                                <input
                                    type="number"
                                    name="working_days"
                                    id="working_days"
                                    class="form-control"
                                    value="{{ old('working_days', 0) }}"
                                    min="0"
                                />
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label class="form-label fw-semibold text-secondary">Days Present</label>
                                <div class="input-group">
                                    <input
                                        type="number"
                                        name="working_days"
                                        id="days_present"
                                        class="form-control border-end-0"
                                        value="{{ old('working_days', $payroll->working_days ?? 0) }}"
                                        required
                                    />
                                    <button
                                        class="btn btn-outline-primary"
                                        type="button"
                                        id="btn_view_presence_breakdown"
                                        data-bs-toggle="modal"
                                        data-bs-target="#presenceBreakdownModal"
                                    >
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label for="late_count" class="form-label fw-semibold text-secondary">Late Count</label>
                                <div class="input-group">
                                    <input
                                        type="number"
                                        name="late_count"
                                        id="late_count"
                                        class="form-control border-end-0"
                                        value="{{ old('late_count', 0) }}"
                                        min="0"
                                    />
                                    <span class="input-group-text text-muted">Times</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label for="late_deduction" class="form-label fw-semibold text-secondary"
                                    >Late Deduction</label
                                >
                                <div class="input-group">
                                    <span class="input-group-text border-end-0 text-muted">Rp</span>
                                    <input
                                        type="text"
                                        inputmode="numeric"
                                        name="late_deduction"
                                        id="late_deduction"
                                        class="form-control border-start-0 calc-deduction format-rupiah"
                                        value="{{ old('late_deduction', 0) }}"
                                    />
                                </div>
                                <small class="text-muted">1% of Basic Salary / late</small>
                            </div>
                        </div>
                    </div>

                    <div class="row row-gap-3 mt-3">
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label class="form-label fw-semibold text-secondary">Days Absent</label>
                                <div class="input-group">
                                    <input
                                        type="number"
                                        name="absent_count"
                                        id="absent_count"
                                        class="form-control border-end-0"
                                        value="{{ old('absent_count', $payroll->absent_count ?? 0) }}"
                                        required
                                    />
                                    <button
                                        class="btn btn-outline-danger"
                                        type="button"
                                        id="btn_view_absent_breakdown"
                                        data-bs-toggle="modal"
                                        data-bs-target="#absentBreakdownModal"
                                    >
                                        <i class="bi bi-eye-fill"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label for="absent_deduction" class="form-label fw-semibold text-secondary"
                                    >Absent Deduction</label
                                >
                                <div class="input-group">
                                    <span class="input-group-text border-end-0 text-muted">Rp</span>
                                    <input
                                        type="text"
                                        inputmode="numeric"
                                        name="absent_deduction"
                                        id="absent_deduction"
                                        class="form-control border-start-0 calc-deduction format-rupiah"
                                        value="{{ old('absent_deduction', 0) }}"
                                    />
                                </div>
                                <small class="text-muted">1% of Basic Salary / absent</small>
                            </div>
                        </div>
                    </div>

                    <hr class="text-muted my-4" />

                    <h6 class="text-danger fw-bold mb-3">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> Fines / Penalties
                    </h6>
                    <div class="row row-gap-3">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label for="penalty_amount" class="form-label fw-semibold text-secondary"
                                    >Penalty Amount</label
                                >
                                <div class="input-group">
                                    <span class="input-group-text border-end-0 text-muted">Rp</span>
                                    <input
                                        type="text"
                                        inputmode="numeric"
                                        name="penalty_amount"
                                        id="penalty_amount"
                                        class="form-control border-start-0 calc-deduction format-rupiah"
                                        value="{{ old('penalty_amount', 0) }}"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group mb-0">
                                <label for="penalty_notes" class="form-label fw-semibold text-secondary"
                                    >Penalty Details</label
                                >
                                <input
                                    type="text"
                                    name="penalty_notes"
                                    id="penalty_notes"
                                    class="form-control"
                                    value="{{ old('penalty_notes') }}"
                                    placeholder="Example: SOP violation, inventory damage, etc."
                                />
                            </div>
                        </div>
                    </div>

                    <hr class="text-muted my-4" />

                    <h6 class="text-danger fw-bold mb-3"><i class="bi bi-shield-check me-2"></i> BPJS & Taxes</h6>
                    <div class="row row-gap-3">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label for="bpjs_kes" class="form-label fw-semibold text-secondary">Health BPJS</label>
                                <div class="input-group">
                                    <span class="input-group-text border-end-0 text-muted">Rp</span>
                                    <input
                                        type="text"
                                        inputmode="numeric"
                                        name="bpjs_kes"
                                        id="bpjs_kes"
                                        class="form-control border-start-0 calc-deduction format-rupiah"
                                        value="{{ old('bpjs_kes', 0) }}"
                                    />
                                </div>
                                <small class="text-muted"
                                    >{{ ($config['bpjs_kes_employee_rate'] ?? 0.01) * 100 }}% of Basic Salary</small
                                >
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label for="bpjs_tk" class="form-label fw-semibold text-secondary"
                                    >Employment BPJS</label
                                >
                                <div class="input-group">
                                    <span class="input-group-text border-end-0 text-muted">Rp</span>
                                    <input
                                        type="text"
                                        inputmode="numeric"
                                        name="bpjs_tk"
                                        id="bpjs_tk"
                                        class="form-control border-start-0 calc-deduction format-rupiah"
                                        value="{{ old('bpjs_tk', 0) }}"
                                    />
                                </div>
                                <small class="text-muted"
                                    >{{ ($config['bpjs_tk_employee_rate'] ?? 0.02) * 100 }}% of Basic Salary</small
                                >
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label for="pph21" class="form-label fw-semibold text-secondary"
                                    >Income Tax (PPh 21)</label
                                >
                                <div class="input-group">
                                    <span class="input-group-text border-end-0 text-muted">Rp</span>
                                    <input
                                        type="text"
                                        name="pph21"
                                        id="pph21"
                                        class="form-control border-start-0 calc-deduction format-rupiah"
                                        value="{{ old('pph21', 0) }}"
                                        readonly
                                    />
                                </div>
                                <small class="text-muted">Calculated automatically</small>
                            </div>
                        </div>
                    </div>

                    <hr class="text-muted my-4" />

                    <h6 class="text-danger fw-bold mb-3">
                        <i class="bi bi-dash-circle-fill me-2"></i> Other Deductions
                    </h6>
                    <div class="row row-gap-3">
                        <div class="col-md-4">
                            <div class="form-group mb-0">
                                <label for="other_deduction" class="form-label fw-semibold text-secondary"
                                    >Other Deductions</label
                                >
                                <div class="input-group">
                                    <span class="input-group-text border-end-0 text-muted">Rp</span>
                                    <input
                                        type="text"
                                        inputmode="numeric"
                                        name="other_deduction"
                                        id="other_deduction"
                                        class="form-control border-start-0 calc-deduction format-rupiah"
                                        value="{{ old('other_deduction', 0) }}"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group mb-0">
                                <label for="deduction_notes" class="form-label fw-semibold text-secondary"
                                    >Deduction Notes</label
                                >
                                <input
                                    type="text"
                                    name="deduction_notes"
                                    id="deduction_notes"
                                    class="form-control"
                                    value="{{ old('deduction_notes') }}"
                                    placeholder="Other deduction details (optional)"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="col-12 mt-4">
                        <div class="card border border-danger rounded-3 mb-0">
                            <div class="card-body py-3">
                                <div class="form-check form-switch d-flex align-items-start mb-0">
                                    <input
                                        class="form-check-input me-3 mt-1"
                                        type="checkbox"
                                        id="missedTarget"
                                        role="switch"
                                        style="width: 2.75em; height: 1.35em"
                                    />

                                    <div>
                                        <label class="form-check-label fw-bold text-danger mb-1" for="missedTarget">
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                            Employee Did Not Meet Target
                                        </label>

                                        <div class="small text-muted">
                                            When enabled, Transportation & Meal allowances will be reset to <strong>0</strong>.
                                            Days Present will equal Working Days, and Late/Absent Deductions, along with Income Tax (PPh 21) will be <strong>0</strong>.
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="alert bg-danger-subtle border border-danger border-opacity-50 mb-0 mt-4 py-3 rounded-3 shadow-sm"
                    >
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-danger"
                                ><i class="bi bi-calculator me-2"></i> Deductions Subtotal</span
                            >
                            <span class="fs-4 fw-bolder text-danger" id="display-total-deductions">Rp 0</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- section 4: summary & submit --}}
            <div class="card shadow-sm border-0 rounded-3 mb-4 bg-primary-subtle">
                <div class="card-body p-4 p-md-5">
                    <div class="text-center mb-4">
                        <h4 class="text-primary fw-bold"><i class="bi bi-receipt-cutoff me-2"></i> Salary Summary</h4>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="bg-body border p-4 rounded-3 shadow-sm h-100">
                                <h5 class="text-success fw-bold mb-3 border-bottom pb-3"><i class="bi bi-graph-up-arrow me-2"></i> Earnings</h5>
                                <table class="table table-sm table-borderless mb-0 fs-6">
                                    <tr><td class="text-secondary py-2">Base Salary</td><td class="text-end fw-semibold text-body py-2" id="summary-salary">Rp 0</td></tr>
                                    <tr><td class="text-secondary py-2">Transport Allowance</td><td class="text-end fw-semibold text-body py-2" id="summary-transport_allowance">Rp 0</td></tr>
                                    <tr><td class="text-secondary py-2">Meal Allowance</td><td class="text-end fw-semibold text-body py-2" id="summary-meal_allowance">Rp 0</td></tr>
                                    <tr><td class="text-secondary py-2">Position Allowance</td><td class="text-end fw-semibold text-body py-2" id="summary-position_allowance">Rp 0</td></tr>
                                    <tr><td class="text-secondary py-2">Overtime Amount</td><td class="text-end fw-semibold text-body py-2" id="summary-overtime_amount">Rp 0</td></tr>
                                    <tr><td class="text-secondary py-2">Performance Bonus</td><td class="text-end fw-semibold text-body py-2" id="summary-performance_bonus">Rp 0</td></tr>
                                    <tr><td class="text-secondary py-2">Attendance Bonus</td><td class="text-end fw-semibold text-body py-2" id="summary-attendance_bonus">Rp 0</td></tr>
                                    <tr><td class="text-secondary py-2">Other Bonus</td><td class="text-end fw-semibold text-body py-2" id="summary-other_bonus">Rp 0</td></tr>
                                    <tr><td class="text-secondary py-2">Reimbursement</td><td class="text-end fw-semibold text-body py-2" id="summary-reimbursement">Rp 0</td></tr>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <div class="bg-body border p-4 rounded-3 shadow-sm h-100">
                                <h5 class="text-danger fw-bold mb-3 border-bottom pb-3"><i class="bi bi-graph-down-arrow me-2"></i> Deductions</h5>
                                <table class="table table-sm table-borderless mb-0 fs-6">
                                    <tr><td class="text-secondary py-2">Late Deduction</td><td class="text-end fw-semibold text-body py-2" id="summary-late_deduction">Rp 0</td></tr>
                                    <tr><td class="text-secondary py-2">Absent Deduction</td><td class="text-end fw-semibold text-body py-2" id="summary-absent_deduction">Rp 0</td></tr>
                                    <tr><td class="text-secondary py-2">Penalty Amount</td><td class="text-end fw-semibold text-body py-2" id="summary-penalty_amount">Rp 0</td></tr>
                                    <tr><td class="text-secondary py-2">BPJS Kesehatan</td><td class="text-end fw-semibold text-body py-2" id="summary-bpjs_kes">Rp 0</td></tr>
                                    <tr><td class="text-secondary py-2">BPJS Ketenagakerjaan</td><td class="text-end fw-semibold text-body py-2" id="summary-bpjs_tk">Rp 0</td></tr>
                                    <tr><td class="text-secondary py-2">Income Tax (PPh 21)</td><td class="text-end fw-semibold text-body py-2" id="summary-pph21">Rp 0</td></tr>
                                    <tr><td class="text-secondary py-2">Other Deduction</td><td class="text-end fw-semibold text-body py-2" id="summary-other_deduction">Rp 0</td></tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row justify-content-center">
                        <div class="col-md-8">
                            <div class="bg-primary bg-opacity-10 border border-primary border-opacity-25 p-4 rounded-3 shadow-sm mb-4">
                                <table class="table table-borderless mb-0">
                                    <tr>
                                        <td class="fw-semibold fs-5 text-secondary py-2">Total Earnings</td>
                                        <td class="text-end fs-4 text-success fw-bold py-2" id="summary-earnings">Rp 0</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold fs-5 text-secondary py-2">Total Deductions</td>
                                        <td class="text-end fs-4 text-danger fw-bold py-2" id="summary-deductions">Rp 0</td>
                                    </tr>
                                    <tr class="border-top border-1 border-secondary border-opacity-25">
                                        <td class="fw-semibold fs-5 text-secondary py-2">Total After Deductions</td>
                                        <td class="text-end fs-4 text-body fw-bold py-2" id="summary-after-deductions">Rp 0</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold fs-5 text-secondary py-2">Total PPh 21</td>
                                        <td class="text-end fs-4 text-danger fw-bold py-2" id="summary-total-pph21">Rp 0</td>
                                    </tr>
                                    <tr class="border-top border-2 border-primary border-opacity-25">
                                        <td class="fw-bold fs-3 text-body pt-3">Net Salary</td>
                                        <td class="text-end fw-bolder fs-2 text-primary pt-3" id="summary-net">Rp 0</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center gap-3 mt-2">
                        <a
                            href="{{ route('payrolls.index') }}"
                            class="btn btn-secondary shadow-sm rounded-3 px-4 fw-semibold"
                            ><i class="bi bi-arrow-left me-1"></i> Back</a
                        >
                        <button type="submit" class="btn btn-primary shadow-sm rounded-3 px-5 fw-bold">
                            <i class="bi bi-save me-1"></i> Save Payroll
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    {{-- MODAL PRESENCE BREAKDOWN --}}
    <div class="modal fade" id="presenceBreakdownModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">
                <div class="modal-header border-0 px-4 py-3 bg-primary">
                    <div>
                        <h5 class="modal-title fw-bold mb-1 text-white">
                            <i class="bi bi-calendar2-check me-2"></i> Attendance Breakdown
                        </h5>
                        <small class="text-white-50">Employee attendance statistics</small>
                    </div>
                    <button
                        type="button"
                        class="btn-close btn-close-white shadow-none"
                        data-bs-dismiss="modal"
                    ></button>
                </div>

                <div class="modal-body p-4">
                    <div class="d-flex flex-column gap-3">
                        <div class="bg-body border rounded-3 p-3 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div
                                        class="rounded-3 d-flex align-items-center justify-content-center me-3 bg-primary bg-opacity-10"
                                        style="width: 58px; height: 58px"
                                    >
                                        <i class="bi bi-building fs-4 text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-body">WFO</div>
                                        <small class="text-muted">Work From Office</small>
                                    </div>
                                </div>
                                <span
                                    class="badge bg-primary bg-opacity-10 text-primary rounded-3 px-4 py-2 fs-6 fw-semibold"
                                    id="breakdown_wfo"
                                    >0 Days</span
                                >
                            </div>
                        </div>

                        <div class="bg-body border rounded-3 p-3 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div
                                        class="rounded-3 d-flex align-items-center justify-content-center me-3 bg-primary bg-opacity-10"
                                        style="width: 58px; height: 58px"
                                    >
                                        <i class="bi bi-house-door fs-4 text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-body">WFH</div>
                                        <small class="text-muted">Work From Home</small>
                                    </div>
                                </div>
                                <span
                                    class="badge bg-primary bg-opacity-10 text-primary rounded-3 px-4 py-2 fs-6 fw-semibold"
                                    id="breakdown_wfh"
                                    >0 Days</span
                                >
                            </div>
                        </div>

                        <div class="bg-body border rounded-3 p-3 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div
                                        class="rounded-3 d-flex align-items-center justify-content-center me-3 bg-primary bg-opacity-10"
                                        style="width: 58px; height: 58px"
                                    >
                                        <i class="bi bi-globe fs-4 text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-body">WFA</div>
                                        <small class="text-muted">Work From Anywhere</small>
                                    </div>
                                </div>
                                <span
                                    class="badge bg-primary bg-opacity-10 text-primary rounded-3 px-4 py-2 fs-6 fw-semibold"
                                    id="breakdown_wfa"
                                    >0 Days</span
                                >
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3 p-4 mt-4 bg-primary text-white shadow-sm border-0">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <div class="fw-bold fs-5 text-white">Total Attendance</div>
                                <small class="text-white-50">Total attendance records</small>
                            </div>
                            <div class="fw-bold display-6 mt-2 mt-md-0" id="breakdown_total">0</div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-3">
                    <button
                        type="button"
                        class="btn btn-primary rounded-3 w-100 py-2 fw-semibold shadow-sm"
                        data-bs-dismiss="modal"
                    >
                        <i class="bi bi-x-circle me-1"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    {{-- MODAL ABSENT BREAKDOWN --}}
    <div class="modal fade" id="absentBreakdownModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">
                <div class="modal-header border-0 px-4 py-3 bg-danger">
                    <div>
                        <h5 class="modal-title fw-bold mb-1 text-white">
                            <i class="bi bi-exclamation-triangle me-2"></i> Absence Breakdown
                        </h5>
                        <small class="text-white-50">Employee absence statistics</small>
                    </div>
                    <button
                        type="button"
                        class="btn-close btn-close-white shadow-none"
                        data-bs-dismiss="modal"
                    ></button>
                </div>

                <div class="modal-body p-4">
                    <div class="bg-body border rounded-3 p-3 shadow-sm mb-4">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <div class="fw-bold text-body mb-1">Employee Type</div>
                                <small class="text-muted">Employee working category</small>
                            </div>
                            <span
                                class="badge bg-danger bg-opacity-10 text-danger rounded-3 px-4 py-2 fs-6 fw-semibold text-capitalize"
                                id="lbl_working_type"
                                >-</span
                            >
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        <div class="bg-body border rounded-3 p-3 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div
                                        class="rounded-3 d-flex align-items-center justify-content-center me-3 bg-danger bg-opacity-10"
                                        style="width: 58px; height: 58px"
                                    >
                                        <i class="bi bi-x-circle fs-4 text-danger"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-body">Full Absence</div>
                                        <small class="text-muted">No attendance activity</small>
                                    </div>
                                </div>
                                <span
                                    class="badge bg-danger bg-opacity-10 text-danger rounded-3 px-4 py-2 fs-6 fw-semibold"
                                    id="breakdown_absent_murni"
                                    >0 Days</span
                                >
                            </div>
                        </div>

                        <div class="bg-body border rounded-3 p-3 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div
                                        class="rounded-3 d-flex align-items-center justify-content-center me-3 bg-danger bg-opacity-10"
                                        style="width: 58px; height: 58px"
                                    >
                                        <i class="bi bi-building-x fs-4 text-danger"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-body">WFO Quota</div>
                                        <small class="text-muted">Required WFO quota not fulfilled</small>
                                    </div>
                                </div>
                                <span
                                    class="badge bg-danger bg-opacity-10 text-danger rounded-3 px-4 py-2 fs-6 fw-semibold"
                                    id="breakdown_absent_wfo"
                                    >0 Days</span
                                >
                            </div>
                        </div>
                    </div>

                    <div class="rounded-3 p-4 mt-4 bg-danger text-white shadow-sm border-0">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <div class="fw-bold fs-5 text-white">Total Absence</div>
                                <small class="text-white-50">Total counted as absent</small>
                            </div>
                            <div class="fw-bold display-6 mt-2 mt-md-0" id="breakdown_absent_total">0</div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-3">
                    <button
                        type="button"
                        class="btn btn-danger rounded-3 w-100 py-2 fw-semibold shadow-sm"
                        data-bs-dismiss="modal"
                    >
                        <i class="bi bi-x-circle me-1"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL REIMBURSEMENT --}}
    <div class="modal fade" id="reimbursementModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">
                <div class="modal-header border-0 px-4 py-3 bg-primary">
                    <div>
                        <h5 class="modal-title fw-bold mb-1 text-white">
                            <i class="bi bi-cash-stack me-2"></i> Reimbursement Details
                        </h5>
                        <small class="text-white-50">Approved reimbursements for this period</small>
                    </div>
                    <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="reimbursement-list">
                    <p class="text-muted text-center mb-0">Please fetch attendance data first.</p>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-3">
                    <button type="button" class="btn btn-primary rounded-3 w-100 py-2 fw-semibold shadow-sm" data-bs-dismiss="modal">
                        <i class="bi bi-check-circle me-1"></i> Apply & Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    @push ('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const empSelect = document.getElementById('employee_id');
                const monthSelect = document.getElementById('period_month');
                const yearSelect = document.getElementById('period_year');
                const btnFetch = document.getElementById('btn-fetch-attendance');
                const payrollDetails = document.getElementById('payroll-details');
                const overtimeHoursEl = document.getElementById('overtime_hours');

                // helper: format number to rupiah string (example: 1000000 -> 1.000.000)
                window.formatRibuan = function formatRibuan(angka) {
                    // keep empty value if input is cleared
                    if (angka === '' || angka === null || angka === undefined) {
                        return '';
                    }

                    // remove all non numeric characters
                    let number_string = angka.toString().replace(/[^,\d]/g, '');

                    if (number_string === '') {
                        return '';
                    }

                    // remove leading zero
                    number_string = parseInt(number_string, 10).toString();

                    let split = number_string.split(','),
                        sisa = split[0].length % 3,
                        rupiah = split[0].substr(0, sisa),
                        ribuan = split[0].substr(sisa).match(/\d{3}/gi);

                    if (ribuan) {
                        let separator = sisa ? '.' : '';
                        rupiah += separator + ribuan.join('.');
                    }

                    return rupiah;
                }

                // helper: convert rupiah string back to raw number
                function parseRupiah(text) {
                    if (!text) {
                        return 0;
                    }

                    let parsed = parseFloat(text.toString().replace(/\./g, ''));

                    return isNaN(parsed) ? 0 : parsed;
                }

                // format input on typing
                document.querySelectorAll('.format-rupiah').forEach((el) => {
                    el.addEventListener('input', function (e) {
                        // store current cursor position and text length
                        let cursorPosition = this.selectionStart;
                        let originalLength = this.value.length;

                        // format value with thousand separator
                        this.value = formatRibuan(this.value);

                        // adjust cursor position after formatting
                        let newLength = this.value.length;
                        cursorPosition = cursorPosition + (newLength - originalLength);

                        // restore cursor position
                        this.setSelectionRange(cursorPosition, cursorPosition);

                        // recalculate payroll summary
                        recalculate();
                    });

                    // clear input if value is only zero
                    el.addEventListener('focus', function (e) {
                        if (this.value === '0') {
                            this.value = '';
                        }
                    });

                    // restore zero if input is left empty
                    el.addEventListener('blur', function (e) {
                        if (this.value === '') {
                            this.value = '0';
                            recalculate();
                        }
                    });
                });

                // event: remove formatting right before form submission so laravel gets raw numbers
                document.getElementById('payroll-form').addEventListener('submit', function (e) {
                    document.querySelectorAll('.format-rupiah').forEach((el) => {
                        el.value = parseRupiah(el.value);
                    });
                });

                // auto check and fetch payroll data
                function checkAndFetchData() {
                    const empId = empSelect.value;
                    const month = monthSelect.value;
                    const year = yearSelect.value;

                    if (empId && month && year) {
                        payrollDetails.style.display = 'block';

                        const salary = empSelect.options[empSelect.selectedIndex].dataset.salary || 0;
                        // set and format base salary
                        document.getElementById('salary').value = formatRibuan(Math.round(parseFloat(salary)));

                        fetchAttendanceData(empId, month, year);
                    } else {
                        payrollDetails.style.display = 'none';
                    }
                }

                empSelect.addEventListener('change', checkAndFetchData);
                monthSelect.addEventListener('change', checkAndFetchData);
                yearSelect.addEventListener('change', checkAndFetchData);

                // main fetch function
                function fetchAttendanceData(empId, month, year) {
                    btnFetch.disabled = true;
                    btnFetch.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Auto Calculating...';

                    fetch(`{{ route('payrolls.attendance-data') }}?employee_id=${empId}&month=${month}&year=${year}`)
                        .then((r) => r.json())
                        .then((res) => {
                            if (res.success) {
                                const missedTarget = document.getElementById('missedTarget');
                                if (missedTarget && missedTarget.checked) {
                                    missedTarget.checked = false;
                                    document.querySelectorAll('.target-notice').forEach(el => el.remove());
                                }

                                const d = res.data;
                                document.getElementById('salary').value = formatRibuan(Math.round(d.base_salary));

                                document.getElementById('transport_allowance').value = formatRibuan(
                                    Math.round(d.transport_allowance || 0),
                                );
                                document.getElementById('meal_allowance').value = formatRibuan(Math.round(d.meal_allowance || 0));
                                document.getElementById('position_allowance').value = formatRibuan(
                                    Math.round(d.position_allowance || 0),
                                );
                                document.getElementById('total_salary').value = formatRibuan(Math.round(d.total_salary || 0));

                                document.getElementById('working_days').value = d.working_days;
                                document.getElementById('days_present').value = d.days_present;

                                document.getElementById('breakdown_wfo').innerText = (d.wfo_count || 0) + ' Days';
                                document.getElementById('breakdown_wfh').innerText = (d.wfh_count || 0) + ' Days';
                                document.getElementById('breakdown_wfa').innerText = (d.wfa_count || 0) + ' Days';
                                document.getElementById('breakdown_total').innerText = (d.days_present || 0) + ' Days';

                                document.getElementById('lbl_working_type').innerText = d.employee_working_type
                                    ? d.employee_working_type.replace('_', ' ')
                                    : '-';
                                document.getElementById('breakdown_absent_murni').innerText = (d.absent_murni || 0) + ' Days';
                                document.getElementById('breakdown_absent_wfo').innerText = (d.absent_wfo_deficit || 0) + ' Days';
                                document.getElementById('breakdown_absent_total').innerText = (d.absent_count || 0) + ' Days';

                                window.currentPph21Rate = d.pph21_rate || 0.5;

                                document.getElementById('late_count').value = d.late_count;
                                document.getElementById('late_deduction').value = formatRibuan(d.late_deduction);
                                document.getElementById('absent_count').value = d.absent_count;
                                document.getElementById('absent_deduction').value = formatRibuan(Math.round(d.absent_deduction));

                                document.getElementById('overtime_hours').value = d.overtime_hours || 0;
                                document.getElementById('overtime_amount').value = formatRibuan(d.overtime_amount || 0);

                                document.getElementById('reimbursement').value = formatRibuan(Math.round(d.reimbursement || 0));

                                // Build reimbursement modal list
                                let reimburseHtml = '';
                                if (d.reimbursement_details && d.reimbursement_details.length > 0) {
                                    d.reimbursement_details.forEach(item => {
                                        const dateStr = new Date(item.created_at).toLocaleDateString();
                                        reimburseHtml += `
                                        <div class="form-check d-flex justify-content-between align-items-center mb-3 p-3 border rounded-3 bg-light">
                                            <div>
                                                <input class="form-check-input reimburse-checkbox ms-0 me-3" type="checkbox" value="${item.amount}" id="reimb_${item.id}" checked onchange="updateReimbursementTotal()">
                                                <label class="form-check-label fw-bold" for="reimb_${item.id}">
                                                    ${item.title}
                                                    <div class="text-muted fw-normal small mt-1"><i class="bi bi-calendar me-1"></i> ${dateStr}</div>
                                                </label>
                                            </div>
                                            <span class="fw-bold text-success fs-5">Rp ${formatRibuan(Math.round(item.amount))}</span>
                                        </div>
                                        `;
                                    });
                                } else {
                                    reimburseHtml = '<p class="text-muted text-center mb-0">No approved reimbursements for this period.</p>';
                                }
                                document.getElementById('reimbursement-list').innerHTML = reimburseHtml;

                                const infoEl = document.getElementById('attendance-info');
                                const infoText = document.getElementById('attendance-info-text');
                                infoEl.classList.remove('d-none');
                                infoText.textContent = `Working days: ${d.working_days} | Present: ${d.days_present} | Late: ${d.late_count} | Absent: ${d.absent_count} | Leave: ${d.leave_count}`;

                                recalculate();
                            }
                        })
                        .catch((err) => {
                            console.error("Fetch Attendance Error:", err);
                            if (typeof Swal !== 'undefined') {
                                Swal.fire('Error', 'Failed to fetch system data: ' + err.message, 'error');
                            } else {
                                alert('Failed to fetch system data.');
                            }
                        })
                        .finally(() => {
                            btnFetch.disabled = false;
                            btnFetch.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i> Manual Recalculate';
                        });
                }

                btnFetch.addEventListener('click', function () {
                    if (empSelect.value && monthSelect.value && yearSelect.value) {
                        fetchAttendanceData(empSelect.value, monthSelect.value, yearSelect.value);
                    }
                });

                // auto calculate overtime amount
                overtimeHoursEl.addEventListener('input', function () {
                    const salary = parseRupiah(document.getElementById('salary').value);
                    const multiplier = {{ $config['overtime_rate_per_hour'] }};
                    const totalHours = document.getElementById('overtime_hours').value;

                    document.getElementById('overtime_amount').value = formatRibuan(Math.round(totalHours * multiplier));
                    recalculate();
                });

                // auto calculate late deduction
                document.getElementById('late_count').addEventListener('input', function () {
                    const totalSalary = parseRupiah(document.getElementById('total_salary').value);
                    const count = parseFloat(this.value) || 0;
                    document.getElementById('late_deduction').value = formatRibuan(Math.round(count * (totalSalary * 0.01)));
                    recalculate();
                });

                // auto calculate absent deduction
                document.getElementById('absent_count').addEventListener('input', function () {
                    const totalSalary = parseRupiah(document.getElementById('total_salary').value);
                    const count = parseFloat(this.value) || 0;
                    document.getElementById('absent_deduction').value = formatRibuan(Math.round(count * (totalSalary * 0.01)));
                    recalculate();
                });

                // core recalculate logic
                function recalculate() {
                    const v = (id) => parseRupiah(document.getElementById(id).value);
                    const fmt = (n) => 'Rp ' + Math.round(n).toLocaleString('id-ID');

                    const earningsFields = ['salary', 'transport_allowance', 'meal_allowance', 'position_allowance', 'overtime_amount', 'performance_bonus', 'attendance_bonus', 'other_bonus', 'reimbursement'];
                    const deductionsFields = ['late_deduction', 'absent_deduction', 'penalty_amount', 'bpjs_kes', 'bpjs_tk', 'pph21', 'other_deduction'];

                    let totalEarnings = 0;
                    earningsFields.forEach(f => {
                        let val = v(f);
                        totalEarnings += val;
                        let el = document.getElementById('summary-' + f);
                        if (el) {
                            el.textContent = fmt(val);
                            el.closest('tr').style.display = val === 0 ? 'none' : '';
                        }
                    });

                    let totalDeductionsExceptPph = 0;
                    deductionsFields.forEach(f => {
                        if (f !== 'pph21') {
                            let val = v(f);
                            totalDeductionsExceptPph += val;
                            let el = document.getElementById('summary-' + f);
                            if (el) {
                                el.textContent = fmt(val);
                                el.closest('tr').style.display = val === 0 ? 'none' : '';
                            }
                        }
                    });

                    // Compute PPh21
                    const netBeforePph = totalEarnings - totalDeductionsExceptPph;
                    let pph21Amount = 0;
                    let pphZeroReason = '';
                    const missedTargetEl = document.getElementById('missedTarget');
                    
                    if (netBeforePph >= 4500000) {
                        if (!missedTargetEl || !missedTargetEl.checked) {
                            const pphRate = window.currentPph21Rate || 0.5;
                            pph21Amount = netBeforePph * (pphRate / 100);
                        } else {
                            pphZeroReason = 'Waived';
                        }
                    } else {
                        pphZeroReason = 'Not Eligible';
                    }
                    
                    const pph21Input = document.getElementById('pph21');
                    if (pph21Input) {
                        pph21Input.value = formatRibuan(Math.round(pph21Amount));
                    }
                    
                    let elPph = document.getElementById('summary-pph21');
                    if (elPph) {
                        elPph.textContent = fmt(pph21Amount);
                        elPph.closest('tr').style.display = pph21Amount === 0 ? 'none' : '';
                    }

                    const totalDeductions = totalDeductionsExceptPph + pph21Amount;
                    const net = totalEarnings - totalDeductions;

                    const computedTotalSalary = v('salary') + v('transport_allowance') + v('meal_allowance') + v('position_allowance');
                    const totalSalaryInput = document.getElementById('total_salary');
                    if (totalSalaryInput) {
                        totalSalaryInput.value = formatRibuan(Math.round(computedTotalSalary));
                    }

                    document.getElementById('display-total-earnings').textContent = fmt(totalEarnings);
                    document.getElementById('display-total-deductions').textContent = fmt(totalDeductions);
                    document.getElementById('summary-earnings').textContent = fmt(totalEarnings);
                    document.getElementById('summary-deductions').textContent = fmt(totalDeductionsExceptPph);
                    
                    const elAfterDed = document.getElementById('summary-after-deductions');
                    if (elAfterDed) elAfterDed.textContent = fmt(netBeforePph);
                    
                    const elTotalPph = document.getElementById('summary-total-pph21');
                    if (elTotalPph) {
                        if (pph21Amount === 0) {
                            elTotalPph.innerHTML = `<span class="badge bg-secondary bg-opacity-25 text-secondary border border-secondary border-opacity-50 fw-normal py-1 px-2 fs-6">${pphZeroReason}</span>`;
                        } else {
                            elTotalPph.textContent = fmt(pph21Amount);
                        }
                    }

                    document.getElementById('summary-net').textContent = fmt(net);
                    document.getElementById('summary-net').className =
                        'text-end fw-bolder fs-2 pt-3 ' + (net >= 0 ? 'text-primary' : 'text-danger');
                }
                window.recalculate = recalculate;

                if (empSelect.value) {
                    checkAndFetchData();
                }
            });

            document.addEventListener('DOMContentLoaded', function () {
                window.currentPph21Rate = 0.5;

                const targetFields = [
                    // Earnings
                    'transport_allowance',
                    'meal_allowance',
                    'position_allowance',
                    'overtime_amount',
                    'performance_bonus',
                    'attendance_bonus',
                    'other_bonus',
                    'reimbursement',

                    // Deductions
                    'late_deduction',
                    'absent_deduction',
                    'absent_count',
                    'penalty_amount',
                    'bpjs_kes',
                    'bpjs_tk',
                    'pph21',
                    'other_deduction',
                ];

                const missedTarget = document.getElementById('missedTarget');

                const missedTargetZeroFields = [
                    'transport_allowance',
                    'meal_allowance',
                    'late_count',
                    'late_deduction',
                    'absent_count',
                    'absent_deduction',
                    'pph21'
                ];

                missedTarget.addEventListener('change', function () {
                    const isChecked = this.checked;

                    if (isChecked) {
                        Swal.fire({
                            title: 'Apply Penalty?',
                            text: 'Transport & Meal allowances will be cleared. Absent/Late deductions and Income Tax (PPh 21) will be reset to 0.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#dc3545',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Yes, Apply!',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                missedTargetZeroFields.forEach((field) => {
                                    const el = document.querySelector(`input[name="${field}"]`);

                                    if (el) {
                                        el.dataset.oldVal = el.value;
                                        el.value = 0;
                                        el.readOnly = true;
                                    }
                                });

                                const daysPresentEl = document.getElementById('days_present');
                                const workingDaysEl = document.getElementById('working_days');
                                if (daysPresentEl && workingDaysEl) {
                                    daysPresentEl.dataset.oldVal = daysPresentEl.value;
                                    daysPresentEl.value = workingDaysEl.value;
                                    daysPresentEl.readOnly = true;
                                }

                                const targetNoticeHtml = '<div class="alert alert-warning target-notice mb-3"><i class="bi bi-exclamation-triangle-fill me-2"></i> Employee Did Not Meet Target</div>';
                                const presenceModalBody = document.querySelector('#presenceBreakdownModal .modal-body');
                                const absentModalBody = document.querySelector('#absentBreakdownModal .modal-body');
                                if (presenceModalBody && !presenceModalBody.querySelector('.target-notice')) presenceModalBody.insertAdjacentHTML('afterbegin', targetNoticeHtml);
                                if (absentModalBody && !absentModalBody.querySelector('.target-notice')) absentModalBody.insertAdjacentHTML('afterbegin', targetNoticeHtml);

                                const salaryInput = document.querySelector('input[name="salary"]');

                                if (salaryInput) {
                                    salaryInput.dispatchEvent(new Event('input', { bubbles: true }));
                                }

                                Swal.fire('Applied!', 'The values have been adjusted successfully.', 'success');
                            } else {
                                missedTarget.checked = false;
                            }
                        });
                    } else {
                        missedTargetZeroFields.forEach((field) => {
                            const el = document.querySelector(`input[name="${field}"]`);

                            if (el) {
                                if (el.dataset.oldVal !== undefined) {
                                    el.value = el.dataset.oldVal;
                                }

                                el.readOnly = false;
                            }
                        });

                        const daysPresentEl = document.getElementById('days_present');
                        if (daysPresentEl && daysPresentEl.dataset.oldVal !== undefined) {
                            daysPresentEl.value = daysPresentEl.dataset.oldVal;
                            daysPresentEl.readOnly = false;
                        }

                        document.querySelectorAll('.target-notice').forEach(el => el.remove());

                        const salaryInput = document.querySelector('input[name="salary"]');

                        if (salaryInput) {
                            salaryInput.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    }
                });
                // Update reimbursement total based on checkboxes
                window.updateReimbursementTotal = function() {
                    let total = 0;
                    document.querySelectorAll('.reimburse-checkbox:checked').forEach(cb => {
                        total += parseFloat(cb.value);
                    });
                    document.getElementById('reimbursement').value = formatRibuan(Math.round(total));
                    recalculate();
                };
            });
        </script>
    @endpush
@endsection
