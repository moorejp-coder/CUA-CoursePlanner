<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'full_name',
    'degree',
    'catalog_year',
    'admit_term',
    'expected_graduation',
    'years_at_cua',
    'specialization_1',
    'specialization_2',
    'specialization_3',
    'gpa',
    'credits_completed',
    'projected_standing',
    'math_placement',
    'language_placement',
    'semester_prompt_shown_at',
    'last_updated_at',
])]
#[Hidden(['id', 'user_id', 'created_at', 'updated_at', 'semester_prompt_shown_at', 'last_updated_at'])]
class StudentProfile extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'gpa' => 'decimal:2',
            'semester_prompt_shown_at' => 'datetime',
            'last_updated_at' => 'datetime',
            'credits_completed' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
