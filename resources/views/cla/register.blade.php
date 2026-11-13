@extends('cla.layouts.master')

@section('title','Register')

@section('page-title','Register at Client Area')

@section('page-para','Join our community and enjoy automation')

@section('class-active')
    <a href="/login">Login</a><a href="/register" class="active">Register</a>
@endsection

@section('content')
    <form method="POST" action="{{ route('do.register') }}">
        @csrf

        <input class="form-control" type="name" name="name" placeholder="Name" required>
        <input class="form-control" type="email" name="email" placeholder="E-mail Address" required>
        <input class="form-control" type="password" name="password" placeholder="Password" required>
        <input class="form-control" type="password" name="password_confirmation" placeholder="Password" required>
        <div class="form-button">
            <button id="submit" type="submit" class="ibtn">Register</button>
        </div>
    </form>
@endsection

