@extends ('layouts.dashboard')



@section ('content')
    <div class="page-heading mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex align-items-center order-2 order-md-1">
                <a href="{{ route('crm.email-blasts.index') }}" class="btn btn-secondary me-3" title="Back">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>
                <div>
                    <h3 class="mb-0">Create New Email Blast</h3>
                    <p class="text-subtitle text-muted mb-0 mt-1">Send mass emails with a queuing system to CRM contacts.</p>
                </div>
            </div>

            <nav aria-label="breadcrumb" class="breadcrumb-header order-1 order-md-2">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('crm.email-blasts.index') }}">Email Blasts</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create New Email Blast</li>
                </ol>
            </nav>
        </div>
    </div>
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-exclamation-octagon me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <section class="section">
        <div class="card shadow-sm w-100">
            <div class="card-body mt-2">
                <form action="{{ route('crm.email-blasts.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-12 mb-4">
                            <label for="target_type" class="form-label fw-bold"
                                >Target Recipients <span class="text-danger">*</span></label
                            >
                            <select
                                name="target_type"
                                id="target_type"
                                class="form-select @error('target_type') is-invalid @enderror"
                            >
                                <option value="all">All Contacts with Email ({{ $contactCount }} contacts)</option>
                                <option value="selected" {{ request('contact_id') ? 'selected' : '' }}
                                    >Select Specific Contacts / Manual
                                </option>
                            </select>
                            @error ('target_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div
                            class="col-12 mb-4"
                            id="manual_contact_selection"
                            style="{{ request('contact_id') ? '' : 'display:none;' }}"
                        >
                            <label class="form-label fw-bold">Select Target Contacts</label>
                            <div class="border border-secondary-subtle rounded">
                                <div class="p-2 border-bottom border-secondary-subtle bg-body-tertiary rounded-top">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-body text-body border-secondary-subtle">
                                            <i class="bi bi-search"></i>
                                        </span>
                                        <input
                                            type="text"
                                            id="search-contact"
                                            class="form-control bg-body text-body border-secondary-subtle"
                                            placeholder="Search company name or email..."
                                        />
                                    </div>
                                </div>

                                <div
                                    class="p-3 bg-body rounded-bottom"
                                    style="max-height: 250px; overflow-y: auto"
                                    id="contact-list"
                                >
                                    <div class="form-check mb-3 pb-2 border-bottom border-secondary-subtle">
                                        <input class="form-check-input" type="checkbox" id="select-all-contacts" />
                                        <label class="form-check-label cursor-pointer w-100" for="select-all-contacts">
                                            Select All Search Results
                                        </label>
                                    </div>

                                    @foreach ($allContacts as $contact)
                                        <div class="form-check contact-item mb-2">
                                            <input
                                                class="form-check-input contact-checkbox"
                                                type="checkbox"
                                                name="contact_ids[]"
                                                value="{{ $contact->id }}"
                                                id="contact-{{ $contact->id }}"
                                                {{ request('contact_id') == $contact->id ? 'checked' : '' }}
                                            />
                                            <label
                                                class="form-check-label w-100 cursor-pointer d-flex flex-column"
                                                for="contact-{{ $contact->id }}"
                                            >
                                                <span
                                                    class="fw-bold text-body"
                                                    >{{ $contact->company_name ?? 'No Name' }}</span
                                                >
                                                <span class="text-muted small"
                                                    ><i class="bi bi-envelope me-1"></i>{{ $contact->email }}</span
                                                >
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mb-4">
                            <label for="template_selector" class="form-label fw-bold">
                                Use Email Template (Optional)
                            </label>
                            <select id="template_selector" class="form-select">
                                <option value="">-- Type manual message or select an email template --</option>
                                @foreach ($templates as $template)
                                    <option value="{{ $template->id }}">{{ $template->name }}</option>
                                @endforeach
                            </select>
                            <small class="text-warning mt-2 d-block">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Selecting a template will overwrite
                                all text in the message editor below.
                            </small>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="subject" class="form-label fw-bold"
                                >Email Subject <span class="text-danger">*</span></label
                            >
                            <input
                                type="text"
                                id="subject"
                                class="form-control @error('subject') is-invalid @enderror"
                                name="subject"
                                value="{{ old('subject') }}"
                                placeholder="Example: Special Offer from Company"
                                required
                            />
                            @error ('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mt-2">
                            <div class="card border shadow-sm">
                                <div class="card-body">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="rounded-3 p-2 bg-body-tertiary me-2">
                                            <i class="bi bi-magic fs-5"></i>
                                        </div>

                                        <div>
                                            <h6 class="mb-0 fw-semibold">Email Personalization Variables</h6>
                                            <small class="text-body-secondary">
                                                Automatic tags for recipient data
                                            </small>
                                        </div>
                                    </div>

                                    <p class="mb-3 text-body-secondary">The system automatically changes the following codes according to the recipient's data. Please <em>copy-paste</em> the following tags into the email body:</p>

                                    <div class="d-flex flex-wrap gap-2">
                                        <div
                                            class="border rounded-3 px-3 py-2 bg-body-tertiary d-flex align-items-center"
                                        >
                                            <code class="fw-semibold me-2">[perusahaan]</code>
                                            <small class="border-start ps-2 text-body-secondary"> Company Name </small>
                                        </div>

                                        <div
                                            class="border rounded-3 px-3 py-2 bg-body-tertiary d-flex align-items-center"
                                        >
                                            <code class="fw-semibold me-2">[email]</code>
                                            <small class="border-start ps-2 text-body-secondary"> Email Address </small>
                                        </div>

                                        <div
                                            class="border rounded-3 px-3 py-2 bg-body-tertiary d-flex align-items-center"
                                        >
                                            <code class="fw-semibold me-2">[telepon]</code>
                                            <small class="border-start ps-2 text-body-secondary"> Phone Number </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <label for="body" class="form-label fw-bold"
                                >Message Body <span class="text-danger">*</span></label
                            >
                            <textarea
                                id="body"
                                class="form-control @error('body') is-invalid @enderror"
                                name="body"
                                >{{ old('body') }}</textarea
                            >
                            @error ('body')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div
                        class="d-flex flex-column flex-md-row justify-content-end gap-2 mt-4 pt-3 border-top border-secondary-subtle"
                    >
                        <a href="{{ route('crm.email-blasts.index') }}" class="btn btn-light-secondary">
                            <i class="bi bi-x-circle me-1"></i> Cancel
                        </a>
                        <button
                            type="submit"
                            class="btn btn-primary"
                            onclick="
                                return confirm(
                                    'Are you sure the data is correct and you want to send this email queue?',
                                );
                            "
                        >
                            <i class="bi bi-send-fill me-1"></i> Process Delivery
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endsection

@push ('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js"></script>
    <script>
        $(document).ready(function () {
            // 1. show / hide contact checkbox area based on selection
            $('#target_type').on('change', function () {
                if ($(this).val() === 'selected') {
                    $('#manual_contact_selection').slideDown();
                } else {
                    $('#manual_contact_selection').slideUp();
                }
            });

            // 2. real-time contact checkbox search feature
            $('#search-contact').on('keyup', function () {
                let value = $(this).val().toLowerCase();
                $('.contact-item').filter(function () {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });

            // 3. select all search results feature
            $('#select-all-contacts').on('change', function () {
                let isChecked = $(this).is(':checked');
                // only check/uncheck elements that are currently visible (search filter results)
                $('.contact-item:visible .contact-checkbox').prop('checked', isChecked);
            });

            // 4. initialize tinymce editor with full toolbar settings
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

            // 5. inject template data from server to js without needing additional ajax calls
            const templatesData = @json ($templates->keyBy('id'));

            // 6. event listener when template dropdown is changed to fill editor content
            $('#template_selector').on('change', function () {
                let id = $(this).val();

                if (id && templatesData[id]) {
                    // insert template content into tinymce workspace
                    tinymce.get('body').setContent(templatesData[id].body || '');
                    
                    // automatically fill subject column if template has a subject or name and subject is not manually filled
                    if ($('#subject').val().trim() === '') {
                        $('#subject').val(templatesData[id].subject || templatesData[id].name);
                    }
                } else {
                    // clear editor if user goes back to selecting manual input
                    tinymce.get('body').setContent('');
                }
            });
        });
    </script>
@endpush
