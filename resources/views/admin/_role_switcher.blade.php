@php
    /*
     * Props:
     *   $relatedProfiles  – stdClass|array with keys: fellow, examiner, programme_director, country_rep, member (nullable ints)
     *   $currentRole      – string: 'fellow' | 'examiner' | 'programme_director' | 'country_rep' | 'member'
     */
    $rp = $relatedProfiles ?? null;
    if (!$rp) { return; }
    $rp = (object) $rp;

    $links = [
        'fellow'      => ['label' => 'Fellow',      'icon' => 'fa-user-graduate', 'url' => fn($id) => url("admin/associates/fellows/view/{$id}")],
        'examiner'    => ['label' => 'Examiner',     'icon' => 'fa-user-check',   'url' => fn($id) => url("admin/exams/view_examiner/{$id}")],
        'programme_director' => ['label' => 'Programme Director', 'icon' => 'fa-chalkboard-teacher', 'url' => fn($id) => url("admin/associates/programme-directors/view/{$id}")],
        'country_rep' => ['label' => 'Country Rep',  'icon' => 'fa-globe-africa', 'url' => fn($id) => url("admin/associates/reps/view/{$id}")],
        'member'      => ['label' => 'Member',       'icon' => 'fa-id-badge',     'url' => fn($id) => url("admin/associates/members/view/{$id}")],
    ];

    $hasOther = false;
    foreach ($links as $role => $cfg) {
        if ($role !== ($currentRole ?? '') && !empty($rp->$role)) { $hasOther = true; break; }
    }
@endphp
@if($hasOther)
<div class="role-switcher-bar mb-2">
    <span class="rs-label">Also in:</span>
    @foreach($links as $role => $cfg)
        @php $roleId = $rp->$role ?? null; @endphp
        @if($role === ($currentRole ?? ''))
            <span class="rs-chip rs-active">
                <i class="fas {{ $cfg['icon'] }} mr-1"></i>{{ $cfg['label'] }}
            </span>
        @elseif($roleId)
            <a href="{{ $cfg['url']($roleId) }}" class="rs-chip rs-link"
               data-rs-label="{{ $cfg['label'] }}" data-rs-icon="{{ $cfg['icon'] }}">
                <i class="fas {{ $cfg['icon'] }} mr-1"></i>{{ $cfg['label'] }}
            </a>
        @endif
    @endforeach
</div>
@php
    // The person's other role profiles, prerendered in the background
    // (Chrome Speculation Rules) so switching roles shows the page at once,
    // fully loaded with its own scripts, instead of a fresh page load.
    $otherRoleUrls = [];
    foreach ($links as $role => $cfg) {
        if ($role !== ($currentRole ?? '') && !empty($rp->$role)) {
            $otherRoleUrls[] = $cfg['url']($rp->$role);
        }
    }
@endphp
<script type="speculationrules">
{!! json_encode(['prerender' => [['urls' => $otherRoleUrls, 'eagerness' => 'immediate']]], JSON_UNESCAPED_SLASHES) !!}
</script>
{{-- Placeholder shown while switching roles: a skeleton of a profile page
     over the current one, then the next profile fades/slides in. --}}
<template id="rsSkeletonTpl">
    <div class="rs-skeleton" role="status" aria-live="polite">
        <div class="rs-sk-caption"><i class="fas rs-sk-icon mr-2"></i><span class="rs-sk-text"></span></div>
        <div class="rs-sk-bar rs-sk"></div>
        <div class="rs-sk-grid">
            <div class="rs-sk-card rs-sk-side">
                <div class="rs-sk rs-sk-avatar"></div>
                <div class="rs-sk rs-sk-line" style="width:70%"></div>
                <div class="rs-sk rs-sk-line" style="width:50%"></div>
                <div class="rs-sk rs-sk-line" style="width:85%"></div>
                <div class="rs-sk rs-sk-line" style="width:60%"></div>
            </div>
            <div>
                <div class="rs-sk-tiles">
                    <div class="rs-sk rs-sk-tile"></div><div class="rs-sk rs-sk-tile"></div>
                    <div class="rs-sk rs-sk-tile"></div><div class="rs-sk rs-sk-tile"></div>
                </div>
                <div class="rs-sk rs-sk-tabs"></div>
                <div class="rs-sk-card">
                    <div class="rs-sk rs-sk-line" style="width:40%"></div>
                    <div class="rs-sk rs-sk-line"></div>
                    <div class="rs-sk rs-sk-line" style="width:92%"></div>
                    <div class="rs-sk rs-sk-line" style="width:78%"></div>
                    <div class="rs-sk rs-sk-line" style="width:88%"></div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
