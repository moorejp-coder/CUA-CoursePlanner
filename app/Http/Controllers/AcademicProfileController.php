<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class AcademicProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user()->load(['studentProfile', 'studentCourses']);
        $profile = $user->studentProfile;
        $courses = $user->studentCourses;

        // Group courses by requirement_category
        $coursesByCategory = $courses->groupBy('requirement_category');

        // Calculate completion stats per category
        $stats = [];
        foreach ($coursesByCategory as $category => $categoryCourses) {
            $completed = $categoryCourses->where('status', 'completed')->count();
            $total = $categoryCourses->count();
            $stats[$category] = ['completed' => $completed, 'total' => $total];
        }

        return view('profile.academic', compact('profile', 'courses', 'coursesByCategory', 'stats'));
    }
}
