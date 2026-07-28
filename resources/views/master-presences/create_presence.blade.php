@extends('layouts.dashboard')

@section('content')
<div class="page-heading mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div class="d-flex align-items-center order-2 order-md-1 mt-3 mt-md-0">
            <a href="{{ route('master-presences.index') }}" class="btn btn-secondary me-3" title="Back">
                <i class="bi bi-arrow-left fs-5"></i>
            </a>
            <div>
                <h3 class="mb-0 fw-bold">Manual Attendance Entry</h3>
                <p class="text-subtitle text-muted mb-0 mt-1">Log attendance for employees who failed to clock in.</p>
            </div>
        </div>
        <nav aria-label="breadcrumb" class="breadcrumb-header order-1 order-md-2">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('presences.index') }}" class="text-decoration-none">Presences</a></li>
                <li class="breadcrumb-item"><a href="{{ route('master-presences.index') }}" class="text-decoration-none">Master</a></li>
                <li class="breadcrumb-item active" aria-current="page">Manual Entry</li>
            </ol>
        </nav>
    </div>
</div>

<div class="card shadow-sm border-0 rounded mb-4">
    <div class="card-body p-3 p-md-5">
        <form action="{{ route('master-presences.store-presence') }}" method="POST">
            @csrf

            <div class="form-group mb-4">
                <label class="form-label fw-semibold text-secondary">Select Employee <span class="text-danger">*</span></label>
                <select name="employee_id" class="form-select rounded" required>
                    <option value="">-- Select Employee --</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->fullname }} ({{ $emp->emp_code ?? '-' }})</option>
                    @endforeach
                </select>
            </div>

            <div class="row mb-4">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="form-group mb-0">
                        <label class="form-label fw-semibold text-secondary">Attendance Date <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control rounded" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="col-md-6" id="office_location_container">
                    <div class="form-group mb-0">
                        <label class="form-label fw-semibold text-secondary">Reference Office Location <span class="text-danger">*</span></label>
                        <select name="office_location_id" id="office_select" class="form-select rounded" required>
                            <option value="">-- Select Office --</option>
                            @foreach($offices as $off)
                                <option value="{{ $off->id }}" data-lat="{{ $off->latitude }}" data-lng="{{ $off->longitude }}">
                                    {{ $off->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="form-group mb-0">
                        <label class="form-label fw-semibold text-secondary">Check In Time</label>
                        <input type="time" name="check_in_time" class="form-control rounded" value="09:00">
                        <small class="text-muted d-block mt-1">Leave as 09:00 or change as needed</small>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-0">
                        <label class="form-label fw-semibold text-secondary">Check Out Time</label>
                        <input type="time" name="check_out_time" class="form-control rounded" value="17:00">
                        <small class="text-muted d-block mt-1">Leave as 17:00 or change as needed</small>
                    </div>
                </div>
            </div>

            <div class="row bg-body-tertiary p-3 rounded mb-4 border mx-0" id="coordinates_container">
                <div class="col-sm-6 mb-3 mb-sm-0">
                    <label class="form-label fw-semibold text-secondary mb-1">Latitude</label>
                    <input type="text" name="latitude" id="input_lat" class="form-control rounded" placeholder="e.g. -6.200000">
                    <small class="text-muted d-block mt-1">Leave empty to use office default</small>
                </div>
                <div class="col-sm-6">
                    <label class="form-label fw-semibold text-secondary mb-1">Longitude</label>
                    <input type="text" name="longitude" id="input_lng" class="form-control rounded" placeholder="e.g. 106.816666">
                    <small class="text-muted d-block mt-1">Leave empty to use office default</small>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-md-6 mb-3 mb-md-0">
                    <div class="form-group mb-0">
                        <label class="form-label fw-semibold text-secondary">Work Type <span class="text-danger">*</span></label>
                        <select name="work_type" class="form-select rounded" required>
                            <option value="WFO" selected>WFO (Work From Office)</option>
                            <option value="WFH">WFH (Work From Home)</option>
                            <option value="WFA">WFA (Work From Anywhere)</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group mb-0">
                        <label class="form-label fw-semibold text-secondary">Attendance Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-select rounded" required>
                            <option value="present" selected>Present (On Time)</option>
                            <option value="late">Late</option>
                            <option value="leave">Leave (Authorized)</option>
                            <option value="absent">Absent (No Show)</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-column flex-md-row gap-3 mt-5">
                <button type="submit" class="btn btn-primary shadow-sm rounded px-5 fw-bold order-1 order-md-2">
                    <i class="bi bi-cloud-upload me-1"></i> Submit Attendance
                </button>
                <a href="{{ route('master-presences.index') }}" class="btn btn-outline-secondary shadow-sm rounded px-4 fw-semibold text-center order-2 order-md-1">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('office_select').addEventListener('change', function() {
    let selectedOption = this.options[this.selectedIndex];
    let lat = selectedOption ? (selectedOption.getAttribute('data-lat') || '') : '';
    let lng = selectedOption ? (selectedOption.getAttribute('data-lng') || '') : '';
    
    document.getElementById('input_lat').value = lat;
    document.getElementById('input_lng').value = lng;
});

const workTypeSelect = document.querySelector('select[name="work_type"]');
const officeContainer = document.getElementById('office_location_container');
const coordinatesContainer = document.getElementById('coordinates_container');
const officeSelect = document.getElementById('office_select');

function toggleOfficeFields() {
    if (workTypeSelect.value === 'WFO') {
        officeContainer.style.display = 'block';
        officeSelect.setAttribute('required', 'required');
    } else {
        officeContainer.style.display = 'none';
        officeSelect.removeAttribute('required');
        officeSelect.value = ''; // clear selection when not needed
    }
}

workTypeSelect.addEventListener('change', toggleOfficeFields);
toggleOfficeFields(); // initial setup
</script>
@endpush
@endsection