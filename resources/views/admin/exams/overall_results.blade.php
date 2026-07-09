@extends('layout.app')

@push('styles')
<style>
    .or-hero { background:linear-gradient(135deg,#a02626 0%,#7a1f1f 100%); border-radius:10px;
               padding:20px 24px; color:#fff; margin-bottom:1.2rem; }
    .or-hero h4 { font-weight:700; margin:0 0 4px; }

    .filter-bar { background:#fff; border:1px solid #e9ecef; border-radius:8px;
                  padding:14px 16px; margin-bottom:1.2rem; }
    body.dark-mode .filter-bar { background:#374151; border-color:#4a5568; }

    .stat-chip { display:inline-flex; flex-direction:column; align-items:center;
                 background:#fff; border:1px solid #e9ecef; border-radius:8px;
                 padding:10px 18px; min-width:100px; text-align:center; }
    .stat-chip .lbl { font-size:.66rem; color:#999; text-transform:uppercase; letter-spacing:.04em; }
    .stat-chip .val { font-size:1.2rem; font-weight:700; color:#222; }
    body.dark-mode .stat-chip { background:#374151; border-color:#4a5568; }
    body.dark-mode .stat-chip .val { color:#e0e0e0; }

    .summary-table thead th { background:#f8f0f0; color:#a02626; font-size:.75rem;
                               text-transform:uppercase; letter-spacing:.04em; }
    .detail-table thead th  { background:#f8f0f0; color:#a02626; font-size:.75rem;
                               text-transform:uppercase; letter-spacing:.04em; }
    .summary-table td { vertical-align:middle; }
    .count-pill { display:inline-block; min-width:44px; padding:6px 4px;
                  border-radius:8px; font-size:1.05rem; font-weight:700; }
    .nav-tabs .nav-link.active { color:#a02626; border-bottom:2px solid #a02626; font-weight:600; }

    /* Pass/Fail/Absent/Rate pills — colored backgrounds stay readable in both
       light and dark mode, unlike plain dark-green/red text on a dark page. */
    .val-pill { display:inline-block; padding:3px 12px; border-radius:20px; font-weight:700; }
    .val-pass  { background:#d4edda; color:#155724; }
    .val-fail  { background:#f8d7da; color:#721c24; }
    .val-absent{ background:#fff3cd; color:#856404; }
    .val-rate-high { background:#d4edda; color:#155724; }
    .val-rate-low  { background:#f8d7da; color:#721c24; }
</style>
@endpush

@section('content')
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header"></section>
        <div class="col-md-12">@include('_message')</div>

        <section class="content">
            <div class="container-wrapper">

                {{-- Hero --}}
                <div class="or-hero">
                    <h4><i class="fas fa-chart-bar mr-2"></i>Overall Exam Results</h4>
                    <div style="font-size:.85rem;opacity:.85;">Historical pass/fail records from Capsule CRM — {{ $results->count() }} records</div>
                </div>

                {{-- Filters --}}
                <div class="filter-bar">
                    <form method="GET" action="{{ url('admin/exams/overall_results') }}"
                          class="d-flex flex-wrap align-items-end" style="gap:.75rem;">
                        <div>
                            <label class="d-block mb-1 small font-weight-bold text-muted">Year</label>
                            <select name="year" class="form-control form-control-sm" style="width:110px;">
                                <option value="">All years</option>
                                @foreach($years as $yr)
                                    <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>{{ $yr }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="d-block mb-1 small font-weight-bold text-muted">Programme</label>
                            <select name="programme_id" class="form-control form-control-sm" style="width:200px;">
                                <option value="">All programmes</option>
                                @foreach($programmes as $p)
                                    <option value="{{ $p->id }}" {{ $selectedProgramme == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="d-flex" style="gap:.4rem;">
                            <button type="submit" class="btn btn-danger btn-sm" style="white-space:nowrap;">
                                <i class="fas fa-filter mr-1"></i>Filter
                            </button>
                            <a href="{{ url('admin/exams/overall_results') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                        </div>
                    </form>
                </div>

                {{-- Quick stats --}}
                @php
                    $totalPass   = $results->where('result', 'Pass')->count();
                    $totalFail   = $results->where('result', 'Fail')->count();
                    $totalAbsent = $results->where('result', 'Absent')->count();
                    $passRate    = ($totalPass + $totalFail) > 0
                        ? round($totalPass / ($totalPass + $totalFail) * 100) : null;
                @endphp
                <div class="d-flex flex-wrap mb-3" style="gap:.75rem;">
                    <div class="stat-chip">
                        <span class="lbl">Total</span>
                        <span class="val">{{ $results->count() }}</span>
                    </div>
                    <div class="stat-chip">
                        <span class="lbl">Pass</span>
                        <span class="val"><span class="val-pill val-pass">{{ $totalPass }}</span></span>
                    </div>
                    <div class="stat-chip">
                        <span class="lbl">Fail</span>
                        <span class="val"><span class="val-pill val-fail">{{ $totalFail }}</span></span>
                    </div>
                    @if($totalAbsent)
                    <div class="stat-chip">
                        <span class="lbl">Absent</span>
                        <span class="val"><span class="val-pill val-absent">{{ $totalAbsent }}</span></span>
                    </div>
                    @endif
                    @if($passRate !== null)
                    <div class="stat-chip">
                        <span class="lbl">Pass Rate</span>
                        <span class="val"><span class="val-pill {{ $passRate >= 50 ? 'val-rate-high' : 'val-rate-low' }}">{{ $passRate }}%</span></span>
                    </div>
                    @endif
                </div>

                {{-- Tabs: Summary / Detail --}}
                <ul class="nav nav-tabs mb-3" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#tab-summary" role="tab">
                            <i class="fas fa-table mr-1"></i>Summary by Programme &amp; Year
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tab-detail" role="tab">
                            <i class="fas fa-list mr-1"></i>All Records
                            <span class="badge badge-secondary ml-1" style="font-size:.7rem;">{{ $results->count() }}</span>
                        </a>
                    </li>
                </ul>

                <div class="tab-content">

                    {{-- Summary tab --}}
                    <div class="tab-pane fade show active" id="tab-summary" role="tabpanel">
                        <div class="card">
                            <div class="card-body p-0">
                                <table id="summaryTable" class="table table-sm table-bordered table-striped summary-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Year</th>
                                            <th>Programme</th>
                                            <th class="text-center" style="color:#155724;">Pass</th>
                                            <th class="text-center" style="color:#721c24;">Fail</th>
                                            <th class="text-center">Absent</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Pass Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($summary as $year => $progs)
                                            @foreach($progs as $progName => $counts)
                                            @php
                                                $p = $counts['Pass']   ?? 0;
                                                $f = $counts['Fail']   ?? 0;
                                                $a = $counts['Absent'] ?? 0;
                                                $tot = $p + $f + $a;
                                                $rate = ($p + $f) > 0 ? round($p / ($p + $f) * 100) : null;
                                            @endphp
                                            <tr>
                                                <td>{{ $year }}</td>
                                                <td>{{ $progName }}</td>
                                                <td class="text-center">
                                                    @if($p)<span class="count-pill val-pass">{{ $p }}</span>@else <span class="text-muted">—</span>@endif
                                                </td>
                                                <td class="text-center">
                                                    @if($f)<span class="count-pill val-fail">{{ $f }}</span>@else <span class="text-muted">—</span>@endif
                                                </td>
                                                <td class="text-center">
                                                    @if($a)<span class="count-pill val-absent">{{ $a }}</span>@else <span class="text-muted">—</span>@endif
                                                </td>
                                                <td class="text-center"><span style="font-size:1.05rem;font-weight:700;">{{ $tot }}</span></td>
                                                <td class="text-center">
                                                    @if($rate !== null)
                                                        <span class="val-pill {{ $rate >= 50 ? 'val-rate-high' : 'val-rate-low' }}">{{ $rate }}%</span>
                                                    @else <span class="text-muted">—</span>@endif
                                                </td>
                                            </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Detail tab --}}
                    <div class="tab-pane fade" id="tab-detail" role="tabpanel">
                        <div class="card">
                            <div class="card-body p-0">
                                <table id="detailTable" class="table table-sm table-bordered table-striped detail-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Programme</th>
                                            <th>Year</th>
                                            <th class="text-center">Part I (%)</th>
                                            <th class="text-center">Part II (%)</th>
                                            <th class="text-center">Result</th>
                                            <th>Profile</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($results as $i => $r)
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
                                            <td>{{ $displayName }}</td>
                                            <td>{{ $r->programme_name ?? $r->specialty ?? '—' }}</td>
                                            <td>{{ $r->exam_year }}</td>
                                            <td class="text-center">
                                                {{ $r->part1_score !== null ? number_format($r->part1_score, 2) : '—' }}
                                            </td>
                                            <td class="text-center">
                                                {{ $r->part2_score !== null ? number_format($r->part2_score, 2) : '—' }}
                                            </td>
                                            <td class="text-center">
                                                <span class="badge" style="background:{{ $rBg }};color:{{ $rClr }};padding:3px 8px;font-size:.8rem;">
                                                    {{ strtoupper($r->result ?? '—') }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($r->trainee_id)
                                                <a href="{{ url('admin/associates/trainees/view/'.$r->trainee_id) }}"
                                                   class="btn btn-xs btn-light border" title="View trainee">
                                                    <i class="fas fa-eye text-info"></i>
                                                </a>
                                                @else —
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>{{-- /.tab-content --}}

            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    $('#summaryTable').DataTable({
        pageLength: 25,
        order: [[0, 'desc'], [1, 'asc']],
        columnDefs: [{ orderable: false, targets: [] }]
    });
    $('#detailTable').DataTable({
        pageLength: 25,
        order: [[3, 'desc'], [1, 'asc']]
    });
});
</script>
@endpush
