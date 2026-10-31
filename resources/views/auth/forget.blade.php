<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Forgot password | COSECSA MIS</title>
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
    <link rel="icon" href="{{ asset('dist/img/Cosecsa_Logo_sm.png') }}">
    @include('auth._style')
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <img src="{{ asset('dist/img/Cosecsa_Logo_sm.png') }}" alt="COSECSA Logo">
            <br>
            <a href="#" class="h1" style="color: #a02626;"><b>COSECSA-MIS</b></a>
        </div>
        <div class="card-body">
            @include('_message')

            <p class="login-box-msg mb-1">Forgot your password?</p>
            <p class="auth-help mb-3">Enter your email address and we will send you a link to choose a new one. Use the email you gave COSECSA (your login email or your personal email).</p>

            <form method="POST" action="{{ url('forget-password') }}">
                @csrf
                <div class="mb-3">
                    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="Email address" required autofocus>
                    @error('email')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                </div>
                <button type="submit" class="btn btn-login btn-block"><i class="fas fa-paper-plane mr-1"></i> Send reset link</button>
            </form>

            <p class="text-center mt-4 mb-0 auth-back"><a href="{{ url('/') }}"><i class="fas fa-arrow-left mr-1"></i>Back to login</a></p>
        </div>
    </div>
</div>
</body>
</html>
