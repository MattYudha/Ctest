@extends('layouts.dashboard')
@section('title', 'Master KPI Configuration')
@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold mb-1">Master KPI Configuration</h2>
            <p class="text-muted">Konfigurasi bobot indikator penilaian kinerja karyawan.</p>
        </div>
    </div>
    
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        </div>
    @endif

    @if(session('error') || $errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') ?? 'Terdapat kesalahan pada input Anda.' }}
        </div>
    @endif

    <div class="accordion mb-4 shadow-sm" id="kpiGuideAccordion">
        <div class="accordion-item border-0 rounded-4 overflow-hidden">
            <h2 class="accordion-header" id="guideHeading">
                <button class="accordion-button text-primary fw-bold collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGuide" aria-expanded="false" aria-controls="collapseGuide">
                    <i class="bi bi-info-circle-fill me-2"></i> Klik di sini untuk Panduan Lengkap Darimana Nilai KPI Dihitung
                </button>
            </h2>
            <div id="collapseGuide" class="accordion-collapse collapse" aria-labelledby="guideHeading" data-bs-parent="#kpiGuideAccordion">
                <div class="accordion-body small lh-lg">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold text-primary"><i class="bi bi-fingerprint me-2"></i>1. Presensi (Checkout)</h6>
                            <p class="mb-0 text-muted"><strong>Sumber Penilaian:</strong> Riwayat absensi karyawan.<br>
                            Sistem menghitung rasio jumlah hari karyawan melakukan <em>Checkout</em> secara disiplin dibandingkan dengan total kewajiban hari kerjanya dalam satu bulan.</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold text-primary"><i class="bi bi-journal-text me-2"></i>2. Log Kerja Harian</h6>
                            <p class="mb-0 text-muted"><strong>Sumber Penilaian:</strong> Laporan pekerjaan (Work Log).<br>
                            Sistem membandingkan jumlah laporan kerja yang telah divalidasi/diisi dengan total hari kerja wajib karyawan. Karyawan wajib mengisi log setiap hari.</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold text-primary"><i class="bi bi-list-check me-2"></i>3. Penyelesaian Tugas (Tasks)</h6>
                            <p class="mb-0 text-muted"><strong>Sumber Penilaian:</strong> Modul <em>Tasks / Pipeline</em>.<br>
                            Dihitung dari persentase tugas dengan status "Selesai/Done" dibandingkan dengan seluruh tugas yang dibebankan kepada karyawan pada bulan tersebut.</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold text-primary"><i class="bi bi-graph-up-arrow me-2"></i>4. Target Sales / Deal</h6>
                            <p class="mb-0 text-muted"><strong>Sumber Penilaian:</strong> Modul CRM (Penjualan).<br>
                            Khusus tim Sales. Diukur dari persentase prospek (*leads*) atau tiket *deal* yang berhasil "Dimenangkan / Won" dibandingkan dengan total target/deal yang masuk.</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold text-primary"><i class="bi bi-envelope-paper me-2"></i>5. Pengelolaan Surat</h6>
                            <p class="mb-0 text-muted"><strong>Sumber Penilaian:</strong> Modul Persuratan (*Letters*).<br>
                            Menilai kualitas dan akurasi administrasi. Dihitung berdasarkan jumlah surat buatan karyawan yang sukses disetujui (Approved) tanpa direvisi/ditolak oleh atasan.</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6 class="fw-bold text-primary"><i class="bi bi-wallet2 me-2"></i>6. Buku Kas (Cashbook)</h6>
                            <p class="mb-0 text-muted"><strong>Sumber Penilaian:</strong> Modul Finance (Anti-Anomali).<br>
                            &bull; <strong>Tim Finance:</strong> Kecepatan input transaksi maksimal 2 Hari Kerja.<br>
                            &bull; <strong>Non-Finance:</strong> Persentase klaim/kas bon yang disetujui (Klaim berstatus *Pending* tidak akan merusak skor).</p>
                        </div>
                    </div>
                    <div class="alert alert-warning mb-0 mt-2 py-2 px-3 border-0">
                        <i class="bi bi-lightbulb-fill me-1"></i> <strong>Sistem Anti-Anomali (N/A):</strong> Jika seorang karyawan tidak memiliki kewajiban di bulan terkait (misal: 0 tugas, 0 pengajuan klaim, atau bukan tim Sales), sistem akan secara otomatis memberi status <strong>N/A</strong> dan menyebar bobotnya ke indikator lain agar gaji/skor karyawan tidak dirugikan.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-xl-10">
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-transparent border-0 pt-4 pb-0 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Indikator KPI Global</h5>
                    <form action="{{ route('kpi-masters.reset') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mereset ke pengaturan awal?');">
                        @csrf
                        <button class="btn btn-outline-danger btn-sm rounded-pill"><i class="bi bi-arrow-counterclockwise"></i> Reset Default</button>
                    </form>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('kpi-masters.publish') }}" method="POST" id="kpiForm">
                        @csrf
                        
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Indikator</th>
                                        <th style="width:30%">Role Terdampak (N/A jika di luar)</th>
                                        <th style="width:20%">Bobot (%)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($config as $index => $ind)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ $ind['label'] }}</div>
                                            <small class="text-muted">{{ $ind['desc'] }}</small>
                                            <input type="hidden" name="indicators[{{ $index }}][key]" value="{{ $ind['key'] }}">
                                        </td>
                                        <td>
                                            <select name="indicators[{{ $index }}][applicable_roles][]" class="form-select select2-roles" multiple data-placeholder="Pilih Role (Kosongkan = Berlaku ke Semua)">
                                                <option value="*" {{ $ind['is_all'] ? 'selected' : '' }}>Semua Role</option>
                                                @foreach($roles as $role)
                                                    <option value="{{ $role->title }}" {{ in_array($role->title, $ind['applicable_roles'] ?? []) && !$ind['is_all'] ? 'selected' : '' }}>
                                                        {{ $role->title }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <input type="number" class="form-control kpi-weight" name="indicators[{{ $index }}][weight]" value="{{ $ind['weight'] }}" min="0" max="100" required>
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2" class="text-end text-uppercase fs-6">Total Bobot</th>
                                        <th>
                                            <div class="input-group">
                                                <input type="text" class="form-control fw-bold fs-5 text-center" id="totalWeight" value="0" readonly>
                                                <span class="input-group-text fw-bold">%</span>
                                            </div>
                                            <div id="totalError" class="text-danger small mt-1 d-none"><i class="bi bi-x-circle"></i> Total harus 100%</div>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary px-5 rounded-pill shadow" id="btnSave">
                                <i class="bi bi-save me-1"></i> Simpan Konfigurasi
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Main Input Area */
    .select2-container .select2-selection--multiple {
        border-color: var(--bs-border-color, #dee2e6);
        min-height: 38px;
        background-color: transparent; 
    }
    /* Selected Tags (Chips) */
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: rgba(128, 128, 128, 0.2);
        border: 1px solid var(--bs-border-color, #dee2e6);
        color: inherit;
    }
    /* Input Text */
    .select2-container--default .select2-search--inline .select2-search__field {
        color: inherit;
    }
    /* Dropdown Menu Container */
    .select2-dropdown {
        background-color: var(--bs-body-bg, #212529);
        border-color: var(--bs-border-color, #dee2e6);
        color: inherit;
    }
    /* Dropdown Options */
    .select2-container--default .select2-results__option {
        color: inherit;
        background-color: transparent;
    }
    /* Selected Option in Dropdown */
    .select2-container--default .select2-results__option[aria-selected=true] {
        background-color: rgba(128, 128, 128, 0.15);
        color: inherit;
    }
    /* Hovered/Highlighted Option in Dropdown */
    .select2-container--default .select2-results__option--highlighted[aria-selected] {
        background-color: var(--bs-primary, #0d6efd);
        color: #ffffff !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    $('.select2-roles').select2({
        width: '100%'
    });

    // Validasi Total Bobot (Harus 100)
    const weightInputs = document.querySelectorAll('.kpi-weight');
    const totalInput = document.getElementById('totalWeight');
    const totalError = document.getElementById('totalError');
    const btnSave = document.getElementById('btnSave');
    
    function calculateTotal() {
        let total = 0;
        weightInputs.forEach(input => {
            total += parseInt(input.value) || 0;
        });
        
        totalInput.value = total;
        
        if (total !== 100) {
            totalInput.classList.add('is-invalid', 'text-danger');
            totalInput.classList.remove('is-valid', 'text-success');
            totalError.classList.remove('d-none');
            btnSave.disabled = true;
        } else {
            totalInput.classList.remove('is-invalid', 'text-danger');
            totalInput.classList.add('is-valid', 'text-success');
            totalError.classList.add('d-none');
            btnSave.disabled = false;
        }
    }
    
    weightInputs.forEach(input => {
        input.addEventListener('input', calculateTotal);
    });
    
    calculateTotal();
});
</script>
@endpush
