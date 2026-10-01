@php
    $items  = $payload['items'] ?? [];
    $intro  = collect($items)->firstWhere('type', 'intro');
    $summary = collect($items)->firstWhere('type', 'summary');
    $steps  = collect($items)->where('type', 'step')->values();
    $introImg = $intro['media']['image'] ?? null;
    $introUrl = \App\Support\LearningView::imageUrl($introImg);
@endphp

<div class="block block-process" data-process>
    <div class="process-view">
        @if($introUrl)
            <div class="process-hero">@include('learning.blocks._img', ['img' => $introImg, 'url' => $introUrl, 'frameDefaults' => ['radius' => 0]])</div>
        @endif
        <div class="process-body">
            @if($intro && ($intro['title'] ?? null))
                <h4 class="process-title">{{ $intro['title'] }}</h4>
            @endif
            @if($intro && ($intro['description'] ?? null))
                <div class="process-desc">{!! \App\Support\LearningView::html($intro['description']) !!}</div>
            @endif

            <div class="process-steps">
                @foreach($steps as $i => $step)
                    @php
                        $stepImg = $step['media']['image'] ?? null;
                        $stepUrl = \App\Support\LearningView::imageUrl($stepImg);
                    @endphp
                    <div class="process-step {{ $i === 0 ? 'active' : '' }}" data-step="{{ $i }}">
                        <div class="step-head">
                            <span class="step-num">{{ $i + 1 }}</span>
                            <span class="step-title">{{ $step['title'] ?? 'Step ' . ($i + 1) }}</span>
                        </div>
                        <div class="step-body">
                            @if($stepUrl)@include('learning.blocks._img', ['img' => $stepImg, 'url' => $stepUrl, 'frameDefaults' => ['radius' => 10]])@endif
                            <div class="step-desc">{!! \App\Support\LearningView::html($step['description'] ?? null) !!}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($steps->count() > 1)
                <div class="process-nav">
                    <button type="button" class="btn btn-outline btn-sm" data-process-prev>← Previous</button>
                    <div class="process-dots" aria-hidden="true">
                        @foreach($steps as $i => $step)
                            <span class="{{ $i === 0 ? 'active' : '' }}"></span>
                        @endforeach
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" data-process-next>Next →</button>
                </div>
            @endif

            @if($summary && ($summary['description'] ?? null))
                <div class="process-summary">{!! \App\Support\LearningView::html($summary['description']) !!}</div>
            @endif
        </div>
    </div>
</div>
