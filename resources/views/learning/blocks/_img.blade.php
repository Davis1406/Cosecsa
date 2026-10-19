@php
    $frame = \App\Support\LearningView::frame($img ?? null, $frameDefaults ?? []);
    $op = (int) (($img['opacity'] ?? null) ?? 100);
    $still = $img['still']['image']['resolved_url'] ?? null;
@endphp
<span class="img-frame {{ $frame['class'] }}" style="{{ $frame['style'] }}"><img src="{{ $url }}" alt="{{ $img['alt'] ?? '' }}" loading="lazy" @if($still) class="img-motion" @endif @if($op < 100) style="opacity:{{ $op / 100 }};" @endif>@if($still)<img class="img-still" src="{{ $still }}" alt="{{ $img['alt'] ?? '' }}" loading="lazy">@endif</span>
