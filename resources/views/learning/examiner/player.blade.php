@extends('layout.app')

@section('content')
@php
    $position = collect($modules)->search(fn ($m) => $m['id'] === $module['id']) + 1;
    $preview = $preview ?? false;
    $lr = $preview ? 'admin.exams.learning.preview' : 'examiner.learning';
@endphp
<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid lms" style="--brand: {{ $course['color'] }}">
            @include('learning.partials.flash')
            @includeWhen($preview, 'learning.partials.preview-bar')
            <div class="reading-progress" aria-hidden="true"><span id="reading-progress-fill"></span></div>

            <div class="player-shell">
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

                <main class="player-main">
                    <div class="pm-top">
                        <button type="button" class="pm-modules-btn" data-drawer-open aria-controls="player-sidebar">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
                            Modules · {{ $progress_percent }}%
                        </button>
                        <div class="pm-breadcrumb">
                            <span>Module {{ $position }} of {{ count($modules) }}</span>
                            @if($module['type'] === 'quiz') <span class="pm-badge">Quiz</span> @endif
                            @if($completed)
                                <span class="pm-badge pm-badge-done">
                                    <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="3.2"><polyline points="20 6 9 17 4 12"/></svg>
                                    Completed
                                </span>
                            @endif
                        </div>
                        <h1 class="pm-title">{{ $module['title'] }}</h1>
                        @if($module['description'])
                            <div class="pm-desc">{!! \App\Support\LearningView::html($module['description']) !!}</div>
                        @endif
                    </div>

                    <div class="pm-content card card-pad">
                        @foreach(\App\Support\LearningView::segments($blocks) as $segment)
                            @isset($segment['units'])
                                @include('learning.blocks.video-grid', $segment)
                            @else
                                @include('learning.blocks._dispatch', ['block' => $segment['block']])
                            @endisset
                        @endforeach

                        @if($module['type'] === 'quiz')
                            <div class="pm-complete">
                                @if($preview)
                                    <a href="{{ route($lr.'.quiz', $module['slug']) }}" class="btn btn-primary">Preview quiz</a>
                                @elseif($completed)
                                    <a href="{{ route($lr.'.result', $module['slug']) }}" class="btn btn-primary">View quiz result</a>
                                @elseif($quiz_score !== null)
                                    <a href="{{ route($lr.'.quiz', $module['slug']) }}" class="btn btn-primary">Retake quiz</a>
                                    <a href="{{ route($lr.'.result', $module['slug']) }}" class="btn btn-light">Last attempt: {{ round($quiz_score) }}%</a>
                                @else
                                    <a href="{{ route($lr.'.quiz', $module['slug']) }}" class="btn btn-primary">Start quiz</a>
                                @endif
                            </div>
                        @endif
                    </div>

                    {{-- Prev / next. "Next" completes this module and moves on. --}}
                    <div class="pm-prevnext">
                        @if($prev)
                            <a href="{{ route($lr.'.module', $prev['slug']) }}" class="pn-card pn-prev">
                                <span class="pn-dir">← Previous</span>
                                <span class="pn-title">{{ $prev['title'] }}</span>
                            </a>
                        @else
                            <span></span>
                        @endif

                        @if($preview)
                            {{-- Preview never records progress: plain links instead of the advance form. --}}
                            <a href="{{ $next ? route($lr.'.module', $next['slug']) : route($lr) }}" class="pn-card pn-next pn-primary">
                                <span class="pn-dir">{{ $next ? 'Next module' : 'End of course' }}</span>
                                <span class="pn-title">{{ $next ? $next['title'] : 'Back to course home' }} →</span>
                            </a>
                        @elseif($module['type'] === 'quiz' && ! $completed)
                            <a href="{{ route($lr.'.quiz', $module['slug']) }}" class="pn-card pn-next pn-primary">
                                <span class="pn-dir">Continue</span>
                                <span class="pn-title">{{ $quiz_score !== null ? 'Retake quiz' : 'Start quiz' }} →</span>
                            </a>
                        @else
                            <form method="POST" action="{{ route($lr.'.advance', $module['slug']) }}" class="pn-form" data-advance>
                                @csrf
                                <button type="submit" class="pn-card pn-next pn-primary">
                                    <span class="pn-dir">{{ $next ? 'Next module' : 'All done' }}</span>
                                    <span class="pn-title">{{ $next ? $next['title'] : 'Finish course' }} →</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </main>
            </div>

            @include('learning.partials.lightbox')
        </div>
    </section>
