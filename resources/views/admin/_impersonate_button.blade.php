{{-- Usage: @include('admin._impersonate_button', ['userId' => $trainee->user_id]) --}}
@if(!empty($userId) && Auth::check() && Auth::user()->hasPermission('admin_users.manage'))
    <a href="{{ url('admin/impersonate/'.$userId) }}" class="btn btn-sm btn-outline-dark"
       onclick="return confirm('Log in as this user? You can return to your admin account anytime from the banner at the top of the page.')">
        <i class="fas fa-user-secret mr-1"></i> Login as User
    </a>
@endif
