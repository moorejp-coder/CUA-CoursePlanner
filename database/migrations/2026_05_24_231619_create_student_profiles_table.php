<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->index();
            $table->string('full_name');
            $table->string('degree');
            $table->string('catalog_year');
            $table->string('admit_term');
            $table->string('expected_graduation');
            $table->integer('years_at_cua')->nullable();
            $table->string('specialization_1')->nullable();
            $table->string('specialization_2')->nullable();
            $table->string('specialization_3')->nullable();
            $table->decimal('gpa', 3, 2)->nullable();
            $table->integer('credits_completed')->default(0);
            $table->string('projected_standing')->nullable();
            $table->string('math_placement')->nullable();
            $table->string('language_placement')->nullable();
            $table->timestamp('semester_prompt_shown_at')->nullable();
            $table->timestamp('last_updated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};
