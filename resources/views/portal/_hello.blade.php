{{-- Greeting card + role switcher (multi-role users). Expects $roleLabel, $subtitle; uses $photo, $userRoles, $activeRole. --}}
@php
    $roleNames = [1=>'Admin',2=>'Trainee',3=>'Candidate',4=>'Programme Director',5=>'Country Representative',7=>'Fellow',8=>'Member',9=>'Examiner'];
    $roleIcons = [1=>'fa-shield-alt',2=>'fa-user-graduate',3=>'fa-user-edit',4=>'fa-chalkboard-teacher',5=>'fa-globe-africa',7=>'fa-award',8=>'fa-id-badge',9=>'fa-user-md'];
@endphp
<div class="portal-hello">
    @if(!empty($photo))
        <img src="{{ $photo }}" alt="">
    @else
        <div class="portal-avatar"><i class="fas {{ $roleIcons[$activeRole] ?? 'fa-user' }}"></i></div>
    @endif
    <div>
        <h2>Welcome, {{ auth()->user()->name }}</h2>
        <div class="portal-sub">{{ $roleLabel }}@if(!empty($subtitle)) &nbsp;·&nbsp; {{ $subtitle }}@endif</div>
    </div>
    @if(!empty($userRoles) && count($userRoles) > 1)
        <div class="ml-auto d-flex align-items-center flex-wrap" style="gap:.35rem;">
            <span style="font-size:.75rem;font-weight:700;color:#a02626;letter-spacing:.5px;text-transform:uppercase;"><i class="fas fa-exchange-alt mr-1"></i>Switch role</span>
            @foreach($userRoles as $r)
                @if($r != $activeRole)
                    <form action="{{ route('switch.role') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="role" value="{{ $r }}">
                        <button type="submit" class="btn btn-sm" style="background:#fff8e1;border:1px solid #e8d48b;color:#856404;font-size:.78rem;border-radius:20px;padding:3px 12px;">
                            <i class="fas {{ $roleIcons[$r] ?? 'fa-user' }} mr-1"></i>{{ $roleNames[$r] ?? 'Role '.$r }}
                        </button>
                    </form>
                @endif
            @endforeach
        </div>
    @endif
</div>
