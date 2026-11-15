<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class RegisterController extends AuthController
{
    public function showRegister()
    {
        return view('cla.register');
    }

    public function doRegister(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8),
            ],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Keep the current ZeitOps enrollment in the session.
        // Do not generate a new UUID or SSH key here.
        $pendingEnrollment = $request->session()->get(
            'zeitops.pending_enrollment'
        );

        if ($pendingEnrollment) {
            $request->session()->put(
                'zeitops.pending_enrollment',
                $pendingEnrollment
            );
        }

        return redirect()
            ->route('login')
            ->with(
                'success',
                'Your account has been created. Please sign in to continue.'
            );
    }
}
