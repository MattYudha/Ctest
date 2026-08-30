@extends ('layouts.dashboard')

@section ('content')
    <div class="page-heading mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div class="d-flex align-items-center order-2 order-md-1 mt-3 mt-md-0">
                <a href="{{ route('presences.index') }}" class="btn btn-secondary me-3" title="Kembali">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>

                <div>
                    <h3 class="mb-0">Edit Presence</h3>
                    <p class="text-subtitle text-muted mb-0 mt-1">Monitor presences data.</p>
                </div>
            </div>

            <nav aria-label="breadcrumb" class="breadcrumb-header order-1 order-md-2">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('presences.index') }}">Presences</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Presence</li>
                </ol>
            </nav>
        </div>
    </div>
    <section class="section">
        <div class="card">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card-body">
                <form action="{{ route('presences.update', $presence->id) }}" method="POST">
                    @csrf
                    @method ('PUT')

                    <div class="mb-3">
                        <label for="employee_id" class="form-label">Employee</label>
                        <select name="employee_id" class="form-control" id="employee_id" required>
                            @foreach ($employees as $employee)
                                <option
                                    value="{{ $employee->id }}"
                                    {{
                                        $presence->employee_id == $employee->id
                                            ? 'selected'
                                            : ''
                                    }}
                                    >{{ $employee->fullname }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="check_in" class="form-label">Check-in Time</label>
                        <input
                            type="datetime-local"
                            name="check_in"
                            class="form-control"
                            id="check_in"
                            value="{{ old('check_in', $presence->check_in ? \Carbon\Carbon::parse($presence->check_in)->format('Y-m-d\TH:i:s') : '') }}"
                            step="1"
                            required
                        />
                    </div>

                    <div class="mb-3">
                        <label for="check_out" class="form-label">Check-out Time</label>
                        <input
                            type="datetime-local"
                            name="check_out"
                            class="form-control"
                            id="check_out"
                            value="{{ old('check_out', $presence->check_out ? \Carbon\Carbon::parse($presence->check_out)->format('Y-m-d\TH:i:s') : '') }}"
                            step="1"
                        />
                    </div>

                    <div class="mb-3 form-check mt-4">
                        <input type="hidden" name="is_late" value="0">
                        <input 
                            type="checkbox" 
                            name="is_late" 
                            class="form-check-input" 
                            id="is_late" 
                            value="1" 
                            {{ old('is_late', $presence->is_late) ? 'checked' : '' }}
                        >
                        <label class="form-check-label" for="is_late">
                            Mark as Late
                        </label>
                    </div>

                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select name="status" class="form-control" id="status" required>
                            <option value="present" {{ $presence->status == 'present' ? 'selected' : '' }}
                                >Present
                            </option>
                            <option value="absent" {{ $presence->status == 'absent' ? 'selected' : '' }}>Absent</option>
                            <option value="leave" {{ $presence->status == 'leave' ? 'selected' : '' }}>Leave</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-success">Update</button>
                </form>
            </div>
        </div>
    </section>
@endsection
