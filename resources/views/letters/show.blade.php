@extends ('layouts.dashboard')

@section ('content')
    @php
        $config = \App\Models\LetterConfiguration::first();
        
        // Increase timeout and memory for DOMPDF for large base64 images
        set_time_limit(300);
        ini_set('memory_limit', '512M');
        
        $html = view('letters.pdf', compact('letter', 'config'))->render();
        // Fix DOMPDF deadlock on Windows artisan serve by replacing HTTP URLs with local file paths (with forward slashes for DOMPDF)
        $localPath = str_replace('\\', '/', storage_path('app/public/tinymce_uploads'));
        $html = preg_replace('/src=".*?tinymce_uploads\/([^"]+)"/', 'src="' . $localPath . '/$1"', $html);
        
        // Render PDF once to count pages
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4', 'portrait');
        $pdf->render();
        $pageCount = $pdf->getCanvas()->get_page_count();

        // translate numbers to words
        $terbilang = [
            1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
        ];
        $word = $terbilang[$pageCount] ?? $pageCount;
        $lampiranText = "{$word} ({$pageCount}) Sheets";

        // overwrite placeholder with final attachment text
        $finalHtml = str_replace('{TOTAL_PAGES_PLACEHOLDER}', $lampiranText, $html);

        // second render (final) to get the final PDF output
        $pdfFinal = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($finalHtml)->setPaper('a4', 'portrait');
        $pdfBase64 = base64_encode($pdfFinal->output());

        $isAdmin =
            Auth::user()->employee &&
            in_array(Auth::user()->employee->role->title, ['HR Administrator', \App\Constants\Roles::MASTER_ADMIN]);

        // check if the logged in user has a pending signature on this document (for banner)
        $myPendingSignature = \App\Models\Signature::where('signable_type', \App\Models\Letter::class)
            ->where('signable_id', $letter->id)
            ->where('user_id', Auth::id())
            ->where(function ($q) {
                $q->whereNull('signature_image')->orWhere('signature_image', 'PENDING');
            })
            ->first();

        // check if the logged in user has already signed this document (for button status)
        $hasSigned = \App\Models\Signature::where('signable_type', \App\Models\Letter::class)
            ->where('signable_id', $letter->id)
            ->where('user_id', Auth::id())
            ->whereNotNull('signature_image')
            ->where('signature_image', '!=', 'PENDING')
            ->exists();
    @endphp
    <div class="page-heading mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div class="d-flex align-items-center order-2 order-md-1 mt-3 mt-md-0">
                <a href="{{ route('letters.index') }}" class="btn btn-secondary me-3" title="Back">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>

                <div>
                    <h3 class="mb-0">Letter Preview</h3>
                    <p class="text-subtitle text-muted mb-0 mt-1">Preview document content and manage approval actions.</p>
                </div>
            </div>

            <nav aria-label="breadcrumb" class="breadcrumb-header order-1 order-md-2">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('letters.index') }}">Letters</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Letter Preview</li>
                </ol>
            </nav>
        </div>
    </div>
    @if ($myPendingSignature)
        <div
            class="alert alert-warning shadow-sm border-0 rounded-3 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3"
            style="background-color: #fff3cd"
        >
            <div class="d-flex align-items-center">
                <div>
                    <h5 class="fw-bold mb-1 text-dark" style="color: #664d03 !important">
                        <i class="bi bi-exclamation-circle-fill text-warning me-2"></i>Action Required!
                    </h5>
                    <span class="text-dark small" style="color: #664d03 !important"
                        >This document is waiting for your digital signature.</span
                    >
                </div>
            </div>
            <a
                href="{{ url('signatures/letter/' . $letter->id . '/pad') }}"
                class="btn btn-success fw-bold px-4 py-2 rounded-3 shadow-sm text-nowrap"
            >
                <i class="bi bi-pen me-1"></i> Sign Now
            </a>
        </div>
    @endif
    <div class="page-content">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible shadow-sm border-0 rounded-3 show fade mb-4">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible shadow-sm border-0 rounded-3 show fade mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-header bg-transparent border-bottom-0 pt-4 px-4 pb-2">
                        <h5 class="fw-bold mb-3"><i class="bi bi-display text-primary me-2"></i> Web Preview</h5>
                        <div
                            class="alert alert-light-secondary py-2 px-3 d-flex align-items-start"
                            style="font-size: 0.85rem"
                        >
                            <i class="bi bi-info-circle-fill text-secondary me-3 fs-5" style="line-height: 0.8"></i>
                            <span class="mb-0">
                                The web preview display will connect downwards. Physical cutting of A4 paper size will
                                only occur after the document is downloaded as a PDF.
                            </span>
                        </div>
                    </div>

                    <div
                        class="card-body bg-light position-relative p-0"
                        style="border-radius: 0 0 1rem 1rem; border-top: 1px solid #eee; overflow: hidden"
                    >
                        <div
                            class="preview-wrapper"
                            style="width: 100%; height: 800px; overflow: auto; background: #e9ecef; padding: 20px 0;"
                        >
                            <div id="pdf-viewer" class="d-flex flex-column align-items-center gap-3">
                                <div class="text-muted" id="pdf-loading">
                                    <div class="spinner-border spinner-border-sm me-2" role="status"></div>
                                    Loading PDF preview...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 rounded-3 mb-4 position-sticky" style="top: 2rem">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-4">Letter Information</h5>

                        <div class="mb-3">
                            <span class="text-muted d-block" style="font-size: 0.85rem">Approval Status</span>
                            @php
                                $badgeClass = match ($letter->status) {
                                    'approved' => 'bg-success',
                                    'pending' => 'bg-warning',
                                    'rejected' => 'bg-danger',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} px-3 py-2 rounded-3 mt-1 fw-bold fs-6 shadow-sm">
                                {{ strtoupper($letter->status) }}
                            </span>
                        </div>

                        @if ($letter->status === 'rejected' && !empty($letter->reason))
                            <div class="mb-3 p-3 bg-danger-subtle border border-danger-subtle rounded-3">
                                <span class="text-danger fw-bold d-block mb-1" style="font-size: 0.85rem">
                                    <i class="bi bi-exclamation-octagon me-1"></i> Reason for Rejection:
                                </span>
                                <span class="text-danger-emphasis" style="font-size: 0.9rem">
                                    {{ $letter->reason }}
                                </span>
                            </div>
                        @endif

                        <div class="mb-3">
                            <span class="text-muted d-block" style="font-size: 0.85rem">Letter Number</span>
                            <strong class="fw-bold text-primary" style="font-size: 1.1rem">
                                {{ $letter->letter_number ?? 'Not Generated Yet (Draft)' }}
                            </strong>
                        </div>

                        <div class="mb-3">
                            <span class="text-muted d-block" style="font-size: 0.85rem">Document Creator</span>
                            <strong class="fw-bold">{{ $letter->user->name ?? '-' }}</strong>
                        </div>

                        <div class="mb-3">
                            <span class="text-muted d-block" style="font-size: 0.85rem">Date Created</span>
                            <strong class="fw-bold">{{
                                $letter->created_date
                                    ? \Carbon\Carbon::parse($letter->created_date)->format('d M Y')
                                    : '-'
                            }}</strong>
                        </div>

                        @if (in_array($letter->status, ['approved', 'printed']))
                            <div class="mb-3">
                                <span class="text-muted d-block" style="font-size: 0.85rem">Approved By</span>
                                <strong class="fw-bold">{{ $letter->approver->name ?? '-' }}</strong>
                            </div>
                            <div class="mb-3">
                                <span class="text-muted d-block" style="font-size: 0.85rem">Date Approved</span>
                                <strong class="fw-bold">{{
                                    $letter->approved_date
                                        ? \Carbon\Carbon::parse($letter->approved_date)->format('d M Y, H:i')
                                        : '-'
                                }}</strong>
                            </div>
                        @endif

                        <div class="mb-3">
                            <span class="text-muted d-block" style="font-size: 0.85rem">Letter Type</span>
                            <strong class="fw-bold">{{ ucfirst($letter->letter_type) }}</strong>
                        </div>

                        <hr class="my-4" />

                        <h5 class="fw-bold mb-3">Actions</h5>
                        <div class="d-grid gap-2">
                            @if (in_array($letter->status, ['draft', 'rejected']))
                                <form action="{{ route('letters.submit', $letter->id) }}" method="POST" class="m-0">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="btn btn-primary w-100 fw-semibold rounded-3 shadow-sm py-2"
                                        onclick="return confirm('Are you sure you want to submit for approval?');"
                                    >
                                        <i class="bi bi-send-check me-1"></i> Submit Approval
                                    </button>
                                </form>
                                <a
                                    href="{{ route('letters.edit', $letter->id) }}"
                                    class="btn btn-warning w-100 fw-semibold rounded-3 py-2"
                                >
                                    <i class="bi bi-pencil me-1"></i> Edit Content
                                </a>
                            @endif

                            @if ($letter->status === 'pending' && $isAdmin)
                                <form action="{{ route('letters.approve', $letter->id) }}" method="POST" class="m-0">
                                    @csrf
                                    <button
                                        type="submit"
                                        class="btn btn-success w-100 fw-semibold rounded-3 shadow-sm py-2"
                                        onclick="return confirm('Are you sure you want to approve this document?');"
                                    >
                                        <i class="bi bi-check-circle me-1"></i> Approve Letter
                                    </button>
                                </form>
                                <button
                                    type="button"
                                    class="btn btn-danger w-100 fw-semibold rounded-3 py-2"
                                    data-bs-toggle="modal"
                                    data-bs-target="#rejectModal"
                                >
                                    <i class="bi bi-x-circle me-1"></i> Reject
                                </button>
                            @endif

                            @if (($letter->user_id == Auth::id() || $isAdmin) && in_array($letter->status, ['approved', 'printed']))
                                @if (!$hasSigned)
                                    <a
                                        href="{{ url('signatures/letter/' . $letter->id . '/pad') }}"
                                        class="btn btn-success w-100 fw-semibold rounded-3 shadow-sm py-2"
                                    >
                                        <i class="bi bi-vector-pen me-1"></i> Sign This Letter
                                    </a>
                                @else
                                    <button class="btn btn-success w-100 fw-semibold rounded-3 shadow-sm py-2" disabled>
                                        <i class="bi bi-check-circle-fill me-1"></i> Already Signed
                                    </button>
                                @endif
                            @endif

                            @if (in_array($letter->status, ['approved', 'printed']))
                                <a
                                    href="{{ route('letters.export', $letter->id) }}"
                                    class="btn btn-danger w-100 fw-semibold rounded-3 shadow-sm py-2"
                                >
                                    <i class="bi bi-file-earmark-pdf me-1"></i> Download / Export PDF
                                </a>
                            @endif

                            @if ($isAdmin && in_array($letter->status, ['approved', 'printed']))
                                <a
                                    href="{{ route('signatures.list', ['signable' => 'letter', 'id' => $letter->id]) }}"
                                    class="btn btn-info w-100 fw-semibold rounded-3 shadow-sm py-2"
                                >
                                    <i class="bi bi-pen me-1"></i> Manage Signatures
                                </a>
                            @endif

                            @if ($isAdmin || ($letter->user_id == Auth::id() && in_array($letter->status, ['draft', 'rejected'])))
                                <form
                                    action="{{ route('letters.destroy', $letter->id) }}"
                                    method="POST"
                                    class="m-0 mt-2"
                                >
                                    @csrf
                                    @method ('DELETE')
                                    <button
                                        type="submit"
                                        class="btn btn-danger w-100 fw-semibold rounded-3 py-2"
                                        onclick="
                                            return confirm('Are you sure you want to permanently delete this letter?');
                                        "
                                    >
                                        <i class="bi bi-trash me-1"></i> Delete Letter
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-3 shadow">
                <form action="{{ route('letters.reject', $letter->id) }}" method="POST">
                    @csrf
                    <div class="modal-header border-bottom-0 pt-4 px-4">
                        <h5 class="modal-title fw-bold text-danger">
                            <i class="bi bi-exclamation-triangle"></i> Reject Letter
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-4">
                        <div class="form-group mb-0">
                            <label class="form-label fw-semibold"
                                >Reason for Rejection <span class="text-danger">*</span></label
                            >
                            <textarea
                                name="reason"
                                class="form-control"
                                rows="3"
                                required
                                placeholder="Enter detailed reason for rejection..."
                            ></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pb-4 px-4">
                        <button type="button" class="btn btn-secondary px-4 fw-semibold" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-danger px-4 fw-semibold">Reject Letter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@push('scripts')
    <!-- PDF.js library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        // Set worker URL
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        document.addEventListener("DOMContentLoaded", function () {
            // Read Base64 data and decode it to binary
            const base64Data = '{!! $pdfBase64 !!}';
            const pdfData = atob(base64Data);

            const loadingTask = pdfjsLib.getDocument({ data: pdfData });
            
            loadingTask.promise.then(function(pdf) {
                const viewer = document.getElementById('pdf-viewer');
                const loadingIndicator = document.getElementById('pdf-loading');
                if (loadingIndicator) loadingIndicator.style.display = 'none';

                // Fetch and render all pages
                for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                    pdf.getPage(pageNum).then(function(page) {
                        // Use scale 1.5 for better resolution
                        const viewport = page.getViewport({ scale: 1.5 });

                        // Create wrapper for the canvas to look like a physical page
                        const pageContainer = document.createElement('div');
                        pageContainer.className = 'pdf-page shadow-sm bg-white';
                        // Insert according to page number order to avoid async race condition rendering pages out of order
                        pageContainer.style.order = pageNum; 

                        const canvas = document.createElement('canvas');
                        const context = canvas.getContext('2d');
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;
                        canvas.style.width = '100%';
                        canvas.style.maxWidth = viewport.width + 'px';
                        canvas.style.display = 'block';

                        pageContainer.appendChild(canvas);
                        viewer.appendChild(pageContainer);

                        const renderContext = {
                            canvasContext: context,
                            viewport: viewport
                        };
                        page.render(renderContext);
                    });
                }
            }).catch(function(error) {
                console.error("Error loading PDF: ", error);
                const loadingIndicator = document.getElementById('pdf-loading');
                if (loadingIndicator) {
                    loadingIndicator.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-circle"></i> Failed to load PDF preview. Please download the document instead.</span>';
                }
            });
        });
    </script>
@endpush
@endsection