</div>
@endsection

@push('styles')
@include('learning.partials.styles')
<style>
    .reading-progress {
        position: fixed; top: 0; left: 0; right: 0; height: 3px; z-index: 1000;
        background: transparent; pointer-events: none;
    }
    .reading-progress span {
        display: block; height: 100%; width: 100%;
        transform-origin: 0 50%; transform: scaleX(0);
        background: linear-gradient(90deg, var(--brand), #f2bd2c);
    }

    .player-shell { display: flex; align-items: flex-start; }

    .player-sidebar {
        position: sticky; top: 12px; align-self: flex-start;
        width: 290px; min-width: 290px; height: calc(100vh - 24px);
        border-radius: 14px; overflow: hidden; border: 1px solid #e6e9ef;
        background: #fff;
        border-right: 1px solid #e6e9ef;
        display: flex; flex-direction: column;
        box-shadow: 4px 0 18px rgba(16,24,40,.03);
    }
    .ps-head { padding: 22px 22px 18px; border-bottom: 1px solid #eef1f5; }
    .ps-head-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
    .ps-home {
        display: inline-flex; align-items: center; gap: 7px;
        font-size: 12.5px; font-weight: 600; color: var(--brand); letter-spacing: .2px;
    }
    .ps-close { display: none; border: 0; background: #f1f5f9; width: 30px; height: 30px; border-radius: 8px; font-size: 20px; line-height: 1; color: #475569; cursor: pointer; }
    .ps-course { font-size: 15px; font-weight: 800; color: #0f172a; line-height: 1.35; display: flex; align-items: center; gap: 10px; }
    .ps-logo { width: 36px; height: 36px; object-fit: contain; flex-shrink: 0; }
    .ps-progress { margin-top: 16px; }
    .ps-progress-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 7px; }
    .ps-progress-label { font-size: 12px; color: #64748b; font-weight: 600; }
    .ps-progress-pct { font-size: 12px; font-weight: 800; color: var(--brand); }
    .ps-progress .progress-track { height: 7px; background: #eef1f5; }
    .ps-progress .progress-fill { animation: fillIn 1s cubic-bezier(.22,1,.36,1) both .2s; transform-origin: 0 50%; }
    @keyframes fillIn { from { transform: scaleX(0); } to { transform: scaleX(1); } }

    .ps-nav { flex: 1; overflow-y: auto; padding: 12px 12px 16px; scrollbar-width: thin; scrollbar-color: #e2e8f0 transparent; }
    .ps-nav::-webkit-scrollbar { width: 6px; }
    .ps-nav::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 999px; }
    .ps-nav-label {
        font-size: 10.5px; font-weight: 800; color: #94a3b8;
        text-transform: uppercase; letter-spacing: 1.1px; padding: 4px 10px 10px;
    }
    .ps-item {
        display: flex; align-items: center; gap: 12px;
        padding: 10px 12px; border-radius: 12px;
        font-size: 13.5px; color: #475569; margin-bottom: 4px;
        transition: background .14s ease, transform .14s ease, box-shadow .14s ease;
        animation: psIn .45s cubic-bezier(.22,1,.36,1) both;
        animation-delay: calc(var(--i, 0) * 35ms);
    }
    @keyframes psIn { from { opacity: 0; transform: translateX(-10px); } to { opacity: 1; transform: none; } }
    .ps-item:hover { background: #f6f8fb; transform: translateX(2px); }
    .ps-item.active {
        background: rgba(153,6,10,.06);
        color: var(--brand); font-weight: 700;
        box-shadow: inset 3px 0 0 var(--brand);
    }
    .ps-num {
        width: 26px; height: 26px; min-width: 26px; border-radius: 8px;
        background: #f1f5f9; color: #64748b;
        display: flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700;
        transition: background .15s ease, color .15s ease, transform .2s ease;
    }
    .ps-item:hover .ps-num { transform: scale(1.08); }
    .ps-item.active .ps-num { background: var(--brand); color: #fff; }
    .ps-num.done { background: #10b981; color: #fff; }
    .ps-title { line-height: 1.35; flex: 1; }
    .ps-backdrop { display: none; }

    .player-main { flex: 1; min-width: 0; padding: 4px 0 60px 32px; }
    .pm-top { margin-bottom: 28px; max-width: 820px; animation: pmIn .6s cubic-bezier(.22,1,.36,1) both; }
    @keyframes pmIn { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }
    .pm-modules-btn {
        display: none; align-items: center; gap: 8px; margin-bottom: 14px;
        border: 1px solid #e2e8f0; background: #fff; color: #334155;
        padding: 8px 14px; border-radius: 999px; font-size: 13px; font-weight: 600; cursor: pointer;
    }
    .pm-breadcrumb {
        font-size: 12px; color: #94a3b8; font-weight: 700;
        text-transform: uppercase; letter-spacing: .8px; margin-bottom: 8px;
        display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
    }
    .pm-badge {
        display: inline-flex; align-items: center; gap: 4px;
        background: rgba(153,6,10,.1); color: var(--brand);
        padding: 2px 9px; border-radius: 999px; font-size: 10.5px; letter-spacing: .5px;
    }
    .pm-badge-done { background: #d1fae5; color: #047857; }
    .pm-title { font-size: 28px; font-weight: 800; color: #0f172a; letter-spacing: -.3px; line-height: 1.25; }
    .pm-desc { font-size: 14.5px; color: #64748b; margin-top: 10px; line-height: 1.6; max-width: 640px; }
    .pm-content {
        padding: 38px 40px; max-width: 820px;
        border-radius: 18px;
        box-shadow: 0 1px 3px rgba(16,24,40,.05), 0 12px 40px rgba(16,24,40,.06);
        animation: pmIn .7s cubic-bezier(.22,1,.36,1) both .08s;
    }
    .pm-content:hover {
        box-shadow: 0 1px 3px rgba(16,24,40,.05), 0 12px 40px rgba(16,24,40,.06);
        transform: none;
    }

    .pm-complete {
        margin-top: 16px; padding-top: 26px; border-top: 1px solid #eef1f5;
        display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
    }

    .pm-prevnext { display: flex; justify-content: space-between; gap: 16px; margin-top: 28px; max-width: 820px; }
    .pn-form { flex: 1; max-width: 46%; display: flex; }
    .pn-card {
        flex: 1; max-width: 46%; display: flex; flex-direction: column; gap: 3px;
        padding: 16px 20px; border-radius: 14px;
        border: 1px solid #e6e9ef; background: #fff;
        font: inherit; text-align: left; cursor: pointer;
        transition: transform .18s ease, box-shadow .18s ease, border-color .18s ease, opacity .2s ease;
    }
    .pn-form .pn-card { max-width: none; }
    .pn-card:hover { transform: translateY(-2px); box-shadow: 0 8px 22px rgba(16,24,40,.08); border-color: var(--brand); }
    .pn-prev { align-items: flex-start; }
    .pn-next { align-items: flex-end; text-align: right; }
    .pn-primary { background: var(--brand); border-color: var(--brand); }
    .pn-primary:hover { background: var(--brand); box-shadow: 0 10px 26px rgba(153,6,10,.28); }
    .pn-primary.is-loading { opacity: .75; pointer-events: none; }
    .pn-dir { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .7px; color: #94a3b8; }
    .pn-primary .pn-dir { color: rgba(255,255,255,.75); }
    .pn-title { font-size: 14px; font-weight: 700; color: #1e293b; }
    .pn-primary .pn-title { color: #fff; }

    /* Leaving the page after "Next" — content eases out while the request runs */
    body.is-leaving .pm-content, body.is-leaving .pm-top {
        opacity: .4; transform: translateY(-6px);
        transition: opacity .25s ease, transform .25s ease;
    }

    @media (max-width: 900px) {
        .player-sidebar {
            position: fixed; top: 0; left: 0; bottom: 0; z-index: 1100;
            transform: translateX(-105%); transition: transform .3s cubic-bezier(.22,1,.36,1);
            box-shadow: 12px 0 40px rgba(16,24,40,.18);
        }
        body.drawer-open .player-sidebar { transform: none; }
        .ps-backdrop {
            display: block; position: fixed; inset: 0; z-index: 1090;
            background: rgba(15,23,42,.45); opacity: 0; pointer-events: none; transition: opacity .3s ease;
        }
        body.drawer-open .ps-backdrop { opacity: 1; pointer-events: auto; }
        .ps-close { display: inline-block; }
        .pm-modules-btn { display: inline-flex; }
        .player-main { padding: 8px 0 60px; }
        .pm-title { font-size: 23px; }
        .pm-content { padding: 24px 18px; }
        .pm-prevnext { flex-direction: column-reverse; }
        .pn-card, .pn-form { max-width: none; }
        .block-image-aside { flex-direction: column; }
    }

    @media (prefers-reduced-motion: reduce) {
        .pm-top, .pm-content, .ps-item, .ps-progress .progress-fill { animation: none; }
        body.is-leaving .pm-content, body.is-leaving .pm-top { transform: none; }
    }
</style>
@endpush

@push('scripts')
@include('learning.partials.scripts')
<script>
    // ── Reading progress bar ───────────────────────────────────────────────
    (function () {
        const fill = document.getElementById('reading-progress-fill');
        if (!fill) return;
        let ticking = false;
        const update = () => {
            const max = document.documentElement.scrollHeight - window.innerHeight;
            fill.style.transform = 'scaleX(' + (max > 0 ? Math.min(1, window.scrollY / max) : 0) + ')';
            ticking = false;
        };
        const onScroll = () => { if (!ticking) { ticking = true; requestAnimationFrame(update); } };
        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', onScroll);
        update();
    })();

    // ── Auto-pause media when it leaves the viewport ───────────────────────
    (function () {
        const videos = [...document.querySelectorAll('.pm-content video')];
        if (!videos.length) return;

        const pause = v => { if (!v.paused && !v.ended) v.pause(); };

        if ('IntersectionObserver' in window) {
            const vio = new IntersectionObserver((entries) => {
                entries.forEach(entry => { if (!entry.isIntersecting) pause(entry.target); });
            }, { threshold: 0.3 });
            videos.forEach(v => vio.observe(v));
        }

        document.addEventListener('visibilitychange', () => { if (document.hidden) videos.forEach(pause); });
        window.addEventListener('pagehide', () => videos.forEach(pause));

        // Play only one video at a time — starting one pauses the rest
        videos.forEach(v => v.addEventListener('play', () => videos.forEach(other => { if (other !== v) pause(other); })));
    })();

    // ── "Next module": completes this module, then navigates ───────────────
    document.querySelectorAll('[data-advance]').forEach(form => {
        form.addEventListener('submit', () => {
            form.querySelector('.pn-card').classList.add('is-loading');
            document.body.classList.add('is-leaving');
        });
    });
    // Undo the leaving state if the page is restored from the back/forward cache
    window.addEventListener('pageshow', (e) => {
        if (!e.persisted) return;
        document.body.classList.remove('is-leaving');
        document.querySelectorAll('.pn-card.is-loading').forEach(b => b.classList.remove('is-loading'));
    });

    // ── Mobile module drawer ───────────────────────────────────────────────
    (function () {
        const open = () => document.body.classList.add('drawer-open');
        const close = () => document.body.classList.remove('drawer-open');
        document.querySelectorAll('[data-drawer-open]').forEach(b => b.addEventListener('click', open));
        document.querySelectorAll('[data-drawer-close]').forEach(b => b.addEventListener('click', close));
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
    })();
</script>
@endpush
