<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'nomor_induk')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('nomor_induk', 30)->nullable()->after('email');
            });
        }

        // TODO 2: Lengkapi kolom tabel mata kuliah
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            
            // gunakan tipe string untuk data teks pendek.
            // Contoh pola: // $table->string('nama_kolom');
            // salah satu kolom harus memiliki nilai unik.
            // hubungkan dosen pengampu ke tabel users.
            $table->string('kode_mk', 20)->unique();
            $table->string('nama_mk');
            $table->text('deskripsi')->nullable();
            $table->unsignedTinyInteger('sks')->default(3);
            $table->string('semester', 50);
            $table->foreignId('dosen_id')->constrained('users')->cascadeOnDelete();

            $table->timestamps();
        });

        // Tabel Enrollment Mahasiswa
        Schema::create('course_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unique(['course_id', 'user_id']);
            $table->timestamps();
        });

        // Tabel Materi Kuliah
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('judul');
            $table->longText('konten');
            $table->unsignedTinyInteger('minggu_ke')->default(1);
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });

        // Tabel Tugas Kuliah
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('judul');
            $table->text('deskripsi');
            $table->dateTime('deadline');
            $table->timestamps();
        });

        // Tabel Pengumpulan Jawaban Teks Mahasiswa
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->longText('jawaban');
            $table->dateTime('submitted_at');
            $table->unique(['assignment_id', 'user_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Hanya menghapus tabel CeLOE baru dalam urutan relasi terbalik
        Schema::dropIfExists('submissions');
        Schema::dropIfExists('assignments');
        Schema::dropIfExists('materials');
        Schema::dropIfExists('course_user');
        Schema::dropIfExists('courses');

        if (Schema::hasColumn('users', 'nomor_induk')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('nomor_induk');
            });
        }
    }
};
