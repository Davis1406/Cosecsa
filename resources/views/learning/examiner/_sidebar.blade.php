{{-- Module sidebar of the examiner player (needs $modules, $module, $course, $progress_percent, $lr). Shared by player.blade.php and the coded modules. --}}
{{-- Module sidebar (off-canvas drawer on small screens) --}}
<aside class="player-sidebar" id="player-sidebar" aria-label="Course modules">
    <div class="ps-head">
        <div class="ps-head-row">
            <a href="{{ route($lr) }}" class="ps-home">
                <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                Course home
            </a>
            <button type="button" class="ps-close" data-drawer-close aria-label="Close modules">×</button>
        </div>
        <div class="ps-course">
            @if($course['logo_url'])<img src="{{ $course['logo_url'] }}" alt="" class="ps-logo">@endif
            {{ $course['title'] }}
        </div>
        <div class="ps-progress">
            <div class="ps-progress-top">
                <span class="ps-progress-label">Course progress</span>
                <span class="ps-progress-pct">{{ $progress_percent }}%</span>
            </div>
            <div class="progress-track"><div class="progress-fill" style="width: {{ $progress_percent }}%;"></div></div>
        </div>
    </div>
    <nav class="ps-nav">
        <div class="ps-nav-label">Modules</div>
        @foreach($modules as $i => $m)
            <a href="{{ route($lr.'.module', $m['slug']) }}"
               class="ps-item {{ $m['id'] === $module['id'] ? 'active' : '' }}" style="--i: {{ $i }}"
               @if($m['id'] === $module['id']) aria-current="page" @endif>
                <span class="ps-num {{ $m['completed'] ? 'done' : '' }}">
                    @if($m['completed'])
                        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                    @else
                        {{ $i + 1 }}
                    @endif
                </span>
                <span class="ps-title">{{ $m['title'] }}</span>
            </a>
        @endforeach
    </nav>
</aside>
<div class="ps-backdrop" data-drawer-close></div>
