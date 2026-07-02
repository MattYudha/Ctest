@extends ('layouts.dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('mazer/assets/extensions/choices.js/public/assets/styles/choices.css') }}">
<style>
    .kanban-board-container {
        padding-bottom: 10px;
        overflow-x: auto;
    }
    /* Custom scrollbar for kanban */
    .kanban-board-container::-webkit-scrollbar {
        height: 8px;
    }
    .kanban-board-container::-webkit-scrollbar-track {
        background: var(--bs-gray-200);
        border-radius: 4px;
    }
    .kanban-board-container::-webkit-scrollbar-thumb {
        background: var(--bs-gray-400);
        border-radius: 4px;
    }
    [data-bs-theme="dark"] .kanban-board-container::-webkit-scrollbar-track {
        background: #1e1e2d;
    }
    [data-bs-theme="dark"] .kanban-board-container::-webkit-scrollbar-thumb {
        background: #323248;
    }
    .kanban-board {
        display: flex;
        flex-wrap: wrap;
        align-items: stretch; /* Make columns in the same row the same height */
        gap: 1.5rem;
        min-height: calc(100vh - 280px);
        width: 100%;
    }
    .kanban-column {
        flex: 1 1 300px;
        min-width: 280px;
        max-width: 100%;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        height: calc(100vh - 250px); /* Fixed height so they don't shrink */
        min-height: 500px; /* Minimum height to fit a few cards nicely */
        background-color: #ffffff; /* User requested white background for columns */
        border: 1px solid rgba(0,0,0,0.08);
    }
    @media (min-width: 1400px) {
        .kanban-column {
            flex: 1 1 0;
        }
    }
    .kanban-column-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        border-bottom: 1px solid rgba(0,0,0,0.03);
    }
    .kanban-cards {
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
        overflow-y: auto;
        padding: 1rem;
        min-height: 100px;
    }
    /* Scrollbar for columns */
    .kanban-cards::-webkit-scrollbar {
        width: 6px;
    }
    .kanban-cards::-webkit-scrollbar-track {
        background: transparent;
    }
    .kanban-cards::-webkit-scrollbar-thumb {
        background: var(--bs-gray-300);
        border-radius: 10px;
    }
    .kanban-card {
        background-color: #ffffff;
        border: 1px solid rgba(0,0,0,0.08);
        border-radius: 8px;
        cursor: grab;
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        transition: all 0.2s ease-in-out;
        position: relative;
    }
    .kanban-card:active {
        cursor: grabbing;
    }
    .kanban-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .sortable-ghost {
        opacity: 0.5;
        background: var(--bs-gray-200) !important;
        border: 2px dashed var(--bs-primary) !important;
    }
    [data-bs-theme="dark"] .kanban-column {
        background-color: #13131a; /* Darker than card */
        border-color: rgba(255,255,255,0.05);
    }
    [data-bs-theme="dark"] .kanban-column-header {
        border-color: rgba(255,255,255,0.05);
    }
    [data-bs-theme="dark"] .kanban-card {
        background-color: #1e1e2d;
        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    }
    [data-bs-theme="dark"] .sortable-ghost {
        background-color: #151521 !important;
        border-color: #435ebe !important;
    }
    .sortable-drag {
        width: 304px !important;
        opacity: 0.95 !important;
        cursor: grabbing !important;
        z-index: 9999 !important;
        box-shadow: 0 10px 25px rgba(0,0,0,0.15) !important;
        transform: rotate(2deg) !important;
    }
    
    .card-actions {
        position: absolute;
        top: 10px;
        right: 10px;
        opacity: 0;
        transition: opacity 0.2s;
    }
    .kanban-card:hover .card-actions {
        opacity: 1;
    }
    .user-filter-select {
        width: 100%;
    }
    @media (min-width: 768px) {
        .user-filter-select {
            width: 350px;
        }
    }
