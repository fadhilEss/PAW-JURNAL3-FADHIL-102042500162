<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'judul',
        'konten',
        'minggu_ke',
        'urutan',
    ];

    protected $casts = [
        'minggu_ke' => 'integer',
        'urutan' => 'integer',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class, 'course_id');
    }
}
