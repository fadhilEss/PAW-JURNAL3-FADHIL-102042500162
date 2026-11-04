<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'judul',
        'deskripsi',
        'deadline',
    ];

    protected $casts = [
        'deadline' => 'datetime',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class, 'course_id');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function isOverdue(): bool
    {
        return now()->greaterThan($this->deadline);
    }

    public function userSubmission(?int $userId): ?Submission
    {
        if (!$userId) {
            return null;
        }

        return $this->submissions()->where('user_id', $userId)->first();
    }
}
