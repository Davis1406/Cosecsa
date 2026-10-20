{{--
  Page shell for the coded examiner-course pages (final design pack: resources/views/course/modules/*).
  Keeps the MIS module sidebar and drawer; the page itself (banner, topic cards, quiz) fills the main area.
  The pages extend course.layout, which extends this view (config/course.php).
--}}
@extends($layout ?? 'layout.app')

@section('content')
@php
    $preview = $preview ?? false;
    $lr = $preview ? 'admin.exams.learning.preview' : 'examiner.learning';
    $position = collect($modules)->search(fn ($m) => $m['id'] === $module['id']) + 1;
@endphp
<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid lms coded-course" style="--brand: {{ $module['hero']['c1'] ?? $course['color'] }}">
            @include('learning.partials.flash')
            @includeWhen($preview, 'learning.partials.preview-bar')

            <div class="player-shell">
                @include('learning.examiner._sidebar')

                <main class="player-main">
                    <div class="pm-top" style="margin-bottom: 14px;">
                        <button type="button" class="pm-modules-btn" data-drawer-open aria-controls="player-sidebar">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
                            Modules · {{ $progress_percent }}%
                        </button>
                        <div class="pm-breadcrumb">
                            <span>Module {{ $position }} of {{ count($modules) }}</span>
                            @if($module['type'] === 'quiz') <span class="pm-badge">Quiz</span> @endif
                            @if($completed ?? false)
                                <span class="pm-badge pm-badge-done">
                                    <svg viewBox="0 0 24 24" width="11" height="11" fill="none" stroke="currentColor" stroke-width="3.2"><polyline points="20 6 9 17 4 12"/></svg>
                                    Completed
                                </span>
                            @endif
                        </div>
                    </div>

                    @yield('course-shell')
                </main>
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
@include('learning.examiner._shell-styles')
<style>
    /* The page brings its own banner and typography: give it the whole main area. */
    .coded-course .player-main { min-width: 0; }
    .coded-course .cs-course .app { max-width: none; }
</style>
@endpush

@push('scripts')
<script>
    // Mobile module drawer
    (function () {
        const open = () => document.body.classList.add('drawer-open');
        const close = () => document.body.classList.remove('drawer-open');
        document.querySelectorAll('[data-drawer-open]').forEach(b => b.addEventListener('click', open));
        document.querySelectorAll('[data-drawer-close]').forEach(b => b.addEventListener('click', close));
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') close(); });
    })();
</script>
@endpush
