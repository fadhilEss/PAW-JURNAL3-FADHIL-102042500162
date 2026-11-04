@extends('layouts.app')

@section('title', $course->kode_mk . ' — ' . $course->nama_mk)

@section('content')
<div style="margin-bottom: 1.25rem;">
    <a href="{{ route('courses.index') }}" class="btn btn-secondary btn-sm">
        <span>&larr;</span> Kembali ke Katalog Mata Kuliah
    </a>
</div>

<!-- Course Header Card -->
<div class="card" style="margin-bottom: 1.75rem;">
    <div class="card-body" style="padding: 1.75rem;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem; margin-bottom: 0.75rem;">
            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
                <span style="font-weight: 700; color: var(--slate-800); font-size: 0.9375rem;">{{ $course->kode_mk }}</span>
                <span style="color: var(--slate-400);">&bull;</span>
                <span style="color: var(--slate-600);">{{ $course->sks }} SKS</span>
                <span style="color: var(--slate-400);">&bull;</span>
                <span style="color: var(--slate-600);">{{ $course->semester }}</span>
            </div>

            @if(Auth::user()->isDosen() && $course->dosen_id === Auth::id())
                <div style="display: flex; gap: 0.375rem;">
                    <a href="{{ route('courses.edit', $course->id) }}" class="btn btn-secondary btn-sm">
                        Edit Informasi Kelas
                    </a>
                    <form action="{{ route('courses.destroy', $course->id) }}" method="POST" onsubmit="return confirm('Hapus mata kuliah ini secara permanen?');" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">
                            Hapus
                        </button>
                    </form>
                </div>
            @endif
        </div>
        <h1 style="font-size: 1.5rem; font-weight: 800; color: var(--slate-900); margin-bottom: 0.75rem; line-height: 1.3;">
            {{ $course->nama_mk }}
        </h1>

        <p style="font-size: 0.9375rem; color: var(--slate-600); line-height: 1.6; margin-bottom: 1.25rem;">
            {{ $course->deskripsi ?: 'Belum ada deskripsi mata kuliah yang ditambahkan.' }}
        </p>

        <div style="padding-top: 1rem; border-top: 1px solid var(--slate-100); display: flex; flex-wrap: wrap; gap: 1.5rem; font-size: 0.8125rem; color: var(--slate-500);">
            <div><strong>Dosen Pengampu:</strong> {{ $course->dosen->name ?? 'Belum Ditentukan' }} ({{ $course->dosen->email ?? '-' }})</div>
            @if(Auth::user()->isDosen())
                <div><strong>Mahasiswa Aktif:</strong> {{ $course->approvedStudents->count() }} Orang</div>
                <div><strong>Menunggu Persetujuan:</strong> <span style="color: {{ $course->pendingStudents->count() > 0 ? 'var(--tel-red)' : 'inherit' }}; font-weight: 600;">{{ $course->pendingStudents->count() }} Pengajuan</span></div>
                <div><strong>Total Materi:</strong> {{ $course->materials->count() }} Bab</div>
                <div><strong>Total Tugas:</strong> {{ $course->assignments->count() }} Tugas</div>
            @elseif(isset($enrollmentStatus) && $enrollmentStatus === 'approved')
                <div><strong>Total Materi:</strong> {{ $course->materials->count() }} Bab</div>
                <div><strong>Total Tugas:</strong> {{ $course->assignments->count() }} Tugas</div>
            @endif
        </div>
    </div>
</div>

