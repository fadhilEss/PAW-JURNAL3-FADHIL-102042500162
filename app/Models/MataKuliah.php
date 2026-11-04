<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataKuliah extends Model
{
    use HasFactory;

    /**
     * Nama tabel eksplisit untuk memetakan ke tabel courses.
     */
    protected $table = 'courses';

    /**
     * Atribut yang boleh diisi melalui form CRUD (sesuai kolom tabel courses).
     */
    protected $fillable = [
        'kode_mk',
        'nama_mk',
        'deskripsi',
        'sks',
        'semester',
        'dosen_id',
    ];

    protected $casts = [
        'sks' => 'integer',
    ];

    /**
     * Dosen pengampu mata kuliah.
     */
    public function dosen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    /**
     * Mahasiswa yang terdaftar atau mengajukan pada mata kuliah ini.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_user', 'course_id', 'user_id')
            ->withPivot('status')
            ->withTimestamps();
    }

    /**
     * Mahasiswa yang sudah disetujui (approved) oleh Dosen.
     */
    public function approvedStudents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_user', 'course_id', 'user_id')
            ->where('course_user.status', 'approved')
            ->withPivot('status')
            ->withTimestamps();
    }

    /**
     * Mahasiswa yang pengajuannya masih menunggu persetujuan (pending) Dosen.
     */
    public function pendingStudents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_user', 'course_id', 'user_id')
            ->where('course_user.status', 'pending')
            ->withPivot('status')
            ->withTimestamps();
    }

    /**
     * Memeriksa apakah mahasiswa tertentu berstatus approved di mata kuliah ini.
     */
    public function hasApprovedStudent(int $userId): bool
    {
        return $this->students()
            ->where('users.id', $userId)
            ->wherePivot('status', 'approved')
            ->exists();
    }

    /**
     * Mendapatkan status pendaftaran mahasiswa ('approved', 'pending', 'rejected', atau 'none').
     */
    public function getStudentEnrollmentStatus(int $userId): string
    {
        $student = $this->students()->where('users.id', $userId)->first();
        return $student ? ($student->pivot->status ?? 'pending') : 'none';
    }

    /**
     * Materi perkuliahan yang diurutkan berdasarkan minggu dan urutan.
     */
    public function materials(): HasMany
    {
        return $this->hasMany(Material::class, 'course_id')->orderBy('minggu_ke')->orderBy('urutan');
    }

    /**
     * Tugas perkuliahan yang diurutkan berdasarkan batas waktu.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'course_id')->orderBy('deadline');
    }
}