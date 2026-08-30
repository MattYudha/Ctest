@extends('layouts.dashboard')

@section('content')
    <div class="page-heading mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div class="d-flex align-items-center order-2 order-md-1 mt-3 mt-md-0">
                <a href="{{ route('my-profile') }}" class="btn btn-secondary me-3" title="Kembali">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>    
           
                <div>
                    <h3 class="mb-0">Edit Personal Information</h3>
                    <p class="text-subtitle text-muted mb-0 mt-1">Update your personal details and profile picture.</p>
                </div>
            </div>

            <nav aria-label="breadcrumb" class="breadcrumb-header order-1 order-md-2">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('my-profile') }}">My Profile</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit</li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-transparent border-bottom-0 pb-0 pt-4 px-4">
                        <h5 class="card-title mb-0"><i class="bi bi-person-lines-fill me-2 text-primary"></i> Profile Data</h5>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('my-profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="d-flex flex-column flex-md-row align-items-center gap-4 mb-4 pb-4 border-bottom">
                                <div class="position-relative">
                                    {{-- Default Icon (only shows if no photo and no preview) --}}
                                    <div class="bg-primary text-white rounded-circle align-items-center justify-content-center {{ $employee->profile_photo ? 'd-none' : 'd-flex' }}" style="width: 120px; height: 120px;" id="defaultAvatarIcon">
                                        <i class="bi bi-person-fill" style="font-size: 5rem; margin: 0 !important; line-height: 0 !important; transform: translate(-30px, -35px);"></i>
                                    </div>
                                    
                                    {{-- Image Preview (shows if has photo OR if user selects a file) --}}
                                    <div class="avatar avatar-2xl bg-light {{ $employee->profile_photo ? '' : 'd-none' }}" id="avatarImageContainer">
                                        <img src="{{ $employee->profile_photo ? asset('storage/'.$employee->profile_photo) : '' }}" alt="Profile Photo" class="rounded-circle" style="object-fit: cover;" id="photoPreview">
                                    </div>
                                </div>
                                <div class="flex-grow-1 text-center text-md-start w-100">
                                    <label class="form-label fw-bold">Upload New Photo</label>
                                    <input type="file" name="profile_photo" id="profilePhotoInput" class="form-control @error('profile_photo') is-invalid @enderror" accept="image/png, image/jpeg, image/jpg">
                                    <small class="text-muted mt-2 d-block">Allowed formats: JPG, JPEG, PNG. Max size: 2MB.</small>
                                    @error('profile_photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row g-4">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label fw-semibold">Phone Number</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-telephone mb-2"></i></span>
                                            <input type="text" name="phone_number" class="form-control @error('phone_number') is-invalid @enderror" value="{{ old('phone_number', $employee->phone_number) }}" placeholder="e.g. 081234567890">
                                        </div>
                                        @error('phone_number') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label fw-semibold">Gender</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-gender-ambiguous mb-2"></i></span>
                                            <select name="gender" class="form-select @error('gender') is-invalid @enderror">
                                                <option value="">-- Select Gender --</option>
                                                <option value="male" {{ old('gender', $employee->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                                <option value="female" {{ old('gender', $employee->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                            </select>
                                        </div>
                                        @error('gender') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label fw-semibold">Place of Birth</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-geo-alt mb-2"></i></span>
                                            <input type="text" name="place_of_birth" class="form-control @error('place_of_birth') is-invalid @enderror" value="{{ old('place_of_birth', $employee->place_of_birth) }}" placeholder="City">
                                        </div>
                                        @error('place_of_birth') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label fw-semibold">Date of Birth</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-calendar-event mb-2"></i></span>
                                            <input type="date" name="birth_date" class="form-control @error('birth_date') is-invalid @enderror" value="{{ old('birth_date', optional($employee->birth_date)->format('Y-m-d')) }}">
                                        </div>
                                        @error('birth_date') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label fw-semibold">Religion</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-star mb-2"></i></span>
                                            <input type="text" name="religion" class="form-control @error('religion') is-invalid @enderror" value="{{ old('religion', $employee->religion) }}" placeholder="e.g. Islam, Catholic, etc">
                                        </div>
                                        @error('religion') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="form-label fw-semibold">Marital Status</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="bi bi-heart mb-2"></i></span>
                                            <select name="marital_status" class="form-select @error('marital_status') is-invalid @enderror">
                                                <option value="">-- Select Status --</option>
                                                <option value="single" {{ old('marital_status', $employee->marital_status) == 'single' ? 'selected' : '' }}>Single</option>
                                                <option value="married" {{ old('marital_status', $employee->marital_status) == 'married' ? 'selected' : '' }}>Married</option>
                                                <option value="divorced" {{ old('marital_status', $employee->marital_status) == 'divorced' ? 'selected' : '' }}>Divorced</option>
                                                <option value="widowed" {{ old('marital_status', $employee->marital_status) == 'widowed' ? 'selected' : '' }}>Widowed</option>
                                            </select>
                                        </div>
                                        @error('marital_status') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label class="form-label fw-semibold">Current Address</label>
                                        <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="3" placeholder="Enter your full address">{{ old('address', $employee->address) }}</textarea>
                                        @error('address') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                                <a href="{{ route('my-profile') }}" class="btn btn-light-secondary px-4">Cancel</a>
                                <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-circle me-1"></i> Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Face Recognition Section --}}
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-transparent border-bottom-0 pb-0 pt-4 px-4">
                        <h5 class="card-title mb-0"><i class="bi bi-person-bounding-box me-2 text-primary"></i> Face Registration</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6 text-center">
                                @if(Auth::user() && Auth::user()->faceProfile)
                                    @php
                                        $facePath = 'storage/faces/user_' . Auth::id() . '.jpg';
                                        $hasFaceImg = file_exists(public_path($facePath));
                                    @endphp
                                    @if($hasFaceImg)
                                        <img src="{{ asset($facePath) }}?v={{ filemtime(public_path($facePath)) }}" alt="Enrolled Face" class="img-fluid rounded-3 mb-3 shadow-sm border" style="width: 250px; height: 250px; object-fit: cover;">
                                    @else
                                        <div class="bg-secondary bg-opacity-10 rounded-3 d-flex align-items-center justify-content-center mb-3 mx-auto text-secondary shadow-sm" style="height: 250px; width: 250px;">
                                            <i class="bi bi-person-bounding-box" style="font-size: 4rem;"></i>
                                        </div>
                                    @endif
                                    
                                    <form action="{{ route('api.face.destroy') }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger rounded-pill px-4" onclick="return confirm('Are you sure you want to remove your face data? You will need to re-enroll to perform self-attendance.')">
                                            <i class="bi bi-trash me-2"></i> Remove Face Data
                                        </button>
                                    </form>
                                @else
                                    <p class="text-muted small">Please ensure your face is clearly visible. We need 5 snapshots for better accuracy.</p>
                                    
                                    <style>
                                        @keyframes snapshot-fly {
                                            0% { transform: scale(1) translateX(0) translateY(0); opacity: 1; }
                                            50% { transform: scale(0.6) translateX(0) translateY(-20px); opacity: 0.9; }
                                            100% { transform: scale(0.2) translateX(-100px) translateY(100px); opacity: 0; }
                                        }
                                        .anim-snapshot {
                                            position: absolute;
                                            top: 0;
                                            left: 0;
                                            width: 100%;
                                            height: 100%;
                                            object-fit: cover;
                                            z-index: 20;
                                            border: 4px solid white;
                                            border-radius: 8px;
                                            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                                            animation: snapshot-fly 0.7s cubic-bezier(0.25, 1, 0.5, 1) forwards;
                                        }
                                        @keyframes flash {
                                            0% { opacity: 0.8; }
                                            100% { opacity: 0; }
                                        }
                                        .anim-flash {
                                            animation: flash 0.3s ease-out forwards;
                                        }
                                        .gallery-thumb {
                                            width: 40px;
                                            height: 40px;
                                            object-fit: cover;
                                            border-radius: 4px;
                                            border: 2px solid white;
                                            box-shadow: 0 2px 4px rgba(0,0,0,0.5);
                                            animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
                                        }
                                        @keyframes popIn {
                                            0% { transform: scale(0); }
                                            100% { transform: scale(1); }
                                        }
                                    </style>
                                    <div id="camera-container" class="bg-light rounded-3 d-flex align-items-center justify-content-center mb-3 mx-auto shadow-sm" style="width: 100%; max-width: 300px; aspect-ratio: 1/1; overflow: hidden; position: relative;">
                                        <video id="face-video" autoplay playsinline style="width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1);"></video>
                                        <div id="flash-overlay" class="position-absolute top-0 start-0 w-100 h-100 bg-white" style="opacity: 0; pointer-events: none; z-index: 10;"></div>
                                        <div id="face-overlay" class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center pb-4 justify-content-center flex-column" style="display: none !important; z-index: 11;">
                                            <span id="face-status" class="badge bg-dark bg-opacity-75 fs-6 shadow-sm px-3 py-2 text-wrap text-center mx-3 mt-auto mb-3"><i class="spinner-border spinner-border-sm me-2"></i> Initializing...</span>
                                        </div>
                                        <div id="capture-gallery" class="position-absolute bottom-0 start-0 w-100 p-2 d-flex justify-content-center gap-2" style="z-index: 12; background: linear-gradient(to top, rgba(0,0,0,0.5), transparent);"></div>
                                    </div>
                                    
                                    <button type="button" id="btn-start-enrollment" class="btn btn-primary rounded-pill px-4">
                                        <i class="bi bi-camera me-2"></i> Start Enrollment
                                    </button>
                                @endif
                            </div>
                            <div class="col-md-6 mt-4 mt-md-0">
                                <h5 class="mb-3">Registration Status</h5>
                                @if(Auth::user() && Auth::user()->faceProfile)
                                    <div class="alert alert-success rounded-3 shadow-sm border-0">
                                        <i class="bi bi-check-circle-fill me-2"></i> <strong>Enrolled</strong>
                                        <p class="mb-0 small mt-1">Your face is registered and ready for attendance.</p>
                                    </div>
                                    <p class="text-muted small mt-3"><i class="bi bi-info-circle me-1"></i> If you are experiencing issues with face verification or want to update your face, you can remove the existing data and re-enroll.</p>
                                @else
                                    <div class="alert alert-warning rounded-3 shadow-sm border-0">
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i> <strong>Not Enrolled</strong>
                                        <p class="mb-0 small mt-1">You must register your face before you can perform self-attendance.</p>
                                    </div>
                                    <p class="text-muted small mt-3"><i class="bi bi-info-circle me-1"></i> Position your face within the camera frame and click "Start Enrollment".</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- </div> -->

<script src="{{ asset('vendor/face-api/face-api.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const video = document.getElementById('face-video');
    const btnStart = document.getElementById('btn-start-enrollment');
    const overlay = document.getElementById('face-overlay');
    const statusTxt = document.getElementById('face-status');
    let stream = null;

    function getBrightness(videoEl) {
        const c = document.createElement('canvas');
        c.width = videoEl.videoWidth || 320;
        c.height = videoEl.videoHeight || 240;
        const ctx = c.getContext('2d');
        ctx.drawImage(videoEl, 0, 0, c.width, c.height);
        const imageData = ctx.getImageData(0, 0, c.width, c.height);
        const data = imageData.data;
        let colorSum = 0;
        for (let i = 0; i < data.length; i += 4) {
            colorSum += (data[i] + data[i+1] + data[i+2]) / 3;
        }
        return colorSum / (data.length / 4);
    }

    async function startCamera() {
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: true });
            if (video) video.srcObject = stream;
        } catch (err) {
            console.error(err);
        }
    }

    if (video) {
        startCamera();
    }

    if (btnStart) {
        btnStart.addEventListener('click', async function() {
            if (!stream) {
                alert('Camera is not active. Please check your permissions.');
                return;
            }

            overlay.style.setProperty('display', 'flex', 'important');
            btnStart.disabled = true;
            
            try {
                statusTxt.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i> Loading AI Models...';
                const MODEL_URL = '{{ asset("vendor/face-api/weights") }}';
                await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
            } catch(e) {
                console.error("Error loading models", e);
                alert("Failed to load face detection model.");
                overlay.style.setProperty('display', 'none', 'important');
                btnStart.disabled = false;
                return;
            }

            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            const ctx = canvas.getContext('2d');
            const gallery = document.getElementById('capture-gallery');
            if (gallery) gallery.innerHTML = '';
            const flashOverlay = document.getElementById('flash-overlay');
            const cameraContainer = document.getElementById('camera-container');
            const images = [];

            let aborted = false;
            
            for (let i = 1; i <= 5; i++) {
                if (aborted) break;
                let faceDetected = false;
                while (!faceDetected) {
                    if (aborted) break;
                    statusTxt.innerHTML = `<i class="bi bi-person-bounding-box me-1"></i> Snapshot ${i}/5<br><small class="fw-normal">Please look clearly at the camera...</small>`;
                    
                    const brightness = getBrightness(video);
                    if (brightness < 40) {
                        statusTxt.innerHTML = `<i class="bi bi-moon me-1 text-warning"></i> <span class="text-warning">Too Dark! Please move to a brighter place.</span>`;
                        await new Promise(r => setTimeout(r, 500));
                        continue;
                    }

                    const detections = await faceapi.detectAllFaces(video, new faceapi.TinyFaceDetectorOptions());
                    
                    if (detections && detections.length > 1) {
                        if (i === 1) {
                            statusTxt.innerHTML = `<i class="bi bi-people me-1 text-danger"></i> <span class="text-danger">Multiple faces detected! Please ensure only you are visible.</span>`;
                            await new Promise(r => setTimeout(r, 500));
                            continue;
                        } else {
                            aborted = true;
                            alert("Intruder detected! Multiple faces found during enrollment. Process aborted.");
                            break;
                        }
                    }
                    
                    if (detections && detections.length === 1) {
                        const detection = detections[0];
                        if (detection.score > 0.80 && detection.box.width > 70) {
                            faceDetected = true;
                            statusTxt.innerHTML = `<i class="bi bi-camera me-1 text-success"></i> <span class="text-success">Capturing ${i}/5...</span>`;
                            await new Promise(r => setTimeout(r, 100)); 
                        } else {
                            await new Promise(r => setTimeout(r, 150));
                        }
                    } else {
                        await new Promise(r => setTimeout(r, 150));
                    }
                }

                if (aborted) break;

                if (flashOverlay) {
                    flashOverlay.classList.remove('anim-flash');
                    void flashOverlay.offsetWidth;
                    flashOverlay.classList.add('anim-flash');
                }

                // Mirror the canvas context horizontally to match the mirrored video feed
                ctx.translate(canvas.width, 0);
                ctx.scale(-1, 1);
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                ctx.setTransform(1, 0, 0, 1, 0, 0); // reset transform for future iterations
                
                // Create Flying Animation
                const dataUrl = canvas.toDataURL('image/jpeg', 0.6);
                const flyingImg = document.createElement('img');
                flyingImg.src = dataUrl;
                flyingImg.className = 'anim-snapshot';
                if (cameraContainer) cameraContainer.appendChild(flyingImg);

                setTimeout(() => {
                    flyingImg.remove();
                    if (gallery) {
                        const thumb = document.createElement('img');
                        thumb.src = dataUrl;
                        thumb.className = 'gallery-thumb';
                        gallery.appendChild(thumb);
                    }
                }, 700);

                const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', 0.9));
                images.push(blob);
                
                await new Promise(r => setTimeout(r, 700));
            }

            if (aborted) {
                overlay.style.setProperty('display', 'none', 'important');
                btnStart.disabled = false;
                return;
            }
            
            statusTxt.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i> Processing and uploading...';
            
            const formData = new FormData();
            images.forEach((blob, idx) => {
                formData.append('images[]', blob, `snapshot_${idx}.jpg`);
            });
            formData.append('_token', '{{ csrf_token() }}');

            try {
                const response = await fetch('{{ route("api.face.register") }}', {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                });
                
                const result = await response.json();
                if (result.success) {
                    alert('Face registered successfully!');
                    window.location.reload();
                } else {
                    let msg = result.message || 'Unknown error';
                    if (result.errors) msg = Object.values(result.errors).join(', ');
                    alert('Failed: ' + msg);
                    overlay.style.setProperty('display', 'none', 'important');
                    btnStart.disabled = false;
                }
            } catch (err) {
                alert('Error uploading face data: ' + err.message);
                overlay.style.setProperty('display', 'none', 'important');
                btnStart.disabled = false;
            }
        });
    }
});
</script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('profilePhotoInput');
        const previewImg = document.getElementById('photoPreview');
        const imgContainer = document.getElementById('avatarImageContainer');
        const defaultIcon = document.getElementById('defaultAvatarIcon');

        if (input) {
            input.addEventListener('change', function(e) {
                if (e.target.files && e.target.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
                        previewImg.src = event.target.result;
                        imgContainer.classList.remove('d-none');
                        defaultIcon.classList.remove('d-flex');
                        defaultIcon.classList.add('d-none');
                    }
                    reader.readAsDataURL(e.target.files[0]);
                }
            });
        }
    });
</script>

@endsection
