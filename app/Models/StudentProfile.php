<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // AES-256-CBC via APP_KEY — fields that are FERPA-protected or personal
            'full_name' => 'encrypted',
            'gpa' => 'encrypted',
            'math_placement' => 'encrypted',
            'language_placement' => 'encrypted',
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
