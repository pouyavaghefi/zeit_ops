<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Traits\RecordsActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LoginController extends AuthController
{
    use RecordsActivity;

    public function showLogin()
    {
        return view('cla.login');
    }

    public function doLogin(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Invalid email or password.');
        }

        // Preserve existing email verification requirement.
        if (is_null($user->email_verified_at)) {
            return redirect()
                ->route('login')
                ->with('error', 'Your email is not verified. Check your inbox again.');
        }

        // Preserve pending ZeitOps enrollment before authentication.
        $pendingEnrollment = $request->session()->get(
            'zeitops.pending_enrollment'
        );

        Auth::login($user);

        // Prevent session fixation after successful login.
        $request->session()->regenerate();

        $this->recordActivity('Logged in');

        // Redirect to existing enrollment if still valid.
        if (is_array($pendingEnrollment)) {

            $serverId = $pendingEnrollment['server_id'] ?? null;
            $token = $pendingEnrollment['token'] ?? null;
            $createdAt = (int) ($pendingEnrollment['created_at'] ?? 0);

            $notExpired = $createdAt > 0
                && now()->timestamp >= $createdAt
                && (now()->timestamp - $createdAt) <= 7200;

            if (
                is_string($serverId) &&
                Str::isUuid($serverId) &&
                is_string($token) &&
                $token !== '' &&
                $notExpired
            ) {
                $metadataPath = storage_path(
                    "app/private/zeitops/servers/{$serverId}/server.json"
                );

                if (is_file($metadataPath) && is_readable($metadataPath)) {

                    $server = json_decode(
                        file_get_contents($metadataPath),
                        true
                    );

                    if (
                        is_array($server) &&
                        !empty($server['enrollment_token_hash']) &&
                        hash_equals(
                            $server['enrollment_token_hash'],
                            hash('sha256', $token)
                        )
                    ) {
                        $request->session()->put(
                            'zeitops.pending_enrollment',
                            $pendingEnrollment
                        );

                        return redirect()
                            ->route('servers.enroll', [
                                'serverId' => $serverId,
                            ])
                            ->with(
                                'success',
                                'Welcome back! Continue setting up your server.'
                            );
                    }
                }
            }

            // Discard invalid or expired pending enrollment.
            $request->session()->forget('zeitops.pending_enrollment');
        }

        // Normal login without an active enrollment.
        return redirect('/panel/dashboard')
            ->with('success', 'Login successfully');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            $this->recordActivity('Logged out');
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Logged out successfully');
    }

    public function logoutGet(Request $request)
    {
        if (Auth::check()) {
            $this->recordActivity('Logged out');
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
