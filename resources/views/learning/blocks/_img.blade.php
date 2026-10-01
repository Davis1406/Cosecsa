@php
    $frame = \App\Support\LearningView::frame($img ?? null, $frameDefaults ?? []);
    $op = (int) (($img['opacity'] ?? null) ?? 100);
@endphp
<span class="img-frame {{ $frame['class'] }}" style="{{ $frame['style'] }}"><img src="{{ $url }}" alt="" loading="lazy" @if($op < 100) style="opacity:{{ $op / 100 }};" @endif></span>
