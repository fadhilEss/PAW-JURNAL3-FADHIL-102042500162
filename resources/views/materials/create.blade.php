@extends('layouts.app')

@section('title', 'Tambah Materi — ' . $course->kode_mk)

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
                <h1 class="card-title">Tambah Materi Pembelajaran</h1>
                <p class="card-subtitle">Mata Kuliah: <strong>{{ $course->nama_mk }}</strong> ({{ $course->kode_mk }})</p>
            </div>
            <span class="code-pill">{{ $course->kode_mk }}</span>
        </div>

        <div class="card-body">
            <form action="{{ route('materials.store', $course->id) }}" method="POST">
                @csrf

                <div class="form-group">
                    <label for="judul" class="form-label">
                        Judul Materi <span class="required-indicator">*</span>
                    </label>
                    <input 
                        type="text" 
                        name="judul" 
                        id="judul" 
                        class="form-control @error('judul') is-invalid @enderror" 
                        value="{{ old('judul') }}" 
                        placeholder="Contoh: Pertemuan 03 - Pembuatan Controller & Form Request" 
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
                            value="{{ old('minggu_ke', $nextMinggu) }}" 
                            required
                        >
                        <div class="form-help">Rentang minggu antara 1 sampai 16.</div>
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
                            value="{{ old('urutan', 0) }}"
                        >
                        <div class="form-help">Urutan tayang di dalam minggu pertemuan.</div>
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
                        placeholder="Tuliskan rangkuman materi, pokok bahasan, referensi pustaka, atau petunjuk belajar mingguan..." 
                        required
                    >{{ old('konten') }}</textarea>
                    @error('konten')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--slate-200);">
                    <a href="{{ route('courses.show', $course->id) }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">Publikasikan Materi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
