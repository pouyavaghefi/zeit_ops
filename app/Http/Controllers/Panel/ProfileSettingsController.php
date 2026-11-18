<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileSettingsController extends Controller
{
    /**
     * Show profile settings.
     */
    public function index()
    {
        return view('panel.profile-settings');
    }

    /**
     * Update personal information.
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validateWithBag('profile', [
            'name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        $user->fill($validated);

        // Require re-verification when email changes.
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()
            ->route('panel.profile-settings')
            ->with('success', 'Profile updated successfully.');
    }

    /**
     * Update account password.
     */
    public function updatePassword(Request $request)
    {
        $validated = $request->validateWithBag('password', [
            'current_password' => [
                'required',
                'current_password',
            ],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->letters()->numbers(),
            ],
        ]);

        $user = $request->user();

        $user->password = Hash::make($validated['password']);
        $user->save();

        return redirect()
            ->route('panel.profile-settings')
            ->with('success', 'Password updated successfully.')
            ->with('active_tab', 'security');
    }
}
