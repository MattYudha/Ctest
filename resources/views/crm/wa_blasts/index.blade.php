@extends ('layouts.dashboard')

@section ('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>WhatsApp Blast</h3>
                    <p class="text-subtitle text-muted">Manage and track your WhatsApp mass messaging campaigns.</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">WA Blasts</li>
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
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <a href="{{ route('crm.wa-blasts.create') }}" class="btn btn-primary">
                        <i class="bi bi-whatsapp me-1"></i> Create New WA Campaign
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle" id="blast-table" style="width: 100%">
                        <thead>
                            <tr>
                                <th style="width: 15%">Date</th>
                                <th style="width: 25%">Campaign Name</th>
                                <th style="width: 10%">Status</th>
                                <th style="width: 25%">Sending Progress</th>
                                <th style="width: 15%">Created By</th>
                                <th style="width: 10%" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($blasts as $blast)
                                <tr>
                                    <td>{{ $blast->created_at->format('d M Y, H:i') }}</td>

                                    <td class="fw-bold text-body">{{ $blast->campaign_name }}</td>

                                    <td>
                                        @if ($blast->status == 'draft')
                                            <span class="badge bg-secondary">Draft</span>
                                        @elseif ($blast->status == 'active')
                                            <span class="badge bg-primary">Active</span>
                                        @else
                                            <span class="badge bg-success">Completed</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="mb-1 text-sm fw-bold">
                                                {{ $blast->sent_count }} of {{ $blast->target_count }} Sent
                                            </span>
                                            <div class="progress progress-sm">
                                                @php
                                                    $percent = $blast->target_count > 0 ? ($blast->sent_count / $blast->target_count) * 100 : 0;
                                                @endphp
                                                <div
                                                    class="progress-bar bg-success"
                                                    role="progressbar"
                                                    style="width: {{ $percent }}%"
                                                    aria-valuenow="{{ $percent }}"
                                                    aria-valuemin="0"
                                                    aria-valuemax="100"
                                                ></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $blast->creator->name ?? 'System' }}</td>

                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <a
                                                href="{{ route('crm.wa-blasts.show', $blast->id) }}"
                                                class="btn btn-sm btn-outline-info"
                                                title="View & Send"
                                            >
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            <form
                                                action="{{ route('crm.wa-blasts.destroy', $blast->id) }}"
                                                method="POST"
                                                onsubmit="
                                                    return confirm(
                                                        'Are you sure you want to delete this campaign? All recipient logs will be deleted.',
                                                    );
                                                "
                                            >
                                                @csrf
                                                @method ('DELETE')
                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    title="Delete Campaign"
                                                >
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
@endsection

@push ('scripts')
    <script>
        $(document).ready(function () {
            $('#blast-table').DataTable({
                responsive: true,
                order: [[0, 'desc']],
                columnDefs: [
                    { orderable: false, targets: 5 },
                ],
            });
        });
    </script>
@endpush
