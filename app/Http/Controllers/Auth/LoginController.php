<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Traits\RecordsActivity;

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
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $data['email'])->first();

        if ($user) {
            if (Hash::check($data['password'], $user->password)) {

                if (is_null($user->email_verified_at)) {
                    return redirect()->route('login')
                        ->with('error', 'Your email is not verified. Check your inbox again');
                }

                auth()->login($user);
                $this->recordActivity('Logged in');

                session()->flash('success', 'You are now logged in');
                return redirect('panel/dashboard')->with('success', 'Login successfully');
            } else {
                return redirect()->route('login')->with('error', 'Wrong password');
            }
        } else {
            return redirect()->route('login')->with('error', 'Such user does not exist');
        }
    }

    public function logout(Request $request)
    {
        $this->recordActivity('Logged out'); // <-- Record logout activity

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Logged out successfully');
    }

    public function logoutGet(Request $request)
    {
        $this->recordActivity('Logged out'); // <-- Record logout activity

        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
