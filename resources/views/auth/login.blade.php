<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>Login | COSECSA MIS</title>
    <link rel="stylesheet" href="{{ url('public/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ url('public/dist/css/adminlte.min.css') }}">
    <link rel="icon" href="{{ url('public/dist/img/Cosecsa_Logo.png') }}">

    <style>
        /* Login Box */
        .login-box {
            width: 400px;
            margin: 7% auto;
        }

        @media (max-width: 768px) {
            .login-box {
                width: 95%;
                margin: 5% auto;
                min-height: calc(100vh - 10%);
                display: flex;
                flex-direction: column;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .login-box {
                width: 98%;
                margin: 2% auto;
            }
        }

        /* Card */
        .card.card-outline.card-primary {
            border-color: #FEC503;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .card-header img {
            max-width: 80px;
            height: auto;
        }

        @media (max-width: 768px) {
            .card-header img {
                max-width: 100px;
            }
        }

        /* Inputs */
        .login-box input.form-control {
            font-size: 16px; /* prevents mobile zoom */
            height: 45px;
        }

        /* Buttons */
        .btn-login, .btn-custom {
            background-color: #a02626;
            border-color: #a02626;
            color: #fff;
            height: 45px;
            font-size: 16px;
        }

        .btn-login:hover, .btn-custom:hover {
            background-color: #870f0f;
            border-color: #870f0f;
            color: #FEC503;
        }

        .btn-secondary {
            height: 45px;
            font-size: 16px;
        }

        /* Messages */
        .login-box-msg {
            font-size: 1.1rem;
            margin-bottom: 1.5rem;
        }

        /* Body full height */
        body.login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @media (max-width: 768px) {
            body.login-page {
                padding: 10px;
                align-items: flex-start;
                padding-top: 5vh;
            }
        }

        /* Form spacing */
        .mb-3 {
            margin-bottom: 1rem;
        }

        /* Password toggle */
        .password-wrapper {
            position: relative;
        }
        .password-wrapper .form-control {
            padding-right: 46px;
        }
        .btn-password-toggle {
            position: absolute;
            right: 0;
            top: 0;
            height: 45px;
            width: 44px;
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            z-index: 5;
        }
        .btn-password-toggle:hover { color: #a02626; }
        .btn-password-toggle:focus { outline: none; }
    </style>
</head>
<body class="hold-transition login-page">
<div class="login-box">
    <div class="card card-outline card-primary">
        <div class="card-header text-center">
            <img src="{{ url('public/dist/img/Cosecsa_Logo.png') }}" alt="COSECSA Logo">
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
                    9 => 'Examiner',
                ];
                $roleName = $roleNames[$roleId] ?? 'Unknown Role';
            @endphp

            <p class="login-box-msg">Login as <strong>{{ $roleName }}</strong></p>

            <form method="POST" action="{{ url('login') }}">
                @csrf
                <input type="hidden" name="role" value="{{ $roleId }}">

                <div class="mb-3">
                    <input type="text" class="form-control" name="email" placeholder="Email or Username" required>
                </div>

                <div class="mb-3 password-wrapper">
                    <input type="password" class="form-control" name="password" id="passwordInput"
                           placeholder="Password" required>
                    <button type="button" class="btn-password-toggle" id="togglePassword"
                            title="Show / hide password">
                        <i class="fas fa-eye" id="toggleIcon"></i>
                    </button>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Remember Me</label>
                </div>

                <div class="row mt-4">
                    <div class="col-6">
                        <a href="{{ url('forget-password') }}" class="btn btn-secondary btn-block d-flex align-items-center justify-content-center">
                            <i class="fas fa-unlock-alt mr-1"></i> Forget Password
                        </a>
                    </div>
                    <div class="col-6">
                        <button type="submit" class="btn btn-login btn-block">
                            Login
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    document.getElementById('togglePassword').addEventListener('click', function () {
        var input = document.getElementById('passwordInput');
        var icon  = document.getElementById('toggleIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
        }
    });
</script>
</body>
</html>
