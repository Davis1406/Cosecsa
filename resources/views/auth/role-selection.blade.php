<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Select Role | COSECSA MIS</title>
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
    <link rel="icon" href="{{ asset('dist/img/Cosecsa_Logo_sm.png') }}">
    
    <style>
        .login-box {
            width: 400px;
            margin: 7% auto;
        }
        
        /* Mobile styles */
        @media (max-width: 768px) {
            .login-box {
                width: 95%;
                margin: 5% auto;
                min-height: calc(100vh - 10%);
                display: flex;
                flex-direction: column;
                justify-content: center;
            }
            
            .card {
                margin: 0;
                min-height: auto;
            }
            
            .card-header {
                padding: 2rem 1rem;
            }
            
            .card-body {
                padding: 2rem 1.5rem;
            }
            
            .login-box-msg {
                font-size: 1.1rem;
                margin-bottom: 2rem;
            }
        }
        
        /* Extra small mobile devices */
        @media (max-width: 480px) {
            .login-box {
                width: 98%;
                margin: 2% auto;
            }
            
            .card-header {
                padding: 1.5rem 1rem;
            }
            
            .card-body {
                padding: 1.5rem 1rem;
            }
        }
        
        .custom-select-role {
            display: block;
            width: 100%;
            height: 46px;
            padding: 0 2.6rem 0 .9rem;
            border-radius: 8px;
            border: 1px solid #ced4da;
            background-color: #fff;
            color: #2d3748;
            font-size: 1rem;
            line-height: 46px;
            cursor: pointer;
            -webkit-appearance: none; -moz-appearance: none; appearance: none;
            /* Chevron inset from the edge (the native arrow sat flush right). */
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1.5l5 5 5-5' fill='none' stroke='%236b7280' stroke-width='1.6' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right .9rem center;
            background-size: 12px 8px;
        }
        .custom-select-role::-ms-expand { display: none; }
        .form-group label[for="role"] { font-weight: 600; margin-bottom: .4rem; }
        
        .custom-select-role:focus {
            border-color: #a02626;
            box-shadow: 0 0 0 0.2rem rgba(160, 38, 38, 0.25);
            outline: none;
        }
        
        .btn-custom {
            background-color: #a02626;
            border-color: #a02626;
            color: #fff;
            height: 46px;
            padding: 0 20px;
            font-size: 1rem;
            display: flex; align-items: center; justify-content: center; gap: .45rem;
        }
        
        .btn-custom:hover {
            background-color: #870f0f;
            border-color: #870f0f;
            color: #FEC503;
        }
        
        .btn-secondary {
            height: 46px;
            padding: 0 20px;
            font-size: 1rem;
            display: flex; align-items: center; justify-content: center; gap: .45rem;
        }
        
        .card.card-outline.card-primary {
            border-color: #FEC503;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        
        .form-group label {
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 10px;
        }
        
        .h1 {
            font-size: 2rem;
        }
        
        /* Logo sizing */
        .card-header img {
            max-width: 80px;
            height: auto;
        }
        
        @media (max-width: 768px) {
            .card-header img {
                max-width: 100px;
            }
            
            .h1 {
                font-size: 2.2rem;
            }
        }
        
        /* Button improvements for mobile */
        .row.mt-4 .col-6 {
            padding-left: 5px;
            padding-right: 5px;
        }
        
        @media (max-width: 480px) {
            .row.mt-4 {
                margin-top: 2rem !important;
            }
        }
        
        /* Ensure full viewport height usage on mobile */
        body.login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        @media (max-width: 768px) {
            body.login-page {
                padding: 10px;
                align-items: flex-start;
                padding-top: 5vh;
            }
        }
    </style>
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
                                <option value="{{ $role['id'] }}" {{ (int) session('previous_role') === $role['id'] ? 'selected' : '' }}>
                                    {{ $role['name'] }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                
                <div class="row mt-4">
                    <div class="col-6">
                        <a href="{{ url('logout') }}" class="btn btn-secondary btn-block">
                            <i class="fas fa-sign-out-alt"></i> Reset Role
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