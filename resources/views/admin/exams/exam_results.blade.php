@extends('layout.app')

@section('content')
@php
    $a = $analysis ?? [];
    $max = $passMarks['max_total'] ?? 160;
    $passTotal = $passMarks['total'] ?? null;
    $passStation = $passMarks['station'] ?? null;
    $passPct = ($passTotal !== null && $max) ? round($passTotal / $max * 100, 1) : null;
@endphp

<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header"></section>

        <div class="col-md-12">
            @include('_message')
            <div id="rsAlert"></div>
        </div>

        <section class="content">
            <div class="container-wrapper">

                {{-- ───────── Header: year, pass mark, reports, clear ───────── --}}
                <div class="card rs-noprint">
                    <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem;">
                        <h3 class="card-title mb-0">MCS Results — {{ $selectedYearName }}</h3>
                        <div class="d-flex align-items-center flex-wrap" style="gap:.4rem;">
                            <form method="GET" action="{{ url('admin/exams/exam_results') }}" class="d-flex align-items-center" style="gap:.4rem;">
                                <select name="year_id" class="form-control form-control-sm" style="width:110px;">
                                    @foreach($allYears as $yr)
                                        <option value="{{ $yr->id }}" {{ $selectedYearId == $yr->id ? 'selected' : '' }}>{{ $yr->year_name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-danger btn-sm" style="padding:4px 12px;">Filter</button>
                            </form>
                            <button class="btn btn-sm btn-outline-secondary" id="btnPassMarks"><i class="fas fa-bullseye mr-1"></i> Pass marks</button>
                            <button class="btn btn-sm btn-outline-secondary" id="btnGroupStations" title="Choose the stations each group's results are based on"><i class="fas fa-layer-group mr-1"></i> Group stations</button>
                            <button class="btn btn-sm btn-outline-secondary" id="btnCsv"><i class="fas fa-file-csv mr-1"></i> Download CSV</button>
                            <button class="btn btn-sm btn-outline-secondary" id="btnPrint"><i class="fas fa-print mr-1"></i> Print report</button>
                            <button class="btn btn-sm btn-outline-secondary" id="btnCandStations" title="Judge the ticked candidates on the stations they sat"><i class="fas fa-sliders-h mr-1"></i> Candidate stations</button>
                            <button class="btn btn-sm btn-outline-secondary" id="btnAbsent" title="Mark the ticked candidates absent"><i class="fas fa-user-slash mr-1"></i> Mark absent</button>
                            <button class="btn btn-sm btn-outline-danger" id="btnClear"><i class="fas fa-eraser mr-1"></i> Clear marks</button>
                        </div>
                    </div>
                </div>

                {{-- Report heading (printed only) --}}
                <div class="rs-print-only mb-3">
                    <h2 style="margin:0;">COSECSA MCS Results — {{ $selectedYearName }}</h2>
                    <div>
                        @if($passTotal !== null)
                            Pass mark: {{ $passTotal }} of {{ $max }} marks ({{ $passPct }}%)@if($passStation !== null), at least {{ $passStation }} at every station @endif
                        @else
                            No pass mark has been set.
                        @endif
                        · Printed {{ now()->format('j F Y') }}
                    </div>
                </div>

                @if(empty($rows))
                    <div class="card"><div class="card-body text-center text-muted py-5">
                        <i class="fas fa-clipboard-list fa-2x mb-2"></i><br>No MCS marks have been saved for {{ $selectedYearName }}.
                    </div></div>
                @else

                {{-- ───────── Report & analysis (collapsed until opened) ───────── --}}
                <div class="card">
                    <div class="card-header d-flex align-items-center rs-noprint" id="rsReportToggle" data-toggle="collapse" data-target="#rsReport" aria-expanded="false" aria-controls="rsReport" style="cursor:pointer;">
                        <h3 class="card-title mb-0"><i class="fas fa-chart-bar mr-2" style="color:#a02626;"></i>Report &amp; analysis</h3>
                        <small class="text-muted ml-3">
                            {{ $a['candidates'] ?? 0 }} marked · {{ $a['complete'] ?? 0 }} complete
                            @if(isset($a['mean'])) · average {{ $a['mean'] }}/{{ $max }} @endif
                            @if(isset($a['pass_rate'])) · pass rate {{ $a['pass_rate'] }}% @else · pass mark not set @endif
                        </small>
                        <i class="fas fa-chevron-down ml-auto rs-chev"></i>
                    </div>
                    <div class="collapse" id="rsReport"><div class="card-body">

                {{-- ───────── Summary tiles ───────── --}}
                <div class="row">
                    @php
                        $tiles = [
                            ['Candidates marked', $a['candidates'] ?? 0, ($a['complete'] ?? 0).' completed all stations'.(!empty($a['absent']) ? ' · '.$a['absent'].' absent' : '')],
                            ['Average total', isset($a['mean']) ? $a['mean'] : '—', 'of '.$max.' · median '.($a['median'] ?? '—')],
                            ['Highest / lowest', ($a['highest'] ?? '—').' / '.($a['lowest'] ?? '—'), 'completed candidates'],
                            ['Pass rate', isset($a['pass_rate']) ? $a['pass_rate'].'%' : 'Not set', isset($a['pass_rate']) ? ($a['passed'] ?? 0).' passed · '.($a['failed'] ?? 0).' failed' : 'set a pass mark'],
                        ];
                    @endphp
                    @foreach($tiles as [$label, $value, $sub])
                        <div class="col-6 col-lg-3 mb-3">
                            <div class="card h-100 mb-0"><div class="card-body py-3 text-center">
                                <div class="text-muted small text-uppercase" style="letter-spacing:.05em;">{{ $label }}</div>
                                <div style="font-size:1.9rem;font-weight:800;color:#a02626;line-height:1.2;">{{ $value }}</div>
                                <div class="text-muted small">{{ $sub }}</div>
                            </div></div>
                        </div>
                    @endforeach
                </div>

                {{-- ───────── Charts ───────── --}}
                <div class="row">
                    <div class="col-lg-6 mb-3"><div class="card h-100 mb-0"><div class="card-header py-2"><h3 class="card-title" style="font-size:1rem;">Score distribution <small class="text-muted">(candidates who completed every station)</small></h3></div><div class="card-body"><canvas id="chBands" height="200"></canvas></div></div></div>
                    <div class="col-lg-6 mb-3"><div class="card h-100 mb-0"><div class="card-header py-2"><h3 class="card-title" style="font-size:1rem;">Results</h3></div><div class="card-body"><div style="max-width:320px;margin:auto;"><canvas id="chResult" height="200"></canvas></div></div></div></div>
                    <div class="col-lg-6 mb-3"><div class="card h-100 mb-0"><div class="card-header py-2"><h3 class="card-title" style="font-size:1rem;">Station averages <small class="text-muted">(bar = average, line = maximum)</small></h3></div><div class="card-body"><canvas id="chStations" height="200"></canvas></div></div></div>
                    <div class="col-lg-6 mb-3"><div class="card h-100 mb-0"><div class="card-header py-2"><h3 class="card-title" style="font-size:1rem;">Average total by group</h3></div><div class="card-body"><canvas id="chGroups" height="200"></canvas></div></div></div>
                </div>

                {{-- ───────── Tables for the report ───────── --}}
                <div class="row">
                    <div class="col-lg-7 mb-3">
                        <div class="card h-100 mb-0">
                            <div class="card-header py-2"><h3 class="card-title" style="font-size:1rem;">By station</h3></div>
                            <div class="card-body p-0">
                                <table class="table table-sm mb-0" style="font-size:.85rem;">
                                    <thead><tr><th>Station</th><th class="text-right">Marked</th><th class="text-right">Average</th><th class="text-right">Lowest</th><th class="text-right">Highest</th><th class="text-right">Max</th>@if($passStation !== null)<th class="text-right">Below {{ $passStation }}</th>@endif</tr></thead>
                                    <tbody>
                                    @foreach(($a['stations'] ?? []) as $s)
                                        <tr><td>{{ $s['name'] }}</td><td class="text-right">{{ $s['candidates'] }}</td><td class="text-right"><strong>{{ $s['mean'] ?? '—' }}</strong></td><td class="text-right">{{ $s['min'] ?? '—' }}</td><td class="text-right">{{ $s['highest'] ?? '—' }}</td><td class="text-right">{{ $s['max'] }}</td>@if($passStation !== null)<td class="text-right">{{ $s['below_pass'] ?? '—' }}</td>@endif</tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5 mb-3">
                        <div class="card h-100 mb-0">
                            <div class="card-header py-2"><h3 class="card-title" style="font-size:1rem;">By group, session and country</h3></div>
                            <div class="card-body p-0">
                                <table class="table table-sm mb-0" style="font-size:.85rem;">
                                    <thead><tr><th></th><th class="text-right">Candidates</th><th class="text-right">Average</th>@if($passTotal !== null)<th class="text-right">Passed</th>@endif</tr></thead>
                                    <tbody>
                                    @foreach(['by_session' => 'Session', 'by_group' => 'Group', 'by_country' => 'Country'] as $key => $title)
                                        <tr class="table-active"><td colspan="{{ $passTotal !== null ? 4 : 3 }}"><strong>{{ $title }}</strong></td></tr>
                                        @foreach(($a[$key] ?? []) as $g)
                                            <tr><td>{{ $g['label'] }}</td><td class="text-right">{{ $g['candidates'] }}</td><td class="text-right">{{ $g['mean'] ?? '—' }}</td>@if($passTotal !== null)<td class="text-right">{{ $g['passed'] }}</td>@endif</tr>
                                        @endforeach
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                    </div></div>
                </div>

                {{-- ───────── Results table ───────── --}}
                <div class="card">
                    <div class="card-header d-flex align-items-center flex-wrap" style="gap:.5rem;">
                        <h3 class="card-title mb-0">Candidates</h3>
                        <small class="text-muted">A station marked more than once counts once, at the average (shown with *).</small>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 d-flex align-items-center flex-wrap rs-noprint" style="gap:.5rem;">
                            <label class="mb-0 small font-weight-bold text-muted">Min total:</label>
                            <input type="number" id="mcsMinScore" class="form-control form-control-sm" min="0" placeholder="e.g. 100" style="width:100px;">
                            <select id="rsResult" class="form-control form-control-sm" style="width:auto;">
                                <option value="">All results</option><option>Pass</option><option>Fail</option><option>Incomplete</option><option>Absent</option>
                            </select>
                            <select id="rsGroup" class="form-control form-control-sm" style="width:auto;">
                                <option value="">All groups</option>
                                @foreach(collect($rows)->pluck('group')->filter()->unique()->sort(SORT_NATURAL|SORT_FLAG_CASE) as $g)<option>{{ $g }}</option>@endforeach
                            </select>
                            <button id="mcsClearScore" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times mr-1"></i>Reset</button>
                            <small class="text-muted ml-2" id="mcsFilteredCount"></small>
                        </div>
                        <div class="table-responsive">
                            <table id="mcsresultstable" class="table table-bordered table-striped" style="width:100%;font-size:.88rem;">
                                <thead>
                                    <tr>
                                        <th class="rs-noprint" style="width:28px;"><input type="checkbox" id="rsAll"></th>
                                        <th>Candidate ID</th><th>Name</th><th>Group</th><th>Session</th>
                                        @foreach($stations as $s)<th class="text-right" title="{{ $s['name'] }} (max {{ $s['max'] }})">S{{ $s['number'] }}</th>@endforeach
                                        <th class="text-right">Total</th><th class="text-right">%</th><th>Result</th><th class="rs-noprint"></th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </section>
    </div>
</div>

{{-- Pass marks --}}
{{-- Candidate stations --}}
<div class="modal fade" id="csModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Candidate stations — MCS {{ $selectedYearName }}</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
            <p class="small text-muted">For candidates who were not examined at every station (for example only seven). They are judged on the stations chosen here instead of their group's, and Pass / Fail uses the same pass percentage as everyone else. Saved marks are never changed.</p>
            <div class="small mb-2"><strong id="csCount"></strong> <span class="text-muted">selected</span></div>
            <div id="csList" class="small mb-3" style="max-height:140px;overflow:auto;"></div>
            <div class="custom-control custom-radio"><input type="radio" id="csMarked" name="csMode" value="marked" class="custom-control-input" checked><label for="csMarked" class="custom-control-label">The stations each candidate was <strong>marked at</strong></label></div>
            <div class="custom-control custom-radio mt-1"><input type="radio" id="csSet" name="csMode" value="set" class="custom-control-input"><label for="csSet" class="custom-control-label">These stations for all of them:</label></div>
            <div id="csPick" class="ml-4 mt-1">@foreach($stations as $s)<label class="mr-2 mb-0 small"><input type="checkbox" class="cs-st" value="{{ $s['number'] }}" checked> S{{ $s['number'] }}</label>@endforeach</div>
            <div class="custom-control custom-radio mt-2"><input type="radio" id="csClear" name="csMode" value="clear" class="custom-control-input"><label for="csClear" class="custom-control-label">Back to their <strong>group's</strong> stations</label></div>
        </div>
        <div class="modal-footer"><button class="btn btn-light" data-dismiss="modal">Cancel</button><button class="btn btn-danger" id="btnApplyCs">Apply</button></div>
    </div></div>
</div>

{{-- Group stations --}}
<div class="modal fade" id="gsModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Group stations — MCS {{ $selectedYearName }}</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
            <p class="small text-muted">Each group is judged on <strong>all stations</strong> unless you untick some here, for a group where examiners were missing at those stations. A group judged on fewer stations is <em>complete</em> once its candidates are marked at the ticked stations, its total is out of those stations only, and Pass / Fail uses the same pass percentage as every other group. Saved marks are never changed.</p>
            <table class="table table-sm mb-0" id="gsTable"><thead><tr><th>Group</th>@foreach($stations as $s)<th class="text-center" title="{{ $s['name'] }}">S{{ $s['number'] }}</th>@endforeach<th></th></tr></thead><tbody></tbody></table>
        </div>
        <div class="modal-footer"><button class="btn btn-light" data-dismiss="modal">Close</button></div>
    </div></div>
</div>

<div class="modal fade" id="passModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title">Pass marks — MCS {{ $selectedYearName }}</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
            <div class="form-group">
                <label for="pmTotal">Pass mark (total marks out of {{ $max }})</label>
                <input type="number" id="pmTotal" class="form-control" min="0" max="{{ $max }}" step="any" value="{{ $passTotal }}" placeholder="e.g. 96">
                <small class="text-muted" id="pmPct"></small>
            </div>
            
            <div class="form-group mb-1" id="pmByMaxWrap" style="display:none;">
                <label class="mb-1">Pass mark for a smaller total <span class="text-muted">(optional)</span></label>
                <small class="text-muted d-block mb-2">For candidates judged on fewer stations (see <em>Group stations</em> and <em>Candidate stations</em>). Leave a box empty to hold them to the same percentage as everyone else.</small>
                <div id="pmByMax"></div>
            </div>
            <p class="text-muted small mb-0 mt-3">Pass or Fail depends only on this overall pass mark, never on a single station. A group judged on fewer stations (see <em>Group stations</em>) is held to the same percentage of its own total. Candidates who have not been marked at every station show as <em>Incomplete</em>; a candidate who did not sit the exam can be marked <em>Absent</em> (the user-slash button on their row, or tick several and use <em>Mark absent</em>) and is then left out of the averages and the pass rate. Leave the pass mark empty to show no Pass or Fail. It applies to {{ $selectedYearName }} only and can be changed any time.</p>
        </div>
        <div class="modal-footer"><button class="btn btn-light" data-dismiss="modal">Cancel</button><button class="btn btn-danger" id="btnSavePass">Save pass marks</button></div>
    </div></div>
</div>

{{-- Clear marks --}}
<div class="modal fade" id="clearModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <div class="modal-header" style="border-bottom:3px solid #dc3545;"><h5 class="modal-title text-danger"><i class="fas fa-eraser mr-1"></i> Clear marks — MCS {{ $selectedYearName }}</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
        <div class="modal-body">
            <p class="small text-muted">Deletes the saved marks, so the sitting can be marked again. A copy of everything deleted is kept on the server.</p>
            <div class="custom-control custom-radio"><input type="radio" class="custom-control-input" name="clScope" id="clSel" value="candidates"><label class="custom-control-label" for="clSel">The candidates I ticked in the table (<span id="clSelCount">0</span>)</label></div>
            <div class="custom-control custom-radio mt-2"><input type="radio" class="custom-control-input" name="clScope" id="clStation" value="station"><label class="custom-control-label" for="clStation">One station, for everyone</label>
                <select id="clStationSel" class="form-control form-control-sm mt-1" style="max-width:260px;">@foreach($stations as $s)<option value="{{ $s['number'] }}">{{ $s['name'] }}</option>@endforeach</select></div>
            <div class="custom-control custom-radio mt-2"><input type="radio" class="custom-control-input" name="clScope" id="clGroup" value="group"><label class="custom-control-label" for="clGroup">A whole group</label>
                <select id="clGroupSel" class="form-control form-control-sm mt-1" style="max-width:260px;">@foreach(collect($rows)->pluck('group')->filter()->unique()->sort(SORT_NATURAL|SORT_FLAG_CASE) as $g)<option>{{ $g }}</option>@endforeach</select></div>
            <div class="custom-control custom-radio mt-2"><input type="radio" class="custom-control-input" name="clScope" id="clAll" value="all"><label class="custom-control-label text-danger" for="clAll"><strong>Everything for {{ $selectedYearName }}</strong></label></div>
            <div class="alert alert-warning mt-3 mb-2" id="clPreview" style="display:none;"></div>
            <div id="clConfirmWrap" style="display:none;"><label class="small mb-1">Type <strong>CLEAR</strong> to confirm</label><input type="text" id="clConfirm" class="form-control" autocomplete="off" style="max-width:200px;"></div>
        </div>
        <div class="modal-footer"><button class="btn btn-light" data-dismiss="modal">Cancel</button><button class="btn btn-danger" id="btnDoClear" disabled><i class="fas fa-eraser mr-1"></i> Clear these marks</button></div>
    </div></div>
</div>
@endsection

@push('styles')
<style>
    .rs-print-only { display: none; }
    .rs-chev { transition: transform .2s; }
    #rsReportToggle[aria-expanded="true"] .rs-chev { transform: rotate(180deg); }
    #mcsresultstable td, #mcsresultstable th { white-space: nowrap; }
    .rs-res { font-weight: 700; padding: .1rem .5rem; border-radius: 999px; font-size: .78rem; }
    .rs-res.Pass { background: #dcfce7; color: #166534; } .rs-res.Fail { background: #fee2e2; color: #991b1b; } .rs-res.Incomplete { background: #fef3c7; color: #92400e; } .rs-res.Absent { background: #e5e7eb; color: #374151; }
    .rs-low { background: rgba(220,53,69,.14); }
    @media print {
        #rsReport.collapse { display: block !important; height: auto !important; }
        .rs-noprint, .main-sidebar, .main-header, .main-footer, .dataTables_filter, .dataTables_length, .dataTables_paginate, .dataTables_info, .dt-buttons { display: none !important; }
        .rs-print-only { display: block !important; }
        .content-wrapper { margin-left: 0 !important; background: #fff !important; }
        .card { break-inside: avoid; box-shadow: none !important; border: 1px solid #ddd !important; }
        canvas { max-width: 100% !important; }
        body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
</style>
@endpush

@push('scripts')
<script>
$(function () {
    var ROWS = @json($rows), STATIONS = @json($stations), GROUPS = @json($groups), A = @json($analysis), PASS = @json($passMarks);
    var YEAR_ID = @json($selectedYearId), YEAR = @json($selectedYearName), MAX = PASS.max_total || 160;
    var base = @json(url('admin/exams/exam_results')), stationUrl = @json(url('admin/exams/station_results'));
    var csrf = $('meta[name="csrf-token"]').attr('content') || @json(csrf_token());
    function esc(v) { return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
    function say(msg, ok) {
        var el = $('<div class="alert alert-' + (ok === false ? 'danger' : 'success') + ' alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>' + esc(msg) + '</div>');
        $('#rsAlert').empty().append(el); window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    function post(path, data) {
        return $.ajax({ url: base + path, method: 'POST', data: JSON.stringify(data), contentType: 'application/json', dataType: 'json', headers: { 'X-CSRF-TOKEN': csrf, Accept: 'application/json' } });
    }
    function fail(x) { say((x.responseJSON && x.responseJSON.message) || 'Something went wrong. Nothing was changed.', false); }

    // ───── Table ─────
    var dt = null;
    if (ROWS.length) {
        dt = $('#mcsresultstable').DataTable({
            data: ROWS, deferRender: true, pageLength: 25, order: [[1, 'asc']],
            columns: [].concat(
                [{ data: null, orderable: false, className: 'rs-noprint', render: function (d, t, r) { return '<input type="checkbox" class="rs-pick" value="' + r.cnd_id + '">'; } },
                 { data: 'number', render: function (d, t, r) { return esc(d || r.pen || '—'); } },
                 { data: 'name', render: function (d, t) { return esc(d); } },
                 { data: 'group', defaultContent: '', render: function (d, t, r) { return esc(d || '') + (t === 'display' && r.required_stations ? ' <span class="badge badge-light border" title="Judged on stations ' + r.required_stations.join(', ') + ' only (' + (r.stations_scope === 'candidate' ? 'set for this candidate' : 'set for the group') + ')">' + r.required_stations.length + ' stn</span>' : ''); } },
                 { data: 'session', defaultContent: '', render: function (d) { return esc(d || ''); } }],
                STATIONS.map(function (s, i) {
                    return { data: null, className: 'text-right', orderable: true, render: function (d, type, r) {
                        var st = r.stations[i]; if (type !== 'display') return st ? st.mark : -1;
                        if (!st) return '';
                        var outside = r.required_stations && r.required_stations.indexOf(s.number) === -1;
                        var low = PASS.station !== null && st.mark < PASS.station;
                        return '<a href="' + stationUrl + '/' + r.cnd_id + '/' + s.number + '" style="color:inherit;text-decoration:none;' + (outside ? 'opacity:.4;' : '') + '" class="' + (low ? 'rs-low d-block' : '') + '" title="' + esc(s.name) + (st.marks > 1 ? ' (' + st.marks + ' marks, average)' : '') + '">' + st.mark + (st.marks > 1 ? '*' : '') + '</a>';
                    } };
                }),
                [{ data: 'total', className: 'text-right', render: function (d, t, r) { return t === 'display' ? '<strong>' + d + '</strong>' + (r.required_stations ? ' <small class="text-muted">/ ' + r.max + '</small>' : '') : d; } },
                 { data: 'percent', className: 'text-right', render: function (d, t) { return t === 'display' ? (d == null ? '' : d + '%') : d; } },
                 { data: 'result', defaultContent: '', render: function (d, t) { return t === 'display' ? (d ? '<span class="rs-res ' + d + '">' + d + '</span>' : '<span class="text-muted">—</span>') : (d || ''); } },
                 { data: null, orderable: false, className: 'rs-noprint text-right', render: function (d, t, r) { return '<button class="btn btn-xs btn-light border rs-stations-one" data-id="' + r.cnd_id + '" title="Choose the stations this candidate is judged on"><i class="fas fa-sliders-h text-secondary"></i></button> <button class="btn btn-xs btn-light border rs-absent-one" data-id="' + r.cnd_id + '" data-absent="' + (r.absent ? 1 : 0) + '" title="' + (r.absent ? 'Absent: click to undo' : 'Mark absent') + '"><i class="fas fa-user-slash ' + (r.absent ? 'text-dark' : 'text-secondary') + '"></i></button> <button class="btn btn-xs btn-light border rs-clear-one" data-id="' + r.cnd_id + '" title="Clear this candidate\'s marks"><i class="fas fa-eraser text-danger"></i></button>'; } }]
            )
        });
        $.fn.dataTable.ext.search.push(function (settings, data, i) {
            if (settings.nTable.id !== 'mcsresultstable') return true;
            var r = ROWS[i] || dt.row(i).data(); if (!r) return true;
            var min = parseFloat($('#mcsMinScore').val()), res = $('#rsResult').val(), grp = $('#rsGroup').val();
            if (!isNaN(min) && !(r.total >= min)) return false;
            if (res && (r.result || '') !== res) return false;
            if (grp && (r.group || '') !== grp) return false;
            return true;
        });
        var redraw = function () { dt.draw(); var info = dt.page.info(); $('#mcsFilteredCount').text(info.recordsDisplay < info.recordsTotal ? 'Showing ' + info.recordsDisplay + ' of ' + info.recordsTotal : ''); };
        $('#mcsMinScore').on('input', redraw); $('#rsResult, #rsGroup').on('change', redraw);
        $('#mcsClearScore').on('click', function () { $('#mcsMinScore').val(''); $('#rsResult, #rsGroup').val(''); redraw(); });
        $('#rsAll').on('change', function () { $('#mcsresultstable .rs-pick').prop('checked', this.checked); });

        // ───── Come back to exactly where you were ─────
        // The table is built by script, so the browser cannot put the page back by itself. When leaving (to a
        // candidate's station, say) the page, filters, search, sort order and scroll position are kept for this
        // exam year, and put back when the person returns with Back, the arrow, a swipe, or "Back to Results List".
        var VIEW_KEY = 'rsView.' + YEAR_ID;
        function saveView() {
            try {
                sessionStorage.setItem(VIEW_KEY, JSON.stringify({
                    page: dt.page(), len: dt.page.len(), order: dt.order(), search: dt.search(),
                    min: $('#mcsMinScore').val(), res: $('#rsResult').val(), grp: $('#rsGroup').val(),
                    report: $('#rsReport').hasClass('show'), y: window.scrollY
                }));
            } catch (e) {}
        }
        window.addEventListener('pagehide', saveView);
        $('#mcsresultstable').on('click', 'a', saveView);
        try {
            var nav = (performance.getEntriesByType('navigation')[0] || {}).type;
            var fromStation = /\/admin\/exams\/station_results\//.test(document.referrer || '');
            var v = (nav === 'back_forward' || fromStation) ? JSON.parse(sessionStorage.getItem(VIEW_KEY) || 'null') : null;
            if (v) {
                $('#mcsMinScore').val(v.min || ''); $('#rsResult').val(v.res || ''); $('#rsGroup').val(v.grp || '');
                if (v.len) dt.page.len(v.len);
                if (v.order) dt.order(v.order);
                dt.search(v.search || '').draw(false);
                var info = dt.page.info(); $('#mcsFilteredCount').text(info.recordsDisplay < info.recordsTotal ? 'Showing ' + info.recordsDisplay + ' of ' + info.recordsTotal : '');
                dt.page(Math.min(v.page || 0, Math.max(dt.page.info().pages - 1, 0))).draw('page');
                if (v.report) $('#rsReport').collapse('show');
                var back = function () { window.scrollTo(0, v.y || 0); };
                back(); setTimeout(back, 150); setTimeout(back, 600);
            }
        } catch (e) {}
    }

    // ───── Charts (built when the report is first opened, as a hidden canvas has no size) ─────
    var chartsBuilt = false;
    function buildCharts() {
        if (chartsBuilt || !window.Chart || !ROWS.length) return;
        chartsBuilt = true;
        var maroon = '#a02626', gold = '#FEC503', green = '#2e9e5b', red = '#dc3545', grey = '#9aa3ad';
        var passPct = PASS.total !== null ? PASS.total / MAX * 100 : null;
        new Chart(document.getElementById('chBands'), { type: 'bar',
            data: { labels: A.bands.map(function (b) { return b.label; }), datasets: [{ label: 'Candidates', data: A.bands.map(function (b) { return b.count; }),
                backgroundColor: A.bands.map(function (b, i) { return passPct === null ? maroon : (i * 10 >= passPct ? green : red); }) }] },
            options: { plugins: { legend: { display: false }, tooltip: { callbacks: { footer: function () { return passPct !== null ? 'Pass mark ' + passPct.toFixed(0) + '%' : ''; } } } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } } });
        var rs = [A.passed || 0, A.failed || 0, A.pass_set ? 0 : (A.complete || 0), A.incomplete || 0];
        var labels = ['Pass', 'Fail', 'No pass mark set', 'Incomplete'], colors = [green, red, grey, gold];
        new Chart(document.getElementById('chResult'), { type: 'doughnut', data: { labels: labels, datasets: [{ data: rs, backgroundColor: colors }] }, options: { plugins: { legend: { position: 'bottom' } } } });
        new Chart(document.getElementById('chStations'), { data: { labels: A.stations.map(function (s) { return 'S' + s.number; }), datasets: [
            { type: 'bar', label: 'Average', data: A.stations.map(function (s) { return s.mean; }), backgroundColor: maroon },
            { type: 'line', label: 'Maximum', data: A.stations.map(function (s) { return s.max; }), borderColor: gold, backgroundColor: gold, pointRadius: 3 }] },
            options: { scales: { y: { beginAtZero: true } }, plugins: { legend: { position: 'bottom' } } } });
        var g = A.by_group.filter(function (x) { return x.mean !== null; });
        new Chart(document.getElementById('chGroups'), { type: 'bar', data: { labels: g.map(function (x) { return x.label; }), datasets: [{ label: 'Average total', data: g.map(function (x) { return x.mean; }), backgroundColor: maroon }] },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, suggestedMax: MAX } } } });
    }
    $('#rsReport').on('shown.bs.collapse', buildCharts);
    window.addEventListener('beforeprint', function () { $('#rsReport').addClass('show'); buildCharts(); });

    // ───── Pass marks ─────
    // The totals a candidate can be judged on besides the full exam: every seven-station total, the totals in use, and any already set
    function smallerTotals() {
        var seen = {}, out = [];
        function add(v) { v = Math.round(v * 100) / 100; if (v > 0 && v < MAX && !seen[v]) { seen[v] = 1; out.push(v); } }
        STATIONS.forEach(function (s) { add(MAX - s.max); });
        ROWS.forEach(function (r) { if (r.required_stations) add(r.max); });
        Object.keys(PASS.by_max || {}).forEach(function (k) { add(parseFloat(k)); });
        return out.sort(function (a, b) { return b - a; });
    }
    function byMaxBoxes() {
        var t = smallerTotals();
        $('#pmByMaxWrap').toggle(t.length > 0);
        $('#pmByMax').html(t.map(function (m) {
            var set = PASS.by_max && PASS.by_max[String(m)], same = PASS.total !== null ? Math.round(PASS.total / MAX * m * 100) / 100 : null;
            return '<div class="d-flex align-items-center mb-1"><span class="mr-2" style="width:150px;">Total of <strong>' + m + '</strong></span>' +
                   '<input type="number" class="form-control form-control-sm pm-max" data-max="' + m + '" min="0" max="' + m + '" step="any" style="width:110px;" value="' + (set != null ? set : '') + '" placeholder="' + (same != null ? same : '') + '">' +
                   '<span class="small text-muted ml-2">marks' + (same != null ? ' (same percentage: ' + same + ')' : '') + '</span></div>';
        }).join(''));
    }
    $('#btnPassMarks').on('click', function () { byMaxBoxes(); $('#passModal').modal('show'); updPct(); });
    function updPct() { var v = parseFloat($('#pmTotal').val()); $('#pmPct').text(isNaN(v) ? 'Nothing set: no Pass / Fail is shown.' : (v / MAX * 100).toFixed(1) + '% of ' + MAX + ' marks'); }
    $('#pmTotal').on('input', updPct);
    $('#btnSavePass').on('click', function () {
        var byMax = {}; $('.pm-max').each(function () { byMax[$(this).data('max')] = $(this).val(); });
        post('/pass-marks', { year_id: YEAR_ID, total_pass_mark: $('#pmTotal').val(), max_pass_marks: byMax })
            .done(function (r) { sessionStorage.setItem('rsMsg', r.message); location.reload(); }).fail(fail);
    });
    var m = sessionStorage.getItem('rsMsg'); if (m) { say(m); sessionStorage.removeItem('rsMsg'); }

    // ───── CSV ─────
    $('#btnCsv').on('click', function () {
        var head = ['Candidate ID', 'PEN', 'Name', 'Group', 'Session', 'Country'].concat(STATIONS.map(function (s) { return s.name; }), ['Stations marked', 'Total', 'Max', 'Percent', 'Result']);
        var lines = [head].concat(ROWS.map(function (r) {
            return [r.number, r.pen, r.name, r.group, r.session, r.country].concat(STATIONS.map(function (s, i) { return r.stations[i] ? r.stations[i].mark : ''; }), [r.stations_marked, r.total, r.max, r.percent, r.result || '']);
        }));
        var csv = lines.map(function (l) { return l.map(function (v) { v = v == null ? '' : String(v); return /[",\n]/.test(v) ? '"' + v.replace(/"/g, '""') + '"' : v; }).join(','); }).join('\r\n');
        var a = document.createElement('a'); a.href = URL.createObjectURL(new Blob(['﻿' + csv], { type: 'text/csv;charset=utf-8' })); a.download = 'MCS-results-' + YEAR + '.csv'; document.body.appendChild(a); a.click(); a.remove();
    });

    // ───── Print ─────
    $('#btnPrint').on('click', function () {
        $('#rsReport').addClass('show'); buildCharts();   // the printed report always has the analysis open
        if (dt) dt.page.len(-1).draw(); setTimeout(function () { window.print(); if (dt) dt.page.len(25).draw(); }, 600);
    });

    // ───── Clear marks ─────
    var clearIds = [];
    function scopeData() {
        var sc = $('input[name=clScope]:checked').val(); if (!sc) return null;
        var d = { year_id: YEAR_ID, scope: sc };
        if (sc === 'candidates') d.candidate_ids = clearIds.length ? clearIds : $('#mcsresultstable .rs-pick:checked').map(function () { return this.value; }).get();
        if (sc === 'station') d.station_id = $('#clStationSel').val();
        if (sc === 'group') d.group_name = $('#clGroupSel').val();
        return d;
    }
    function openClear(ids) {
        clearIds = ids || []; var n = clearIds.length || $('#mcsresultstable .rs-pick:checked').length;
        $('#clSelCount').text(n); $('#clSel').prop('disabled', n === 0);
        $('input[name=clScope]').prop('checked', false); if (n > 0) $('#clSel').prop('checked', true);
        $('#clPreview').hide(); $('#clConfirm').val(''); $('#clConfirmWrap').hide(); $('#btnDoClear').prop('disabled', true);
        $('#clearModal').modal('show'); if (n > 0) preview();
    }
    // ───── Candidate stations ─────
    var csIds = [];
    function csOpen(ids) {
        csIds = ids; if (!ids.length) return say('Tick the candidates first.', false);
        var rows = ids.map(function (id) { return ROWS.filter(function (r) { return r.cnd_id == id; })[0]; }).filter(Boolean);
        $('#csCount').text(rows.length);
        $('#csList').html(rows.slice(0, 60).map(function (r) {
            var marked = STATIONS.filter(function (s, i) { return r.stations[i]; }).map(function (s) { return s.number; });
            return '<div><strong>' + esc(r.number || '') + '</strong> ' + esc(r.name || '') + ' <span class="text-muted">— marked at ' + (marked.length ? marked.join(', ') : 'no station') + (r.required_stations ? ' · now judged on ' + r.required_stations.join(', ') : '') + '</span></div>';
        }).join('') + (rows.length > 60 ? '<div class="text-muted">…and ' + (rows.length - 60) + ' more</div>' : ''));
        $('#csMarked').prop('checked', true); $('.cs-st').prop('checked', true);
        $('#csModal').modal('show');
    }
    $('#mcsresultstable').on('click', '.rs-stations-one', function () { csOpen([$(this).data('id')]); });
    $('#btnCandStations').on('click', function () { csOpen($('#mcsresultstable .rs-pick:checked').map(function () { return this.value; }).get()); });
    $('#csPick').on('change', '.cs-st', function () { $('#csSet').prop('checked', true); });
    $('#btnApplyCs').on('click', function () {
        var mode = $('input[name=csMode]:checked').val(), data = { year_id: YEAR_ID, candidate_ids: csIds, mode: mode };
        if (mode === 'set') { data.stations = $('.cs-st:checked').map(function () { return parseInt(this.value, 10); }).get(); if (!data.stations.length) return say('Tick at least one station.', false); }
        post('/candidate-stations', data).done(function (r) { sessionStorage.setItem('rsMsg', r.message); location.reload(); }).fail(function (x) { $('#csModal').modal('hide'); fail(x); });
    });

    // ───── Group stations ─────
    function gsRows() {
        var all = STATIONS.map(function (s) { return s.number; });
        $('#gsTable tbody').html(GROUPS.map(function (g) {
            var on = g.stations || all;
            return '<tr data-group="' + g.id + '"><td><strong>' + esc(g.name) + '</strong>' + (g.stations ? ' <span class="badge badge-warning">' + g.stations.length + ' of ' + all.length + '</span>' : '') + '</td>' +
                STATIONS.map(function (s) { return '<td class="text-center"><input type="checkbox" class="gs-st" value="' + s.number + '"' + (on.indexOf(s.number) !== -1 ? ' checked' : '') + '></td>'; }).join('') +
                '<td class="text-right text-nowrap"><button class="btn btn-xs btn-light border gs-all" title="Tick every station">All</button> <button class="btn btn-xs btn-danger gs-save">Save</button></td></tr>';
        }).join(''));
    }
    $('#btnGroupStations').on('click', function () { gsRows(); $('#gsModal').modal('show'); });
    $('#gsTable').on('click', '.gs-all', function () { $(this).closest('tr').find('.gs-st').prop('checked', true); });
    $('#gsTable').on('click', '.gs-save', function () {
        var tr = $(this).closest('tr'), picked = tr.find('.gs-st:checked').map(function () { return parseInt(this.value, 10); }).get();
        if (!picked.length) return say('Tick at least one station for the group.', false);
        post('/group-stations', { year_id: YEAR_ID, group_id: tr.data('group'), stations: picked })
            .done(function (r) { sessionStorage.setItem('rsMsg', r.message); location.reload(); }).fail(fail);
    });

    // ───── Absent ─────
    function setAbsent(ids, absent) {
        post('/absent', { year_id: YEAR_ID, candidate_ids: ids, absent: absent })
            .done(function (r) { sessionStorage.setItem('rsMsg', r.message); location.reload(); }).fail(fail);
    }
    $('#mcsresultstable').on('click', '.rs-absent-one', function () {
        var id = $(this).data('id'), isAbs = String($(this).data('absent')) === '1', row = ROWS.filter(function (r) { return r.cnd_id == id; })[0] || {};
        CosecsaAlert.confirm(isAbs ? 'Put ' + (row.number || '') + ' back? They will show as Incomplete or with their result again.' : 'Mark ' + (row.number || 'this candidate') + (row.name ? ' (' + row.name + ')' : '') + ' as absent? They will show as Absent instead of Incomplete and be left out of the averages and the pass rate. Saved marks are kept.', { variant: 'question', confirmText: isAbs ? 'Put back' : 'Mark absent' })
            .then(function (ok) { if (ok) setAbsent([id], !isAbs); });
    });
    $('#btnAbsent').on('click', function () {
        var ids = $('#mcsresultstable .rs-pick:checked').map(function () { return this.value; }).get();
        if (!ids.length) return say('Tick the candidates to mark absent first.', false);
        CosecsaAlert.confirm('Mark ' + ids.length + ' candidate' + (ids.length === 1 ? '' : 's') + ' as absent? They will show as Absent instead of Incomplete and be left out of the averages and the pass rate. Saved marks are kept.', { variant: 'question', confirmText: 'Mark absent' })
            .then(function (ok) { if (ok) setAbsent(ids, true); });
    });
    $('#btnClear').on('click', function () { openClear([]); });
    $('#mcsresultstable').on('click', '.rs-clear-one', function () { openClear([$(this).data('id')]); });
    function preview() {
        var d = scopeData(); if (!d) return;
        d.dry_run = 1;
        post('/clear', d).done(function (r) {
            $('#clPreview').html(r.marks ? 'This will delete <strong>' + r.marks + '</strong> saved mark' + (r.marks === 1 ? '' : 's') + ' of <strong>' + r.candidates + '</strong> candidate' + (r.candidates === 1 ? '' : 's') + '.' : 'There are no saved marks for that selection.').show();
            var needType = d.scope !== 'candidates' || r.candidates > 5;
            $('#clConfirmWrap').toggle(needType && r.marks > 0); $('#btnDoClear').data('count', r.marks).data('needType', needType); checkConfirm();
        }).fail(fail);
    }
    function checkConfirm() { var ok = ($('#btnDoClear').data('count') || 0) > 0 && (!$('#btnDoClear').data('needType') || $('#clConfirm').val().trim().toUpperCase() === 'CLEAR'); $('#btnDoClear').prop('disabled', !ok); }
    $('input[name=clScope], #clStationSel, #clGroupSel').on('change', preview);
    $('#clConfirm').on('input', checkConfirm);
    $('#btnDoClear').on('click', function () {
        var d = scopeData(); if (!d) return;
        post('/clear', d).done(function (r) { sessionStorage.setItem('rsMsg', r.message); location.reload(); }).fail(fail);
    });
});
</script>
@endpush
