<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Widens columns that will store AES-256-CBC ciphertext via Laravel's `encrypted` cast.
// Encrypted values are base64-encoded JSON (~180-350 chars) so varchar(255) is not safe,
// and decimal/float types obviously cannot hold string ciphertext.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->text('full_name')->change();
            $table->text('gpa')->nullable()->change();
            $table->text('math_placement')->nullable()->change();
            $table->text('language_placement')->nullable()->change();
        });

        Schema::table('student_courses', function (Blueprint $table) {
            $table->text('grade')->nullable()->change();
            // `notes` is already TEXT — no column change needed, cast added at model layer only
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('full_name')->change();
            $table->decimal('gpa', 3, 2)->nullable()->change();
            $table->string('math_placement')->nullable()->change();
            $table->string('language_placement')->nullable()->change();
        });

        Schema::table('student_courses', function (Blueprint $table) {
            $table->string('grade', 5)->nullable()->change();
        });
    }
};
