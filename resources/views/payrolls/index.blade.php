@extends ('layouts.dashboard')

@section ('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Payrolls</h3>
                    <p class="text-subtitle text-muted">Manage employee payroll records.</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item active">Payrolls</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <section class="section">
        {{-- filter bar --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12 mb-2">
                        <div class="row g-2 justify-content-start justify-content-lg-start">
                            <div class="col-12 col-sm-auto">
                                <button
                                    type="button"
                                    class="btn btn-success btn-sm w-100 shadow-sm"
                                    id="btnExportCsv"
                                    disabled
                                >
                                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>
                                    Export Payment XLSX (<span id="countSelected">0</span>)
                                </button>
                            </div>

                            <div class="col-12 col-sm-auto">
                                <button
                                    type="button"
                                    class="btn btn-info btn-sm w-100 shadow-sm text-white"
                                    id="btnExportDataCsv"
                                    disabled
                                >
                                    <i class="bi bi-file-earmark-check me-1"></i>
                                    Export Paid Data XLSX (<span id="countDataSelected">0</span>)
                                </button>
                            </div>

                            @if (\App\Constants\Roles::hasFullFinanceAccess(session('role')))
                                <div class="col-12 col-sm-auto">
                                    <a
                                        href="{{ route('payrolls.create') }}"
                                        class="btn btn-primary btn-sm w-100 shadow-sm"
                                    >
                                        <i class="bi bi-plus-circle me-1"></i> Create New Payroll
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-12 my-1">
                        <hr class="text-muted opacity-25 m-0" />
                    </div>

                    {{-- filter month --}}
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2">
                        <label class="form-label text-muted fw-bold small mb-2" for="filter-month">
                            <i class="bi bi-calendar-event me-1"></i> Month
                        </label>
                        <select id="filter-month" class="form-select form-select-sm">
                            <option value="">-- Select Month --</option>
                            @foreach ($months as $i => $m)
                                <option value="{{ $i+1 }}">{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- filter year --}}
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2">
                        <label class="form-label text-muted fw-bold small mb-2" for="filter-year">
                            <i class="bi bi-calendar me-1"></i> Year
                        </label>
                        <select id="filter-year" class="form-select form-select-sm">
                            @for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                                <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}> {{ $y }}</option>
                            @endfor
                        </select>
                    </div>

                    {{-- filter status --}}
                    <div class="col-12 col-sm-6 col-md-4 col-lg-3 col-xl-2">
                        <label class="form-label text-muted fw-bold small mb-2" for="filter-status">
                            <i class="bi bi-check-circle me-1"></i> Status
                        </label>
                        <select id="filter-status" class="form-select form-select-sm">
                            <option value="">All Statuses</option>
                            <option value="draft">Draft</option>
                            <option value="approved">Approved</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>

                    {{-- fund source --}}
                    <div class="col-12 col-sm-6 col-md-6 col-lg-3 col-xl-3">
                        @php
                            $currentAccount = $assetAccounts->where('id', $defaultAccountId)->first();
                            $accountLabel = $currentAccount ? $currentAccount->code . ' - ' . $currentAccount->name : 'Not Set';
                            $labelClass = $currentAccount ? 'text-success' : 'text-danger';
                        @endphp

                        <label class="form-label text-muted fw-bold small mb-2 d-block">
                            <i class="bi bi-wallet2 me-1"></i> Account Source
                        </label>

                        <button
                            type="button"
                            class="btn btn-outline-primary btn-sm w-100 text-start d-flex align-items-center justify-content-between"
                            data-bs-toggle="modal"
                            data-bs-target="#modalSettingAkun"
                            style="height: 31px"
                        >
                            <span>
                                <i class="bi bi-gear-fill me-1"></i>
                                <span
                                    id="labelAkunTerpilih"
                                    class="fw-bold {{ $labelClass }}"
                                    data-current-id="{{ $defaultAccountId }}"
                                >
                                    {{ $accountLabel }}
                                </span>
                            </span>
                            <i class="bi bi-chevron-down small text-muted"></i>
                        </button>
                    </div>
                </div>

                {{-- hidden export form --}}
                <form id="formExportCsv" action="{{ route('payrolls.export-csv') }}" method="POST" class="d-none">
                    @csrf
                    <div id="hiddenCsvInputs"></div>
                </form>

                <form
                    id="formExportDataCsv"
                    action="{{ route('payrolls.export-data-csv') }}"
                    method="POST"
                    class="d-none"
                >
                    @csrf
                    <div id="hiddenDataCsvInputs"></div>
                </form>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @php
            $now = \Carbon\Carbon::now('Asia/Jakarta');
            $curMonthName = $months[$now->month - 1];
            $curMonthVal = $now->month;
            $curYear = $now->year;

            $prevDate = $now->copy()->subMonth();
            $prevMonthName = $months[$prevDate->month - 1];
            $prevMonthVal = $prevDate->month;
            $prevYear = $prevDate->year;
        @endphp

        {{-- initial message --}}
        <div id="initial-message" class="card shadow-sm border-0 mb-4" style="border-radius: 15px">
            <div class="card-body text-center" style="padding: 5rem 2rem">
                <div
                    class="d-inline-block bg-primary bg-opacity-10 rounded-circle mb-4"
                    style="padding: 1.5rem; border: 1px dashed var(--bs-primary)"
                >
                    <i class="bi bi-receipt-cutoff text-primary" style="font-size: 4rem; line-height: 1"></i>
                </div>
                <h4 class="fw-bold mb-2">Waiting for Period Selection</h4>
                <p
                    class="text-muted fs-6 mb-4"
                    style="max-width: 400px; margin: 0 auto"
                >Select a month in the filter above or use the quick shortcuts below to view data.</p>

                <div class="d-flex flex-column flex-md-row justify-content-center gap-2 gap-md-3 mt-3">
                    <button
                        type="button"
                        class="btn btn-primary px-4 py-2 rounded-3 btn-shortcut w-100 w-md-auto"
                        data-month="{{ $curMonthVal }}"
                        data-year="{{ $curYear }}"
                    >
                        <i class="bi bi-calendar-check-fill me-1"></i> This Month: {{ $curMonthName }}
                    </button>
                    <button
                        type="button"
                        class="btn btn-outline-primary px-4 py-2 rounded-3 btn-shortcut w-100 w-md-auto"
                        data-month="{{ $prevMonthVal }}"
                        data-year="{{ $prevYear }}"
                    >
                        <i class="bi bi-calendar-minus me-1"></i> Last Month: {{ $prevMonthName }}
                    </button>
                </div>
            </div>
        </div>

        {{-- table container --}}
        <div class="card shadow-sm border-0" id="table-card" style="display: none; border-radius: 15px">
            <div class="card-body p-0">
                <div class="px-4 pt-4 pb-3 border-bottom d-flex align-items-center mb-3">
                    <h5 class="mb-0 fw-bold text-body" id="table-period-title">
                        <i class="bi bi-calendar-check text-primary me-2"></i> Payroll Data Period
                    </h5>
                </div>
                <div class="table-responsive p-4 pt-2">
                    <table class="table table-striped table-hover align-middle w-100" id="payroll-table">
                        <thead>
                            <tr>
                                <th class="text-center" width="50">
                                    <input type="checkbox" id="checkAll" class="form-check-input" />
                                </th>
                                <th class="text-uppercase" style="font-size: 0.8rem">Employee</th>
                                <th class="text-uppercase text-end" style="font-size: 0.8rem">Earnings</th>
                                <th class="text-uppercase text-end" style="font-size: 0.8rem">Deductions</th>
                                <th class="text-uppercase text-end" style="font-size: 0.8rem">Net Salary</th>
                                <th class="text-uppercase text-center" style="font-size: 0.8rem">Status</th>
                                <th class="text-uppercase text-center" style="font-size: 0.8rem">Manage Status</th>
                                <th class="text-uppercase text-center" style="font-size: 0.8rem; width: 120px">
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
    {{-- modal account setting --}}
    <div
        class="modal fade"
        id="modalSettingAkun"
        tabindex="-1"
        aria-labelledby="modalSettingAkunLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white" id="modalSettingAkunLabel">Set Account Source</h5>
                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">The cash/bank account selected here will be used automatically to disburse payroll funds.</p>
                    <div class="mb-3">
                        <label for="master_account_id" class="form-label fw-bold">
                            Select Account (Expense) <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="master_account_id" name="master_account_id">
                            <option value="" disabled {{ !$defaultAccountId ? 'selected' : '' }}>
                                -- Select Cash/Bank Account --
                            </option>
                            @foreach ($assetAccounts as $akun)
                                <option value="{{ $akun->id }}" {{ $defaultAccountId == $akun->id ? 'selected' : '' }}>
                                    {{ $akun->code }} - {{ $akun->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="btnSimpanSetting">Save</button>
                </div>
            </div>
        </div>
    </div>
    @push ('scripts')
        <script>
            $(function () {
                let table = null;

                let selectedApprovedIds = [];
                let selectedPaidIds = [];

                const navEntries = performance.getEntriesByType('navigation');
                if (navEntries.length > 0 && navEntries[0].type === 'reload') {
                    sessionStorage.removeItem('payroll_pref_month');
                    sessionStorage.removeItem('payroll_pref_year');
                    sessionStorage.removeItem('payroll_pref_status');
                }

                function getSelectedMonthName() {
                    return $('#filter-month option:selected').text();
                }

                function checkAndLoadData() {
                    let month = $('#filter-month').val();
                    let year = $('#filter-year').val();
                    let status = $('#filter-status').val();

                    if (month) {
                        sessionStorage.setItem('payroll_pref_month', month);
                        sessionStorage.setItem('payroll_pref_year', year);
                        sessionStorage.setItem('payroll_pref_status', status);

                        selectedApprovedIds = [];
                        selectedPaidIds = [];
                        updateExportButton();
                        $('#checkAll').prop('checked', false);

                        $('#initial-message').hide();
                        $('#table-card').fadeIn();
                        
                        let monthName = getSelectedMonthName();
                        $('#table-period-title').html(`<i class="bi bi-calendar-check text-primary me-2"></i> Payroll Data Period: ${monthName} ${year}`);

                        if (!table) {
                            table = $('#payroll-table').DataTable({
                                processing: true,
                                serverSide: true,
                                dom:
                                    "<'row align-items-center mb-3'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6 text-md-end'f>>" +
                                    "<'row'<'col-sm-12'tr>>" +
                                    "<'row align-items-center mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7 d-flex justify-content-md-end'p>>",
                                ajax: {
                                    url: '{{ route('payrolls.index') }}',
                                    data: function (d) {
                                        d.filter_month = $('#filter-month').val();
                                        d.filter_year = $('#filter-year').val();
                                        d.filter_status = $('#filter-status').val();
                                    },
                                },
                                order: [],
                                columns: [
                                    {
                                        data: 'id',
                                        name: 'id',
                                        orderable: false,
                                        searchable: false,
                                        className: 'text-center align-middle',
                                        render: function (data, type, row) {
                                            if (row.status === 'approved' || row.status === 'paid') {
                                                let isApprovedSelected = selectedApprovedIds.includes(data.toString());
                                                let isPaidSelected = selectedPaidIds.includes(data.toString());
                                                let checked = isApprovedSelected || isPaidSelected ? 'checked' : '';

                                                return `<input type="checkbox" class="check-item form-check-input" value="${data}" data-status="${row.status}" ${checked}>`;
                                            }
                                            return `<input type="checkbox" class="form-check-input" disabled title="Cannot be exported.">`;
                                        },
                                    },
                                    { data: 'employee_name', name: 'employee.fullname', orderable: false },
                                    { data: 'total_earnings', name: 'total_earnings', className: 'text-end' },
                                    { data: 'total_deductions', name: 'total_deductions', className: 'text-end' },
                                    { data: 'net_salary', name: 'net_salary', className: 'text-end fw-bold' },
                                    {
                                        data: 'status_badge',
                                        name: 'status',
                                        className: 'text-center',
                                        orderable: true,
                                        searchable: false,
                                    },
                                    { data: 'status_actions', name: 'status_actions', orderable: false, searchable: false },
                                    {
                                        data: 'action',
                                        name: 'action',
                                        orderable: false,
                                        searchable: false,
                                        className: 'text-center',
                                    },
                                ],
                                language: {
                                    processing: '<div class="spinner-border text-primary spinner-border-sm" role="status"></div>',
                                    emptyTable: 'No payroll data found for ' + getSelectedMonthName() + '.',
                                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                                    infoEmpty: 'No data available',
                                    search: '<i class="bi bi-search"></i>',
                                    searchPlaceholder: 'Search employee...',
                                    paginate: {
                                        previous: '<i class="bi bi-chevron-left"></i>',
                                        next: '<i class="bi bi-chevron-right"></i>',
                                    },
                                },
                            });

                            setTimeout(function () {
                                table.columns.adjust();
                            }, 10);
                        } else {
                            table.context[0].oLanguage.sEmptyTable = 'No payroll data found for ' + getSelectedMonthName() + '.';
                            table.draw();
                        }
                    } else {
                        $('#table-card').hide();
                        $('#initial-message').fadeIn();
                    }
                }

                function updateExportButton() {
                    let countApproved = selectedApprovedIds.length;
                    let countPaid = selectedPaidIds.length;

                    $('#countSelected').text(countApproved);
                    $('#btnExportCsv').prop('disabled', countApproved === 0);

                    $('#countDataSelected').text(countPaid);
                    $('#btnExportDataCsv').prop('disabled', countPaid === 0);
                }

                $('#checkAll').on('change', function () {
                    let isChecked = $(this).prop('checked');
                    $('.check-item:not(:disabled)').prop('checked', isChecked);

                    $('.check-item:not(:disabled)').each(function () {
                        let val = $(this).val().toString();
                        let status = $(this).data('status');

                        if (isChecked) {
                            if (status === 'approved' && !selectedApprovedIds.includes(val)) selectedApprovedIds.push(val);
                            if (status === 'paid' && !selectedPaidIds.includes(val)) selectedPaidIds.push(val);
                        } else {
                            if (status === 'approved') selectedApprovedIds = selectedApprovedIds.filter((id) => id !== val);
                            if (status === 'paid') selectedPaidIds = selectedPaidIds.filter((id) => id !== val);
                        }
                    });
                    updateExportButton();
                });

                $(document).on('change', '.check-item', function () {
                    let val = $(this).val().toString();
                    let status = $(this).data('status');

                    if ($(this).prop('checked')) {
                        if (status === 'approved' && !selectedApprovedIds.includes(val)) selectedApprovedIds.push(val);
                        if (status === 'paid' && !selectedPaidIds.includes(val)) selectedPaidIds.push(val);
                    } else {
                        if (status === 'approved') selectedApprovedIds = selectedApprovedIds.filter((id) => id !== val);
                        if (status === 'paid') selectedPaidIds = selectedPaidIds.filter((id) => id !== val);
                        $('#checkAll').prop('checked', false);
                    }
                    updateExportButton();
                });

                // export payment data to csv
                $('#btnExportCsv').click(function () {
                    if (selectedApprovedIds.length === 0) return;

                    let inputs = '';
                    selectedApprovedIds.forEach((id) => {
                        inputs += `<input type="hidden" name="ids[]" value="${id}">`;
                    });

                    $('#hiddenCsvInputs').html(inputs);
                    $('#formExportCsv').submit();
                });

                // export paid data to csv
                $('#btnExportDataCsv').click(function () {
                    if (selectedPaidIds.length === 0) return;

                    let inputs = '';
                    selectedPaidIds.forEach((id) => {
                        inputs += `<input type="hidden" name="ids[]" value="${id}">`;
                    });

                    $('#hiddenDataCsvInputs').html(inputs);
                    $('#formExportDataCsv').submit();
                });

                // save account setting logic
                $('#btnSimpanSetting').click(function () {
                    let accountId = $('#master_account_id').val();

                    if (!accountId) {
                        Swal.fire('Oops!', 'Please select an account first!', 'warning');
                        return;
                    }

                    let btn = $(this);
                    let originalText = btn.text();
                    btn.html('<span class="spinner-border spinner-border-sm"></span> Saving...').prop('disabled', true);

                    $.ajax({
                        url: `{{ route('payrolls.update-setting') }}`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            account_id: accountId,
                        },
                        success: function (res) {
                            btn.html(originalText).prop('disabled', false);
                            if (res.success) {
                                $('#labelAkunTerpilih').removeClass('text-danger').addClass('text-success').text(res.account_name);
                                $('#labelAkunTerpilih').attr('data-current-id', accountId);
                                $('#modalSettingAkun').modal('hide');
                                Swal.fire('Saved!', 'Central fund source updated successfully.', 'success');
                            }
                        },
                        error: function () {
                            btn.html(originalText).prop('disabled', false);
                            Swal.fire('Failed!', 'System error occurred.', 'error');
                        },
                    });
                });

                // update status logic
                $(document).on('click', '.btn-update-status', function () {
                    let id = $(this).data('id');
                    let status = $(this).data('status');

                    if (status === 'paid') {
                        let currentId = $('#labelAkunTerpilih').attr('data-current-id');
                        let currentName = $('#labelAkunTerpilih').text();

                        if (!currentId) {
                            Swal.fire(
                                'Stop!',
                                'Central fund source not set. Please click the "Account Source" button above the table first.',
                                'error',
                            );
                            return;
                        }

                        Swal.fire({
                            title: 'Payment Confirmation',
                            html: `Salary will be disbursed and recorded to account:<br><strong class="text-success">${currentName}</strong>`,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, Pay!',
                            cancelButtonText: 'Cancel',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                prosesUpdateStatus(id, 'paid', currentId);
                            }
                        });
                    } else if (status === 'approved') {
                        let currentMonth = new Date().getMonth() + 1;
                        let currentYear = new Date().getFullYear();
                        let selectedMonth = parseInt($('#filter-month').val());
                        let selectedYear = parseInt($('#filter-year').val());

                        if (selectedYear > currentYear || (selectedYear === currentYear && selectedMonth >= currentMonth)) {
                            Swal.fire({
                                title: 'Warning: Premature Approval!',
                                html: 'You are approving payroll for a month that <b>has not yet ended</b>.<br><br>Attendance data, deductions, and overtime may not be final, which could result in invalid payroll records.<br><br>Do you still want to proceed?',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Yes, Approve Anyway',
                                confirmButtonColor: '#dc3545',
                                cancelButtonText: 'Cancel'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    prosesUpdateStatus(id, status, null);
                                }
                            });
                        } else {
                            Swal.fire({
                                title: 'Approve Payroll?',
                                text: 'Payroll status will be changed to approved.',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Yes, Approve!',
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    prosesUpdateStatus(id, status, null);
                                }
                            });
                        }
                    } else {
                        Swal.fire({
                            title: 'Change Status?',
                            text: 'Payroll status will be changed to ' + status + '.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, Change!',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                prosesUpdateStatus(id, status, null);
                            }
                        });
                    }
                });

                function prosesUpdateStatus(id, status, account_id) {
                    Swal.fire({
                        title: 'Processing...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                    });

                    $.ajax({
                        url: `{{ url('payrolls') }}/${id}/update-status`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            status: status,
                            account_id: account_id,
                        },
                        success: function (res) {
                            if (res.success) {
                                $('#payroll-table').DataTable().ajax.reload(null, false);
                                Swal.fire('Success!', res.message, 'success').then(() => {
                                    if (status === 'paid') {
                                        Swal.fire({
                                            title: 'Preparing PDF...',
                                            text: 'Please wait, rendering layout...',
                                            allowOutsideClick: false,
                                            didOpen: () => {
                                                Swal.showLoading();
                                            },
                                        });

                                        let iframe = document.createElement('iframe');
                                        iframe.style.position = 'fixed';
                                        iframe.style.top = '0';
                                        iframe.style.left = '0';
                                        iframe.style.width = '850px';
                                        iframe.style.height = '100vh';
                                        iframe.style.zIndex = '-9999';
                                        iframe.style.opacity = '0.01';
                                        document.body.appendChild(iframe);

                                        window.addEventListener(
                                            'message',
                                            function (event) {
                                                if (event.data === 'pdf_selesai') {
                                                    document.body.removeChild(iframe);
                                                    Swal.close();
                                                }
                                            },
                                            { once: true },
                                        );

                                        iframe.src = `{{ url('payrolls') }}/${id}/slip?auto_pdf=true`;
                                    }
                                });
                            }
                        },
                        error: function (err) {
                            Swal.fire('Failed!', err.responseJSON?.message || 'Failed to update payroll status.', 'error');
                        },
                    });
                }

                // delete logic
                $(document).on('click', '.btn-delete-payroll', function () {
                    const id = $(this).data('id');
                    const status = $(this).data('status');

                    let config = {
                        title: 'Are you sure?',
                        text: 'This payroll data will be permanently deleted!',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, Delete!',
                    };

                    if (status === 'paid') {
                        config.title = 'Critical Warning!';
                        config.text =
                            'This payroll is already marked as Paid. Deleting this data will automatically remove the related cash book transaction, which may affect your cashflow report and account balance.';
                        config.icon = 'error';
                        config.confirmButtonText = 'I Understand, Delete Anyway';
                    }

                    Swal.fire(config).then((result) => {
                        if (result.isConfirmed) {
                            $(`#form-delete-${id}`).submit();
                        }
                    });
                });

                // page load inits
                const savedMonth = sessionStorage.getItem('payroll_pref_month');
                const savedYear = sessionStorage.getItem('payroll_pref_year');
                const savedStatus = sessionStorage.getItem('payroll_pref_status');

                if (savedMonth) {
                    $('#filter-month').val(savedMonth);
                    $('#filter-year').val(savedYear);
                    $('#filter-status').val(savedStatus);
                    checkAndLoadData();
                }

                $('#filter-month, #filter-year, #filter-status').on('change', function () {
                    checkAndLoadData();
                });

                $(document).on('click', '.btn-shortcut', function () {
                    $('#filter-month').val($(this).data('month'));
                    $('#filter-year').val($(this).data('year'));
                    checkAndLoadData();
                });
            });
        </script>
    @endpush
@endsection
