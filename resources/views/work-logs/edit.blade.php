@extends('layouts.dashboard')

@section('content')
<div class="page-heading">
    <div class="page-title">
        <div class="row">
            <div class="col-12 col-md-6 order-md-1 order-last">
                <h3>Ubah Log Aktivitas</h3>
                <p class="text-subtitle text-muted">Perbarui catatan aktivitas harian yang telah Anda buat.</p>
            </div>
            <div class="col-12 col-md-6 order-md-2 order-first">
                <nav aria-label="breadcrumb" class="breadcrumb-header float-start float-lg-end">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('work-logs.index') }}">My Activity</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Update Log Activity</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>
</div>

<section class="section">
    <div class="card">
        <div class="card-body pt-4">
            
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li><i class="bi bi-exclamation-triangle me-1"></i> {{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('work-logs.update', $work_log->id) }}" method="POST" enctype="multipart/form-data" id="form-log">
                @csrf
                @method('PUT')
                
                <div class="alert alert-info d-flex align-items-center" role="alert">
                    <i class="bi bi-info-circle-fill me-2 fs-4"></i>
                    <div>
                        <strong>Perhatian:</strong> Pengisian log dikunci berdasarkan tanggal awal pembuatan log. Sistem KPI menghitung performa log berdasarkan <em>Hari Unik (Unique Day)</em>, sehingga Anda tidak bisa mengubah tanggal log ini ke hari lain untuk merapel performa hari sebelumnya.
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tanggal Pengisian</label>
                        <input type="date" name="log_date" class="form-control bg-light" value="{{ \Carbon\Carbon::parse($work_log->log_date)->format('Y-m-d') }}" readonly required style="cursor: not-allowed;">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Hubungkan dengan Tugas (Task)</label>
                        <select name="task_id" class="form-select">
                            <option value="">-- Tidak Ada Tugas Terkait --</option>
                            @foreach($tasks as $task)
                                <option value="{{ $task->id }}" {{ $work_log->task_id == $task->id ? 'selected' : '' }}>{{ $task->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">Unggah Lampiran Baru (Opsional)</label>
                        <input type="file" name="evidence" class="form-control">
                        @if($work_log->evidence_path)
                            <div class="mt-2 p-2 bg-light-primary rounded border">
                                <small class="text-primary d-block fw-bold"><i class="bi bi-paperclip"></i> Lampiran saat ini:</small>
                                <a href="{{ asset('storage/'.$work_log->evidence_path) }}" target="_blank" class="small text-decoration-none">Buka / Lihat Lampiran</a>
                            </div>
                        @endif
                        <small class="text-muted d-block mt-1">Maksimal ukuran berkas adalah 5MB. Kosongkan jika tidak ingin mengubah lampiran lama.</small>
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label fw-bold">Rincian Aktivitas</label>
                        <textarea name="description" class="form-control" rows="10" style="resize: vertical;" required>{{ $work_log->description }}</textarea>
                    </div>
                </div>
                
                <div class="d-flex justify-content-end gap-2 mt-3 pt-3 border-top">
                    <a href="{{ route('work-logs.index') }}" class="btn btn-light-secondary">Batal / Kembali</a>
                    <button type="submit" class="btn btn-primary px-4 d-flex align-items-center" id="btn-submit">
                        <i class="bi bi-save me-2 mb-2"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
    document.getElementById('form-log').addEventListener('submit', function() {
        let btn = document.getElementById('btn-submit');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Menyimpan...';
    });
</script>
@endsection