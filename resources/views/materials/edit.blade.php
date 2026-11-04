@extends('layouts.app')

@section('title', 'Edit Materi — ' . $material->judul)

@section('content')
<div style="max-width: 760px; margin: 0 auto;">
    <div style="margin-bottom: 1.25rem;">
        <a href="{{ route('courses.show', $material->course_id) }}" class="btn btn-secondary btn-sm">
            Kembali ke Mata Kuliah
        </a>
    </div>

    <div class="card">
        <div class="card-header">
            <div>
                <h1 class="card-title">Edit Materi Pembelajaran</h1>
                <p class="card-subtitle">Mata Kuliah: <strong>{{ $material->course->nama_mk }}</strong> ({{ $material->course->kode_mk }})</p>
            </div>
            <span style="color: var(--slate-600); font-size: 0.8125rem; font-weight: 500;">Minggu ke-{{ $material->minggu_ke }}</span>
        </div>

        <div class="card-body">
            <form action="{{ route('materials.update', $material->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="judul" class="form-label">
                        Judul Materi <span class="required-indicator">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="judul" 
                        id="judul" 
                        class="form-control @error('judul') is-invalid @enderror" 
                        value="{{ old('judul', $material->judul) }}" 
                        required
                    >
                    @error('judul')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                    <!-- Minggu ke -->
                    <div class="form-group">
                        <label for="minggu_ke" class="form-label">
                            Minggu Pertemuan ke- <span class="required-indicator">*</span>
                        </label>
                        <input 
                            type="number" 
                            name="minggu_ke" 
                            id="minggu_ke" 
                            min="1" 
                            max="16" 
                            class="form-control @error('minggu_ke') is-invalid @enderror" 
                            value="{{ old('minggu_ke', $material->minggu_ke) }}" 
                            required
                        >
                        @error('minggu_ke')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Urutan -->
                    <div class="form-group">
                        <label for="urutan" class="form-label">
                            Nomor Urut Tampilan
                        </label>
                        <input 
                            type="number" 
                            name="urutan" 
                            id="urutan" 
                            min="0" 
                            class="form-control @error('urutan') is-invalid @enderror" 
                            value="{{ old('urutan', $material->urutan) }}"
                        >
                        @error('urutan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Konten Materi -->
                <div class="form-group">
                    <label for="konten" class="form-label">
                        Konten & Instruksi Pembelajaran <span class="required-indicator">*</span>
                    </label>
                    <textarea 
                        name="konten" 
                        id="konten" 
                        rows="8" 
                        class="form-control @error('konten') is-invalid @enderror" 
                        required
                    >{{ old('konten', $material->konten) }}</textarea>
                    @error('konten')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--slate-200);">
                    <a href="{{ route('courses.show', $material->course_id) }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
