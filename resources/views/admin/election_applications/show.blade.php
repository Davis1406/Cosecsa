@extends('layout.app')

@section('title', 'Fellowship by Election application')

@push('styles')
@include('admin.election_applications._style')
<style>
    .fe-grid { display:grid; grid-template-columns:minmax(0,1fr) 340px; gap:20px; align-items:start; }
    @media (max-width: 991px) { .fe-grid { grid-template-columns:1fr; } }
    .fe-aside { position:sticky; top:12px; }
    @media (max-width: 991px) { .fe-aside { position:static; } }
    .fe-back { color:var(--fe-muted) !important; font-size:.85rem; text-decoration:none !important; }
    .fe-back:hover { color:var(--fe-accent) !important; }
    .fe-id { display:flex; align-items:center; gap:16px; margin:10px 0 22px; }
    .fe-id h1 { font-size:1.5rem; font-weight:650; margin:0; letter-spacing:-.01em; }
    .fe-dl { display:grid; grid-template-columns:190px 1fr; }
    .fe-dl dt, .fe-dl dd { margin:0; padding:10px 0; border-top:1px solid var(--fe-line); font-size:.9rem; }
    .fe-dl dt:first-of-type, .fe-dl dd:first-of-type { border-top:0; }
    .fe-dl dt { color:var(--fe-muted); font-weight:550; padding-right:14px; }
    .fe-dl dd { white-space:pre-line; word-break:break-word; }
    .fe-dl .none { color:var(--fe-muted); opacity:.6; }
    @media (max-width: 575px) { .fe-dl { grid-template-columns:1fr; } .fe-dl dt { border-top:1px solid var(--fe-line); padding-bottom:0; } .fe-dl dd { border-top:0; padding-top:2px; } }
    .fe-doc { display:flex; align-items:center; gap:12px; padding:10px 0; border-top:1px solid var(--fe-line); }
    .fe-doc:first-child { border-top:0; }
    .fe-doc .ic { width:34px; height:34px; border-radius:8px; background:var(--fe-soft); color:var(--fe-muted); display:flex; align-items:center; justify-content:center; }
    .fe-doc.on .ic { background:var(--fe-accent-soft); color:var(--fe-accent); }
    .fe-doc .t { flex:1; font-size:.9rem; font-weight:550; }
    details.fe-more summary { cursor:pointer; list-style:none; }
    details.fe-more summary::-webkit-details-marker { display:none; }
    details.fe-more[open] .chev { transform:rotate(90deg); }
    .chev { transition:transform .15s; }
    .fe-callout { border-left:3px solid var(--fe-line); padding:2px 0 2px 12px; font-size:.86rem; margin-bottom:14px; color:var(--fe-text); }
    .fe-callout.info { border-color:var(--fe-info-fg); }
    .fe-callout.warn { border-color:var(--fe-warn-fg); }
    .fe-callout.ok { border-color:var(--fe-ok-fg); }
    .fe-callout a { text-decoration:underline; }
    .fe-gap { display:flex; align-items:flex-start; gap:10px; padding:9px 0; border-top:1px solid var(--fe-line); font-size:.88rem; cursor:pointer; margin:0; }
    .fe-gap:first-of-type { border-top:0; }
    .fe-gap input { margin-top:3px; accent-color:var(--fe-accent); }
    .fe-gap .l { font-weight:600; } .fe-gap .v { color:var(--fe-muted); word-break:break-word; }
    .fe-stack > * + * { margin-top:10px; }
</style>
@endpush

