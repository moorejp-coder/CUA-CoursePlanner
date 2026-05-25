<?php

use App\Http\Controllers\AcademicProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UploadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('chat')
        : view('welcome');
});

Route::get('/dashboard', function () {
    return redirect()->route('chat');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/chat', [ChatController::class, 'index'])->name('chat');
    Route::post('/api/chat', [ChatController::class, 'message'])->middleware('throttle:20,1')->name('chat.message');
    Route::post('/api/upload', [UploadController::class, 'handle'])->middleware('throttle:10,1')->name('upload');

    // Academic profile
    Route::get('/profile/academic', [AcademicProfileController::class, 'show'])->name('profile.academic');
    Route::post('/api/profile/suggest-update', [AcademicProfileController::class, 'suggestUpdate'])->middleware('throttle:30,1')->name('profile.suggest-update');
    Route::post('/api/profile/dismiss-prompt', [AcademicProfileController::class, 'dismissSemesterPrompt'])->middleware('throttle:10,1')->name('profile.dismiss-prompt');

    // Onboarding wizard
    Route::get('/onboarding', [OnboardingController::class, 'index'])->name('onboarding');
    Route::get('/onboarding/step/{step}', [OnboardingController::class, 'show'])->name('onboarding.step')->where('step', '[1-6]');
    Route::post('/onboarding/step/{step}', [OnboardingController::class, 'save'])->name('onboarding.save')->where('step', '[1-6]');
    Route::get('/onboarding/step-accounting', [OnboardingController::class, 'showAccounting'])->name('onboarding.step.accounting');
    Route::post('/onboarding/step-accounting', [OnboardingController::class, 'saveAccounting'])->name('onboarding.save.accounting');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ── Admin Routes ──────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'dean'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/students', [AdminController::class, 'students'])->name('students');
    Route::get('/students/export', [AdminController::class, 'exportStudentsCsv'])->name('students.export');
    Route::get('/students/{user}', [AdminController::class, 'studentProfile'])->name('students.show');
    Route::get('/requirements', [AdminController::class, 'requirements'])->name('requirements');
    Route::post('/requirements', [AdminController::class, 'saveRequirements'])->name('requirements.save');
    Route::get('/system-prompt', [AdminController::class, 'systemPrompt'])->name('system-prompt');
    Route::post('/system-prompt', [AdminController::class, 'saveSystemPrompt'])->name('system-prompt.save');
    Route::post('/system-prompt/restore', [AdminController::class, 'restoreSystemPrompt'])->name('system-prompt.restore');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users/{user}/role', [AdminController::class, 'updateUserRole'])->name('users.role');
    Route::get('/stats', [AdminController::class, 'stats'])->name('stats');
});

require __DIR__.'/auth.php';
