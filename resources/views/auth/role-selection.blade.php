<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Select Role | COSECSA MIS</title>
    <link rel="stylesheet" href="{{ url('public/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ url('public/dist/css/adminlte.min.css') }}">
    <link rel="icon" href="{{ url('public/dist/img/Cosecsa_Logo.png') }}">

    <style>
        .login-box {
            width: 400px;
        }
        .custom-select-role {
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ced4da;
            width: 100%;
            font-size: 1rem;
        }
        .custom-select-role:focus {
            border-color: #a02626;
            box-shadow: 0 0 0 0.2rem rgba(160, 38, 38, 0.25);
            outline: none;
        }
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
            <img src="{{ url('public/dist/img/Cosecsa_Logo.png') }}" alt="COSECSA Logo" style="max-width:80px;">
            <br>
            <a href="#" class="h1" style="color: #a02626;"><b>COSECSA-MIS</b></a>
        </div>
        <div class="card-body">
            <p class="login-box-msg">Please select your role to continue</p>

            @include('_message')

            <form action="{{ url('select-role') }}" method="POST" id="roleForm">
                @csrf

                <div class="form-group">
                    <label for="role">Select Role</label>
                    <select name="role" id="role" class="custom-select-role" required>
                        <option value="">-- Choose Role --</option>
                        @if(isset($availableRoles) && count($availableRoles))
                            @foreach($availableRoles as $role)
                                <option value="{{ $role['id'] }}">
                                    {{ $role['name'] }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="row mt-4">
                    <div class="col-6">
                        <a href="{{ url('logout') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                    <div class="col-6">
                        <button type="submit" class="btn btn-custom btn-block">
                            Continue <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
