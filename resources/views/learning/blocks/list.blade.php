@php
    $items   = $payload['items'] ?? [];
    $variant = $payload['variant'] ?? '';
    $tag     = $variant === 'numbered' ? 'ol' : 'ul';
    $cls     = $variant === 'checkboxes' ? 'list-check' : ($variant === 'numbered' ? 'list-num' : 'list-bullet');
@endphp

<div class="block">
    @if($variant === 'checkboxes')
        <div class="checklist" data-checklist="{{ $block->id }}">
            <ul class="list-check" role="list">
                @foreach($items as $item)
                    <li role="checkbox" aria-checked="false" tabindex="0" style="--i: {{ $loop->index }}">
                        <span class="check-box" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>
                        </span>
                        <span class="check-text">{!! \App\Support\LearningView::html($item['paragraph'] ?? null) !!}</span>
                    </li>
                @endforeach
            </ul>
            <div class="checklist-foot" aria-live="polite">
                <span class="checklist-bar"><span data-checklist-fill></span></span>
                <span data-checklist-count>0 of {{ count($items) }} checked</span>
            </div>
        </div>
    @else
        <{{ $tag }} class="{{ $cls }}">
            @foreach($items as $item)
                <li style="--i: {{ $loop->index }}">{!! \App\Support\LearningView::html($item['paragraph'] ?? null) !!}</li>
            @endforeach
        </{{ $tag }}>
    @endif
</div>
