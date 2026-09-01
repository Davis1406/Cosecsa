@extends('layout.app')

@push('styles')
<style>
/* ════════════════════════════════════════════════════════════════
   TRAINER (ToT) PROFILE — COSECSA THEME
   All selectors scoped under .trainer-view so this page is portable.
   Palette: carseat red #a02626 · gold #FEC503 · light tints.
   ════════════════════════════════════════════════════════════════ */

/* ── Page heading ── */
.trainer-view .page-title {
    font-size: 1.2rem;
    font-weight: 600;
    margin: 0;
    color: #2b2b2b;
}
.trainer-view .page-title .fa {
    color: #a02626;
}

/* ── Action bar ── */
.trainer-view .admin-action-bar {
    background: linear-gradient(180deg, #fff 0%, #faf3f3 100%);
    border: 1px solid #ecd4d4;
    border-left: 4px solid #a02626;
    border-radius: 8px;
    padding: 12px 18px;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    box-shadow: 0 1px 4px rgba(160,38,38,.05);
}
.trainer-view .action-name {
    font-weight: 700;
    color: #a02626;
    font-size: .95rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Action buttons — replace bootstrap blue with COSECSA red */
.trainer-view .btn-cosecsa {
    background: #a02626;
    border: 1px solid #870f0f;
    color: #fff;
    font-weight: 500;
    transition: background .15s, transform .1s;
}
.trainer-view .btn-cosecsa:hover {
    background: #870f0f;
    color: #fff;
    transform: translateY(-1px);
}
.trainer-view .btn-cosecsa-gold {
    background: #FEC503;
    border: 1px solid #e6b000;
    color: #2b2b2b;
    font-weight: 600;
    transition: background .15s, transform .1s;
}
.trainer-view .btn-cosecsa-gold:hover {
    background: #ffe566;
    color: #2b2b2b;
    transform: translateY(-1px);
}

/* ── Profile card (left panel) ── */
.trainer-view .profile-card {
    border-top: 3px solid #a02626;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,.05);
    background: #fff;
}
.trainer-view .profile-card .card-body.head {
    background: linear-gradient(180deg, #fcf3f3 0%, #fff 100%);
    border-bottom: 1px solid #f0e0e0;
    border-radius: 7px 7px 0 0;
}

/* ── Avatar (initials circle — trainers have no photo) ── */
.trainer-view .trainer-avatar {
    width: 96px;
    height: 96px;
    border-radius: 50%;
    border: 3px solid #a02626;
    background: #f5e6e6;
    color: #a02626;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 10px;
    font-size: 2.1rem;
    font-weight: 700;
    box-shadow: 0 2px 10px rgba(160,38,38,.18);
    letter-spacing: 1px;
    line-height: 1;
}
.trainer-view .trainer-name {
    font-size: 1.1rem;
    font-weight: 700;
    color: #222;
    margin: 0;
    line-height: 1.2;
}
.trainer-view .trainer-org {
    font-size: .82rem;
    color: #6c757d;
    margin: 2px 0 0;
    line-height: 1.2;
}
.trainer-view .trainer-id-line {
    font-size: .68rem;
    color: #999;
    margin: 4px 0 0;
}

/* ── Role pills (Master Trainer / Safe Surgery) ── */
.trainer-view .role-pill {
    display: inline-flex;
    align-items: center;
    padding: 4px 10px;
    border-radius: 14px;
    font-size: .72rem;
    font-weight: 600;
    margin: 2px;
    line-height: 1.4;
    border: 1px solid transparent;
    transition: transform .12s;
}
.trainer-view .role-pill:hover { transform: translateY(-1px); }
.trainer-view .role-pill.role-yes {
    background: #d4edda;
    color: #155724;
    border-color: #a8d8b0;
}
.trainer-view .role-pill.role-no {
    background: #f0f0f0;
    color: #777;
    border-color: #ddd;
}
.trainer-view .role-pill.role-master {
    background: linear-gradient(180deg, #fff5dc 0%, #fce7a8 100%);
    color: #6d4d00;
    border-color: #e6b000;
}
.trainer-view .role-pill.role-ss {
    background: linear-gradient(180deg, #e6f0fc 0%, #c9def7 100%);
    color: #1746a8;
    border-color: #7ba0d6;
}

/* ── Section divider ── */
.trainer-view .sect-div {
    font-size: .68rem;
    font-weight: 700;
    letter-spacing: .9px;
    text-transform: uppercase;
    color: #a02626;
    border-bottom: 2px solid #f0d4d4;
    padding-bottom: 3px;
    margin: 12px 0 8px;
}

/* ── Tag pills — ToT cohort short labels ── */
.trainer-view .tag-pill {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: .7rem;
    font-weight: 600;
    margin: 2px 3px 2px 0;
    line-height: 1.5;
    cursor: default;
    transition: transform .12s;
    border: 1px solid transparent;
}
.trainer-view .tag-pill:hover { transform: translateY(-1px); }
.trainer-view .tag-red    { background:#f5e6e6; color:#a02626; border-color:#e8c9c9; }
.trainer-view .tag-gold   { background:#fff5dc; color:#7a5600; border-color:#e8cf7e; }
.trainer-view .tag-green  { background:#e6f4ea; color:#1e7a3a; border-color:#b8d9bf; }
.trainer-view .tag-grey   { background:#f0f0f0; color:#555;    border-color:#ddd; }
.trainer-view .tag-empty  { background:transparent; color:#aaa; border:1px dashed #ccc; }

/* ── Info rows (left panel contact list) ── */
.trainer-view .info-row {
    display: flex;
    align-items: flex-start;
    padding: 5px 0;
    border-bottom: 1px solid #f3f3f3;
    font-size: .83rem;
}
.trainer-view .info-row:last-child { border-bottom: none; }
.trainer-view .info-icon {
    width: 22px;
    color: #a02626;
    flex-shrink: 0;
    padding-top: 1px;
    font-size: .8rem;
    text-align: center;
}
.trainer-view .info-label {
    font-size: .68rem;
    color: #aaa;
    display: block;
    line-height: 1;
    margin-bottom: 2px;
}
.trainer-view .info-text { color: #495057; word-break: break-word; }
.trainer-view .info-text a { color: #a02626; font-weight: 500; text-decoration: none; }
.trainer-view .info-text a:hover { text-decoration: underline; }

/* ── Stat chips ── */
.trainer-view .stat-chip {
    border-radius: 8px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 12px;
    background: #fff;
    border: 1px solid #e9ecef;
    transition: transform .12s, box-shadow .15s;
}
.trainer-view .stat-chip:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(160,38,38,.07);
}
.trainer-view .stat-chip.border-red   { border-left: 3px solid #a02626; }
.trainer-view .stat-chip.border-gold   { border-left: 3px solid #FEC503; }
.trainer-view .stat-chip.border-green  { border-left: 3px solid #28a745; }
.trainer-view .stat-chip.border-blue   { border-left: 3px solid #007bff; }
.trainer-view .stat-chip.border-grey   { border-left: 3px solid #adb5bd; }

.trainer-view .chip-icon {
    width: 38px;
    height: 38px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}
.trainer-view .chip-icon.bg-red   { background:#f0d4d4; color:#a02626; }
.trainer-view .chip-icon.bg-gold  { background:#fff5dc; color:#7a5600; }
.trainer-view .chip-icon.bg-green { background:#e6f7ea; color:#28a745; }
.trainer-view .chip-icon.bg-blue  { background:#e6f0fc; color:#007bff; }
.trainer-view .chip-icon.bg-grey  { background:#eee;     color:#6c757d; }

.trainer-view .chip-label { font-size: .65rem; color: #999; margin-bottom: 1px; text-transform: uppercase; letter-spacing: .03em; }
.trainer-view .chip-val   { font-size: .95rem; color: #222; font-weight: 600; }
.trainer-view .chip-sub   { font-size: .7rem; color: #888; }

/* ── Detail card with field rows ── */
.trainer-view .detail-card {
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,.05);
    border-top: 3px solid #a02626;
}
.trainer-view .field-row {
    display: flex;
    padding: 7px 0;
    border-bottom: 1px solid #f5f5f5;
    font-size: .855rem;
    align-items: flex-start;
}
.trainer-view .field-row:last-child { border-bottom: none; }
.trainer-view .field-lbl {
    width: 200px;
    font-weight: 600;
    color: #555;
    flex-shrink: 0;
    padding-right: 12px;
}
.trainer-view .field-val { color: #222; flex: 1; word-break: break-word; }
.trainer-view .field-val a { color: #a02626; font-weight: 500; text-decoration: none; }
.trainer-view .field-val a:hover { text-decoration: underline; }
.trainer-view .field-val .text-muted { color: #999 !important; }

/* ── Cohort cards (ToT history) ── */
.trainer-view .cohort-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    background: #fff;
    margin-bottom: 8px;
    transition: box-shadow .12s, transform .12s;
}
.trainer-view .cohort-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,.07);
    transform: translateX(2px);
}
.trainer-view .cohort-card.tone-red   { border-left: 3px solid #a02626; }
.trainer-view .cohort-card.tone-gold  { border-left: 3px solid #FEC503; }
.trainer-view .cohort-card.tone-green { border-left: 3px solid #28a745; }
.trainer-view .cohort-card.tone-grey  { border-left: 3px solid #adb5bd; }
.trainer-view .cohort-card.tone-blue  { border-left: 3px solid #007bff; }

.trainer-view .cohort-icon {
    width: 34px;
    height: 34px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: .9rem;
}
.trainer-view .cohort-card.tone-red   .cohort-icon { background:#f0d4d4; color:#a02626; }
.trainer-view .cohort-card.tone-gold  .cohort-icon { background:#fff5dc; color:#7a5600; }
.trainer-view .cohort-card.tone-green .cohort-icon { background:#e6f7ea; color:#28a745; }
.trainer-view .cohort-card.tone-grey  .cohort-icon { background:#eee; color:#666; }
.trainer-view .cohort-card.tone-blue  .cohort-icon { background:#e6f0fc; color:#007bff; }

.trainer-view .cohort-name { font-size: .82rem; font-weight: 600; color: #222; line-height: 1.2; }
.trainer-view .cohort-code { font-size: .68rem; color: #999; margin-top: 1px; letter-spacing: .03em; }

/* ── Empty state ── */
.trainer-view .empty-block {
    display: block;
    padding: 14px;
    text-align: center;
    color: #aaa;
    font-size: .82rem;
    background: #fafafa;
    border: 1px dashed #e0e0e0;
    border-radius: 6px;
    margin-bottom: 6px;
}

/* ── Comment box (admin notes) ── */
.trainer-view .comment-box {
    background: #fafafa;
    border: 1px solid #eaeaea;
    border-radius: 6px;
    padding: 10px 12px;
    font-size: .85rem;
    color: #495057;
    min-height: 44px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
}
.trainer-view .comment-box.empty { color: #aaa; font-style: italic; }
.trainer-view .comment-box .ie-field { flex: 1; }

/* ── Dark mode ──
   The .card itself already goes dark via the site-wide body.dark-mode .card
   rule (!important), but every custom component on this page (chips, pills,
   cohort cards, avatar, action bar…) paints its own light background/text
   and needs its own override, or it reads as near-black text on a near-
   black card. Tokens match the rest of the admin (see custom.css): card bg
   #1e2330, border #2d3748, body text #e2e8f0, muted label #9ca3af. */
body.dark-mode .trainer-view .page-title { color: #e2e8f0; }

body.dark-mode .trainer-view .admin-action-bar {
    background: linear-gradient(180deg, #1e2330 0%, #252c3b 100%);
    border-color: #3a2a2a;
}
body.dark-mode .trainer-view .action-name { color: #f87171; }
body.dark-mode .trainer-view .action-name .text-muted { color: #8892a4 !important; }

body.dark-mode .trainer-view .profile-card .card-body.head {
    background: linear-gradient(180deg, #252c3b 0%, #1e2330 100%);
    border-bottom-color: #2d3748;
}
body.dark-mode .trainer-view .trainer-name { color: #e2e8f0; }
body.dark-mode .trainer-view .trainer-org,
body.dark-mode .trainer-view .trainer-id-line { color: #8892a4; }

body.dark-mode .trainer-view .role-pill.role-no { background: #2d3340; color: #9ca3af; border-color: #3a4152; }
body.dark-mode .trainer-view .role-pill.role-yes { background: #16351f; color: #86e0a0; border-color: #235431; }
body.dark-mode .trainer-view .role-pill.role-master { background: linear-gradient(180deg, #3a2f0d 0%, #4a3c10 100%); color: #f0d060; border-color: #6b551a; }
body.dark-mode .trainer-view .role-pill.role-ss { background: linear-gradient(180deg, #16233d 0%, #1c2e4d 100%); color: #8fb4f0; border-color: #2e4a7a; }

body.dark-mode .trainer-view .sect-div { color: #f87171; border-bottom-color: #3a2a2a; }

body.dark-mode .trainer-view .tag-red   { background:#3a2222; color:#f0a8a8; border-color:#5a3232; }
body.dark-mode .trainer-view .tag-gold  { background:#3a2f0d; color:#f0d060; border-color:#5a4a15; }
body.dark-mode .trainer-view .tag-green { background:#16351f; color:#86e0a0; border-color:#235431; }
body.dark-mode .trainer-view .tag-grey  { background:#2d3340; color:#9ca3af; border-color:#3a4152; }
body.dark-mode .trainer-view .tag-empty { color:#6b7280; border-color:#3a4152; }

body.dark-mode .trainer-view .info-row { border-bottom-color: #2d3748; }
body.dark-mode .trainer-view .info-label { color: #6b7280; }
body.dark-mode .trainer-view .info-text { color: #cbd5e0; }
body.dark-mode .trainer-view .info-text a,
body.dark-mode .trainer-view .field-val a { color: #f87171; }

body.dark-mode .trainer-view .stat-chip { background: #1e2330; border-color: #2d3748; }
body.dark-mode .trainer-view .chip-icon.bg-red   { background:#3a2222; color:#f0a8a8; }
body.dark-mode .trainer-view .chip-icon.bg-gold  { background:#3a2f0d; color:#f0d060; }
body.dark-mode .trainer-view .chip-icon.bg-green { background:#16351f; color:#86e0a0; }
body.dark-mode .trainer-view .chip-icon.bg-blue  { background:#16233d; color:#8fb4f0; }
body.dark-mode .trainer-view .chip-icon.bg-grey  { background:#2d3340; color:#9ca3af; }
body.dark-mode .trainer-view .chip-label { color: #6b7280; }
body.dark-mode .trainer-view .chip-val { color: #e2e8f0; }
body.dark-mode .trainer-view .chip-sub { color: #8892a4; }
body.dark-mode .trainer-view .chip-val.text-muted { color: #6b7280 !important; }

body.dark-mode .trainer-view .field-row { border-bottom-color: #2d3748; }
body.dark-mode .trainer-view .field-lbl { color: #9ca3af; }
body.dark-mode .trainer-view .field-val { color: #e2e8f0; }
body.dark-mode .trainer-view .field-val .text-muted { color: #6b7280 !important; }

body.dark-mode .trainer-view .cohort-card { background: #1e2330; border-color: #2d3748; }
body.dark-mode .trainer-view .cohort-card.tone-red   .cohort-icon { background:#3a2222; color:#f0a8a8; }
body.dark-mode .trainer-view .cohort-card.tone-gold  .cohort-icon { background:#3a2f0d; color:#f0d060; }
body.dark-mode .trainer-view .cohort-card.tone-green .cohort-icon { background:#16351f; color:#86e0a0; }
body.dark-mode .trainer-view .cohort-card.tone-grey  .cohort-icon { background:#2d3340; color:#9ca3af; }
body.dark-mode .trainer-view .cohort-card.tone-blue  .cohort-icon { background:#16233d; color:#8fb4f0; }
body.dark-mode .trainer-view .cohort-name { color: #e2e8f0; }
body.dark-mode .trainer-view .cohort-code { color: #6b7280; }

body.dark-mode .trainer-view .empty-block {
    background: #181c26; border-color: #2d3748; color: #6b7280;
}

body.dark-mode .trainer-view .comment-box {
    background: #181c26; border-color: #2d3748; color: #cbd5e0;
}
body.dark-mode .trainer-view .comment-box.empty { color: #6b7280; }

/* Print-friendly */
@media print {
    .trainer-view .admin-action-bar .btn,
    .trainer-view .ie-pencil { display: none !important; }
    .trainer-view .stat-chip:hover,
    .trainer-view .cohort-card:hover { transform: none !important; box-shadow: none !important; }
}
</style>
@endpush

@section('content')

<div class="content-wrapper trainer-view">

    {{-- ── Page heading ── --}}
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0 page-title">
                        <i class="fas fa-chalkboard-teacher mr-2"></i>Trainer Profile
                        <small class="text-muted ml-2" style="font-size:.7rem;font-weight:400;">COSECSA ToT (Training of Trainers) roster</small>
                    </h1>
                </div>
            </div>
        </div>
    </section>

    <div class="col-md-12">
        @include('_message')
    </div>

    @if ($trainer)
    @php
        // Prepare derived view data once
        $countries      = collect($trainer->countries ?? []);
        $totYears       = collect($trainer->tot_years ?? []);
        $hasHospital    = !empty($trainer->hospital);
        $hasProgramme   = !empty($trainer->programme_id);
        $isMasterTrainer = !empty($trainer->is_master_trainer);
        $isSpecialtySurgeon = !empty($trainer->is_specialty_surgeon);

        // Avatar initials — first letters of first and last words of the name
        $nameParts = array_values(array_filter(preg_split('/\s+/', trim($trainer->name ?? ''))));
        $initials = '';
        if (count($nameParts) >= 2) {
            $initials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts) - 1], 0, 1));
        } elseif (count($nameParts) === 1) {
            $initials = strtoupper(substr($nameParts[0], 0, 2));
        } else {
            $initials = 'TT';
        }
    @endphp

    <section class="content" style="padding-top:0">
        <div class="container-fluid">

            {{-- ── Admin action bar ── --}}
            <div class="admin-action-bar">
                <div class="action-name">
                    <i class="fas fa-id-card"></i>
                    {{ $trainer->name }}
                    <span class="text-muted font-weight-normal">(#{{ $trainer->id }})</span>
                </div>
                <div class="d-flex flex-wrap" style="gap:6px;">
                    <a href="{{ url('admin/associates/trainers/edit/' . $trainer->id) }}"
                       class="btn btn-sm btn-cosecsa-gold">
                        <i class="fas fa-edit mr-1"></i> Edit Trainer
                    </a>
                    <a href="{{ url('admin/associates/trainers/list') }}"
                       class="btn btn-sm btn-cosecsa">
                        <i class="fas fa-arrow-left mr-1"></i> Back to List
                    </a>
                </div>
            </div>

            <div class="row">

                {{-- ══ LEFT PANEL — profile card ══ --}}
                <div class="col-md-4">
                    <div class="card profile-card">
                        <div class="card-body head text-center pt-4 pb-3">
                            {{-- Avatar --}}
                            <div class="trainer-avatar" title="{{ $trainer->name }}">{{ $initials }}</div>

                            {{-- Name + organisation --}}
                            <p class="trainer-name">{{ $trainer->name }}</p>
                            <p class="trainer-org">
                                @if($hasHospital)
                                    {{ $trainer->hospital->name }}
                                @else
                                    {{ $trainer->organisation ?: '—' }}
                                @endif
                            </p>
                            <p class="trainer-id-line">Trainer ID #{{ $trainer->id }}</p>

                            {{-- Status / role pills --}}
                            <div class="mt-2 mb-1 d-flex justify-content-center flex-wrap">
                                @if($isMasterTrainer)
                                    <span class="role-pill role-master"><i class="fas fa-medal mr-1"></i>Master Trainer</span>
                                @else
                                    <span class="role-pill role-no">Not a Master Trainer</span>
                                @endif
                                @if($isSpecialtySurgeon)
                                    <span class="role-pill role-ss"><i class="fas fa-user-md mr-1"></i>Safe Surgery</span>
                                @else
                                    <span class="role-pill role-no">Not Safe Surgery</span>
                                @endif
                            </div>
                        </div>

                        <div class="card-body pt-3">

                            {{-- ToT cohorts attended — short badges --}}
                            <div class="sect-div">ToT Cohorts Attended</div>
                            @if($totYears->isNotEmpty())
                                @foreach($totYears as $year)
                                    @php $tone = ['red','gold','green','grey','blue'][$loop->iteration % 5]; @endphp
                                    <span class="tag-pill tag-{{ $tone }}" title="{{ $year->label_full }}">
                                        {{ $year->label_short }}
                                    </span>
                                @endforeach
                            @else
                                <span class="tag-pill tag-empty">No cohorts recorded</span>
                            @endif

                            {{-- Contact --}}
                            <div class="sect-div mt-3">Contact</div>

                            @if($trainer->email)
                            <div class="info-row">
                                <span class="info-icon"><i class="fas fa-envelope"></i></span>
                                <span>
                                    <span class="info-label">Email</span>
                                    <span class="info-text">
                                        <a href="mailto:{{ $trainer->email }}">{{ $trainer->email }}</a>
                                    </span>
                                </span>
                            </div>
                            @endif

                            <div class="info-row">
                                <span class="info-icon"><i class="fas fa-flag"></i></span>
                                <span>
                                    <span class="info-label">Country Attended In</span>
                                    <span class="info-text">
                                        @forelse($countries as $c)
                                            <a href="{{ url('admin/countries/view/' . $c->id) }}">{{ $c->name }}</a>@if(!$loop->last), @endif
                                        @empty
                                            {{ $trainer->country_name_raw ?: '—' }}
                                        @endforelse
                                    </span>
                                </span>
                            </div>

                            @if($hasHospital)
                            <div class="info-row">
                                <span class="info-icon"><i class="fas fa-hospital"></i></span>
                                <span>
                                    <span class="info-label">Hospital</span>
                                    <span class="info-text">
                                        <a href="{{ url('admin/hospital/view_hospital/' . $trainer->hospital->id) }}">{{ $trainer->hospital->name }}</a>
                                        @if($trainer->organisation && $trainer->organisation !== $trainer->hospital->name)
                                            <br><small class="text-muted">Source: {{ $trainer->organisation }}</small>
                                        @endif
                                    </span>
                                </span>
                            </div>
                            @else
                            <div class="info-row">
                                <span class="info-icon"><i class="fas fa-hospital"></i></span>
                                <span>
                                    <span class="info-label">Organisation</span>
                                    <span class="info-text">{{ $trainer->organisation ?: '—' }}</span>
                                </span>
                            </div>
                            @endif

                            @if($trainer->specialty)
                            <div class="info-row">
                                <span class="info-icon"><i class="fas fa-stethoscope"></i></span>
                                <span>
                                    <span class="info-label">Specialty</span>
                                    <span class="info-text">
                                        @if($hasProgramme)
                                            <a href="{{ url('admin/programmes/view/' . $trainer->programme_id) }}">{{ $trainer->specialty }}</a>
                                        @else
                                            {{ $trainer->specialty }}
                                            @if(!empty($trainer->is_subspecialty))
                                                <span class="badge badge-light border">subspecialty</span>
                                            @endif
                                        @endif
                                    </span>
                                </span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                {{-- /.left panel --}}

                {{-- ══ RIGHT PANEL — stat chips + detail card ══ --}}
                <div class="col-md-8">

                    {{-- ── Stat chips row ── --}}
                    <div class="row mb-3">
                        <div class="col-6 col-md-3 mb-2">
                            <div class="stat-chip border-red">
                                <div class="chip-icon bg-red"><i class="fas fa-calendar-alt"></i></div>
                                <div>
                                    <div class="chip-label">ToT Cohorts</div>
                                    <strong class="chip-val">{{ $totYears->count() }}</strong>
                                    <div class="chip-sub">{{ $totYears->count() === 1 ? 'cohort' : 'cohorts' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-2">
                            <div class="stat-chip border-gold">
                                <div class="chip-icon bg-gold"><i class="fas fa-globe-africa"></i></div>
                                <div>
                                    <div class="chip-label">Countries</div>
                                    <strong class="chip-val">{{ $countries->count() }}</strong>
                                    <div class="chip-sub">{{ $countries->count() === 1 ? 'country' : 'countries' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-2">
                            <div class="stat-chip @if($isMasterTrainer)border-gold@else border-grey @endif">
                                <div class="chip-icon @if($isMasterTrainer)bg-gold@else bg-grey @endif">
                                    <i class="fas fa-medal"></i>
                                </div>
                                <div>
                                    <div class="chip-label">Master Trainer</div>
                                    @if($isMasterTrainer)
                                        <strong class="chip-val" style="color:#7a5600;">Yes</strong>
                                    @else
                                        <strong class="chip-val text-muted">No</strong>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3 mb-2">
                            <div class="stat-chip @if($isSpecialtySurgeon)border-blue@else border-grey @endif">
                                <div class="chip-icon @if($isSpecialtySurgeon)bg-blue@else bg-grey @endif">
                                    <i class="fas fa-user-md"></i>
                                </div>
                                <div>
                                    <div class="chip-label">Safe Surgery</div>
                                    @if($isSpecialtySurgeon)
                                        <strong class="chip-val" style="color:#007bff;">Yes</strong>
                                    @else
                                        <strong class="chip-val text-muted">No</strong>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Detail card ── --}}
                    <div class="card detail-card">
                        <div class="card-body pt-3">

                            {{-- Identity --}}
                            <p class="sect-div">Identity</p>
                            <div class="field-row">
                                <span class="field-lbl">Full Name</span>
                                <span class="field-val">{{ $trainer->name }}</span>
                            </div>
                            <div class="field-row">
                                <span class="field-lbl">Organisation</span>
                                <span class="field-val">
                                    @if($trainer->organisation)
                                        {{ $trainer->organisation }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </span>
                            </div>
                            <div class="field-row">
                                <span class="field-lbl">Linked Hospital</span>
                                <span class="field-val">
                                    @if($hasHospital)
                                        <a href="{{ url('admin/hospital/view_hospital/' . $trainer->hospital->id) }}">{{ $trainer->hospital->name }}</a>
                                        @if($trainer->organisation && $trainer->organisation !== $trainer->hospital->name)
                                            <span class="text-muted small ml-1">(source: {{ $trainer->organisation }})</span>
                                        @endif
                                    @else
                                        <span class="text-muted">Not linked — showing organisation only</span>
                                    @endif
                                </span>
                            </div>
                            <div class="field-row">
                                <span class="field-lbl">Email</span>
                                <span class="field-val">
                                    @if($trainer->email)
                                        <a href="mailto:{{ $trainer->email }}">{{ $trainer->email }}</a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </span>
                            </div>
                            <div class="field-row">
                                <span class="field-lbl">Country Attended In</span>
                                <span class="field-val">
                                    @forelse($countries as $c)
                                        <a href="{{ url('admin/countries/view/' . $c->id) }}">{{ $c->name }}</a>@if(!$loop->last), @endif
                                    @empty
                                        @if($trainer->country_name_raw)
                                            {{ $trainer->country_name_raw }}
                                            <span class="badge badge-light border ml-1" title="Free-text from spreadsheet, no matched country">unmatched</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    @endforelse
                                </span>
                            </div>

                            {{-- Specialty --}}
                            <p class="sect-div mt-3">Specialty</p>
                            <div class="field-row">
                                <span class="field-lbl">Specialty Label</span>
                                <span class="field-val">
                                    @if($trainer->specialty)
                                        {{ $trainer->specialty }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </span>
                            </div>
                            <div class="field-row">
                                <span class="field-lbl">COSECSA Programme</span>
                                <span class="field-val">
                                    @if($hasProgramme)
                                        <a href="{{ url('admin/programmes/view/' . $trainer->programme_id) }}">{{ $trainer->specialty }}</a>
                                    @else
                                        @if(!empty($trainer->is_subspecialty))
                                            <span class="role-pill role-no">Unmatched subspecialty</span>
                                            <span class="text-muted ml-1">— best-effort label, not linked to a COSECSA Fellowship programme</span>
                                        @else
                                            <span class="text-muted">Not linked to a COSECSA programme</span>
                                        @endif
                                    @endif
                                </span>
                            </div>

                            {{-- ToT History --}}
                            <p class="sect-div mt-3">ToT Cohort History</p>
                            @forelse($totYears as $year)
                                @php $tone = ['red','gold','green','grey','blue'][$loop->iteration % 5]; @endphp
                                <div class="cohort-card tone-{{ $tone }}">
                                    <div class="cohort-icon"><i class="fas fa-calendar-alt"></i></div>
                                    <div>
                                        <div class="cohort-name">{{ $year->label_full }}</div>
                                        <div class="cohort-code">Code · {{ $year->code }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="empty-block">
                                    <i class="fas fa-calendar-times mr-1"></i>
                                    No ToT cohorts recorded for this trainer.
                                </div>
                            @endforelse

                            {{-- Admin Notes --}}
                            <p class="sect-div mt-3">Admin Notes</p>
                            <div class="comment-box {{ $trainer->comment ? '' : 'empty' }}">
                                <span class="ie-field" data-ie="comment" data-ie-type="text"
                                      data-ie-value="{{ $trainer->comment ?? '' }}"
                                      data-ie-url="{{ url('admin/associates/trainers/'.$trainer->id.'/quick-update') }}"
                                      data-ie-csrf="{{ csrf_token() }}"
                                      data-ie-label="Comment">
                                    <span class="ie-value">
                                        @if($trainer->comment)
                                            {{ $trainer->comment }}
                                        @else
                                            <i class="fas fa-pen-line mr-1"></i>No comment — click the pencil to add one.
                                        @endif
                                    </span>
                                    <button class="ie-pencil" type="button" title="Edit comment"><i class="fas fa-pen"></i></button>
                                </span>
                            </div>

                        </div>

                        @include('partials.associate_notes', ['associateType' => 'trainer', 'associateId' => $trainer->id, 'notes' => $notes])
                    </div>
                </div>
                {{-- /.right panel --}}

            </div>
        </div>
    </section>

    @else
    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-body text-center text-muted py-4">
                    <i class="fas fa-exclamation-circle mr-1" style="color:#a02626;"></i>
                    No Trainer Data found.
                </div>
            </div>
        </div>
    </section>
    @endif

</div>

@endsection