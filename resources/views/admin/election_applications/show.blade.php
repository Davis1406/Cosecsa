@extends('layout.app')

@section('title', 'Fellowship by Election application')

@push('styles')
<style>
    .fe-hero { background:linear-gradient(135deg,#a02626 0%,#7a1f1f 100%); border-radius:10px; padding:20px 24px; color:#fff; margin-bottom:1.2rem; }
    .detail-card { background:#fff; border-radius:8px; padding:20px; box-shadow:0 1px 6px rgba(0,0,0,.08); margin-bottom:20px; }
    body.dark-mode .detail-card { background:#374151; }
    .detail-card h6 { color:#a02626; font-weight:700; text-transform:uppercase; font-size:.78rem; letter-spacing:.04em; margin-bottom:14px; border-bottom:1px solid #eee; padding-bottom:8px; }
    .dl-row { display:flex; padding:6px 0; font-size:.9rem; border-bottom:1px solid #f5f5f5; }
    body.dark-mode .dl-row { border-color:#4a5568; }
    .dl-row .dl-label { width:190px; flex-shrink:0; color:#888; font-weight:600; }
    .dl-row .dl-value { flex:1; white-space:pre-line; word-break:break-word; }
    .fe-pill { display:inline-block; padding:3px 12px; border-radius:20px; font-weight:600; font-size:.78rem; }
    .fe-pending  { background:#fff3cd; color:#856404; }
    .fe-approved { background:#d4edda; color:#155724; }
    .fe-rejected { background:#f8d7da; color:#721c24; }
    .fe-note { font-size:.82rem; opacity:.85; }
</style>
@endpush

@section('content')
@php
    $docs = [
        'motivation' => 'Motivation letter',
        'reference1' => 'Reference 1' . ($app['referee1_name'] ? ' (' . $app['referee1_name'] . ')' : ''),
        'reference2' => 'Reference 2' . ($app['referee2_name'] ? ' (' . $app['referee2_name'] . ')' : ''),
        'cv'         => 'CV',
        'photo'      => 'Passport photograph',
    ];
    $available = collect($files)->pluck('slot')->all();
    $canManage = Auth::user()->hasPermission('fellows.manage');
    $row = fn ($label, $value) => '<div class="dl-row"><div class="dl-label">' . e($label) . '</div><div class="dl-value">' . (filled($value) ? e($value) : '—') . '</div></div>';
@endphp
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header"></section>
        <div class="col-md-12">@include('_message')</div>

        <section class="content">
            <div class="container-wrapper">

                <div class="fe-hero d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0"><i class="fas fa-vote-yea mr-2"></i>{{ $app['full_name'] }}</h4>
                        <div style="font-size:.85rem;opacity:.85;">
                            Wufoo entry {{ $app['wufoo_entry_id'] }}
                            @if ($app['submitted_at']) &middot; applied {{ \Carbon\Carbon::parse($app['submitted_at'])->format('d M Y') }} @endif
                        </div>
                    </div>
                    <a href="{{ url('admin/fellowship-by-election') }}" class="btn btn-light btn-sm font-weight-bold" style="color:#a02626;">
                        <i class="fas fa-arrow-left mr-1"></i>Back to list
                    </a>
                </div>

                <div class="row">
                    <div class="col-lg-8">
                        <div class="detail-card">
                            <h6>Applicant</h6>
                            {!! $row('Title', $app['title']) !!}
                            {!! $row('Name', trim(($app['firstname'] ?? '') . ' ' . ($app['middlename'] ?? '') . ' ' . ($app['lastname'] ?? ''))) !!}
                            {!! $row('Gender', $app['gender']) !!}
                            {!! $row('Phone', $app['phone']) !!}
                            {!! $row('Email', $app['email']) !!}
                            {!! $row('Home address', $app['address']) !!}
                            {!! $row('Country', $app['country']) !!}
                            {!! $row('Organisation(s)', $app['organisation']) !!}
                            {!! $row('Job title(s)', $app['job_title']) !!}
                        </div>

                        <div class="detail-card">
                            <h6>Qualifications</h6>
                            {!! $row('Medical qualification(s)', $app['medical_qualifications']) !!}
                            {!! $row('Specialty qualification(s)', $app['specialty_qualifications']) !!}
                            {!! $row('Specialty qualification date', $app['specialty_qualification_date']) !!}
                            {!! $row('Other qualification(s)', $app['other_qualifications']) !!}
                            {!! $row('Fellow of another College?', $app['other_fellowship']) !!}
                            @if (filled($app['other_fellowship_details']) || filled($app['other_fellowship_date']))
                                {!! $row('Details of that Fellowship', $app['other_fellowship_details']) !!}
                                {!! $row('Date of that Fellowship', $app['other_fellowship_date']) !!}
                            @endif
                        </div>

                        <div class="detail-card">
                            <h6>Referees and documents</h6>
                            {!! $row('Referee 1', $app['referee1_name']) !!}
                            {!! $row('Referee 2', $app['referee2_name']) !!}
                            <div class="mt-3">
                                @foreach ($docs as $slot => $label)
                                    @if (in_array($slot, $available, true))
                                        <a href="{{ route('admin.election.file', [$app['id'], $slot]) }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm mb-1">
                                            <i class="fas fa-file-download mr-1"></i>{{ $label }}
                                        </a>
                                    @else
                                        <span class="btn btn-outline-secondary btn-sm mb-1 disabled" title="The document opens after the next sync with Wufoo"><i class="fas fa-file mr-1"></i>{{ $label }}</span>
                                    @endif
                                @endforeach
                            </div>
                            @if (! count($available))
                                <div class="fe-note mt-2">The documents open once Wufoo is connected live (the list was loaded from an export, which has file names but no links).</div>
                            @endif
                        </div>

                        <div class="detail-card">
                            <h6>Everything the applicant submitted</h6>
                            @foreach ($answers as $a)
                                {!! $row($a['label'], $a['value']) !!}
                            @endforeach
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="detail-card">
                            <h6>Review</h6>
                            <div class="mb-2">
                                <span class="fe-pill fe-{{ $app['status'] }}">{{ ucfirst($app['status']) }}</span>
                                @if ($app['fellowship_year'] && $app['status'] === 'approved') <span class="fe-note ml-1">for {{ $app['fellowship_year'] }}</span> @endif
                            </div>
                            @if ($app['reviewed_at'])<div class="fe-note mb-2">Reviewed {{ \Carbon\Carbon::parse($app['reviewed_at'])->format('d M Y H:i') }}</div>@endif
                            @if ($app['review_note'])<div class="mb-2" style="white-space:pre-line;">{{ $app['review_note'] }}</div>@endif

                            @if ($known)
                                <div class="alert alert-{{ $known['kind'] === 'fellow' ? 'warning' : 'info' }} py-2 small mb-3">
                                    @if ($known['kind'] === 'fellow')
                                        <strong>Already a Fellow:</strong> {{ $known['label'] }} (#{{ $known['id'] }}), matched by {{ $known['by'] === 'email' ? 'the same email' : 'the same name' }}.
                                    @elseif ($known['kind'] === 'fellow_tentative')
                                        <strong>Already tentative:</strong> Fellow #{{ $known['id'] }} ({{ $known['label'] }}). Approving links this application to them; nothing new is added.
                                    @else
                                        <strong>Already on the tentative list:</strong> {{ $known['label'] }} (#{{ $known['id'] }}). Approving links this application to them; nothing new is added.
                                    @endif
                                </div>
                            @endif

                            @if ($canManage)
                                @if ($app['status'] !== 'approved')
                                    <form method="POST" action="{{ route('admin.election.approve', $app['id']) }}" class="mb-3">
                                        @csrf
                                        <label class="small font-weight-bold mb-0">Fellowship year</label>
                                        <input type="number" name="fellowship_year" value="{{ $defaultYear }}" min="2000" max="2100" class="form-control form-control-sm mb-2" required>
                                        <label class="small font-weight-bold mb-0">Note (optional)</label>
                                        <textarea name="note" rows="2" class="form-control form-control-sm mb-2">{{ old('note') }}</textarea>
                                        @if (session('can_force'))
                                            <input type="hidden" name="force" value="1">
                                            <button type="submit" class="btn btn-warning btn-block btn-sm" onclick="return confirm('Approve anyway? Only do this if this is a different person from the Fellow with the same name.');">
                                                <i class="fas fa-user-plus mr-1"></i>This is a different person: approve anyway
                                            </button>
                                        @else
                                            <button type="submit" class="btn btn-success btn-block btn-sm">
                                                <i class="fas fa-check mr-1"></i>Approve (tentative Fellow By Election)
                                            </button>
                                        @endif
                                    </form>
                                @endif

                                @if ($app['status'] !== 'rejected')
                                    <form method="POST" action="{{ route('admin.election.review', $app['id']) }}" class="mb-2" onsubmit="return confirm('Reject this application?');">
                                        @csrf
                                        <input type="hidden" name="status" value="rejected">
                                        <textarea name="note" rows="2" class="form-control form-control-sm mb-2" placeholder="Reason (optional)"></textarea>
                                        <button type="submit" class="btn btn-outline-danger btn-block btn-sm"><i class="fas fa-times mr-1"></i>Reject</button>
                                    </form>
                                @endif

                                @if ($app['status'] !== 'pending')
                                    <form method="POST" action="{{ route('admin.election.review', $app['id']) }}" onsubmit="return confirm('Set this application back to pending?{{ $app['status'] === 'approved' ? ' The tentative entry this approval made is removed.' : '' }}');">
                                        @csrf
                                        <input type="hidden" name="status" value="pending">
                                        <button type="submit" class="btn btn-outline-secondary btn-block btn-sm"><i class="fas fa-undo mr-1"></i>Back to pending</button>
                                    </form>
                                @endif
                            @endif
                        </div>

                        @if (count($otherEntries))
                            <div class="detail-card">
                                <h6>Other entries by this email</h6>
                                @foreach ($otherEntries as $o)
                                    <div class="dl-row">
                                        <div class="dl-value">
                                            <a href="{{ route('admin.election.show', $o['id']) }}">{{ $o['submitted_at'] ? \Carbon\Carbon::parse($o['submitted_at'])->format('d M Y') : 'Entry' }}</a>
                                            <span class="fe-pill fe-{{ $o['status'] }} ml-1">{{ ucfirst($o['status']) }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </section>
    </div>
</div>
@endsection
