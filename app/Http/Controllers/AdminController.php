<?php

namespace App\Http\Controllers;

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminController extends Controller
{
    public function dashboard(): View
    {
        $totalStudents = User::students()->count();
        $totalWithProfile = StudentProfile::count();
        $registeredToday = User::whereDate('created_at', today())->count();
        $recentUsers = User::with('studentProfile')->latest()->limit(10)->get();

        $specCounts = StudentProfile::whereNotNull('specialization_1')
            ->selectRaw('specialization_1 as spec, count(*) as total')
            ->groupBy('specialization_1')
            ->orderByDesc('total')
            ->limit(5)
            ->pluck('total', 'spec');

        return view('admin.dashboard', compact(
            'totalStudents', 'totalWithProfile', 'registeredToday', 'recentUsers', 'specCounts'
        ));
    }

    public function students(Request $request): View
    {
        $search = $request->string('search')->toString();

        $students = User::with('studentProfile')
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
            ))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.students.index', compact('students', 'search'));
    }

    public function studentProfile(User $user): View
    {
        $user->load(['studentProfile', 'studentCourses']);

        return view('admin.students.show', compact('user'));
    }

    public function exportStudentsCsv(): StreamedResponse
    {
        return response()->stream(function (): void {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Name', 'Email', 'Role', 'Degree', 'Catalog Year', 'Specialization', 'Credits', 'GPA', 'Standing', 'Registered']);

            User::with('studentProfile')->chunk(100, function ($users) use ($handle): void {
                foreach ($users as $user) {
                    $p = $user->studentProfile;
                    fputcsv($handle, [
                        $user->name, $user->email, $user->role,
                        $p?->degree ?? '', $p?->catalog_year ?? '',
                        $p?->specialization_1 ?? '', $p?->credits_completed ?? '',
                        $p?->gpa ?? '', $p?->projected_standing ?? '',
                        $user->created_at->format('Y-m-d'),
                    ]);
                }
            });

            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="students-'.now()->format('Y-m-d').'.csv"',
        ]);
    }

    public function requirements(): View
    {
        $requirements = $this->loadRequirements();

        return view('admin.requirements', compact('requirements'));
    }

    public function saveRequirements(Request $request): RedirectResponse
    {
        $request->validate([
            'data' => ['required', 'array'],
            'data.liberal_arts' => ['required', 'string'],
            'data.pre_2024' => ['required', 'array'],
            'data.pre_2024.business_core' => ['required', 'string'],
            'data.pre_2024.bs_accounting' => ['required', 'string'],
            'data.pre_2024.spec' => ['required', 'array'],
            'data.pre_2024.spec.*' => ['required', 'string'],
            'data.post_2024' => ['required', 'array'],
            'data.post_2024.business_core' => ['required', 'string'],
            'data.post_2024.bs_accounting' => ['required', 'string'],
            'data.post_2024.spec' => ['required', 'array'],
            'data.post_2024.spec.*' => ['required', 'string'],
        ]);

        $raw = $request->input('data');
        $toLines = fn (string $text): array => array_values(array_filter(array_map('trim', explode("\n", $text))));

        $existing = $this->loadRequirements();

        $rebuild = function (string $year) use ($raw, $toLines, $existing): array {
            $specData = $existing[$year]['specializations'] ?? [];
            $updatedSpecs = [];
            foreach ($raw[$year]['spec'] ?? [] as $slug => $text) {
                $updatedSpecs[$slug] = [
                    'label' => $specData[$slug]['label'] ?? ucwords(str_replace('_', ' ', $slug)),
                    'courses' => $toLines($text),
                ];
            }

            return [
                'business_core' => $toLines($raw[$year]['business_core']),
                'bs_accounting' => $toLines($raw[$year]['bs_accounting']),
                'specializations' => $updatedSpecs,
            ];
        };

        $requirements = [
            'liberal_arts' => $toLines($raw['liberal_arts']),
            'pre_2024' => $rebuild('pre_2024'),
            'post_2024' => $rebuild('post_2024'),
        ];

        file_put_contents(storage_path('app/requirements.json'), json_encode($requirements, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return redirect()->route('admin.requirements')->with('success', 'Requirements updated successfully.');
    }

    public function systemPrompt(): View
    {
        $content = file_get_contents(storage_path('app/system_prompt.txt'));
        $charCount = mb_strlen($content);
        $tokenEstimate = (int) round($charCount / 4);
        $versions = $this->loadPromptVersions();

        return view('admin.system-prompt', compact('content', 'charCount', 'tokenEstimate', 'versions'));
    }

    public function saveSystemPrompt(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'content' => ['required', 'string', 'min:100'],
        ]);

        $this->archiveSystemPrompt();
        file_put_contents(storage_path('app/system_prompt.txt'), $validated['content']);

        return redirect()->route('admin.system-prompt')->with('success', 'System prompt saved.');
    }

    public function restoreSystemPrompt(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'filename' => ['required', 'string', 'regex:/^system_prompt_[\d_-]+\.txt$/'],
        ]);

        $path = storage_path('app/system_prompt_versions/'.$validated['filename']);
        abort_unless(file_exists($path), 404);

        $this->archiveSystemPrompt();
        copy($path, storage_path('app/system_prompt.txt'));

        return redirect()->route('admin.system-prompt')->with('success', 'System prompt restored from '.$validated['filename'].'.');
    }

    public function users(Request $request): View
    {
        $search = $request->string('search')->toString();

        $users = User::when($search, fn ($q) => $q->where(fn ($q) => $q
            ->where('name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%")
        ))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.users', compact('users', 'search'));
    }

    public function updateUserRole(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'role' => ['required', 'in:student,dean,admin'],
        ]);

        abort_if($user->id === $request->user()->id, 403, 'You cannot change your own role.');
        abort_if($validated['role'] === 'admin' && ! $request->user()->isAdmin(), 403, 'Only admins can promote to admin.');

        $user->update(['role' => $validated['role']]);

        return response()->json(['success' => true, 'role' => $user->role]);
    }

    public function stats(): View
    {
        $totalUsers = User::count();
        $totalWithProfile = StudentProfile::count();

        $degreeBreakdown = StudentProfile::selectRaw('degree, count(*) as total')
            ->groupBy('degree')->pluck('total', 'degree');

        $specBreakdown = StudentProfile::whereNotNull('specialization_1')
            ->selectRaw('specialization_1 as spec, count(*) as total')
            ->groupBy('specialization_1')->orderByDesc('total')
            ->pluck('total', 'spec');

        $registrationsByMonth = User::selectRaw("strftime('%Y-%m', created_at) as month, count(*) as total")
            ->where('created_at', '>=', Carbon::now()->subMonths(6))
            ->groupBy('month')->orderBy('month')
            ->pluck('total', 'month');

        return view('admin.stats', compact(
            'totalUsers', 'totalWithProfile',
            'degreeBreakdown', 'specBreakdown', 'registrationsByMonth'
        ));
    }

    private function loadRequirements(): array
    {
        $path = storage_path('app/requirements.json');

        return file_exists($path)
            ? (json_decode(file_get_contents($path), true) ?? [])
            : [];
    }

    private function archiveSystemPrompt(): void
    {
        $dir = storage_path('app/system_prompt_versions');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $current = storage_path('app/system_prompt.txt');
        if (! file_exists($current)) {
            return;
        }

        copy($current, $dir.'/system_prompt_'.now()->format('Y-m-d_H-i-s').'.txt');

        $versions = glob($dir.'/system_prompt_*.txt') ?: [];
        if (count($versions) > 10) {
            usort($versions, fn ($a, $b) => filemtime($a) <=> filemtime($b));
            foreach (array_slice($versions, 0, count($versions) - 10) as $old) {
                unlink($old);
            }
        }
    }

    private function loadPromptVersions(): array
    {
        $dir = storage_path('app/system_prompt_versions');
        if (! is_dir($dir)) {
            return [];
        }

        $files = glob($dir.'/system_prompt_*.txt') ?: [];
        usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));

        return array_map(fn (string $path): array => [
            'filename' => basename($path),
            'saved_at' => Carbon::createFromTimestamp(filemtime($path))->format('M j, Y g:i A'),
            'size' => number_format(filesize($path) / 1024, 1).' KB',
        ], array_slice($files, 0, 10));
    }
}
