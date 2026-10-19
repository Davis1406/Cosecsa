@php
    $items = $payload['items'] ?? [];
    $title = $payload['title'] ?? null;
@endphp

<div class="block block-details">
    @if($title)<div class="details-label">{!! \App\Support\LearningView::html($title) !!}</div>@endif
    @foreach($items as $item)
        <details class="details-item">
            <summary>{!! \App\Support\LearningView::html($item['title'] ?? null) !!}</summary>
            <div class="block-paragraph">{!! \App\Support\LearningView::html($item['body'] ?? null) !!}</div>
        </details>
    @endforeach
</div>
