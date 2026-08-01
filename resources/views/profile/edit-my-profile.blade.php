@extends('layouts.dashboard')

@section('content')
    <div class="page-heading">
        <div class="page-title mb-4">
            <div class="row align-items-center">
                <div class="col-12 col-md-6 order-md-1 order-last d-flex align-items-center">
                    <a href="{{ route('my-profile') }}" class="btn btn-secondary me-3"><i class="bi bi-arrow-left"></i></a>
                    <div>
                        <h3 class="mb-0">Edit Personal Information</h3>
                        <p class="text-subtitle text-muted mb-0">Update your personal details and profile picture.</p>
                    </div>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('my-profile') }}">My Profile</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Edit</li>
                        </ol>
                    </nav>
                </div>
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
        </section>
    </div>
@endsection

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
