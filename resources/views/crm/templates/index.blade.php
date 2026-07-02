@extends('layouts.dashboard')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Message Templates</h3>
                <p class="text-subtitle text-muted">Manage your email and WA templates for CRM.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">CRM Templates</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <section class="section">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible show fade">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <a href="{{ route('crm.templates.create') }}" class="btn btn-primary">
                        <i class="bi bi-plus"></i> Create New Template
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle" id="template-table" style="width: 100%">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Subject</th>
                                <th>Created By</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($templates as $template)
                                <tr>
                                    <td class="fw-bold text-body">{{ $template->name }}</td>
                                    <td>
                                        @if($template->type === 'email')
                                            <span class="badge bg-primary">Email</span>
                                        @else
                                            <span class="badge bg-success">WhatsApp</span>
                                        @endif
                                    </td>
                                    <td>{{ $template->subject ?? '-' }}</td>
                                    <td>{{ $template->creator->name ?? 'System' }}</td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-info view-template-btn" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#viewTemplateModal"
                                                    data-name="{{ $template->name }}"
                                                    data-type="{{ $template->type }}"
                                                    data-subject="{{ $template->subject }}"
                                                    data-body="{{ $template->body }}"
                                                    title="View Template">
                                                <i class="bi bi-eye"></i>
                                            </button>
                                            <a href="{{ route('crm.templates.edit', $template->id) }}" class="btn btn-sm btn-outline-warning" title="Edit Template">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('crm.templates.destroy', $template->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this template?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Template">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- View Template Modal -->
<div class="modal fade" id="viewTemplateModal" tabindex="-1" aria-labelledby="viewTemplateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewTemplateModalLabel">View Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <h6 class="text-muted text-uppercase" style="font-size: 0.75rem;">Template Name</h6>
                        <p class="fw-bold mb-0" id="modal-template-name"></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <h6 class="text-muted text-uppercase" style="font-size: 0.75rem;">Template Type</h6>
                        <p class="mb-0" id="modal-template-type"></p>
                    </div>
                    <div class="col-12 mb-3" id="modal-subject-container">
                        <h6 class="text-muted text-uppercase" style="font-size: 0.75rem;">Email Subject</h6>
                        <p class="fw-bold mb-0" id="modal-template-subject"></p>
                    </div>
                    <div class="col-12">
                        <h6 class="text-muted text-uppercase" style="font-size: 0.75rem;">Message Body</h6>
                        <div class="p-3 bg-light border rounded" id="modal-template-body" style="white-space: pre-wrap;"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        $('#template-table').DataTable({
            responsive: true,
            order: [[0, 'asc']],
            language: {
                search: "Search templates:",
                lengthMenu: "Show _MENU_ templates per page",
                info: "Showing _START_ to _END_ of _TOTAL_ templates",
                infoEmpty: "Showing 0 to 0 of 0 templates",
                infoFiltered: "(filtered from _MAX_ total templates)"
            }
        });

        // View Template Modal Population
        $('.view-template-btn').on('click', function() {
            var name = $(this).data('name');
            var type = $(this).data('type');
            var subject = $(this).data('subject');
            var body = $(this).data('body');
            
            $('#modal-template-name').text(name);
            $('#modal-template-body').text(body);
            
            if (type === 'email') {
                $('#modal-template-type').html('<span class="badge bg-primary">Email</span>');
                $('#modal-subject-container').show();
                $('#modal-template-subject').text(subject ? subject : '-');
            } else {
                $('#modal-template-type').html('<span class="badge bg-success">WhatsApp</span>');
                $('#modal-subject-container').hide();
            }
        });
    });
</script>
@endpush
