@extends ('layouts.dashboard')

@section ('content')
    <div class="page-heading mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex align-items-center order-2 order-md-1">
                <a href="{{ route('crm.wa-blasts.index') }}" class="btn btn-secondary me-3" title="Back">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>
                <div>
                    <h3 class="mb-0">Create New WA Campaign</h3>
                    <p class="text-subtitle text-muted mb-0 mt-1">Send mass WhatsApp messages directly from the CRM.</p>
                </div>
            </div>

            <nav aria-label="breadcrumb" class="breadcrumb-header order-1 order-md-2">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('crm.wa-blasts.index') }}">WA Blasts</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Create New WA Campaign</li>
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
                <form action="{{ route('crm.wa-blasts.store') }}" method="POST">
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
                                <option value="all">All Contacts with Phone Numbers ({{ $contactCount }} contacts)</option>
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
                                            placeholder="Search company name or phone number..."
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
                                                    ><i class="bi bi-telephone me-1"></i>{{ $contact->phone }}</span
                                                >
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="col-12 mb-4">
                            <label for="template_selector" class="form-label fw-bold">
                                Use CRM Template (Optional)
                            </label>
                            <select id="template_selector" class="form-select">
                                <option value="">-- Type manual message or select a template --</option>
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
                            <label for="campaign_name" class="form-label fw-bold"
                                >Campaign Name <span class="text-danger">*</span></label
                            >
                            <input
                                type="text"
                                id="campaign_name"
                                class="form-control @error('campaign_name') is-invalid @enderror"
                                name="campaign_name"
                                value="{{ old('campaign_name') }}"
                                placeholder="Example: Monthly Promo WA Blast"
                                required
                            />
                            @error ('campaign_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 mb-4">
                            <label for="body_template" class="form-label fw-bold"
                                >WhatsApp Message Content <span class="text-danger">*</span></label
                            >
                            <textarea
                                id="body_template"
                                name="body_template"
                                class="form-control @error('body_template') is-invalid @enderror"
                                rows="10"
                                required
                                placeholder="Write your WhatsApp message here. You can use standard WA formatting (*bold*, _italic_, ~strikethrough~)."
                            >{{ old('body_template') }}</textarea>
                            @error ('body_template')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary px-4 shadow-sm w-100 d-md-inline-block w-md-auto">
                                <i class="bi bi-send me-2"></i> Save & Continue to Sending
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Hidden templates for javascript population -->
    @foreach ($templates as $template)
        <div id="template-content-{{ $template->id }}" style="display:none;">
            {!! $template->body !!}
        </div>
    @endforeach
@endsection

@push ('scripts')
    <script>
        $(document).ready(function () {
            // Target type selector logic
            $('#target_type').change(function () {
                if ($(this).val() === 'selected') {
                    $('#manual_contact_selection').slideDown();
                } else {
                    $('#manual_contact_selection').slideUp();
                }
            });

            // Template selector logic - text version for WA
            $('#template_selector').change(function () {
                var templateId = $(this).val();
                if (templateId) {
                    var htmlContent = $('#template-content-' + templateId).html();
                    // Basic HTML to WA text conversion
                    var textContent = htmlContent
                        .replace(/<br\s*[\/]?>/gi, "\n")
                        .replace(/<p[^>]*>/gi, "")
                        .replace(/<\/p>/gi, "\n\n")
                        .replace(/<strong[^>]*>|<\/strong>|<b[^>]*>|<\/b>/gi, "*")
                        .replace(/<em[^>]*>|<\/em>|<i[^>]*>|<\/i>/gi, "_")
                        .replace(/<strike[^>]*>|<\/strike>|<s[^>]*>|<\/s>/gi, "~")
                        .replace(/<[^>]+>/ig, '') // remove all other tags
                        .replace(/&nbsp;/ig, ' ')
                        .trim();
                        
                    $('#body_template').val(textContent);
                }
            });

            // Contact List Search logic
            $('#search-contact').on('keyup', function () {
                var value = $(this).val().toLowerCase();
                $('.contact-item').filter(function () {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });

            // Select All logic
            $('#select-all-contacts').change(function () {
                var isChecked = $(this).prop('checked');
                // Only check visible items based on search filter
                $('.contact-item:visible .contact-checkbox').prop('checked', isChecked);
            });
        });
    </script>
@endpush
