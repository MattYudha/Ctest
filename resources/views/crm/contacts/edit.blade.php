@extends ('layouts.dashboard')

@section ('content')
    <div class="page-heading mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div class="d-flex align-items-center order-2 order-md-1 mt-3 mt-md-0">
                <a href="{{ route('crm.contacts.index') }}" class="btn btn-secondary me-3" title="Back">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>

                <div>
                    <h3 class="mb-0">Edit Contact</h3>
                    <p class="text-subtitle text-muted mb-0 mt-1">Update company information entries if there are changes to operational field data.</p>
                </div>
            </div>

            <nav aria-label="breadcrumb" class="breadcrumb-header order-1 order-md-2">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('crm.contacts.index') }}">Contacts</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Edit Contact</li>
                </ol>
            </nav>
        </div>
    </div>
    <section class="section">
        <div class="card w-100">
            <div class="card-header">
                <h4 class="card-title">Edit Form: {{ $contact->company_name }}</h4>
            </div>
            <div class="card-body mt-2">
                <form action="{{ route('crm.contacts.update', $contact->id) }}" method="POST">
                    @csrf
                    @method ('PUT')
                    <div class="row">
                        <div class="col-12 mb-3">
                            <div class="form-group">
                                <label for="company_name" class="form-label fw-bold"
                                    >Company Name <span class="text-danger">*</span></label
                                >
                                <input
                                    type="text"
                                    id="company_name"
                                    class="form-control @error('company_name') is-invalid @enderror"
                                    name="company_name"
                                    value="{{ old('company_name', $contact->company_name) }}"
                                    required
                                />
                                @error ('company_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12 mb-3">
                            <div class="form-group">
                                <label for="address" class="form-label fw-bold">Address</label>
                                <textarea
                                    id="address"
                                    class="form-control"
                                    name="address"
                                    rows="3"
                                    >{{ old('address', $contact->address) }}</textarea
                                >
                            </div>
                        </div>

                        <div class="col-12 col-md-6 mb-3">
                            <div class="form-group">
                                <label for="phone" class="form-label fw-bold">Phone Number</label>
                                <input
                                    type="text"
                                    id="phone"
                                    class="form-control @error('phone') is-invalid @enderror"
                                    name="phone"
                                    value="{{ old('phone', $contact->phone) }}"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                />
                                @error ('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-12 col-md-6 mb-3">
                            <div class="form-group">
                                <label for="email" class="form-label fw-bold">Email Address</label>
                                <input
                                    type="email"
                                    id="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    name="email"
                                    value="{{ old('email', $contact->email) }}"
                                />
                                @error ('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-12 mb-3 mt-2">
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="has_website"
                                    name="has_website"
                                    value="1"
                                    {{
                                        old('has_website', $contact->has_website)
                                            ? 'checked'
                                            : ''
                                    }}
                                />
                                <label class="form-check-label fw-bold ms-2" for="has_website">
                                    The company has an active website
                                </label>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 mb-3" id="website-url-group" style="display: none">
                            <div class="form-group">
                                <label for="website_url" class="form-label fw-bold">Website URL</label>
                                <input
                                    type="text"
                                    id="website_url"
                                    class="form-control"
                                    name="website_url"
                                    value="{{ old('website_url', $contact->website_url) }}"
                                />
                            </div>
                        </div>

                        <div class="col-12 col-md-6 mb-3">
                            <div class="form-group">
                                <label for="source" class="form-label fw-bold">Data Source</label>
                                <input
                                    type="text"
                                    id="source"
                                    class="form-control"
                                    name="source"
                                    value="{{ old('source', $contact->source) }}"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="{{ route('crm.contacts.index') }}" class="btn btn-light-secondary"
                            ><i class="bi bi-arrow-left-short"></i> Cancel</a
                        >
                        <button type="submit" class="btn btn-warning text-dark">
                            <i class="bi bi-check-circle"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
    @push ('scripts')
        <script>
            $(document).ready(function () {
                function toggleWebsiteField() {
                    if ($('#has_website').is(':checked')) {
                        $('#website-url-group').slideDown();
                    } else {
                        $('#website-url-group').slideUp();
                        $('#website_url').val('-');
                    }
                }

                $('#has_website').on('change', toggleWebsiteField);
                toggleWebsiteField(); // run on load
            });
        </script>
    @endpush
@endsection