</style>
@endpush

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row align-items-center">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>CRM Pipeline Board</h3>
                <p class="text-subtitle text-muted">Manage your sales tasks and deals via drag and drop.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first d-flex justify-content-md-end justify-content-start mb-3 mb-md-0">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('crm.dashboard') }}">CRM</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Pipeline</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<section class="section">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <h5 class="mb-0 text-muted">Sales Pipeline Overview</h5>
        <div class="d-flex flex-column flex-md-row align-items-md-center gap-2">
            @if(!$isSales && count($salesUsers) > 0)
            <div class="user-filter-select">
                <select class="form-select shadow-sm rounded-2 fw-bold" onchange="window.location.href='?user_id=' + this.value">
                    <option value="">All Sales Users</option>
                    @foreach($salesUsers as $sUser)
                        <option value="{{ $sUser->id }}" {{ $filterUserId == $sUser->id ? 'selected' : '' }}>
                            {{ $sUser->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            @endif
            <button class="btn btn-primary shadow-sm text-nowrap w-100" data-bs-toggle="modal" data-bs-target="#addDealModal">
                <i class="bi bi-plus-lg me-2"></i> New Deal
            </button>
        </div>
    </div>
    
    @php
        $columns = [
            'lead' => ['title' => 'Leads / Prospect', 'color' => 'secondary', 'icon' => 'bi-funnel'],
            'contacted' => ['title' => 'Contacted', 'color' => 'info', 'icon' => 'bi-telephone'],
            'proposal' => ['title' => 'Proposal Sent', 'color' => 'warning', 'icon' => 'bi-file-text'],
            'won' => ['title' => 'Won', 'color' => 'success', 'icon' => 'bi-check-circle'],
            'lost' => ['title' => 'Lost', 'color' => 'danger', 'icon' => 'bi-x-circle']
        ];
    @endphp

    <div class="kanban-board-container">
        <div class="kanban-board">
            @foreach($columns as $key => $column)
                <div class="kanban-column shadow-sm">
                    <div class="kanban-column-header">
                        <div class="d-flex align-items-center">
                            <div class="bg-light-{{ $column['color'] }} text-{{ $column['color'] }} rounded me-2" style="width: 35px; height: 35px;">
                                <i class="bi {{ $column['icon'] }} fs-5 d-flex align-items-center justify-content-center w-100 h-100" style="line-height: 0;"></i>
                            </div>
                            <h6 class="mb-0 fw-bold">{{ $column['title'] }}</h6>
                        </div>
                        <span class="badge bg-light-secondary text-secondary rounded-pill fw-bold px-2 py-1" id="count-{{ $key }}">
                            {{ isset($deals[$key]) ? count($deals[$key]) : 0 }}
                        </span>
                    </div>
                    
                    <div class="kanban-cards" id="column-{{ $key }}" data-status="{{ $key }}">
                        @if(isset($deals[$key]))
                            @foreach($deals[$key] as $deal)
                                <div class="kanban-card border-{{ $column['color'] }}" style="border-left: 4px solid var(--bs-{{ $column['color'] }}); cursor: pointer;" 
                                     data-id="{{ $deal->id }}"
                                     data-title="{{ $deal->title }}"
                                     data-contact="{{ $deal->crm_contact_id }}"
                                     data-value="{{ $deal->value }}"
                                     data-desc="{{ $deal->description }}"
                                     onclick="openEditModal(this)">
                                    <div class="p-3">
                                        <div class="card-actions">
                                            <form action="{{ route('crm.board.destroy', $deal->id) }}" method="POST" class="d-inline form-delete">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-light-danger rounded-circle p-1" style="width: 26px; height: 26px; display: flex; align-items: center; justify-content: center;" title="Delete">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                        
                                        <div class="mb-2 pe-4">
                                            <h6 class="mb-1 text-truncate fw-bold" style="font-size: 0.95rem;" title="{{ $deal->title }}">{{ $deal->title }}</h6>
                                            @if($deal->contact)
                                                <div class="text-muted small d-flex align-items-center">
                                                    <i class="bi bi-building me-1"></i> 
                                                    <span class="text-truncate">{{ $deal->contact->company_name }}</span>
                                                </div>
                                            @endif
                                        </div>
                                        
                                        @if($deal->description)
                                            <div class="text-muted small mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                {{ $deal->description }}
                                            </div>
                                        @endif
                                        
                                        <div class="d-flex justify-content-between align-items-center mt-auto pt-2 border-top border-light">
                                            @if($deal->value)
                                                <div class="fw-bold text-{{ $column['color'] }} small deal-value-text">
                                                    Rp {{ number_format($deal->value, 0, ',', '.') }}
                                                </div>
                                            @else
                                                <div class="text-muted small">No Value</div>
                                            @endif
                                            
                                            <div class="d-flex align-items-center">
                                                @if($deal->creator)
                                                    <div class="avatar avatar-sm bg-light-primary me-2" title="Created by {{ $deal->creator->name }}" style="width: 20px; height: 20px;">
                                                        <span class="avatar-content text-primary fw-bold" style="font-size: 10px;">
                                                            {{ strtoupper(substr($deal->creator->name, 0, 1)) }}
                                                        </span>
                                                    </div>
                                                    <span class="text-muted small fw-bold" style="font-size: 0.75rem;">{{ $deal->creator->name }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                    
                    <!-- Quick Add Button at bottom of column -->
                    <div class="p-3 pt-0 mt-auto">
                        <button class="btn btn-outline-secondary w-100 fw-bold shadow-none" style="border-style: dashed; border-radius: 8px; border-color: rgba(0,0,0,0.15);" data-bs-toggle="modal" data-bs-target="#addDealModal" onclick="document.querySelector('#addDealModal select[name=status]').value='{{ $key }}'">
                            Add Card
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Add Deal Modal -->
<div class="modal fade" id="addDealModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('crm.board.store') }}" method="POST" onsubmit="this.querySelector('button[type=submit]').disabled = true; this.querySelector('button[type=submit]').innerHTML = 'Saving...';">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Task / Deal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" required placeholder="E.g., Follow up meeting">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Related Contact</label>
                        <select class="form-select" name="crm_contact_id" id="contact_id_add">
                            <option value="">-- None --</option>
                            @foreach($contacts as $contact)
                                <option value="{{ $contact->id }}">{{ $contact->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Estimated Value (Rp)</label>
                        <input type="text" class="form-control rupiah-input" name="value" placeholder="10.000.000">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Initial Stage <span class="text-danger">*</span></label>
                        <select class="form-select" name="status" required>
                            @foreach($columns as $key => $column)
                                <option value="{{ $key }}">{{ $column['title'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Additional notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Task</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Edit Deal Modal -->
<div class="modal fade" id="editDealModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="editDealForm" method="POST" onsubmit="this.querySelector('button[type=submit]').disabled = true; this.querySelector('button[type=submit]').innerHTML = 'Saving...';">
            @csrf
            @method('PUT')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Task / Deal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="editDealCreatorInfo" class="alert alert-light-info py-2 px-3 mb-3 d-none align-items-center">
                        <i class="bi bi-person-circle me-2 fs-5"></i>
                        <div>
                            <span class="small fw-bold d-block">Created by: </span>
                            <span class="small text-muted" id="editDealCreatorName"></span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Related Contact</label>
                        <select class="form-select" name="crm_contact_id" id="contact_id_edit">
                            <option value="">-- None --</option>
                            @foreach($contacts as $contact)
                                <option value="{{ $contact->id }}">{{ $contact->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Estimated Value (Rp)</label>
                        <input type="text" class="form-control rupiah-input" name="value">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('mazer/assets/extensions/choices.js/public/assets/scripts/choices.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Choices.js
        const choicesOptions = {
            searchEnabled: true,
            itemSelectText: '',
            shouldSort: false
        };
        const contactAddChoices = new Choices('#contact_id_add', choicesOptions);
        window.contactEditChoices = new Choices('#contact_id_edit', choicesOptions);
        
        const columns = document.querySelectorAll('.kanban-cards');
        
        columns.forEach(column => {
            new Sortable(column, {
                group: 'kanban', // set both lists to same group
                animation: 250, // slightly longer for smoother transition
                easing: "cubic-bezier(1, 0, 0, 1)", // smooth easing
                ghostClass: 'sortable-ghost',
                dragClass: 'sortable-drag',
                forceFallback: true,
                fallbackClass: 'sortable-drag',
                fallbackOnBody: true,
                fallbackTolerance: 5,
                delay: 200, // wait 200ms before drag starts to allow for scrolling on mobile
                delayOnTouchOnly: true, // only delay if user is using touch
                touchStartThreshold: 5, // how many pixels the point should move before cancelling a delayed drag event
                scroll: true, // Enable auto-scrolling
                scrollSensitivity: 100, // px, how near the mouse must be to an edge to start scrolling.
                scrollSpeed: 25, // px, speed of the scrolling
                bubbleScroll: true, // applies autoscroll to all parent elements, allowing the window to scroll
                onEnd: function (evt) {
                    const itemEl = evt.item;  // dragged HTMLElement
                    const toColumn = evt.to;  // target list
                    const newStatus = toColumn.getAttribute('data-status');
                    
                    // Update left border color visually in real-time
                    const bsColors = {
                        'lead': 'secondary',
                        'contacted': 'info',
                        'proposal': 'warning',
                        'won': 'success',
                        'lost': 'danger'
                    };
                    const colorName = bsColors[newStatus];
                    
                    // Replace border class
                    Array.from(itemEl.classList).forEach(c => {
                        if(c.startsWith('border-')) itemEl.classList.remove(c);
                    });
                    itemEl.classList.add('border-' + colorName);
                    // Replace inline style for specific border color
                    itemEl.style.borderLeft = '4px solid var(--bs-' + colorName + ')';
                    
                    // Update value text color
                    const valueEl = itemEl.querySelector('.deal-value-text');
                    if (valueEl) {
                        Array.from(valueEl.classList).forEach(c => {
                            if(c.startsWith('text-')) valueEl.classList.remove(c);
                        });
                        valueEl.classList.add('text-' + colorName);
                    }
                    
                    // Get all card IDs in the new column for ordering
                    const newOrder = [];
                    toColumn.querySelectorAll('.kanban-card').forEach(card => {
                        newOrder.push(card.getAttribute('data-id'));
                    });

                    // Update counts
                    updateCounts();

                    // Send AJAX request
                    $.ajax({
                        url: '{{ route("crm.board.update-status") }}',
                        type: 'POST',
                        data: {
                            deal_id: itemEl.getAttribute('data-id'),
                            new_status: newStatus,
                            new_order: newOrder,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if(!response.success) {
                                Swal.fire('Error', 'Failed to update deal status', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Server error occurred while updating status', 'error');
                        }
                    });
                }
            });
        });

        function updateCounts() {
            document.querySelectorAll('.kanban-cards').forEach(column => {
                const status = column.getAttribute('data-status');
                const count = column.querySelectorAll('.kanban-card').length;
                document.getElementById('count-' + status).innerText = count;
            });
        }
        
        $(document).on('submit', '.form-delete', function(e) {
            e.preventDefault();
            let form = this;
            Swal.fire({
                title: 'Delete Task/Deal?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
        
        // Format rupiah helper
        function formatRupiah(angka, prefix) {
            let number_string = angka.replace(/[^,\d]/g, '').toString(),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);
            
            if (ribuan) {
                let separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            
            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return prefix == undefined ? rupiah : (rupiah ? prefix + rupiah : '');
        }

        // Apply formatting on input
        $('.rupiah-input').on('keyup', function(e) {
            this.value = formatRupiah(this.value);
        });
        
        // Remove dots on submit for all forms with rupiah input
        $('form').on('submit', function() {
            $(this).find('.rupiah-input').each(function() {
                this.value = this.value.replace(/\./g, '');
            });
        });
        
        // Expose format helper globally if needed
        window.formatRupiah = formatRupiah;
    });

    function openEditModal(el) {
        // Prevent opening if clicking on the delete button
        if(event.target.closest('.card-actions')) return;
        
        let id = el.getAttribute('data-id');
        let title = el.getAttribute('data-title');
        let contact = el.getAttribute('data-contact');
        let value = el.getAttribute('data-value');
        let desc = el.getAttribute('data-desc');
        
        let creatorName = '';
        const creatorSpan = el.querySelector('.avatar + span');
        if (creatorSpan) {
            creatorName = creatorSpan.innerText.trim();
        }
        
        let form = document.getElementById('editDealForm');
        form.action = '{{ url("crm/board") }}/' + id;
        
        form.querySelector('[name=title]').value = title || '';
        
        // Update choices.js value
        if (window.contactEditChoices) {
            window.contactEditChoices.setChoiceByValue(contact || '');
        } else {
            form.querySelector('[name=crm_contact_id]').value = contact || '';
        }
        let parsedValue = value ? Math.floor(Number(value)).toString() : '';
        form.querySelector('[name=value]').value = parsedValue ? window.formatRupiah(parsedValue) : '';
        form.querySelector('[name=description]').value = desc || '';
        
        let creatorInfoAlert = document.getElementById('editDealCreatorInfo');
        if (creatorName) {
            document.getElementById('editDealCreatorName').innerText = creatorName;
            creatorInfoAlert.classList.remove('d-none');
            creatorInfoAlert.classList.add('d-flex');
        } else {
            creatorInfoAlert.classList.add('d-none');
            creatorInfoAlert.classList.remove('d-flex');
        }
        
        new bootstrap.Modal(document.getElementById('editDealModal')).show();
    }
</script>
@endpush
