@extends('layout.app')

@push('styles')
<style>
    .prog-hero { background:linear-gradient(135deg,#a02626 0%,#7a1f1f 100%); border-radius:10px;
                 padding:22px 24px; color:#fff; margin-bottom:1.2rem; }
    .prog-hero h4 { font-weight:700; margin:0 0 4px; }
    .prog-hero .meta { font-size:.85rem; opacity:.85; }

    .prog-info-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:1rem; margin-bottom:1.2rem; }
    .prog-info-chip { background:#fff; border:1px solid #e9ecef; border-radius:8px; padding:12px 14px; text-align:center; }
    .prog-info-chip .lbl { font-size:.66rem; color:#999; text-transform:uppercase; letter-spacing:.04em; }
    .prog-info-chip .val { font-size:1rem; font-weight:700; color:#222; margin-top:2px; }

    .nav-tabs .nav-link        { color:#555; font-size:.87rem; }
    .nav-tabs .nav-link.active { color:#a02626; border-bottom:2px solid #a02626; font-weight:600; }
    .tab-count { display:inline-block; background:#e8d5d5; color:#a02626; border-radius:10px;
                 font-size:.7rem; font-weight:700; padding:1px 7px; margin-left:4px; }
    .nav-tabs .nav-link.active .tab-count { background:#a02626; color:#fff; }

    .entity-table td, .entity-table th { vertical-align:middle; font-size:.875rem; }
    .entity-table thead th { background:#f8f0f0; color:#a02626; font-size:.75rem; text-transform:uppercase;
                             letter-spacing:.05em; border-bottom:2px solid #e8d5d5; }
    .dot { display:inline-block; width:8px; height:8px; border-radius:50%; margin-right:4px; }
    .dot-active, .dot-1 { background:#22c55e; }
    .dot-inactive, .dot-0 { background:#ef4444; }
    .dot-Active { background:#22c55e; }
    .dot-Expired { background:#ef4444; }
    .entity-link { color:#a02626; font-weight:500; text-decoration:none; }
    .entity-link:hover { text-decoration:underline; }
    .empty-state { text-align:center; padding:32px; color:#aaa; font-size:.9rem; }
    .empty-state i { font-size:2rem; display:block; margin-bottom:8px; }

    body.dark-mode .prog-info-chip { background:#374151 !important; border-color:#4a5568 !important; }
    body.dark-mode .prog-info-chip .lbl { color:#9ca3af !important; }
    body.dark-mode .prog-info-chip .val { color:#e0e0e0 !important; }
    body.dark-mode .entity-table thead th { background:#374151 !important; color:#f87171 !important; border-bottom-color:#4a5568 !important; }
    body.dark-mode .entity-table td, body.dark-mode .entity-table th { border-color:#4a5568 !important; color:#e0e0e0 !important; }
    body.dark-mode .nav-tabs .nav-link { color:#9ca3af; }
    body.dark-mode .nav-tabs .nav-link.active { color:#f87171; border-bottom-color:#f87171; }
    body.dark-mode .tab-count { background:#4a5568; color:#e0e0e0; }
    body.dark-mode .nav-tabs .nav-link.active .tab-count { background:#f87171; color:#fff; }
</style>
@endpush

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
                        <a href="{{ url('admin/programmes/edit_programmes/'.$programme->id) }}"
                           class="btn btn-sm" style="background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.3);">
                            <i class="fas fa-edit mr-1"></i> Edit
                        </a>
                    </div>
                </div>

                {{-- Info chips --}}
                <div class="prog-info-grid mb-3">
                    <div class="prog-info-chip">
                        <div class="lbl">Accredited Hospitals</div>
                        <div class="val">{{ $hospitals->count() }}</div>
                    </div>
                    <div class="prog-info-chip">
                        <div class="lbl">Trainees</div>
                        <div class="val">{{ $trainees->count() }}</div>
                    </div>
                    <div class="prog-info-chip">
                        <div class="lbl">Fellows</div>
                        <div class="val">{{ $fellows->count() }}</div>
                    </div>
                    @if(isset($examResultsAll) && $examResultsAll->count())
                    <div class="prog-info-chip">
                        <div class="lbl">Exam Records</div>
                        <div class="val">{{ $examResultsAll->count() }}</div>
                    </div>
                    @endif
                    @if($programme->duration)
                    <div class="prog-info-chip">
                        <div class="lbl">Duration</div>
                        <div class="val">{{ $programme->duration }}</div>
                    </div>
                    @endif
                    @if($programme->entry_fee)
                    <div class="prog-info-chip">
                        <div class="lbl">Entry Fee</div>
                        <div class="val">{{ number_format($programme->entry_fee) }}</div>
                    </div>
                    @endif
                    @if($programme->exam_fee)
                    <div class="prog-info-chip">
                        <div class="lbl">Exam Fee</div>
                        <div class="val">{{ number_format($programme->exam_fee) }}</div>
                    </div>
                    @endif
                </div>

                {{-- Tabs --}}
                <ul class="nav nav-tabs mb-3" id="progTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#pane-hospitals" role="tab">
                            <i class="fas fa-hospital-alt mr-1"></i>Accredited Hospitals
                            <span class="tab-count">{{ $hospitals->count() }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#pane-trainees" role="tab">
                            <i class="fas fa-user-graduate mr-1"></i>Trainees
                            <span class="tab-count">{{ $trainees->count() }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#pane-fellows" role="tab">
                            <i class="fas fa-award mr-1"></i>Fellows
                            <span class="tab-count">{{ $fellows->count() }}</span>
                        </a>
                    </li>
                    @if(isset($examResultsAll) && $examResultsAll->count())
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#pane-results" role="tab">
                            <i class="fas fa-chart-bar mr-1"></i>Exam Results
                            <span class="tab-count">{{ $examResultsAll->count() }}</span>
                        </a>
                    </li>
                    @endif
                </ul>

                <div class="tab-content">

                    {{-- ── Hospitals tab ── --}}
                    <div class="tab-pane fade show active" id="pane-hospitals" role="tabpanel">
                        <div class="card">
                            <div class="card-body p-0">
                                @if($hospitals->count())
                                <table class="table table-bordered table-striped entity-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Hospital</th>
                                            <th>Country</th>
                                            <th>Accredited</th>
                                            <th>Expires</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($hospitals as $i => $h)
                                        <tr>
                                            <td>{{ $i+1 }}</td>
                                            <td>
                                                <a href="{{ url('admin/hospital/view_hospital/'.$h->hospital_id) }}" class="entity-link">
                                                    {{ $h->hospital_name }}
                                                </a>
                                            </td>
                                            <td>{{ $h->country_name }}</td>
                                            <td>{{ $h->accredited_date ? \Carbon\Carbon::parse($h->accredited_date)->format('M Y') : '-' }}</td>
                                            <td>{{ $h->expiry_date    ? \Carbon\Carbon::parse($h->expiry_date)->format('M Y')    : '-' }}</td>
                                            <td>
                                                <span class="dot dot-{{ $h->status }}"></span>{{ $h->status }}
                                            </td>
                                            <td>
                                                <a href="{{ url('admin/hospital/view_hospital/'.$h->hospital_id) }}" class="btn btn-xs btn-light border">
                                                    <i class="fas fa-eye text-info"></i>
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
                    </div>

                    {{-- ── Trainees tab ── --}}
                    <div class="tab-pane fade" id="pane-trainees" role="tabpanel">
                        <div class="card">
                            <div class="card-body p-0">
                                @if($trainees->count())
                                <table class="table table-bordered table-striped entity-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Hospital</th>
                                            <th>Admission Year</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($trainees as $i => $t)
                                        <tr>
                                            <td>{{ $i+1 }}</td>
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
                                            <td>
                                                <span class="dot dot-{{ strtolower($t->status??'')=='active'?'active':'inactive' }}"></span>
                                                {{ $t->status ?: '—' }}
                                            </td>
                                            <td>
                                                <a href="{{ url('admin/associates/trainees/view/'.$t->trainee_id) }}" class="btn btn-xs btn-light border">
                                                    <i class="fas fa-eye text-info"></i>
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
                    </div>

                    {{-- ── Fellows tab ── --}}
                    <div class="tab-pane fade" id="pane-fellows" role="tabpanel">
                        <div class="card">
                            <div class="card-body p-0">
                                @if($fellows->count())
                                <table class="table table-bordered table-striped entity-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Country</th>
                                            <th>Fellowship Year</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($fellows as $i => $f)
                                        <tr>
                                            <td>{{ $i+1 }}</td>
                                            <td>
                                                <a href="{{ url('admin/associates/fellows/view/'.$f->fellow_id) }}" class="entity-link">
                                                    {{ $f->name }}
                                                </a>
                                            </td>
                                            <td>{{ $f->email ?: '-' }}</td>
                                            <td>{{ $f->country_name ?: '-' }}</td>
                                            <td>{{ $f->fellowship_year ?: '-' }}</td>
                                            <td>
                                                <a href="{{ url('admin/associates/fellows/view/'.$f->fellow_id) }}" class="btn btn-xs btn-light border">
                                                    <i class="fas fa-eye text-info"></i>
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
                    </div>

                    {{-- ── Results tab ── --}}
                    @if(isset($examResultsAll) && $examResultsAll->count())
                    <div class="tab-pane fade" id="pane-results" role="tabpanel">
                        <div class="card mb-3">
                            <div class="card-header" style="background:#fff5f5;border-bottom:1px solid #e8d5d5;">
                                <strong style="color:#a02626;font-size:.9rem;">
                                    <i class="fas fa-table mr-1"></i>Results by Year
                                </strong>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-bordered entity-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Year</th>
                                            <th class="text-center" style="color:#155724;">Pass</th>
                                            <th class="text-center" style="color:#721c24;">Fail</th>
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
                                                @if($passN)<span class="badge" style="background:#d4edda;color:#155724;">{{ $passN }}</span>@else -@endif
                                            </td>
                                            <td class="text-center">
                                                @if($failN)<span class="badge" style="background:#f8d7da;color:#721c24;">{{ $failN }}</span>@else -@endif
                                            </td>
                                            <td class="text-center">
                                                @if($absentN)<span class="badge" style="background:#fff3cd;color:#856404;">{{ $absentN }}</span>@else -@endif
                                            </td>
                                            <td class="text-center">{{ $total }}</td>
                                            <td class="text-center">
                                                @if($rate !== null)
                                                    <span style="font-weight:600;color:{{ $rate >= 50 ? '#155724' : '#721c24' }};">{{ $rate }}%</span>
                                                @else -@endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-header" style="background:#fff5f5;border-bottom:1px solid #e8d5d5;">
                                <strong style="color:#a02626;font-size:.9rem;">
                                    <i class="fas fa-list mr-1"></i>All Results
                                </strong>
                            </div>
                            <div class="card-body p-0">
                                <table class="table table-sm table-bordered table-striped entity-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Year</th>
                                            <th>Exam Type</th>
                                            <th class="text-center">Score</th>
                                            <th class="text-center">Result</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($examResultsAll as $i => $r)
                                        @php
                                            $rRes = strtolower($r->result ?? '');
                                            $rBg  = $rRes === 'pass'   ? '#d4edda' :
                                                    ($rRes === 'fail'   ? '#f8d7da' :
                                                    ($rRes === 'absent' ? '#fff3cd' : '#e9ecef'));
                                            $rClr = $rRes === 'pass'   ? '#155724' :
                                                    ($rRes === 'fail'   ? '#721c24' :
                                                    ($rRes === 'absent' ? '#856404' : '#495057'));
                                            $displayName = $r->trainee_name ?: $r->contact_name;
                                        @endphp
                                        <tr>
                                            <td>{{ $i + 1 }}</td>
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
                                                <span class="badge" style="background:{{ $rBg }};color:{{ $rClr }};padding:3px 8px;font-size:.8rem;">
                                                    {{ strtoupper($r->result ?? '-') }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($r->trainee_id)
                                                <a href="{{ url('admin/associates/trainees/view/'.$r->trainee_id) }}" class="btn btn-xs btn-light border">
                                                    <i class="fas fa-eye text-info"></i>
                                                </a>
                                                @else -
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>{{-- /.tab-content --}}

            </div>
        </section>
    </div>
</div>
@endsection
