@extends ('layouts.dashboard')

@push ('styles')
    <style>
        .nav-tabs .nav-link {
            border: none;
            color: #6c757d;
            padding: 1rem 1.5rem;
            font-weight: 500;
            transition: all 0.2s ease;
            border-bottom: 2px solid transparent;
        }
        .nav-tabs .nav-link:hover {
            color: #435ebe;
            background: rgba(67, 94, 190, 0.05);
            border-color: transparent;
        }
        .nav-tabs .nav-link.active {
            color: #435ebe;
            background: transparent;
            border-bottom: 2px solid #435ebe;
        }
        .tab-pane {
            padding: 1rem 0.5rem;
        }
        .bg-primary-light {
            background-color: rgba(67, 94, 190, 0.1);
        }
        .bg-success-light {
            background-color: rgba(25, 135, 84, 0.1);
        }
        .bg-info-light {
            background-color: rgba(13, 202, 240, 0.1);
        }
        .bg-danger-light {
            background-color: rgba(220, 53, 69, 0.1);
        }

        .custom-switch .form-check-input {
            width: 3em;
            height: 1.5em;
            cursor: pointer;
        }
        .word-break-all {
            word-break: break-all;
        }

        .table thead th {
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.025em;
            font-weight: 700;
            color: #4b5563;
        }
    </style>
@endpush

