<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\MataKuliah;
use App\Models\Material;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        $announcements = [
            [
                'judul' => 'Jadwal Unggah Materi & Evaluasi Pembelajaran Semester Ganjil 2026/2027',
                'tanggal' => '16 Oktober 2026',
                'kategori' => 'Akademik',
                'isi' => 'Dosen pengampu diharapkan melengkapi bahan ajar mingguan dan penugasan terstruktur sesuai Rencana Pembelajaran Semester (RPS).',
            ],
            [
                'judul' => 'Pemeliharaan Rutin Server Pembelajaran Daring (CeLOE Cloud)',
                'tanggal' => '14 Oktober 2026',
                'kategori' => 'Sistem',
                'isi' => 'Layanan sinkronisasi materi akan menjalani pemeliharaan berkala setiap hari Minggu pukul 01.00 - 04.00 WIB.',
            ],
            [
                'judul' => 'Panduan Pengumpulan Tugas Teks Berbasis Capaian Pembelajaran',
                'tanggal' => '10 Oktober 2026',
                'kategori' => 'Kemahasiswaan',
                'isi' => 'Mahasiswa diimbau mengumpulkan jawaban tugas sebelum batas waktu deadline. Sistem akan otomatis menutup pengumpulan saat tenggat berakhir.',
            ],
        ];

        if ($user->isDosen()) {
            $courses = MataKuliah::where('dosen_id', $user->id)
                ->withCount([
                    'approvedStudents as students_count',
                    'materials',
                    'assignments',
                ])
                ->latest()
                ->get();

            $upcomingAssignments = Assignment::whereHas('course', function ($q) use ($user) {
                $q->where('dosen_id', $user->id);
            })
            ->with('course')
            ->withCount('submissions')
            ->orderBy('deadline')
            ->take(5)
            ->get();

            $pendingApprovals = \Illuminate\Support\Facades\DB::table('course_user')
                ->join('courses', 'course_user.course_id', '=', 'courses.id')
                ->where('courses.dosen_id', $user->id)
                ->where('course_user.status', 'pending')
                ->count();

            $stats = [
                'total_courses' => $courses->count(),
                'total_students' => $courses->sum('students_count'),
                'total_materials' => $courses->sum('materials_count'),
                'total_assignments' => $courses->sum('assignments_count'),
                'pending_approvals' => $pendingApprovals,
            ];

            return view('dashboard', compact('stats', 'courses', 'upcomingAssignments', 'announcements'));
        }

        // Mahasiswa role
        $courses = $user->enrolledCourses()
            ->with('dosen')
            ->withCount(['materials', 'assignments'])
            ->get();

        $courseIds = $courses->pluck('id');

        $upcomingAssignments = Assignment::whereIn('course_id', $courseIds)
            ->with(['course', 'submissions' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }])
            ->orderBy('deadline')
            ->take(6)
            ->get();

        $stats = [
            'enrolled_courses' => $courses->count(),
            'total_materials' => $courses->sum('materials_count'),
            'total_assignments' => $courses->sum('assignments_count'),
            'submitted_count' => Submission::where('user_id', $user->id)->count(),
        ];

        return view('dashboard', compact('stats', 'courses', 'upcomingAssignments', 'announcements'));
    }
}
