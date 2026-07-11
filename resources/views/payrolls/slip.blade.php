<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - {{ $payroll->employee?->fullname }}</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            box-sizing: border-box;
        }

        body { 
            background-color: #525659;
            margin: 0;
            padding: 20px;
            display: flex; 
            justify-content: center; 
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        #slip-padd {
            width: 800px;
            min-height: 1130px;
            background-color: #ffffff;
            padding: 45px;
            box-shadow: 0 0 15px rgba(0,0,0,0.3);
            color: #333333;
        }

        /* corevo primary colors */
        .text-teal { color: #44c0b5 !important; }
        .bg-teal { background-color: #44c0b5 !important; }
        .bg-teal-light { background-color: #eff9f8 !important; }
        .border-teal { border-color: #44c0b5 !important; }
        
        .border-soft { border-color: #eef0f2 !important; }
        .text-dark-gray { color: #4b5563 !important; }

        /* grid lock */
        .row { display: flex !important; flex-wrap: wrap !important; }
        .col-6 { flex: 0 0 auto !important; width: 50% !important; }
        .col-7 { flex: 0 0 auto !important; width: 58.33% !important; }
        .col-5 { flex: 0 0 auto !important; width: 41.66% !important; }
        .col-8 { flex: 0 0 auto !important; width: 66.66% !important; }
        .col-4 { flex: 0 0 auto !important; width: 33.33% !important; }
        .col-9 { flex: 0 0 auto !important; width: 75% !important; }
        .col-3 { flex: 0 0 auto !important; width: 25% !important; }

        .border-end { border-right: 1px solid #eef0f2 !important; }
        .text-end { text-align: right !important; }
        .text-start { text-align: left !important; }
        
        /* table modifications */
        .table-details td { padding: 4px 0; font-size: 0.85rem; }

        /* when printing */
        @media print {
            body { 
                background-color: white !important; 
                padding: 0 !important; 
                margin: 0 !important; 
            }
            #slip-padd {
                box-shadow: none !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: none !important;
                min-height: auto !important; 
                height: auto !important;
                padding: 0 !important;
            }
            .d-print-none { display: none !important; }
        }
    </style>
</head>
<body>

    {{-- manual navigation (hidden when printed or generating PDF) --}}
    <div id="print-controls" class="text-center mt-2 mb-4 d-print-none" style="display: none;">
        <button onclick="window.print()" class="btn btn-primary shadow-sm me-2" style="padding: 6px 12px; font-weight: bold; cursor: pointer;">
            Reprint
        </button>
        <button onclick="window.close()" class="btn btn-secondary shadow-sm" style="padding: 6px 12px; font-weight: bold; cursor: pointer;">
            Close Page
        </button>
    </div>

    <div id="slip-padd">
        @php
            $reimbursement = $payroll->reimbursement ?? 0;
            $totalEarningsWithoutReimbursement = $payroll->total_earnings - $reimbursement;
            $takeHomePayWithoutReimbursement = $payroll->net_salary - $reimbursement;
        @endphp

        @php
            $totalPages = $reimbursement > 0 ? 3 : 2;
        @endphp

        <!-- PAGE 1: SALARY SLIP -->
        <div class="pdf-page">
            {{-- letterhead --}}
            <div class="row align-items-center mb-4 pb-4 border-bottom border-soft border-2">
                <div class="col-9 d-flex align-items-center">
                    <div class="d-flex align-items-center justify-content-center me-3 flex-shrink-0 text-teal" style="width: 55px; height: 55px; border-radius: 12px;">
                        <img src="{{ asset('img/aratech-logo-only.png') }}" class="logo-light" style="height:70px; width:auto; max-width:150px; object-fit:contain;">
                    </div>
                    <div>
                        <h4 class="fw-bolder mb-0 text-dark text-nowrap" style="letter-spacing: 0.5px; font-size: 1.4rem;">PT. ARATECH NUSANTARA INDONESIA</h4>
                        <p class="mb-0 text-muted" style="font-size: 0.8rem;">The Plaza Office Tower 41st Floor, Jl. M.H. Thamrin No.Kav. 28-30, DKI Jakarta</p>
                        <p class="mb-0 text-muted" style="font-size: 0.8rem;">Web: www.aratechnology.id | Email: office@aratechnology.id </p>
                    </div>
                </div>
                <div class="col-3 text-end">
                    <h5 class="fw-bolder mb-2 text-black" style="text-transform: uppercase; letter-spacing: 1px;">Salary <br> Slip</h5>
                    <span class="badge border border-teal text-teal bg-white px-3 py-1" style="font-size: 0.75rem; border-radius: 20px;">
                        {{ strtoupper($payroll->status) }}
                    </span>
                </div>
            </div>

            {{-- employee information --}}
            <div class="p-3 mb-4 rounded bg-teal-light border-start border-teal" style="border-left-width: 4px !important;">
                <div class="row g-3">
                    <div class="col-6">
                        <table class="table-details w-100 text-dark-gray">
                            <tr><td style="width: 110px;">Reference No.</td><td width="10">:</td><td class="fw-bold text-dark">PAY/{{ date('ym', strtotime($payroll->pay_date ?? now())) }}/{{ str_pad($payroll->id, 6, '0', STR_PAD_LEFT) }}</td></tr>
                            <tr><td>Employee Name</td><td>:</td><td class="fw-bold text-dark">{{ $payroll->employee?->fullname }}</td></tr>
                            <tr><td>Employee NIK</td><td>:</td><td class="text-dark">{{ $payroll->employee?->nik ?? '-' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-6">
                        <table class="table-details w-100 text-dark-gray">
                            <tr><td style="width: 110px;">Payroll Period</td><td width="10">:</td><td class="fw-bold text-dark">{{ $payroll->period_label ?? DateTime::createFromFormat('!m', $payroll->period_month)->format('F').' '.$payroll->period_year }}</td></tr>
                            <tr><td>Print Date</td><td>:</td><td class="text-dark">{{ date('d M Y') }}</td></tr>
                            <tr><td>Payment Date</td><td>:</td><td class="text-dark">{{ $payroll->pay_date ? date('d M Y', strtotime($payroll->pay_date)) : '-' }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- SECTION 1: PENDAPATAN (EARNINGS) --}}
            <div class="mb-3 border border-soft rounded overflow-hidden">
                <div class="p-2 px-3 border-bottom border-soft bg-teal-light">
                    <h6 class="fw-bold mb-0 small text-teal text-uppercase" style="letter-spacing: 0.5px;">1. Earnings</h6>
                </div>
                <div class="p-3">
                    <table class="table table-sm table-borderless mb-0 small text-dark-gray">
                        <tr><td class="py-1">Basic Salary</td><td class="py-1 text-end text-dark">Rp {{ number_format($payroll->salary, 0, ',', '.') }}</td></tr>
                        @if($payroll->transport_allowance > 0) <tr><td class="py-1">Transport Allowance</td><td class="py-1 text-end text-dark">Rp {{ number_format($payroll->transport_allowance, 0, ',', '.') }}</td></tr> @endif
                        @if($payroll->meal_allowance > 0) <tr><td class="py-1">Meal Allowance</td><td class="py-1 text-end text-dark">Rp {{ number_format($payroll->meal_allowance, 0, ',', '.') }}</td></tr> @endif
                        @if($payroll->position_allowance > 0) <tr><td class="py-1">Position Allowance</td><td class="py-1 text-end text-dark">Rp {{ number_format($payroll->position_allowance, 0, ',', '.') }}</td></tr> @endif
                        @if($payroll->overtime_amount > 0) <tr><td class="py-1">Overtime Amount</td><td class="py-1 text-end text-dark">Rp {{ number_format($payroll->overtime_amount, 0, ',', '.') }}</td></tr> @endif
                        @php $bonus = $payroll->performance_bonus + $payroll->attendance_bonus + $payroll->other_bonus; @endphp
                        @if($bonus > 0) <tr><td class="py-1">Bonus & Incentives</td><td class="py-1 text-end text-dark">Rp {{ number_format($bonus, 0, ',', '.') }}</td></tr> @endif
                    </table>
                </div>
                @php $totalEarningsWithoutReimbursement = $payroll->total_earnings - $reimbursement; @endphp
                <div class="p-2 px-3 border-top border-soft" style="background-color: #fafafa;">
                    <div class="d-flex justify-content-between fw-bold small">
                        <span class="text-dark">Total Earnings (A)</span><span class="text-teal">Rp {{ number_format($totalEarningsWithoutReimbursement, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- SECTION 2: POTONGAN (DEDUCTIONS) --}}
            <div class="mb-4 border border-soft rounded overflow-hidden">
                <div class="p-2 px-3 border-bottom border-soft" style="background-color: #fdf2f2;">
                    <h6 class="fw-bold mb-0 small text-danger text-uppercase" style="letter-spacing: 0.5px;">2. Deductions</h6>
                </div>
                <div class="p-3">
                    <table class="table table-sm table-borderless mb-0 small text-dark-gray">
                        @if($payroll->late_deduction > 0) <tr><td class="py-1">Lateness</td><td class="py-1 text-end text-dark">Rp {{ number_format($payroll->late_deduction, 0, ',', '.') }}</td></tr> @endif
                        @if($payroll->absent_deduction > 0) <tr><td class="py-1">Absence</td><td class="py-1 text-end text-dark">Rp {{ number_format($payroll->absent_deduction, 0, ',', '.') }}</td></tr> @endif
                        @if($payroll->bpjs_kes + $payroll->bpjs_tk > 0) <tr><td class="py-1">BPJS Contribution</td><td class="py-1 text-end text-dark">Rp {{ number_format($payroll->bpjs_kes + $payroll->bpjs_tk, 0, ',', '.') }}</td></tr> @endif
                        @if($payroll->pph21 > 0)
                            @php
                                $rate = floatval($payroll->employee?->pph21_rate ?? 0.5);
                                $baseTax = ($payroll->total_earnings - ($payroll->reimbursement ?? 0)) - ($payroll->total_deductions - $payroll->pph21);
                            @endphp
                            <tr>
                                <td class="py-1">
                                    PPh 21 <br>
                                    <small class="text-muted" style="font-size: 0.7rem;">(Rp {{ number_format($baseTax, 0, ',', '.') }} &times; {{ $rate }}%)</small>
                                </td>
                                <td class="py-1 text-end text-dark align-middle">Rp {{ number_format($payroll->pph21, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                        @if($payroll->penalty_amount > 0) <tr><td class="py-1">Penalty Amount</td><td class="py-1 text-end text-dark">Rp {{ number_format($payroll->penalty_amount, 0, ',', '.') }}</td></tr> @endif
                        @if($payroll->other_deduction > 0) <tr><td class="py-1">Other Deductions</td><td class="py-1 text-end text-dark">Rp {{ number_format($payroll->other_deduction, 0, ',', '.') }}</td></tr> @endif
                        @if($payroll->total_deductions == 0) <tr><td class="py-1 fst-italic text-muted">No Deductions</td><td class="py-1 text-end text-dark">Rp 0</td></tr> @endif
                    </table>
                </div>
                <div class="p-2 px-3 border-top border-soft" style="background-color: #fafafa;">
                    <div class="d-flex justify-content-between fw-bold small">
                        <span class="text-dark">Total Deductions (B)</span><span class="text-danger">Rp {{ number_format($payroll->total_deductions, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- footer notes --}}
            <div class="mt-4 pt-4 text-center border-top border-soft">
                <p class="text-muted mb-0 fw-bold" style="font-size: 0.85rem;">
                    This document is system-generated and does not require a signature.
                </p>
                <p class="text-muted mb-0 mt-1" style="font-size: 0.75rem;">
                    Official proof of payment for PT. Aratech Nusantara Indonesia. Printed on {{ date('d M Y H:i') }} | Page 1 of {{ $totalPages }}
                </p>
            </div>
        </div>


        <!-- PAGE 2: REIMBURSEMENT -->
        @if($reimbursement > 0)
        <div style="page-break-before: always;"></div>
        <div class="pdf-page" style="padding-top: 45px;">
            {{-- letterhead --}}
            <div class="row align-items-center mb-4 pb-4 border-bottom border-soft border-2">
                <div class="col-9 d-flex align-items-center">
                    <div class="d-flex align-items-center justify-content-center me-3 flex-shrink-0 text-teal" style="width: 55px; height: 55px; border-radius: 12px;">
                        <img src="{{ asset('img/aratech-logo-only.png') }}" class="logo-light" style="height:70px; width:auto; max-width:150px; object-fit:contain;">
                    </div>
                    <div>
                        <h4 class="fw-bolder mb-0 text-dark text-nowrap" style="letter-spacing: 0.5px; font-size: 1.4rem;">PT. ARATECH NUSANTARA INDONESIA</h4>
                        <p class="mb-0 text-muted" style="font-size: 0.8rem;">The Plaza Office Tower 41st Floor, Jl. M.H. Thamrin No.Kav. 28-30, DKI Jakarta</p>
                        <p class="mb-0 text-muted" style="font-size: 0.8rem;">Web: www.aratechnology.id | Email: office@aratechnology.id </p>
                    </div>
                </div>
                <div class="col-3 text-end">
                    <h6 class="fw-bolder mb-2 text-black" style="text-transform: uppercase; letter-spacing: 1px;">Reimbursement <br> Slip</h6>
                    <span class="badge border border-teal text-teal bg-white px-3 py-1" style="font-size: 0.75rem; border-radius: 20px;">
                        {{ strtoupper($payroll->status) }}
                    </span>
                </div>
            </div>

            {{-- employee information --}}
            <div class="p-3 mb-4 rounded bg-teal-light border-start border-teal" style="border-left-width: 4px !important;">
                <div class="row g-3">
                    <div class="col-6">
                        <table class="table-details w-100 text-dark-gray">
                            <tr><td style="width: 110px;">Reference No.</td><td width="10">:</td><td class="fw-bold text-dark">PAY/{{ date('ym', strtotime($payroll->pay_date ?? now())) }}/{{ str_pad($payroll->id, 6, '0', STR_PAD_LEFT) }}</td></tr>
                            <tr><td>Employee Name</td><td>:</td><td class="fw-bold text-dark">{{ $payroll->employee?->fullname }}</td></tr>
                            <tr><td>Employee NIK</td><td>:</td><td class="text-dark">{{ $payroll->employee?->nik ?? '-' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-6">
                        <table class="table-details w-100 text-dark-gray">
                            <tr><td style="width: 110px;">Payroll Period</td><td width="10">:</td><td class="fw-bold text-dark">{{ $payroll->period_label ?? DateTime::createFromFormat('!m', $payroll->period_month)->format('F').' '.$payroll->period_year }}</td></tr>
                            <tr><td>Print Date</td><td>:</td><td class="text-dark">{{ date('d M Y') }}</td></tr>
                            <tr><td>Payment Date</td><td>:</td><td class="text-dark">{{ $payroll->pay_date ? date('d M Y', strtotime($payroll->pay_date)) : '-' }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- SECTION: REIMBURSEMENT --}}
            <div class="mb-3 border border-soft rounded overflow-hidden">
                <div class="p-2 px-3 border-bottom border-soft bg-teal-light">
                    <h6 class="fw-bold mb-0 small text-teal text-uppercase" style="letter-spacing: 0.5px;">1. Reimbursement Details</h6>
                </div>
                <div class="p-3">
                    <table class="table table-sm table-borderless mb-0 small text-dark-gray">
                        @if(isset($reimbursements) && $reimbursements->count() > 0)
                            @foreach($reimbursements as $claim)
                                <tr><td class="py-1">{{ $claim->title }}</td><td class="py-1 text-end text-dark">Rp {{ number_format($claim->amount, 0, ',', '.') }}</td></tr>
                            @endforeach
                        @else
                            <tr><td class="py-1">Reimbursement Amount</td><td class="py-1 text-end text-dark">Rp {{ number_format($reimbursement, 0, ',', '.') }}</td></tr>
                        @endif
                    </table>
                </div>
                <div class="p-2 px-3 border-top border-soft" style="background-color: #fafafa;">
                    <div class="d-flex justify-content-between fw-bold small">
                        <span class="text-dark">Total Reimbursement (B)</span><span class="text-teal">Rp {{ number_format($reimbursement, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            {{-- footer notes --}}
            <div class="mt-4 pt-4 text-center border-top border-soft">
                <p class="text-muted mb-0 fw-bold" style="font-size: 0.85rem;">
                    This document is system-generated and does not require a signature.
                </p>
                <p class="text-muted mb-0 mt-1" style="font-size: 0.75rem;">
                    Official proof of payment for PT. Aratech Nusantara Indonesia. Printed on {{ date('d M Y H:i') }} | Page 2 of {{ $totalPages }}
                </p>
            </div>
        </div>
        @endif


        <!-- PAGE 3: SUMMARY -->
        <div style="page-break-before: always;"></div>
        <div class="pdf-page" style="padding-top: 45px;">
            {{-- letterhead --}}
            <div class="row align-items-center mb-4 pb-4 border-bottom border-soft border-2">
                <div class="col-9 d-flex align-items-center">
                    <div class="d-flex align-items-center justify-content-center me-3 flex-shrink-0 text-teal" style="width: 55px; height: 55px; border-radius: 12px;">
                        <img src="{{ asset('img/aratech-logo-only.png') }}" class="logo-light" style="height:70px; width:auto; max-width:150px; object-fit:contain;">
                    </div>
                    <div>
                        <h4 class="fw-bolder mb-0 text-dark text-nowrap" style="letter-spacing: 0.5px; font-size: 1.4rem;">PT. ARATECH NUSANTARA INDONESIA</h4>
                        <p class="mb-0 text-muted" style="font-size: 0.8rem;">The Plaza Office Tower 41st Floor, Jl. M.H. Thamrin No.Kav. 28-30, DKI Jakarta</p>
                        <p class="mb-0 text-muted" style="font-size: 0.8rem;">Web: www.aratechnology.id | Email: office@aratechnology.id </p>
                    </div>
                </div>
                <div class="col-3 text-end">
                    <h5 class="fw-bolder mb-2 text-black" style="text-transform: uppercase; letter-spacing: 1px;">Summary <br> Slip</h5>
                    <span class="badge border border-teal text-teal bg-white px-3 py-1" style="font-size: 0.75rem; border-radius: 20px;">
                        {{ strtoupper($payroll->status) }}
                    </span>
                </div>
            </div>

            {{-- employee information --}}
            <div class="p-3 mb-4 rounded bg-teal-light border-start border-teal" style="border-left-width: 4px !important;">
                <div class="row g-3">
                    <div class="col-6">
                        <table class="table-details w-100 text-dark-gray">
                            <tr><td style="width: 110px;">Reference No.</td><td width="10">:</td><td class="fw-bold text-dark">PAY/{{ date('ym', strtotime($payroll->pay_date ?? now())) }}/{{ str_pad($payroll->id, 6, '0', STR_PAD_LEFT) }}</td></tr>
                            <tr><td>Employee Name</td><td>:</td><td class="fw-bold text-dark">{{ $payroll->employee?->fullname }}</td></tr>
                            <tr><td>Employee NIK</td><td>:</td><td class="text-dark">{{ $payroll->employee?->nik ?? '-' }}</td></tr>
                        </table>
                    </div>
                    <div class="col-6">
                        <table class="table-details w-100 text-dark-gray">
                            <tr><td style="width: 110px;">Payroll Period</td><td width="10">:</td><td class="fw-bold text-dark">{{ $payroll->period_label ?? DateTime::createFromFormat('!m', $payroll->period_month)->format('F').' '.$payroll->period_year }}</td></tr>
                            <tr><td>Print Date</td><td>:</td><td class="text-dark">{{ date('d M Y') }}</td></tr>
                            <tr><td>Payment Date</td><td>:</td><td class="text-dark">{{ $payroll->pay_date ? date('d M Y', strtotime($payroll->pay_date)) : '-' }}</td></tr>
                        </table>
                    </div>
                </div>
            </div>

            {{-- SUMMARY KANAN KIRI --}}
        <div class="row g-0 mb-4 border border-soft rounded overflow-hidden align-items-stretch">
            <div class="col-6 border-end d-flex flex-column">
                <div class="p-2 px-3 border-bottom border-soft bg-teal-light">
                    <h6 class="fw-bold mb-0 small text-teal text-uppercase" style="letter-spacing: 0.5px;">Earnings Summary</h6>
                </div>
                <div class="p-3 flex-grow-1">
                    <table class="table table-sm table-borderless mb-0 small text-dark-gray">
                        <tr><td class="py-1 fw-bold text-dark" colspan="2">Total Earnings (A)</td></tr>
                        <tr><td class="py-1 ps-3 text-muted">&bull; Basic Salary</td><td class="py-1 text-end text-muted">Rp {{ number_format($payroll->salary, 0, ',', '.') }}</td></tr>
                        @if($payroll->transport_allowance > 0) <tr><td class="py-1 ps-3 text-muted">&bull; Transport Allowance</td><td class="py-1 text-end text-muted">Rp {{ number_format($payroll->transport_allowance, 0, ',', '.') }}</td></tr> @endif
                        @if($payroll->meal_allowance > 0) <tr><td class="py-1 ps-3 text-muted">&bull; Meal Allowance</td><td class="py-1 text-end text-muted">Rp {{ number_format($payroll->meal_allowance, 0, ',', '.') }}</td></tr> @endif
                        @if($payroll->position_allowance > 0) <tr><td class="py-1 ps-3 text-muted">&bull; Position Allowance</td><td class="py-1 text-end text-muted">Rp {{ number_format($payroll->position_allowance, 0, ',', '.') }}</td></tr> @endif
                        @if($payroll->overtime_amount > 0) <tr><td class="py-1 ps-3 text-muted">&bull; Overtime Amount</td><td class="py-1 text-end text-muted">Rp {{ number_format($payroll->overtime_amount, 0, ',', '.') }}</td></tr> @endif
                        @php $bonus = $payroll->performance_bonus + $payroll->attendance_bonus + $payroll->other_bonus; @endphp
                        @if($bonus > 0) <tr><td class="py-1 ps-3 text-muted">&bull; Bonus & Incentives</td><td class="py-1 text-end text-muted">Rp {{ number_format($bonus, 0, ',', '.') }}</td></tr> @endif
                        
                        @if($reimbursement > 0)
                        <tr><td class="py-1 fw-bold text-dark pt-3" colspan="2">Total Reimbursement (C)</td></tr>
                        @if(isset($reimbursements) && $reimbursements->count() > 0)
                            @foreach($reimbursements as $claim)
                                <tr><td class="py-1 ps-3 text-muted">&bull; {{ $claim->title }}</td><td class="py-1 text-end text-muted">Rp {{ number_format($claim->amount, 0, ',', '.') }}</td></tr>
                            @endforeach
                        @else
                            <tr><td class="py-1 ps-3 text-muted">&bull; Reimbursement Amount</td><td class="py-1 text-end text-muted">Rp {{ number_format($reimbursement, 0, ',', '.') }}</td></tr>
                        @endif
                        @endif
                    </table>
                </div>
                <div class="p-2 px-3 border-top border-soft mt-auto" style="background-color: #fafafa;">
                    <div class="d-flex justify-content-between fw-bold small">
                        <span class="text-dark">Subtotal (+)</span><span class="text-teal">Rp {{ number_format($totalEarningsWithoutReimbursement + $reimbursement, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-6 d-flex flex-column">
                <div class="p-2 px-3 border-bottom border-soft" style="background-color: #fdf2f2;">
                    <h6 class="fw-bold mb-0 small text-danger text-uppercase" style="letter-spacing: 0.5px;">Deductions Summary</h6>
                </div>
                <div class="p-3 flex-grow-1">
                    <table class="table table-sm table-borderless mb-0 small text-dark-gray">
                        <tr><td class="py-1 fw-bold text-dark" colspan="2">Total Deductions (B)</td></tr>
                        @if($payroll->late_deduction > 0) <tr><td class="py-1 ps-3 text-muted">&bull; Lateness</td><td class="py-1 text-end text-muted">Rp {{ number_format($payroll->late_deduction, 0, ',', '.') }}</td></tr> @endif
                        @if($payroll->absent_deduction > 0) <tr><td class="py-1 ps-3 text-muted">&bull; Absence</td><td class="py-1 text-end text-muted">Rp {{ number_format($payroll->absent_deduction, 0, ',', '.') }}</td></tr> @endif
                        @if($payroll->bpjs_kes + $payroll->bpjs_tk > 0) <tr><td class="py-1 ps-3 text-muted">&bull; BPJS Contribution</td><td class="py-1 text-end text-muted">Rp {{ number_format($payroll->bpjs_kes + $payroll->bpjs_tk, 0, ',', '.') }}</td></tr> @endif
                        @if($payroll->pph21 > 0)
                            @php
                                $rate = floatval($payroll->employee?->pph21_rate ?? 0.5);
                                $baseTax = ($payroll->total_earnings - ($payroll->reimbursement ?? 0)) - ($payroll->total_deductions - $payroll->pph21);
                            @endphp
                            <tr>
                                <td class="py-1 ps-3 text-muted">
                                    &bull; PPh 21 <br>
                                    <small class="ms-2" style="font-size: 0.7rem;">(Rp {{ number_format($baseTax, 0, ',', '.') }} &times; {{ $rate }}%)</small>
                                </td>
                                <td class="py-1 text-end text-muted align-middle">Rp {{ number_format($payroll->pph21, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                        @if($payroll->penalty_amount > 0) <tr><td class="py-1 ps-3 text-muted">&bull; Penalty Amount</td><td class="py-1 text-end text-muted">Rp {{ number_format($payroll->penalty_amount, 0, ',', '.') }}</td></tr> @endif
                        @if($payroll->other_deduction > 0) <tr><td class="py-1 ps-3 text-muted">&bull; Other Deductions</td><td class="py-1 text-end text-muted">Rp {{ number_format($payroll->other_deduction, 0, ',', '.') }}</td></tr> @endif
                        @if($payroll->total_deductions == 0) <tr><td class="py-1 ps-3 fst-italic text-muted">&bull; No Deductions</td><td class="py-1 text-end text-muted">Rp 0</td></tr> @endif
                    </table>
                </div>
                <div class="p-2 px-3 border-top border-soft mt-auto" style="background-color: #fafafa;">
                    <div class="d-flex justify-content-between fw-bold small">
                        <span class="text-dark">Subtotal (-)</span><span class="text-danger">Rp {{ number_format($payroll->total_deductions, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

            {{-- GRAND TOTAL --}}
            <div class="rounded-3 px-4 py-4 mb-4 bg-white shadow-sm" style="border: 2px solid #212529 !important;">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <div class="rounded-circle bg-dark" style="width: 10px; height: 10px;"></div>
                            <span class="fw-bolder text-dark text-uppercase" style="font-size: 0.85rem; letter-spacing: 1px;">
                                TOTAL TAKE HOME PAY @if($reimbursement > 0) <span class="text-muted" style="letter-spacing: 0px; font-size: 0.75rem; text-transform: none; margin-left: 4px;">(A - B + C)</span> @else <span class="text-muted" style="letter-spacing: 0px; font-size: 0.75rem; text-transform: none; margin-left: 4px;">(A - B)</span> @endif
                            </span>
                        </div>
                        @if(class_exists('App\Helpers\Terbilang'))
                            <p class="mb-0 text-secondary fst-italic" style="font-size: 0.85rem;">
                                "{{ \App\Helpers\Terbilang::make($payroll->net_salary) }} Rupiah"
                            </p>
                        @endif
                    </div>
                    <div class="text-end">
                        <h2 class="fw-bold mb-0 text-dark">
                            Rp {{ number_format($payroll->net_salary, 0, ',', '.') }}
                        </h2>
                    </div>
                </div>
            </div>

            {{-- footer notes --}}
            <div class="mt-4 pt-4 text-center border-top border-soft">
                <p class="text-muted mb-0 fw-bold" style="font-size: 0.85rem;">
                    This document is system-generated and does not require a signature.
                </p>
                <p class="text-muted mb-0 mt-1" style="font-size: 0.75rem;">
                    Official proof of payment for PT. Aratech Nusantara Indonesia. Printed on {{ date('d M Y H:i') }} | Page {{ $totalPages }} of {{ $totalPages }}
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('auto_pdf') === 'true') {
                
                document.body.style.backgroundColor = '#ffffff';
                document.body.style.padding = '0';
                
                const element = document.getElementById('slip-padd');
                element.style.boxShadow = 'none';

                // Set format to fixed A4 size equivalent
                const opt = {
                    margin:       0,
                    filename:     'payslip_{{ str_replace(" ", "_", $payroll->employee?->fullname ?? "Employee") }}_{{ DateTime::createFromFormat("!m", $payroll->period_month)->format("F") }}_{{ $payroll->period_year }}.pdf',
                    image:        { type: 'jpeg', quality: 1 },
                    html2canvas:  { scale: 2, windowWidth: 800, x: 0, y: 0 },
                    jsPDF:        { unit: 'px', format: [800, 1130], orientation: 'portrait' } 
                };

                html2pdf().set(opt).from(element).save().then(() => {
                    window.parent.postMessage('pdf_selesai', '*');
                });
            } else if (urlParams.get('mode') === 'render') {
                // Background render mode: Generate PDF blob and send to parent, then wait for commands
                const element = document.getElementById('slip-padd');
                element.style.boxShadow = 'none';
                document.body.style.backgroundColor = '#ffffff';

                const opt = {
                    margin:       0,
                    filename:     'payslip_{{ str_replace(" ", "_", $payroll->employee?->fullname ?? "Employee") }}_{{ DateTime::createFromFormat("!m", $payroll->period_month)->format("F") }}_{{ $payroll->period_year }}.pdf',
                    image:        { type: 'jpeg', quality: 1 },
                    html2canvas:  { scale: 2, windowWidth: 800, x: 0, y: 0 },
                    jsPDF:        { unit: 'px', format: [800, 1130], orientation: 'portrait' } 
                };

                html2pdf().set(opt).from(element).outputPdf('bloburl').then(function(pdfUrl) {
                    window.parent.postMessage({ type: 'pdf_ready', url: pdfUrl }, '*');
                });

                window.addEventListener('message', function(event) {
                    if (event.data === 'trigger_download') {
                        html2pdf().set(opt).from(element).save();
                    } else if (event.data === 'trigger_print') {
                        window.print();
                    }
                });
            } else {
                document.getElementById('print-controls').style.display = 'block';
                setTimeout(() => {
                    window.print();
                }, 500);
            }
        }
    </script>
</body>
</html>