@section('content')
@php
    $docs = [
        'motivation' => 'Motivation letter',
        'reference1' => 'Reference 1' . ($app['referee1_name'] ? ' · ' . $app['referee1_name'] : ''),
        'reference2' => 'Reference 2' . ($app['referee2_name'] ? ' · ' . $app['referee2_name'] : ''),
        'cv'         => 'CV',
        'photo'      => 'Passport photograph',
    ];
    $available = collect($files)->pluck('slot')->all();
    $canManage = Auth::user()->hasPermission('fellows.manage');
    $e = fn ($v) => filled($v) ? e($v) : '<span class="none">—</span>';
    $dl = fn ($label, $value) => '<dt>' . e($label) . '</dt><dd>' . $e($value) . '</dd>';
    $initials = strtoupper(collect(preg_split('/\s+/', trim(preg_replace('/^(Dr|Prof|Mr|Mrs|Ms|Miss)\.?\s+/i', '', $app['full_name']))))->filter()->map(fn ($w) => mb_substr($w, 0, 1))->take(2)->implode(''));
    $isFellowRecord = $known && in_array($known['kind'], ['fellow', 'fellow_tentative'], true);
@endphp
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header"></section>
        <div class="col-md-12">@include('_message')</div>

        <section class="content">
            <div class="container-wrapper">
            <div class="fe">

                <a href="{{ url('admin/fellowship-by-election') }}" class="fe-back"><i class="fas fa-arrow-left mr-1"></i>All applications</a>

                <div class="fe-id">
                    <span class="fe-avatar lg">{{ $initials ?: '?' }}</span>
                    <div>
                        <h1>{{ $app['full_name'] }}</h1>
                        <div class="fe-sub">
                            Wufoo entry {{ $app['wufoo_entry_id'] }}@if ($app['submitted_at']) · applied {{ \Carbon\Carbon::parse($app['submitted_at'])->format('d M Y') }}@endif
                            · <span class="fe-pill {{ $app['status'] }}">{{ ucfirst($app['status']) }}</span>
                        </div>
                    </div>
                </div>

                <div class="fe-grid">
                    <div>
                        <div class="fe-panel">
                            <div class="fe-panel-h">Applicant</div>
                            <div class="fe-panel-b"><dl class="fe-dl">
                                {!! $dl('Name', trim(($app['title'] ?? '') . ' ' . ($app['firstname'] ?? '') . ' ' . ($app['middlename'] ?? '') . ' ' . ($app['lastname'] ?? ''))) !!}
                                {!! $dl('Gender', $app['gender']) !!}
                                {!! $dl('Email', $app['email']) !!}
                                {!! $dl('Phone', $app['phone']) !!}
                                {!! $dl('Country', $app['country']) !!}
                                {!! $dl('Home address', $app['address']) !!}
                                {!! $dl('Organisation(s)', $app['organisation']) !!}
                                {!! $dl('Job title(s)', $app['job_title']) !!}
                            </dl></div>
                        </div>

                        <div class="fe-panel">
                            <div class="fe-panel-h">Qualifications</div>
                            <div class="fe-panel-b"><dl class="fe-dl">
                                {!! $dl('Medical', $app['medical_qualifications']) !!}
                                {!! $dl('Specialty', $app['specialty_qualifications']) !!}
                                {!! $dl('Specialty date', $app['specialty_qualification_date']) !!}
                                {!! $dl('Other', $app['other_qualifications']) !!}
                                {!! $dl('Fellow of another College?', $app['other_fellowship']) !!}
                                @if (filled($app['other_fellowship_details']) || filled($app['other_fellowship_date']))
                                    {!! $dl('That Fellowship', $app['other_fellowship_details']) !!}
                                    {!! $dl('Date', $app['other_fellowship_date']) !!}
                                @endif
                            </dl></div>
                        </div>

                        <div class="fe-panel">
                            <div class="fe-panel-h">Documents</div>
                            <div class="fe-panel-b">
                                @foreach ($docs as $slot => $label)
                                    @php $has = in_array($slot, $available, true); @endphp
                                    <div class="fe-doc {{ $has ? 'on' : '' }}">
                                        <span class="ic"><i class="fas {{ $slot === 'photo' ? 'fa-image' : 'fa-file-alt' }}"></i></span>
                                        <span class="t">{{ $label }}</span>
                                        @if ($has)
                                            <a href="{{ route('admin.election.file', [$app['id'], $slot]) }}" target="_blank" rel="noopener" class="fe-btn">Open</a>
                                        @else
                                            <span class="fe-note">Not available yet</span>
                                        @endif
                                    </div>
                                @endforeach
                                @if (! count($available))
                                    <div class="fe-note mt-2">Documents open once Wufoo is connected live; the export has file names but no links.</div>
                                @endif
                            </div>
                        </div>

                        <details class="fe-panel fe-more">
                            <summary class="fe-panel-h"><span>Everything submitted</span><i class="fas fa-chevron-right chev"></i></summary>
                            <div class="fe-panel-b"><dl class="fe-dl">
                                @foreach ($answers as $a){!! $dl($a['label'], $a['value']) !!}@endforeach
                            </dl></div>
                        </details>
                    </div>

                    <div class="fe-aside fe-stack">
                        {{-- The decision --}}
                        <div class="fe-panel">
                            <div class="fe-panel-h">Decision</div>
                            <div class="fe-panel-b" style="padding-top:16px;">
                                @if ($app['status'] === 'approved')
                                    <div class="fe-callout ok"><i class="fas fa-check-circle mr-1"></i>Approved @if ($app['fellowship_year']) for {{ $app['fellowship_year'] }}@endif
                                        @if ($app['reviewed_at']) · {{ \Carbon\Carbon::parse($app['reviewed_at'])->format('d M Y') }}@endif</div>
                                @elseif ($app['status'] === 'rejected')
                                    <div class="fe-callout warn"><i class="fas fa-ban mr-1"></i>Rejected @if ($app['reviewed_at']) · {{ \Carbon\Carbon::parse($app['reviewed_at'])->format('d M Y') }}@endif</div>
                                @else
                                    <div class="fe-note mb-2">Waiting for a decision.</div>
                                @endif
                                @if ($app['review_note'])<div class="mb-3" style="white-space:pre-line;font-size:.88rem;">{{ $app['review_note'] }}</div>@endif

                                @if ($known)
                                    <div class="fe-callout {{ $known['kind'] === 'fellow' ? 'warn' : 'info' }}">
                                        @if ($known['kind'] === 'fellow')
                                            <strong>Already a Fellow</strong> · {{ $known['label'] }} (#{{ $known['id'] }}), same {{ $known['by'] }}. Approving is refused.
                                        @elseif ($known['kind'] === 'fellow_tentative')
                                            <strong>Already tentative</strong> · Fellow #{{ $known['id'] }}, {{ $known['label'] }}. Approving links them; nothing new is added.
                                        @else
                                            <strong>On the tentative list</strong> · {{ $known['label'] }}. Approving links them; nothing new is added.
                                        @endif
                                    </div>
                                @endif

                                @if ($canManage)
                                    @if ($app['status'] !== 'approved')
                                        <form method="POST" action="{{ route('admin.election.approve', $app['id']) }}">
                                            @csrf
                                            <label class="fe-label">Fellowship year</label>
                                            <input type="number" name="fellowship_year" value="{{ $defaultYear }}" min="2000" max="2100" class="fe-input mb-2" required>
                                            <label class="fe-label">Note (optional)</label>
                                            <textarea name="note" rows="2" class="fe-textarea mb-3">{{ old('note') }}</textarea>
                                            @if (session('can_force'))
                                                <input type="hidden" name="force" value="1">
                                                <button type="submit" class="fe-btn block" onclick="return confirm('Approve anyway? Only do this if this is a different person from the Fellow with the same name.');"><i class="fas fa-user-plus"></i>Different person: approve anyway</button>
                                            @elseif (! $known || $known['kind'] !== 'fellow')
                                                <button type="submit" class="fe-btn ok block"><i class="fas fa-check"></i>Approve</button>
                                            @else
                                                <button type="button" class="fe-btn block" disabled><i class="fas fa-check"></i>Already a Fellow</button>
                                            @endif
                                        </form>
                                    @endif

                                    <div class="d-flex mt-2" style="gap:8px;">
                                        @if ($app['status'] !== 'rejected')
                                            <form method="POST" action="{{ route('admin.election.review', $app['id']) }}" style="flex:1" onsubmit="var r=prompt('Reason for rejecting (optional):'); if (r === null) return false; this.note.value=r; return true;">
                                                @csrf
                                                <input type="hidden" name="status" value="rejected"><input type="hidden" name="note" value="">
                                                <button type="submit" class="fe-btn ghost-danger block"><i class="fas fa-times"></i>Reject</button>
                                            </form>
                                        @endif
                                        @if ($app['status'] !== 'pending')
                                            <form method="POST" action="{{ route('admin.election.review', $app['id']) }}" style="flex:1" onsubmit="return confirm('Set this application back to pending?{{ $app['status'] === 'approved' ? ' The tentative entry this approval made is removed.' : '' }}');">
                                                @csrf
                                                <input type="hidden" name="status" value="pending">
                                                <button type="submit" class="fe-btn block"><i class="fas fa-undo"></i>Back to pending</button>
                                            </form>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- The matching Fellow record and what it is missing --}}
                        @if ($fellow)
                            <div class="fe-panel" id="fellow-record">
                                <div class="fe-panel-h"><span>Fellow record</span><a href="{{ url($fellow['url']) }}" class="fe-note" style="text-decoration:underline;">Open #{{ $fellow['id'] }}</a></div>
                                <div class="fe-panel-b" style="padding-top:14px;">
                                    <div class="mb-2" style="font-size:.9rem;"><strong>{{ $fellow['name'] }}</strong> <span class="fe-note">· {{ $fellow['label'] }}</span></div>
                                    @if (count($fellow['missing']))
                                        <div class="fe-callout warn"><i class="fas fa-exclamation-circle mr-1"></i>This record is missing {{ count($fellow['missing']) }} {{ count($fellow['missing']) === 1 ? 'detail' : 'details' }} that this application has.</div>
                                        @if ($canManage)
                                            <form method="POST" action="{{ route('admin.election.fill', $app['id']) }}">
                                                @csrf
                                                @foreach ($fellow['missing'] as $m)
                                                    <label class="fe-gap"><input type="checkbox" name="fields[]" value="{{ $m['field'] }}" checked><span><span class="l">{{ $m['label'] }}</span><br><span class="v">{{ $m['value'] }}</span></span></label>
                                                @endforeach
                                                <button type="submit" class="fe-btn primary block mt-3"><i class="fas fa-fill-drip"></i>Fill the ticked details</button>
                                                <div class="fe-note mt-2">Only empty fields are filled. Nothing already on the record is changed.</div>
                                            </form>
                                        @else
                                            @foreach ($fellow['missing'] as $m)<div class="fe-gap" style="cursor:default;"><span><span class="l">{{ $m['label'] }}</span><br><span class="v">{{ $m['value'] }}</span></span></div>@endforeach
                                        @endif
                                    @else
                                        <div class="fe-callout ok mb-0"><i class="fas fa-check-circle mr-1"></i>Nothing missing: the record already has these details.</div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if (count($otherEntries))
                            <div class="fe-panel">
                                <div class="fe-panel-h">Other entries by this email</div>
                                <div class="fe-panel-b">
                                    @foreach ($otherEntries as $o)
                                        <div class="d-flex justify-content-between align-items-center py-2">
                                            <a href="{{ route('admin.election.show', $o['id']) }}" style="font-size:.9rem;">{{ $o['submitted_at'] ? \Carbon\Carbon::parse($o['submitted_at'])->format('d M Y') : 'Entry' }}</a>
                                            <span class="fe-pill {{ $o['status'] }}">{{ ucfirst($o['status']) }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

            </div>
            </div>
        </section>
    </div>
</div>
@endsection
