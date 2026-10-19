@php
    $count = count($topicLayout['topics']);
    $number = preg_match('/^Module\s+(\d+)/i', $module['title'], $m) ? 'Module '.$m[1] : 'this module';
    $storeKey = ($preview ?? false) ? null : 'lms-topics:'.auth()->id().':'.$module['slug'];
@endphp

@foreach($topicLayout['intro'] as $segment)
    @include('learning.partials.segment', ['segment' => $segment])
@endforeach

<div class="topics" data-topics data-total="{{ $count }}" @if($storeKey) data-store="{{ $storeKey }}" @endif>
    <div class="topics-progress">
        <div class="tp-count" data-topics-count aria-live="polite">0 of {{ $count }} topics read</div>
        <div class="tp-track" role="progressbar" aria-label="Topics read" aria-valuemin="0" aria-valuemax="{{ $count }}" aria-valuenow="0" data-topics-track><div class="tp-fill" data-topics-fill></div></div>
    </div>

    @foreach($topicLayout['topics'] as $i => $topic)
        <div class="topic" data-topic="{{ $i }}">
            <button type="button" class="topic-head" aria-expanded="false" aria-controls="topic-body-{{ $i }}">
                <span class="topic-n" aria-hidden="true"><span>{{ $i + 1 }}</span></span>
                <span class="topic-titles">
                    <span class="topic-title">{{ $topic['title'] }}</span>
                    @if($topic['subtitle'])<span class="topic-sub">{{ $topic['subtitle'] }}</span>@endif
                </span>
                <span class="topic-read-sr" data-read-label></span>
                <span class="topic-chev" aria-hidden="true"></span>
            </button>
            <div class="topic-body" id="topic-body-{{ $i }}" hidden>
                @foreach($topic['segments'] as $segment)
                    @include('learning.partials.segment', ['segment' => $segment])
                @endforeach
            </div>
        </div>
    @endforeach

    <div class="topics-done" data-topics-done hidden>
        <div class="td-msg" role="status">You have read all {{ $count }} topics of {{ $number }}. Use the course menu to move to the next module or the quiz module.</div>
        @foreach($topicLayout['outro'] as $segment)
            @include('learning.partials.segment', ['segment' => $segment])
        @endforeach
    </div>
</div>

<script>
(function () {
    const root = document.querySelector('[data-topics]');
    if (!root) return;
    const topics = [...root.querySelectorAll('.topic')];
    const total = topics.length;
    const key = root.dataset.store || null;
    const read = new Set();

    try { if (key) JSON.parse(localStorage.getItem(key) || '[]').forEach(i => read.add(i)); } catch (e) {}

    function paint() {
        topics.forEach((t, i) => {
            const done = read.has(i);
            t.classList.toggle('is-read', done);
            t.querySelector('[data-read-label]').textContent = done ? '(read)' : '';
        });
        root.querySelector('[data-topics-count]').textContent = read.size + ' of ' + total + ' topics read';
        root.querySelector('[data-topics-fill]').style.width = (total ? read.size / total * 100 : 0) + '%';
        root.querySelector('[data-topics-track]').setAttribute('aria-valuenow', read.size);
        root.querySelector('[data-topics-done]').hidden = read.size < total;
    }

    function setOpen(topic, open) {
        topic.classList.toggle('is-open', open);
        topic.querySelector('.topic-head').setAttribute('aria-expanded', open ? 'true' : 'false');
        topic.querySelector('.topic-body').hidden = !open;
    }

    topics.forEach((topic, i) => {
        topic.querySelector('.topic-head').addEventListener('click', () => {
            const open = !topic.classList.contains('is-open');
            topics.forEach(t => setOpen(t, false));
            setOpen(topic, open);
            if (open) {
                read.add(i);
                try { if (key) localStorage.setItem(key, JSON.stringify([...read])); } catch (e) {}
                paint();
                topic.scrollIntoView({ block: 'nearest', behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
            }
        });
    });

    paint();
})();
</script>
