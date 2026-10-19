@php
    $panels = [
        'do'   => ['label' => 'Do', 'items' => $payload['do'] ?? []],
        'dont' => ['label' => 'Don’t', 'items' => $payload['dont'] ?? []],
    ];
@endphp

<div class="block block-dodont">
    @foreach($panels as $key => $panel)
        <section class="dd-panel dd-{{ $key }}" aria-label="{{ $panel['label'] }}">
            <h3 class="dd-title">{{ $panel['label'] }}</h3>
            <ul role="list">
                @foreach($panel['items'] as $text)
                    <li>{!! \App\Support\LearningView::html($text) !!}</li>
                @endforeach
            </ul>
        </section>
    @endforeach
</div>
