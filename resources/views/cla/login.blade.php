@extends('cla.layouts.master')

@section('title','Login')

@section('page-title','Login to Client Area')

@section('class-active')
    <a href="/login" class="active">Login</a><a href="/register">Register</a>
@endsection

@section('content')
    <form method="POST" action="{{ route('do.login') }}">
        @csrf

        <input class="form-control" type="text" name="email" placeholder="E-mail Address" value="{{ old('email') }}" required>
        <input class="form-control" type="password" name="password" placeholder="Password" required>
        <div class="form-button">
            <button id="submit" type="submit" class="ibtn">Login</button> <a href="/forgot">Forget password?</a>
        </div>
    </form>
    {{--                    <div class="other-links">--}}
    {{--                        <span>Or login with</span><a href="#">Facebook</a><a href="#">Google</a><a href="#">Linkedin</a>--}}
    {{--                    </div>--}}
@endsection
