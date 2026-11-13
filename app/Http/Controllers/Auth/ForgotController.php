<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ForgotController extends AuthController
{
    public function showForgot()
    {
        return view('cla.forgot');
    }

    public function doForgot(Request $request)
    {
        dd(4);
    }
}
