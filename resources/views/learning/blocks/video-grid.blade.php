{{-- Back-to-back videos as square tiles, two per row (see LearningView::segments). --}}
@if($heading)
    @include('learning.blocks._dispatch', ['block' => $heading])
@endif
<div class="video-grid">
    @foreach($units as $unit)
        <div class="video-card">
            @if($unit['lead'])
                @include('learning.blocks._dispatch', ['block' => $unit['lead']])
            @endif
            @include('learning.blocks._dispatch', ['block' => $unit['video']])
            @if($unit['after'])
                @include('learning.blocks._dispatch', ['block' => $unit['after']])
            @endif
        </div>
    @endforeach
</div>
