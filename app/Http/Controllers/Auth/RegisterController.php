<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends AuthController
{
    public function showRegister()
    {
        return view('cla.register');
    }

    public function doRegister(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|max:20',
            'password_confirmation' => 'required|same:password',
        ]);
        $password = Hash::make($data['password']);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $password
        ]);

        return redirect()->route('login')->with('success','Your account has been created. An email has been sent to your inbox. Please activate your account.');
    }
}
