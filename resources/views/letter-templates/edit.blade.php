@extends ('layouts.dashboard')

@section ('content')
    <div class="page-heading mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div class="d-flex align-items-center order-2 order-md-1 mt-3 mt-md-0">
                <a href="{{ route('letter-templates.index') }}" class="btn btn-secondary me-3" title="Kembali">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>

                <div>
                    <h3 class="mb-0">Edit Letter Template</h3>
                    <p class="text-subtitle text-muted mb-0 mt-1">Modify the content or configuration of the letter template.</p>
                </div>
            </div>

            <nav aria-label="breadcrumb" class="breadcrumb-header order-1 order-md-2">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('letter-templates.index') }}">Letter Templates</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Letter Template</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="page-content">
        <div class="container-fluid">
            <div class="row align-items-start">
                <div class="col-12 col-lg-8">
                    <div class="card shadow-sm">
                        <div class="card-body pt-4">
                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <form action="{{ route('letter-templates.update', $letterTemplate) }}" method="POST">
                                @csrf
                                @method ('PUT')

                                <div class="mb-3">
                                    <label for="name" class="form-label fw-bold"
                                        >Template Name <span class="text-danger">*</span></label
                                    >
                                    <input
                                        type="text"
                                        class="form-control"
                                        id="name"
                                        name="name"
                                        value="{{ old('name', $letterTemplate->name) }}"
                                        required
                                    />
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label fw-bold">Description</label>
                                    <textarea
                                        class="form-control"
                                        id="description"
                                        name="description"
                                        rows="2"
                                        >{{ old('description', $letterTemplate->description) }}</textarea
                                    >
                                </div>

                                <div class="mb-3">
                                    <label for="type" class="form-label fw-bold"
                                        >Type <span class="text-danger">*</span></label
                                    >
                                    <select class="form-select" id="type" name="type" required>
                                        <option
                                            value="official"
                                            {{
                                                old('type', $letterTemplate->type) == 'official'
                                                    ? 'selected'
                                                    : ''
                                            }}
                                            >Official Letter
                                        </option>
                                        <option
                                            value="memo"
                                            {{
                                                old('type', $letterTemplate->type) == 'memo'
                                                    ? 'selected'
                                                    : ''
                                            }}
                                            >Memorandum
                                        </option>
                                        <option
                                            value="notice"
                                            {{
                                                old('type', $letterTemplate->type) == 'notice'
                                                    ? 'selected'
                                                    : ''
                                            }}
                                            >Notice
                                        </option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="content" class="form-label fw-bold"
                                        >Template Content <span class="text-danger">*</span></label
                                    >
                                    <textarea
                                        class="form-control"
                                        id="content"
                                        name="content"
                                        >{{ old('content', $letterTemplate->content) }}</textarea
                                    >
                                </div>

                                <div class="form-group mt-4 pt-3 border-top d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-pencil-square me-1"></i> Update Template
                                    </button>
                                    <a href="{{ route('letter-templates.index') }}" class="btn btn-secondary"
                                        ><i class="bi bi-x-circle me-1"></i> Cancel</a
                                    >
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4 mt-4 mt-lg-0 sticky-lg-top" style="top: 2rem; z-index: 10">
                    <div class="card shadow-sm">
                        <div class="card-header border-bottom">
                            <h5 class="card-title mb-0 fs-6">
                                <i class="bi bi-tags-fill me-2 text-primary"></i> List of Tag Variables
                            </h5>
                        </div>
                        <div class="card-body pt-3">
                            <p class="text-muted small">Click a tag below to automatically insert the variable at the cursor position in the editor.</p>

                            <div class="d-flex flex-wrap gap-2">
                                @if (isset($tags) && $tags->count() > 0)
                                    @foreach ($tags as $tag)
                                        <span
                                            class="badge bg-primary px-3 py-2"
                                            style="cursor: pointer; user-select: none; transition: 0.2s"
                                            onmouseover="this.classList.replace('bg-primary', 'bg-dark')"
                                            onmouseout="this.classList.replace('bg-dark', 'bg-primary')"
                                            onclick="window.insertTagToEditor('{{ $tag->tag_name }}')"
                                        >
                                            [{{ $tag->tag_name }}]
                                        </span>
                                    @endforeach
                                @else
                                    <div class="alert alert-light-warning w-100 small">
                                        No tags found in Master Data. <br />
                                        <a href="{{ route('letter-tags.index') }}">Create New Tag</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push ('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js"></script>
    <script>
        $(document).ready(function () {
            let isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            tinymce.init({
                selector: '#content',
                plugins: 'advlist autolink lists link image charmap preview anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking table emoticons template help',
                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | outdent indent | numlist bullist | forecolor backcolor removeformat | pagebreak | charmap emoticons | fullscreen preview print | insertfile image media template link anchor codesample | ltr rtl',
                height: 500,
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
                content_style: `
                    img { max-width: 100% !important; height: auto !important; }
                    body {
                        width: 210mm !important;
                        margin: 0 auto !important;
                        padding: 20px 20mm !important;
                        box-sizing: border-box !important;
                        font-family: 'Helvetica', 'Arial', sans-serif !important;
                        font-size: 12px !important;
                        line-height: 1.5 !important;
                        text-align: justify !important;
                    }
                    p, span, div, td, th {
                        margin-top: 0 !important;
                        margin-bottom: 5px !important;
                        line-height: 1.5 !important;
                        font-family: 'Helvetica', 'Arial', sans-serif !important;
                        font-size: 12px !important;
                    }
                    ul, ol {
                        padding-left: 24px !important;
                        margin-top: 5px !important;
                        margin-bottom: 10px !important;
                    }
                    li {
                        margin-bottom: 4px !important;
                        text-align: left !important;
                    }
                    h1, h2, h3, h4, h5, h6 {
                        margin-top: 8px !important;
                        margin-bottom: 4px !important;
                        line-height: 1.2 !important;
                    }
                `,
                table_use_colgroups: false
            });
        });

        window.insertTagToEditor = function (tagName) {
            let tagText = `[${tagName}]`;
            tinymce.get('content').insertContent(tagText);
        };
    </script>
@endpush
