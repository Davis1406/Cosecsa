@php
    $items   = $payload['items'] ?? [];
    $variant = $payload['variant'] ?? '';
    $first   = $items[0] ?? [];
    $img     = $first['media']['image'] ?? null;
    $url     = \App\Support\LearningView::imageUrl($img);
    $caption = $first['caption'] ?? null;
    $para    = $first['paragraph'] ?? null;
    $isOverlay = $variant === 'text overlay';
    $opacity = (int) ($img['opacity'] ?? ($isOverlay ? 55 : 100));
    $overlayFrame = \App\Support\LearningView::frame($img);
@endphp

@if($variant === 'hero')
    <div class="block block-image-hero">
        @if($url)@include('learning.blocks._img', ['img' => $img, 'url' => $url])@endif
        @if($caption)<div class="img-caption">{!! \App\Support\LearningView::html($caption) !!}</div>@endif
    </div>
@elseif($variant === 'text aside')
    <div class="block block-image-aside">
        @if($url)<div class="aside-img">@include('learning.blocks._img', ['img' => $img, 'url' => $url])</div>@endif
        <div class="aside-text">
            @if($caption)<div class="aside-caption">{!! \App\Support\LearningView::html($caption) !!}</div>@endif
            @if($para)<div class="block-paragraph">{!! \App\Support\LearningView::html($para) !!}</div>@endif
        </div>
    </div>
@elseif($variant === 'text overlay')
    <div class="block block-image-overlay {{ $overlayFrame['class'] }}" style="{{ $overlayFrame['style'] }}">
        @if($url)<img src="{{ $url }}" alt="" loading="lazy">@endif
        @if($caption || $para)
            <div class="overlay-content" style="background: rgba(0,0,0,{{ $opacity / 100 }});">
                @if($caption)<div class="overlay-caption">{!! \App\Support\LearningView::html($caption) !!}</div>@endif
                @if($para)<div class="block-paragraph">{!! \App\Support\LearningView::html($para) !!}</div>@endif
            </div>
        @endif
    </div>
@elseif($variant === 'two column')
    <div class="block block-image-grid2">
        @foreach($items as $item)
            @php
                $img = $item['media']['image'] ?? null;
                $u = \App\Support\LearningView::imageUrl($img);
            @endphp
            <figure>
                @if($u)@include('learning.blocks._img', ['img' => $img, 'url' => $u])@endif
                @if($item['caption'] ?? null)<figcaption>{!! \App\Support\LearningView::html($item['caption']) !!}</figcaption>@endif
            </figure>
        @endforeach
    </div>
@endif