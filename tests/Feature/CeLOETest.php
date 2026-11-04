<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\MataKuliah;
use App\Models\Material;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CeLOETest extends TestCase
{
    public function test_mahasiswa_forbidden_from_course_create(): void
    {
        $mahasiswa = User::where('role', 'mahasiswa')->first();
        if (!$mahasiswa) {
            $this->markTestSkipped('Demo user mahasiswa not found');
        }

        $response = $this->actingAs($mahasiswa)->get('/courses/create');
        $response->assertStatus(403);
    }

    public function test_dosen_can_create_course(): void
    {
        $dosen = User::where('role', 'dosen')->first();
        if (!$dosen) {
            $this->markTestSkipped('Dosen not found');
        }

        $kodeMk = 'TEST' . rand(100, 999);
        $response = $this->actingAs($dosen)->post('/courses', [
            'kode_mk' => $kodeMk,
            'nama_mk' => 'Mata Kuliah Uji Mandiri Dosen',
            'sks' => 3,
            'semester' => 'Ganjil 2026/2027',
            'deskripsi' => 'Uji coba pembuatan mata kuliah langsung oleh dosen.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('courses', [
            'kode_mk' => $kodeMk,
            'dosen_id' => $dosen->id,
        ]);

        // Cleanup
        MataKuliah::where('kode_mk', $kodeMk)->delete();
    }

    public function test_dosen_cannot_manage_other_dosen_course(): void
    {
        $dosen1 = User::where('role', 'dosen')->first();
        if (!$dosen1) {
            $this->markTestSkipped('Dosen 1 not found');
        }

        $course1 = MataKuliah::firstOrCreate(
            ['kode_mk' => 'TEST_OWNER'],
            [
                'nama_mk' => 'Kelas Dosen 1',
                'sks' => 3,
                'semester' => 'Ganjil 2026/2027',
                'dosen_id' => $dosen1->id,
            ]
        );

        // Buat dosen pembanding
        $dosen2 = User::updateOrCreate(
            ['email' => 'dosen_rival@celoe.test'],
            [
                'name' => 'Dosen Rival, M.Kom.',
                'password' => Hash::make('password123'),
                'role' => 'dosen',
                'nomor_induk' => '9988776655',
            ]
        );

        // Dosen 2 mencoba mengakses form edit mata kuliah milik Dosen 1
        $response = $this->actingAs($dosen2)->get("/courses/{$course1->id}/edit");
        $response->assertStatus(403);

        // Dosen 2 mencoba membuat materi di kelas Dosen 1
        $responseMat = $this->actingAs($dosen2)->get("/courses/{$course1->id}/materials/create");
        $responseMat->assertStatus(403);

        // Cleanup
        $course1->delete();
        $dosen2->delete();
    }

    public function test_mahasiswa_can_register_new_account(): void
    {
        $uniqueEmail = 'mhs_baru_' . rand(1000, 9999) . '@student.telkomuniversity.ac.id';
        $uniqueNim = '130121' . rand(1000, 9999);

        $response = $this->post('/register', [
            'name' => 'Mahasiswa Baru Mandiri',
            'email' => $uniqueEmail,
            'nomor_induk' => $uniqueNim,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', [
            'email' => $uniqueEmail,
            'nomor_induk' => $uniqueNim,
            'role' => 'mahasiswa',
        ]);

        // Cleanup
        User::where('email', $uniqueEmail)->delete();
    }

    public function test_mahasiswa_can_request_enrollment_and_dosen_can_approve(): void
    {
        $dosen = User::where('role', 'dosen')->first();
        $course = MataKuliah::firstOrCreate(
            ['kode_mk' => 'TEST_ENROLL'],
            [
                'nama_mk' => 'Kelas Uji Enroll',
                'sks' => 3,
                'semester' => 'Ganjil 2026/2027',
                'dosen_id' => $dosen->id,
            ]
        );

        // Buat mahasiswa penguji
        $testMahasiswa = User::updateOrCreate(
            ['email' => 'mhs_test_enroll@celoe.test'],
            [
                'name' => 'Siswa Penguji Enroll',
                'password' => Hash::make('password123'),
                'role' => 'mahasiswa',
                'nomor_induk' => '1301999999',
            ]
        );

        // Pastikan tidak ada enrollment awal
        $course->students()->detach($testMahasiswa->id);

        // 1. Mahasiswa mengajukan pendaftaran
        $responseReq = $this->actingAs($testMahasiswa)->post("/courses/{$course->id}/enroll-request");
        $responseReq->assertRedirect();
        $this->assertDatabaseHas('course_user', [
            'course_id' => $course->id,
            'user_id' => $testMahasiswa->id,
            'status' => 'pending',
        ]);

        // 2. Mahasiswa berstatus pending diblokir dari submission tugas
        $assignment = Assignment::firstOrCreate(
            ['course_id' => $course->id, 'judul' => 'Tugas Uji Enroll'],
            ['deskripsi' => 'Uji tugas', 'deadline' => now()->addDays(5)]
        );

        $responseSubmitPending = $this->actingAs($testMahasiswa)->post("/assignments/{$assignment->id}/submit", [
            'jawaban' => 'Jawaban dari mahasiswa yang statusnya masih pending.',
        ]);
        $responseSubmitPending->assertStatus(403);

        // 3. Dosen menyetujui pendaftaran
        $responseApprove = $this->actingAs($dosen)->patch("/courses/{$course->id}/enrollments/{$testMahasiswa->id}/approve");
        $responseApprove->assertRedirect();
        $this->assertDatabaseHas('course_user', [
            'course_id' => $course->id,
            'user_id' => $testMahasiswa->id,
            'status' => 'approved',
        ]);

        // 4. Mahasiswa yang sudah approved berhasil submit tugas
        $responseSubmitApproved = $this->actingAs($testMahasiswa)->post("/assignments/{$assignment->id}/submit", [
            'jawaban' => 'Jawaban setelah berhasil disetujui oleh dosen pengampu kelas.',
        ]);
        $responseSubmitApproved->assertRedirect();
        $this->assertDatabaseHas('submissions', [
            'assignment_id' => $assignment->id,
            'user_id' => $testMahasiswa->id,
        ]);

        // Cleanup
        $course->students()->detach($testMahasiswa->id);
        $assignment->submissions()->delete();
        $assignment->delete();
        $course->delete();
        $testMahasiswa->delete();
    }

    public function test_unauthenticated_requests_redirect_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/profile')->assertRedirect('/login');
        $this->get('/courses')->assertRedirect('/login');
        $this->get('/courses/create')->assertRedirect('/login');
    }

    public function test_pending_mahasiswa_cannot_view_materials_content_on_course_page(): void
    {
        $dosen = User::where('role', 'dosen')->first();
        $course = MataKuliah::firstOrCreate(
            ['kode_mk' => 'TEST_MAT'],
            [
                'nama_mk' => 'Kelas Uji Materi',
                'sks' => 3,
                'semester' => 'Ganjil 2026/2027',
                'dosen_id' => $dosen->id,
            ]
        );

        $firstMaterial = Material::firstOrCreate(
            ['course_id' => $course->id, 'judul' => 'Materi Uji'],
            ['konten' => 'Konten Rahasia Materi Uji', 'minggu_ke' => 1, 'urutan' => 1]
        );

        $pendingStudent = User::updateOrCreate(
            ['email' => 'mhs_pending_view@celoe.test'],
            [
                'name' => 'Siswa Pending View',
                'password' => Hash::make('password123'),
                'role' => 'mahasiswa',
                'nomor_induk' => '1301888888',
            ]
        );

        $course->students()->syncWithoutDetaching([$pendingStudent->id => ['status' => 'pending']]);

        $response = $this->actingAs($pendingStudent)->get("/courses/{$course->id}");
        $response->assertOk();
        $response->assertSee('Pendaftaran Anda Sedang Menunggu Persetujuan Dosen');
        $response->assertDontSee($firstMaterial->konten);

        // Cleanup
        $course->students()->detach($pendingStudent->id);
        $firstMaterial->delete();
        $course->delete();
        $pendingStudent->delete();
    }

    public function test_dosen_can_reject_student_enrollment(): void
    {
        $dosen = User::where('role', 'dosen')->first();
        $course = MataKuliah::firstOrCreate(
            ['kode_mk' => 'TEST_REJECT'],
            [
                'nama_mk' => 'Kelas Uji Reject',
                'sks' => 3,
                'semester' => 'Ganjil 2026/2027',
                'dosen_id' => $dosen->id,
            ]
        );

        $rejectStudent = User::updateOrCreate(
            ['email' => 'mhs_reject_test@celoe.test'],
            [
                'name' => 'Siswa Reject Test',
                'password' => Hash::make('password123'),
                'role' => 'mahasiswa',
                'nomor_induk' => '1301777777',
            ]
        );

        $course->students()->syncWithoutDetaching([$rejectStudent->id => ['status' => 'pending']]);

        $response = $this->actingAs($dosen)->patch("/courses/{$course->id}/enrollments/{$rejectStudent->id}/reject");
        $response->assertRedirect();
        $this->assertDatabaseHas('course_user', [
            'course_id' => $course->id,
            'user_id' => $rejectStudent->id,
            'status' => 'rejected',
        ]);

        $assignment = Assignment::firstOrCreate(
            ['course_id' => $course->id, 'judul' => 'Tugas Uji Reject'],
            ['deskripsi' => 'Uji tugas reject', 'deadline' => now()->addDays(5)]
        );

        $responseSubmit = $this->actingAs($rejectStudent)->post("/assignments/{$assignment->id}/submit", [
            'jawaban' => 'Jawaban dari mahasiswa yang sudah ditolak.',
        ]);
        $responseSubmit->assertStatus(403);

        // Cleanup
        $course->students()->detach($rejectStudent->id);
        $assignment->delete();
        $course->delete();
        $rejectStudent->delete();
    }
}
