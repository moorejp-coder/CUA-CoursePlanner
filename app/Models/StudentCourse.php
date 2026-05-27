<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'course_code',
    'course_name',
    'requirement_category',
    'status',
    'grade',
    'semester_completed',
    'notes',
])]
#[Hidden(['id', 'user_id', 'created_at', 'updated_at'])]
class StudentCourse extends Model
{
    protected function casts(): array
    {
        return [
            // AES-256-CBC via APP_KEY — FERPA-protected academic records
            'grade' => 'encrypted',
            'notes' => 'encrypted',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