(function () {
    // Bound once per page even if the partial were included twice.
    if (window.__rsSwitchBound) return;
    window.__rsSwitchBound = true;

    var MIN_SHOW_MS = 300; // long enough to read as a transition, short enough not to feel slow

    function clearSkeleton() {
        document.querySelectorAll('.rs-skeleton').forEach(function (el) { el.remove(); });
        document.querySelectorAll('.content-wrapper.rs-switching').forEach(function (el) { el.classList.remove('rs-switching'); });
    }

    document.addEventListener('click', function (e) {
        var link = e.target.closest('a.rs-chip.rs-link');
        if (!link) return;
        // New tab / window: let the browser handle it, no placeholder.
        if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

        var wrapper = document.querySelector('.content-wrapper');
        var tpl = document.getElementById('rsSkeletonTpl');
        if (!wrapper || !tpl) return;

        e.preventDefault();
        clearSkeleton();
        var sk = tpl.content.firstElementChild.cloneNode(true);
        sk.querySelector('.rs-sk-icon').classList.add(link.dataset.rsIcon || 'fa-user');
        sk.querySelector('.rs-sk-text').textContent = 'Opening ' + (link.dataset.rsLabel || '') + ' profile…';
        wrapper.classList.add('rs-switching');
        wrapper.appendChild(sk);

        var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        setTimeout(function () { window.location.href = link.href; }, reduce ? 0 : MIN_SHOW_MS);
    });

    // Back/forward from the bfcache restores this page with the skeleton still up.
    window.addEventListener('pageshow', function (e) { if (e.persisted) clearSkeleton(); });
})();
</script>
<style>
/* Switch animation between a person's role profiles (both pages include
   this partial, so both opt in). Browsers without support just navigate. */
@view-transition { navigation: auto; }
::view-transition-old(root) { animation: rs-out .18s ease-in both; }
::view-transition-new(root) { animation: rs-in .32s cubic-bezier(.2,.7,.2,1) both; }
@keyframes rs-out { to { opacity: 0; } }
@keyframes rs-in  { from { opacity: 0; transform: translateY(10px); } }

/* Skeleton overlay over the current profile while the next one opens */
.content-wrapper.rs-switching { position: relative; }
.content-wrapper.rs-switching > :not(.rs-skeleton) { opacity: .25; transition: opacity .2s ease; pointer-events: none; }
.rs-skeleton {
    position: absolute; inset: 0; z-index: 1040; padding: 18px 24px;
    background: #f4f6f9; animation: rs-sk-in .2s ease both;
}
.rs-sk-caption { font-size: .9rem; font-weight: 600; color: #a02626; margin-bottom: 14px; }
.rs-sk-bar   { height: 56px; border-radius: 8px; margin-bottom: 16px; }
.rs-sk-grid  { display: grid; grid-template-columns: minmax(220px, 1fr) 3fr; gap: 16px; }
.rs-sk-card  { background: #fff; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,.07); padding: 18px; }
.rs-sk-side  { display: flex; flex-direction: column; align-items: center; }
.rs-sk-avatar { width: 96px; height: 96px; border-radius: 50%; margin-bottom: 16px; }
.rs-sk-line  { height: 11px; border-radius: 6px; margin: 7px 0; width: 100%; }
.rs-sk-side .rs-sk-line { align-self: stretch; }
.rs-sk-tiles { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 14px; }
.rs-sk-tile  { height: 72px; border-radius: 8px; }
.rs-sk-tabs  { height: 38px; border-radius: 8px; margin-bottom: 14px; }
.rs-sk {
    background: linear-gradient(90deg, #e2e8f0 25%, #eef2f7 50%, #e2e8f0 75%);
    background-size: 200% 100%; animation: rs-shimmer 1.2s ease-in-out infinite;
}
@keyframes rs-shimmer { from { background-position: 200% 0; } to { background-position: -200% 0; } }
@keyframes rs-sk-in { from { opacity: 0; } }
body.dark-mode .rs-skeleton { background: #141921; }
body.dark-mode .rs-sk-caption { color: #f48a8a; }
body.dark-mode .rs-sk-card { background: #1e2330; box-shadow: 0 1px 4px rgba(0,0,0,.2); }
body.dark-mode .rs-sk { background-image: linear-gradient(90deg, #2d3748 25%, #374151 50%, #2d3748 75%); }
@media (max-width: 767px) {
    .rs-sk-grid  { grid-template-columns: 1fr; }
    .rs-sk-tiles { grid-template-columns: repeat(2, 1fr); }
}
@media (prefers-reduced-motion: reduce) {
    .rs-sk, .rs-skeleton { animation: none; }
    ::view-transition-old(root), ::view-transition-new(root) { animation: none; }
}
</style>
<style>
.role-switcher-bar {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px;
    padding: 7px 14px;
    background: #fff8f8;
    border: 1px solid #f0dada;
    border-radius: 6px;
    font-size: .8rem;
}
body.dark-mode .role-switcher-bar {
    background: #2d2020;
    border-color: #5a2e2e;
}
.rs-label {
    font-size: .72rem;
    font-weight: 700;
    color: #a02626;
    text-transform: uppercase;
    letter-spacing: .06em;
    margin-right: 2px;
    white-space: nowrap;
}
.rs-chip {
    display: inline-flex;
    align-items: center;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: .78rem;
    font-weight: 600;
    line-height: 1.5;
    white-space: nowrap;
    text-decoration: none;
    transition: background .15s, color .15s;
}
.rs-chip.rs-active {
    background: #a02626;
    color: #fff;
    cursor: default;
}
.rs-chip.rs-link {
    background: #fff;
    color: #a02626;
    border: 1px solid #e8b4b4;
}
.rs-chip.rs-link:hover {
    background: #a02626;
    color: #fff;
    border-color: #a02626;
    text-decoration: none;
}
body.dark-mode .rs-chip.rs-link {
    background: #3a1f1f;
    border-color: #7a3a3a;
    color: #f8a5a5;
}
body.dark-mode .rs-chip.rs-link:hover {
    background: #a02626;
    color: #fff;
}
</style>
@endif
