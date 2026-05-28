<?php

namespace Database\Factories;

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StudentProfile>
 */
class StudentProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'full_name' => $this->faker->name(),
            'degree' => 'bsba',
            'catalog_year' => 'post_2024',
            'admit_term' => 'Fall 2025',
            'expected_graduation' => 'Spring 2029',
            'years_at_cua' => null,
            'specialization_1' => null,
            'specialization_2' => null,
            'specialization_3' => null,
            'gpa' => null,
            'credits_completed' => 30,
            'projected_standing' => 'Sophomore',
            'math_placement' => null,
            'language_placement' => null,
            'semester_prompt_shown_at' => null,
            'last_updated_at' => now(),
        ];
    }
}
