@php
    $items = $payload['items'] ?? [];
@endphp

<div class="block block-flashcards">
    <div class="flashcards-grid">
        @foreach($items as $card)
            @php
                $front = $card['front'] ?? [];
                $back  = $card['back'] ?? [];
                $frontText = $front['description'] ?? $front['title'] ?? '?';
                $backText  = $back['description'] ?? $back['title'] ?? '';
                $fUrl = \App\Support\LearningView::imageUrl($front['media']['image'] ?? null);
                $bUrl = \App\Support\LearningView::imageUrl($back['media']['image'] ?? null);
                $fOp = (int) (($front['media']['image']['opacity'] ?? null) ?? 100);
                $bOp = (int) (($back['media']['image']['opacity'] ?? null) ?? 100);
            @endphp
            <div class="flip-card" tabindex="0" role="button" aria-pressed="false" aria-label="Flashcard — press to flip" style="--i: {{ $loop->index }}">
                <div class="flip-inner">
                    <div class="flip-face flip-front">
                        @if($fUrl)<img src="{{ $fUrl }}" alt="" loading="lazy" @if($fOp < 100) style="opacity:{{ $fOp / 100 }};" @endif>@endif
                        <div class="flip-label">{!! \App\Support\LearningView::html($frontText) !!}</div>
                        <div class="flip-hint">Tap to reveal</div>
                    </div>
                    <div class="flip-face flip-back">
                        @if($bUrl)<img src="{{ $bUrl }}" alt="" loading="lazy" @if($bOp < 100) style="opacity:{{ $bOp / 100 }};" @endif>@endif
                        <div class="flip-label">{!! \App\Support\LearningView::html($backText) !!}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
