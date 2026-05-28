<?php

namespace App\Http\Controllers;

use App\Services\PrerequisiteService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(): View
    {
        $profile = Auth::user()?->studentProfile;

        $showSemesterBanner = false;
        if ($profile) {
            $month = now()->month;
            if ($month === 9 || $month === 1) {
                $shownAt = $profile->semester_prompt_shown_at;
                $showSemesterBanner = ! $shownAt || $shownAt->lt(now()->subMonths(4));
            }
        }

        return view('chat', compact('showSemesterBanner'));
    }

    public function message(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string|max:2000',
            'history' => 'array|max:50',
            'history.*.role' => 'required|in:user,assistant',
            'history.*.content' => 'required|string|max:4000',
        ]);

        $cleanMessage = strip_tags($validated['message']);

        $systemPrompt = file_get_contents(storage_path('app/system_prompt.txt'));

        $formattingRule = "\n\nFORMATTING RULE: Never use markdown bold formatting (** **) in your responses. Use plain text, dashes, or numbered lists only.";

        $profileContext = $this->buildProfileContext();
        $messages = [['role' => 'system', 'content' => $systemPrompt.$formattingRule.$profileContext]];

        foreach ($validated['history'] ?? [] as $turn) {
            $messages[] = ['role' => $turn['role'], 'content' => strip_tags($turn['content'])];
        }

        $messages[] = ['role' => 'user', 'content' => $cleanMessage];

        try {
            $response = Http::withToken(config('services.groq.key'))
                ->timeout(30)
                ->retry(2, 500, fn ($e) => $e instanceof ConnectionException)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => config('services.groq.model'),
                    'messages' => $messages,
                    'max_tokens' => 4096,
                ]);
        } catch (\Throwable $e) {
            Log::error('Groq API connection error', [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(
                ['error' => 'The AI service is temporarily unavailable. Please try again.'],
                502,
            );
        }

        if (! $response->successful()) {
            Log::error('Groq API error response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return response()->json(
                ['error' => 'The AI service is temporarily unavailable. Please try again.'],
                502,
            );
        }

        Log::info('Groq API success', [
            'status' => $response->status(),
            'model' => $response->json('model'),
            'usage' => $response->json('usage'),
            'finish_reason' => $response->json('choices.0.finish_reason'),
        ]);

        return response()->json([
            'message' => $response->json('choices.0.message.content'),
        ]);
    }

    private function buildProfileContext(): string
    {
        $user = Auth::user()->load(['studentProfile', 'studentCourses']);
        $profile = $user->studentProfile;

        if (! $profile) {
            return "\n\nSTUDENT PROFILE: Not yet set up. If the student asks about their personal course plan, encourage them to complete their academic profile setup at /onboarding first.";
        }

        $completedCourses = $user->studentCourses->where('status', 'completed');
        $inProgressCourses = $user->studentCourses->where('status', 'in_progress');

        $completedCodes = $completedCourses->pluck('course_code')->values()->all();
        $inProgressCodes = $inProgressCourses->pluck('course_code')->values()->all();

        $specs = array_filter([
            $profile->specialization_1,
            $profile->specialization_2,
            $profile->specialization_3,
        ]);

        $catalogLabel = $profile->catalog_year === 'post_2024' ? 'Post-2024' : 'Pre-2024';
        $degreeLabel = match ($profile->degree) {
            'bs_accounting' => 'B.S. Accounting',
            'double_major' => 'BA in Business (Double Major)',
            'business_minor' => 'Business Minor',
            default => 'B.S.B.A.',
        };
        $specList = implode(', ', $specs) ?: 'None selected';

        $lines = [
            "STUDENT PROFILE: {$profile->full_name} | {$degreeLabel} | {$catalogLabel} Catalog | Admit: {$profile->admit_term} | Standing: {$profile->projected_standing} | GPA: ".($profile->gpa ?? 'N/A')." | Credits: {$profile->credits_completed} | Grad: {$profile->expected_graduation}",
            "Specializations: {$specList}",
            'COMPLETED: '.(implode(', ', $completedCodes) ?: 'None'),
            'IN PROGRESS: '.(implode(', ', $inProgressCodes) ?: 'None'),
        ];

        if ($profile->degree === 'bs_accounting') {
            $acctCourses = $user->studentCourses->where('requirement_category', 'accounting');
            $acctParts = $acctCourses->map(function ($c) {
                $status = match ($c->status) {
                    'completed' => 'done',
                    'in_progress' => 'IP',
                    default => 'needed',
                };

                return "{$c->course_code}({$status})";
            })->join(' ');
            $lines[] = 'ACCT REQUIREMENTS: '.($acctParts ?: 'Not yet entered');
        }

        // Prerequisite conflict detection + next-eligible analysis
        $prereqService = new PrerequisiteService;
        $prereqSummary = $prereqService->buildContextSummary(
            $completedCodes,
            $inProgressCodes,
            strtolower($profile->projected_standing),
            (int) $profile->credits_completed,
            $profile->degree,
            $profile->specialization_1,
            $profile->specialization_2,
            $profile->specialization_3,
        );

        if ($prereqSummary) {
            $lines[] = $prereqSummary;
        }

        return "\n\n".implode("\n", $lines);
    }
}
