@extends('layout.app')

@section('content')
<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid lms" style="--brand: {{ $course['color'] }}">
            @include('learning.partials.flash')
            <div class="result-wrap">
                <div class="card card-pad result-hero {{ $passed ? 'passed' : 'failed' }}">
                    <div class="result-score">
                        <div class="score-ring {{ $passed ? 'passed' : 'failed' }}"><span>{{ round($score) }}%</span></div>
                    </div>
                    <div class="result-copy">
                        <h1>{{ $passed ? 'Congratulations, you passed!' : 'Keep going — you can do better.' }}</h1>
                        <p class="muted">
                            You scored <strong>{{ round($score) }}%</strong> — passing mark is {{ round($passing) }}%.
                            @if($attempts > 1) Attempt {{ $attempts }}. @endif
                            @unless($passed)
                                Questions you got wrong are marked below — revisit the modules, then retake the quiz.
                                The correct answers are shown once you pass.
                            @endunless
                        </p>
                        <div class="result-actions">
                            @if($passed)
                                @if($next)
                                    <a href="{{ route('examiner.learning.module', $next['slug']) }}" class="btn btn-primary">Continue → {{ $next['title'] }}</a>
                                @else
                                    <a href="{{ route('examiner.learning') }}" class="btn btn-primary">Back to course home</a>
                                    @if($course_complete ?? false)
                                        <a href="{{ route('examiner.learning.certificate') }}" class="btn btn-light">View your certificate</a>
                                    @endif
                                @endif
                            @else
                                <a href="{{ route('examiner.learning.quiz', $module['slug']) }}" class="btn btn-primary">Retake quiz</a>
                                <a href="{{ route('examiner.learning') }}" class="btn btn-light">Review modules</a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="review-list">
                    @foreach($questions as $q)
                        <div class="card card-pad review-item {{ $q['is_correct'] ? 'ok' : 'no' }}">
                            <div class="review-head">
                                <span class="review-badge {{ $q['is_correct'] ? 'badge-green' : 'badge-red' }}">{{ $q['is_correct'] ? '✓ Correct' : '✗ Incorrect' }}</span>
                                <div class="q-text">{!! \App\Support\LearningView::html($q['title']) !!}</div>
                            </div>
                            <div class="review-options">
                                @foreach($q['answers'] as $a)
                                    @php
                                        // correct_ids is only filled in by the API once the quiz is passed
                                        $isCorrect = in_array($a['id'], $q['correct_ids'], true);
                                        $isSelected = $q['selected'] === $a['id'];
                                        $isWrong = $isSelected && ! $q['is_correct'];
                                        $isRight = $isSelected && $q['is_correct'];
                                    @endphp
                                    <div class="review-option {{ $isCorrect || $isRight ? 'correct' : '' }} {{ $isWrong ? 'wrong' : '' }}">
                                        <span class="ro-icon">@if($isCorrect || $isRight) ✓ @elseif($isWrong) ✗ @endif</span>
                                        <span>{!! \App\Support\LearningView::html($a['title']) !!}</span>
                                    </div>
                                @endforeach
                            </div>
                            @if($q['feedback'])
                                <div class="review-feedback">{!! \App\Support\LearningView::html($q['feedback']) !!}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
@include('learning.partials.styles')
<style>
    .result-wrap { max-width: 780px; margin: 0 auto; }
    .result-hero { display: flex; gap: 28px; align-items: center; padding: 36px; }
    .score-ring {
        width: 130px; height: 130px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 30px; font-weight: 800;
    }
    .score-ring.passed { background: #ecfdf5; color: #065f46; border: 8px solid #10b981; }
    .score-ring.failed { background: #fef2f2; color: #991b1b; border: 8px solid #f87171; }
    .result-copy h1 { font-size: 22px; font-weight: 800; color: #1e293b; margin-bottom: 8px; }
    .result-copy p { font-size: 14.5px; line-height: 1.6; }
    .result-actions { margin-top: 18px; }

    .review-list { margin-top: 24px; display: flex; flex-direction: column; gap: 14px; }
    .review-item { padding: 22px 24px; border-left: 4px solid; }
    .review-item.ok { border-left-color: #10b981; }
    .review-item.no { border-left-color: #f87171; }
    .review-head { margin-bottom: 12px; }
    .review-badge { margin-bottom: 8px; }
    .review-item .q-text { font-size: 15px; font-weight: 600; color: #1e293b; line-height: 1.5; }
    .review-options { display: flex; flex-direction: column; gap: 6px; }
    .review-option {
        display: flex; align-items: center; gap: 10px;
        padding: 8px 12px; border-radius: 8px; font-size: 14px;
        border: 1px solid #e2e8f0; background: #f8fafc; color: #475569;
    }
    .review-option.correct { background: #ecfdf5; border-color: #a7f3d0; color: #065f46; }
    .review-option.wrong { background: #fef2f2; border-color: #fecaca; color: #991b1b; }
    .ro-icon { width: 20px; text-align: center; font-weight: 700; }
    .review-feedback {
        margin-top: 12px; padding: 12px 14px; border-radius: 8px;
        background: #f1f5f9; font-size: 13.5px; line-height: 1.6; color: #334155;
    }
</style>
@endpush
