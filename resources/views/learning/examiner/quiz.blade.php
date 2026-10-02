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
            <div class="quiz-wrap">
                <a href="{{ route($lr.'.module', $module['slug']) }}" class="back-link">← Back to the course</a>
                <div class="card card-pad">
                    <div class="quiz-head">
                        <div class="flex-between mb-2">
                            <h1 class="quiz-title">{{ $module['title'] }}</h1>
                            <span class="badge-brand">{{ count($questions) }} questions</span>
                        </div>
                        @if($module['description'])
                            <div class="quiz-desc muted">{!! \App\Support\LearningView::html($module['description']) !!}</div>
                        @endif
                        @if($last_attempt)
                            <div class="alert alert-info quiz-retake">
                                <strong>Retake — attempt {{ $last_attempt['attempts'] + 1 }}.</strong>
                                Your last score was {{ round($last_attempt['score']) }}%; you need {{ round($passing) }}% to pass.
                            </div>
                        @endif
                        <div class="progress-track mt-2"><div class="progress-fill" id="quiz-progress" style="width:0%"></div></div>
                    </div>

                    {{-- Preview has no submit route: answers are never sent or scored. --}}
                    <form method="POST" action="{{ $preview ? '#' : route($lr.'.submit', $module['slug']) }}" id="quiz-form" @if($preview) onsubmit="return false" @endif>
                        @csrf
                        <div class="quiz-questions">
                            @foreach($questions as $q)
                                <div class="quiz-question" data-question="{{ $q['id'] }}">
                                    <div class="q-count">Question {{ $loop->iteration }} of {{ count($questions) }}</div>
                                    <div class="q-text">{!! \App\Support\LearningView::html($q['title']) !!}</div>
                                    <div class="q-options">
                                        @foreach($q['answers'] as $a)
                                            <label class="q-option">
                                                <input type="radio" name="question_{{ $q['id'] }}" value="{{ $a['id'] }}" required>
                                                <span class="q-option-label">{!! \App\Support\LearningView::html($a['title']) !!}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="quiz-footer">
                            <span class="muted" id="quiz-answered">0 / {{ count($questions) }} answered</span>
                            @if($preview)
                                <button type="button" class="btn btn-primary btn-lg" disabled title="Submitting is turned off in preview">Submit quiz</button>
                            @else
                                <button type="submit" class="btn btn-primary btn-lg" id="quiz-submit">Submit quiz</button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
@include('learning.partials.styles')
<style>
    .quiz-wrap { max-width: 760px; margin: 0 auto; }
    .quiz-title { font-size: 24px; font-weight: 800; color: #1e293b; }
    .quiz-desc { font-size: 14px; margin-top: 6px; }
    .quiz-questions { margin-top: 24px; }
    .quiz-question { display: none; padding: 8px 0 24px; }
    .quiz-question.active { display: block; }
    .quiz-question.animate-in { animation: qIn .4s ease; }
    @keyframes qIn { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: none; } }
    .quiz-retake { margin: 14px 0 0; }
    .q-count { font-size: 12px; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: .6px; margin-bottom: 10px; }
    .q-text { font-size: 17px; font-weight: 600; color: #1e293b; line-height: 1.5; margin-bottom: 18px; }
    .q-options { display: flex; flex-direction: column; gap: 10px; }
    .q-option {
        display: flex; align-items: center; gap: 12px;
        border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 14px 16px;
        cursor: pointer; transition: all .15s ease; background: #fff;
    }
    .q-option:hover { border-color: var(--brand); background: rgba(153,6,10,.02); }
    .q-option:has(input:checked) { border-color: var(--brand); background: rgba(153,6,10,.05); }
    .q-option input { accent-color: var(--brand); }
    .q-option-label { font-size: 15px; color: #334155; }
    .quiz-footer { margin-top: 24px; padding-top: 20px; border-top: 1px solid #eef1f5; display: flex; align-items: center; justify-content: space-between; }
</style>
<style>
    .lms .back-link { display: inline-block; font-size: 13px; font-weight: 600; color: var(--brand); margin-bottom: 12px; }
    .lms .btn-lg { padding: 10px 26px; font-weight: 600; border-radius: 10px; }
    .lms .quiz-question { min-height: 0; }
</style>
@endpush

@push('scripts')
<script>
    const questions = [...document.querySelectorAll('.quiz-question')];
    const progressFill = document.getElementById('quiz-progress');
    const answeredCount = document.getElementById('quiz-answered');
    let current = 0;
    let answered = 0;

    const refreshAnswered = () => {
        answered = questions.filter(q => q.querySelector('input:checked')).length;
        answeredCount.textContent = answered + ' / ' + questions.length + ' answered';
    };

    const show = (i) => {
        current = Math.max(0, Math.min(questions.length - 1, i));
        questions.forEach((q, idx) => {
            q.classList.toggle('active', idx === current);
            if (idx === current) q.classList.remove('animate-in');
            // Retrigger entrance animation
            void q.offsetWidth;
            if (idx === current) q.classList.add('animate-in');
        });
        progressFill.style.width = ((current + 1) / questions.length * 100) + '%';
    };

    questions.forEach((q, i) => {
        q.querySelectorAll('input[type=radio]').forEach(input => {
            input.addEventListener('change', () => {
                refreshAnswered();
                setTimeout(() => show(i + 1), 250);
            });
        });
    });

    show(0);
    refreshAnswered();
</script>
@endpush