{{-- JIKA MAHASISWA BELUM APPROVED: HANYA TAMPILKAN STATUS PENDAFTARAN (KONTEN MATERI/TUGAS TIDAK DIMUAT) --}}
@if(Auth::user()->isMahasiswa() && isset($enrollmentStatus) && $enrollmentStatus !== 'approved')
    <div class="card" style="padding: 2.5rem 1.75rem; text-align: center;">
        @if($enrollmentStatus === 'pending')
            <div style="display: inline-flex; align-items: center; justify-content: center; width: 4rem; height: 4rem; background-color: #fef3c7; color: #d97706; border-radius: 50%; font-size: 1.75rem; margin-bottom: 1rem;">
                ⏳
            </div>
            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--slate-900); margin-bottom: 0.5rem;">
                Pendaftaran Anda Sedang Menunggu Persetujuan Dosen
            </h2>
            <p style="font-size: 0.9375rem; color: var(--slate-600); max-width: 600px; margin: 0 auto 1.5rem auto; line-height: 1.6;">
                Permohonan pendaftaran Anda pada mata kuliah <strong>{{ $course->nama_mk }}</strong> telah terkirim kepada Dosen pengampu <strong>{{ $course->dosen->name ?? 'Dosen' }}</strong>. Modul materi dan penugasan akan otomatis dapat diakses segera setelah pendaftaran Anda disetujui.
            </p>
            <div style="display: inline-block; background-color: var(--slate-50); border: 1px solid var(--slate-200); padding: 0.625rem 1.25rem; border-radius: 6px; font-size: 0.8125rem; color: var(--slate-600);">
                Status saat ini: <strong style="color: #d97706;">Pending (Menunggu Konfirmasi)</strong>
            </div>
        @elseif($enrollmentStatus === 'rejected')
            <div style="display: inline-flex; align-items: center; justify-content: center; width: 4rem; height: 4rem; background-color: #fee2e2; color: #dc2626; border-radius: 50%; font-size: 1.75rem; margin-bottom: 1rem;">
                ✕
            </div>
            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--slate-900); margin-bottom: 0.5rem;">
                Permohonan Pendaftaran Anda Ditolak
            </h2>
            <p style="font-size: 0.9375rem; color: var(--slate-600); max-width: 600px; margin: 0 auto 1.5rem auto; line-height: 1.6;">
                Permohonan pendaftaran Anda untuk kelas ini sebelumnya tidak disetujui oleh Dosen pengampu. Anda dapat mengajukan permohonan pendaftaran ulang di bawah ini jika terdapat kekeliruan.
            </p>
            <form action="{{ route('courses.enroll-request', $course->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary">
                    Ajukan Permohonan Pendaftaran Ulang
                </button>
            </form>
        @else
            <div style="display: inline-flex; align-items: center; justify-content: center; width: 4rem; height: 4rem; background-color: #f1f5f9; color: var(--slate-700); border-radius: 50%; font-size: 1.75rem; margin-bottom: 1rem;">
                🔒
            </div>
            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--slate-900); margin-bottom: 0.5rem;">
                Anda Belum Terdaftar pada Mata Kuliah Ini
            </h2>
            <p style="font-size: 0.9375rem; color: var(--slate-600); max-width: 600px; margin: 0 auto 1.5rem auto; line-height: 1.6;">
                Materi perkuliahan mingguan dan penugasan hanya dapat diakses oleh mahasiswa yang telah disetujui oleh Dosen pengampu <strong>{{ $course->dosen->name ?? 'Dosen' }}</strong>.
            </p>
            <form action="{{ route('courses.enroll-request', $course->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.5rem; font-size: 0.9375rem;">
                    + Ajukan Pendaftaran ke Kelas Ini
                </button>
            </form>
        @endif
    </div>

