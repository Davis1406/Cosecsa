@isset($segment['units'])
    @include('learning.blocks.video-grid', $segment)
@else
    @include('learning.blocks._dispatch', ['block' => $segment['block']])
@endisset