@section ('content')
    @php
        $role = session('role');
        $isAdmin = \App\Constants\Roles::isAdmin($role);
        // Everyone can edit their own personal data sections (Family, Bank, Documents, etc.)
        $canEditFamily = true;
        $canDeleteFamily = true;
        $canEditEducation = true;
    @endphp
    <div class="page-heading">
        <div class="page-title mb-4">
            <div class="row">
                <div class="col-md-6">
                    <h3>Edit Employee</h3>
                    <p class="text-subtitle text-muted">Update employee data</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <nav aria-label="breadcrumb" class="breadcrumb-header">
                        <ol class="breadcrumb justify-content-md-end">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('employees.index') }}">Employees</a>
                            </li>
                            <li class="breadcrumb-item active">Edit</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <section class="section">
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form action="{{ route('employees.update', $employee->id) }}" method="POST" enctype="multipart/form-data" novalidate>
                                @csrf
                                @method ('PUT')

                                <!-- Tabs Navigation -->
                                <ul class="nav nav-tabs mb-4" id="employeeEditTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button
                                            class="nav-link active"
                                            id="general-tab"
                                            data-bs-toggle="tab"
                                            data-bs-target="#general"
                                            type="button"
                                            role="tab"
                                            aria-controls="general"
                                            aria-selected="true"
                                        >
                                            <i class="bi bi-person me-1"></i> General
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button
                                            class="nav-link"
                                            id="employment-tab"
                                            data-bs-toggle="tab"
                                            data-bs-target="#employment"
                                            type="button"
                                            role="tab"
                                            aria-controls="employment"
                                            aria-selected="false"
                                        >
                                            <i class="bi bi-briefcase me-1"></i> Employment
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button
                                            class="nav-link"
                                            id="family-tab"
                                            data-bs-toggle="tab"
                                            data-bs-target="#family"
                                            type="button"
                                            role="tab"
                                            aria-controls="family"
                                            aria-selected="false"
                                        >
                                            <i class="bi bi-people me-1"></i> Family
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button
                                            class="nav-link"
                                            id="finance-tab"
                                            data-bs-toggle="tab"
                                            data-bs-target="#finance"
                                            type="button"
                                            role="tab"
                                            aria-controls="finance"
                                            aria-selected="false"
                                        >
                                            <i class="bi bi-wallet2 me-1"></i> Finance
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button
                                            class="nav-link"
                                            id="documents-tab"
                                            data-bs-toggle="tab"
                                            data-bs-target="#documents"
                                            type="button"
                                            role="tab"
                                            aria-controls="documents"
                                            aria-selected="false"
                                        >
                                            <i class="bi bi-file-earmark-text me-1"></i> Documents
                                        </button>
                                    </li>
                                    @if (\App\Constants\Roles::isAdmin(session('role')) && $userAccount)
                                        <li class="nav-item" role="presentation">
                                            <button
                                                class="nav-link"
                                                id="security-tab"
                                                data-bs-toggle="tab"
                                                data-bs-target="#security"
                                                type="button"
                                                role="tab"
                                                aria-controls="security"
                                                aria-selected="false"
                                            >
                                                <i class="bi bi-shield-lock me-1"></i> Security
                                            </button>
                                        </li>
                                    @endif
                                </ul>

                                <!-- Tab Content -->
                                <div class="tab-content" id="employeeEditTabContent">
                                    <!-- General Information -->
                                    <div
                                        class="tab-pane fade show active"
                                        id="general"
                                        role="tabpanel"
                                        aria-labelledby="general-tab"
                                    >
                                        <div class="mb-4 text-center p-3 rounded bg-light">
                                            <div class="position-relative d-inline-block">
                                                @if ($employee->profile_photo)
                                                    <img src="{{ asset('storage/' . $employee->profile_photo) }}" alt="Profile Photo" class="rounded-circle shadow-sm" style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #435ebe;" id="photoPreview">
                                                @else
                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center mx-auto shadow-sm" style="width: 120px; height: 120px; font-size: 40px; font-weight: bold; border: 3px solid #435ebe;" id="photoPreview">
                                                        {{ Str::upper(substr($employee->fullname, 0, 2)) }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="mt-3 col-md-6 mx-auto">
                                                <label class="form-label fw-bold">Foto Profil (Profile Photo)</label>
                                                <input type="file" name="profile_photo" class="form-control" accept="image/*">
                                                <small class="text-muted">Format yang didukung: JPG, PNG, WEBP</small>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">NIK (Nomor Induk Karyawan)</label>
                                                            <input
                                                                type="text"
                                                                name="nik"
                                                                class="form-control"
                                                                value="{{ old('nik', $employee->nik) }}"
                                                                required
                                                            />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="mb-3">
                                                            <label class="form-label">NPWP</label>
                                                            <input
                                                                type="text"
                                                                name="npwp"
                                                                class="form-control"
                                                                value="{{ old('npwp', $employee->npwp) }}"
                                                                required
                                                            />
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Fullname</label>
                                                    <input
                                                        type="text"
                                                        name="fullname"
                                                        class="form-control"
                                                        value="{{ old('fullname', $employee->fullname) }}"
                                                        required
                                                    />
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Email</label>
                                                    <input
                                                        type="email"
                                                        name="email"
                                                        class="form-control"
                                                        value="{{ old('email', $employee->email) }}"
                                                        required
                                                    />
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Phone Number</label>
                                                    <input
                                                        type="text"
                                                        name="phone_number"
                                                        class="form-control"
                                                        value="{{ old('phone_number', $employee->phone_number) }}"
                                                    />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Address</label>
                                                    <input
                                                        type="text"
                                                        name="address"
                                                        class="form-control"
                                                        value="{{ old('address', $employee->address) }}"
                                                    />
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Place of Birth</label>
                                                    <input
                                                        type="text"
                                                        name="place_of_birth"
                                                        class="form-control"
                                                        value="{{ old('place_of_birth', $employee->place_of_birth) }}"
                                                    />
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Birth Date</label>
                                                    <input
                                                        type="date"
                                                        name="birth_date"
                                                        class="form-control"
                                                        value="{{ old('birth_date', \Carbon\Carbon::parse($employee->birth_date)->format('Y-m-d')) }}"
                                                        required
                                                    />
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Education Level</label>
                                                    <select
                                                        name="education_level_id"
                                                        class="form-select"
                                                        {{ $canEditEducation ? '' : 'disabled' }}
                                                    >
                                                        <option value="">-- Select --</option>
                                                        @foreach ($educationLevels as $level)
                                                            <option
                                                                value="{{ $level->education_level_id }}"
                                                                {{
                                                                    old('education_level_id', $employee->education_level_id) ==
                                                                    $level->education_level_id
                                                                        ? 'selected'
                                                                        : ''
                                                                }}
                                                            >
                                                                {{ $level->level }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @unless ($canEditEducation)
                                                        <input
                                                            type="hidden"
                                                            name="education_level_id"
                                                            value="{{ $employee->education_level_id }}"
                                                        />
                                                    @endunless
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="mb-3">
                                                            <label class="form-label">Gender</label>
                                                            <select name="gender" class="form-select">
                                                                <option value="">-- Select --</option>
                                                                <option
                                                                    value="male"
                                                                    {{
                                                                        old('gender', $employee->gender) == 'male'
                                                                            ? 'selected'
                                                                            : ''
                                                                    }}
                                                                    >Male
                                                                </option>
                                                                <option
                                                                    value="female"
                                                                    {{
                                                                        old('gender', $employee->gender) == 'female'
                                                                            ? 'selected'
                                                                            : ''
                                                                    }}
                                                                    >Female
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="mb-3">
                                                            <label class="form-label">Religion</label>
                                                            <input
                                                                type="text"
                                                                name="religion"
                                                                class="form-control"
                                                                value="{{ old('religion', $employee->religion) }}"
                                                                placeholder="e.g. Islam"
                                                            />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="mb-3">
                                                            <label class="form-label">Marital Status</label>
                                                            <select name="marital_status" class="form-select">
                                                                <option value="">-- Select --</option>
                                                                <option
                                                                    value="single"
                                                                    {{
                                                                        old('marital_status', $employee->marital_status) == 'single'
                                                                            ? 'selected'
                                                                            : ''
                                                                    }}
                                                                    >Single
                                                                </option>
                                                                <option
                                                                    value="married"
                                                                    {{
                                                                        old('marital_status', $employee->marital_status) == 'married'
                                                                            ? 'selected'
                                                                            : ''
                                                                    }}
                                                                    >Married
                                                                </option>
                                                                <option
                                                                    value="divorced"
                                                                    {{
                                                                        old('marital_status', $employee->marital_status) == 'divorced'
                                                                            ? 'selected'
                                                                            : ''
                                                                    }}
                                                                    >Divorced
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Employment Tab -->
                                    <div
                                        class="tab-pane fade"
                                        id="employment"
                                        role="tabpanel"
                                        aria-labelledby="employment-tab"
                                    >
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Hire Date</label>
                                                    <input
                                                        type="date"
                                                        name="hire_date"
                                                        id="hire_date"
                                                        class="form-control @error('hire_date') is-invalid @enderror"
                                                        value="{{ old('hire_date', $employee->hire_date ? $employee->hire_date->format('Y-m-d') : '') }}"
                                                        {{ $isAdmin ? '' : 'readonly' }}
                                                        required
                                                    />
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Department</label>
                                                    <select
                                                        name="department_id"
                                                        id="department_id"
                                                        class="form-select @error('department_id') is-invalid @enderror"
                                                        {{ $isAdmin ? '' : 'disabled' }}
                                                    >
                                                        @foreach ($departments as $department)
                                                            <option
                                                                value="{{ $department->id }}"
                                                                {{
                                                                    old('department_id', $employee->department_id) == $department->id
                                                                        ? 'selected'
                                                                        : ''
                                                                }}
                                                            >
                                                                {{ $department->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Office Location</label>
                                                    <select
                                                        name="office_location_id"
                                                        id="office_location_id"
                                                        class="form-select @error('office_location_id') is-invalid @enderror"
                                                        {{ $isAdmin ? '' : 'disabled' }}
                                                    >
                                                        @foreach ($officeLocations as $officeLocation)
                                                            <option
                                                                value="{{ $officeLocation->id }}"
                                                                {{
                                                                    old('office_location_id', $employee->office_location_id) == $officeLocation->id
                                                                        ? 'selected'
                                                                        : ''
                                                                }}
                                                            >
                                                                {{ $officeLocation->name }} ({{ $officeLocation->type_label }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <small class="text-muted"
                                                        >Lokasi kerja ini dipakai untuk pengaturan WFO dan
                                                        presensi.</small
                                                    >
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Role</label>
                                                    <select
                                                        name="role_id"
                                                        id="role_id"
                                                        class="form-select @error('role_id') is-invalid @enderror"
                                                        {{ $isAdmin ? '' : 'disabled' }}
                                                    >
                                                        @foreach ($roles as $roleItem)
                                                            <option
                                                                value="{{ $roleItem->id }}"
                                                                {{
                                                                    old('role_id', $employee->role_id) == $roleItem->id
                                                                        ? 'selected'
                                                                        : ''
                                                                }}
                                                            >
                                                                {{ $roleItem->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="position_id" class="form-label">Position</label>
                                                    <select
                                                        name="position_id"
                                                        id="position_id"
                                                        class="form-select"
                                                        required
                                                    >
                                                        <option value="">-- Select Position --</option>
                                                        @foreach ($positions as $pos)
                                                            <option
                                                                value="{{ $pos->position_id }}"
                                                                {{
                                                                    old('position_id', $employee->position_id ?? '') == $pos->position_id
                                                                        ? 'selected'
                                                                        : ''
                                                                }}
                                                            >
                                                                {{ $pos->position_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Employee Status</label>
                                                    <select name="employee_status" class="form-select" required>
                                                        @foreach (\App\Models\Employee::getAvailableStatuses() as $key => $label)
                                                            <option
                                                                value="{{ $key }}"
                                                                {{
                                                                    old('employee_status', $employee->employee_status) == $key
                                                                        ? 'selected'
                                                                        : ''
                                                                }}
                                                            >
                                                                {{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Resign Date</label>
                                                    <input
                                                        type="date"
                                                        name="resign_date"
                                                        class="form-control"
                                                        value="{{ old('resign_date', $employee->resign_date ? $employee->resign_date->format('Y-m-d') : '') }}"
                                                        {{ $isAdmin ? '' : 'readonly' }}
                                                    />
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Permanent Date</label>
                                                    <input
                                                        type="date"
                                                        name="permanent_date"
                                                        class="form-control"
                                                        value="{{ old('permanent_date', $employee->permanent_date ? $employee->permanent_date->format('Y-m-d') : '') }}"
                                                        {{ $isAdmin ? '' : 'readonly' }}
                                                    />
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Contract Expiry</label>
                                                    <input
                                                        type="date"
                                                        name="contract_expiry"
                                                        class="form-control"
                                                        value="{{ old('contract_expiry', $employee->contract_expiry ? $employee->contract_expiry->format('Y-m-d') : '') }}"
                                                        {{ $isAdmin ? '' : 'readonly' }}
                                                    />
                                                </div>
                                                @if (\App\Constants\Roles::isAdmin(session('role')))
                                                    <div class="mb-3">
                                                        <label class="form-label">Reporting To (Supervisor)</label>
                                                        <select name="supervisor_id" class="form-select">
                                                            <option value="">-- No Supervisor --</option>
                                                            @foreach ($potentialSupervisors as $supervisor)
                                                                <option
                                                                    value="{{ $supervisor->id }}"
                                                                    {{
                                                                        old('supervisor_id', $employee->supervisor_id) == $supervisor->id
                                                                            ? 'selected'
                                                                            : ''
                                                                    }}
                                                                >
                                                                    {{ $supervisor->fullname }} ({{ $supervisor->role->title ?? 'N/A' }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @endif
                                                <div class="mb-3">
                                                    <label class="form-label">Status</label>
                                                    <select name="status" class="form-select" required>
                                                        <option
                                                            value="active"
                                                            {{
                                                                old('status', $employee->status) == 'active'
                                                                    ? 'selected'
                                                                    : ''
                                                            }}
                                                            >Active
                                                        </option>
                                                        <option
                                                            value="inactive"
                                                            {{
                                                                old('status', $employee->status) == 'inactive'
                                                                    ? 'selected'
                                                                    : ''
                                                            }}
                                                            >Inactive
                                                        </option>
                                                    </select>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Working Type</label>
                                                    <select name="working_type" class="form-select" required>
                                                        <option
                                                            value="full_time"
                                                            {{
                                                                old('working_type', $employee->working_type) == 'full_time'
                                                                    ? 'selected'
                                                                    : ''
                                                            }}
                                                            >Full Time
                                                        </option>
                                                        <option
                                                            value="part_time"
                                                            {{
                                                                old('working_type', $employee->working_type) == 'part_time'
                                                                    ? 'selected'
                                                                    : ''
                                                            }}
                                                            >Part Time
                                                        </option>
                                                    </select>
                                                </div>
                                                @if ($isAdmin)
                                                    {{-- <div class="mb-3">
                                                        <label class="form-label">Salary</label>
                                                        <input type="number" name="salary" id="salary" class="form-control @error('salary') is-invalid @enderror" value="{{ old('salary', $employee->salary) }}" step="0.01">
                                                    </div> --}}
                                                    <div class="mb-3">
                                                        <label class="form-label">PPh 21 Rate (%)</label>
                                                        <div class="input-group">
                                                            <input
                                                                type="number"
                                                                name="pph21_rate"
                                                                class="form-control"
                                                                step="0.01"
                                                                min="0"
                                                                max="100"
                                                                value="{{ old('pph21_rate', $employee->pph21_rate ?? 0) }}"
                                                                placeholder="Contoh: 0.50 atau 5.00"
                                                            />
                                                            <span class="input-group-text">%</span>
                                                        </div>
                                                        <small class="text-muted">
                                                            Enter the monthly PPh21 tax deduction percentage
                                                            specifically for this employee.
                                                        </small>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label"
                                                            >Salary <span class="text-danger">*</span></label
                                                        >
                                                        <div class="input-group">
                                                            <span class="input-group-text">Rp</span>
                                                            <input
                                                                type="text"
                                                                id="display_salary"
                                                                class="form-control"
                                                                value="{{ old('salary', $employee->salary ?? 0) }}"
                                                                readonly
                                                                placeholder="Total Salary"
                                                            />
                                                            <button
                                                                type="button"
                                                                class="btn btn-primary"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#salaryModal"
                                                            >
                                                                <i class="bi bi-calculator"></i> Set Salary
                                                            </button>
                                                        </div>

                                                        <input
                                                            type="hidden"
                                                            name="salary"
                                                            id="salary"
                                                            value="{{ (int) old('salary', $employee->salary ?? 0) }}"
                                                        />
                                                        <input
                                                            type="hidden"
                                                            name="basic_salary"
                                                            id="basic_salary"
                                                            value="{{ (int) old('basic_salary', $employee->basic_salary ?? 0) }}"
                                                        />
                                                        <input
                                                            type="hidden"
                                                            name="meal_allowance"
                                                            id="meal_allowance"
                                                            value="{{ (int) old('meal_allowance', $employee->meal_allowance ?? 0) }}"
                                                        />
                                                        <input
                                                            type="hidden"
                                                            name="transport_allowance"
                                                            id="transport_allowance"
                                                            value="{{ (int) old('transport_allowance', $employee->transport_allowance ?? 0) }}"
                                                        />
                                                        <input
                                                            type="hidden"
                                                            name="position_allowance"
                                                            id="position_allowance"
                                                            value="{{ (int) old('position_allowance', $employee->position_allowance ?? 0) }}"
                                                        />
                                                    </div>
                                                    <div
                                                        class="modal fade"
                                                        id="salaryModal"
                                                        tabindex="-1"
                                                        aria-hidden="true"
                                                    >
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content">
                                                                <div class="modal-header bg-primary">
                                                                    <h5 class="modal-title text-white">
                                                                        Salary Details
                                                                    </h5>
                                                                    <button
                                                                        type="button"
                                                                        class="btn-close btn-close-white"
                                                                        data-bs-dismiss="modal"
                                                                    ></button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="mb-3">
                                                                        <label class="form-label text-dark fw-bold"
                                                                            >Basic Salary</label
                                                                        >
                                                                        <div class="input-group">
                                                                            <span class="input-group-text">Rp</span>
                                                                            <input
                                                                                type="text"
                                                                                id="modal_basic"
                                                                                class="form-control calc-modal"
                                                                                value="{{ (int) old('basic_salary', $employee->basic_salary ?? 0) }}"
                                                                            />
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label text-dark fw-bold"
                                                                            >Meal Allowance</label
                                                                        >
                                                                        <div class="input-group">
                                                                            <span class="input-group-text">Rp</span>
                                                                            <input
                                                                                type="text"
                                                                                id="modal_meal"
                                                                                class="form-control calc-modal"
                                                                                value="{{ (int) old('meal_allowance', $employee->meal_allowance ?? 0) }}"
                                                                            />
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label text-dark fw-bold"
                                                                            >Transport Allowance</label
                                                                        >
                                                                        <div class="input-group">
                                                                            <span class="input-group-text">Rp</span>
                                                                            <input
                                                                                type="text"
                                                                                id="modal_transport"
                                                                                class="form-control calc-modal"
                                                                                value="{{ (int) old('transport_allowance', $employee->transport_allowance ?? 0) }}"
                                                                            />
                                                                        </div>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label text-dark fw-bold"
                                                                            >Position Allowance</label
                                                                        >
                                                                        <div class="input-group">
                                                                            <span class="input-group-text">Rp</span>
                                                                            <input
                                                                                type="text"
                                                                                id="modal_position"
                                                                                class="form-control calc-modal"
                                                                                value="{{ (int) old('position_allowance', $employee->position_allowance ?? 0) }}"
                                                                            />
                                                                        </div>
                                                                    </div>
                                                                    <hr />
                                                                    <div
                                                                        class="d-flex justify-content-between align-items-center"
                                                                    >
                                                                        <h6 class="mb-0 fw-bold">Total Salary:</h6>
                                                                        <h5
                                                                            class="mb-0 fw-bold text-success"
                                                                            id="modal_total_text"
                                                                        >
                                                                            Rp 0
                                                                        </h5>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button
                                                                        type="button"
                                                                        class="btn btn-secondary"
                                                                        data-bs-dismiss="modal"
                                                                    >
                                                                        Cancel
                                                                    </button>
                                                                    <button
                                                                        type="button"
                                                                        class="btn btn-primary"
                                                                        id="btnSaveSalaryModal"
                                                                    >
                                                                        Apply Salary
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="col-12 mt-3">
                                                <div class="mb-3">
                                                    <label class="form-label"
                                                        >Mutation/Promotion Reason (Optional)</label
                                                    >
                                                    <textarea
                                                        name="mutation_reason"
                                                        class="form-control"
                                                        rows="2"
                                                        >{{ old('mutation_reason') }}</textarea
                                                    >
                                                    <small class="text-muted"
                                                        >Recorded only if department, role, or salary changes.</small
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Family Tab -->
                                    <div class="tab-pane fade" id="family" role="tabpanel" aria-labelledby="family-tab">
                                        <div class="mb-3 d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0">Family Members</h6>
                                            @if ($canEditFamily)
                                                <button
                                                    id="add-family"
                                                    class="btn btn-sm btn-outline-primary"
                                                    type="button"
                                                >
                                                    <i class="bi bi-plus-circle"></i> Add Family
                                                </button>
                                            @endif
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm align-middle border">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Nama</th>
                                                        <th>Relation</th>
                                                        <th>NIK</th>
                                                        <th>No KK</th>
                                                        <th>Tempat Lahir</th>
                                                        <th>Tanggal Lahir</th>
                                                        <th>Gender</th>
                                                        @if ($canDeleteFamily)
                                                            <th style="width: 50px" class="text-center">Aksi</th>
                                                        @endif
                                                    </tr>
                                                </thead>
                                                <tbody id="family-rows">
                                                    @foreach ($families as $index => $fam)
                                                        <tr class="family-row" data-family-id="{{ $fam->id }}">
                                                            <td>
                                                                <input
                                                                    type="hidden"
                                                                    name="families[{{ $index }}][id]"
                                                                    value="{{ $fam->id }}"
                                                                />
                                                                <input
                                                                    type="text"
                                                                    class="form-control form-control-sm"
                                                                    name="families[{{ $index }}][fullname]"
                                                                    value="{{ $fam->fullname }}"
                                                                    {{ $canEditFamily ? '' : 'readonly' }}
                                                                />
                                                            </td>
                                                            <td>
                                                                <input
                                                                    type="text"
                                                                    class="form-control form-control-sm"
                                                                    name="families[{{ $index }}][relation]"
                                                                    value="{{ $fam->relation }}"
                                                                    {{ $canEditFamily ? '' : 'readonly' }}
                                                                />
                                                            </td>
                                                            <td>
                                                                <input
                                                                    type="text"
                                                                    class="form-control form-control-sm"
                                                                    name="families[{{ $index }}][nik]"
                                                                    value="{{ $fam->nik }}"
                                                                    {{ $canEditFamily ? '' : 'readonly' }}
                                                                />
                                                            </td>
                                                            <td>
                                                                <input
                                                                    type="text"
                                                                    class="form-control form-control-sm"
                                                                    name="families[{{ $index }}][no_kk]"
                                                                    value="{{ $fam->no_kk }}"
                                                                    {{ $canEditFamily ? '' : 'readonly' }}
                                                                />
                                                            </td>
                                                            <td>
                                                                <input
                                                                    type="text"
                                                                    class="form-control form-control-sm"
                                                                    name="families[{{ $index }}][place_of_birth]"
                                                                    value="{{ $fam->place_of_birth }}"
                                                                    {{ $canEditFamily ? '' : 'readonly' }}
                                                                />
                                                            </td>
                                                            <td>
                                                                <input
                                                                    type="date"
                                                                    class="form-control form-control-sm"
                                                                    name="families[{{ $index }}][date_of_birth]"
                                                                    value="{{ optional($fam->date_of_birth)->format('Y-m-d') }}"
                                                                    {{ $canEditFamily ? '' : 'readonly' }}
                                                                />
                                                            </td>
                                                            <td>
                                                                <input
                                                                    type="text"
                                                                    class="form-control form-control-sm"
                                                                    name="families[{{ $index }}][gender]"
                                                                    value="{{ $fam->gender }}"
                                                                    {{ $canEditFamily ? '' : 'readonly' }}
                                                                />
                                                            </td>
                                                            @if ($canDeleteFamily)
                                                                <td class="text-center">
                                                                    <button
                                                                        type="button"
                                                                        class="btn btn-sm btn-outline-danger"
                                                                        onclick="deleteFamilyRow(this)"
                                                                    >
                                                                        <i class="bi bi-trash"></i>
                                                                    </button>
                                                                </td>
                                                            @endif
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @if ($canDeleteFamily)
                                            <div id="families-to-delete"></div>
                                        @endif
                                    </div>

                                    <!-- Finance Tab -->
                                    <div
                                        class="tab-pane fade"
                                        id="finance"
                                        role="tabpanel"
                                        aria-labelledby="finance-tab"
                                    >
                                        <div class="mb-3 d-flex justify-content-between align-items-center mt-2">
                                            <h6 class="mb-0">Bank Accounts</h6>
                                            <button id="add-bank" class="btn btn-sm btn-outline-primary" type="button">
                                                <i class="bi bi-plus-circle"></i> Add Bank
                                            </button>
                                        </div>
                                        <div class="table-responsive mb-4">
                                            <table class="table table-sm align-middle border">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Bank Name</th>
                                                        <th>Account No</th>
                                                        <th>Holder</th>
                                                        <th class="text-center">Primary</th>
                                                        <th style="width: 50px" class="text-center">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="bank-rows">
                                                    @foreach ($bankAccounts as $index => $bank)
                                                        <tr class="bank-row" data-bank-id="{{ $bank->id }}">
                                                            <td>
                                                                <input
                                                                    type="hidden"
                                                                    name="banks[{{ $index }}][id]"
                                                                    value="{{ $bank->id }}"
                                                                />
                                                                <input
                                                                    type="text"
                                                                    class="form-control form-control-sm"
                                                                    name="banks[{{ $index }}][bank_name]"
                                                                    value="{{ $bank->bank_name }}"
                                                                />
                                                            </td>
                                                            <td>
                                                                <input
                                                                    type="text"
                                                                    class="form-control form-control-sm"
                                                                    name="banks[{{ $index }}][account_no]"
                                                                    value="{{ $bank->account_no }}"
                                                                />
                                                            </td>
                                                            <td>
                                                                <input
                                                                    type="text"
                                                                    class="form-control form-control-sm"
                                                                    name="banks[{{ $index }}][account_holder]"
                                                                    value="{{ $bank->account_holder }}"
                                                                />
                                                            </td>
                                                            <td class="text-center">
                                                                <input
                                                                    class="form-check-input bank-primary-radio"
                                                                    type="radio"
                                                                    name="bank_primary_index"
                                                                    value="{{ $index }}"
                                                                    {{ $bank->is_primary ? 'checked' : '' }}
                                                                />
                                                            </td>
                                                            <td class="text-center">
                                                                <button
                                                                    type="button"
                                                                    class="btn btn-sm btn-outline-danger"
                                                                    onclick="deleteBankRow(this)"
                                                                >
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div id="banks-to-delete"></div>

                                        <div class="mb-3 d-flex justify-content-between align-items-center mt-2">
                                            <h6 class="mb-0">Document Identities</h6>
                                            <button
                                                id="add-document"
                                                class="btn btn-sm btn-outline-primary"
                                                type="button"
                                            >
                                                <i class="bi bi-plus-circle"></i> Add Document
                                            </button>
                                        </div>
                                        <div class="table-responsive">
                                            <table class="table table-sm align-middle border">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Type</th>
                                                        <th>ID Number</th>
                                                        <th>Description</th>
                                                        <th>File</th>
                                                        <th style="width: 100px" class="text-center">Aksi</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="document-rows">
                                                    @foreach ($documentIdentities as $index => $doc)
                                                        <tr class="document-row" data-document-id="{{ $doc->id }}">
                                                            <td style="width: 150px">
                                                                <input
                                                                    type="hidden"
                                                                    name="documents[{{ $index }}][id]"
                                                                    value="{{ $doc->id }}"
                                                                />
                                                                <select
                                                                    name="documents[{{ $index }}][identity_type_id]"
                                                                    class="form-select form-select-sm"
                                                                >
                                                                    @foreach ($identityTypes as $type)
                                                                        <option
                                                                            value="{{ $type->identity_type_id }}"
                                                                            {{
                                                                                $doc->identity_type_id == $type->identity_type_id
                                                                                    ? 'selected'
                                                                                    : ''
                                                                            }}
                                                                        >
                                                                            {{ $type->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <input
                                                                    type="text"
                                                                    class="form-control form-control-sm"
                                                                    name="documents[{{ $index }}][identity_number]"
                                                                    value="{{ $doc->identity_number }}"
                                                                />
                                                            </td>
                                                            <td>
                                                                <input
                                                                    type="text"
                                                                    class="form-control form-control-sm"
                                                                    name="documents[{{ $index }}][description]"
                                                                    value="{{ $doc->description }}"
                                                                />
                                                            </td>
                                                            <td>
                                                                @if ($doc->file_name)
                                                                    <a
                                                                        href="{{ asset('storage/employee_documents/' . $employee->id . '/' . $doc->file_name) }}"
                                                                        target="_blank"
                                                                        class="btn btn-xs btn-outline-info"
                                                                    >
                                                                        <i class="bi bi-download"></i>
                                                                    </a>
                                                                @else
                                                                    <span class="text-muted small">No File</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                <div class="btn-group btn-group-sm">
                                                                    <button
                                                                        type="button"
                                                                        class="btn btn-outline-primary"
                                                                        onclick="showUploadModal({{ $doc->id }}, '{{ $doc->identityType->name ?? 'Document' }}')"
                                                                    >
                                                                        <i class="bi bi-upload"></i>
                                                                    </button>
                                                                    <button
                                                                        type="button"
                                                                        class="btn btn-outline-danger"
                                                                        onclick="deleteDocumentRow(this)"
                                                                    >
                                                                        <i class="bi bi-trash"></i>
                                                                    </button>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <div id="documents-to-delete"></div>
                                    </div>

                                    <!-- Security Tab -->
                                    @if (\App\Constants\Roles::isAdmin(session('role')) && $userAccount)
                                        <div
                                            class="tab-pane fade"
                                            id="security"
                                            role="tabpanel"
                                            aria-labelledby="security-tab"
                                        >
                                            <div class="alert alert-light-info border-0 rounded-3 mb-4">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-info-circle-fill fs-4 text-info me-3"></i>
                                                    <div>
                                                        <h6 class="mb-1 text-info">Device Registration Info</h6>
                                                        <p class="mb-0 small">Karyawan hanya dapat melakukan absen dari perangkat yang telah didaftarkan. Anda dapat merest pendaftaran jika mereka berganti perangkat.</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card border shadow-none mb-4">
                                                <div class="card-header bg-light-danger border-bottom p-3">
                                                    <h6 class="mb-0 text-danger">
                                                        <i class="bi bi-key-fill me-2"></i> Password Reset
                                                    </h6>
                                                </div>
                                                <div class="card-body p-3">
                                                    <p class="text-muted small mb-3">Kosongkan jika tidak ingin mengubah password karyawan ini. Minimal 8 karakter.</p>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label">New Password</label>
                                                                <input
                                                                    type="password"
                                                                    name="password"
                                                                    class="form-control"
                                                                    autocomplete="new-password"
                                                                />
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="mb-3">
                                                                <label class="form-label">Confirm New Password</label>
                                                                <input
                                                                    type="password"
                                                                    name="password_confirmation"
                                                                    class="form-control"
                                                                    autocomplete="new-password"
                                                                />
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row g-4 mb-4">
                                                <div class="col-md-6">
                                                    <div class="card border h-100 shadow-none">
                                                        <div class="card-body p-3">
                                                            <div
                                                                class="d-flex justify-content-between align-items-center mb-3"
                                                            >
                                                                <div class="d-flex align-items-center">
                                                                    <div class="bg-primary-light p-2 rounded-3 me-3">
                                                                        <i class="bi bi-laptop text-primary fs-4"></i>
                                                                    </div>
                                                                    <h6 class="mb-0">Desktop / Laptop</h6>
                                                                </div>
                                                                @if ($userAccount->browser_fingerprint_desktop)
                                                                    <span class="badge bg-success-light text-success"
                                                                        ><i class="bi bi-check-circle-fill me-1"></i>
                                                                        Registered</span
                                                                    >
                                                                @else
                                                                    <span class="badge bg-light text-muted"
                                                                        >Not Registered</span
                                                                    >
                                                                @endif
                                                            </div>

                                                            @if ($userAccount->browser_fingerprint_desktop)
                                                                <div class="bg-light p-2 rounded mb-3">
                                                                    <code
                                                                        class="text-muted small word-break-all"
                                                                        >{{ $userAccount->browser_fingerprint_desktop }}</code
                                                                    >
                                                                </div>
                                                                <div class="form-check form-switch custom-switch">
                                                                    <input
                                                                        class="form-check-input"
                                                                        type="checkbox"
                                                                        name="reset_desktop_fingerprint"
                                                                        id="reset_desktop"
                                                                    />
                                                                    <label
                                                                        class="form-check-label text-danger small pt-1"
                                                                        for="reset_desktop"
                                                                        >Reset Registration ID</label
                                                                    >
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="card border h-100 shadow-none">
                                                        <div class="card-body p-3">
                                                            <div
                                                                class="d-flex justify-content-between align-items-center mb-3"
                                                            >
                                                                <div class="d-flex align-items-center">
                                                                    <div class="bg-primary-light p-2 rounded-3 me-3">
                                                                        <i class="bi bi-phone text-primary fs-4"></i>
                                                                    </div>
                                                                    <h6 class="mb-0">Mobile Device</h6>
                                                                </div>
                                                                @if ($userAccount->browser_fingerprint_mobile)
                                                                    <span class="badge bg-success-light text-success"
                                                                        ><i class="bi bi-check-circle-fill me-1"></i>
                                                                        Registered</span
                                                                    >
                                                                @else
                                                                    <span class="badge bg-light text-muted"
                                                                        >Not Registered</span
                                                                    >
                                                                @endif
                                                            </div>

                                                            @if ($userAccount->browser_fingerprint_mobile)
                                                                <div class="bg-light p-2 rounded mb-3">
                                                                    <code
                                                                        class="text-muted small word-break-all"
                                                                        >{{ $userAccount->browser_fingerprint_mobile }}</code
                                                                    >
                                                                </div>
                                                                <div class="form-check form-switch custom-switch">
                                                                    <input
                                                                        class="form-check-input"
                                                                        type="checkbox"
                                                                        name="reset_mobile_fingerprint"
                                                                        id="reset_mobile"
                                                                    />
                                                                    <label
                                                                        class="form-check-label text-danger small pt-1"
                                                                        for="reset_mobile"
                                                                        >Reset Registration ID</label
                                                                    >
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <hr class="mt-4" />

                                <div class="d-flex justify-content-between align-items-center mt-4">
                                    @if ($isAdmin)
                                        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary px-4">
                                            <i class="bi bi-arrow-left me-2"></i> Kembali
                                        </a>
                                        <button type="submit" class="btn btn-primary px-4">
                                            <i class="bi bi-save me-2"></i> Update Data
                                        </button>
                                    @else
                                        <a href="{{ route('my-profile') }}" class="btn btn-outline-secondary px-4">
                                            <i class="bi bi-arrow-left me-2"></i> Kembali ke Profile
                                        </a>
                                        <div class="text-end">
                                            <p class="text-muted small mb-2"><i class="bi bi-info-circle me-1"></i> Foto profil &amp; data kontak langsung disimpan. Perubahan data sensitif akan diajukan ke HR.</p>
                                            <button type="submit" class="btn btn-warning fw-bold px-4 shadow-sm">
                                                <i class="bi bi-check-circle me-2"></i> Simpan Perubahan
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    @if ($canEditFamily)
        <!-- Document Upload Modal -->
        <div
            class="modal fade"
            id="uploadDocumentModal"
            tabindex="-1"
            aria-labelledby="uploadDocumentModalLabel"
            aria-hidden="true"
        >
            <div class="modal-dialog">
                <div class="modal-content">
                    <form id="uploadDocumentForm" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="uploadDocumentModalLabel">Upload Document</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Document Type</label>
                                <input type="text" id="modal_doc_type" class="form-control" readonly />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Identity Number</label>
                                <input
                                    type="text"
                                    name="identity_number"
                                    id="modal_id_number"
                                    class="form-control"
                                    required
                                />
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Identity Type</label>
                                <select name="identity_type_id" id="modal_id_type_id" class="form-select" required>
                                    @foreach ($identityTypes as $type)
                                        <option value="{{ $type->identity_type_id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Select File</label>
                                <input
                                    type="file"
                                    name="file"
                                    class="form-control"
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    required
                                />
                                <small class="text-muted">PDF, JPG, PNG (Max 2MB)</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description (Optional)</label>
                                <textarea
                                    name="description"
                                    id="modal_description"
                                    class="form-control"
                                    rows="2"
                                ></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Upload & Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @push ('scripts')
            <script>
                const familyRows = document.getElementById('family-rows');
                const bankRows = document.getElementById('bank-rows');
                const documentRows = document.getElementById('document-rows');
                const canDeleteFamily = {{ $canDeleteFamily ? 'true' : 'false' }};

                // Document Upload Logic
                const uploadModal = new bootstrap.Modal(document.getElementById('uploadDocumentModal'));
                const uploadForm = document.getElementById('uploadDocumentForm');

                window.showUploadModal = function (docId, typeName) {
                    uploadForm.action = '{{ route('employees.documents.store', $employee->id) }}';
                    document.getElementById('modal_doc_type').value = typeName;

                    if (docId) {
                        // Find row data
                        const row = document.querySelector(`.document-row[data-document-id="${docId}"]`);
                        if (row) {
                            document.getElementById('modal_id_number').value = row.querySelector(
                                'input[name*="identity_number"]',
                            ).value;
                            document.getElementById('modal_id_type_id').value = row.querySelector(
                                'select[name*="identity_type_id"]',
                            ).value;
                            document.getElementById('modal_description').value = row.querySelector('input[name*="description"]').value;
                        }
                    } else {
                        document.getElementById('modal_id_number').value = '';
                        document.getElementById('modal_description').value = '';
                    }

                    uploadModal.show();
                };

                // Family Logic
                // ... (rest of the scripts)

                // Family Logic
                document.getElementById('add-family')?.addEventListener('click', function (e) {
                    e.preventDefault();
                    const index = familyRows.querySelectorAll('.family-row').length + Date.now();
                    const rowHtml = `
                    <tr class="family-row">
                        <td><input type="text" class="form-control form-control-sm" name="families[${index}][fullname]"></td>
                        <td><input type="text" class="form-control form-control-sm" name="families[${index}][relation]"></td>
                        <td><input type="text" class="form-control form-control-sm" name="families[${index}][nik]"></td>
                        <td><input type="text" class="form-control form-control-sm" name="families[${index}][no_kk]"></td>
                        <td><input type="text" class="form-control form-control-sm" name="families[${index}][place_of_birth]"></td>
                        <td><input type="date" class="form-control form-control-sm" name="families[${index}][date_of_birth]"></td>
                        <td><input type="text" class="form-control form-control-sm" name="families[${index}][gender]"></td>
                        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteFamilyRow(this)"><i class="bi bi-trash"></i></button></td>
                    </tr>`;
                    familyRows.insertAdjacentHTML('beforeend', rowHtml);
                });

                window.deleteFamilyRow = function (btn) {
                    const row = btn.closest('.family-row');
                    const existingId = row?.dataset.familyId;
                    if (existingId) {
                        const target = document.getElementById('families-to-delete');
                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = 'families_to_delete[]';
                        hidden.value = existingId;
                        target.appendChild(hidden);
                    }
                    row.remove();
                };

                // Bank Logic
                document.getElementById('add-bank')?.addEventListener('click', function (e) {
                    e.preventDefault();
                    const index = bankRows.querySelectorAll('.bank-row').length + Date.now();
                    const rowHtml = `
                    <tr class="bank-row">
                        <td><input type="text" class="form-control form-control-sm" name="banks[${index}][bank_name]"></td>
                        <td><input type="text" class="form-control form-control-sm" name="banks[${index}][account_no]"></td>
                        <td><input type="text" class="form-control form-control-sm" name="banks[${index}][account_holder]"></td>
                        <td class="text-center">
                            <div class="form-check d-flex justify-content-center">
                                <input class="form-check-input bank-primary-radio" type="radio" name="bank_primary_index" value="${index}">
                            </div>
                        </td>
                        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteBankRow(this)"><i class="bi bi-trash"></i></button></td>
                    </tr>`;
                    bankRows.insertAdjacentHTML('beforeend', rowHtml);
                });

                window.deleteBankRow = function (btn) {
                    const row = btn.closest('.bank-row');
                    const existingId = row?.dataset.bankId;
                    if (existingId) {
                        const target = document.getElementById('banks-to-delete');
                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = 'banks_to_delete[]';
                        hidden.value = existingId;
                        target.appendChild(hidden);
                    }
                    row.remove();
                };

                // Document Logic
                document.getElementById('add-document')?.addEventListener('click', function (e) {
                    e.preventDefault();
                    showUploadModal(null, 'New Document');
                });

                window.deleteDocumentRow = function (btn) {
                    const row = btn.closest('.document-row');
                    const existingId = row?.dataset.documentId;
                    if (existingId) {
                        const target = document.getElementById('documents-to-delete');
                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = 'documents_to_delete[]';
                        hidden.value = existingId;
                        target.appendChild(hidden);
                    }
                    row.remove();
                };

                const formatRp = (n) => Number(n).toLocaleString('id-ID');

                // convert formatted string like "1.000.000" into raw number
                const parseNumber = (str) => {
                    if (!str) {
                        return 0;
                    }

                    // remove all non numeric characters
                    let numStr = str.toString().replace(/\D/g, '');

                    return numStr === '' ? 0 : parseFloat(numStr);
                };

                // format input value in real time
                const formatInputRupiah = (input) => {
                    // store current cursor position and text length
                    let cursorPosition = input.selectionStart;
                    let originalLength = input.value.length;

                    let val = parseNumber(input.value);

                    if (val === 0 && input.value === '') {
                        input.value = '';
                    } else {
                        input.value = formatRp(val);
                    }

                    // adjust cursor position after formatting
                    let newLength = input.value.length;

                    cursorPosition = cursorPosition + (newLength - originalLength);

                    // restore cursor position
                    input.setSelectionRange(cursorPosition, cursorPosition);
                };

                // initialize formatted salary on page load
                document.getElementById('display_salary').value = formatRp(document.getElementById('salary').value);

                // format modal inputs on page load
                document.querySelectorAll('.calc-modal').forEach((el) => {
                    if (el.value !== '' && el.value !== '0') {
                        formatInputRupiah(el);
                    }
                });

                const calcModal = () => {
                    let b = parseNumber(document.getElementById('modal_basic').value);
                    let m = parseNumber(document.getElementById('modal_meal').value);
                    let t = parseNumber(document.getElementById('modal_transport').value);
                    let p = parseNumber(document.getElementById('modal_position').value);

                    let total = b + m + t + p;

                    document.getElementById('modal_total_text').innerText = 'Rp ' + formatRp(total);

                    return total;
                };

                // listen for modal input changes
                document.querySelectorAll('.calc-modal').forEach((el) => {
                    el.addEventListener('input', function () {
                        // format input value
                        formatInputRupiah(this);

                        // recalculate total
                        calcModal();
                    });
                });

                document.getElementById('salaryModal').addEventListener('show.bs.modal', calcModal);

                document.getElementById('btnSaveSalaryModal').addEventListener('click', function () {
                    let b = parseNumber(document.getElementById('modal_basic').value);
                    let m = parseNumber(document.getElementById('modal_meal').value);
                    let t = parseNumber(document.getElementById('modal_transport').value);
                    let p = parseNumber(document.getElementById('modal_position').value);

                    let total = calcModal();

                    // save raw numeric values to hidden inputs
                    document.getElementById('basic_salary').value = b;
                    document.getElementById('meal_allowance').value = m;
                    document.getElementById('transport_allowance').value = t;
                    document.getElementById('position_allowance').value = p;
                    document.getElementById('salary').value = total;

                    // update formatted salary display
                    document.getElementById('display_salary').value = formatRp(total);

                    var modal = bootstrap.Modal.getInstance(document.getElementById('salaryModal'));

                    modal.hide();
                });
            </script>
        @endpush
    @endif

@endsection
