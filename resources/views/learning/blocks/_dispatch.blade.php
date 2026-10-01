@php
    $type   = $block->type;
    $family = $block->family;
    $variant = $block->variant;
    $payload = $block->payload ?? [];
    $animate = !empty($payload['settings']['entranceAnimation']);
@endphp

<div class="block-reveal" data-animate="{{ $animate ? '1' : '0' }}">
    @if($type === 'text' || $type === 'quote' || $type === 'MULTIPLE_CHOICE')
        @include('learning.blocks.text', ['block' => $block, 'payload' => $payload])
    @elseif($type === 'image')
        @include('learning.blocks.image', ['block' => $block, 'payload' => $payload])
    @elseif($type === 'list')
        @include('learning.blocks.list', ['block' => $block, 'payload' => $payload])
    @elseif($type === 'multimedia')
        @include('learning.blocks.multimedia', ['block' => $block, 'payload' => $payload])
    @elseif($type === 'divider')
        @include('learning.blocks.divider', ['block' => $block, 'payload' => $payload])
    @elseif($type === 'interactive' && $family === 'flashcard')
        @include('learning.blocks.flashcard', ['block' => $block, 'payload' => $payload])
    @elseif($type === 'interactive' && $variant === 'labeledgraphic')
        @include('learning.blocks.labeledgraphic', ['block' => $block, 'payload' => $payload])
    @elseif($type === 'interactive' && $variant === 'process')
        @include('learning.blocks.process', ['block' => $block, 'payload' => $payload])
    @endif
</div>