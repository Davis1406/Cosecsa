@php
    $items  = $payload['items'] ?? [];
    $img    = $payload['media']['image'] ?? null;
    $url    = \App\Support\LearningView::imageUrl($img);
    $opacity = (int) (($img['opacity'] ?? null) ?? 100);
    $frame = \App\Support\LearningView::frame($img);
@endphp

<div class="block block-labeledgraphic" data-labeledgraphic>
    @if($url)
        <div class="lg-wrap {{ $frame['class'] }}" style="{{ $frame['style'] }}">
            <img src="{{ $url }}" alt="" loading="lazy" @if($opacity < 100) style="opacity:{{ $opacity / 100 }};" @endif>
            @foreach($items as $marker)
                @php
                    $x = (float) ($marker['x'] ?? 50);
                    $y = (float) ($marker['y'] ?? 50);
                @endphp
                <button type="button" class="lg-marker"
                        data-lg-marker aria-label="{{ strip_tags($marker['title'] ?? 'Show detail') }}" style="left: {{ $x }}%; top: {{ $y }}%; --i: {{ $loop->index }}">
                    <span>+</span>
                </button>
            @endforeach
        </div>
        <script type="application/json" data-lg-data>@json($items)</script>
        <div class="lg-pop" data-lg-pop hidden>
            <button type="button" class="lg-close" data-lg-close aria-label="Close">×</button>
            <div class="lg-title" data-lg-title></div>
            <div class="lg-desc" data-lg-desc></div>
        </div>
    @endif
</div>
