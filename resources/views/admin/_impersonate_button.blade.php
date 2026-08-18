{{--
    Usage: @include('admin._impersonate_button', ['userId' => $trainee->user_id])
    Optional: pass 'iconOnly' => true for a compact icon-only button (title
    tooltip instead of a text label) — used on pages with a minimal action bar.
--}}
@if(!empty($userId) && Auth::check() && Auth::user()->hasPermission('admin_users.manage'))
    <a href="{{ url('admin/impersonate/'.$userId) }}"
       class="btn btn-sm btn-outline-dark{{ !empty($iconOnly) ? ' btn-icon' : '' }}"
       @if(!empty($iconOnly)) title="Login as User" data-toggle="tooltip" @endif
       onclick="return confirm('Log in as this user? You can return to your admin account anytime from the banner at the top of the page.')">
        <i class="fas fa-user-secret{{ empty($iconOnly) ? ' mr-1' : '' }}"></i>{{ empty($iconOnly) ? ' Login as User' : '' }}
    </a>
@endif
