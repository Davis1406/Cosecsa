@extends($layout ?? 'layout.app')

@section('content')
@php
    $position = collect($modules)->search(fn ($m) => $m['id'] === $module['id']) + 1;
    $preview = $preview ?? false;
    $lr = $preview ? 'admin.exams.learning.preview' : 'examiner.learning';
@endphp
<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid lms" style="--brand: {{ $module['hero']['c1'] ?? $course['color'] }}">
            @include('learning.partials.flash')
            @includeWhen($preview, 'learning.partials.preview-bar')
            <div class="reading-progress" aria-hidden="true"><span id="reading-progress-fill"></span></div>

            <div class="player-shell">
                @include('learning.examiner._sidebar')

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
                        @if(empty($module['hero']))
                            <h1 class="pm-title">{{ $module['title'] }}</h1>
                            @if($module['description'])
                                <div class="pm-desc">{!! \App\Support\LearningView::html($module['description']) !!}</div>
                            @endif
                        @endif
                    </div>
                    @include('learning.partials.hero')

                    <div class="pm-content card card-pad">
                        @php $topicLayout = \App\Support\LearningView::topics($blocks); @endphp
                        @if($topicLayout)
                            @include('learning.partials.topics', ['topicLayout' => $topicLayout, 'topicsRead' => $topics_read ?? [], 'saveUrl' => $preview ? null : route($lr.'.topics', $module['slug'])])
                        @else
                            @foreach(\App\Support\LearningView::segments($blocks) as $segment)
                                @include('learning.partials.segment', ['segment' => $segment])
                            @endforeach
                        @endif

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
                            @if($next)
                                <a href="{{ route($lr.'.module', $next['slug']) }}" class="pn-card pn-next pn-primary">
                                    <span class="pn-title">{{ $next['title'] }} →</span>
                                </a>
                            @else
                                {{-- Demo of the examiner's completion: opens the certificate modal, records nothing. --}}
                                <button type="button" class="pn-card pn-next pn-primary" data-demo-certificate="{{ route('admin.exams.learning.certificate.modal') }}">
                                    <span class="pn-title">🎓 Get your certificate (demo)</span>
                                </button>
                            @endif
                        @elseif($module['type'] === 'quiz' && ! $completed)
                            <a href="{{ route($lr.'.quiz', $module['slug']) }}" class="pn-card pn-next pn-primary">
                                <span class="pn-dir">Continue</span>
                                <span class="pn-title">{{ $quiz_score !== null ? 'Retake quiz' : 'Start quiz' }} →</span>
                            </a>
                        @else
                            <form method="POST" action="{{ route($lr.'.advance', $module['slug']) }}" class="pn-form" @if($next) data-advance @else data-certificate @endif>
                                @csrf
                                <button type="submit" class="pn-card pn-next pn-primary">
                                    <span class="pn-title">{{ $next ? $next['title'].' →' : '🎓 Get your certificate' }}</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </main>
            </div>

            @include('learning.partials.lightbox')

            @include('learning.partials.certificate-modal-shell')
        </div>
    </section>
</div>
@endsection

@push('styles')
@include('learning.examiner._shell-styles')
@endpush

@push('scripts')
@include('learning.partials.certificate-script')
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

    // ── "Next": completes this module, then navigates ────────────────────────
    document.querySelectorAll('[data-advance]').forEach(form => {
        form.addEventListener('submit', () => {
            form.querySelector('.pn-card').classList.add('is-loading');
            document.body.classList.add('is-leaving');
        });
    });
    // ── Last module: "Get your certificate" completes the course, then opens the certificate in a modal ──
    document.querySelectorAll('form[data-certificate]').forEach(form => {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = form.querySelector('.pn-card');
            btn.classList.add('is-loading');

            try {
                const done = await fetch(form.action, {
                    method: 'POST', body: new FormData(form), credentials: 'same-origin',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!done.ok) throw new Error('advance failed');
                const info = await done.json();
                await window.lmsOpenCertificateModal(info.modal);
                btn.classList.remove('is-loading');
            } catch (err) {
                // Fall back to a normal submit, which lands on the certificate page.
                form.submit();
            }
        });
    });

    // ── Admin preview: demo of the completion modal (no progress is recorded) ──
    document.querySelectorAll('[data-demo-certificate]').forEach(btn => {
        btn.addEventListener('click', async () => {
            btn.classList.add('is-loading');
            try { await window.lmsOpenCertificateModal(btn.dataset.demoCertificate); }
            catch (err) { alert('The certificate demo could not be loaded.'); }
            btn.classList.remove('is-loading');
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
