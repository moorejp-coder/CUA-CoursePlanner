<?php

namespace App\Http\Controllers;

use App\Models\StudentProfile;
use App\Services\PlannerService;
use App\Services\PrerequisiteService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();

        if (! $user->studentProfile) {
            return redirect()->route('onboarding');
        }

        $profile = $user->studentProfile;

        $showSemesterBanner = false;
        $month = now()->month;
        if ($month === 9 || $month === 1) {
            $shownAt = $profile->semester_prompt_shown_at;
            $showSemesterBanner = ! $shownAt || $shownAt->lt(now()->subMonths(4));
        }

        $welcomeMessage = $this->buildWelcomeMessage($profile);

        return view('chat', compact('showSemesterBanner', 'welcomeMessage'));
    }

    private function buildWelcomeMessage(?StudentProfile $profile): string
    {
        if (! $profile) {
            return "Hello! I'm the Busch School Course Planning Bot.\n\nI can help you with degree requirements, course sequencing, specializations, minors, prerequisites, and graduation planning for your B.S.B.A. or B.S. in Accounting.\n\nTo get started, tell me your degree program, catalog year, and where you are in your studies, or choose a topic from the sidebar.";
        }

        $degreeLabels = [
            'bsba' => 'B.S.B.A.',
            'bs_accounting' => 'B.S. in Accounting',
            'double_major' => 'B.A. in Business (Double Major)',
            'business_minor' => 'Business Minor',
        ];

        $firstName = explode(' ', trim($profile->full_name ?? ''))[0] ?: 'there';
        $degree = $degreeLabels[$profile->degree] ?? strtoupper((string) $profile->degree);
        $standing = ucfirst((string) ($profile->projected_standing ?? 'student'));
        $credits = (int) $profile->credits_completed;
        $graduation = $profile->expected_graduation;

        $specs = array_filter([
            $profile->specialization_1,
            $profile->specialization_2,
            $profile->specialization_3,
        ]);

        $specLabels = [];
        if ($specs) {
            $requirements = json_decode(file_get_contents(storage_path('app/requirements.json')), true);
            $specData = $requirements[$profile->catalog_year]['specializations'] ?? [];
            foreach ($specs as $specKey) {
                $raw = $specData[$specKey]['label'] ?? ucwords(str_replace('_', ' ', $specKey));
                $specLabels[] = trim(preg_split('/\s{2,}/', $raw)[0] ?? $raw);
            }
        }

        $profileLine = "I can see your academic profile — you're a {$standing} {$degree} student";
        if ($specLabels) {
            $profileLine .= ' specializing in '.implode(' and ', $specLabels);
        }
        $profileLine .= " with {$credits} credits completed";
        if ($graduation) {
            $profileLine .= ", on track to graduate in {$graduation}";
        }
        $profileLine .= '.';

        return "Welcome back, {$firstName}! {$profileLine}\n\nI'm ready to help with your 4-year plan, prerequisite status, elective options, and graduation planning. What would you like to explore?";
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

        $systemPrompt = Cache::remember('system_prompt', 3600, fn () => file_get_contents(storage_path('app/system_prompt.txt')));

        $formattingRule = "\n\nFORMATTING RULE: Never use markdown bold formatting (** **) in your responses. Use plain text, dashes, or numbered lists only. Be concise — 3 to 5 sentences for general questions. For any course or semester recommendation, always provide a complete list totaling 15-17 credits — do not cut the list short for brevity. Never repeat information already shown in the student profile.";

        $profileContext = $this->buildProfileContext($cleanMessage);
        $messages = [['role' => 'system', 'content' => $systemPrompt.$formattingRule.$profileContext]];

        // Keep only the most recent 6 history turns to stay within token limits.
        $history = array_slice($validated['history'] ?? [], -6);

        foreach ($history as $turn) {
            $messages[] = ['role' => $turn['role'], 'content' => strip_tags($turn['content'])];
        }

        $messages[] = ['role' => 'user', 'content' => $cleanMessage];

        try {
            $response = Http::withToken(config('services.groq.key'))
                ->timeout(30)
                ->retry(3, 2000, function ($exception, $response) {
                    // Retry transient connection failures immediately
                    if ($exception instanceof ConnectionException) {
                        return true;
                    }

                    // Retry Groq 429 rate-limit responses after the sleep delay
                    return $response && $response->status() === 429;
                }, throw: false)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => config('services.groq.model'),
                    'messages' => $messages,
                    'max_tokens' => 600,
                    'temperature' => 0.3,
                ]);
        } catch (\Throwable $e) {
            $msg = $e->getMessage();
            Log::error('Groq API error', ['exception' => $msg]);

            if (str_contains($msg, '429') || str_contains($msg, 'rate limit') || str_contains($msg, 'Rate limit')) {
                return response()->json(
                    ['error' => 'The AI is handling too many requests right now. Please wait a few seconds and try again.'],
                    429,
                );
            }

            if (str_contains($msg, '413') || str_contains($msg, 'too large')) {
                return response()->json(
                    ['error' => 'This conversation has grown too long for the AI to process. Please start a new conversation to continue.'],
                    413,
                );
            }

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

            if ($response->status() === 429) {
                return response()->json(
                    ['error' => 'The AI is handling too many requests right now. Please wait a few seconds and try again.'],
                    429,
                );
            }

            if ($response->status() === 413) {
                return response()->json(
                    ['error' => 'This conversation has grown too long for the AI to process. Please start a new conversation to continue.'],
                    413,
                );
            }

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

    /**
     * Build a plain-English EXEMPTIONS string from the student's saved course records.
     * Only includes exemptions that are explicitly stored (not inferred).
     *
     * @param  Collection  $courses
     */
    private function buildExemptionsList($courses): string
    {
        $parts = [];

        // Business Core Math: Level 1 or 2 placement exempt
        $mathCore = $courses->where('requirement_category', 'business_core')
            ->where('course_name', 'Math Requirement')
            ->first();
        if ($mathCore && in_array(strtolower($mathCore->course_code ?? ''), ['level 1 or 2 exempt', 'math_exempt', 'math exempt'], true)) {
            $parts[] = 'Business Core Math Requirement — Level 1 or 2 Placement Exempt (counts as satisfied; do NOT say they still need MATH 110 or MATH 111 for this requirement)';
        }

        // LA Math Thinking: math placement exempt
        $mathLa = $courses->where('requirement_category', 'liberal_arts')
            ->where('course_name', 'Math Thinking')
            ->first();
        if ($mathLa && strtolower($mathLa->course_code ?? '') === 'math_exempt') {
            $parts[] = 'Liberal Arts Math Thinking — Exempt via math placement (requirement satisfied)';
        }

        // Language II: exempt because SPAN 113 (or SPAN 111) satisfies both Language I and II
        $lang1 = $courses->where('requirement_category', 'liberal_arts')
            ->where('course_name', 'Language I')
            ->whereIn('course_code', ['SPAN 111', 'SPAN 113', 'span 111', 'span 113'])
            ->first();
        if ($lang1) {
            $code = strtoupper($lang1->course_code);
            $parts[] = "Language II — Exempt ({$code} satisfies both Language I and Language II in a single course)";
        }

        return implode('; ', $parts);
    }

    private function buildProfileContext(string $userMessage = ''): string
    {
        $now = now();
        $month = (int) $now->month;
        $year = (int) $now->year;

        // Map calendar month to CUA academic semester
        if ($month <= 5) {
            $currentSemester = "Spring {$year}";
            $nextSemester = "Fall {$year}";
        } elseif ($month <= 7) {
            $currentSemester = "Summer {$year}";
            $nextSemester = "Fall {$year}";
        } else {
            $currentSemester = "Fall {$year}";
            $nextSemester = 'Spring '.($year + 1);
        }

        // Advise on registration window so the bot can tell students what to register for
        $registrationNote = match (true) {
            $month >= 3 && $month <= 4 => "Fall {$year} registration is open — recommend courses for next fall.",
            $month >= 10 && $month <= 11 => 'Spring '.($year + 1).' registration is open — recommend courses for next spring.',
            $month === 5 => "Summer/Fall {$year} — advise students to confirm fall registration.",
            default => '',
        };

        $dateContext = "DATE: {$now->format('M j, Y')} | SEM: {$currentSemester} | NEXT: {$nextSemester}".
            ($registrationNote ? " | {$registrationNote}" : '');

        $user = Auth::user()->load(['studentProfile', 'studentCourses']);
        $profile = $user->studentProfile;

        if (! $profile) {
            return "\n\n{$dateContext}\n\nSTUDENT PROFILE: Not yet set up. If the student asks about their personal course plan, encourage them to complete their academic profile setup at /onboarding first.";
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

        // Detect structured exemptions from course records so the bot never tells
        // an exempt student they still need a requirement.
        $exemptions = $this->buildExemptionsList($user->studentCourses);

        $lines = [
            $dateContext,
            "STUDENT: {$profile->full_name} | {$degreeLabel} | {$catalogLabel} | Admit: {$profile->admit_term} | Standing: {$profile->projected_standing} | Credits: {$profile->credits_completed} | Grad: {$profile->expected_graduation} | Specs: {$specList}",
            'COMPLETED: '.(implode(', ', $completedCodes) ?: 'None'),
            'IN_PROGRESS: '.(implode(', ', $inProgressCodes) ?: 'None'),
        ];

        if ($exemptions) {
            $lines[] = 'EXEMPTIONS (these requirements ARE satisfied — do NOT tell the student they still need them): '.$exemptions;
        }

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
            $lines[] = 'ACCT: '.($acctParts ?: 'Not yet entered');
        }

        $planningKeywords = [
            'plan', 'schedule', 'semester', 'remaining', 'graduate',
            'prerequisite', 'register', 'take', 'recommend', 'course',
            'credit', 'finish', 'sequence', 'requirement',
        ];
        $lowerMessage = strtolower($userMessage);
        $isPlanningIntent = count(array_filter($planningKeywords, fn ($kw) => str_contains($lowerMessage, $kw))) > 0;

        $plannerService = new PlannerService;

        if ($isPlanningIntent) {
            // Remaining degree requirements for 4-year plan generation
            $remainingContext = $plannerService->buildRemainingContext(
                $profile->degree,
                $profile->catalog_year,
                strtolower($profile->projected_standing),
                (int) $profile->credits_completed,
                $profile->specialization_1,
                $profile->specialization_2,
                $profile->specialization_3,
                $user->studentCourses,
            );
            $lines[] = $remainingContext;

            // Prerequisite conflict detection + next-eligible analysis
            $prereqSummary = (new PrerequisiteService)->buildContextSummary(
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

            // Eligible elective suggestions per specialization
            $eligibleElectives = $plannerService->buildEligibleElectives(
                $profile->catalog_year,
                $completedCodes,
                $inProgressCodes,
                strtolower($profile->projected_standing),
                (int) $profile->credits_completed,
                $profile->specialization_1,
                $profile->specialization_2,
                $profile->specialization_3,
            );

            if ($eligibleElectives) {
                $lines[] = $eligibleElectives;
            }

            // Fastest path to graduation — critical chains + minimum semesters
            $fastestPath = $plannerService->buildFastestPathAnalysis(
                $profile->degree,
                $profile->catalog_year,
                $completedCodes,
                $inProgressCodes,
                strtolower($profile->projected_standing),
                (int) $profile->credits_completed,
                $profile->specialization_1,
                $profile->specialization_2,
                $profile->specialization_3,
                $profile->expected_graduation ?? '',
            );

            if ($fastestPath) {
                $lines[] = $fastestPath;
            }
        }

        return "\n\n".implode("\n", $lines);
    }
}
