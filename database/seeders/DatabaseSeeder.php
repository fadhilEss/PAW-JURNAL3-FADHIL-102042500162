<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\MataKuliah;
use App\Models\Material;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Akun Pengguna LMS (1 Dosen & 1 Mahasiswa)
        $dosen = User::updateOrCreate(
            ['email' => 'dosen@telkomuniversity.ac.id'],
            [
                'name' => 'Dr. Budi Santoso, M.Kom.',
                'password' => Hash::make('password123'),
                'role' => 'dosen',
                'nomor_induk' => '14850001',
            ]
        );

        $mahasiswa = User::updateOrCreate(
            ['email' => 'mahasiswa@student.telkomuniversity.ac.id'],
            [
                'name' => 'Andi Pratama',
                'password' => Hash::make('password123'),
                'role' => 'mahasiswa',
                'nomor_induk' => '1302220001',
            ]
        );

        // Data awal mata kuliah milik dosen
        $course = MataKuliah::updateOrCreate(
            ['kode_mk' => 'CII2D3'],
            [
                'nama_mk' => 'Pemrograman Web Lanjut',
                'deskripsi' => 'Pembelajaran pengembangan aplikasi web berbasis framework Laravel: routing, controller, Eloquent ORM, Blade, autentikasi, dan middleware.',
                'sks' => 3,
                'semester' => 'Ganjil 2026/2027',
                'dosen_id' => $dosen->id,
            ]
        );

        // Mahasiswa langsung berstatus approved agar bisa melihat materi & tugas
        $course->students()->syncWithoutDetaching([
            $mahasiswa->id => ['status' => 'approved'],
        ]);

        // Data awal materi pembelajaran
        Material::updateOrCreate(
            ['course_id' => $course->id, 'judul' => 'Pengenalan Laravel & MVC'],
            [
                'konten' => "Pokok bahasan:\n1. Konsep MVC (Model, View, Controller)\n2. Struktur direktori proyek Laravel\n3. Routing dasar dan Blade template",
                'minggu_ke' => 1,
                'urutan' => 1,
            ]
        );

        Material::updateOrCreate(
            ['course_id' => $course->id, 'judul' => 'Migration, Model, dan Eloquent'],
            [
                'konten' => "Pokok bahasan:\n1. Membuat skema database dengan migration\n2. Model, fillable, dan relasi Eloquent\n3. Seeder untuk data awal",
                'minggu_ke' => 2,
                'urutan' => 1,
            ]
        );

        // Data awal tugas
        Assignment::updateOrCreate(
            ['course_id' => $course->id, 'judul' => 'Tugas 01 - Analisis Arsitektur MVC'],
            [
                'deskripsi' => 'Jelaskan alur sebuah request di Laravel mulai dari route hingga view dan berikan satu contoh kode controller sederhana.',
                'deadline' => now()->addDays(14)->setTime(23, 59),
            ]
        );
    }
}