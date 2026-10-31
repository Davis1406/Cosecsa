<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Reset password | COSECSA MIS</title>
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

            @if(! $account)
                <p class="login-box-msg mb-2">This link is no longer valid</p>
                <p class="auth-help mb-3">Reset links work once and expire after an hour. Enter your email to get a new one.</p>
                <a href="{{ url('forget-password') }}" class="btn btn-login btn-block d-flex align-items-center justify-content-center">Get a new link</a>
            @else
                <p class="login-box-msg mb-1">Choose a new password</p>
                <p class="auth-help mb-3">For <strong>{{ $account->email ?: $account->name }}</strong>. At least 8 characters.</p>

                <form method="POST" action="{{ url('reset/'.request()->route('token')) }}">
                    @csrf
                    <div class="mb-3 password-wrapper">
                        <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" id="passwordInput" placeholder="New password" minlength="8" required autofocus>
                        <button type="button" class="btn-password-toggle" id="togglePassword" title="Show / hide password"><i class="fas fa-eye" id="toggleIcon"></i></button>
                    </div>
                    @error('password')<span class="invalid-feedback d-block mb-2">{{ $message }}</span>@enderror
                    <div class="mb-3">
                        <input type="password" class="form-control" name="password_confirmation" id="confirmInput" placeholder="Retype new password" minlength="8" required>
                    </div>
                    <button type="submit" class="btn btn-login btn-block">Change password</button>
                </form>
            @endif

            <p class="text-center mt-4 mb-0 auth-back"><a href="{{ url('/') }}"><i class="fas fa-arrow-left mr-1"></i>Back to login</a></p>
        </div>
    </div>
</div>
<script>
    var toggle = document.getElementById('togglePassword');
    if (toggle) toggle.addEventListener('click', function () {
        var show = document.getElementById('passwordInput').type === 'password';
        document.getElementById('passwordInput').type = show ? 'text' : 'password';
        document.getElementById('confirmInput').type = show ? 'text' : 'password';
        document.getElementById('toggleIcon').classList.toggle('fa-eye', ! show);
        document.getElementById('toggleIcon').classList.toggle('fa-eye-slash', show);
    });
</script>
</body>
</html>
