@extends('layouts.app')

@section('title', 'Daftar Pengumpulan — ' . $assignment->judul)

@section('content')
<div style="margin-bottom: 1.25rem;">
    <a href="{{ route('courses.show', $assignment->course_id) }}" class="btn btn-secondary btn-sm">
        Kembali ke Mata Kuliah
    </a>
</div>

<!-- Header Penugasan -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-header" style="flex-wrap: wrap; gap: 1rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                <span class="code-pill">{{ $assignment->course->kode_mk }}</span>
                <span style="font-size: 0.85rem; color: var(--text-muted);">{{ $assignment->course->nama_mk }}</span>
            </div>
            <h1 class="card-title">{{ $assignment->judul }}</h1>
            <p class="card-subtitle">
                Batas Pengumpulan: <strong>{{ $assignment->deadline ? $assignment->deadline->translatedFormat('l, d F Y — H:i') . ' WIB' : '-' }}</strong>
                @if($assignment->isOverdue())
                    <span style="color: #dc2626; font-size: 0.8125rem; font-weight: 600; margin-left: 0.5rem;">Batas Waktu Terlewat</span>
                @else
                    <span style="color: #059669; font-size: 0.8125rem; font-weight: 600; margin-left: 0.5rem;">Sedang Berlangsung</span>
                @endif
            </p>
        </div>

        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('assignments.edit', $assignment->id) }}" class="btn btn-secondary btn-sm">
                Edit Tugas
            </a>
        </div>
    </div>

    <div class="card-body" style="background-color: #f8fafc; border-top: 1px solid var(--border-color); padding: 1rem 1.25rem;">
        <div style="font-size: 0.85rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.25rem;">PETUNJUK PENUGASAN:</div>
        <div style="font-size: 0.925rem; color: var(--text-main); white-space: pre-wrap; line-height: 1.6;">{{ $assignment->deskripsi }}</div>
    </div>
</div>

<!-- Statistik Pengumpulan -->
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div class="stat-label">Total Mahasiswa Terdaftar</div>
        <div class="stat-value">{{ $students->count() }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Sudah Mengumpulkan</div>
        <div class="stat-value" style="color: #10b981;">{{ $submissions->count() }}</div>
    </div>
    <div class="stat-card">
        <div class="stat-label">Belum Mengumpulkan</div>
        <div class="stat-value" style="color: #f59e0b;">{{ max(0, $students->count() - $submissions->count()) }}</div>
    </div>
</div>

<!-- Daftar Pengumpulan Mahasiswa -->
<div class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title" style="font-size: 1.1rem;">Rekapitulasi Pengumpulan Mahasiswa</h2>
            <p class="card-subtitle">Semua mahasiswa yang terdaftar di kelas mata kuliah ini</p>
        </div>
    </div>

    <div class="card-body" style="padding: 0;">
        @if($students->isEmpty())
            <div class="empty-state" style="padding: 3rem 1.5rem;">
                <p style="font-weight: 600; margin-bottom: 0.25rem;">Belum ada mahasiswa yang disetujui.</p>
                <p style="font-size: 0.875rem; color: var(--slate-500);">Mahasiswa perlu mengajukan pendaftaran dan disetujui untuk mengikuti kelas mata kuliah ini terlebih dahulu.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Mahasiswa</th>
                            <th style="width: 140px;">NIM</th>
                            <th style="width: 180px;">Status</th>
                            <th style="width: 180px;">Waktu Kirim</th>
                            <th>Jawaban Teks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($students as $index => $student)
                            @php
                                $sub = $submissions->get($student->id);
                            @endphp
                            <tr>
                                <td style="color: var(--text-muted); font-size: 0.875rem;">{{ $index + 1 }}</td>
                                <td>
                                    <div style="font-weight: 600; color: var(--text-main);">{{ $student->name }}</div>
                                    <div style="font-size: 0.8rem; color: var(--text-muted);">{{ $student->email }}</div>
                                </td>
                                <td>
                                    <span class="code-pill">{{ $student->nomor_induk ?? '-' }}</span>
                                </td>
                                <td>
                                    @if($sub)
                                        <span style="color: #059669; font-weight: 600; font-size: 0.8125rem;">Sudah Mengumpulkan</span>
                                    @else
                                        <span style="color: #d97706; font-weight: 600; font-size: 0.8125rem;">Belum Mengumpulkan</span>
                                    @endif
                                </td>
                                <td style="font-size: 0.85rem; color: var(--text-muted);">
                                    @if($sub)
                                        {{ $sub->submitted_at ? $sub->submitted_at->translatedFormat('d M Y, H:i') : $sub->updated_at->translatedFormat('d M Y, H:i') }} WIB
                                    @else
                                        &mdash;
                                    @endif
                                </td>
                                <td>
                                    @if($sub)
                                        <details style="cursor: pointer;">
                                            <summary style="font-weight: 600; color: var(--tel-red); font-size: 0.875rem; outline: none;">
                                                Lihat Jawaban ({{ strlen($sub->jawaban) }} Karakter)
                                            </summary>
                                            <div style="margin-top: 0.75rem; padding: 0.875rem; background: #f8fafc; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.9rem; line-height: 1.6; white-space: pre-wrap; max-height: 300px; overflow-y: auto;">{{ $sub->jawaban }}</div>
                                        </details>
                                    @else
                                        <span style="font-size: 0.85rem; color: var(--text-muted); font-style: italic;">Tidak ada kiriman</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
