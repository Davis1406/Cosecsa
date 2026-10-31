{{-- Look of the login page, for the forgot-password and reset-password pages. --}}
<style>
    .login-box { width: 400px; margin: 7% auto; }
    @media (max-width: 768px) {
        .login-box { width: 95%; margin: 5% auto; display: flex; flex-direction: column; justify-content: center; }
    }
    @media (max-width: 480px) { .login-box { width: 98%; margin: 2% auto; } }
    .card.card-outline.card-primary { border-color: #FEC503; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
    .card-header img { max-width: 80px; height: auto; }
    .login-box input.form-control { font-size: 16px; height: 45px; }
    .btn-login { background-color: #a02626; border-color: #a02626; color: #fff; height: 45px; font-size: 16px; }
    .btn-login:hover { background-color: #870f0f; border-color: #870f0f; color: #FEC503; }
    .login-box-msg { font-size: 1.05rem; margin-bottom: 1.25rem; }
    body.login-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
    @media (max-width: 768px) { body.login-page { padding: 10px; align-items: flex-start; padding-top: 5vh; } }
    .password-wrapper { position: relative; }
    .password-wrapper .form-control { padding-right: 46px; }
    .btn-password-toggle { position: absolute; right: 0; top: 0; height: 45px; width: 44px; background: none; border: none; color: #6c757d; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 15px; z-index: 5; }
    .btn-password-toggle:hover { color: #a02626; }
    .btn-password-toggle:focus { outline: none; }
    .auth-help { font-size: .85rem; color: #6c757d; }
    .auth-back a { color: #a02626; }
</style>
