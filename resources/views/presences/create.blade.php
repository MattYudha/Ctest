@extends ('layouts.dashboard')

@section ('content')
    <div class="page-heading mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div class="d-flex align-items-center order-2 order-md-1 mt-3 mt-md-0">
                <a href="{{ route('presences.index') }}" class="btn btn-secondary me-3" title="Kembali">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>

                <div>
                    <h3 class="mb-0">New Presence</h3>
                    <p class="text-subtitle text-muted mb-0 mt-1">Create new presences data.</p>
                </div>
            </div>

            <nav aria-label="breadcrumb" class="breadcrumb-header order-1 order-md-2">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('presences.index') }}">Presences</a></li>
                    <li class="breadcrumb-item active" aria-current="page">New Presences</li>
                </ol>
            </nav>
        </div>
    </div>
    <section class="section">
        <div class="card shadow-sm border-0 rounded-3">
            @if (session('success'))
                <div class="alert bg-success bg-opacity-10 text-success border-0 m-3 rounded-3">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                </div>
            @endif

            @if (session('warning'))
                <div class="alert bg-warning bg-opacity-10 text-warning border-0 m-3 rounded-3">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('warning') }}
                </div>
            @endif

            @if (session('error'))
                <div class="alert bg-danger bg-opacity-10 text-danger border-0 m-3 rounded-3">
                    <i class="bi bi-x-circle-fill me-2"></i> {{ session('error') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert bg-danger bg-opacity-10 text-danger border-0 m-3 rounded-3">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card-body p-3 p-md-4">
                @if (session('role') == 'HR Administrator')
                    <form action="{{ route('presences.store') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="employee_id" class="form-label fw-bold">Employee</label>
                            <select name="employee_id" class="form-select rounded-3" id="employee_id" required>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->fullname }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="check_in" class="form-label fw-bold">Check In</label>
                            <input
                                type="datetime-local"
                                name="check_in"
                                class="form-control rounded-3"
                                id="check_in"
                                required
                            />
                        </div>

                        <div class="mb-3">
                            <label for="check_out" class="form-label fw-bold">Check Out</label>
                            <input
                                type="datetime-local"
                                name="check_out"
                                class="form-control rounded-3"
                                id="check_out"
                            />
                        </div>

                        <div class="mb-4">
                            <label for="status" class="form-label fw-bold">Status</label>
                            <select name="status" class="form-select rounded-3" id="status" required>
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="leave">Leave</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 rounded-3 py-2 fw-bold">
                            Save Presence
                        </button>
                    </form>

                @else
                    @if(!Auth::user()->faceProfile)
                        <div class="alert alert-warning border-0 shadow-sm rounded-3 mb-4 p-4">
                            <div class="d-flex align-items-center">
                                <!-- <div class="fs-1 text-warning me-4">
                                    <i class="bi bi-person-exclamation"></i>
                                </div> -->
                                <div>
                                    <h5 class="alert-heading fw-bold mb-1">Face Data Required</h5>
                                    <p class="mb-2">You haven't registered your face yet. Please enroll your face in the profile settings before you can submit attendance.</p>
                                    <a href="{{ route('my-profile.edit') }}" class="btn btn-warning fw-bold rounded-pill px-4 shadow-sm">
                                        <i class="bi bi-camera-fill me-1"></i> Register Face Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    @else
                        <div id="step-choose-type">
                        <h5 class="mb-3 mb-md-4 text-center text-md-start">Select Today's Work Type</h5>
                        <div class="row g-3 g-md-4">
                            {{-- WFO Card --}}
                            <div class="col-12 col-md-4">
                                <div
                                    class="card h-100 shadow-sm border border-primary border-opacity-25 bg-primary bg-opacity-10 work-type-card"
                                    onclick="selectWorkType('WFO')"
                                >
                                    <div
                                        class="card-body d-flex flex-column justify-content-center align-items-center text-center p-4"
                                    >
                                        <div
                                            class="icon-wrapper bg-primary rounded-3 text-white shadow-sm mb-3"
                                            style="width: 40px; height: 40px"
                                        >
                                            <i class="bi bi-building fs-4"></i>
                                        </div>

                                        <h5 class="fw-bold text-primary mb-2">Work From Office</h5>

                                        <span class="badge bg-primary rounded-3 px-3 py-2 mb-3"> WFO </span>

                                        <p class="text-muted small mb-0">GPS + IP Network + Face + Fingerprint</p>
                                    </div>
                                </div>
                            </div>
                            {{-- WFH Card --}}
                            <div class="col-12 col-md-4">
                                <div
                                    class="card h-100 shadow-sm border border-success border-opacity-25 bg-success bg-opacity-10 work-type-card"
                                    onclick="selectWorkType('WFH')"
                                >
                                    <div
                                        class="card-body d-flex flex-column justify-content-center align-items-center text-center p-4"
                                    >
                                        <div
                                            class="icon-wrapper bg-success rounded-3 text-white shadow-sm mb-3"
                                            style="width: 40px; height: 40px"
                                        >
                                            <i class="bi bi-house-door fs-4"></i>
                                        </div>

                                        <h5 class="fw-bold text-success mb-2">Work From Home</h5>

                                        <span class="badge bg-success rounded-3 px-3 py-2 mb-3"> WFH </span>

                                        <p class="text-muted small mb-0">GPS + Face + Fingerprint</p>
                                    </div>
                                </div>
                            </div>
                            {{-- WFA Card --}}
                            <div class="col-12 col-md-4">
                                <div
                                    class="card h-100 shadow-sm border border-info border-opacity-25 bg-info bg-opacity-10 work-type-card"
                                    onclick="selectWorkType('WFA')"
                                >
                                    <div
                                        class="card-body d-flex flex-column justify-content-center align-items-center text-center p-4"
                                    >
                                        <div
                                            class="icon-wrapper bg-info rounded-3 text-white shadow-sm mb-3"
                                            style="width: 40px; height: 40px"
                                        >
                                            <i class="bi bi-laptop fs-4"></i>
                                        </div>

                                        <h5 class="fw-bold text-info mb-2">Work From Anywhere</h5>

                                        <span class="badge bg-info rounded-3 px-3 py-2 mb-3"> WFA </span>

                                        <p class="text-muted small mb-0">GPS + Face + Fingerprint</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- WFO FORM --}}
                    <div id="form-wfo" style="display: none">
                        <div
                            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 pb-2 border-bottom gap-2"
                        >
                            <h5 class="mb-0 d-flex align-items-center">
                                <span class="badge bg-primary rounded-3 me-2">WFO</span>
                                <span class="fs-6 fs-md-5">Work From Office</span>
                            </h5>
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-secondary rounded-3 px-3 w-100 w-md-auto"
                                onclick="backToChooseType()"
                            >
                                <i class="bi bi-arrow-left"></i> Change Type
                            </button>
                        </div>

                        <form action="{{ route('presences.store') }}" method="POST" id="form-wfo-submit">
                            @csrf
                            <input type="hidden" name="work_type" value="WFO" />
                            <input type="hidden" name="fingerprint" id="fingerprint-wfo" />
                            <input type="hidden" name="is_mobile" id="is_mobile-wfo" />
                            <input type="hidden" name="latitude" id="latitude-wfo" />
                            <input type="hidden" name="longitude" id="longitude-wfo" />
                            <input type="hidden" name="accuracy" id="accuracy-wfo" />
                            <input type="hidden" name="photo_data" id="photo_data-wfo" />

                            <div class="mb-3">
                                <label class="form-label fw-bold small mb-1"
                                    ><i class="bi bi-building text-primary"></i> WFO Office Site</label
                                >
                                <select
                                    class="form-select form-select-sm rounded-3 border-primary border-opacity-25"
                                    name="office_location_id"
                                    id="office-location-wfo"
                                    {{ !empty($wfoOfficeLocations) ? 'required' : 'disabled' }}
                                >
                                    <option value="">-- Select Nearest Office --</option>
                                    @forelse ($wfoOfficeLocations as $officeLocation)
                                        <option
                                            value="{{ $officeLocation['id'] }}"
                                            {{
                                                (string) old('office_location_id', $selectedWfoOfficeLocation['id'] ?? '') ===
                                                (string) $officeLocation['id']
                                                    ? 'selected'
                                                    : ''
                                            }}
                                        >
                                            {{ $officeLocation['name'] }}
                                        </option>
                                    @empty
                                        <option value="">No active office location</option>
                                    @endforelse
                                </select>
                                <small class="text-muted mt-1 d-block" style="font-size: 0.75rem"
                                    >Select an office site to match your GPS Distance and IP Network.</small
                                >
                            </div>

                            <div
                                class="alert rounded-3 border-end-0 border-top-0 border-bottom-0 border-start border-4 border-primary bg-primary bg-opacity-10 text-body mb-3 shadow-sm p-3"
                            >
                                <strong class="text-primary d-block mb-1 small"
                                    ><i class="bi bi-clipboard-check"></i> WFO Validation:</strong
                                >
                                <span style="font-size: 0.8rem"
                                    >Select office site + GPS + IP Network + Face Verification + Fingerprint</span
                                >
                                <hr class="my-2 border-primary border-opacity-25" />
                                <div style="font-size: 0.8rem">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="opacity-75">Location:</span>
                                        <strong id="wfo-office-name" class="text-end">{{
                                            $selectedWfoOfficeLocation['name'] ??
                                                'No active office location'
                                        }}</strong>
                                    </div>
                                    <div
                                        class="d-flex justify-content-between mb-1"
                                        id="wfo-office-address-wrapper"
                                        @if (empty($selectedWfoOfficeLocation['address']))
                                            style="display: none"
                                        @endif
                                    >
                                        <span class="opacity-75">Address:</span>
                                        <span
                                            id="wfo-office-address"
                                            class="text-end text-truncate ms-2"
                                            style="max-width: 60%"
                                            >{{ $selectedWfoOfficeLocation['address'] ?? '' }}</span
                                        >
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="opacity-75">Radius:</span>
                                        <strong
                                            ><span
                                                id="wfo-office-radius"
                                                >{{ $selectedWfoOfficeLocation['radius'] ?? 0 }}</span
                                            >
                                            meters</strong
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-6">
                                    <div class="card h-100 bg-secondary bg-opacity-10 border-0 shadow-sm rounded-3">
                                        <div class="card-body p-3">
                                            <h6 class="fw-bold fs-6 mb-2">
                                                <i class="bi bi-shield-check text-primary"></i> Network Security
                                            </h6>
                                            <div id="network-status-wfo" class="mb-2">
                                                <span
                                                    class="badge bg-warning bg-opacity-10 text-warning rounded-3 px-2 py-1 w-100 text-start"
                                                    >⏳ Checking Network...</span
                                                >
                                            </div>
                                            <div class="mb-2 small" id="network-details-wfo" style="display: none">
                                                <div
                                                    class="d-flex justify-content-between align-items-center mb-1 border-bottom border-secondary border-opacity-10 pb-1"
                                                >
                                                    <span class="text-muted">Device IP:</span>
                                                    <span
                                                        id="ip-display-wfo"
                                                        class="badge bg-secondary bg-opacity-10 text-body border rounded-2"
                                                        >{{ request()->ip() }}</span
                                                    >
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-muted">Status:</span>
                                                    <span id="ip-status-text-wfo" class="text-end">-</span>
                                                </div>
                                            </div>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary rounded-3 px-3 w-100 mt-2"
                                                onclick="refreshNetwork(true)"
                                            >
                                                <i class="bi bi-arrow-clockwise"></i> Refresh Network
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <div class="card h-100 bg-secondary bg-opacity-10 border-0 shadow-sm rounded-3">
                                        <div class="card-body p-3">
                                            <h6 class="fw-bold fs-6 mb-2">
                                                <i class="bi bi-geo-alt text-primary"></i> GPS Location
                                            </h6>
                                            <div id="gps-status-wfo" class="mb-2">
                                                <span
                                                    class="badge bg-warning bg-opacity-10 text-warning rounded-3 px-2 py-1 w-100 text-start"
                                                    >⏳ Loading GPS...</span
                                                >
                                            </div>
                                            <div class="mb-2 small">
                                                <div
                                                    class="d-flex justify-content-between mb-1 border-bottom border-secondary border-opacity-10 pb-1"
                                                >
                                                    <span class="text-muted">Latitude:</span>
                                                    <span id="lat-display-wfo" class="fw-medium text-break text-end"
                                                        >-</span
                                                    >
                                                </div>
                                                <div
                                                    class="d-flex justify-content-between mb-1 border-bottom border-secondary border-opacity-10 pb-1"
                                                >
                                                    <span class="text-muted">Longitude:</span>
                                                    <span id="lon-display-wfo" class="fw-medium text-break text-end"
                                                        >-</span
                                                    >
                                                </div>
                                                <div
                                                    class="d-flex justify-content-between mb-1 border-bottom border-secondary border-opacity-10 pb-1"
                                                >
                                                    <span class="text-muted">Distance:</span>
                                                    <span class="fw-bold text-end"
                                                        ><span id="dist-display-wfo">-</span> m</span
                                                    >
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span class="text-muted">Ref:</span>
                                                    <span
                                                        id="wfo-distance-office-name"
                                                        class="fw-medium text-end ms-2 text-truncate"
                                                        style="max-width: 70%"
                                                        >{{ $selectedWfoOfficeLocation['name'] ?? '-' }}</span
                                                    >
                                                </div>
                                            </div>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary rounded-3 px-3 w-100 mt-1"
                                                onclick="refreshGPS()"
                                            >
                                                <i class="bi bi-arrow-clockwise"></i> Refresh GPS
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card bg-secondary bg-opacity-10 border-0 shadow-sm rounded-3 mb-3">
                                <div class="card-body p-3">
                                    <h6 class="fw-bold fs-6 mb-2">
                                        <i class="bi bi-camera text-primary"></i> Face Verification
                                    </h6>

                                    <div
                                        class="alert bg-primary bg-opacity-10 text-body py-2 px-3 mb-3 border-0 rounded-3"
                                        style="font-size: 0.75rem"
                                    >
                                        <i class="bi bi-info-circle-fill text-primary me-1"></i> A photo will be
                                        automatically taken once your face is verified.
                                    </div>

                                    <div
                                        id="video-container-wfo"
                                        class="position-relative text-center bg-dark rounded-3 overflow-hidden mb-3 shadow-sm mx-auto"
                                        style="width: 100%; max-width: 320px; border: 1px solid var(--bs-border-color)"
                                    >
                                        <video
                                            id="video-wfo"
                                            autoplay
                                            muted
                                            playsinline
                                            style="
                                                width: 100%;
                                                max-height: 280px;
                                                object-fit: cover;
                                                transform: scaleX(-1);
                                                display: block;
                                            "
                                        ></video>
                                        <canvas id="overlay-wfo" class="position-absolute top-0 start-0 pe-none" style="width: 100%; max-height: 280px; object-fit: cover; transform: scaleX(-1); display: block;"></canvas>
                                    </div>

                                    <div id="preview-container-wfo" class="text-center mb-3" style="display: none">
                                        <p class="text-success fw-bold small mb-2"><i class="bi bi-check-circle-fill"></i> Photo captured successfully</p>
                                        <img
                                            id="preview-img-wfo"
                                            class="rounded-3 shadow-sm border border-3 border-success mb-3 w-100"
                                            style="max-width: 320px; max-height: 280px; object-fit: cover"
                                        />
                                        <div>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-warning rounded-3 px-4 shadow-sm w-100 w-md-auto"
                                                onclick="retakePhoto('wfo')"
                                            >
                                                <i class="bi bi-arrow-counterclockwise"></i> Retake Photo
                                            </button>
                                        </div>
                                    </div>

                                    <div id="face-status-wfo" class="text-center">
                                        <span
                                            class="badge bg-secondary bg-opacity-10 text-body border rounded-3 px-3 py-2 w-100 text-wrap lh-sm text-md-center text-start"
                                            >Waiting for Camera...</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-3 bg-secondary bg-opacity-10 border-0 shadow-sm rounded-3">
                                <div class="card-body text-center p-3">
                                    <div id="fingerprint-status-wfo" class="mb-0">
                                        <span
                                            class="badge bg-warning bg-opacity-10 text-warning rounded-3 px-3 py-2 w-100 text-wrap lh-sm text-md-center text-start"
                                            >Loading Fingerprint...</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <button
                                type="submit"
                                id="btn-submit-wfo"
                                class="btn btn-primary btn-lg w-100 rounded-3 shadow-sm fw-bold py-2 mb-4"
                                disabled
                            >
                                <i class="bi bi-check-circle me-1"></i> Submit Presence (WFO)
                            </button>
                        </form>
                    </div>
                    {{-- WFH FORM --}}
                    <div id="form-wfh" style="display: none">
                        <div
                            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 pb-2 border-bottom gap-2"
                        >
                            <h5 class="mb-0 d-flex align-items-center">
                                <span class="badge bg-success rounded-3 me-2">WFH</span>
                                <span class="fs-6 fs-md-5">Work From Home</span>
                            </h5>
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-secondary rounded-3 px-3 w-100 w-md-auto"
                                onclick="backToChooseType()"
                            >
                                <i class="bi bi-arrow-left"></i> Change Type
                            </button>
                        </div>

                        <form action="{{ route('presences.store') }}" method="POST" id="form-wfh-submit">
                            @csrf
                            <input type="hidden" name="work_type" value="WFH" />
                            <input type="hidden" name="fingerprint" id="fingerprint-wfh" />
                            <input type="hidden" name="is_mobile" id="is_mobile-wfh" />
                            <input type="hidden" name="latitude" id="latitude-wfh" />
                            <input type="hidden" name="longitude" id="longitude-wfh" />
                            <input type="hidden" name="accuracy" id="accuracy-wfh" />
                            <input type="hidden" name="photo_data" id="photo_data-wfh" />

                            <div
                                class="alert rounded-3 border-end-0 border-top-0 border-bottom-0 border-start border-4 border-success bg-success bg-opacity-10 text-body mb-3 shadow-sm p-3"
                            >
                                <strong class="text-success d-block mb-1 small"
                                    ><i class="bi bi-clipboard-check"></i> WFH Validation:</strong
                                >
                                <span style="font-size: 0.8rem">GPS + Face Verification + Fingerprint</span>
                            </div>

                            <div class="card mb-3 bg-secondary bg-opacity-10 border-0 shadow-sm rounded-3">
                                <div class="card-body p-3">
                                    <h6 class="fw-bold fs-6 mb-2">
                                        <i class="bi bi-geo-alt text-success"></i> GPS Location
                                    </h6>
                                    <div id="gps-status-wfh" class="mb-2">
                                        <span
                                            class="badge bg-warning bg-opacity-10 text-warning rounded-3 px-2 py-1 w-100 text-start"
                                            >⏳ Loading GPS...</span
                                        >
                                    </div>
                                    <div class="mb-2 small border-bottom border-secondary border-opacity-10 pb-2">
                                        <div class="d-flex flex-column mb-1">
                                            <span class="text-muted">Latitude:</span>
                                            <span id="lat-display-wfh" class="fw-medium text-break">-</span>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="text-muted">Longitude:</span>
                                            <span id="lon-display-wfh" class="fw-medium text-break">-</span>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-success rounded-3 px-4 w-100 w-md-auto mt-1"
                                        onclick="refreshGPSFree('wfh')"
                                    >
                                        <i class="bi bi-arrow-clockwise"></i> Refresh GPS
                                    </button>
                                </div>
                            </div>

                            <div class="card bg-secondary bg-opacity-10 border-0 shadow-sm rounded-3 mb-3">
                                <div class="card-body p-3">
                                    <h6 class="fw-bold fs-6 mb-2">
                                        <i class="bi bi-camera text-success"></i> Face Verification
                                    </h6>

                                    <div
                                        class="alert bg-success bg-opacity-10 text-body py-2 px-3 mb-3 border-0 rounded-3"
                                        style="font-size: 0.75rem"
                                    >
                                        <i class="bi bi-info-circle-fill text-success me-1"></i> A photo will be
                                        automatically taken once your face is verified.
                                    </div>

                                    <div
                                        id="video-container-wfh"
                                        class="position-relative text-center bg-dark rounded-3 overflow-hidden mb-3 shadow-sm mx-auto"
                                        style="width: 100%; max-width: 320px; border: 1px solid var(--bs-border-color)"
                                    >
                                        <video
                                            id="video-wfh"
                                            autoplay
                                            muted
                                            playsinline
                                            style="
                                                width: 100%;
                                                max-height: 280px;
                                                object-fit: cover;
                                                transform: scaleX(-1);
                                                display: block;
                                            "
                                        ></video>
                                        <canvas id="overlay-wfh" class="position-absolute top-0 start-0 pe-none" style="width: 100%; max-height: 280px; object-fit: cover; transform: scaleX(-1); display: block;"></canvas>
                                    </div>

                                    <div id="preview-container-wfh" class="text-center mb-3" style="display: none">
                                        <p class="text-success fw-bold small mb-2"><i class="bi bi-check-circle-fill"></i> Photo captured successfully</p>
                                        <img
                                            id="preview-img-wfh"
                                            class="rounded-3 shadow-sm border border-3 border-success mb-3 w-100"
                                            style="max-width: 320px; max-height: 280px; object-fit: cover"
                                        />
                                        <div>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-warning rounded-3 px-4 shadow-sm w-100 w-md-auto"
                                                onclick="retakePhoto('wfh')"
                                            >
                                                <i class="bi bi-arrow-counterclockwise"></i> Retake Photo
                                            </button>
                                        </div>
                                    </div>

                                    <div id="face-status-wfh" class="text-center">
                                        <span
                                            class="badge bg-secondary bg-opacity-10 text-body border rounded-3 px-3 py-2 w-100 text-wrap lh-sm text-md-center text-start"
                                            >Waiting for Camera...</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-3 bg-secondary bg-opacity-10 border-0 shadow-sm rounded-3">
                                <div class="card-body text-center p-3">
                                    <div id="fingerprint-status-wfh" class="mb-0">
                                        <span
                                            class="badge bg-warning bg-opacity-10 text-warning rounded-3 px-3 py-2 w-100 text-wrap lh-sm text-md-center text-start"
                                            >Loading Fingerprint...</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <button
                                type="submit"
                                id="btn-submit-wfh"
                                class="btn btn-success btn-lg w-100 rounded-3 shadow-sm fw-bold py-2 mb-4"
                                disabled
                            >
                                <i class="bi bi-check-circle me-1"></i> Submit Presence (WFH)
                            </button>
                        </form>
                    </div>
                    {{-- WFA FORM --}}
                    <div id="form-wfa" style="display: none">
                        <div
                            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 pb-2 border-bottom gap-2"
                        >
                            <h5 class="mb-0 d-flex align-items-center">
                                <span class="badge bg-info rounded-3 me-2">WFA</span>
                                <span class="fs-6 fs-md-5">Work From Anywhere</span>
                            </h5>
                            <button
                                type="button"
                                class="btn btn-sm btn-outline-secondary rounded-3 px-3 w-100 w-md-auto"
                                onclick="backToChooseType()"
                            >
                                <i class="bi bi-arrow-left"></i> Change Type
                            </button>
                        </div>

                        <form action="{{ route('presences.store') }}" method="POST" id="form-wfa-submit">
                            @csrf
                            <input type="hidden" name="work_type" value="WFA" />
                            <input type="hidden" name="fingerprint" id="fingerprint-wfa" />
                            <input type="hidden" name="is_mobile" id="is_mobile-wfa" />
                            <input type="hidden" name="latitude" id="latitude-wfa" />
                            <input type="hidden" name="longitude" id="longitude-wfa" />
                            <input type="hidden" name="accuracy" id="accuracy-wfa" />
                            <input type="hidden" name="photo_data" id="photo_data-wfa" />

                            <div
                                class="alert rounded-3 border-end-0 border-top-0 border-bottom-0 border-start border-4 border-info bg-info bg-opacity-10 text-body mb-3 shadow-sm p-3"
                            >
                                <strong class="text-info d-block mb-1 small"
                                    ><i class="bi bi-clipboard-check"></i> WFA Validation:</strong
                                >
                                <span style="font-size: 0.8rem">GPS + Face Verification + Fingerprint</span>
                            </div>

                            <div class="card mb-3 bg-secondary bg-opacity-10 border-0 shadow-sm rounded-3">
                                <div class="card-body p-3">
                                    <h6 class="fw-bold fs-6 mb-2">
                                        <i class="bi bi-geo-alt text-info"></i> GPS Location
                                    </h6>
                                    <div id="gps-status-wfa" class="mb-2">
                                        <span
                                            class="badge bg-warning bg-opacity-10 text-warning rounded-3 px-2 py-1 w-100 text-start"
                                            >⏳ Loading GPS...</span
                                        >
                                    </div>
                                    <div class="mb-2 small border-bottom border-secondary border-opacity-10 pb-2">
                                        <div class="d-flex flex-column mb-1">
                                            <span class="text-muted">Latitude:</span>
                                            <span id="lat-display-wfa" class="fw-medium text-break">-</span>
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="text-muted">Longitude:</span>
                                            <span id="lon-display-wfa" class="fw-medium text-break">-</span>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-info rounded-3 px-4 w-100 w-md-auto mt-1"
                                        onclick="refreshGPSFree('wfa')"
                                    >
                                        <i class="bi bi-arrow-clockwise"></i> Refresh GPS
                                    </button>
                                </div>
                            </div>

                            <div class="card bg-secondary bg-opacity-10 border-0 shadow-sm rounded-3 mb-3">
                                <div class="card-body p-3">
                                    <h6 class="fw-bold fs-6 mb-2">
                                        <i class="bi bi-camera text-info"></i> Face Verification
                                    </h6>

                                    <div
                                        class="alert bg-info bg-opacity-10 text-body py-2 px-3 mb-3 border-0 rounded-3"
                                        style="font-size: 0.75rem"
                                    >
                                        <i class="bi bi-info-circle-fill text-info me-1"></i> A photo will be
                                        automatically taken once your face is verified.
                                    </div>

                                    <div
                                        id="video-container-wfa"
                                        class="position-relative text-center bg-dark rounded-3 overflow-hidden mb-3 shadow-sm mx-auto"
                                        style="width: 100%; max-width: 320px; border: 1px solid var(--bs-border-color)"
                                    >
                                        <video
                                            id="video-wfa"
                                            autoplay
                                            muted
                                            playsinline
                                            style="
                                                width: 100%;
                                                max-height: 280px;
                                                object-fit: cover;
                                                transform: scaleX(-1);
                                                display: block;
                                            "
                                        ></video>
                                        <canvas id="overlay-wfa" class="position-absolute top-0 start-0 pe-none" style="width: 100%; max-height: 280px; object-fit: cover; transform: scaleX(-1); display: block;"></canvas>
                                    </div>

                                    <div id="preview-container-wfa" class="text-center mb-3" style="display: none">
                                        <p class="text-success fw-bold small mb-2"><i class="bi bi-check-circle-fill"></i> Photo captured successfully</p>
                                        <img
                                            id="preview-img-wfa"
                                            class="rounded-3 shadow-sm border border-3 border-success mb-3 w-100"
                                            style="max-width: 320px; max-height: 280px; object-fit: cover"
                                        />
                                        <div>
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-warning rounded-3 px-4 shadow-sm w-100 w-md-auto"
                                                onclick="retakePhoto('wfa')"
                                            >
                                                <i class="bi bi-arrow-counterclockwise"></i> Retake Photo
                                            </button>
                                        </div>
                                    </div>

                                    <div id="face-status-wfa" class="text-center">
                                        <span
                                            class="badge bg-secondary bg-opacity-10 text-body border rounded-3 px-3 py-2 w-100 text-wrap lh-sm text-md-center text-start"
                                            >Waiting for Camera...</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <div class="card mb-3 bg-secondary bg-opacity-10 border-0 shadow-sm rounded-3">
                                <div class="card-body text-center p-3">
                                    <div id="fingerprint-status-wfa" class="mb-0">
                                        <span
                                            class="badge bg-warning bg-opacity-10 text-warning rounded-3 px-3 py-2 w-100 text-wrap lh-sm text-md-center text-start"
                                            >Loading Fingerprint...</span
                                        >
                                    </div>
                                </div>
                            </div>

                            <button
                                type="submit"
                                id="btn-submit-wfa"
                                class="btn btn-info btn-lg w-100 rounded-3 shadow-sm fw-bold text-body py-2 mb-4"
                                disabled
                            >
                                <i class="bi bi-check-circle me-1"></i> Submit Presence (WFA)
                            </button>
                        </form>
                    </div>

                @endif
            </div>
        </div>
    </section>
    <script src="{{ asset('vendor/fingerprintjs/fp.min.js') }}"></script>
    <script src="{{ asset('vendor/face-api/face-api.min.js') }}"></script>
    <script>
        const userIp = '{{ request()->ip() }}';
        const wfoOfficeLocations = @json ($wfoOfficeLocations);

        const modeState = {
            wfo: { gps: false, fingerprint: false, network: false, face: false },
            wfh: { gps: false, fingerprint: false, face: false },
            wfa: { gps: false, fingerprint: false, face: false },
        };

        let currentWorkType = null;
        let gpsWatchId = null;
        let gpsWatchIds = { wfh: null, wfa: null };
        let videoStream = null;
        let videoStreams = { wfh: null, wfa: null };
        let faceDetectionInterval = null;
        let faceDetectionIntervals = { wfh: null, wfa: null };

        // ============ WFO OFFICE & NETWORK LOGIC ============
        function getSelectedWfoOffice() {
            const select = document.getElementById('office-location-wfo');
            if (!select || !select.value) {
                return null;
            }
            const selectedId = Number(select.value);
            return wfoOfficeLocations.find((officeLocation) => Number(officeLocation.id) === selectedId) ?? null;
        }

        function refreshNetwork(isManualRefresh = false) {
            const office = getSelectedWfoOffice();
            const statusEl = document.getElementById('network-status-wfo');
            const detailsEl = document.getElementById('network-details-wfo');
            const textEl = document.getElementById('ip-status-text-wfo');

            if (isManualRefresh) {
                statusEl.style.display = 'block';
                statusEl.innerHTML =
                    '<span class="badge bg-warning bg-opacity-10 text-warning rounded-3 px-3 py-2">⏳ Checking Network...</span>';
                detailsEl.style.display = 'none';
                modeState.wfo.network = false;
                checkReady('wfo');
            }

            setTimeout(
                () => {
                    statusEl.style.display = 'none';
                    detailsEl.style.display = 'block';

                    if (!office) {
                        textEl.innerHTML =
                            '<span class="text-warning small fw-medium"><i class="bi bi-exclamation-triangle"></i> Select office site first</span>';
                        modeState.wfo.network = false;
                    } else {
                        const allowedIps = office.allowed_ips || [];

                        if (allowedIps.length === 0) {
                            textEl.innerHTML =
                                '<span class="text-info small fw-medium"><i class="bi bi-info-circle"></i> Office does not restrict IPs</span>';
                            modeState.wfo.network = true;
                        } else if (allowedIps.includes(userIp)) {
                            textEl.innerHTML =
                                '<span class="text-success fw-bold small"><i class="bi bi-check-circle-fill"></i> Verified (Secure)</span>';
                            modeState.wfo.network = true;
                        } else {
                            textEl.innerHTML =
                                '<span class="text-danger fw-bold small"><i class="bi bi-x-circle-fill"></i> Unknown Network</span>';
                            modeState.wfo.network = false;
                        }
                    }
                    checkReady('wfo');
                },
                isManualRefresh ? 800 : 0,
            );
        }

        function renderWfoOfficeDetails() {
            const office = getSelectedWfoOffice();
            const officeName = office ? office.name : 'No active office location';
            const officeAddress = office?.address ?? '';
            const officeRadius = office?.radius ?? 0;

            document.getElementById('wfo-office-name').textContent = officeName;
            document.getElementById('wfo-office-radius').textContent = officeRadius;
            document.getElementById('wfo-distance-office-name').textContent = office ? office.name : '-';

            const addressWrapper = document.getElementById('wfo-office-address-wrapper');
            const addressEl = document.getElementById('wfo-office-address');
            if (officeAddress) {
                addressEl.textContent = officeAddress;
                addressWrapper.style.display = 'flex'; // Adjusted for flex layout
            } else {
                addressEl.textContent = '';
                addressWrapper.style.display = 'none';
            }

            refreshNetwork(true);

            const latitude = document.getElementById('latitude-wfo').value;
            const longitude = document.getElementById('longitude-wfo').value;
            const accuracy = document.getElementById('accuracy-wfo').value;

            if (office && latitude && longitude) {
                updateWfoDistanceStatus(Number(latitude), Number(longitude), Number(accuracy || 0));
            } else if (!office) {
                document.getElementById('gps-status-wfo').innerHTML =
                    '<span class="badge bg-danger bg-opacity-10 text-danger rounded-3 px-3 py-2">❌ No active office location</span>';
                document.getElementById('dist-display-wfo').textContent = '-';
                modeState.wfo.gps = false;
                checkReady('wfo');
            }
        }

        function calculateDistanceMeters(lat, lon, officeLat, officeLon) {
            const distDegrees = Math.sqrt(Math.pow(lat - officeLat, 2) + Math.pow(lon - officeLon, 2));
            return Math.round(distDegrees * 111320);
        }

        function updateWfoDistanceStatus(lat, lon, acc) {
            const office = getSelectedWfoOffice();
            if (!office) {
                document.getElementById('gps-status-wfo').innerHTML =
                    '<span class="badge bg-danger bg-opacity-10 text-danger rounded-3 px-3 py-2">❌ No active office location</span>';
                document.getElementById('dist-display-wfo').textContent = '-';
                modeState.wfo.gps = false;
                checkReady('wfo');
                return;
            }

            document.getElementById('latitude-wfo').value = lat;
            document.getElementById('longitude-wfo').value = lon;
            document.getElementById('accuracy-wfo').value = acc;

            document.getElementById('lat-display-wfo').textContent = Number(lat).toFixed(6);
            document.getElementById('lon-display-wfo').textContent = Number(lon).toFixed(6);

            const distMeters = calculateDistanceMeters(
                Number(lat),
                Number(lon),
                Number(office.latitude),
                Number(office.longitude),
            );
            document.getElementById('dist-display-wfo').textContent = distMeters;

            if (distMeters <= Number(office.radius)) {
                document.getElementById('gps-status-wfo').innerHTML =
                    '<span class="badge bg-success bg-opacity-10 text-success rounded-3 px-3 py-2">✅ GPS OK (' +
                    distMeters +
                    'm)</span>';
                modeState.wfo.gps = true;
            } else {
                document.getElementById('gps-status-wfo').innerHTML =
                    '<span class="badge bg-danger bg-opacity-10 text-danger rounded-3 px-3 py-2">❌ Too far (' +
                    distMeters +
                    'm)</span>';
                modeState.wfo.gps = false;
            }

            checkReady('wfo');
        }

        // ============ UI NAVIGATION ============
        function selectWorkType(type) {
            currentWorkType = type;
            document.getElementById('step-choose-type').style.display = 'none';
            document.getElementById('form-' + type.toLowerCase()).style.display = 'block';

            if (type === 'WFO') {
                initWFO();
            } else if (type === 'WFH') {
                initWFH();
            } else if (type === 'WFA') {
                initWFA();
            }
        }

        function backToChooseType() {
            if (gpsWatchId) {
                navigator.geolocation.clearWatch(gpsWatchId);
                gpsWatchId = null;
            }

            ['wfh', 'wfa'].forEach((mode) => {
                if (gpsWatchIds[mode]) {
                    navigator.geolocation.clearWatch(gpsWatchIds[mode]);
                    gpsWatchIds[mode] = null;
                }
                if (videoStreams[mode]) {
                    videoStreams[mode].getTracks().forEach((track) => track.stop());
                    videoStreams[mode] = null;
                }
                if (faceDetectionIntervals[mode]) {
                    clearInterval(faceDetectionIntervals[mode]);
                    faceDetectionIntervals[mode] = null;
                }
            });

            if (videoStream) {
                videoStream.getTracks().forEach((track) => track.stop());
                videoStream = null;
            }
            if (faceDetectionInterval) {
                clearInterval(faceDetectionInterval);
                faceDetectionInterval = null;
            }

            document.getElementById('form-wfo').style.display = 'none';
            document.getElementById('form-wfh').style.display = 'none';
            document.getElementById('form-wfa').style.display = 'none';
            document.getElementById('step-choose-type').style.display = 'block';
            currentWorkType = null;
        }

        // ============ GENERIC READY CHECK ============
        function checkReady(mode) {
            const state = modeState[mode];
            let isReady = false;

            if (mode === 'wfo') {
                isReady = state.gps && state.fingerprint && state.network && state.face;
            } else {
                isReady = state.gps && state.fingerprint && state.face;
            }

            const button = document.getElementById('btn-submit-' + mode);
            if (button) {
                button.disabled = !isReady;
            }
        }

        // ============ WFO MODE ============
        async function initWFO() {
            document.getElementById('network-status-wfo').style.display = 'block';
            document.getElementById('network-details-wfo').style.display = 'none';

            initFingerprintForMode('wfo');
            renderWfoOfficeDetails();
            startGPSForWFO();
            setTimeout(() => initFaceDetectionForMode('wfo'), 1000);
        }

        function startGPSForWFO() {
            const office = getSelectedWfoOffice();
            if (!office) {
                renderWfoOfficeDetails();
                return;
            }

            document.getElementById('gps-status-wfo').innerHTML =
                '<span class="badge bg-warning bg-opacity-10 text-warning rounded-3 px-3 py-2">🔍 Locating GPS...</span>';

            if (!navigator.geolocation) {
                document.getElementById('gps-status-wfo').innerHTML =
                    '<span class="badge bg-danger bg-opacity-10 text-danger rounded-3 px-3 py-2">❌ GPS is not supported</span>';
                return;
            }

            const onSuccess = (position) => {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                const acc = position.coords.accuracy;
                updateWfoDistanceStatus(lat, lon, acc);
            };

            const onError = (error) => {
                let msg = 'GPS Error: ';
                if (error.code === error.PERMISSION_DENIED) msg += 'Permission denied';
                else if (error.code === error.POSITION_UNAVAILABLE) msg += 'Unavailable';
                else if (error.code === error.TIMEOUT) msg += 'Timeout';

                document.getElementById('gps-status-wfo').innerHTML =
                    '<span class="badge bg-danger bg-opacity-10 text-danger rounded-3 px-3 py-2">❌ ' + msg + '</span>';
            };

            gpsWatchId = navigator.geolocation.watchPosition(onSuccess, onError, {
                enableHighAccuracy: true,
                timeout: 30000,
                maximumAge: 5000,
            });
        }

        function refreshGPS() {
            if (gpsWatchId) {
                navigator.geolocation.clearWatch(gpsWatchId);
            }
            modeState.wfo.gps = false;
            startGPSForWFO();
        }

        // ============ WFH MODE ============
        async function initWFH() {
            initFingerprintForMode('wfh');
            startGPSFree('wfh');
            setTimeout(() => initFaceDetectionForMode('wfh'), 1000);
        }

        // ============ WFA MODE ============
        async function initWFA() {
            initFingerprintForMode('wfa');
            startGPSFree('wfa');
            setTimeout(() => initFaceDetectionForMode('wfa'), 1000);
        }

        // ============ GPS FREE (WFH/WFA) ============
        function startGPSFree(mode) {
            document.getElementById('gps-status-' + mode).innerHTML =
                '<span class="badge bg-warning bg-opacity-10 text-warning rounded-3 px-3 py-2">🔍 Locating GPS...</span>';

            if (!navigator.geolocation) {
                document.getElementById('gps-status-' + mode).innerHTML =
                    '<span class="badge bg-danger bg-opacity-10 text-danger rounded-3 px-3 py-2">❌ GPS is not supported</span>';
                return;
            }

            const onSuccess = (position) => {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                const acc = position.coords.accuracy;

                document.getElementById('latitude-' + mode).value = lat;
                document.getElementById('longitude-' + mode).value = lon;
                document.getElementById('accuracy-' + mode).value = acc;

                document.getElementById('lat-display-' + mode).textContent = lat.toFixed(6);
                document.getElementById('lon-display-' + mode).textContent = lon.toFixed(6);

                document.getElementById('gps-status-' + mode).innerHTML =
                    '<span class="badge bg-success bg-opacity-10 text-success rounded-3 px-3 py-2">✅ GPS OK</span>';
                modeState[mode].gps = true;
                checkReady(mode);
            };

            const onError = (error) => {
                let msg = 'GPS Error: ';
                if (error.code === error.PERMISSION_DENIED) msg += 'Permission denied';
                else if (error.code === error.POSITION_UNAVAILABLE) msg += 'Unavailable';
                else if (error.code === error.TIMEOUT) msg += 'Timeout';

                document.getElementById('gps-status-' + mode).innerHTML =
                    '<span class="badge bg-danger bg-opacity-10 text-danger rounded-3 px-3 py-2">❌ ' + msg + '</span>';
            };

            gpsWatchIds[mode] = navigator.geolocation.watchPosition(onSuccess, onError, {
                enableHighAccuracy: true,
                timeout: 30000,
                maximumAge: 5000,
            });
        }

        function refreshGPSFree(mode) {
            if (gpsWatchIds[mode]) {
                navigator.geolocation.clearWatch(gpsWatchIds[mode]);
            }
            modeState[mode].gps = false;
            startGPSFree(mode);
        }

        async function initFaceDetectionForMode(mode) {
            const statusEl = document.getElementById('face-status-' + mode);
            const videoContainer = document.getElementById('video-container-' + mode);
            const previewContainer = document.getElementById('preview-container-' + mode);
            const previewImg = document.getElementById('preview-img-' + mode);

            statusEl.innerHTML =
                '<span class="badge bg-warning bg-opacity-10 text-warning rounded-3 px-3 py-2"><i class="bi bi-hourglass-split"></i> Requesting camera access...</span>';

            try {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    throw new Error('BrowserNotSupported');
                }

                let stream = mode === 'wfo' ? videoStream : videoStreams[mode];

                if (!stream) {
                    try {
                        stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
                    } catch (err1) {
                        stream = await navigator.mediaDevices.getUserMedia({ video: true });
                    }

                    if (mode === 'wfo') videoStream = stream;
                    else videoStreams[mode] = stream;
                }

                const videoEl = document.getElementById('video-' + mode);
                videoEl.srcObject = stream;

                statusEl.innerHTML =
                    '<span class="badge bg-primary bg-opacity-10 text-primary rounded-3 px-3 py-2"><i class="bi bi-cpu"></i> Camera is active. Loading AI...</span>';
                const MODEL_URL = '{{ asset("vendor/face-api/weights") }}';
                await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
                await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
                await faceapi.nets.faceExpressionNet.loadFromUri(MODEL_URL);

                // Load MediaPipe Local
                if (typeof faceLandmarker === 'undefined' || !faceLandmarker) {
                    const { FaceLandmarker, FilesetResolver } = await import('{{ asset("vendor/mediapipe/vision_bundle.mjs") }}');
                    const filesetResolver = await FilesetResolver.forVisionTasks('{{ asset("vendor/mediapipe/wasm") }}');
                    window.faceLandmarker = await FaceLandmarker.createFromOptions(filesetResolver, {
                        baseOptions: {
                            modelAssetPath: '{{ asset("vendor/mediapipe/face_landmarker.task") }}',
                            delegate: 'GPU'
                        },
                        outputFaceBlendshapes: true,
                        runningMode: 'VIDEO',
                        numFaces: 1
                    });
                }

                statusEl.innerHTML =
                    '<span class="badge bg-info bg-opacity-10 text-info rounded-3 px-3 py-2"><i class="bi bi-person-bounding-box"></i> Waiting for face...</span>';
                startFaceDetectionLoop(mode, videoEl, statusEl, videoContainer, previewContainer, previewImg);
            } catch (err) {
                let errorMsg = err.message;
                let rawError = err.name || 'UnknownError';

                if (rawError === 'NotAllowedError' || errorMsg.includes('not allowed')) {
                    errorMsg =
                        'Camera access was denied. Click the 🔒 lock icon in your browser, open Permissions, and allow camera access.';
                } else if (rawError === 'NotFoundError' || rawError === 'DevicesNotFoundError') {
                    errorMsg = 'No camera was found on this device.';
                } else if (rawError === 'NotReadableError' || rawError === 'TrackStartError') {
                    errorMsg = 'The camera is currently being used by another application (e.g., WhatsApp or Zoom).';
                } else if (errorMsg === 'BrowserNotSupported') {
                    errorMsg = 'Your browser is outdated or not running over HTTPS (secure connection required).';
                }

                statusEl.innerHTML = `
                    <div class="alert bg-danger bg-opacity-10 text-danger border-0 text-wrap text-start lh-base p-3 mt-2 mb-0 shadow-sm rounded-3" style="font-size: 0.85rem;">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> <strong>Error:</strong> ${errorMsg}
                        <br><small class="opacity-75" style="font-size: 0.7rem;">(Code: ${rawError})</small>
                        <hr class="my-2 border-danger" style="opacity: 0.2;">
                        <button type="button" class="btn btn-sm btn-outline-danger w-100 rounded-3" onclick="initFaceDetectionForMode('${mode}')">
                            <i class="bi bi-camera-video"></i> Restart Camera
                        </button>
                    </div>`;
            }
        }

        const BLINK_CONFIG = {
            closeThreshold: 0.22,
            openThreshold: 0.27,
            minClosedFrames: 2,
            minOpenFrames: 2,
            minBlinkDuration: 80,
            maxBlinkDuration: 800,
            cooldown: 500
        };
        const DEBUG_BLINK = true;

        function calculateEAR(eye) {
            const A = Math.hypot(eye[1].x - eye[5].x, eye[1].y - eye[5].y);
            const B = Math.hypot(eye[2].x - eye[4].x, eye[2].y - eye[4].y);
            const C = Math.hypot(eye[0].x - eye[3].x, eye[0].y - eye[3].y);
            return (A + B) / (2.0 * C);
        }

        function drawHUDBox(ctx, x, y, width, height, color) {
            const len = 20;
            ctx.strokeStyle = color;
            ctx.lineWidth = 3;
            ctx.beginPath();
            
            ctx.moveTo(x, y + len); ctx.lineTo(x, y); ctx.lineTo(x + len, y);
            ctx.moveTo(x + width - len, y); ctx.lineTo(x + width, y); ctx.lineTo(x + width, y + len);
            ctx.moveTo(x + width, y + height - len); ctx.lineTo(x + width, y + height); ctx.lineTo(x + width - len, y + height);
            ctx.moveTo(x + len, y + height); ctx.lineTo(x, y + height); ctx.lineTo(x, y + height - len);
            
            ctx.stroke();
            
            ctx.fillStyle = color === '#00ff88' ? 'rgba(0, 255, 136, 0.1)' : 'rgba(0, 212, 255, 0.1)';
            ctx.fillRect(x, y, width, height);
        }

        function drawFaceBox(ctx, box) {
            let { x, y, width, height } = box;
            const expandY = height * 0.25;
            y -= expandY;
            height += expandY;
            const expandX = width * 0.1;
            x -= expandX;
            width += (expandX * 2);
            drawHUDBox(ctx, x, y, width, height, '#00ff88');
        }

        function drawEyesBox(ctx, landmarks) {
            const leftEye = landmarks.getLeftEye();
            const rightEye = landmarks.getRightEye();
            const pts = [...leftEye, ...rightEye];
            let minX = Infinity, minY = Infinity, maxX = -Infinity, maxY = -Infinity;
            pts.forEach(p => {
                if (p.x < minX) minX = p.x;
                if (p.y < minY) minY = p.y;
                if (p.x > maxX) maxX = p.x;
                if (p.y > maxY) maxY = p.y;
            });
            const padX = 30, padY = 25;
            drawHUDBox(ctx, minX - padX, minY - padY, (maxX - minX) + padX * 2, (maxY - minY) + padY * 2, '#00d4ff');
        }

        function startFaceDetectionLoop(mode, videoEl, statusEl, videoContainer, previewContainer, previewImg) {
            let stage = 0; // 0: wait for face, 1: verify & countdown
            let isVerifying = false;
            
            // Blink State Machine Variables
            let eyeState = 'OPEN';
            let consecutiveClosedFrames = 0;
            let consecutiveOpenFrames = 0;
            let blinkStartTime = 0;
            let lastBlinkTime = 0;
            let lastVideoTime = -1;

            const overlay = document.getElementById('overlay-' + mode);
            let ctx = null;
            
            if (overlay) {
                overlay.width = videoEl.videoWidth || 320;
                overlay.height = videoEl.videoHeight || 240;
                ctx = overlay.getContext('2d');
            }

            statusEl.innerHTML = '<span class="badge bg-secondary bg-opacity-10 text-secondary rounded-3 px-3 py-2 fs-6 shadow-sm text-wrap"><i class="bi bi-person-bounding-box me-1"></i> Detecting Face...</span>';

            let isDetecting = false;
            
            // Fast polling loop (~30 FPS). face-api.js throttles via isDetecting flag.
            const interval = setInterval(async () => {
                try {
                    if (videoEl.videoWidth > 0 && overlay && overlay.width !== videoEl.videoWidth) {
                        overlay.width = videoEl.videoWidth;
                        overlay.height = videoEl.videoHeight;
                    }

                    if (stage === 0) {
                        if (isDetecting) return;
                        isDetecting = true;
                        
                        try {
                            const detection = await faceapi.detectSingleFace(videoEl, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks();
                            if (ctx && overlay) ctx.clearRect(0, 0, overlay.width, overlay.height);

                            if (detection) {
                                drawFaceBox(ctx, detection.detection.box);
                                if (detection.detection.score >= 0.50 && detection.detection.box.width >= 50) {
                                    if (!isVerifying) {
                                        isVerifying = true;
                                        statusEl.innerHTML = '<span class="badge bg-primary bg-opacity-10 text-primary rounded-3 px-3 py-2 fs-6 shadow-sm"><i class="bi bi-arrow-repeat"></i> Verifying Identity...</span>';
                                        
                                        const canvas = document.createElement('canvas');
                                        canvas.width = videoEl.videoWidth;
                                        canvas.height = videoEl.videoHeight;
                                        const tctx = canvas.getContext('2d');
                                        tctx.drawImage(videoEl, 0, 0, canvas.width, canvas.height);
                                        const photoData = canvas.toDataURL('image/jpeg', 0.8);
                                        
                                        try {
                                            const response = await fetch('/api/face/verify', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                                },
                                                body: JSON.stringify({ image: photoData })
                                            });
                                            const result = await response.json();
                                            
                                            if (result.success) {
                                                stage = 1; // Verified! Move to blink check via MediaPipe
                                                isVerifying = false;
                                                statusEl.innerHTML = '<span class="badge bg-info bg-opacity-10 text-info rounded-3 px-3 py-2 fs-6 shadow-sm text-wrap lh-sm"><i class="bi bi-eye"></i> Face Matched!<br>Please <strong>BLINK YOUR EYES</strong> to confirm...</span>';
                                            } else {
                                                statusEl.innerHTML = '<span class="badge bg-danger bg-opacity-10 text-danger rounded-3 px-3 py-2 fs-6 shadow-sm"><i class="bi bi-x-circle me-1"></i> Face Not Recognized!</span>';
                                                setTimeout(() => {
                                                    isVerifying = false;
                                                    if (stage === 0) statusEl.innerHTML = '<span class="badge bg-secondary bg-opacity-10 text-secondary rounded-3 px-3 py-2 fs-6 shadow-sm"><i class="bi bi-person-bounding-box me-1"></i> Detecting Face...</span>';
                                                }, 2000);
                                            }
                                        } catch (e) {
                                            isVerifying = false;
                                        }
                                    }
                                } else {
                                    statusEl.innerHTML = '<span class="badge bg-warning bg-opacity-10 text-warning rounded-3 px-3 py-2 fs-6 shadow-sm text-wrap"><i class="bi bi-arrows-fullscreen"></i> Please Move Closer</span>';
                                }
                            } else {
                                statusEl.innerHTML = '<span class="badge bg-danger bg-opacity-10 text-danger rounded-3 px-3 py-2 fs-6 shadow-sm text-wrap"><i class="bi bi-exclamation-triangle me-1"></i> Face Not Detected</span>';
                            }
                        } finally {
                            isDetecting = false;
                        }
                    } else if (stage === 1) {
                        // STAGE 1: MEDIAPIPE BLINK DETECTION
                        if (!window.faceLandmarker) return; 
                        
                        const startTimeMs = performance.now();
                        if (lastVideoTime !== videoEl.currentTime) {
                            lastVideoTime = videoEl.currentTime;
                            
                            const mpResult = window.faceLandmarker.detectForVideo(videoEl, startTimeMs);
                            const inferTime = performance.now() - startTimeMs;
                            
                            if (ctx && overlay) ctx.clearRect(0, 0, overlay.width, overlay.height);

                            let leftBlink = 0, rightBlink = 0, blinkScore = 0;

                            if (mpResult.faceBlendshapes && mpResult.faceBlendshapes.length > 0) {
                                const blendshapes = mpResult.faceBlendshapes[0].categories;
                                leftBlink = blendshapes.find(b => b.categoryName === 'eyeBlinkLeft')?.score || 0;
                                rightBlink = blendshapes.find(b => b.categoryName === 'eyeBlinkRight')?.score || 0;
                                
                                blinkScore = Math.max(leftBlink, rightBlink);
                                const now = Date.now();

                                const isClosed = blinkScore > 0.40;
                                const isOpen = blinkScore < 0.28;

                                if (eyeState === 'OPEN') {
                                    if (isClosed) {
                                        consecutiveClosedFrames++;
                                        if (consecutiveClosedFrames >= 2) {
                                            eyeState = 'CLOSED';
                                            blinkStartTime = now;
                                            consecutiveOpenFrames = 0;
                                        }
                                    } else if (isOpen) {
                                        consecutiveClosedFrames = 0;
                                    }
                                } else if (eyeState === 'CLOSED') {
                                    if (isOpen) {
                                        consecutiveOpenFrames++;
                                        if (consecutiveOpenFrames >= 2) {
                                            const blinkDuration = now - blinkStartTime;
                                            
                                            if (blinkDuration >= 30 && blinkDuration <= 2000 && (now - lastBlinkTime) > 500) {
                                                lastBlinkTime = now;
                                                stage = 2; // Valid blink!
                                            }
                                            eyeState = 'OPEN';
                                            consecutiveClosedFrames = 0;
                                        }
                                    } else if (isClosed) {
                                        consecutiveOpenFrames = 0;
                                    }
                                }
                            } else {
                                // Face lost
                                eyeState = 'OPEN';
                                consecutiveClosedFrames = 0;
                                consecutiveOpenFrames = 0;
                            }

                            if (DEBUG_BLINK && videoContainer) {
                                let debugDiv = document.getElementById('debug-blink-' + mode);
                                if (!debugDiv) {
                                    debugDiv = document.createElement('div');
                                    debugDiv.id = 'debug-blink-' + mode;
                                    debugDiv.style = "position:absolute; top:10px; left:10px; background:rgba(0,0,0,0.7); color:#0f0; padding:10px; font-family:monospace; font-size:12px; z-index:9999; border-radius:5px;";
                                    videoContainer.appendChild(debugDiv);
                                }
                                debugDiv.innerHTML = `
                                    <strong>BLINK DEBUG (MediaPipe)</strong><br>
                                    Blink L: ${leftBlink.toFixed(3)}<br>
                                    Blink R: ${rightBlink.toFixed(3)}<br>
                                    Blink Score: ${blinkScore.toFixed(3)}<br>
                                    State: ${eyeState}<br>
                                    Clsd Frms: ${consecutiveClosedFrames}<br>
                                    Open Frms: ${consecutiveOpenFrames}<br>
                                    Infer Time: ${inferTime.toFixed(1)}ms<br>
                                    FPS (est): ${Math.round(1000 / inferTime)}
                                `;
                            }
                        }
                    } else if (stage === 2) {
                        // STAGE 2: COUNTDOWN AND CAPTURE
                        if (!isVerifying) {
                            isVerifying = true;
                            clearInterval(interval);
                            
                            let countdown = 3;
                            statusEl.innerHTML = `<span class="badge bg-success bg-opacity-10 text-success rounded-3 px-3 py-2 fs-6 shadow-sm text-wrap"><i class="bi bi-${countdown}-circle"></i> Liveness OK! Get Ready...</span>`;
                            
                            const countdownInterval = setInterval(() => {
                                countdown--;
                                if (countdown > 0) {
                                    statusEl.innerHTML = `<span class="badge bg-success bg-opacity-10 text-success rounded-3 px-3 py-2 fs-6 shadow-sm text-wrap"><i class="bi bi-${countdown}-circle"></i> Liveness OK! Get Ready...</span>`;
                                } else {
                                    clearInterval(countdownInterval);
                                    
                                    statusEl.innerHTML = '<span class="badge bg-success bg-opacity-10 text-success rounded-3 px-3 py-2 fs-6 shadow-sm text-wrap"><i class="bi bi-check-circle"></i> Identity Verified! Wait...</span>';

                                    const canvas = document.createElement('canvas');
                                    const targetWidth = 480;
                                    const scale = targetWidth / videoEl.videoWidth;
                                    const targetHeight = videoEl.videoHeight * scale;

                                    canvas.width = targetWidth;
                                    canvas.height = targetHeight;
                                    const tctx = canvas.getContext('2d');
                                    tctx.translate(targetWidth, 0);
                                    tctx.scale(-1, 1);
                                    tctx.drawImage(videoEl, 0, 0, targetWidth, targetHeight);

                                    const photoData = canvas.toDataURL('image/jpeg', 0.8);
                                    const photoInput = document.getElementById('photo_data-' + mode);
                                    if (photoInput) photoInput.value = photoData;

                                    previewImg.src = photoData;
                                    videoContainer.style.display = 'none';
                                    previewContainer.style.display = 'block';

                                    modeState[mode].face = true;
                                    checkReady(mode);
                                }
                            }, 1000);
                        }
                    }
                } catch (err) {
                    console.error('Face detection error:', err);
                }
            }, 33); // 33ms (~30 FPS)
            
            if (mode === 'wfo') faceDetectionInterval = interval;
            else faceDetectionIntervals[mode] = interval;
        }

        function retakePhoto(mode) {
            modeState[mode].face = false;
            document.getElementById('photo_data-' + mode).value = '';
            checkReady(mode);

            document.getElementById('preview-container-' + mode).style.display = 'none';
            document.getElementById('video-container-' + mode).style.display = 'block';

            const videoEl = document.getElementById('video-' + mode);
            const statusEl = document.getElementById('face-status-' + mode);
            const videoContainer = document.getElementById('video-container-' + mode);
            const previewContainer = document.getElementById('preview-container-' + mode);
            const previewImg = document.getElementById('preview-img-' + mode);

            statusEl.innerHTML =
                '<span class="badge bg-info bg-opacity-10 text-info rounded-3 px-3 py-2">Waiting for face movement...</span>';
            startFaceDetectionLoop(mode, videoEl, statusEl, videoContainer, previewContainer, previewImg);
        }

        function getDeviceMeta() {
            const ua = navigator.userAgent;
            let browser = 'Unknown';
            let os = 'Unknown';

            if (ua.match(/Edg/i)) browser = 'Edge';
            else if (ua.match(/OPR/i)) browser = 'Opera';
            else if (ua.match(/Chrome/i)) browser = 'Chrome';
            else if (ua.match(/Safari/i)) browser = 'Safari';
            else if (ua.match(/Firefox/i)) browser = 'Firefox';

            if (ua.match(/Win/i)) os = 'Windows';
            else if (ua.match(/iPhone|iPad|iPod/i)) os = 'iOS';
            else if (ua.match(/Mac/i)) os = 'MacOS';
            else if (ua.match(/Android/i)) os = 'Android';
            else if (ua.match(/Linux/i)) os = 'Linux';

            return { os, browser };
        }

        async function initFingerprintForMode(mode) {
            try {
                const statusEl = document.getElementById('fingerprint-status-' + mode);
                if (statusEl) {
                    statusEl.innerHTML =
                        '<span class="badge bg-warning bg-opacity-10 text-warning rounded-3 px-3 py-2">⏳ Loading Fingerprint...</span>';
                }

                const fp = await FingerprintJS.load();
                const result = await fp.get();
                const meta = getDeviceMeta();
                const stableFingerprint = result.visitorId + '|' + meta.os + '|' + meta.browser;

                document.getElementById('fingerprint-' + mode).value = stableFingerprint;

                const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
                document.getElementById('is_mobile-' + mode).value = isMobile ? '1' : '0';

                const registeredDesktop = @json (auth()->user()->browser_fingerprint_desktop);
                const registeredMobile = @json (auth()->user()->browser_fingerprint_mobile);

                let isVerified = false;
                if (isMobile) {
                    if (!registeredMobile || registeredMobile === stableFingerprint) isVerified = true;
                } else {
                    if (!registeredDesktop || registeredDesktop === stableFingerprint) isVerified = true;
                }

                if (isVerified) {
                    if (statusEl)
                        statusEl.innerHTML =
                            '<span class="badge bg-success bg-opacity-10 text-success rounded-3 px-3 py-2"><i class="bi bi-check-circle"></i> Device Verified</span>';
                    modeState[mode].fingerprint = true;
                } else {
                    if (statusEl)
                        statusEl.innerHTML =
                            '<span class="badge bg-danger bg-opacity-10 text-danger rounded-3 px-3 py-2"><i class="bi bi-x-circle"></i> Unregistered Device</span>';
                    modeState[mode].fingerprint = false;
                }
                checkReady(mode);
            } catch (err) {
                const statusEl = document.getElementById('fingerprint-status-' + mode);
                if (statusEl) {
                    statusEl.innerHTML =
                        '<span class="badge bg-danger bg-opacity-10 text-danger rounded-3 px-3 py-2">❌ Error: ' +
                        err.message +
                        '</span>';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const officeSelect = document.getElementById('office-location-wfo');
            if (officeSelect) {
                officeSelect.addEventListener('change', function () {
                    modeState.wfo.network = false;
                    renderWfoOfficeDetails();
                    refreshGPS();
                });
            }
            renderWfoOfficeDetails();
        });
    </script>
                    @endif
@endsection
