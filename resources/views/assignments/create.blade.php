@extends('layouts.app')

@section('title', 'Tambah Tugas — ' . $course->kode_mk)

@section('content')
<div style="max-width: 760px; margin: 0 auto;">
    <div style="margin-bottom: 1.25rem;">
        <a href="{{ route('courses.show', $course->id) }}" class="btn btn-secondary btn-sm">
            Kembali ke Mata Kuliah
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <h1 class="card-title">Buat Penugasan Baru</h1>
                <p class="card-subtitle">Mata Kuliah: <strong>{{ $course->nama_mk }}</strong> ({{ $course->kode_mk }})</p>
            </div>
            <span class="code-pill">{{ $course->kode_mk }}</span>
        </div>

        <div class="card-body">
            <form action="{{ route('assignments.store', $course->id) }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="judul" class="form-label">
                        Judul Tugas <span class="required-indicator">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="judul" 
                        id="judul" 
                        class="form-control @error('judul') is-invalid @enderror" 
                        value="{{ old('judul') }}" 
                        placeholder="Contoh: Tugas 02 - Implementasi Autentikasi & Otorisasi RBAC" 
                        required
                    >
                    @error('judul')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="deadline" class="form-label">
                        Batas Waktu Pengumpulan (Deadline) <span class="required-indicator">*</span>
                    </label>
                    <input 
                        type="datetime-local" 
                        name="deadline" 
                        id="deadline" 
                        class="form-control @error('deadline') is-invalid @enderror" 
                        value="{{ old('deadline', now()->addDays(7)->setTime(23, 59)->format('Y-m-d\TH:i')) }}" 
                        required
                    >
                    <div class="form-help">Waktu penutupan pengumpulan tugas. Mahasiswa tidak dapat mengumpulkan setelah waktu ini.</div>
                    @error('deadline')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="deskripsi" class="form-label">
                        Petunjuk & Deskripsi Tugas <span class="required-indicator">*</span>
                    </label>
                    <textarea 
                        name="deskripsi" 
                        id="deskripsi" 
                        rows="7" 
                        class="form-control @error('deskripsi') is-invalid @enderror" 
                        placeholder="Tuliskan instruksi pengerjaan tugas, kriteria penilaian, dan format jawaban teks yang diharapkan dari mahasiswa..." 
                        required
                    >{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                    <a href="{{ route('courses.show', $course->id) }}" class="btn btn-secondary">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Terbitkan Penugasan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
