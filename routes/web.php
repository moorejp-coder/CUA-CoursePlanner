<?php

use App\Http\Controllers\AcademicProfileController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('chat');
    }

    return view('welcome');
});

// Public confirmation page shown after successful account deletion.
// No auth required — the user is logged out by the time they land here.
Route::get('/account-deleted', function () {
    return view('auth.account-deleted', [
        'courses' => (int) request()->query('courses', 0),
        'profile' => (int) request()->query('profile', 0),
    ]);
})->name('account.deleted');

Route::get('/dashboard', function () {
    return redirect()->route('chat');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/chat', [ChatController::class, 'index'])->name('chat');
    Route::post('/api/chat', [ChatController::class, 'message'])->middleware('throttle:60,1')->name('chat.message');
    // Academic profile
    Route::get('/profile/academic', [AcademicProfileController::class, 'editAcademic'])->name('profile.academic');
    Route::get('/profile/academic/edit', [AcademicProfileController::class, 'editAcademic'])->name('profile.academic.edit');
    Route::post('/profile/academic/edit', [AcademicProfileController::class, 'updateAcademic'])->name('profile.academic.update')->middleware('throttle:form-submissions');
    Route::post('/profile/academic/courses', [AcademicProfileController::class, 'updateCourses'])->name('profile.academic.courses')->middleware('throttle:form-submissions');
    Route::post('/api/profile/suggest-update', [AcademicProfileController::class, 'suggestUpdate'])->middleware('throttle:60,1')->name('profile.suggest-update');
    Route::post('/api/profile/field-update', [AcademicProfileController::class, 'updateField'])->middleware('throttle:60,1')->name('profile.field-update');
    Route::post('/api/profile/dismiss-prompt', [AcademicProfileController::class, 'dismissSemesterPrompt'])->middleware('throttle:10,1')->name('profile.dismiss-prompt');

    // Onboarding wizard
    Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding');
    Route::get('/onboarding/step/{step}', [OnboardingController::class, 'show'])->name('onboarding.step')->where('step', '[1-6]');
    Route::post('/onboarding/step/{step}', [OnboardingController::class, 'save'])->name('onboarding.save')->where('step', '[1-6]')->middleware('throttle:form-submissions');
    Route::get('/onboarding/step-accounting', [OnboardingController::class, 'showAccounting'])->name('onboarding.step.accounting');
    Route::post('/onboarding/step-accounting', [OnboardingController::class, 'saveAccounting'])->name('onboarding.save.accounting')->middleware('throttle:form-submissions');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update')->middleware('throttle:form-submissions');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy')->middleware('throttle:form-submissions');
});

// ── Admin Routes (disabled — see FUTUREUPDATES.md) ───────────────────────────
// Route::middleware(['auth', 'verified', 'dean'])->prefix('admin')->name('admin.')->group(function () {
//     Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
//     Route::get('/students', [AdminController::class, 'students'])->name('students');
//     Route::get('/students/export', [AdminController::class, 'exportStudentsCsv'])->name('students.export');
//     Route::get('/students/{user}', [AdminController::class, 'studentProfile'])->name('students.show');
//     Route::get('/requirements', [AdminController::class, 'requirements'])->name('requirements');
//     Route::post('/requirements', [AdminController::class, 'saveRequirements'])->name('requirements.save');
//     Route::get('/system-prompt', [AdminController::class, 'systemPrompt'])->name('system-prompt');
//     Route::post('/system-prompt', [AdminController::class, 'saveSystemPrompt'])->name('system-prompt.save');
//     Route::post('/system-prompt/restore', [AdminController::class, 'restoreSystemPrompt'])->name('system-prompt.restore');
//     Route::get('/users', [AdminController::class, 'users'])->name('users');
//     Route::post('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.role');
//     Route::get('/stats', [AdminController::class, 'stats'])->name('stats');
// });

require __DIR__.'/auth.php';
