@extends ('layouts.dashboard')

@section ('content')
    <div class="page-heading mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div class="d-flex align-items-center order-2 order-md-1 mt-3 mt-md-0">
                <a href="{{ route('payrolls.index') }}" class="btn btn-secondary me-3" title="Back">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>

                <div>
                    <h3 class="mb-0">Payslip Preview</h3>
                    <p class="text-subtitle text-muted mb-0 mt-1">Preview payslip document and manage print actions.</p>
                </div>
            </div>

            <nav aria-label="breadcrumb" class="breadcrumb-header order-1 order-md-2">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('payrolls.index') }}">Payrolls</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Payslip Preview</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="page-content">
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
                                The document preview uses PDF generation behind the scenes. Wait a moment for it to render.
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
                        <h5 class="fw-bold mb-4">Payslip Information</h5>

                        <div class="mb-3">
                            <span class="text-muted d-block" style="font-size: 0.85rem">Status</span>
                            @php
                                $badgeClass = match ($payroll->status) {
                                    'paid' => 'bg-success',
                                    'approved' => 'bg-info',
                                    'draft' => 'bg-secondary',
                                    default => 'bg-secondary',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} px-3 py-2 rounded-3 mt-1 fw-bold fs-6 shadow-sm">
                                {{ strtoupper($payroll->status) }}
                            </span>
                        </div>

                        <div class="mb-3">
                            <span class="text-muted d-block" style="font-size: 0.85rem">Employee Name</span>
                            <strong class="fw-bold">{{ $payroll->employee?->fullname ?? '-' }}</strong>
                        </div>
                        
                        <div class="mb-3">
                            <span class="text-muted d-block" style="font-size: 0.85rem">Period</span>
                            <strong class="fw-bold">{{ DateTime::createFromFormat("!m", $payroll->period_month)->format("F") }} {{ $payroll->period_year }}</strong>
                        </div>

                        <div class="mb-3">
                            <span class="text-muted d-block" style="font-size: 0.85rem">Total Take Home Pay</span>
                            <strong class="fw-bold text-success fs-5">Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}</strong>
                        </div>

                        <hr class="my-4" />

                        <h5 class="fw-bold mb-3">Actions</h5>
                        <div class="d-grid gap-2">
                            <button id="btn-print" class="btn btn-primary fw-semibold rounded-3 shadow-sm py-2" disabled>
                                <i class="bi bi-printer me-1"></i> Print Payslip
                            </button>
                            <button id="btn-download" class="btn btn-success fw-semibold rounded-3 shadow-sm py-2" disabled>
                                <i class="bi bi-download me-1"></i> Download PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Hidden iframe to generate the PDF via html2pdf -->
    <iframe id="pdf-viewer-frame" src="{{ route('payrolls.slip', $payroll->id) }}?mode=render" style="position: absolute; width: 850px; height: 1100px; left: -9999px; top: -9999px; border: none; visibility: hidden;" scrolling="no"></iframe>

@push('scripts')
    <!-- PDF.js library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        // Set worker URL
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        const viewerFrame = document.getElementById("pdf-viewer-frame");
        const btnDownload = document.getElementById("btn-download");
        const btnPrint = document.getElementById("btn-print");
        
        let globalPdfUrl = null;

        window.addEventListener('message', function(event) {
            if (event.data && event.data.type === 'pdf_ready') {
                globalPdfUrl = event.data.url;
                
                // Enable buttons
                btnDownload.disabled = false;
                btnPrint.disabled = false;

                const loadingTask = pdfjsLib.getDocument(globalPdfUrl);
                
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
            }
        });

        btnDownload.addEventListener("click", function() {
            const btn = this;
            const originalHtml = btn.innerHTML;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Loading...';
            btn.disabled = true;

            viewerFrame.contentWindow.postMessage("trigger_download", "*");
            
            setTimeout(() => {
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }, 3000);
        });

        btnPrint.addEventListener("click", function() {
            viewerFrame.contentWindow.postMessage("trigger_print", "*");
        });
    </script>
@endpush
@endsection