{{-- JIKA DOSEN ATAU MAHASISWA APPROVED: TAMPILKAN TABS LENGKAP --}}
@else
    {{-- Tabs Navigation --}}
    <div style="display: flex; gap: 0.5rem; border-bottom: 2px solid var(--slate-200); margin-bottom: 1.5rem;" id="courseTabs">
        <button type="button" class="tab-btn active" onclick="switchTab('tab-materials', this)">
            Materi Pembelajaran ({{ $course->materials->count() }})
        </button>
        <button type="button" class="tab-btn" onclick="switchTab('tab-assignments', this)">
            Tugas &amp; Evaluasi ({{ $course->assignments->count() }})
        </button>
        @if(Auth::user()->isDosen())
        <button type="button" class="tab-btn" onclick="switchTab('tab-students', this)">
            Persetujuan Mahasiswa 
            @if($course->pendingStudents->count() > 0)
                <span style="background-color: var(--tel-red); color: #ffffff; padding: 0.125rem 0.4375rem; border-radius: 9999px; font-size: 0.6875rem; margin-left: 0.25rem;">
                    {{ $course->pendingStudents->count() }}
                </span>
            @else
                ({{ $course->approvedStudents->count() }})
            @endif
        </button>
        @endif
    </div>

    <style>
        .tab-btn {
            background: transparent;
            border: none;
            border-bottom: 3px solid transparent;
            margin-bottom: -2px;
            padding: 0.75rem 1.25rem;
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--slate-600);
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .tab-btn:hover {
            color: var(--tel-red);
        }
        .tab-btn.active {
            color: var(--tel-red);
            border-bottom-color: var(--tel-red);
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
    </style>

    <!-- TAB 1: MATERI PEMBELAJARAN -->
    <div id="tab-materials" class="tab-content active">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <div>
                <h2 style="font-size: 1.1875rem; font-weight: 700; color: var(--slate-900);">Bahan Ajar &amp; Materi Perkuliahan</h2>
                <p style="font-size: 0.8125rem; color: var(--slate-500);">Pelajari materi ajar yang disusun terstruktur per minggu perkuliahan.</p>
            </div>

            @if(Auth::user()->isDosen() && $course->dosen_id === Auth::id())
                <a href="{{ route('materials.create', $course->id) }}" class="btn btn-primary btn-sm">
                    + Tambah Materi Baru
                </a>
            @endif
        </div>

        <div style="display: flex; flex-direction: column; gap: 1rem;">
            @forelse($course->materials as $material)
                <div class="card" style="margin-bottom: 0;">
                    <div class="card-header" style="background-color: var(--slate-50); padding: 1rem 1.25rem;">
                        <div style="display: flex; align-items: center; gap: 0.625rem;">
                            <span style="font-size: 0.8125rem; font-weight: 500; color: var(--slate-500);">Minggu ke-{{ $material->minggu_ke }} &bull;</span>
                            <h3 style="font-size: 1rem; font-weight: 700; color: var(--slate-900); margin: 0;">
                                {{ $material->judul }}
                            </h3>
                        </div>

                        @if(Auth::user()->isDosen() && $course->dosen_id === Auth::id())
                            <div style="display: flex; gap: 0.375rem;">
                                <a href="{{ route('materials.edit', $material->id) }}" class="btn btn-secondary btn-sm" title="Edit Materi">
                                    Edit
                                </a>
                                <form action="{{ route('materials.destroy', $material->id) }}" method="POST" onsubmit="return confirm('Hapus materi ini?');" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus Materi">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>

                    <div class="card-body" style="padding: 1.25rem;">
                        <div style="font-size: 0.9375rem; color: var(--slate-700); line-height: 1.6; white-space: pre-line;">
                            {{ $material->konten }}
                        </div>

                        <div style="margin-top: 1rem; padding-top: 0.75rem; border-top: 1px solid var(--slate-100); font-size: 0.75rem; color: var(--slate-400);">
                            Dipublikasikan pada: {{ $material->created_at ? $material->created_at->translatedFormat('d F Y, H:i') : '-' }} WIB
                        </div>
                    </div>
                </div>
            @empty
                <div class="card" style="padding: 2.5rem; text-align: center; color: var(--slate-500);">
                    <div style="font-weight: 700; font-size: 1rem; color: var(--slate-800);">Belum Ada Materi Diterbitkan</div>
                    <p style="font-size: 0.875rem; margin-top: 0.25rem;">
                        @if(Auth::user()->isDosen() && $course->dosen_id === Auth::id())
                            Silakan klik tombol <strong>"+ Tambah Materi Baru"</strong> di atas untuk menambahkan modul mingguan.
                        @else
                            Dosen pengampu belum mengunggah materi pembelajaran untuk mata kuliah ini.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- TAB 2: TUGAS & EVALUASI -->
    <div id="tab-assignments" class="tab-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
            <div>
                <h2 style="font-size: 1.1875rem; font-weight: 700; color: var(--slate-900);">Tugas &amp; Evaluasi Pembelajaran</h2>
                <p style="font-size: 0.8125rem; color: var(--slate-500);">Kumpulkan tugas terstruktur sebelum batas waktu (deadline) berakhir.</p>
            </div>

            @if(Auth::user()->isDosen() && $course->dosen_id === Auth::id())
                <a href="{{ route('assignments.create', $course->id) }}" class="btn btn-primary btn-sm">
                    + Buat Tugas Baru
                </a>
            @endif
        </div>

        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
            @forelse($course->assignments as $assignment)
                @php
                    $isOverdue = $assignment->isOverdue();
                    $submission = Auth::user()->isMahasiswa() ? $assignment->submissions->first() : null;
                @endphp
                <div class="card" style="margin-bottom: 0;">
                    <div class="card-header" style="background-color: var(--slate-50); padding: 1rem 1.25rem;">
                        <div>
                            <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                <span style="color: {{ $isOverdue ? '#dc2626' : '#059669' }}; font-weight: 600; font-size: 0.8125rem;">
                                    {{ $isOverdue ? 'Tenggat Berakhir' : 'Aktif' }}
                                </span>
                                <span style="font-size: 0.8125rem; font-weight: 600; color: var(--slate-600);">
                                    Batas Pengumpulan: <strong>{{ $assignment->deadline ? $assignment->deadline->translatedFormat('l, d F Y, H:i') : '-' }} WIB</strong>
                                </span>
                            </div>
                            <h3 style="font-size: 1.0625rem; font-weight: 700; color: var(--slate-900);">
                                {{ $assignment->judul }}
                            </h3>
                        </div>

                        @if(Auth::user()->isDosen() && $course->dosen_id === Auth::id())
                            <div style="display: flex; gap: 0.375rem; flex-wrap: wrap;">
                                <a href="{{ route('assignments.submissions', $assignment->id) }}" class="btn btn-primary btn-sm">
                                    Lihat Pengumpulan ({{ $assignment->submissions->count() }})
                                </a>
                                <a href="{{ route('assignments.edit', $assignment->id) }}" class="btn btn-secondary btn-sm" title="Edit Tugas">
                                    Edit
                                </a>
                                <form action="{{ route('assignments.destroy', $assignment->id) }}" method="POST" onsubmit="return confirm('Hapus tugas ini? Jawaban mahasiswa juga akan terhapus.');" style="display: inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Hapus Tugas">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>

                    <div class="card-body" style="padding: 1.25rem;">
                        <div style="font-size: 0.875rem; color: var(--slate-700); line-height: 1.6; white-space: pre-line; margin-bottom: 1.25rem;">
                            {{ $assignment->deskripsi }}
                        </div>

                        @if(Auth::user()->isMahasiswa())
                            <div style="background-color: var(--slate-50); border: 1px solid var(--slate-200); border-radius: var(--radius-md); padding: 1.25rem;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                                    <h4 style="font-size: 0.875rem; font-weight: 700; color: var(--slate-900);">
                                        Status Pengumpulan Jawaban Anda
                                    </h4>
                                    @if($submission)
                                        <span style="color: #059669; font-weight: 600; font-size: 0.8125rem;">
                                            Sudah Dikumpulkan ({{ $submission->submitted_at ? $submission->submitted_at->translatedFormat('d M Y, H:i') : '' }} WIB)
                                        </span>
                                    @else
                                        <span style="color: #d97706; font-weight: 600; font-size: 0.8125rem;">
                                            Belum Dikumpulkan
                                        </span>
                                    @endif
                                </div>

                                @if(!$isOverdue)
                                    <form action="{{ route('submissions.store', $assignment->id) }}" method="POST">
                                        @csrf
                                        <div class="form-group" style="margin-bottom: 0.75rem;">
                                            <label for="jawaban_{{ $assignment->id }}" class="form-label">
                                                Ketikkan Jawaban Teks Tugas <span class="required-indicator">*</span>
                                            </label>
                                            <textarea 
                                                name="jawaban" 
                                                id="jawaban_{{ $assignment->id }}" 
                                                rows="4" 
                                                class="form-control" 
                                                placeholder="Tuliskan jawaban Anda di sini (minimal 10 karakter)..." 
                                                required
                                            >{{ old('jawaban', $submission->jawaban ?? '') }}</textarea>
                                            <div class="form-help">Anda dapat memperbarui jawaban ini sebelum batas waktu (deadline) berakhir.</div>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            {{ $submission ? 'Perbarui Jawaban Tugas' : 'Kirim Jawaban Tugas' }}
                                        </button>
                                    </form>
                                @else
                                    <div style="font-size: 0.8125rem; color: #ef4444; margin-bottom: 0.5rem; font-weight: 600;">
                                        Batas waktu pengumpulan telah berakhir. Formulir pengumpulan ditutup.
                                    </div>
                                    @if($submission)
                                        <div style="font-size: 0.875rem; color: var(--slate-700); background-color: #ffffff; padding: 0.75rem; border-radius: var(--radius-sm); border: 1px solid var(--slate-200); white-space: pre-line;">
                                            {{ $submission->jawaban }}
                                        </div>
                                    @endif
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="card" style="padding: 2.5rem; text-align: center; color: var(--slate-500);">
                    <div style="font-weight: 700; font-size: 1rem; color: var(--slate-800);">Belum Ada Penugasan</div>
                    <p style="font-size: 0.875rem; margin-top: 0.25rem;">
                        @if(Auth::user()->isDosen() && $course->dosen_id === Auth::id())
                            Klik tombol <strong>"+ Buat Tugas Baru"</strong> untuk mempublikasikan evaluasi penugasan.
                        @else
                            Tidak ada tugas perkuliahan yang aktif saat ini.
                        @endif
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- TAB 3: PERSETUJUAN & MANAJEMEN MAHASISWA (DOSEN ONLY) --}}
    @if(Auth::user()->isDosen())
    <div id="tab-students" class="tab-content">
        <!-- Section 1: Mahasiswa Menunggu Persetujuan (Pending Approval) -->
        <div class="card" style="margin-bottom: 1.5rem;">
            <div class="card-header" style="background-color: var(--slate-50); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size: 1rem; font-weight: 700; color: var(--slate-900); margin: 0;">
                    Permohonan Pendaftaran Menunggu Persetujuan ({{ $course->pendingStudents->count() }})
                </h3>
                <span style="font-size: 0.75rem; color: var(--slate-500);">Perlu konfirmasi Dosen</span>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th>Email</th>
                            <th>Waktu Mengajukan</th>
                            <th style="text-align: right;">Keputusan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($course->pendingStudents as $student)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><code class="code-pill">{{ $student->nomor_induk ?: '-' }}</code></td>
                                <td style="font-weight: 600; color: var(--slate-900);">{{ $student->name }}</td>
                                <td>{{ $student->email }}</td>
                                <td style="font-size: 0.75rem; color: var(--slate-500);">
                                    {{ $student->pivot->created_at ? $student->pivot->created_at->translatedFormat('d M Y, H:i') : '-' }}
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 0.375rem;">
                                        <form action="{{ route('courses.enrollments.approve', ['course' => $course->id, 'user' => $student->id]) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-primary btn-sm" style="background-color: #059669; border-color: #059669;" title="Terima Mahasiswa">
                                                ✓ Terima
                                            </button>
                                        </form>
                                        <form action="{{ route('courses.enrollments.reject', ['course' => $course->id, 'user' => $student->id]) }}" method="POST" onsubmit="return confirm('Tolak permohonan mahasiswa {{ $student->name }}?');">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Tolak Permohonan">
                                                ✕ Tolak
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 1.5rem; color: var(--slate-500); font-size: 0.875rem;">
                                    Tidak ada antrean permohonan mahasiswa saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Section 2: Daftar Mahasiswa Aktif (Approved) -->
        <div class="card" style="margin-bottom: 0;">
            <div class="card-header" style="background-color: var(--slate-50);">
                <h3 style="font-size: 1rem; font-weight: 700; color: var(--slate-900); margin: 0;">
                    Mahasiswa Terdaftar Resmi ({{ $course->approvedStudents->count() }})
                </h3>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>NIM</th>
                            <th>Nama Mahasiswa</th>
                            <th>Email</th>
                            <th style="text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($course->approvedStudents as $student)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><code class="code-pill">{{ $student->nomor_induk ?: '-' }}</code></td>
                                <td style="font-weight: 600; color: var(--slate-900);">{{ $student->name }}</td>
                                <td>{{ $student->email }}</td>
                                <td style="text-align: right;">
                                    <form action="{{ route('courses.unenroll', ['course' => $course->id, 'user' => $student->id]) }}" method="POST" onsubmit="return confirm('Keluarkan mahasiswa {{ $student->name }} dari kelas ini?');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Keluarkan Mahasiswa">
                                            Keluarkan
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" style="text-align: center; padding: 2rem; color: var(--slate-500);">
                                    Belum ada mahasiswa yang disetujui pada mata kuliah ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
@endif

@push('scripts')
<script>
    function switchTab(tabId, buttonElement) {
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });

        const target = document.getElementById(tabId);
        if (target) {
            target.classList.add('active');
            buttonElement.classList.add('active');
        }
    }
</script>
@endpush
@endsection 