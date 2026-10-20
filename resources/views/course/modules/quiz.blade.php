{{-- Examiner quiz (final design pack), driven by cosecsa-api: the sections and questions come from the quiz module's blocks;
     each answer is checked on the server, and the score is saved when all questions are answered. --}}
@extends('course.layout', ['mod'=>'quiz','slug'=>'quiz','key'=>'','lite'=>true,'moduleJs'=>true])

@section('title', 'Examiner Quiz')

@php
    $total = count($questions);
    $passMark = (int) round($passing);
    $needed = (int) ceil($total * $passMark / 100);
    $byId = collect($questions)->values()->mapWithKeys(fn ($q, $i) => [$q['id'] => ['n' => $i + 1] + $q]);
    $icons = ['i17', 'i9', 'i19', 'i14', 'i7', 'i4'];
    $quizConfig = [
        'checkUrl' => route('examiner.learning.check', $module['slug']),
        'submitUrl' => route('examiner.learning.submit', $module['slug']),
        'csrf' => csrf_token(),
        'total' => $total,
        'passMark' => $passMark,
        'needed' => $needed,
        'nextUrl' => $nextUrl ?? null,
        'nextTitle' => $nextTitle ?? null,
        'sections' => collect($sections)->values()->map(fn ($s) => collect($s['question_ids'])->map(fn ($id) => [
            'id' => $id, 'n' => $byId[$id]['n'], 'title' => $byId[$id]['title'],
            'answers' => collect($byId[$id]['answers'])->map(fn ($a) => ['id' => $a['id'], 'title' => $a['title']])->all(),
        ])->all())->all(),
    ];
@endphp

@section('course-body')
<div class="app">
<div class="bar0"><span class="brand"><img class="crest" alt="COSECSA crest" src="{{ asset('images/course/cosecsa-crest.png') }}">COSECSA · Examiner course</span></div>
<div class="hero"><svg class="lens" viewBox="0 0 200 200" aria-hidden="true" fill="none" stroke="#fff" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"><path d="M60 70c0-22 80-22 80 0 0 22-40 20-40 44M100 150h.01" stroke-width="12"/></svg><div class="k">Quiz · {{ $total }} questions</div><h1>Examiner quiz</h1><p class="l">{{ $total }} questions across {{ count($sections) }} sections. Tap an answer to see if you are right and why. The pass mark is {{ $passMark }}% ({{ $needed }} of {{ $total }}). You can answer in any order.@if($last_attempt) Your last attempt: {{ round($last_attempt['score']) }}%.@endif</p>
<div class="prog"><span id="pc">0 of {{ $total }} answered</span><span>Saved to your account</span></div><div class="bar"><i id="fill"></i></div></div>
<div class="topics" id="topics">
@foreach($sections as $i => $s)

<div class="topic" data-i="{{ $i }}"><button class="th" type="button" aria-expanded="false"><span class="ic"><svg><use href="#{{ $icons[$i % count($icons)] }}"/></svg></span><span><b>{{ $s['title'] }}</b><small>{{ count($s['question_ids']) }} {{ count($s['question_ids']) === 1 ? 'question' : 'questions' }}</small></span><span class="ch"></span></button><div class="pn"><div class="qlist" data-s="{{ $i }}"></div></div></div>
@endforeach

</div>
<div class="score" id="score"><h2>Quiz complete</h2><p id="res"></p><p>The explanation under each question tells you why the answer is right. When you have passed, use the course menu to finish the course.</p><a id="next" class="cs-next" href="#" hidden></a> <button id="again" type="button">Start again</button></div>
</div>
<script>window.CS_QUIZ = @json($quizConfig);</script>
@endsection
