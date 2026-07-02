@extends ('layouts.dashboard')

@section ('content')
    <div class="page-heading mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex align-items-center order-2 order-md-1">
                <a href="{{ route('crm.wa-blasts.index') }}" class="btn btn-secondary me-3" title="Back">
                    <i class="bi bi-arrow-left fs-5"></i>
                </a>
                <div>
                    <h3 class="mb-0">WA Campaign Details</h3>
                    <p class="text-subtitle text-muted mb-0 mt-1">Manual dispatch dashboard for your WhatsApp campaign.</p>
                </div>
            </div>
            
            <nav aria-label="breadcrumb" class="breadcrumb-header order-1 order-md-2">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('crm.wa-blasts.index') }}">WA Blasts</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Campaign Details</li>
                </ol>
            </nav>
        </div>
    </div>
    
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Campaign Summary Card -->
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="mb-0">Campaign Info</h5>
                </div>
                <div class="card-body mt-3">
                    <div class="mb-4">
                        <p class="text-muted small mb-1">Campaign Name</p>
                        <h6 class="fw-bold">{{ $blast->campaign_name }}</h6>
                    </div>
                    <div class="mb-4">
                        <p class="text-muted small mb-1">Status</p>
                        <div>
                            @if ($blast->status == 'draft')
                                <span class="badge bg-secondary">Draft</span>
                            @elseif ($blast->status == 'active')
                                <span class="badge bg-primary" id="master-status-badge">Active</span>
                            @else
                                <span class="badge bg-success" id="master-status-badge">Completed</span>
                            @endif
                        </div>
                    </div>
                    <div class="mb-4">
                        <p class="text-muted small mb-1">Progress</p>
                        <div class="d-flex align-items-center mb-2">
                            <span class="fs-4 fw-bold me-2" id="sent-count-text">{{ $blast->sent_count }}</span>
                            <span class="text-muted">/ {{ $blast->target_count }} Sent</span>
                        </div>
                        <div class="progress progress-sm" style="height: 10px;">
                            @php
                                $percent = $blast->target_count > 0 ? ($blast->sent_count / $blast->target_count) * 100 : 0;
                            @endphp
                            <div class="progress-bar bg-success" id="master-progress-bar" role="progressbar" style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <p class="text-muted small mb-1">Message Content</p>
                        <div class="p-3 bg-light rounded text-dark" style="max-height: 200px; overflow-y: auto; font-family: monospace; font-size: 13px; white-space: pre-wrap;">{{ $blast->body_template }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recipients List Card -->
        <div class="col-12 col-lg-8 mt-4 mt-lg-0">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recipients List</h5>
                    
                    @if($blast->status !== 'completed')
                    <button class="btn btn-success fw-bold px-3 shadow-sm" id="btn-send-next">
                        <i class="bi bi-whatsapp me-2"></i> Send Next Pending
                    </button>
                    @endif
                </div>
                <div class="card-body mt-3">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="recipients-table">
                            <thead>
                                <tr>
                                    <th>Recipient / Company</th>
                                    <th>WhatsApp Number</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($blast->recipients as $recipient)
                                    @php
                                        // Standardize phone number for WA (remove leading 0 or +, ensure 62 prefix for ID)
                                        $phone = preg_replace('/[^0-9]/', '', $recipient->phone_number);
                                        if (str_starts_with($phone, '08')) {
                                            $phone = '628' . substr($phone, 2);
                                        }
                                        
                                        // Note: in a real CRM you might replace {name} placeholders here
                                        $messageText = urlencode($blast->body_template);
                                        $waLink = "https://wa.me/{$phone}?text={$messageText}";
                                    @endphp
                                    <tr id="row-{{ $recipient->id }}" data-id="{{ $recipient->id }}" data-status="{{ $recipient->status }}">
                                        <td>
                                            <div class="fw-bold">{{ $recipient->contact->company_name ?? 'Unknown Contact' }}</div>
                                            <div class="small text-muted">{{ $recipient->contact->name ?? '' }}</div>
                                        </td>
                                        <td>
                                            <div class="font-monospace">{{ $recipient->phone_number }}</div>
                                        </td>
                                        <td class="status-cell">
                                            @if($recipient->status == 'sent')
                                                <span class="badge bg-success-subtle text-success border border-success"><i class="bi bi-check2-all me-1"></i> Sent</span>
                                            @elseif($recipient->status == 'failed')
                                                <span class="badge bg-danger-subtle text-danger border border-danger"><i class="bi bi-x-circle me-1"></i> Failed</span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning border border-warning"><i class="bi bi-clock me-1"></i> Pending</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-1">
                                                @if($recipient->status !== 'failed')
                                                    <button 
                                                        class="btn btn-sm btn-outline-danger btn-fail-wa"
                                                        data-id="{{ $recipient->id }}"
                                                        title="Mark as Failed (No WA)"
                                                    >
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                @endif
                                                <button 
                                                    class="btn btn-sm btn-{{ $recipient->status == 'sent' ? 'outline-secondary' : 'success' }} btn-send-wa"
                                                    data-id="{{ $recipient->id }}"
                                                    data-link="{{ $waLink }}"
                                                >
                                                    <i class="bi bi-send me-1"></i> {{ $recipient->status == 'sent' ? 'Resend' : 'Send WA' }}
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const table = $('#recipients-table').DataTable({
            responsive: true,
            pageLength: 25,
            order: [[2, 'asc'], [0, 'asc']], // Order by status (pending first), then name
        });

        // Function to mark a single recipient as sent or failed
        function markAsProcessed(recipientId, rowElement, status) {
            $.ajax({
                url: `{{ route('crm.wa-blasts.mark-sent', $blast->id) }}`,
                type: 'POST',
                data: {
                    recipient_id: recipientId,
                    status: status,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        // Update UI row
                        rowElement.attr('data-status', status);
                        
                        const statusCell = rowElement.find('.status-cell');
                        const btn = rowElement.find('.btn-send-wa');
                        
                        if (status === 'sent') {
                            statusCell.html('<span class="badge bg-success-subtle text-success border border-success"><i class="bi bi-check2-all me-1"></i> Sent</span>');
                            btn.removeClass('btn-success').addClass('btn-outline-secondary').html('<i class="bi bi-send me-1"></i> Resend');
                        } else {
                            statusCell.html('<span class="badge bg-danger-subtle text-danger border border-danger"><i class="bi bi-x-circle me-1"></i> Failed</span>');
                        }
                        
                        // Hide fail button only if it is marked as failed
                        if (status === 'failed') {
                            rowElement.find('.btn-fail-wa').fadeOut();
                        }

                        // Update master progress
                        let currentSent = parseInt($('#sent-count-text').text());
                        let targetCount = {{ $blast->target_count }};
                        
                        // Prevent overcounting in UI if clicked multiple times rapidly
                        // We rely on the button text changing to 'Resend' or hiding to know it was processed
                        let isFirstTimeProcessed = btn.attr('data-processed') !== 'true';
                        
                        if (currentSent < targetCount && isFirstTimeProcessed) {
                            btn.attr('data-processed', 'true');
                            let newSent = currentSent + 1;
                            
                            $('#sent-count-text').text(newSent);
                            let newPercent = (newSent / targetCount) * 100;
                            $('#master-progress-bar').css('width', newPercent + '%').attr('aria-valuenow', newPercent);
                            
                            if (newSent >= targetCount) {
                                $('#master-status-badge').removeClass('bg-primary').addClass('bg-success').text('Completed');
                                $('#btn-send-next').fadeOut();
                            }
                        }
                    }
                }
            });
        }

        // Handle manual individual send click
        $('#recipients-table').on('click', '.btn-send-wa', function() {
            const btn = $(this);
            const waLink = btn.attr('data-link');
            const recipientId = btn.attr('data-id');
            const row = $('#row-' + recipientId);
            
            // Open WA in new tab
            window.open(waLink, '_blank');
            
            // If it was pending, mark as sent via AJAX
            if (row.attr('data-status') === 'pending') {
                // Slight delay to allow tab opening first
                setTimeout(function() {
                    markAsProcessed(recipientId, row, 'sent');
                }, 500);
            }
        });
        
        // Handle manual fail click
        $('#recipients-table').on('click', '.btn-fail-wa', function() {
            const btn = $(this);
            const recipientId = btn.attr('data-id');
            const row = $('#row-' + recipientId);
            
            if (confirm('Mark this recipient as Failed? (e.g., number not registered on WA)')) {
                markAsProcessed(recipientId, row, 'failed');
            }
        });

        // Handle "Send Next Pending" button
        $('#btn-send-next').on('click', function() {
            // Find the first pending row across ALL pages (using datatable API)
            let found = false;
            
            // We search through the underlying DOM nodes managed by DataTables
            table.rows().every(function() {
                if (found) return;
                
                let rowNode = this.node();
                let $row = $(rowNode);
                
                if ($row.attr('data-status') === 'pending') {
                    found = true;
                    // Auto click its send button
                    $row.find('.btn-send-wa').trigger('click');
                    
                    // We optionally jump to the page containing this row if it's not visible
                    // But usually the user just stays on page 1 if we ordered by Pending first.
                }
            });
            
            if (!found) {
                Swal.fire('All Done!', 'There are no pending recipients left in this campaign.', 'success');
                $(this).fadeOut();
            }
        });
    });
</script>
@endpush
