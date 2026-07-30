@extends ('layouts.dashboard')



@section ('content')
    <div class="page-heading mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div class="d-flex align-items-center order-2 order-md-1 mt-3 mt-md-0">
                <a href="{{ route('letters.index') }}" class="btn btn-secondary me-3" title="Back">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>

                <div>
                    <h3 class="mb-0">Edit Letter</h3>
                    <p class="text-subtitle text-muted mb-0 mt-1">Update the letter draft.</p>
                </div>
            </div>

            <nav aria-label="breadcrumb" class="breadcrumb-header order-1 order-md-2">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('letters.index') }}">Letters</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Letter</li>
                </ol>
            </nav>
        </div>
    </div>
    <div class="page-content">
        <div class="container-fluid">
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

                    <div class="alert alert-light-warning mb-4">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Important:</strong> Changing the template will reset all document content below!
                    </div>

                    <form action="{{ route('letters.update', $letter->id) }}" method="POST" id="form-letter">
                        @csrf
                        @method ('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="letter_template_id" class="form-label fw-bold">Use Template</label>
                                <select class="form-select" id="letter_template_id" name="letter_template_id">
                                    <option value="">-- Type Manually (No Template) --</option>
                                    @foreach ($templates as $template)
                                        <option
                                            value="{{ $template->id }}"
                                            {{
                                                old('letter_template_id', $letter->letter_template_id) == $template->id
                                                    ? 'selected'
                                                    : ''
                                            }}
                                            >{{ $template->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="letter_type" class="form-label fw-bold"
                                    >Letter Type <span class="text-danger">*</span></label
                                >
                                <select class="form-select" id="letter_type" name="letter_type" required>
                                    <option
                                        value="official"
                                        {{
                                            old('letter_type', $letter->letter_type) == 'official'
                                                ? 'selected'
                                                : ''
                                        }}
                                        >Official Letter
                                    </option>
                                    <option
                                        value="memo"
                                        {{
                                            old('letter_type', $letter->letter_type) == 'memo'
                                                ? 'selected'
                                                : ''
                                        }}
                                        >Memorandum
                                    </option>
                                    <option
                                        value="notice"
                                        {{
                                            old('letter_type', $letter->letter_type) == 'notice'
                                                ? 'selected'
                                                : ''
                                        }}
                                        >Notice
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label fw-bold"
                                >Letter Subject <span class="text-danger">*</span></label
                            >
                            <input
                                type="text"
                                class="form-control"
                                id="subject"
                                name="subject"
                                value="{{ old('subject', $letter->subject) }}"
                                placeholder="Enter the letter subject/topic..."
                                required
                            />
                        </div>

                        <div id="dynamic-tags-container" class="row mt-4 mb-2"></div>

                        <div class="mb-3 mt-4">
                            <label for="content" class="form-label fw-bold"
                                >Document Content <span class="text-danger">*</span></label
                            >
                            <textarea
                                class="form-control"
                                id="content"
                                name="content"
                                >{{ old('content', $letter->content) }}</textarea
                            >
                        </div>

                        <div class="form-group mt-4 pt-3 border-top d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save me-1"></i> Update Draft
                            </button>
                            <a href="{{ route('letters.index') }}" class="btn btn-secondary"
                                ><i class="bi bi-x-circle me-1"></i> Cancel</a
                            >
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push ('scripts')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js"></script>
    <script>
        // === capture active user data from php to javascript ===
        const currentUserData = @json (\App\Constants\LetterTagConfig::getAutoFillValues(auth()->user()));
        const existingMetaData = @json(json_decode($letter->meta_data, true) ?: []);
        
        let isInitialLoad = true;

        $(document).ready(function () {
            let isDarkMode = document.documentElement.getAttribute('data-bs-theme') === 'dark';
            tinymce.init({
                selector: '#content',
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

            $('#form-letter').on('submit', function (e) {
                let konfirmasi = confirm('Are you sure the letter changes are correct and ready to be saved?');
                if (!konfirmasi) {
                    e.preventDefault();
                }
            });

            // Handle hiding/showing lokasi_kantor based on tipe_kehadiran_kerja
            $(document).on('change', 'select[name="dynamic_tags[tipe_kehadiran_kerja]"]', function() {
                let val = $(this).val();
                let $lokasiInput = $('[name="dynamic_tags[lokasi_kantor]"]');
                let $lokasiContainer = $lokasiInput.closest('.mb-3');
                
                if (val && val.toUpperCase() !== 'WFO') {
                    $lokasiContainer.hide();
                    $lokasiInput.removeAttr('required');
                } else {
                    $lokasiContainer.show();
                    $lokasiInput.attr('required', true);
                }
            });

            $('#letter_template_id').on('change', function () {
                let templateId = $(this).val();
                let container = $('#dynamic-tags-container');
                container.empty();

                if (templateId) {
                    // special alert if employee changes template while in edit mode
                    if (!isInitialLoad) {
                        if (!confirm('Changing the template will reset the existing document text. Continue?')) {
                            $(this).val('{{ $letter->letter_template_id }}'); // return to the original choice
                            return;
                        }
                    }

                    $.ajax({
                        url: '{{ url('letter-templates') }}/' + templateId,
                        type: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            if (!isInitialLoad) {
                                tinymce.get('content').setContent(data.content || '');
                                if (data.template) {
                                    $('#subject').val(data.template.name);
                                    $('#letter_type').val(data.template.type);
                                }
                            }
                            
                            isInitialLoad = false; // turn off initial load flag

                            if (data.tags && data.tags.length > 0) {
                                container.append(
                                    '<div class="col-12"><div class="alert alert-light-primary"><i class="bi bi-info-circle me-2"></i><strong>Complete Letter Variable Data:</strong> The fields below will automatically replace the tags in the document.</div></div>',
                                );

                                data.tags.forEach(function (tag) {
                                    let labelName = tag.tag_name.replace(/_/g, ' ').toUpperCase();
                                    
                                    // prioritize existing metadata, then fallback to default value
                                    let defaultValue = (existingMetaData && existingMetaData[tag.tag_name] !== undefined) 
                                                        ? existingMetaData[tag.tag_name] 
                                                        : (tag.default_value ? tag.default_value : '');

                                    // === new logic: check auto fill ===
                                    let isReadOnly = '';
                                    let helperText = '';

                                    if (tag.dropdown_type === 'auto_fill') {
                                        isReadOnly = 'readonly style="background-color: var(--bs-secondary-bg);"';
                                        helperText =
                                            '<small class="text-primary mt-1 d-block"><i class="bi bi-magic me-1"></i>Automatically filled by the system</small>';
                                        defaultValue = currentUserData[tag.dropdown_model] || '';
                                    }

                                    let colClass = tag.input_type === 'long_text' ? 'col-12' : 'col-md-6';
                                    let inputElement = '';

                                    if (tag.input_type === 'long_text') {
                                        inputElement = `<textarea name="dynamic_tags[${tag.tag_name}]" class="form-control" rows="4" required ${isReadOnly}>${defaultValue}</textarea>`;
                                    } else if (tag.input_type === 'date') {
                                        inputElement = `<input type="date" name="dynamic_tags[${tag.tag_name}]" class="form-control" value="${defaultValue}" required ${isReadOnly}>`;
                                    } else if (tag.input_type === 'multiple_date') {
                                        inputElement = `<input type="text" name="dynamic_tags[${tag.tag_name}]" class="form-control flatpickr-multiple" value="${defaultValue}" required ${isReadOnly} placeholder="Select dates...">`;
                                    } else if (tag.input_type === 'time') {
                                        inputElement = `<input type="time" name="dynamic_tags[${tag.tag_name}]" class="form-control" value="${defaultValue}" required ${isReadOnly}>`;
                                    } else if (tag.input_type === 'number') {
                                        inputElement = `<input type="number" name="dynamic_tags[${tag.tag_name}]" class="form-control" value="${defaultValue}" required ${isReadOnly}>`;
                                    } else if (tag.input_type === 'currency') {
                                        inputElement = `<div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" name="dynamic_tags[${tag.tag_name}]" class="form-control" value="${defaultValue}" required ${isReadOnly}>
                                        </div>`;
                                    } else if (tag.input_type === 'dropdown') {
                                        let optionsHtml = '<option value="">-- Select Data --</option>';
                                        if (tag.dropdown_data) {
                                            for (let [val, label] of Object.entries(tag.dropdown_data)) {
                                                let isSelected = defaultValue == val ? 'selected' : '';
                                                optionsHtml += `<option value="${val}" ${isSelected}>${label}</option>`;
                                            }
                                        }
                                        inputElement = `<select name="dynamic_tags[${tag.tag_name}]" class="form-select" required>${optionsHtml}</select>`;
                                    } else if (tag.input_type === 'terbilang') {
                                        let optionsHtml = '<option value="">-- Pilih Tag Sumber --</option>';
                                        data.tags.forEach(function (otherTag) {
                                            if (otherTag.input_type === 'currency' && otherTag.tag_name !== tag.tag_name) {
                                                let isSelected = defaultValue == otherTag.tag_name ? 'selected' : '';
                                                optionsHtml += `<option value="${otherTag.tag_name}" ${isSelected}>[${otherTag.tag_name.replace(/_/g, ' ').toUpperCase()}]</option>`;
                                            }
                                        });
                                        inputElement = `<select name="dynamic_tags[${tag.tag_name}]" class="form-select" required>${optionsHtml}</select>`;
                                        helperText = '<small class="text-primary mt-1 d-block"><i class="bi bi-info-circle me-1"></i>Pilih tag angka yang akan diubah menjadi terbilang</small>';
                                    } else {
                                        inputElement = `<input type="text" name="dynamic_tags[${tag.tag_name}]" class="form-control" value="${defaultValue}" required ${isReadOnly}>`;
                                    }

                                    let html = `
                                        <div class="${colClass} mb-3">
                                            <label class="form-label fw-bold text-secondary">${labelName}</label>
                                            ${inputElement}
                                            ${helperText}
                                        </div>
                                    `;
                                    container.append(html);
                                });
                                
                                // Initialize Flatpickr for multiple dates
                                flatpickr('.flatpickr-multiple', {
                                    mode: 'multiple',
                                    dateFormat: 'Y-m-d',
                                });

                                // Trigger change for tipe_kehadiran_kerja to set initial visibility
                                setTimeout(() => {
                                    $('select[name="dynamic_tags[tipe_kehadiran_kerja]"]').trigger('change');
                                }, 50);
                            }
                        },
                        error: function () {
                            alert('Failed to load template.');
                        },
                    });
                }
            });

            // Trigger change event to load tags if template is already selected
            if ($('#letter_template_id').val()) {
                $('#letter_template_id').trigger('change');
            }
        });
    </script>
@endpush
