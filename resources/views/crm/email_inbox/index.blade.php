@extends ('layouts.dashboard')

@section ('content')
    <div class="page-heading">
        <div class="page-title">
            <div class="row">
                <div class="col-12 col-md-6 order-md-1 order-last">
                    <h3>Email Inbox</h3>
                    <p class="text-subtitle text-muted">Monitor and manage mass email deliveries to CRM contacts.</p>
                </div>
                <div class="col-12 col-md-6 order-md-2 order-first">
                    <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}">Dashboard</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">Email Blasts</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div>
        <iframe
            src="https://nextcloud.aratechnology.id"
            width="100%"
            height="900"
            style="border:none;"
        >
        </iframe>
    </div>
   
    @push ('scripts')
        <script>
            $(document).ready(function () {
                $('#blast-table').DataTable({
                    responsive: true,
                    order: [[0, 'desc']], // sort from newest date
                    columnDefs: [
                        { orderable: false, targets: 5 }, // disable sorting for actions column
                    ],
                });
            });
        </script>
    @endpush
@endsection
