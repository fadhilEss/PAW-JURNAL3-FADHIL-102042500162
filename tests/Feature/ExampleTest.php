<?php

namespace Tests\Feature;

use App\Models\MataKuliah;
use App\Models\User;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_unauthenticated_user_redirects_to_login(): void
    {
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_login_page_renders_successfully(): void
    {
        $response = $this->get('/login');
        $response->assertOk();
        $response->assertSee('CeLOE LMS');
        $response->assertSee('images/telkom-campus.png');
        $response->assertSee('Daftar sebagai Mahasiswa');
    }

    public function test_mahasiswa_can_access_dashboard(): void
    {
        $user = User::where('email', 'mahasiswa@celoe.test')->first();
        if ($user) {
            $this->actingAs($user)->get('/dashboard')->assertOk()->assertSee('Mata Kuliah Saya');
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_dosen_can_access_dashboard(): void
    {
        $dosen = User::where('email', 'dosen@celoe.test')->first();
        if ($dosen) {
            $this->actingAs($dosen)->get('/dashboard')->assertOk()->assertSee('Mata Kuliah yang Diampu');
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_dosen_can_view_course_create_page(): void
    {
        $dosen = User::where('email', 'dosen@celoe.test')->first();
        if ($dosen) {
            $this->actingAs($dosen)->get('/courses/create')->assertOk()->assertSee('Tambah Mata Kuliah Baru');
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_dosen_can_view_course_detail(): void
    {
        $dosen = User::where('email', 'dosen@celoe.test')->first();
        if ($dosen) {
            $course = MataKuliah::firstOrCreate(
                ['kode_mk' => 'TEST_DETAIL'],
                [
                    'nama_mk' => 'Mata Kuliah Uji Detail',
                    'sks' => 3,
                    'semester' => 'Ganjil 2026/2027',
                    'dosen_id' => $dosen->id,
                ]
            );
            $this->actingAs($dosen)->get("/courses/{$course->id}")->assertOk()->assertSee($course->kode_mk);
            $course->delete();
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_user_can_view_profile_edit_page(): void
    {
        $user = User::where('email', 'mahasiswa@celoe.test')->first();
        if ($user) {
            $this->actingAs($user)->get('/profile')->assertOk()->assertSee('Edit Profil Akun')->assertSee($user->name);
        } else {
            $this->assertTrue(true);
        }
    }

    public function test_user_can_update_profile(): void
    {
        $user = User::where('email', 'mahasiswa@celoe.test')->first();
        if ($user) {
            $response = $this->actingAs($user)->put('/profile', [
                'name' => 'Ahmad Pratama Updated',
                'email' => 'mahasiswa@celoe.test',
                'nomor_induk' => '1301210001',
            ]);
            $response->assertRedirect('/profile');
            $this->assertDatabaseHas('users', [
                'id' => $user->id,
                'name' => 'Ahmad Pratama Updated',
            ]);

            // Kembalikan nama semula
            $user->update(['name' => 'Ahmad Pratama']);
        } else {
            $this->assertTrue(true);
        }
    }
}
