<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\MataKuliah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function create(MataKuliah $course): View|RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!Auth::user()->isDosen() || $course->dosen_id !== Auth::id()) {
            abort(403, 'Akses ditolak. Anda bukan dosen pengampu mata kuliah ini.');
        }

        return view('assignments.create', compact('course'));
    }

    public function store(Request $request, MataKuliah $course): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!Auth::user()->isDosen() || $course->dosen_id !== Auth::id()) {
            abort(403, 'Akses ditolak. Anda bukan dosen pengampu mata kuliah ini.');
        }

        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string', 'max:10000'],
            'deadline' => ['required', 'date', 'after:now'],
        ], [
            'judul.required' => 'Judul tugas wajib diisi.',
            'judul.max' => 'Judul tugas maksimal 255 karakter.',
            'deskripsi.required' => 'Petunjuk dan deskripsi penugasan wajib diisi.',
            'deadline.required' => 'Batas waktu (deadline) pengumpulan tugas wajib ditentukan.',
            'deadline.after' => 'Batas waktu (deadline) harus berada di waktu mendatang.',
        ]);

        $course->assignments()->create($validated);

        return redirect()->route('courses.show', $course->id)
            ->with('success', "Tugas \"{$validated['judul']}\" berhasil dibuat untuk mata kuliah ini.");
    }

    public function edit(Assignment $assignment): View|RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!Auth::user()->isDosen() || $assignment->course->dosen_id !== Auth::id()) {
            abort(403, 'Akses ditolak. Anda bukan dosen pengampu mata kuliah tugas ini.');
        }

        return view('assignments.edit', compact('assignment'));
    }

    public function update(Request $request, Assignment $assignment): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!Auth::user()->isDosen() || $assignment->course->dosen_id !== Auth::id()) {
            abort(403, 'Akses ditolak. Anda bukan dosen pengampu mata kuliah tugas ini.');
        }

        $validated = $request->validate([
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string', 'max:10000'],
            'deadline' => ['required', 'date'],
        ], [
            'judul.required' => 'Judul tugas wajib diisi.',
            'judul.max' => 'Judul tugas maksimal 255 karakter.',
            'deskripsi.required' => 'Petunjuk dan deskripsi penugasan wajib diisi.',
            'deadline.required' => 'Batas waktu (deadline) pengumpulan tugas wajib ditentukan.',
        ]);

        $assignment->update($validated);

        return redirect()->route('courses.show', $assignment->course_id)
            ->with('success', "Tugas \"{$assignment->judul}\" berhasil diperbarui.");
    }

    public function destroy(Assignment $assignment): RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!Auth::user()->isDosen() || $assignment->course->dosen_id !== Auth::id()) {
            abort(403, 'Akses ditolak. Anda bukan dosen pengampu mata kuliah tugas ini.');
        }

        $courseId = $assignment->course_id;
        $judul = $assignment->judul;

        $assignment->delete();

        return redirect()->route('courses.show', $courseId)
            ->with('success', "Tugas \"{$judul}\" berhasil dihapus.");
    }

    public function submissions(Assignment $assignment): View|RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!Auth::user()->isDosen() || $assignment->course->dosen_id !== Auth::id()) {
            abort(403, 'Akses ditolak. Anda bukan dosen pengampu mata kuliah tugas ini.');
        }

        $assignment->load(['course.approvedStudents', 'submissions.user']);
        $students = $assignment->course->approvedStudents;
        $submissions = $assignment->submissions->keyBy('user_id');

        return view('assignments.submissions', compact('assignment', 'students', 'submissions'));
    }
}
