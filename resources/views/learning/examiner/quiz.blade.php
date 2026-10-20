@extends($layout ?? 'layout.app')

@section('content')
@php
    $preview = $preview ?? false;
    $lr = $preview ? 'admin.exams.learning.preview' : 'examiner.learning';
    $byId = collect($questions)->keyBy('id');
    $total = count($questions);
    // Sections (topic dividers in the quiz module); one open card when the quiz has none.
    $sections = collect($sections ?? [])->filter(fn ($s) => ! empty($s['question_ids']))->values();
    if ($sections->isEmpty()) {
        $sections = collect([['title' => 'All questions', 'subtitle' => '', 'question_ids' => collect($questions)->pluck('id')->all()]]);
    }
    $checkUrl = $preview ? route('admin.exams.learning.preview.quiz.check', $module['slug']) : route('examiner.learning.check', $module['slug']);
    $submitUrl = $preview ? null : route('examiner.learning.submit', $module['slug']);
    $letters = range('A', 'Z');
    $number = 0;
@endphp
<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid lms" style="--brand: {{ $module['hero']['c1'] ?? $course['color'] }}">
            @include('learning.partials.flash')
            @includeWhen($preview, 'learning.partials.preview-bar')
            <div class="qz-wrap">
                <a href="{{ route($lr.'.module', $module['slug']) }}" class="back-link">← Back to the course</a>
                @include('learning.partials.hero')

                @if($module['description'])
                    <div class="muted mb-3" style="font-size:15px">{!! \App\Support\LearningView::html($module['description']) !!}</div>
                @endif
                @if($last_attempt)
                    <div class="alert alert-info">
                        <strong>Retake — attempt {{ $last_attempt['attempts'] + 1 }}.</strong>
                        Your last score was {{ round($last_attempt['score']) }}%; you need {{ round($passing) }}% to pass.
                    </div>
                @endif

                <div id="quiz" data-total="{{ $total }}" data-passing="{{ $passing }}"
                     data-check-url="{{ $checkUrl }}" @if($submitUrl) data-submit-url="{{ $submitUrl }}" @endif
                     data-csrf="{{ csrf_token() }}" data-preview="{{ $preview ? 1 : 0 }}">
                    <div class="qz-bar">
                        <div class="qz-count" data-answered aria-live="polite">0 of {{ $total }} answered</div>
                        <div class="tp-track" role="progressbar" aria-label="Questions answered" aria-valuemin="0" aria-valuemax="{{ $total }}" aria-valuenow="0" data-track><div class="tp-fill" data-fill></div></div>
                    </div>

                    <div class="topics">
                    @foreach($sections as $si => $section)
                        <div class="topic" data-section="{{ $si }}">
                            <button type="button" class="topic-head" aria-expanded="false" aria-controls="qz-sec-{{ $si }}">
                                <span class="topic-n" aria-hidden="true"><span>{{ $si + 1 }}</span></span>
                                <span class="topic-titles">
                                    <span class="topic-title">{{ $section['title'] }}</span>
                                    @if($section['subtitle'])<span class="topic-sub">{{ $section['subtitle'] }}</span>@endif
                                </span>
                                <span class="topic-read-sr" data-done-label></span>
                                <span class="topic-chev" aria-hidden="true"></span>
                            </button>
                            <div class="qz-body" id="qz-sec-{{ $si }}" hidden>
                                @foreach($section['question_ids'] as $qid)
                                    @php
                                        $q = $byId[$qid] ?? null;
                                    @endphp
                                    @continue(! $q)
                                    @php
                                        $number++;
                                    @endphp
                                    <div class="qz" data-q="{{ $q['id'] }}">
                                        <div class="qz-n">Question {{ $number }} of {{ $total }}</div>
                                        <div class="qz-text" id="qz-text-{{ $q['id'] }}">{!! \App\Support\LearningView::html($q['title']) !!}</div>
                                        <div class="qz-options" role="group" aria-labelledby="qz-text-{{ $q['id'] }}">
                                            @foreach($q['answers'] as $ai => $a)
                                                <button type="button" class="qz-opt" data-a="{{ $a['id'] }}">
                                                    <span class="qz-letter" aria-hidden="true"><span>{{ $letters[$ai] ?? '' }}</span></span>
                                                    <span class="qz-label">{!! \App\Support\LearningView::html($a['title']) !!}</span>
                                                </button>
                                            @endforeach
                                        </div>
                                        <div class="qz-feedback" hidden role="status" aria-live="polite"></div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                    </div>

                    <div class="qz-result" data-result hidden role="status" aria-live="polite">
                        <div class="qz-score"><span data-score>0</span> <small>of {{ $total }} correct</small></div>
                        <p class="qz-msg" data-message></p>
                        <div class="qz-actions">
                            <a href="{{ route($lr.'.module', $module['slug']) }}" class="btn btn-light" data-continue hidden>Back to the course</a>
                            <button type="button" class="btn btn-primary" data-restart>Start again</button>
                        </div>
                    </div>
                    @if($preview)<p class="muted mt-2" style="font-size:14px">Preview: answers are checked but nothing is recorded.</p>@endif
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
@include('learning.partials.styles')
@include('learning.partials.course-ui')
<style>
    .lms .back-link { display: inline-block; font-size: 14px; font-weight: 600; color: var(--brand); margin-bottom: 12px; }
    .lms .btn-lg { padding: 10px 26px; font-weight: 600; border-radius: 10px; }
    .lms .btn { cursor: pointer; padding: 9px 20px; border-radius: 10px; font-weight: 600; font-size: 15px; display: inline-block; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    const root = document.getElementById('quiz');
    if (!root) return;
    const total = +root.dataset.total, passing = +root.dataset.passing;
    const resultUrl = @json($preview ? null : route('examiner.learning.result', $module['slug']));
    const sections = [...root.querySelectorAll('.topic')];
    const questions = [...root.querySelectorAll('.qz')];
    const answers = {}, correct = {};
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const post = (url, body) => fetch(url, {
        method: 'POST', credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': root.dataset.csrf, 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify(body),
    }).then(r => r.json().then(j => ({ ok: r.ok, status: r.status, json: j })));

    function setOpen(section, open) {
        section.classList.toggle('is-open', open);
        section.querySelector('.topic-head').setAttribute('aria-expanded', open ? 'true' : 'false');
        section.querySelector('.qz-body').hidden = !open;
    }
    sections.forEach(sec => sec.querySelector('.topic-head').addEventListener('click', () => {
        const open = !sec.classList.contains('is-open');
        sections.forEach(s => setOpen(s, false));
        setOpen(sec, open);
        if (open) sec.scrollIntoView({ block: 'nearest', behavior: reduce ? 'auto' : 'smooth' });
    }));
    if (sections[0]) setOpen(sections[0], true);

    function paint() {
        const done = Object.keys(answers).length;
        root.querySelector('[data-answered]').textContent = done + ' of ' + total + ' answered';
        root.querySelector('[data-fill]').style.width = (total ? done / total * 100 : 0) + '%';
        root.querySelector('[data-track]').setAttribute('aria-valuenow', done);
        sections.forEach(sec => {
            const qs = [...sec.querySelectorAll('.qz')];
            const all = qs.length && qs.every(q => answers[q.dataset.q]);
            sec.classList.toggle('is-read', !!all);
            sec.querySelector('[data-done-label]').textContent = all ? '(all answered)' : '';
        });
    }

    questions.forEach(q => q.querySelectorAll('.qz-opt').forEach(btn => btn.addEventListener('click', () => answer(q, btn))));

    async function answer(q, btn) {
        const id = q.dataset.q;
        if (answers[id]) return;
        answers[id] = btn.dataset.a;
        q.querySelectorAll('.qz-opt').forEach(b => b.disabled = true);
        let res;
        try { res = await post(root.dataset.checkUrl, { question_id: +id, answer_id: btn.dataset.a }); }
        catch (e) { res = { ok: false }; }
        if (!res.ok) {
            delete answers[id];
            q.querySelectorAll('.qz-opt').forEach(b => b.disabled = false);
            const fb = q.querySelector('.qz-feedback');
            fb.className = 'qz-feedback is-wrong'; fb.hidden = false;
            fb.textContent = 'This answer could not be checked. Please check your connection and tap it again.';
            return;
        }
        const r = res.json;
        correct[id] = !!r.is_correct;
        btn.classList.add(r.is_correct ? 'is-right' : 'is-wrong');
        q.querySelectorAll('.qz-opt').forEach(b => { if ((r.correct_ids || []).includes(b.dataset.a)) b.classList.add('is-right'); });
        const fb = q.querySelector('.qz-feedback');
        fb.className = 'qz-feedback ' + (r.is_correct ? 'is-right' : 'is-wrong');
        fb.innerHTML = '<div class="qz-verdict">' + (r.is_correct ? 'Correct' : 'Not quite') + '</div>'
            + (r.feedback ? '<div class="qz-klp">Key learning point</div>' + r.feedback : '');
        fb.hidden = false;
        paint();
        if (Object.keys(answers).length === total) finish();
    }

    async function finish() {
        const score = Object.values(correct).filter(Boolean).length;
        let passed = (score / total * 100) >= passing, message;
        if (root.dataset.submitUrl) {
            try {
                const res = await post(root.dataset.submitUrl, { answers });
                if (res.ok) passed = !!res.json.passed;
                else message = 'Your answers could not be saved. Please check your connection and use Start again.';
            } catch (e) { message = 'Your answers could not be saved. Please check your connection and use Start again.'; }
        }
        const box = root.querySelector('[data-result]');
        box.querySelector('[data-score]').textContent = score;
        box.querySelector('[data-message]').textContent = message || (passed
            ? 'You passed. Your result is saved.'
            : 'You need ' + Math.round(passing) + '% to pass (' + Math.ceil(total * passing / 100) + ' of ' + total + '). Read the key learning points above and start again.');
        const cont = box.querySelector('[data-continue]');
        cont.hidden = !passed;
        if (passed && resultUrl) { cont.textContent = 'See your result'; cont.href = resultUrl; }
        box.querySelector('[data-restart]').hidden = passed && !message;
        box.hidden = false;
        box.scrollIntoView({ block: 'nearest', behavior: reduce ? 'auto' : 'smooth' });
    }

    root.querySelector('[data-restart]').addEventListener('click', () => {
        Object.keys(answers).forEach(k => delete answers[k]);
        Object.keys(correct).forEach(k => delete correct[k]);
        questions.forEach(q => {
            q.querySelectorAll('.qz-opt').forEach(b => { b.disabled = false; b.classList.remove('is-right', 'is-wrong'); });
            const fb = q.querySelector('.qz-feedback'); fb.hidden = true; fb.innerHTML = '';
        });
        root.querySelector('[data-result]').hidden = true;
        sections.forEach(s => setOpen(s, false));
        if (sections[0]) setOpen(sections[0], true);
        paint();
        root.scrollIntoView({ block: 'start', behavior: reduce ? 'auto' : 'smooth' });
    });
    paint();
})();
</script>
@endpush
