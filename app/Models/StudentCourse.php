<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
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
])]
class StudentCourse extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
