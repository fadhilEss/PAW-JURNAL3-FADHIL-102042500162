@extends('layouts.app')

@section('title', 'Edit Tugas — ' . $assignment->judul)

@section('content')
<div style="max-width: 760px; margin: 0 auto;">
    <div style="margin-bottom: 1.25rem;">
        <a href="{{ route('courses.show', $assignment->course_id) }}" class="btn btn-secondary btn-sm">
            Kembali ke Mata Kuliah
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <h1 class="card-title">Edit Penugasan</h1>
                <p class="card-subtitle">Mata Kuliah: <strong>{{ $assignment->course->nama_mk }}</strong> ({{ $assignment->course->kode_mk }})</p>
            </div>
            <span class="code-pill">{{ $assignment->course->kode_mk }}</span>
        </div>

        <div class="card-body">
            <form action="{{ route('assignments.update', $assignment->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="judul" class="form-label">
                        Judul Tugas <span class="required-indicator">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="judul" 
                        id="judul" 
                        class="form-control @error('judul') is-invalid @enderror" 
                        value="{{ old('judul', $assignment->judul) }}" 
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
                        value="{{ old('deadline', $assignment->deadline ? $assignment->deadline->format('Y-m-d\TH:i') : '') }}" 
                        required
                    >
                    <div class="form-help">Waktu penutupan pengumpulan tugas dalam zona waktu sistem.</div>
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
                        required
                    >{{ old('deskripsi', $assignment->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                    <a href="{{ route('courses.show', $assignment->course_id) }}" class="btn btn-secondary">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
