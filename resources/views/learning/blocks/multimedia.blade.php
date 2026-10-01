@php
    $items   = $payload['items'] ?? [];
    $first   = $items[0] ?? [];
    $video   = $first['media']['video'] ?? null;
    $caption = $first['caption'] ?? null;
    $localUrl  = $video['video_url'] ?? null;
    $label     = $video['display_label'] ?? $video['originalUrl'] ?? 'Video';
@endphp

<div class="block block-video">
    @if($localUrl)
        <div class="video-frame">
            <video controls preload="metadata" src="{{ $localUrl }}"></video>
        </div>
    @else
        <div class="video-placeholder">
            <div class="vp-icon">
                <svg viewBox="0 0 24 24" width="34" height="34" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><polygon points="10,8 16,12 10,16" fill="currentColor" stroke="none"/></svg>
            </div>
            <div class="vp-text">
                <strong>{{ $label }}</strong>
                <span>Video to be uploaded</span>
            </div>
        </div>
    @endif
    @if($caption)
        <div class="video-caption">{!! \App\Support\LearningView::html($caption) !!}</div>
    @endif
</div>