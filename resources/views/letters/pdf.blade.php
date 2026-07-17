<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>{{ $letter->subject ?? 'Letter Document' }}</title>
    <style>
        @page {
            /* top: 15mm, right: 20mm, bottom: 30mm (expanded for safety), left: 20mm */
            margin: 15mm 20mm 30mm 20mm;
        }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            line-height: 1.15;
            color: #000;
            margin: 0;
            font-size: 11px;
        }

        /* ======================================================== */
        /* letterhead */
        /* ======================================================== */
        .header-table {
            width: 100%;
            margin-bottom: 5px;
            border-collapse: collapse;
        }

        .header-logo {
            /* width: 12%; for square image */
            width: 25%;
            text-align: left;
            vertical-align: middle;
        }

        .header-logo img {
            max-height: 65px;
            /* max-width: 80px; for square images */
            max-width: 170px;
            object-fit: contain;
        }

        .header-content {
            width: 88%;
            text-align: right; /* <-- change to right */
            vertical-align: middle;
        }

        .company-name {
            margin: 0 0 4px 0;
            font-size: 16px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .company-info {
            margin: 0;
            font-size: 9px;
            color: #222;
            line-height: 1.3;
        }

        .header-line-thick {
            border-bottom: 3px solid #000;
            margin-bottom: 1px;
        }
        .header-line-thin {
            border-bottom: 1px solid #000;
            margin-bottom: 15px;
        }

        /* ======================================================== */
        /* letter metadata */
        /* ======================================================== */
        .meta-table {
            width: 100%;
            margin-bottom: 20px;
            font-size: 12px;
        }

        .meta-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .meta-table td.label {
            font-weight: bold;
            width: 15%;
        }

        .meta-table td.separator {
            width: 2%;
            text-align: center;
        }

        /* ======================================================== */
        /* letter content */
        /* ======================================================== */
        .content {
            text-align: justify;
            margin-bottom: 20px;
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            font-size: 12px !important;
            color: #000000 !important;
            font-family: 'Helvetica', 'Arial', sans-serif !important;
        }

        .content *,
        .content p,
        .content span,
        .content div,
        .content td,
        .content th {
            line-height: 1.5 !important;
            margin-top: 0;
            margin-bottom: 5px;
            background-color: transparent;
            font-family: 'Helvetica', 'Arial', sans-serif !important;
            font-size: 12px !important;
        }

        .content ul,
        .content ol {
            padding-left: 24px !important;
            margin-top: 5px !important;
            margin-bottom: 10px !important;
        }

        .content li {
            margin-bottom: 4px !important;
            text-align: left !important;
        }

        .content h1,
        .content h2,
        .content h3,
        .content h4,
        .content h5,
        .content h6 {
            color: #000000 !important;
            margin-top: 8px !important;
            margin-bottom: 4px !important;
            line-height: 1.2 !important;
            font-family: 'Helvetica', 'Arial', sans-serif !important;
        }

        .content img {
            max-width: 100% !important;
            height: auto !important;
        }

        /* ======================================================== */
        /* footer */
        /* ======================================================== */
        .footer {
            position: fixed;
            /* exactly 10mm from the bottom edge of the physical paper */
            bottom: -20mm;
            left: 0;
            right: 0;
            font-size: 8px;
            color: #777;
            border-top: 1px dashed #ccc;
            padding-top: 5px;
        }

        .footer table {
            width: 100%;
        }
        .footer td.left {
            text-align: left;
            vertical-align: bottom;
        }
        .footer td.right {
            text-align: right;
            vertical-align: bottom;
        }

        .content table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            word-wrap: break-word;
        }

        .content table,
        .content table th,
        .content table td {
            border: 1px solid #000000;
        }

        .content table th,
        .content table td {
            padding: 6px 8px;
            vertical-align: top;
            text-align: left; /* adjust if center alignment is needed */
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        .content table th {
            background-color: #f2f2f2; /* add a little gray to make the header look nice */
            font-weight: bold;
        }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="header-logo">
                <img src="{{ public_path('img/logo-aratech-document.png') }}" alt="Logo" />
                {{-- <img src="{{ public_path('img/aratech-logo-only.png') }}" alt="Logo" /> --}}
            </td>
            <td class="header-content">
                <h1 class="company-name">
                    {{
                        $config->company_name ??
                            'PT. ARATECH NUSANTARA INDONESIA'
                    }}
                </h1>
                <p class="company-info">
                    {!!
                        nl2br(
                            e($config->company_address ?? 'The Plaza Office Tower 41st Floor, Jl. M.H. Thamrin No.Kav. 28-30, DKI Jakarta'),
                        )
                    !!}
                </p>
                <p class="company-info" style="margin-top: 2px">
                    Website: {{ $config->company_website ?? '-' }} | Email: {{ $config->company_email ?? '-' }} | Telp: {{ $config->company_phone ?? '-' }}
                </p>
            </td>
        </tr>
    </table>
    <div class="header-line-thick"></div>
    <div class="header-line-thin"></div>

    <table class="meta-table">
        <tr>
            <td class="label">Number</td>
            <td class="separator">:</td>
            <td style="width: 48%">{{ $letter->letter_number ?? '-' }}</td>
            <td style="width: 35%; text-align: right">
                Jakarta, {{
                    $letter->approved_date
                        ? $letter->approved_date->format('d F Y')
                        : ($letter->created_date
                            ? \Carbon\Carbon::parse($letter->created_date)->format('d F Y')
                            : now()->format('d F Y'))
                }}
            </td>
        </tr>
        <tr>
            <td class="label">Attachment</td>
            <td class="separator">:</td>
            <td colspan="2">{TOTAL_PAGES_PLACEHOLDER}</td>
        </tr>
        <tr>
            <td class="label">Subject</td>
            <td class="separator">:</td>
            <td colspan="2"><strong style="text-decoration: underline">{{ $letter->subject }}</strong></td>
        </tr>
    </table>

    <div class="content">{!! str_replace('<!-- pagebreak -->', '<div style="page-break-before: always;"></div>', $letter->formatted_content) !!}</div>

    <div style="margin-top: 30px; padding-bottom: 10mm; page-break-inside: avoid">
        @php
            $allSignatures = $letter->signatures()->whereNotNull('signature_image')->get();
            $sigCount = $allSignatures->count();
        @endphp

        @if ($sigCount == 1)
            <table style="width: 100%; margin-top: 20px">
                <tr>
                    <td style="width: 60%"></td>
                    <td style="width: 40%; text-align: center; vertical-align: bottom">
                        @php $sig = $allSignatures->first(); @endphp
                        <span style="font-size: 11px; color: #000">
                            @if ($sig->user_id)
                                {{
                                    $sig->external_title ?? ($sig->signer->employee->position->position_name ??
                                        'Employee')
                                }}<br
                                 />
                                {{
                                    $sig->external_company ?? ($config->company_name ??
                                        'PT Aratech Nusantara Indonesia')
                                }}
                            @else
                                {{ $sig->external_title ?? 'Company Partner' }}<br
                                 />
                                {{ $sig->external_company ?? '-' }}
                            @endif</span
                        ><br />

                        <img
                            src="{{ $sig->signature_image }}"
                            style="height: 60px; max-width: 140px; margin: 10px 0; object-fit: contain"
                        /><br />

                        <strong style="font-size: 12px; text-decoration: underline">
                            {{
                                $sig->user_id
                                    ? $sig->signer->name
                                    : $sig->external_name ?? 'External Partner'
                            }} </strong
                        ><br />
                        <span style="font-size: 8px; color: #999">Signed via OpenSSL</span>
                    </td>
                </tr>
            </table>
        @elseif ($sigCount > 1)
            @foreach ($allSignatures->chunk(3) as $row)
                <table style="width: 100%; margin-top: 20px; table-layout: fixed">
                    <tr>
                        @foreach ($row as $sig)
                            <td style="text-align: center; vertical-align: bottom; padding: 0 10px">
                                <span style="font-size: 11px; color: #000">
                                    @if ($sig->user_id)
                                        {{
                                            $sig->external_title ?? ($sig->signer->employee->position->position_name ??
                                                'Employee')
                                        }}<br
                                         />
                                        {{
                                            $sig->external_company ?? ($config->company_name ??
                                                'PT Aratech Nusantara Indonesia')
                                        }}
                                    @else
                                        {{ $sig->external_title ?? 'Company Partner' }}<br
                                         />
                                        {{ $sig->external_company ?? '-' }}
                                    @endif</span
                                ><br />

                                <img
                                    src="{{ $sig->signature_image }}"
                                    style="height: 60px; max-width: 140px; margin: 10px 0; object-fit: contain"
                                /><br />

                                <strong style="font-size: 12px; text-decoration: underline">
                                    {{
                                        $sig->user_id
                                            ? $sig->signer->name
                                            : $sig->external_name ?? 'External Partner'
                                    }} </strong
                                ><br />
                                <span style="font-size: 8px; color: #999">Signed via OpenSSL</span>
                            </td>
                        @endforeach
                    </tr>
                </table>
            @endforeach
        @else
            <div style="margin-top: 50px; text-align: center">
                <div
                    style="
                        border: 1px dashed #999;
                        padding: 15px;
                        display: inline-block;
                        border-radius: 4px;
                        background-color: #f9f9f9;
                    "
                >
                    <strong style="font-size: 12px; color: #555">[ Document Not Yet Signed ]</strong><br />
                    <span style="font-size: 10px; color: #777"
                        >This document does not yet have a system-validated digital signature.</span
                    >
                </div>
            </div>
        @endif
    </div>

    <div class="footer">
        <table>
            <tr>
                <td class="left">
                    @if (!empty($config->letterhead_footer))
                        <em>{!! nl2br(e($config->letterhead_footer)) !!}</em
                        ><br />
                    @endif
                    <strong style="color: #444">COREVO Digital Document</strong> - Automatically printed by the
                    system.<br />
                    Print Time: {{ now()->format('d/m/Y H:i') }}
                </td>
                <td class="right">
                    @php
                        $firstSig = isset($allSignatures) ? $allSignatures->first() : null;
                        $qrUrl = $firstSig
                            ? route('signatures.public-verify', ['id' => $firstSig->id, 'token' => $firstSig->verification_token])
                            : null;
                    @endphp

                    @if ($qrUrl)
                        <div style="display: inline-block; text-align: center">
                            <img
                                alt="Verification QR"
                                src="data:image/svg+xml;base64,{!! base64_encode(QrCode::format('svg')->size(70)->margin(0)->errorCorrection('M')->generate($qrUrl)) !!}"
                                width="70"
                                height="70"
                                style="border: 1px solid #eee; padding: 2px"
                            />
                            <p style="font-size: 7px; color: #aaa; margin: 3px 0 0 0">Scan to Verify</p>
                        </div>
                    @endif
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
