<?php

namespace App\Http\Controllers;

use App\Models\StudentCourse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user()->load(['studentProfile', 'studentCourses']);
        $profile = $user->studentProfile;
        $courses = $user->studentCourses;

        $coursesByCategory = $courses->groupBy('requirement_category');

        $stats = [];
        foreach ($coursesByCategory as $category => $categoryCourses) {
            $completed = $categoryCourses->where('status', 'completed')->count();
            $total = $categoryCourses->count();
            $stats[$category] = ['completed' => $completed, 'total' => $total];
        }

        return view('profile.academic', compact('profile', 'courses', 'coursesByCategory', 'stats'));
    }

    public function suggestUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'course_code' => ['required', 'string', 'max:20'],
            'status' => ['required', 'in:completed,in_progress,planned,not_needed'],
            'grade' => ['nullable', 'string', 'max:5'],
            'semester' => ['nullable', 'string', 'max:20'],
        ]);

        $user = $request->user();

        $course = StudentCourse::firstOrNew([
            'user_id' => $user->id,
            'course_code' => strtoupper($validated['course_code']),
        ]);

        $course->status = $validated['status'];
        $course->grade = $validated['grade'] ?? null;
        $course->semester_completed = $validated['semester'] ?? null;

        if (! $course->exists) {
            $course->course_name = strtoupper($validated['course_code']);
            $course->requirement_category = 'updated_by_bot';
        }

        $course->save();

        if ($user->studentProfile) {
            $user->studentProfile->last_updated_at = now();
            $user->studentProfile->save();
        }

        return response()->json(['success' => true, 'course_code' => $course->course_code]);
    }

    public function dismissSemesterPrompt(Request $request): JsonResponse
    {
        $profile = $request->user()->studentProfile;

        if ($profile) {
            $profile->semester_prompt_shown_at = now();
            $profile->save();
        }

        return response()->json(['success' => true]);
    }
}
