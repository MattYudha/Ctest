@extends('layouts.dashboard')

@section('content')



<div class="page-heading">
    <div class="page-title mb-4">
        <div class="row">
            <div class="col-md-6">
                <h3>Employee Detail</h3>
                <p class="text-subtitle text-muted">Detail employee information</p>
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
                        <li class="breadcrumb-item active">Detail</li>
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

                        <div class="row mb-4">
                            <!-- LEFT -->
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="text-muted">NIK (Nomor Induk Karyawan)</label>
                                            <div class="fw-semibold">{{ $employee->nik }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="text-muted">NPWP</label>
                                            <div class="fw-semibold">{{ $employee->npwp ?: '-' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="text-muted">Fullname</label>
                                    <div class="fw-semibold">{{ $employee->fullname }}</div>
                                </div>

                                <div class="mb-3">
                                    <label class="text-muted">Status Karyawan</label>
                                    <div>{!! $employee->employee_status_badge !!}</div>
                                </div>

                                <div class="mb-3">
                                    <label class="text-muted">Email</label>
                                    <div>{{ $employee->email }}</div>
                                </div>

                                <div class="mb-3">
                                    <label class="text-muted">Role</label>
                                    <div>{{ $employee->role->title }}</div>
                                </div>

                                <div class="mb-3">
                                    <label class="text-muted">Department</label>
                                    <div>{{ $employee->department->name ?? '-' }}</div>
                                </div>

                                <div class="mb-3">
                                    <label class="text-muted">Office Location</label>
                                    <div>{{ $employee->officeLocation?->name ?? '-' }}</div>
                                </div>

                                <div class="mb-3">
                                    <label class="text-muted">Status</label>
                                    <div>
                                        @if($employee->status === 'active')
                                            <span class="badge bg-success">Active</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Inactive</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- RIGHT -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="text-muted">Salary</label>
                                    <div class="fw-semibold">
                                        @if(\App\Constants\Roles::isAdmin(session('role')))
                                            Rp {{ number_format($employee->salary, 0, ',', '.') }}
                                        @else
                                            ***
                                        @endif
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="text-muted">Present</label>
                                    <div class="fw-semibold text-success">
                                        {{ $present }}
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="text-muted">Leave</label>
                                    <div class="fw-semibold text-warning">
                                        {{ $leave }}
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="text-muted">Absence</label>
                                    <div class="fw-semibold text-danger">
                                        {{ $absent }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="mb-3"><i class="bi bi-clock-history me-2"></i>Mutation History</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Change History</th>
                                                <th>Reason</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($employee->mutations as $mutation)
                                                <tr>
                                                    <td>{{ $mutation->mutation_date->format('d M Y') }}</td>
                                                    <td>
                                                        @php
                                                            $badgeClass = match($mutation->type) {
                                                                'promotion' => 'bg-success',
                                                                'mutation' => 'bg-info',
                                                                'demotion' => 'bg-danger',
                                                                default => 'bg-secondary'
                                                            };
                                                        @endphp
                                                        <span class="badge {{ $badgeClass }}">{{ ucfirst($mutation->type) }}</span>
                                                    </td>
                                                    <td style="font-size: 0.85rem;">
                                                        @if($mutation->old_department_id !== $mutation->new_department_id)
                                                            <div><strong>Dept:</strong> {{ $mutation->oldDepartment->name ?? '-' }} <i class="bi bi-arrow-right mx-1"></i> {{ $mutation->newDepartment->name ?? '-' }}</div>
                                                        @endif
                                                        @if($mutation->old_role_id !== $mutation->new_role_id)
                                                            <div><strong>Role:</strong> {{ $mutation->oldRole->title ?? '-' }} <i class="bi bi-arrow-right mx-1"></i> {{ $mutation->newRole->title ?? '-' }}</div>
                                                        @endif
                                                        @if((float)$mutation->old_salary !== (float)$mutation->new_salary)
                                                            @if(\App\Constants\Roles::isAdmin(session('role')))
                                                                <div><strong>Salary:</strong> Rp {{ number_format($mutation->old_salary, 0, ',', '.') }} <i class="bi bi-arrow-right mx-1"></i> Rp {{ number_format($mutation->new_salary, 0, ',', '.') }}</div>
                                                            @else
                                                                <div><strong>Salary:</strong> *** <i class="bi bi-arrow-right mx-1"></i> ***</div>
                                                            @endif
                                                        @endif
                                                    </td>
                                                    <td style="font-size: 0.85rem;">{{ $mutation->reason ?: '-' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted py-3">No mutation history available.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="mb-3"><i class="bi bi-award me-2"></i>Incidents & Achievements</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-sm">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Description</th>
                                                <th>Status</th>
                                                <th>Dibuat Oleh</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($employee->incidents as $incident)
                                                <tr>
                                                    <td>{{ $incident->incident_date->format('d M Y') }}</td>
                                                    <td>
                                                        @php
                                                            $typeBadge = match(strtolower($incident->type)) {
                                                                'sp1', 'sp2', 'sp3', 'peringatan' => 'bg-danger',
                                                                'penghargaan', 'award', 'prestasi' => 'bg-success',
                                                                'mutasi', 'promosi' => 'bg-info',
                                                                default => 'bg-secondary'
                                                            };
                                                        @endphp
                                                        <span class="badge {{ $typeBadge }}">{{ strtoupper($incident->type) }}</span>
                                                    </td>
                                                    <td>
                                                        <div class="fw-bold">{{ $incident->description }}</div>
                                                        @if($incident->action_taken)
                                                            <div class="small text-muted">Aksi: {{ $incident->action_taken }}</div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php
                                                            $statusClass = match($incident->status) {
                                                                'resolved', 'closed' => 'text-success',
                                                                'investigating' => 'text-warning',
                                                                default => 'text-muted'
                                                            };
                                                        @endphp
                                                        <span class="{{ $statusClass }} small fw-bold">{{ ucfirst($incident->status) }}</span>
                                                    </td>
                                                    <td>{{ $incident->reportedBy->name ?? 'System' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-muted py-3">No incidents or awards recorded.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Back
                            </a>

                            @php
                                $role = session('role');
                                $isAdmin = \App\Constants\Roles::isAdmin($role);
                                $currentEmployeeId = session('employee_id');
                            @endphp

                            <div class="d-flex gap-2">
                                @if($isAdmin)
                                <form action="{{ route('employees.reset-device', $employee->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Apakah Anda yakin ingin mereset pengunci perangkat (Browser Fingerprint) untuk karyawan ini? Karyawan harus mendaftarkan ulang perangkat mereka saat absen berikutnya.')">
                                        <i class="bi bi-phone-vibrate"></i> Reset Device
                                    </button>
                                </form>
                                @endif

                                @if($isAdmin || $employee->id == $currentEmployeeId)
                                <a href="{{ route('employees.edit', $employee->id) }}" class="btn btn-primary">
                                    <i class="bi bi-pencil"></i> Edit Employee
                                </a>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </section>
</div>

@endsection
