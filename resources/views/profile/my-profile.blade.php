@extends ('layouts.dashboard')

@section ('content')
    @php
        $isAdmin = \App\Constants\Roles::isAdmin(session('role'));
    @endphp
    <div class="page-heading">
        <div class="card shadow-sm border-0 mb-4 rounded-3 bg-primary text-white">
            <div class="card-body p-4 p-md-5 d-flex flex-column flex-md-row align-items-center gap-4">
                {{-- Avatar --}}
                <div class="position-relative">
                    @if ($employee->profile_photo)
                        <div class="avatar avatar-2xl bg-light">
                            <img src="{{ asset('storage/' . $employee->profile_photo) }}" alt="Profile Photo" class="rounded-circle" style="object-fit: cover;" />
                        </div>
                    @else
                        <div class="bg-light text-primary rounded-circle" style="width: 120px; height: 120px; box-shadow: 0 0 0 4px rgba(255,255,255,0.2); display: flex !important; align-items: center !important; justify-content: center !important;">
                            <i class="bi bi-person-fill" style="font-size: 5rem; margin: 0 !important; line-height: 0 !important; transform: translate(-30px, -35px);"></i>
                        </div>
                    @endif
                </div>

                {{-- Employee Info --}}
                <div class="flex-grow-1 text-center text-md-start w-100">
                    <h6 class="text-white-50 mb-1 fw-bold text-uppercase" style="letter-spacing: 0.1em;">{{ $employee->nik ?? 'N/A' }}</h6>
                    <h2 class="mb-1 fw-bold text-white">{{ $employee->fullname }}</h2>
                    <p class="text-white-50 mb-0 fs-5">
                        {{ $employee->department->name ?? 'N/A' }} &bull; {{ $employee->role->title ?? 'N/A' }}
                    </p>
                </div>

                {{-- Edit Button --}}
                <div class="mt-3 mt-md-0 w-100 w-md-auto d-grid d-md-block text-md-end" style="min-width: 180px;">
                    <a href="{{ route('my-profile.edit') }}" class="btn btn-light text-primary fw-semibold px-4 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-pencil-square me-2"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>

        {{-- ══════════════════ Tabs Section ══════════════════ --}}
        <section class="section">
            <style>
                .nav-tabs .nav-link { font-weight: 600; color: var(--bs-body-color); padding: 0.75rem 1.25rem; }
                .table-profile th { width: 30%; font-weight: 600; color: var(--bs-heading-color); }
                .table-profile td { font-weight: 500; }
                @media (max-width: 768px) {
                    .nav-tabs { flex-wrap: nowrap; overflow-x: auto; overflow-y: hidden; white-space: nowrap; padding-bottom: 5px; }
                    .table-profile th, .table-profile td { display: block; width: 100%; padding: 0.25rem 0; }
                    .table-profile tr { display: block; border-bottom: 1px solid var(--bs-border-color); padding: 0.75rem 0; }
                    .table-profile tr:last-child { border-bottom: none; }
                }
            </style>
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-transparent border-bottom-0 pb-0 pt-4 px-4">
                    <ul class="nav nav-tabs" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link active"
                                id="working-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#working"
                                type="button"
                                role="tab"
                            >
                                Working Information
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link"
                                id="personal-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#personal"
                                type="button"
                                role="tab"
                            >
                                Personal Information
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link"
                                id="education-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#education"
                                type="button"
                                role="tab"
                            >
                                Education
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
                            >
                                Family Relation
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link"
                                id="career-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#career"
                                type="button"
                                role="tab"
                            >
                                Career History
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link"
                                id="bank-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#bank"
                                type="button"
                                role="tab"
                            >
                                Bank Account
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link"
                                id="training-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#training"
                                type="button"
                                role="tab"
                            >
                                Training History
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
                            >
                                Documents
                            </button>
                        </li>
                    </ul>
                </div>

                {{-- Tab Content --}}
                <div class="card-body p-4">
                    <div class="tab-content" id="profileTabsContent">
                        {{-- Working Information --}}
                        <div class="tab-pane fade show active" id="working" role="tabpanel">
                            <table class="table table-borderless table-profile mb-0">
                                <tbody>
                                    <tr>
                                        <th>Join Date</th>
                                        <td>
                                            {{
                                                $employee->hire_date
                                                    ? $employee->hire_date->format('d/m/Y')
                                                    : '-'
                                            }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Resign Date</th>
                                        <td>
                                            {{
                                                $employee->resign_date
                                                    ? \Carbon\Carbon::parse($employee->resign_date)->format('d/m/Y')
                                                    : '-'
                                            }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Permanent Date</th>
                                        <td>
                                            {{
                                                $employee->permanent_date
                                                    ? $employee->permanent_date->format('d/m/Y')
                                                    : '-'
                                            }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Contract Expiry</th>
                                        <td>
                                            {{
                                                $employee->contract_expiry
                                                    ? $employee->contract_expiry->format('d/m/Y')
                                                    : '-'
                                            }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Work Location</th>
                                        <td>{{ $employee->department->name ?? 'Head Office' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Homebase</th>
                                        <td>Head Office</td>
                                    </tr>
                                    @if ($isAdmin)
                                        <tr>
                                            <th>JG / PG</th>
                                            <td>
                                                @php
                                                    $latestPos = $employee->employeePositions->sortByDesc('start_date')->first();
                                                @endphp
                                                {{ $latestPos->pay_grade_id ?? '-' }}
                                            </td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th>Employment Status</th>
                                        <td>{{ ucfirst($employee->employee_status ?? 'Permanent') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Personal Information --}}
                        <div class="tab-pane fade" id="personal" role="tabpanel">
                            <table class="table table-borderless table-profile mb-0">
                                <tbody>
                                    <tr>
                                        <th>NIK</th>
                                        <td>{{ $employee->nik ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Full Name</th>
                                        <td>{{ $employee->fullname }}</td>
                                    </tr>
                                    <tr>
                                        <th>Place / Date of Birth</th>
                                        <td>
                                            {{ $employee->place_of_birth ?? '-' }} / {{ $employee->birth_date ? $employee->birth_date->format('d M Y') : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Gender</th>
                                        <td>{{ ucfirst($employee->gender ?? '-') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Religion</th>
                                        <td>{{ $employee->religion ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Marital Status</th>
                                        <td>{{ $employee->marital_status ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>{{ $employee->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>Phone Number</th>
                                        <td>{{ $employee->phone_number ?? '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Address</th>
                                        <td>{{ $employee->address ?? '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Education --}}
                        <div class="tab-pane fade" id="education" role="tabpanel">
                            <table class="table table-borderless table-profile mb-0">
                                <tbody>
                                    <tr>
                                        <th>Latest Education Level</th>
                                        <td>{{ $employee->educationLevel->level ?? '-' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Family Relation --}}
                        <div class="tab-pane fade" id="family" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Relation</th>
                                            <th>NIK</th>
                                            <th>Date of Birth</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($employee->families as $family)
                                            <tr>
                                                <td>{{ $family->fullname }}</td>
                                                <td>{{ $family->relation }}</td>
                                                <td>{{ $family->nik }}</td>
                                                <td>
                                                    {{
                                                        $family->date_of_birth
                                                            ? \Carbon\Carbon::parse($family->date_of_birth)->format('d/m/Y')
                                                            : '-'
                                                    }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">
                                                    No family data available.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Career History --}}
                        <div class="tab-pane fade" id="career" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Old Position</th>
                                            <th>New Position</th>
                                            <th>Reason</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($employee->mutations as $mutation)
                                            <tr>
                                                <td>{{ $mutation->mutation_date->format('d/m/Y') }}</td>
                                                <td>{{ ucfirst($mutation->type) }}</td>
                                                <td>
                                                    {{ $mutation->oldDepartment->name ?? '-' }} – {{ $mutation->oldRole->title ?? '-' }}
                                                </td>
                                                <td>
                                                    {{ $mutation->newDepartment->name ?? '-' }} – {{ $mutation->newRole->title ?? '-' }}
                                                </td>
                                                <td>{{ $mutation->reason }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">
                                                    No career history available.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Bank Account --}}
                        <div class="tab-pane fade" id="bank" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th>Bank Name</th>
                                            <th>Account No</th>
                                            <th>Account Holder</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($employee->bankAccounts as $bank)
                                            <tr>
                                                <td>{{ $bank->bank_name }}</td>
                                                <td>{{ $bank->account_no }}</td>
                                                <td>{{ $bank->account_holder }}</td>
                                                <td>
                                                    @if ($bank->is_primary)
                                                        <span class="badge bg-primary">Primary</span>
                                                    @else
                                                        <span class="badge bg-secondary">Secondary</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">
                                                    No bank account information available.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Training History --}}
                        <div class="tab-pane fade" id="training" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Training Name</th>
                                            <th>Provider</th>
                                            <th>Category</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">
                                                <i class="bi bi-journal-check fs-2 d-block mb-2"></i>
                                                Training history data is not yet available for this employee.
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Documents --}}
                        <div class="tab-pane fade" id="documents" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th>Document Type</th>
                                            <th>ID Number</th>
                                            <th>Description</th>
                                            <th>File</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($employee->documentIdentities as $doc)
                                            <tr>
                                                <td>{{ $doc->identityType->name ?? 'N/A' }}</td>
                                                <td>{{ $doc->identity_number }}</td>
                                                <td>{{ $doc->description ?? '-' }}</td>
                                                <td>
                                                    @if ($doc->file_name)
                                                        <a
                                                            href="{{ asset('storage/' . $doc->file_name) }}"
                                                            target="_blank"
                                                            class="btn btn-sm btn-outline-primary"
                                                        >
                                                            <i class="bi bi-download"></i> View
                                                        </a>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4">
                                                    No documents available.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                    </div>
                    {{-- /tab-content --}}
                </div>
            </div>
        </section>
    </div>
@endsection