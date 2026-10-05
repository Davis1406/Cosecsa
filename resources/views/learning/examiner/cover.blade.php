@extends($layout ?? 'layout.app')

@section('content')
@php
    $preview = $preview ?? false;
    $lr = $preview ? 'admin.exams.learning.preview' : 'examiner.learning';
@endphp
<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid lms" style="--brand: {{ $course['color'] }}">
            @include('learning.partials.flash')
            @includeWhen($preview, 'learning.partials.preview-bar')

            <div class="course-hero">
                <div class="hero-inner">
                    <div class="hero-copy">
                        <h1 class="sr-only">{{ $course['title'] }}</h1>
                        <p class="hero-desc">
                            {{ $course['description'] ?: 'Prepare to examine with fairness, consistency and confidence across COSECSA’s Membership and Fellowship examinations.' }}
                        </p>

                        <ul class="hero-meta">
                            <li>
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5h16M4 12h16M4 19h10"/></svg>
                                {{ count($modules) }} modules
                            </li>
                            <li>
                                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                                About {{ $total_minutes }} minutes
                            </li>
                            @if($quiz && $quiz['questions'])
                                <li>
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 11l3 3 8-8"/><path d="M20 12v7a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h9"/></svg>
                                    {{ $quiz['questions'] }}-question quiz · {{ round($quiz['passing']) }}% to pass
                                </li>
                            @endif
                        </ul>

                        @if($completed)
                            <div class="alert alert-success">🎉 Congratulations — you have completed this course!</div>
                            <div class="hero-actions">
                                @unless($preview)
                                    <a href="{{ route('examiner.learning.certificate') }}" class="btn btn-primary btn-lg">View your certificate</a>
                                @endunless
                                <a href="{{ route($lr.'.module', end($modules)['slug']) }}" class="btn btn-primary btn-lg">Review completion page</a>
                            </div>
                        @elseif($next_slug)
                            <div class="hero-actions">
                                <a href="{{ route($lr.'.module', $next_slug) }}" class="btn btn-primary btn-lg">
                                    {{ $progress_percent > 0 ? 'Resume course' : 'Start course' }}
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                                </a>
                            </div>
                        @endif
                    </div>
                    @if($course['cover_url'])
                        <div class="hero-cover">
                            <img src="{{ $course['cover_url'] }}" alt="" aria-hidden="true">
                        </div>
                    @endif
                </div>
            </div>

            @unless($preview)
            <div class="card card-pad mb-3 mt-4">
                <div class="flex-between mb-2">
                    <div>
                        <strong>Your progress</strong>
                        <div class="muted" style="font-size:13px;">{{ $progress_percent }}% complete</div>
                    </div>
                    <span class="badge-brand">{{ $progress_percent }}%</span>
                </div>
                <div class="progress-track"><div class="progress-fill" style="width: {{ $progress_percent }}%;"></div></div>
            </div>
            @endunless

            <div class="module-list mb-4 {{ $preview ? 'mt-4' : '' }}">
                @foreach($modules as $idx => $module)
                    <a href="{{ route($lr.'.module', $module['slug']) }}" class="module-row card">
                        <div class="module-num {{ $module['completed'] ? 'done' : '' }}">
                            @if($module['completed']) ✓ @else {{ str_pad($idx + 1, 2, '0', STR_PAD_LEFT) }} @endif
                        </div>
                        <div class="module-info">
                            <div class="module-title">{{ $module['title'] }}</div>
                            <div class="module-meta muted">
                                @if($module['type'] === 'quiz') Quiz · {{ $module['duration_minutes'] }} min
                                @elseif($module['type'] === 'completion') Completion
                                @elseif($module['type'] === 'acknowledgements') Acknowledgements
                                @else {{ $module['duration_minutes'] }} min
                                @endif
                            </div>
                        </div>
                        <div class="module-state">
                            @if($module['completed'])
                                <span class="badge-green">Completed</span>
                            @else
                                <span class="badge-gray">Not started</span>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
@include('learning.partials.styles')
<style>
    .lms .course-hero {
        background: var(--brand);
        background-image: linear-gradient(135deg, var(--brand), #6d0407);
        color: #fff; border-radius: 18px; overflow: hidden;
    }
    .lms .hero-inner { display: flex; align-items: center; gap: 32px; padding: 40px; animation: heroIn .5s ease both; }
    @keyframes heroIn { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: none; } }
    .lms .hero-copy { flex: 1; }
    .lms .hero-desc { font-size: 18px; font-weight: 500; opacity: .94; line-height: 1.6; margin-bottom: 18px; max-width: 580px; }
    .lms .hero-meta { list-style: none; display: flex; flex-wrap: wrap; gap: 8px; margin: 0 0 24px; padding: 0; }
    .lms .hero-meta li {
        display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; font-weight: 600;
        padding: 6px 12px; border-radius: 999px; background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.18);
    }
    .lms .hero-meta svg { width: 14px; height: 14px; fill: none; stroke: currentColor; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
    .lms .hero-actions { display: flex; align-items: center; flex-wrap: wrap; gap: 14px; }
    .lms .hero-cover img { width: 300px; height: 200px; object-fit: cover; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,.25); }
    .lms .btn-lg { display: inline-flex; align-items: center; gap: 8px; padding: 12px 30px; font-size: 16px; font-weight: 600; border-radius: 10px; }
    .lms .hero-copy .btn-primary { background: #fff; border-color: #fff; color: var(--brand); }
    .lms .hero-copy .btn-primary:hover { background: #f3f4f6; color: var(--brand); }

    .lms .module-list { display: flex; flex-direction: column; gap: 12px; }
    .lms .module-row {
        display: flex; flex-direction: row; align-items: center; gap: 16px; padding: 16px 20px; margin: 0; color: inherit;
        transition: border-color .15s ease, box-shadow .15s ease, transform .18s ease; animation: rowIn .4s ease both;
    }
    @keyframes rowIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: none; } }
    .lms .module-row:hover { border-color: var(--brand); box-shadow: 0 4px 16px rgba(16,24,40,.1); transform: translateY(-2px); color: inherit; }
    .lms .module-num {
        width: 40px; height: 40px; min-width: 40px; border-radius: 10px; background: #f1f5f9; color: #64748b;
        display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700;
    }
    .lms .module-num.done { background: #d1fae5; color: #065f46; }
    .lms .module-info { flex: 1; }
    .lms .module-title { font-size: 15px; font-weight: 600; color: #1e293b; }
    .lms .module-meta { font-size: 12.5px; margin-top: 3px; }
    @media (max-width: 760px) {
        .lms .hero-inner { flex-direction: column-reverse; align-items: stretch; padding: 28px 20px; }
        .lms .hero-cover img { width: 100%; }
    }
</style>
@endpush
