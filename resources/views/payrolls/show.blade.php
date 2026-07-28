@extends ('layouts.dashboard')

@section ('content')
    <div class="page-heading mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
            <div class="d-flex align-items-center order-2 order-md-1 mt-3 mt-md-0">
                <a href="{{ route('payrolls.index') }}" class="btn btn-secondary me-3" title="Kembali">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>

                <div>
                    <h3 class="mb-0">Payroll Detail</h3>
                    <p class="text-subtitle text-muted mb-0 mt-1">View complete details of salary payment for period {{ $payroll->period_label }}</p>
                </div>
            </div>

            <nav aria-label="breadcrumb" class="breadcrumb-header order-1 order-md-2">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('payrolls.index') }}">Payrolls</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Payroll Detail</li>
                </ol>
            </nav>
        </div>
    </div>
    {{-- action buttons --}}
    <div class="row mb-3">
        <div class="col-12 d-flex flex-wrap gap-2 justify-content-start justify-content-md-end">
            @if (\App\Constants\Roles::hasFullFinanceAccess(session('role')) && $payroll->status !== 'paid')
                <a href="{{ route('payrolls.edit', $payroll->id) }}" class="btn btn-warning btn-sm px-3 rounded-3">
                    <i class="bi bi-pencil"></i> Edit Data
                </a>
            @endif

            @if ($payroll->status === 'paid')
                <button id="btn-print-top" class="btn btn-primary btn-sm px-3 shadow-sm rounded-3" disabled>
                    <i class="bi bi-printer"></i> Print Payslip
                </button>
                <button id="btn-download-top" class="btn btn-success btn-sm px-3 shadow-sm rounded-3" disabled>
                    <i class="bi bi-download"></i> Download PDF
                </button>
            @else
                <button
                    class="btn btn-outline-secondary btn-sm px-3 rounded-3"
                    disabled
                    data-bs-toggle="tooltip"
                    title="Payslip can only be printed if status is 'Paid'."
                >
                    <i class="bi bi-lock-fill"></i> Print (Locked)
                </button>
            @endif
        </div>
    </div>
    <section class="section">
        <div class="row">
            {{-- left card: profile & status --}}
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body py-4">
                        <div class="text-center mb-4">
                            <div
                                class="avatar avatar-xl bg-primary text-white mb-3 d-inline-flex align-items-center justify-content-center shadow-sm"
                                style="
                                    width: 80px;
                                    height: 80px;
                                    border-radius: 50%;
                                    font-size: 1.8rem;
                                    font-weight: bold;
                                "
                            >
                                {{
                                    strtoupper(
                                        substr($payroll->employee?->fullname, 0, 1),
                                    )
                                }}
                            </div>
                            <h5 class="fw-bold mb-1">{{ $payroll->employee?->fullname }}</h5>
                            <p class="text-muted small mb-0">{{ $payroll->employee?->position?->name ?? 'Employee' }}</p>
                            <div class="mt-3">
                                <span
                                    class="badge {{ $payroll->status === 'paid' ? 'bg-success' : ($payroll->status === 'approved' ? 'bg-info' : 'bg-secondary') }} px-3 py-2 rounded-3"
                                >
                                    {{ strtoupper($payroll->status) }}
                                </span>
                            </div>
                        </div>

                        <div class="divider border-bottom mb-3"></div>

                        <div class="list-info">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small text-uppercase fw-bold">NIK</span>
                                <span class="small fw-bold text-body">{{ $payroll->employee?->nik ?? '-' }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small text-uppercase fw-bold">NPWP</span>
                                <span class="small fw-bold text-body">{{ $payroll->employee?->npwp ?? '-' }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small text-uppercase fw-bold">Period</span>
                                <span class="small fw-bold text-body">{{ $payroll->period_label }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small text-uppercase fw-bold">Payment Date</span>
                                <span class="small fw-bold text-body">{{
                                    $payroll->pay_date
                                        ? \Carbon\Carbon::parse($payroll->pay_date)->translatedFormat('d M Y')
                                        : '-'
                                }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-0">
                                <span class="text-muted small text-uppercase fw-bold">Ref No.</span>
                                <span class="small text-body">#{{ str_pad($payroll->id, 5, '0', STR_PAD_LEFT) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- right card: earnings & deductions details --}}
            <div class="col-md-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header border-bottom py-3">
                        <h5 class="card-title mb-0"><i class="bi bi-calculator me-2"></i> Payroll Calculation</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="row g-0">
                            {{-- earnings --}}
                            <div class="col-md-6 border-end p-4">
                                <div class="d-flex align-items-center mb-4">
                                    <div
                                        class="bg-success text-white rounded-circle p-2 me-3 d-flex align-items-center justify-content-center"
                                        style="width: 35px; height: 35px"
                                    >
                                        <i class="bi bi-plus-lg mb-2"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0 text-success uppercase">Earnings</h6>
                                </div>

                                <table class="table table-sm table-borderless small">
                                    <tr>
                                        <td>Basic Salary</td>
                                        <td class="text-end fw-bold text-body">
                                            Rp {{ number_format($payroll->salary, 0, ',', '.') }}
                                        </td>
                                    </tr>
                                    @if ($payroll->transport_allowance > 0)
                                        <tr>
                                            <td>Transport Allowance</td>
                                            <td class="text-end text-body">
                                                Rp {{ number_format($payroll->transport_allowance, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endif
                                    @if ($payroll->meal_allowance > 0)
                                        <tr>
                                            <td>Meal Allowance</td>
                                            <td class="text-end text-body">
                                                Rp {{ number_format($payroll->meal_allowance, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endif
                                    @if ($payroll->position_allowance > 0)
                                        <tr>
                                            <td>Position Allowance</td>
                                            <td class="text-end text-body">
                                                Rp {{ number_format($payroll->position_allowance, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endif
                                    @if ($payroll->overtime_amount > 0)
                                        <tr>
                                            <td>Overtime Amount</td>
                                            <td class="text-end text-body">
                                                Rp {{ number_format($payroll->overtime_amount, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endif
                                    @php
                                        $bonus = $payroll->performance_bonus + $payroll->attendance_bonus + $payroll->other_bonus;
                                    @endphp
                                    @if ($bonus > 0)
                                        <tr>
                                            <td>Bonus & Incentives</td>
                                            <td class="text-end text-body">
                                                Rp {{ number_format($bonus, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                                <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                                    <span class="fw-bold small">Total Earnings (A)</span>
                                    <span class="fw-bold text-success"
                                        >Rp {{ number_format($payroll->total_earnings - $payroll->reimbursement, 0, ',', '.') }}</span
                                    >
                                </div>
                            </div>

                            {{-- deductions --}}
                            <div class="col-md-6 p-4">
                                <div class="d-flex align-items-center mb-4">
                                    <div
                                        class="bg-danger text-white rounded-circle p-2 me-3 d-flex align-items-center justify-content-center"
                                        style="width: 35px; height: 35px"
                                    >
                                        <i class="bi bi-dash-lg mb-2"></i>
                                    </div>
                                    <h6 class="fw-bold mb-0 text-danger uppercase">Deductions</h6>
                                </div>

                                <table class="table table-sm table-borderless small">
                                    @if ($payroll->late_deduction > 0)
                                        <tr>
                                            <td>Lateness</td>
                                            <td class="text-end text-danger fw-bold">
                                                - Rp {{ number_format($payroll->late_deduction, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endif
                                    @if ($payroll->absent_deduction > 0)
                                        <tr>
                                            <td>Absence</td>
                                            <td class="text-end text-danger fw-bold">
                                                - Rp {{ number_format($payroll->absent_deduction, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endif
                                    @if ($payroll->bpjs_kes + $payroll->bpjs_tk > 0)
                                        <tr>
                                            <td>BPJS Contribution</td>
                                            <td class="text-end text-danger fw-bold">
                                                - Rp {{ number_format($payroll->bpjs_kes + $payroll->bpjs_tk, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endif
                                    @if ($payroll->pph21 > 0)
                                        @php
                                            $rate = floatval($payroll->employee?->pph21_rate ?? 0.5);
                                            $baseTax = ($payroll->total_earnings - ($payroll->reimbursement ?? 0)) - ($payroll->total_deductions - $payroll->pph21);
                                        @endphp
                                        <tr>
                                            <td>
                                                Income Tax (PPh 21) <br>
                                                <small class="text-muted" style="font-size: 0.75rem;">(Rp {{ number_format($baseTax, 0, ',', '.') }} &times; {{ $rate }}%)</small>
                                            </td>
                                            <td class="text-end text-danger fw-bold align-middle">
                                                - Rp {{ number_format($payroll->pph21, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endif
                                    @if ($payroll->penalty_amount > 0)
                                        <tr>
                                            <td>Penalty Amount</td>
                                            <td class="text-end text-danger fw-bold">
                                                - Rp {{ number_format($payroll->penalty_amount, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endif
                                    @if ($payroll->other_deduction > 0)
                                        <tr>
                                            <td>Other Deductions</td>
                                            <td class="text-end text-danger fw-bold">
                                                - Rp {{ number_format($payroll->other_deduction, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endif
                                </table>
                                <div class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                                    <span class="fw-bold small">Total Deductions (B)</span>
                                    <span class="fw-bold text-danger"
                                        >Rp {{ number_format($payroll->total_deductions, 0, ',', '.') }}</span
                                    >
                                </div>
                            </div>
                        </div>

                        @if ($payroll->reimbursement > 0)
                        <div class="card-footer p-3 border-top-0 bg-info bg-opacity-10 border-bottom" style="border-left: 6px solid var(--bs-info);">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-info"><i class="bi bi-star-fill me-2"></i> Total Reimbursement (C)</span>
                                <span class="fw-bold text-info fs-5">Rp {{ number_format($payroll->reimbursement, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        @endif
                        {{-- footer card: take home pay (new polish) --}}
                        <div
                            class="card-footer p-4 border-top-0 bg-primary bg-opacity-10"
                            style="border-left: 6px solid var(--bs-primary); border-radius: 0 0 0.7rem 0.7rem"
                        >
                            <div class="row align-items-center">
                                <div class="col-md-7 mb-3 mb-md-0">
                                    <h6 class="fw-bold text-uppercase small text-primary mb-1">
                                        <i class="bi bi-cash-stack me-2"></i> Net Salary Received (Take Home Pay)
                                        @if ($payroll->reimbursement > 0) (A - B + C) @else (A - B) @endif
                                    </h6>
                                    @if (class_exists('App\Helpers\Terbilang'))
                                        <p
                                            class="mb-0 small fst-italic text-secondary"
                                            style="letter-spacing: 0.5px"
                                        >"{{ \App\Helpers\Terbilang::make($payroll->net_salary) }} Rupiah"</p>
                                    @endif
                                </div>
                                <div class="col-md-5 text-md-end">
                                    <h2
                                        class="fw-bold text-primary mb-0"
                                        style="text-shadow: 1px 1px 1px rgba(0, 0, 0, 0.05)"
                                    >
                                        Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}
                                    </h2>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Preview PDF Section --}}
        @if ($payroll->status === 'paid')
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-3 mb-4">
                    <div class="card-header bg-transparent border-bottom-0 pt-4 px-4 pb-2">
                        <h5 class="fw-bold mb-3"><i class="bi bi-display text-primary me-2"></i> Payslip Preview</h5>
                        <div class="alert alert-light-secondary py-2 px-3 d-flex align-items-start" style="font-size: 0.85rem">
                            <i class="bi bi-info-circle-fill text-secondary me-3 fs-5" style="line-height: 0.8"></i>
                            <span class="mb-0">
                                The document preview uses PDF generation behind the scenes. Wait a moment for it to render.
                            </span>
                        </div>
                    </div>
                    <div class="card-body bg-light position-relative p-0" style="border-radius: 0 0 1rem 1rem; border-top: 1px solid #eee; overflow: hidden">
                        <div class="preview-wrapper" style="width: 100%; height: 800px; overflow: auto; background: #e9ecef; padding: 20px 0;">
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
        </div>
        <iframe id="pdf-viewer-frame" src="{{ route('payrolls.slip', $payroll->id) }}?mode=render" style="position: absolute; width: 850px; height: 1100px; left: -9999px; top: -9999px; border: none; visibility: hidden;" scrolling="no"></iframe>
        @endif
    </section>
@endsection

@push ('scripts')
    @if ($payroll->status === 'paid')
    <!-- PDF.js library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        // Set worker URL
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        const viewerFrame = document.getElementById("pdf-viewer-frame");
        const btnDownloadTop = document.getElementById("btn-download-top");
        const btnPrintTop = document.getElementById("btn-print-top");
        
        let globalPdfUrl = null;

        window.addEventListener('message', function(event) {
            if (event.data && event.data.type === 'pdf_ready') {
                globalPdfUrl = event.data.url;
                
                if (btnDownloadTop) btnDownloadTop.disabled = false;
                if (btnPrintTop) btnPrintTop.disabled = false;

                const loadingTask = pdfjsLib.getDocument(globalPdfUrl);
                
                loadingTask.promise.then(function(pdf) {
                    const viewer = document.getElementById('pdf-viewer');
                    const loadingIndicator = document.getElementById('pdf-loading');
                    if (loadingIndicator) loadingIndicator.style.display = 'none';

                    for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                        pdf.getPage(pageNum).then(function(page) {
                            const viewport = page.getViewport({ scale: 1.5 });
                            const pageContainer = document.createElement('div');
                            pageContainer.className = 'pdf-page shadow-sm bg-white';
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

                            page.render({ canvasContext: context, viewport: viewport });
                        });
                    }
                }).catch(function(error) {
                    console.error("Error loading PDF: ", error);
                    const loadingIndicator = document.getElementById('pdf-loading');
                    if (loadingIndicator) {
                        loadingIndicator.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-circle"></i> Failed to load PDF preview.</span>';
                    }
                });
            }
        });

        if (btnDownloadTop) {
            btnDownloadTop.addEventListener("click", function() {
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
        }

        if (btnPrintTop) {
            btnPrintTop.addEventListener("click", function() {
                viewerFrame.contentWindow.postMessage("trigger_print", "*");
            });
        }
    </script>
    @endif
    <script>
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
@endpush
