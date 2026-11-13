@extends('cla.layouts.master')

@section('title','Forgot Password')

@section('page-title','Reset Password')

@section('class-active')
    <a href="/login">Login</a><a href="/register">Register</a>
@endsection

@section('content')
    <form method="POST" action="{{ route('do.forgot') }}">
        @csrf

        <input class="form-control" type="text" name="email" placeholder="E-mail Address" required>
        <div class="form-button">
            <button id="submit" type="submit" class="ibtn">Send Reset Link</button>
        </div>
    </form>
@endsection
