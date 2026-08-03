@php
    /*
     * Props:
     *   $relatedProfiles  – stdClass|array with keys: fellow, examiner, trainer, country_rep (nullable ints)
     *   $currentRole      – string: 'fellow' | 'examiner' | 'trainer' | 'country_rep'
     */
    $rp = $relatedProfiles ?? null;
    if (!$rp) { return; }
    $rp = (object) $rp;

    $links = [
        'fellow'      => ['label' => 'Fellow',      'icon' => 'fa-user-graduate', 'url' => fn($id) => url("admin/associates/fellows/view/{$id}")],
        'examiner'    => ['label' => 'Examiner',     'icon' => 'fa-user-check',   'url' => fn($id) => url("admin/exams/view_examiner/{$id}")],
        'trainer'     => ['label' => 'Trainer (PD)', 'icon' => 'fa-chalkboard-teacher', 'url' => fn($id) => url("admin/associates/trainers/view/{$id}")],
        'country_rep' => ['label' => 'Country Rep',  'icon' => 'fa-globe-africa', 'url' => fn($id) => url("admin/associates/reps/view/{$id}")],
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
            <a href="{{ $cfg['url']($roleId) }}" class="rs-chip rs-link">
                <i class="fas {{ $cfg['icon'] }} mr-1"></i>{{ $cfg['label'] }}
            </a>
        @endif
    @endforeach
</div>
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
