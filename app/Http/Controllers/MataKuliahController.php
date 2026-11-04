<?php

namespace App\Http\Controllers;

use App\Models\MataKuliah;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MataKuliahController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $search = $request->input('search');

        if ($user->isDosen()) {
            // Dosen mengelola mata kuliah miliknya sendiri
            $query = MataKuliah::where('dosen_id', $user->id);
        } else {
            // Mahasiswa menjelajahi seluruh katalog mata kuliah yang tersedia di LMS
            $query = MataKuliah::query();
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode_mk', 'like', "%{$search}%")
                  ->orWhere('nama_mk', 'like', "%{$search}%")
                  ->orWhere('semester', 'like', "%{$search}%");
            });
        }

        $courses = $query->with('dosen')
            ->withCount([
                'approvedStudents as students_count',
                'materials',
                'assignments',
            ])
            ->latest()
            ->paginate(9)
            ->withQueryString();

        return view('courses.index', compact('courses'));
    }

    public function create(): View|RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        if (!$user->isDosen()) {
            abort(403, 'Akses ditolak. Pembuatan mata kuliah baru hanya dapat dilakukan oleh Dosen.');
        }

        return view('courses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        if (!$user->isDosen()) {
            abort(403, 'Akses ditolak. Pembuatan mata kuliah baru hanya dapat dilakukan oleh Dosen.');
        }

        // Validasi data mata kuliah sebelum disimpan
        $validated = $request->validate([
            'kode_mk' => ['required', 'string', 'max:20', 'unique:courses,kode_mk'],
            'nama_mk' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'sks' => ['required', 'integer', 'between:1,6'],
            'semester' => ['required', 'string', 'max:50'],
        ], [
            'kode_mk.required' => 'Kode mata kuliah wajib diisi.',
            'kode_mk.unique' => 'Kode mata kuliah sudah terdaftar. Gunakan kode lain.',
            'nama_mk.required' => 'Nama mata kuliah wajib diisi.',
            'sks.required' => 'Jumlah SKS wajib diisi.',
            'sks.between' => 'Jumlah SKS harus berada dalam rentang 1 hingga 6.',
            'semester.required' => 'Semester perkuliahan wajib diisi.',
        ]);

        $validated['dosen_id'] = $user->id;

        $course = MataKuliah::create($validated);

        return redirect()->route('courses.show', $course->id)
            ->with('success', "Mata kuliah \"{$course->nama_mk}\" ({$course->kode_mk}) berhasil dibuat.");
    }

    public function show(MataKuliah $course): View|RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // 1. Jika aktor adalah DOSEN
        if ($user->isDosen()) {
            if ($course->dosen_id !== $user->id) {
                abort(403, 'Akses ditolak. Anda bukan dosen pengampu mata kuliah ini.');
            }

            $course->load([
                'dosen',
                'materials',
                'assignments.submissions',
                'approvedStudents' => fn($q) => $q->orderBy('name'),
                'pendingStudents' => fn($q) => $q->orderBy('course_user.created_at', 'desc'),
            ]);

            return view('courses.show', compact('course'));
        }

        // kalo aktor adalah MAHASISWA
        $studentPivot = $course->students()->where('users.id', $user->id)->first();
        $enrollmentStatus = $studentPivot ? $studentPivot->pivot->status : 'none';

        if ($enrollmentStatus === 'approved') {
            // Mahasiswa resmi: liat materi dan tugas
            $course->load([
                'dosen',
                'materials',
                'assignments.submissions' => function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                },
                'approvedStudents' => fn($q) => $q->orderBy('name'),
            ]);
        } else {
            // Mahasiswa pending/rejected/belum daftar: HANYA liat ringkasan dosen & mata kuliah
            $course->load('dosen');
        }

        return view('courses.show', compact('course', 'enrollmentStatus'));
    }

    public function edit(MataKuliah $course): View|RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        if (!$user->isDosen() || $course->dosen_id !== $user->id) {
            abort(403, 'Akses ditolak. Anda hanya dapat mengubah mata kuliah yang Anda ampu.');
        }

        return view('courses.edit', compact('course'));
    }

    public function update(Request $request, MataKuliah $course): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        if (!$user->isDosen() || $course->dosen_id !== $user->id) {
            abort(403, 'Akses ditolak. Anda hanya dapat mengubah mata kuliah yang Anda ampu.');
        }

        $validated = $request->validate([
            'kode_mk' => ['required', 'string', 'max:20', Rule::unique('courses', 'kode_mk')->ignore($course->id)],
            'nama_mk' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
            'sks' => ['required', 'integer', 'between:1,6'],
            'semester' => ['required', 'string', 'max:50'],
        ], [
            'kode_mk.required' => 'Kode mata kuliah wajib diisi.',
            'kode_mk.unique' => 'Kode mata kuliah sudah digunakan pada mata kuliah lain.',
            'nama_mk.required' => 'Nama mata kuliah wajib diisi.',
            'sks.required' => 'Jumlah SKS wajib diisi.',
            'sks.between' => 'Jumlah SKS harus berada dalam rentang 1 hingga 6.',
            'semester.required' => 'Semester perkuliahan wajib diisi.',
        ]);

        $course->update($validated);

        return redirect()->route('courses.show', $course->id)
            ->with('success', "Informasi mata kuliah \"{$course->nama_mk}\" ({$course->kode_mk}) berhasil diperbarui.");
    }

    public function destroy(MataKuliah $course): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        if (!$user->isDosen() || $course->dosen_id !== $user->id) {
            abort(403, 'Akses ditolak. Anda hanya dapat menghapus mata kuliah yang Anda ampu.');
        }

        $nama = $course->nama_mk;
        $kode = $course->kode_mk;

        $course->delete();

        return redirect()->route('courses.index')
            ->with('success', "Mata kuliah \"{$nama}\" ({$kode}) berhasil dihapus dari sistem.");
    }

    public function enrollRequest(MataKuliah $course): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        if (!$user->isMahasiswa()) {
            abort(403, 'Akses ditolak. Pengajuan pendaftaran mata kuliah hanya dapat dilakukan oleh Mahasiswa.');
        }

        $existing = $course->students()->where('users.id', $user->id)->first();

        if ($existing) {
            if ($existing->pivot->status === 'approved') {
                return back()->with('info', 'Anda sudah terdaftar resmi pada mata kuliah ini.');
            }

            if ($existing->pivot->status === 'pending') {
                return back()->with('info', 'Permohonan pendaftaran Anda sudah dikirim dan sedang menunggu persetujuan Dosen.');
            }

            // Jika sebelumnya pernah ditolak (rejected), izinkan mengajukan ulang
            $course->students()->updateExistingPivot($user->id, [
                'status' => 'pending',
                'updated_at' => now(),
            ]);

            return back()->with('success', 'Permohonan pendaftaran ulang Anda berhasil diajukan kepada Dosen pengampu.');
        }

        $course->students()->attach($user->id, [
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Permohonan pendaftaran berhasil dikirim. Silakan tunggu persetujuan dari Dosen pengampu.');
    }

    public function approveStudent(MataKuliah $course, User $user): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $authUser = Auth::user();
        if (!$authUser->isDosen() || $course->dosen_id !== $authUser->id) {
            abort(403, 'Akses ditolak. Anda bukan dosen pengampu mata kuliah ini.');
        }

        $course->students()->updateExistingPivot($user->id, [
            'status' => 'approved',
            'updated_at' => now(),
        ]);

        return back()->with('success', "Pendaftaran mahasiswa {$user->name} ({$user->nomor_induk}) berhasil disetujui.");
    }

    public function rejectStudent(MataKuliah $course, User $user): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $authUser = Auth::user();
        if (!$authUser->isDosen() || $course->dosen_id !== $authUser->id) {
            abort(403, 'Akses ditolak. Anda bukan dosen pengampu mata kuliah ini.');
        }

        $course->students()->updateExistingPivot($user->id, [
            'status' => 'rejected',
            'updated_at' => now(),
        ]);

        return back()->with('success', "Permohonan pendaftaran mahasiswa {$user->name} ditolak.");
    }

    public function unenrollStudent(MataKuliah $course, User $user): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $authUser = Auth::user();
        if (!$authUser->isDosen() || $course->dosen_id !== $authUser->id) {
            abort(403, 'Akses ditolak. Anda bukan dosen pengampu mata kuliah ini.');
        }

        $course->students()->detach($user->id);

        return back()->with('success', "Mahasiswa {$user->name} berhasil dikeluarkan dari mata kuliah {$course->kode_mk}.");
    }
}