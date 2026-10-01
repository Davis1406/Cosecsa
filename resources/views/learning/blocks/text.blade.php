@php
    $items   = $payload['items'] ?? [];
    $variant = $payload['variant'] ?? '';
    $isQuote = ($payload['type'] ?? '') === 'quote';
@endphp

@if($isQuote)
    @php
        $item = $items[0] ?? [];
        $name = $item['name'] ?? null;
        $para = $item['paragraph'] ?? $item['description'] ?? null;
    @endphp
    <div class="block block-quote">
        <div class="quote-mark">“</div>
        <blockquote>{!! \App\Support\LearningView::html($para) !!}</blockquote>
        @if($name)
            <div class="quote-attrib">{!! \App\Support\LearningView::html($name) !!}</div>
        @endif
    </div>
@else
    @foreach($items as $item)
        @php
            $heading   = $item['heading'] ?? null;
            $paragraph = $item['paragraph'] ?? null;
        @endphp

        @if($variant === 'heading')
            <div class="block">
                <h2 class="block-heading">{!! \App\Support\LearningView::html($heading) !!}</h2>
            </div>
        @elseif($variant === 'subheading' || $variant === 'subheading paragraph')
            <div class="block">
                <h3 class="block-subheading">{!! \App\Support\LearningView::html($heading ?? $paragraph) !!}</h3>
                @if($paragraph && $variant === 'subheading paragraph')
                    <div class="block-paragraph">{!! \App\Support\LearningView::html($paragraph) !!}</div>
                @endif
            </div>
        @elseif($variant === 'heading paragraph')
            <div class="block">
                <h3 class="block-heading2">{!! \App\Support\LearningView::html($heading) !!}</h3>
                <div class="block-paragraph">{!! \App\Support\LearningView::html($paragraph) !!}</div>
            </div>
        @elseif(str_starts_with($variant, 'impact'))
            <div class="block block-impact">
                <div class="block-paragraph">{!! \App\Support\LearningView::html($heading ?? $paragraph) !!}</div>
            </div>
        @else
            <div class="block">
                @if($heading)
                    <h3 class="block-heading2">{!! \App\Support\LearningView::html($heading) !!}</h3>
                @endif
                @if($paragraph)
                    <div class="block-paragraph">{!! \App\Support\LearningView::html($paragraph) !!}</div>
                @endif
            </div>
        @endif
    @endforeach
@endif