{{-- Hero banner of a topic-card module: eyebrow, heading, text on the module's own gradient (--hero1/--hero2). --}}
@php($hero = $module['hero'] ?? null)
@if($hero)
    <header class="hero" style="--hero1: {{ $hero['c1'] ?? '#5e0a14' }}; --hero2: {{ $hero['c2'] ?? '#99060a' }}">
        @if(!empty($hero['eyebrow']))<div class="hero-eyebrow">{{ $hero['eyebrow'] }}</div>@endif
        <h1 class="hero-title">{{ $hero['heading'] ?? $module['title'] }}</h1>
        @if(!empty($hero['text']))<p class="hero-text">{{ $hero['text'] }}</p>@endif
    </header>
@endif
