@extends('layout.app')

@push('styles')
<style>
    /* ── Hero ── */
    .prog-hero {
        background:linear-gradient(135deg,#a02626 0%,#7a1f1f 100%);
        border-radius:14px; padding:26px 28px; color:#fff; margin-bottom:1.25rem;
    }
    .prog-hero h4 { font-weight:700; margin:0 0 4px; font-size:1.3rem; }
    .prog-hero .meta { font-size:.85rem; opacity:.85; }
    .prog-hero .btn-edit {
        background:rgba(255,255,255,.15); color:#fff; border:1px solid rgba(255,255,255,.35);
        border-radius:8px; font-size:.83rem; font-weight:600; padding:.4rem 1rem; transition:background .15s,color .15s;
    }
    .prog-hero .btn-edit:hover { background:#fff; color:#a02626; }

    /* ── Stat strip ── */
    .prog-stats {
        display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:1px;
        background:#eee; border:1px solid #eee; border-radius:12px; overflow:hidden; margin-bottom:1.25rem;
    }
    .prog-stat { background:#fff; padding:14px 16px; text-align:center; }
    .prog-stat .lbl { font-size:.64rem; color:#999; text-transform:uppercase; letter-spacing:.05em; font-weight:600; }
    .prog-stat .val { font-size:1.25rem; font-weight:700; color:#222; margin-top:3px; line-height:1.1; }

    /* ── Tabs ── */
    .nav-tabs { border-bottom:1px solid #eee; }
    .nav-tabs .nav-link { color:#777; font-size:.86rem; font-weight:500; border:none; padding:.6rem .2rem; margin-right:1.6rem; border-radius:0; }
    .nav-tabs .nav-link.active { color:#a02626; border-bottom:2px solid #a02626; font-weight:700; background:none; }
    .tab-count {
        display:inline-block; background:#f1f1f1; color:#888; border-radius:20px;
        font-size:.68rem; font-weight:700; padding:1px 8px; margin-left:5px;
    }
    .nav-tabs .nav-link.active .tab-count { background:#a02626; color:#fff; }

    /* ── Card shell ── */
    .prog-card { background:#fff; border:1px solid #f0f0f0; border-radius:12px; overflow:hidden; }
    .prog-card + .prog-card { margin-top:1rem; }
    .prog-card-header { padding:14px 18px; border-bottom:1px solid #f2f2f2; font-size:.82rem; font-weight:700; color:#a02626; }
    .prog-card-header i { margin-right:6px; }

    /* ── Minimal table ── */
    .entity-table { margin:0; }
    .entity-table td, .entity-table th { vertical-align:middle; font-size:.86rem; border-color:#f2f2f2; }
    .entity-table thead th {
        background:#fafafa; color:#999; font-size:.68rem; text-transform:uppercase; letter-spacing:.05em;
        font-weight:700; border-bottom:1px solid #eee; border-top:none; padding:10px 16px;
    }
    .entity-table tbody td { padding:11px 16px; border-top:1px solid #f5f5f5; }
    .entity-table tbody tr:hover { background:#fbf7f7; }

    .pill { display:inline-flex; align-items:center; gap:5px; font-size:.76rem; font-weight:600; padding:2px 10px; border-radius:20px; }
    .pill-dot { width:6px; height:6px; border-radius:50%; }
    .pill-green { background:#e8f8ee; color:#1a8a4a; } .pill-green .pill-dot { background:#22c55e; }
    .pill-red   { background:#fdeeee; color:#a02626; } .pill-red .pill-dot   { background:#ef4444; }
    .pill-amber { background:#fef6e6; color:#a5720c; } .pill-amber .pill-dot { background:#f59e0b; }
    .pill-gray  { background:#f2f2f2; color:#888; }    .pill-gray .pill-dot  { background:#aaa; }

    .entity-link { color:#a02626; font-weight:600; text-decoration:none; }
    .entity-link:hover { text-decoration:underline; }
    .row-action {
        width:32px; height:32px; display:inline-flex; align-items:center; justify-content:center;
        border-radius:8px; color:#a02626; background:#fbf7f7; transition:background .15s;
    }
    .row-action:hover { background:#f1e0e0; color:#a02626; }

    .empty-state { text-align:center; padding:48px 20px; color:#aaa; font-size:.88rem; }
    .empty-state i { font-size:2.2rem; display:block; margin-bottom:10px; color:#e8d5d5; }

    .rate-good { color:#1a8a4a; font-weight:700; }
    .rate-bad  { color:#a02626; font-weight:700; }

    /* ── Dark mode ── */
    body.dark-mode .prog-stats, body.dark-mode .prog-stat { background:#1f2937; border-color:#374151; }
    body.dark-mode .prog-stat .lbl { color:#9ca3af; }
    body.dark-mode .prog-stat .val { color:#e5e7eb; }
    body.dark-mode .nav-tabs { border-bottom-color:#374151; }
    body.dark-mode .nav-tabs .nav-link { color:#9ca3af; }
    body.dark-mode .nav-tabs .nav-link.active { color:#f87171; border-bottom-color:#f87171; }
    body.dark-mode .tab-count { background:#374151; color:#d1d5db; }
    body.dark-mode .nav-tabs .nav-link.active .tab-count { background:#f87171; color:#1f2937; }
    body.dark-mode .prog-card { background:#1f2937; border-color:#374151; }
    body.dark-mode .prog-card-header { border-bottom-color:#374151; color:#f87171; }
    body.dark-mode .entity-table thead th { background:#111827; color:#9ca3af; border-bottom-color:#374151; }
    body.dark-mode .entity-table tbody td { border-top-color:#374151; color:#e5e7eb; }
    body.dark-mode .entity-table tbody tr:hover { background:#25303f; }
    body.dark-mode .pill-green { background:#0f3324; color:#4ade80; }
    body.dark-mode .pill-red   { background:#3a1f1f; color:#f87171; }
    body.dark-mode .pill-amber { background:#3a2f10; color:#fbbf24; }
    body.dark-mode .pill-gray  { background:#374151; color:#d1d5db; }
    body.dark-mode .row-action { background:#374151; color:#f87171; }
    body.dark-mode .row-action:hover { background:#4b2626; }
    body.dark-mode .rate-good { color:#4ade80; }
    body.dark-mode .rate-bad  { color:#f87171; }
</style>
@endpush

@php
    // Closure, not a named function — this view's compiled PHP is `include`d
    // fresh per request, but a global `function` declaration would still
    // fatal with "Cannot redeclare" if it's ever rendered twice in one
    // request (e.g. an AJAX preview reusing this same template).
    $progStatusPill = function ($status) {
        $s = strtolower($status ?? '');
        $map = [
            'active'  => ['pill-green', 'Active'],
            '1'       => ['pill-green', 'Active'],
            'expired' => ['pill-red', 'Expired'],
            '0'       => ['pill-red', 'Inactive'],
            'inactive'=> ['pill-red', 'Inactive'],
        ];
        [$class, $label] = $map[$s] ?? ['pill-gray', $status ?: '—'];
        return "<span class=\"pill {$class}\"><span class=\"pill-dot\"></span>{$label}</span>";
    };
@endphp

@section('content')
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header"></section>
        <div class="col-md-12">@include('_message')</div>

        <section class="content">
            <div class="container-wrapper">

                {{-- Breadcrumb --}}
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb" style="background:none;padding:0;font-size:.82rem;">
                        <li class="breadcrumb-item"><a href="{{ url('admin/programmes/list') }}" style="color:#a02626;">Programmes</a></li>
                        <li class="breadcrumb-item active">{{ $programme->name }}</li>
                    </ol>
                </nav>

                {{-- Hero --}}
                <div class="prog-hero">
                    <div class="d-flex justify-content-between align-items-start flex-wrap" style="gap:.5rem;">
                        <div>
                            <h4><i class="fas fa-stethoscope mr-2"></i>{{ $programme->name }}</h4>
                            <div class="meta">
                                @if($programme->programme_type)
                                    {{ $programme->programme_type }}
                                    @if($programme->duration) &nbsp;·&nbsp; {{ $programme->duration }} @endif
                                @endif
                            </div>
                        </div>
                        <a href="{{ url('admin/programmes/edit_programmes/'.$programme->id) }}" class="btn btn-edit">
                            <i class="fas fa-pen mr-1"></i> Edit
                        </a>
                    </div>
                </div>

                {{-- Stat strip --}}
                <div class="prog-stats">
                    <div class="prog-stat">
                        <div class="lbl">Hospitals</div>
                        <div class="val">{{ $hospitals->count() }}</div>
                    </div>
                    <div class="prog-stat">
                        <div class="lbl">Trainees</div>
                        <div class="val">{{ $trainees->count() }}</div>
                    </div>
                    <div class="prog-stat">
                        <div class="lbl">Fellows</div>
                        <div class="val">{{ $fellows->count() }}</div>
                    </div>
                    @if(isset($examResultsAll) && $examResultsAll->count())
                    <div class="prog-stat">
                        <div class="lbl">Exam Records</div>
                        <div class="val">{{ $examResultsAll->count() }}</div>
                    </div>
                    @endif
                    @if($programme->duration)
                    <div class="prog-stat">
                        <div class="lbl">Duration</div>
                        <div class="val">{{ $programme->duration }}</div>
                    </div>
                    @endif
                    @if($programme->entry_fee)
                    <div class="prog-stat">
                        <div class="lbl">Entry Fee</div>
                        <div class="val">{{ number_format($programme->entry_fee) }}</div>
                    </div>
                    @endif
                    @if($programme->exam_fee)
                    <div class="prog-stat">
                        <div class="lbl">Exam Fee</div>
                        <div class="val">{{ number_format($programme->exam_fee) }}</div>
                    </div>
                    @endif
                </div>

                {{-- Tabs --}}
                <ul class="nav nav-tabs mb-3" id="progTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#pane-hospitals" role="tab">
                            Accredited Hospitals <span class="tab-count">{{ $hospitals->count() }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#pane-trainees" role="tab">
                            Trainees <span class="tab-count">{{ $trainees->count() }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#pane-fellows" role="tab">
                            Fellows <span class="tab-count">{{ $fellows->count() }}</span>
                        </a>
                    </li>
                    @if(isset($examResultsAll) && $examResultsAll->count())
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#pane-results" role="tab">
                            Exam Results <span class="tab-count">{{ $examResultsAll->count() }}</span>
                        </a>
                    </li>
                    @endif
                </ul>

                <div class="tab-content">

                    {{-- ── Hospitals tab ── --}}
                    <div class="tab-pane fade show active" id="pane-hospitals" role="tabpanel">
                        <div class="prog-card">
                            @if($hospitals->count())
                            <table class="table entity-table">
                                <thead>
                                    <tr>
                                        <th>Hospital</th>
                                        <th>Country</th>
                                        <th>Accredited</th>
                                        <th>Expires</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($hospitals as $h)
                                    <tr>
                                        <td>
                                            <a href="{{ url('admin/hospital/view_hospital/'.$h->hospital_id) }}" class="entity-link">
                                                {{ $h->hospital_name }}
                                            </a>
                                        </td>
                                        <td>{{ $h->country_name }}</td>
                                        <td>{{ $h->accredited_date ? \Carbon\Carbon::parse($h->accredited_date)->format('M Y') : '-' }}</td>
                                        <td>{{ $h->expiry_date    ? \Carbon\Carbon::parse($h->expiry_date)->format('M Y')    : '-' }}</td>
                                        <td>{!! $progStatusPill($h->status) !!}</td>
                                        <td class="text-right">
                                            <a href="{{ url('admin/hospital/view_hospital/'.$h->hospital_id) }}" class="row-action">
                                                <i class="fas fa-arrow-right"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @else
                            <div class="empty-state"><i class="fas fa-hospital-alt"></i>No hospitals accredited for this programme.</div>
                            @endif
                        </div>
                    </div>

                    {{-- ── Trainees tab ── --}}
                    <div class="tab-pane fade" id="pane-trainees" role="tabpanel">
                        <div class="prog-card">
                            @if($trainees->count())
                            <table class="table entity-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Hospital</th>
                                        <th>Admission Year</th>
                                        <th>Status</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($trainees as $t)
                                    <tr>
                                        <td>
                                            <a href="{{ url('admin/associates/trainees/view/'.$t->trainee_id) }}" class="entity-link">
                                                {{ $t->name }}
                                            </a>
                                        </td>
                                        <td>{{ $t->email ?: '-' }}</td>
                                        <td>
                                            @if($t->hospital_id)
                                            <a href="{{ url('admin/hospital/view_hospital/'.$t->hospital_id) }}" class="entity-link">
                                                {{ $t->hospital_name }}
                                            </a>
                                            @else -
                                            @endif
                                        </td>
                                        <td>{{ $t->admission_year ?: '-' }}</td>
                                        <td>{!! $progStatusPill($t->status) !!}</td>
                                        <td class="text-right">
                                            <a href="{{ url('admin/associates/trainees/view/'.$t->trainee_id) }}" class="row-action">
                                                <i class="fas fa-arrow-right"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @else
                            <div class="empty-state"><i class="fas fa-user-graduate"></i>No trainees enrolled in this programme.</div>
                            @endif
                        </div>
                    </div>

                    {{-- ── Fellows tab ── --}}
                    <div class="tab-pane fade" id="pane-fellows" role="tabpanel">
                        <div class="prog-card">
                            @if($fellows->count())
                            <table class="table entity-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Country</th>
                                        <th>Fellowship Year</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($fellows as $f)
                                    <tr>
                                        <td>
                                            <a href="{{ url('admin/associates/fellows/view/'.$f->fellow_id) }}" class="entity-link">
                                                {{ $f->name }}
                                            </a>
                                        </td>
                                        <td>{{ $f->email ?: '-' }}</td>
                                        <td>{{ $f->country_name ?: '-' }}</td>
                                        <td>{{ $f->fellowship_year ?: '-' }}</td>
                                        <td class="text-right">
                                            <a href="{{ url('admin/associates/fellows/view/'.$f->fellow_id) }}" class="row-action">
                                                <i class="fas fa-arrow-right"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @else
                            <div class="empty-state"><i class="fas fa-award"></i>No fellows have completed this programme.</div>
                            @endif
                        </div>
                    </div>

                    {{-- ── Results tab ── --}}
                    @if(isset($examResultsAll) && $examResultsAll->count())
                    <div class="tab-pane fade" id="pane-results" role="tabpanel">
                        <div class="prog-card">
                            <div class="prog-card-header"><i class="fas fa-chart-bar"></i>Results by Year</div>
                            <table class="table entity-table">
                                <thead>
                                    <tr>
                                        <th>Year</th>
                                        <th class="text-center">Pass</th>
                                        <th class="text-center">Fail</th>
                                        <th class="text-center">Absent</th>
                                        <th class="text-center">Total</th>
                                        <th class="text-center">Pass Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($examResultsByYear as $year => $rows)
                                    @php
                                        $passN   = $rows->firstWhere('result','Pass')->n  ?? 0;
                                        $failN   = $rows->firstWhere('result','Fail')->n  ?? 0;
                                        $absentN = $rows->firstWhere('result','Absent')->n ?? 0;
                                        $total   = $passN + $failN + $absentN;
                                        $rate    = ($passN + $failN) > 0 ? round($passN / ($passN + $failN) * 100) : null;
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $year }}</strong></td>
                                        <td class="text-center">
                                            @if($passN)<span class="pill pill-green">{{ $passN }}</span>@else -@endif
                                        </td>
                                        <td class="text-center">
                                            @if($failN)<span class="pill pill-red">{{ $failN }}</span>@else -@endif
                                        </td>
                                        <td class="text-center">
                                            @if($absentN)<span class="pill pill-amber">{{ $absentN }}</span>@else -@endif
                                        </td>
                                        <td class="text-center">{{ $total }}</td>
                                        <td class="text-center">
                                            @if($rate !== null)
                                                <span class="{{ $rate >= 50 ? 'rate-good' : 'rate-bad' }}">{{ $rate }}%</span>
                                            @else -@endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="prog-card">
                            <div class="prog-card-header"><i class="fas fa-list"></i>All Results</div>
                            <table class="table entity-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Year</th>
                                        <th>Exam Type</th>
                                        <th class="text-center">Score</th>
                                        <th class="text-center">Result</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($examResultsAll as $r)
                                    @php
                                        $rRes = strtolower($r->result ?? '');
                                        $rPill = $rRes === 'pass' ? 'pill-green' : ($rRes === 'fail' ? 'pill-red' : ($rRes === 'absent' ? 'pill-amber' : 'pill-gray'));
                                        $displayName = $r->trainee_name ?: $r->contact_name;
                                    @endphp
                                    <tr>
                                        <td>
                                            @if($r->trainee_id)
                                            <a href="{{ url('admin/associates/trainees/view/'.$r->trainee_id) }}" class="entity-link">
                                                {{ $displayName }}
                                            </a>
                                            @else
                                            {{ $displayName }}
                                            @endif
                                        </td>
                                        <td>{{ $r->exam_year }}</td>
                                        <td>{{ $r->exam_type ?? '-' }}</td>
                                        <td class="text-center">{{ $r->score !== null ? number_format($r->score, 2) : '-' }}</td>
                                        <td class="text-center">
                                            <span class="pill {{ $rPill }}">{{ strtoupper($r->result ?? '-') }}</span>
                                        </td>
                                        <td class="text-right">
                                            @if($r->trainee_id)
                                            <a href="{{ url('admin/associates/trainees/view/'.$r->trainee_id) }}" class="row-action">
                                                <i class="fas fa-arrow-right"></i>
                                            </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @endif

                </div>{{-- /.tab-content --}}

            </div>
        </section>
    </div>
</div>
@endsection
