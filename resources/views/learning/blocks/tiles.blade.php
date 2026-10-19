@php
    $items = $payload['items'] ?? [];
    $title = $payload['title'] ?? null;
    $style = in_array($payload['style'] ?? 'tile', ['tile', 'pill', 'card'], true) ? $payload['style'] : 'tile';
@endphp

<div class="block block-tiles">
    @if($title)<div class="details-label">{!! \App\Support\LearningView::html($title) !!}</div>@endif
    <ul class="tiles tiles-{{ $style }}" role="list">
        @foreach($items as $item)
            <li class="tile-item">
                <strong>{!! \App\Support\LearningView::html($item['title'] ?? null) !!}</strong>
                @if(!empty($item['body']))<span>{!! \App\Support\LearningView::html($item['body']) !!}</span>@endif
            </li>
        @endforeach
    </ul>
</div>
