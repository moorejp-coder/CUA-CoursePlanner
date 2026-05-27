<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\AccountDeletedNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AccountDeletionService
{
    /**
     * Permanently delete a user and every record that references them.
     *
     * Deletion order:
     *   1. Capture manifest (counts before deletion for the confirmation email)
     *   2. Send confirmation email FIRST — address must still be valid
     *   3. Clear per-email rate-limiter keys
     *   4. Delete all active sessions
     *   5. Delete password-reset tokens (no FK constraint — not cascade-deleted)
     *   6. Delete user-owned storage files (none in current app, hook is here for future use)
     *   7. Delete the User row — cascades to student_profiles and student_courses
     *
     * @return array{name: string, email: string, deleted_at: Carbon, records: array<string, int>}
     */
    public function delete(User $user): array
    {
        $manifest = $this->buildManifest($user);

        // Send the email before deleting so the address is still valid and the
        // user receives confirmation of exactly what was removed.
        $this->sendConfirmationEmail($user, $manifest);

        $this->clearRateLimiterKeys($user->email);
        $this->deleteSessions($user);
        $this->deletePasswordResetTokens($user->email);
        $this->deleteStorageFiles($user);

        $user->delete();

        Log::info('Account permanently deleted', [
            'email_hash' => hash('sha256', $user->email),
            'deleted_at' => $manifest['deleted_at']->toIso8601String(),
            'records' => $manifest['records'],
        ]);

        return $manifest;
    }

    /**
     * @return array{name: string, email: string, deleted_at: Carbon, records: array<string, int>}
     */
    private function buildManifest(User $user): array
    {
        return [
            'name' => $user->name,
            'email' => $user->email,
            'deleted_at' => now(),
            'records' => [
                'academic_profile' => $user->studentProfile ? 1 : 0,
                'courses' => $user->studentCourses()->count(),
                'sessions' => DB::table('sessions')->where('user_id', $user->id)->count(),
                'password_reset_tokens' => DB::table('password_reset_tokens')
                    ->where('email', $user->email)
                    ->count(),
            ],
        ];
    }

    private function sendConfirmationEmail(User $user, array $manifest): void
    {
        try {
            $user->notify(new AccountDeletedNotification($manifest));
        } catch (\Throwable $e) {
            // Log the failure but do not abort — the user requested deletion and
            // the inability to send an email must not block data removal.
            Log::warning('Account deletion confirmation email failed to send', [
                'email_hash' => hash('sha256', $user->email),
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function clearRateLimiterKeys(string $email): void
    {
        $lower = Str::lower($email);
        RateLimiter::clear('login_acct|'.$lower);
        RateLimiter::clear('pwd_reset_email|'.$lower);
    }

    private function deleteSessions(User $user): void
    {
        DB::table('sessions')->where('user_id', $user->id)->delete();
    }

    private function deletePasswordResetTokens(string $email): void
    {
        DB::table('password_reset_tokens')->where('email', $email)->delete();
    }

    private function deleteStorageFiles(User $user): void
    {
        // No user-specific files exist in the current application — uploaded APW
        // files are processed in-memory and never written to storage. This hook
        // is intentionally left here: if user avatars or exports are added later,
        // delete them from the correct Storage disk here.
        $userDir = 'users/'.$user->id;
        if (Storage::exists($userDir)) {
            Storage::deleteDirectory($userDir);
        }
    }
}
