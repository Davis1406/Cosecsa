<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Login | COSECSA MIS</title>
    <link rel="stylesheet" href="{{ url('public/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ url('public/dist/css/adminlte.min.css') }}">
    <link rel="icon" href="{{ url('public/dist/img/Cosecsa_Logo.png') }}">

    <style>
        .btn-custom {
            background-color: #a02626;
            border-color: #a02626;
            color: #fff;
        }

        .btn-custom:hover {
            background-color: #870f0f;
            border-color: #870f0f;
            color: #FEC503;
        }

        .card.card-outline.card-primary {
            border-color: #FEC503;
        }
    </style>
</head>

<body class="hold-transition login-page">
    <div class="login-box">
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <img src="{{ url('public/dist/img/Cosecsa_Logo.png') }}" style="max-width:80px;" alt="Logo">
                <br>
                <a href="#" class="h1" style="color: #a02626;"><b>COSECSA-MIS</b></a>
            </div>
            <div class="card-body">
                @include('_message')

                @php
                    $roleId = session('pending_role');
                    $roleNames = [
                        1 => 'Admin',
                        2 => 'Trainee',
                        3 => 'Candidate',
                        4 => 'Trainer',
                        5 => 'Country Representative',
                        7 => 'Fellow',
                        8 => 'Member',
                        9 => 'Examiner / Observer',
                    ];
                    $roleName = $roleNames[$roleId] ?? 'Unknown Role';
                @endphp

                <p class="login-box-msg">Login as <strong>{{ $roleName }}</strong></p>

                <form method="POST" action="{{ url('login') }}">
                    @csrf
                    <input type="hidden" name="role" value="{{ $roleId }}">

                    <div class="mb-3">
                        <input type="text" class="form-control" name="email" placeholder="Email or Username"
                            required>
                    </div>

                    <div class="mb-3">
                        <input type="password" class="form-control" name="password" placeholder="Password" required>
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">Remember Me</label>
                    </div>
                    
                    <div class="row">
                        <div class="col-6">
                            <a href="{{ url('forget-password') }}"
                                class="btn btn-secondary btn-block d-flex align-items-center justify-content-center"
                                style="height: 38px; font-size: 14px;">
                                <i class="fas fa-unlock-alt mr-1"></i>Forget Password
                            </a>
                        </div>
                        <div class="col-6">
                            <button type="submit" class="btn btn-primary btn-block"
                                style="background-color: #a02626; border-color:#a02626; height: 38px; font-size: 14px;">
                                Login
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
