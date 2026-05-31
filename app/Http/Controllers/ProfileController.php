<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Services\AccountDeletionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        // Keep StudentProfile.full_name in sync with User.name
        $profile = $user->studentProfile;
        if ($profile) {
            $profile->full_name = $user->name;
            $profile->save();
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Permanently delete the user's account and all associated data.
     */
    public function destroy(Request $request, AccountDeletionService $deletion): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Log out first — guard::logout() cycles the remember token via $user->save().
        // If we logged out AFTER deleting the user, that save() would re-insert the
        // deleted row (Eloquent treats a model with exists=false as a new record).
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Run the full deletion pipeline: email → rate-limiter → sessions →
        // password-reset tokens → storage files → user row (cascades to profile + courses).
        $manifest = $deletion->delete($user);

        return Redirect::route('account.deleted', [
            'courses' => $manifest['records']['courses'],
            'profile' => $manifest['records']['academic_profile'],
        ]);
    }
}
