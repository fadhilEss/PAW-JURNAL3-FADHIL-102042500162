@extends('layouts.app')

@section('title', Auth::user()->isDosen() ? 'Mata Kuliah Diampu' : 'Katalog Mata Kuliah')

@section('content')
<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 class="card-title">
                {{ Auth::user()->isDosen() ? 'Mata Kuliah yang Diampu' : 'Katalog Mata Kuliah' }}
            </h1>
            <p class="card-subtitle">
                @if(Auth::user()->isDosen())
                    Kelola materi, tugas, dan persetujuan mahasiswa pada mata kuliah yang Anda ampu.
                @else
                    Jelajahi mata kuliah yang tersedia di CeLOE LMS dan ajukan pendaftaran untuk bergabung ke kelas.
                @endif
            </p>
        </div>

        @if(Auth::user()->isDosen())
            <a href="{{ route('courses.create') }}" class="btn btn-primary">
                + Buat Mata Kuliah Baru
            </a>
        @endif
    </div>

    <div class="card-body" style="padding-bottom: 0;">
        <form action="{{ route('courses.index') }}" method="GET" style="display: flex; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1.5rem;">
            <div style="flex: 1; min-width: 260px;">
                <input 
                    type="text" 
                    name="search" 
                    class="form-control" 
                    placeholder="Cari berdasarkan kode mata kuliah, nama, atau semester..." 
                    value="{{ request('search') }}"
                >
            </div>
            <button type="submit" class="btn btn-secondary">
                Cari
            </button>
            @if(request('search'))
                <a href="{{ route('courses.index') }}" class="btn btn-secondary" style="color: var(--slate-500);">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <div style="padding: 0 1.5rem 1.5rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.25rem;">
            @forelse($courses as $course)
                <div class="card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between;">
                    <div class="card-body" style="padding: 1.25rem; flex: 1;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <span style="font-weight: 700; color: var(--slate-800); font-size: 0.875rem;">{{ $course->kode_mk }}</span>
                            <span style="color: var(--slate-500); font-size: 0.8125rem;">{{ $course->sks }} SKS</span>
                        </div>

                        <h2 style="font-size: 1.0625rem; font-weight: 700; color: var(--slate-900); margin-bottom: 0.5rem; line-height: 1.35;">
                            <a href="{{ route('courses.show', $course->id) }}" style="color: inherit; text-decoration: none;">
                                {{ $course->nama_mk }}
                            </a>
                        </h2>

                        <p style="font-size: 0.8125rem; color: var(--slate-600); margin-bottom: 0.75rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $course->deskripsi ?: 'Tidak ada deskripsi mata kuliah.' }}
                        </p>

                        <div style="font-size: 0.8125rem; color: var(--slate-500); display: flex; flex-direction: column; gap: 0.25rem; margin-top: auto;">
                            <div><strong>Dosen Pengampu:</strong> {{ $course->dosen->name ?? '-' }}</div>
                            <div><strong>Semester:</strong> {{ $course->semester }}</div>
                        </div>
                    </div>

                    <div style="padding: 0.875rem 1.25rem; background-color: var(--slate-50); border-top: 1px solid var(--slate-200); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                        <span style="font-size: 0.75rem; color: var(--slate-500);">
                            {{ $course->students_count ?? 0 }} Mahasiswa Aktif
                        </span>

                        <div style="display: flex; align-items: center; gap: 0.375rem; flex-wrap: wrap;">
                            @if(Auth::user()->isDosen())
                                <a href="{{ route('courses.show', $course->id) }}" class="btn btn-primary btn-sm">
                                    Kelola Kelas
                                </a>
                                <a href="{{ route('courses.edit', $course->id) }}" class="btn btn-secondary btn-sm" title="Edit Mata Kuliah">
                                    Edit
                                </a>
                                <form action="{{ route('courses.destroy', $course->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus mata kuliah {{ $course->kode_mk }}?');" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus Mata Kuliah">
                                        Hapus
                                    </button>
                                </form>
                            @else
                                @php
                                    $enrollStatus = $course->getStudentEnrollmentStatus(Auth::id());
                                @endphp

                                @if($enrollStatus === 'approved')
                                    <span style="background-color: #d1fae5; color: #065f46; font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: 600;">
                                        ✓ Terdaftar
                                    </span>
                                    <a href="{{ route('courses.show', $course->id) }}" class="btn btn-primary btn-sm">
                                        Masuk Kelas
                                    </a>
                                @elseif($enrollStatus === 'pending')
                                    <span style="background-color: #fef3c7; color: #92400e; font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: 600;">
                                        ⏳ Menunggu Persetujuan
                                    </span>
                                    <a href="{{ route('courses.show', $course->id) }}" class="btn btn-secondary btn-sm">
                                        Ringkasan
                                    </a>
                                @elseif($enrollStatus === 'rejected')
                                    <span style="background-color: #fee2e2; color: #991b1b; font-size: 0.75rem; padding: 0.25rem 0.5rem; border-radius: 4px; font-weight: 600;">
                                        ✕ Ditolak
                                    </span>
                                    <form action="{{ route('courses.enroll-request', $course->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary btn-sm">
                                            Ajukan Ulang
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('courses.enroll-request', $course->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            + Ajukan Pendaftaran
                                        </button>
                                    </form>
                                    <a href="{{ route('courses.show', $course->id) }}" class="btn btn-secondary btn-sm">
                                        Ringkasan
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; padding: 3rem 1.5rem; text-align: center; color: var(--slate-500);">
                    <div style="font-weight: 700; font-size: 1rem; color: var(--slate-800); margin-bottom: 0.5rem;">Tidak Ada Mata Kuliah Ditemukan</div>
                    <p style="font-size: 0.875rem; margin: 0;">
                        @if(Auth::user()->isDosen())
                            Anda belum membuat mata kuliah. Klik tombol <strong>"+ Buat Mata Kuliah Baru"</strong> di atas.
                        @else
                            Belum ada mata kuliah yang cocok dengan kriteria pencarian Anda.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $courses->links() }}
        </div>
    </div>
</div>
@endsection
