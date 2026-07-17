@extends('layouts.dashboard')

@section('content')
<div class="page-heading mb-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div class="d-flex align-items-center order-2 order-md-1">
            <a href="{{ route('crm.templates.index') }}" class="btn btn-secondary me-3" title="Back">
                <i class="bi bi-arrow-left fs-5"></i>
            </a>
            <div>
                <h3 class="mb-0">Create Template</h3>
                <p class="text-subtitle text-muted mb-0 mt-1">Create a new message template for CRM.</p>
            </div>
        </div>
        <nav aria-label="breadcrumb" class="breadcrumb-header order-1 order-md-2">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ route('crm.templates.index') }}">CRM Templates</a></li>
                <li class="breadcrumb-item active" aria-current="page">Create</li>
            </ol>
        </nav>
    </div>
</div>

<section class="section">
    <div class="card">
        <div class="card-header border-bottom">
            <h4 class="card-title">Template Details</h4>
        </div>
            <div class="card-body">
                <form action="{{ route('crm.templates.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="name">Template Name <span class="text-danger">*</span></label>
                                <input type="text" id="name" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="type">Template Type <span class="text-danger">*</span></label>
                                <select id="type" class="form-select @error('type') is-invalid @enderror" name="type" required onchange="toggleSubject()">
                                    <option value="wa" {{ old('type') == 'wa' ? 'selected' : '' }}>WhatsApp</option>
                                    <option value="email" {{ old('type') == 'email' ? 'selected' : '' }}>Email</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12" id="subject-container" style="display: {{ old('type') == 'email' ? 'block' : 'none' }};">
                            <div class="form-group">
                                <label for="subject">Email Subject <span class="text-danger">*</span></label>
                                <input type="text" id="subject" class="form-control @error('subject') is-invalid @enderror" name="subject" value="{{ old('subject') }}">
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group">
                                <label for="body">Message Body <span class="text-danger">*</span></label>
                                <p class="text-muted small mb-2">Available placeholders: <code>[Contact Name]</code>, <code>[Company Name]</code>, <code>[Sales Name]</code></p>
                                <textarea id="body" class="form-control @error('body') is-invalid @enderror" name="body" rows="6" required>{{ old('body') }}</textarea>
                                @error('body')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12 d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary me-1 mb-1">Save</button>
                            <a href="{{ route('crm.templates.index') }}" class="btn btn-light-secondary me-1 mb-1">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js"></script>
<script>
    function initTinyMCE() {
        if (tinymce.get('body')) {
            return;
        }
        let isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        tinymce.init({
            selector: '#body',
            plugins: 'advlist autolink lists link image charmap preview anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking table emoticons template help',
            toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen preview print | insertfile image media template link anchor codesample | ltr rtl',
            height: 400,
            menubar: 'file edit view insert format tools table help',
            skin: isDarkMode ? 'oxide-dark' : 'oxide',
            content_css: isDarkMode ? 'dark' : 'default',
            paste_data_images: true,
                automatic_uploads: true,
                images_upload_handler: function (blobInfo, progress) {
                return new Promise((resolve, reject) => {
                    let xhr = new XMLHttpRequest();
                    xhr.open('POST', '{{ route('upload.image') }}');
                    xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                    xhr.upload.onprogress = (e) => { progress(e.loaded / e.total * 100); };
                    xhr.onload = () => {
                        if (xhr.status === 403) { reject({ message: 'HTTP Error: ' + xhr.status, remove: true }); return; }
                        if (xhr.status < 200 || xhr.status >= 300) { reject('HTTP Error: ' + xhr.status); return; }
                        let json = JSON.parse(xhr.responseText);
                        if (!json || typeof json.location != 'string') { reject('Invalid JSON: ' + xhr.responseText); return; }
                        resolve(json.location);
                    };
                    xhr.onerror = () => { reject('Image upload failed due to a XHR Transport error. Code: ' + xhr.status); };
                    let formData = new FormData();
                    formData.append('file', blobInfo.blob(), blobInfo.filename());
                    xhr.send(formData);
                });
            },
            content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:14px }',
            table_use_colgroups: false
        });
    }

    function removeTinyMCE() {
        if (tinymce.get('body')) {
            tinymce.remove('#body');
        }
    }

    function toggleSubject() {
        const type = document.getElementById('type').value;
        const subjectContainer = document.getElementById('subject-container');
        const subjectInput = document.getElementById('subject');
        
        if (type === 'email') {
            subjectContainer.style.display = 'block';
            subjectInput.setAttribute('required', 'required');
            initTinyMCE();
        } else {
            subjectContainer.style.display = 'none';
            subjectInput.removeAttribute('required');
            removeTinyMCE();
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        const type = document.getElementById('type').value;
        if (type === 'email') {
            initTinyMCE();
        }
    });
</script>
@endpush
@endsection
