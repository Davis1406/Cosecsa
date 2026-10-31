@extends('layout.app')

@push('styles')
<style>
    .kpi-box { border-radius: 8px; padding: 14px 18px; color: #fff; display: flex; align-items: center; gap: 14px; box-shadow: 0 2px 10px rgba(0,0,0,.12); height: 100%; }
    .kpi-box .kpi-icon { font-size: 2rem; opacity: .75; }
    .kpi-box .kpi-val  { font-size: 1.5rem; font-weight: 700; line-height: 1; }
    .kpi-box .kpi-lbl  { font-size: .78rem; opacity: .9; margin-top: 2px; }
    .kpi-red    { background: linear-gradient(135deg,#a02626,#7a1c1c); }
    .kpi-blue   { background: linear-gradient(135deg,#1a6fa0,#124f72); }
    .kpi-gold   { background: linear-gradient(135deg,#b8860b,#8a6408); }
    .kpi-green  { background: linear-gradient(135deg,#2a7a3b,#1d5629); }
    .kpi-purple { background: linear-gradient(135deg,#6b2d8b,#4a1a6b); }
    .ir-card { background:#fff; border-radius:8px; box-shadow:0 1px 8px rgba(0,0,0,.08); padding:16px; margin-bottom:18px; }
    .ir-card h6 { font-weight:700; font-size:.82rem; text-transform:uppercase; letter-spacing:.5px; color:#555; margin-bottom:12px; border-bottom:2px solid #a02626; padding-bottom:6px; }
    .ir-chart-btn { font-size:.7rem; padding:1px 8px; text-transform:none; letter-spacing:0; margin-top:-2px; }
    .ir-note { font-size: .82rem; color: #6b7280; }
    .ir-matrix-wrap { max-height: 560px; overflow: auto; }
    .ir-matrix { border-collapse: separate; border-spacing: 0; font-size: .84rem; width: 100%; }
    .ir-matrix th, .ir-matrix td { padding: 6px 10px; border-bottom: 1px solid #eef0f3; text-align: right; white-space: nowrap; }
    .ir-matrix thead th { position: sticky; top: 0; background: #f8f9fb; z-index: 2; font-size: .75rem; text-transform: uppercase; letter-spacing: .3px; color: #555; vertical-align: bottom; white-space: normal; min-width: 74px; }
    .ir-matrix th:first-child, .ir-matrix td:first-child { text-align: left; position: sticky; left: 0; background: #fff; z-index: 1; font-weight: 700; }
    .ir-matrix thead th:first-child { z-index: 3; background: #f8f9fb; }
    .ir-matrix tfoot td { font-weight: 700; background: #f8f9fb; position: sticky; bottom: 0; }
    .ir-matrix tfoot td:first-child { z-index: 3; }
    .ir-matrix td.ir-cell { cursor: pointer; }
    .ir-matrix td.ir-cell:hover, .ir-matrix td.ir-year:hover { outline: 2px solid #a02626; outline-offset: -2px; }
    .ir-matrix td.ir-zero { color: #c3c8d0; cursor: default; }
    .ir-matrix td.ir-year { cursor: pointer; }
    .ir-matrix th[data-programme], .ir-matrix tfoot td[data-programme], .ir-matrix td.ir-total, .ir-matrix tfoot td.ir-all { cursor: pointer; }
    .ir-matrix th[data-programme]:hover, .ir-matrix tfoot td[data-programme]:hover, .ir-matrix td.ir-total:hover, .ir-matrix tfoot td.ir-all:hover { outline: 2px solid #a02626; outline-offset: -2px; }
    .kpi-box.ir-link { cursor: pointer; transition: transform .12s, box-shadow .12s; }
    .kpi-box.ir-link:hover, .kpi-box.ir-link:focus { transform: translateY(-2px); box-shadow: 0 4px 14px rgba(0,0,0,.2); outline: none; }
    .ir-hint { font-size: .72rem; color: #8a94a6; }

    /* List modal (opened from tiles, totals, headings and charts) */
    #ir-modal .modal-dialog { margin: 1.75rem auto; max-width: min(1180px, calc(100% - 3rem)); }
    #ir-modal .modal-content { border: 0; border-radius: 8px; overflow: hidden; background: #fff; color: #141d23; box-shadow: 0 16px 48px rgba(20,29,35,.30); }
    #ir-modal .modal-header { align-items: center; padding: 18px 24px; border-top: 3px solid #a02626; border-bottom: 1px solid #e0e9f2; }
    #ir-modal .modal-title { font-size: 1.1rem; font-weight: 700; letter-spacing: -.01em; color: #141d23; }
    #ir-modal #ir-modal-count { background: #a02626; color: #fff; font-size: .72rem; font-weight: 600; padding: 4px 10px; border-radius: 9999px; }
    #ir-modal .close { margin: 0 0 0 auto; padding: 4px 8px; border-radius: 6px; opacity: .55; text-shadow: none; }
    #ir-modal .close:hover { opacity: 1; background: #ecf5fe; }
    #ir-modal .modal-body { padding: 20px 24px 24px; }
    #ir-modal .dataTables_wrapper > .row:first-child { margin-bottom: 14px; align-items: center; }
    #ir-modal .dataTables_wrapper > .row:last-child { margin-top: 14px; align-items: center; }
    #ir-modal .dataTables_length label { display: inline-flex; align-items: center; gap: 8px; margin: 0; font-weight: 500; }
    #ir-modal .dataTables_length select { display: inline-block; width: auto; min-width: 76px; height: 34px; margin: 0; padding: 4px 30px 4px 12px; border-radius: 6px; border: 1px solid #d5dee8; }
    #ir-modal .dataTables_filter label { display: inline-flex; align-items: center; gap: 8px; margin: 0; font-weight: 500; }
    #ir-modal .dataTables_filter input { height: 34px; margin: 0; padding: 4px 12px; border-radius: 6px; border: 1px solid #d5dee8; }
    #ir-modal .dataTables_filter { display: inline-block; vertical-align: middle; margin: 0 16px 0 0; }
    #ir-modal .dt-buttons { display: inline-flex; gap: 8px; vertical-align: middle; }
    #ir-modal .dt-buttons .btn { border-radius: 6px; margin: 0; }
    #ir-modal table.dataTable { border-collapse: separate; border-spacing: 0; border: 1px solid #e0e9f2; border-radius: 6px; overflow: hidden; }
    #ir-modal table.dataTable thead th { background: #f8f9fb; border-bottom: 1px solid #e0e9f2; font-size: .72rem; text-transform: uppercase; letter-spacing: .3px; color: #59413f; padding: 10px 12px; }
    #ir-modal table.dataTable tbody td { padding: 8px 12px; border-top: 1px solid #eef0f3; vertical-align: middle; }
    #ir-modal table.dataTable tbody tr:hover td { background: #fbf3f3; }
    body.dark-mode #ir-modal .modal-content { background: #1e2330; color: #e2e8f0; box-shadow: 0 16px 48px rgba(0,0,0,.55); }
    body.dark-mode #ir-modal .modal-header { border-bottom-color: #2d3748; }
    body.dark-mode #ir-modal .modal-title { color: #f1f5f9; }
    body.dark-mode #ir-modal .close { color: #e2e8f0; }
    body.dark-mode #ir-modal .close:hover { background: #2b3040; }
    body.dark-mode #ir-modal .dataTables_filter input, body.dark-mode #ir-modal .dataTables_length select { background: #2b3040; border-color: #4a5568; color: #e2e8f0; }
    body.dark-mode #ir-modal table.dataTable { border-color: #2d3748; }
    body.dark-mode #ir-modal table.dataTable thead th { background: #252c3b; border-bottom-color: #2d3748; color: #cbd5e0; }
    body.dark-mode #ir-modal table.dataTable tbody td { border-top-color: #2d3748; }
    body.dark-mode #ir-modal table.dataTable tbody tr:hover td { background: #3a2a3a; }
    @media (max-width: 575.98px) { #ir-modal .modal-dialog { margin: .75rem; max-width: none; } #ir-modal .modal-body { padding: 14px; } #ir-modal .modal-header { padding: 14px 16px; } }
    .ir-chip { display:inline-block; font-size:.72rem; padding:1px 7px; border-radius:10px; background:#eef2f7; color:#445; margin:1px 2px 1px 0; }
    .ir-chip.Trainee { background:#e3f0fb; color:#124f72; } .ir-chip.Candidate { background:#fdf0d5; color:#7a5a08; } .ir-chip.Fellow { background:#e0f3e5; color:#1d5629; }
    .ir-check { border:1px solid #e5e7eb; border-radius:8px; margin-bottom:8px; }
    .ir-check > button { width:100%; text-align:left; background:transparent; border:0; padding:10px 14px; display:flex; gap:12px; align-items:center; cursor:pointer; color:inherit; }
    .ir-check .badge { min-width: 52px; font-size: .85rem; }
    .ir-check .ir-body { padding: 0 14px 12px; display:none; }
    .ir-check.open .ir-body { display:block; }
    #error-banner { display:none; }
    body.dark-mode .ir-card, body.dark-mode .ir-matrix th:first-child, body.dark-mode .ir-matrix td:first-child { background:#1e2330; }
    body.dark-mode .ir-card h6, body.dark-mode .ir-note { color:#a0aec0; }
    body.dark-mode .ir-matrix thead th, body.dark-mode .ir-matrix tfoot td, body.dark-mode .ir-matrix thead th:first-child { background:#252c3b; color:#cbd5e1; }
    body.dark-mode .ir-matrix th, body.dark-mode .ir-matrix td { border-color:#2d3748; }
    body.dark-mode .ir-check { border-color:#2d3748; }
    body.dark-mode .ir-chip { background:#2d3748; color:#cbd5e1; }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h4 style="color:#a02626; font-weight:700;"><i class="fas fa-calendar-check mr-2"></i>Intake Reports</h4>
                    <div class="ir-note">Trainees are counted by their admission year (deferred trainees are counted again in the year they resume). Candidates and fellows are counted by the year in their programme entry number (PEN). Each person is counted once per programme and year, even when they appear as a trainee, a candidate and a fellow.</div>
                </div>
            </div>
        </div>
    </section>

    <section class="content" style="padding:0 20px 20px;">
        <div id="error-banner" class="alert alert-danger"><i class="fas fa-exclamation-triangle mr-2"></i><span id="error-msg"></span></div>
        @if(! $filters)
            <div class="alert alert-danger">The intake data could not be loaded. Please try again shortly.</div>
        @else
        <div class="ir-card" style="padding-bottom:6px;">
            <form id="filters" class="form-row align-items-end" onsubmit="return false">
                <div class="form-group col-6 col-md-1"><label class="small mb-1">From year</label>
                    <select name="from_year" class="form-control form-control-sm"><option value="">Any</option>@foreach($filters['years'] as $y)<option value="{{ $y }}">{{ $y }}</option>@endforeach</select></div>
                <div class="form-group col-6 col-md-1"><label class="small mb-1">To year</label>
                    <select name="to_year" class="form-control form-control-sm"><option value="">Any</option>@foreach(array_reverse($filters['years']) as $y)<option value="{{ $y }}">{{ $y }}</option>@endforeach</select></div>
                <div class="form-group col-6 col-md-2"><label class="small mb-1">Level</label>
                    <select name="programme_type" class="form-control form-control-sm"><option value="">MCS and FCS</option><option value="Membership">MCS (Membership)</option><option value="Fellowship">FCS (Fellowship)</option></select></div>
                <div class="form-group col-6 col-md-2"><label class="small mb-1">Programme</label>
                    <select name="programme_id" class="form-control form-control-sm"><option value="">All programmes</option>@foreach($filters['programmes'] as $p)<option value="{{ $p['id'] }}">{{ $p['name'] }}</option>@endforeach</select></div>
                <div class="form-group col-6 col-md-2"><label class="small mb-1">Country</label>
                    <select name="country_id" class="form-control form-control-sm"><option value="">All countries</option>@foreach($filters['countries'] as $c)<option value="{{ $c['id'] }}">{{ $c['name'] }}</option>@endforeach</select></div>
                <div class="form-group col-6 col-md-1"><label class="small mb-1">Status</label>
                    <select name="status" class="form-control form-control-sm"><option value="">Any</option>@foreach($filters['statuses'] as $s)<option value="{{ $s }}">{{ $s }}</option>@endforeach</select></div>
                <div class="form-group col-6 col-md-1"><label class="small mb-1">Gender</label>
                    <select name="gender" class="form-control form-control-sm"><option value="">Any</option><option>Male</option><option>Female</option></select></div>
                <div class="form-group col-12 col-md-2"><label class="small mb-1">Year of joining</label>
                    <select name="year_basis" class="form-control form-control-sm" title="Trainees use the admission year on their trainee record, plus the year they resume if they deferred. Candidates and fellows use the year in their PEN. Completed trainees are counted in the year they were admitted; deferred trainees from past years are left out. People who passed from MCS to a specialty are in both intakes: their PEN year is their MCS intake and the specialty is counted in the year on their trainee record.">
                        <option value="admission">Admission year (Active + Inactive; current deferrals)</option><option value="smart">PEN year, later programmes by admission year</option><option value="pen">PEN year only</option></select></div>
            </form>
            <div class="text-right">
                <button type="button" id="filters-refresh" class="btn btn-outline-secondary" style="font-size:.75rem;padding:2px 10px;"><i class="fas fa-sync-alt mr-1"></i>Refresh</button>
            </div>
        </div>

        <div class="row mb-3" id="kpi-row">
            <div class="col-6 col-md mb-2"><div class="kpi-box kpi-red ir-link" role="button" tabindex="0" data-pick='{}' title="Show these entries below"><i class="fas fa-id-card kpi-icon"></i><div><div class="kpi-val" id="kpi-entries">–</div><div class="kpi-lbl">Intake entries</div></div></div></div>
            <div class="col-6 col-md mb-2"><div class="kpi-box kpi-slate ir-link" role="button" tabindex="0" data-pick='{}' title="Show these entries below" style="background:linear-gradient(135deg,#4a5568,#2d3748)"><i class="fas fa-users kpi-icon"></i><div><div class="kpi-val" id="kpi-people">–</div><div class="kpi-lbl">Different people</div></div></div></div>
            <div class="col-6 col-md mb-2"><div class="kpi-box kpi-blue ir-link" role="button" tabindex="0" data-pick='{"level":"MCS"}' title="Show these entries below"><i class="fas fa-user-graduate kpi-icon"></i><div><div class="kpi-val" id="kpi-mcs">–</div><div class="kpi-lbl">MCS entries</div></div></div></div>
            <div class="col-6 col-md mb-2"><div class="kpi-box kpi-green ir-link" role="button" tabindex="0" data-pick='{"level":"FCS"}' title="Show these entries below"><i class="fas fa-user-md kpi-icon"></i><div><div class="kpi-val" id="kpi-fcs">–</div><div class="kpi-lbl">FCS entries</div></div></div></div>
            <div class="col-6 col-md mb-2"><div class="kpi-box kpi-purple ir-link" role="button" tabindex="0" data-pick='{"multi":true}' title="Show these entries below"><i class="fas fa-route kpi-icon"></i><div><div class="kpi-val" id="kpi-multi">–</div><div class="kpi-lbl">People in several intakes</div></div></div></div>
        </div>

        <div class="row">
            <div class="col-lg-7">
                <div class="ir-card">
                    <h6>Intake by year and programme</h6>
                    <div class="ir-note mb-2">Each number is the people who joined that programme in that year. Click a number or a year to list them below. A person who moved from MCS to FCS appears in both intakes, so the rows add up to <em>entries</em>, not <em>different people</em>.</div>
                    <div class="ir-matrix-wrap"><table class="ir-matrix" id="matrix"><thead></thead><tbody></tbody><tfoot></tfoot></table></div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="ir-card">
                    <h6><span id="chart-title">MCS and FCS intake by year</span>
                        <span class="float-right"><button type="button" id="excel-year-chart" class="btn btn-xs btn-outline-success ir-chart-btn mr-1"><i class="fas fa-file-excel mr-1"></i>Excel</button><button type="button" id="download-year-chart" class="btn btn-xs btn-outline-secondary ir-chart-btn"><i class="fas fa-download mr-1"></i>Image</button></span>
                    </h6>
                    <div class="btn-group btn-group-sm mb-2" id="chart-mode" role="group" aria-label="Chart view">
                        <button type="button" class="btn btn-outline-secondary active" data-mode="split">MCS / FCS</button>
                        <button type="button" class="btn btn-outline-secondary" data-mode="total">Total per year</button>
                    </div>
                    <div class="ir-note mb-2" id="chart-note">The numbers on the bars are the entries for the filters above. Click a bar to list them.</div>
                    <div style="height:340px;position:relative">
                        <canvas id="chartYear"></canvas>
                        <div id="chart-empty" class="ir-note text-center" style="display:none;position:absolute;top:0;right:0;bottom:0;left:0;align-items:center;justify-content:center;">No entries match this selection.</div>
                    </div>
                </div>
                <div class="ir-card">
                    <h6>Intake by gender
                        <span class="float-right"><button type="button" id="excel-gender-chart" class="btn btn-xs btn-outline-success ir-chart-btn mr-1"><i class="fas fa-file-excel mr-1"></i>Excel</button><button type="button" id="download-gender-chart" class="btn btn-xs btn-outline-secondary ir-chart-btn"><i class="fas fa-download mr-1"></i>Image</button></span>
                    </h6>
                    <div class="btn-group btn-group-sm mb-2" id="gender-mode" role="group" aria-label="Gender chart type">
                        <button type="button" class="btn btn-outline-secondary active" data-mode="pie"><i class="fas fa-chart-pie mr-1"></i>Pie</button>
                        <button type="button" class="btn btn-outline-secondary" data-mode="bar"><i class="fas fa-chart-bar mr-1"></i>Bar</button>
                    </div>
                    <div class="ir-note mb-2" id="gender-note">Entries by gender for the filters above; a missing gender is shown as "Not recorded".</div>
                    <div style="height:300px;position:relative">
                        <canvas id="chartGender"></canvas>
                        <div id="gender-empty" class="ir-note text-center" style="display:none;position:absolute;top:0;right:0;bottom:0;left:0;align-items:center;justify-content:center;">No entries match this selection.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="ir-card" id="country-card">
            <h6><span id="country-title">Entries by country</span>
                <span class="float-right"><button type="button" id="excel-country-chart" class="btn btn-xs btn-outline-success ir-chart-btn mr-1"><i class="fas fa-file-excel mr-1"></i>Excel</button><button type="button" id="download-country-chart" class="btn btn-xs btn-outline-secondary ir-chart-btn"><i class="fas fa-download mr-1"></i>Image</button></span>
            </h6>
            <div class="d-flex align-items-center flex-wrap mb-2">
                <label class="small mb-0 mr-2" for="country-year">Year</label>
                <select id="country-year" class="form-control form-control-sm" style="width:auto;min-width:110px;"></select>
            </div>
            <div class="ir-note mb-2" id="country-note"></div>
            <div id="country-wrap" style="position:relative;height:300px">
                <canvas id="chartCountry"></canvas>
                <div id="country-empty" class="ir-note text-center" style="display:none;position:absolute;top:0;right:0;bottom:0;left:0;align-items:center;justify-content:center;">No entries match this selection.</div>
            </div>
        </div>

        <div class="ir-card" id="detail-card">
            <div class="d-flex align-items-center flex-wrap mb-2">
                <h6 class="mb-0" style="border:none;padding:0;margin:0;">Associates in the intake</h6>
                <span class="ml-2 badge badge-secondary" id="detail-count"></span>
            </div>
            <table class="table table-sm table-striped" id="detail-table" style="width:100%">
                <thead><tr><th>Year</th><th>Programme</th><th>PEN</th><th>Name</th><th>Gender</th><th>Country</th><th>Hospital</th><th>Status</th><th>Records</th><th>Journey (other intakes)</th></tr></thead>
                <tbody></tbody>
            </table>
        </div>

        <div class="ir-card">
            <h6>Data checks</h6>
            <div class="ir-note mb-2">What was merged, left out or needs a person's decision. Nothing is merged on a guess.</div>
            <div id="checks"></div>
        </div>
        @endif
    </section>
</div>
{{-- Opened by clicking a tile, total, heading, chart bar or slice --}}
<div class="modal fade" id="ir-modal" tabindex="-1" role="dialog" aria-labelledby="ir-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="ir-modal-title">Intake entries</h5>
                <span class="ml-2 badge badge-secondary align-self-center" id="ir-modal-count"></span>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <table class="table table-sm table-striped" id="modal-table" style="width:100%">
                    <thead><tr><th>Year</th><th>Programme</th><th>PEN</th><th>Name</th><th>Gender</th><th>Country</th><th>Hospital</th><th>Status</th><th>Records</th><th>Journey (other intakes)</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if($filters)
<script>
$(function () {
    var URLS = { data: @json(route('intake.reports.data')), entries: @json(route('intake.reports.entries')) };
    var form = document.getElementById('filters');
    var chart = null, chartGender = null, dt = null, entries = [], progType = {}, mdt = null, seq = 0, lastData = null, chartMode = 'split', genderMode = 'pie', exportYear = null, exportGender = null, chartCountry = null, exportCountry = null, countryYear = null;
    function noPick() { return { year: null, programme: null, level: null, gender: null, country: null, multi: false }; }
    function genderOf(e) { return e.gender === 'Male' || e.gender === 'Female' ? e.gender : 'Not recorded'; }
    function levelOf(e) { return progType[e.programme_id] === 'Membership' ? 'MCS' : 'FCS'; }
    function plain(d) { return String(d).replace(/<[^>]+>/g, ''); }
    // Download exactly what a chart shows as an Excel file (a throw-away DataTable drives the same Excel export as the lists).
    function exportExcel(spec, filename) {
        if (!spec) return;
        var holder = document.createElement('table');
        holder.style.display = 'none';
        document.body.appendChild(holder);
        var t = $(holder).DataTable({
            data: spec.rows, columns: spec.headers.map(function (h) { return { title: h }; }),
            paging: false, searching: false, info: false, ordering: false, dom: 'B',
            buttons: [{ extend: 'excelHtml5', title: spec.title, filename: filename }]
        });
        t.button(0).trigger();
        setTimeout(function () { t.destroy(); document.body.removeChild(holder); }, 600);
    }
    function filterEntries(p) {
        return entries.filter(function (e) {
            return (p.year == null || e.year === p.year)
                && (p.programme == null || e.programme_id === p.programme)
                && (p.level == null || levelOf(e) === p.level)
                && (p.gender == null || genderOf(e) === p.gender)
                && (p.country == null || (e.country || 'Not recorded') === p.country)
                && (!p.multi || (e.other_intakes || []).length > 0);
        });
    }
    function selectionParts(p) {
        var parts = [];
        if (p.year != null) parts.push(p.year);
        if (p.programme != null) { var f = entries.find(function (e) { return e.programme_id === p.programme; }); if (f) parts.push(f.programme); }
        if (p.level != null) parts.push(p.level);
        if (p.gender != null) parts.push(p.gender);
        if (p.country != null) parts.push(p.country);
        if (p.multi) parts.push('in several intakes');
        return parts;
    }
    function rowsFor(list) {
        return list.map(function (e) {
            var basis = e.year_basis && e.year_basis !== 'PEN' ? ' <i class="fas fa-info-circle text-muted" title="PEN year ' + esc(e.pen_year) + '. ' + esc(e.year_basis) + '"></i>' : '';
            return [e.year + basis, esc(e.programme), esc(e.pens.join(', ')), esc(e.name), esc(e.gender || ''), esc(e.country || ''), esc(e.hospital || ''), esc(e.status || ''),
                e.sources.map(function (s) { return '<span class="ir-chip ' + s + '">' + s + '</span>'; }).join(''),
                esc((e.other_intakes || []).join(', '))];
        });
    }
    // One DataTable config for the page list and the modal: same columns, Excel + CSV export.
    function tableOptions(list, fileTitle) {
        var fname = 'intake-report' + (fileTitle ? '-' + fileTitle.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '') : '');
        var exp = { exportOptions: { format: { body: plain } } };
        return {
            data: rowsFor(list), deferRender: true, pageLength: 25, order: [],
            columnDefs: [{ targets: 0, type: 'html-num' }],
            dom: '<"row"<"col-md-6"l><"col-md-6 text-right"fB>>rt<"row"<"col-md-5"i><"col-md-7"p>>',
            buttons: [
                $.extend({ extend: 'excelHtml5', text: '<i class="fas fa-file-excel mr-1"></i> Excel', className: 'btn btn-success btn-sm', title: fileTitle || 'Intake report', filename: fname }, exp),
                $.extend({ extend: 'csvHtml5', text: '<i class="fas fa-file-csv mr-1"></i> CSV', className: 'btn btn-secondary btn-sm', filename: fname }, exp)
            ]
        };
    }
    // Click a tile, total, heading, bar or slice: show that list in a modal (export from there).
    function openList(p) {
        p = Object.assign(noPick(), p || {});
        var list = filterEntries(p), parts = selectionParts(p), title = parts.length ? 'Associates: ' + parts.join(', ') : 'All intake entries';
        document.getElementById('ir-modal-title').textContent = title;
        document.getElementById('ir-modal-count').textContent = list.length + ' entries';
        if (mdt) { mdt.destroy(); $('#modal-table tbody').empty(); }
        mdt = $('#modal-table').DataTable(tableOptions(list, parts.length ? parts.join(' ') : 'All entries'));
        $('#ir-modal').modal('show');
    }
    $('#ir-modal').appendTo('body').on('shown.bs.modal', function () { if (mdt) mdt.columns.adjust(); });

    function esc(v) { return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) { return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]; }); }
    function query() {
        var p = new URLSearchParams();
        new FormData(form).forEach(function (v, k) { if (v !== '') p.set(k, v); });
        return p.toString();
    }
    function getJson(url) {
        // No browser cache: "All countries" reuses the same URL as the first load,
        // so a cached response would leave the previous selection on screen.
        var bust = (url.indexOf('?') === -1 ? '?' : '&') + '_=' + Date.now();
        return fetch(url + bust, { credentials: 'same-origin', cache: 'no-store', headers: { Accept: 'application/json' } }).then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        });
    }

    function load() {
        var mine = ++seq, q = query();
        document.getElementById('error-banner').style.display = 'none';
        Promise.all([getJson(URLS.data + '?' + q), getJson(URLS.entries + '?' + q)]).then(function (res) {
            if (mine !== seq) return;
            entries = res[1].entries || [];
            progType = {};
            (res[1].programmes || []).forEach(function (pr) { progType[pr.id] = pr.type; });
            lastData = res[0]; renderKpis(res[0]); renderMatrix(res[0]); renderChart(res[0]); renderGender(entries); renderCountries(); renderChecks(res[0].checks); renderDetail();
        }).catch(function (err) {
            document.getElementById('error-banner').style.display = 'block';
            document.getElementById('error-msg').textContent = 'The intake report could not be loaded: ' + err.message;
        });
    }

    function renderKpis(d) {
        var t = d.totals || {};
        document.getElementById('kpi-entries').textContent = t.entries;
        document.getElementById('kpi-people').textContent = t.people;
        document.getElementById('kpi-mcs').textContent = t.membership;
        document.getElementById('kpi-fcs').textContent = t.fellowship;
        document.getElementById('kpi-multi').textContent = t.in_several_intakes;
    }

    function renderMatrix(d) {
        var cols = d.columns || [], head = '<tr><th>Year</th>';
        cols.forEach(function (c) { head += '<th data-programme="' + c.id + '" title="' + esc(c.name) + ' (click to list)">' + esc(c.short) + '</th>'; });
        head += '<th>Entries</th><th>People</th></tr>';
        var body = '';
        (d.matrix || []).forEach(function (r) {
            body += '<tr><td class="ir-year" data-year="' + r.year + '">' + r.year + '</td>';
            cols.forEach(function (c) {
                var n = r.cells[c.id] || 0;
                body += n ? '<td class="ir-cell" data-year="' + r.year + '" data-programme="' + c.id + '">' + n + '</td>' : '<td class="ir-zero">·</td>';
            });
            body += '<td class="ir-total" data-year="' + r.year + '"><strong>' + r.entries + '</strong></td><td class="ir-total" data-year="' + r.year + '">' + r.people + '</td></tr>';
        });
        var foot = '<tr><td class="ir-all" title="List every entry">All years</td>';
        cols.forEach(function (c) { foot += '<td data-programme="' + c.id + '">' + (d.column_totals[c.id] || 0) + '</td>'; });
        foot += '<td class="ir-all">' + d.totals.entries + '</td><td class="ir-all">' + d.totals.people + '</td></tr>';
        var m = document.getElementById('matrix');
        m.querySelector('thead').innerHTML = head; m.querySelector('tbody').innerHTML = body; m.querySelector('tfoot').innerHTML = foot;
        m.querySelectorAll('td.ir-cell, td.ir-year, td.ir-total, td.ir-all, th[data-programme], tfoot td[data-programme]').forEach(function (td) {
            td.addEventListener('click', function () {
                openList({ year: td.dataset.year ? +td.dataset.year : null, programme: td.dataset.programme ? +td.dataset.programme : null });
            });
        });
    }

    function renderChart(d) {
        var rows = (d.matrix || []).slice().reverse();
        var empty = !rows.length;
        var emptyBox = document.getElementById('chart-empty');
        emptyBox.style.display = empty ? 'flex' : 'none';
        if (chart) { chart.destroy(); chart = null; }
        if (empty) { exportYear = null; return; }

        var total = chartMode === 'total';
        document.getElementById('chart-title').textContent = total ? 'Total intake by year' : 'MCS and FCS intake by year';
        document.getElementById('chart-note').textContent = total
            ? 'Each bar is all entries (MCS and FCS together) for that year, for the filters above. Click a bar to list them.'
            : 'The numbers on the bars are the entries for the filters above. Click a bar to list them.';
        [].forEach.call(document.querySelectorAll('#chart-mode button'), function (b) { b.classList.toggle('active', b.dataset.mode === chartMode); });
        exportYear = total
            ? { title: 'Total intake by year', headers: ['Year', 'Total entries'], rows: rows.map(function (r) { return [r.year, r.entries]; }) }
            : { title: 'MCS and FCS intake by year', headers: ['Year', 'MCS', 'FCS', 'Total entries'], rows: rows.map(function (r) { return [r.year, r.membership, r.fellowship, r.entries]; }) };
        var data = {
            labels: rows.map(function (r) { return r.year; }),
            datasets: total
                ? [{ label: 'Total entries', data: rows.map(function (r) { return r.entries; }), backgroundColor: '#004356' }]
                : [
                    { label: 'MCS', data: rows.map(function (r) { return r.membership; }), backgroundColor: '#1a6fa0' },
                    { label: 'FCS', data: rows.map(function (r) { return r.fellowship; }), backgroundColor: '#a02626' }
                ]
        };
        chart = new Chart(document.getElementById('chartYear'), {
            type: 'bar', data: data,
            plugins: window.ChartDataLabels ? [ChartDataLabels] : [],
            options: { responsive: true, maintainAspectRatio: false,
                onClick: function (evt, els) {
                    var hit = els && els[0]; if (!hit) return;
                    openList(total ? { year: rows[hit.index].year } : { year: rows[hit.index].year, level: hit.datasetIndex === 0 ? 'MCS' : 'FCS' });
                },
                onHover: function (evt, els) { evt.native.target.style.cursor = els && els.length ? 'pointer' : 'default'; },
                plugins: {
                    legend: { display: !total, position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } },
                    tooltip: { mode: 'index', intersect: false },
                    datalabels: {
                        display: function (ctx) { return ctx.dataset.data[ctx.dataIndex] > 0; },
                        anchor: 'center', align: 'center', clamp: true,
                        color: '#fff', font: { size: 10, weight: 'bold' },
                        formatter: function (v) { return v; }
                    }
                },
                scales: { x: { stacked: true, ticks: { font: { size: 10 }, maxRotation: 60 } }, y: { stacked: true, beginAtZero: true, ticks: { font: { size: 10 }, precision: 0 } } } }
        });
    }

    var GENDER_COLOURS = { Male: '#1a6fa0', Female: '#a02626', 'Not recorded': '#8a94a6' };
    var PIE_NOTE = 'Entries by gender for the filters above; a missing gender is shown as "Not recorded". Click a slice to list them.';

    function renderGender(rows) {
        [].forEach.call(document.querySelectorAll('#gender-mode button'), function (b) { b.classList.toggle('active', b.dataset.mode === genderMode); });
        if (chartGender) { chartGender.destroy(); chartGender = null; }
        var noteBox = document.getElementById('gender-note');
        var counts = { Male: 0, Female: 0, 'Not recorded': 0 };
        (rows || []).forEach(function (e) { counts[genderOf(e)]++; });
        var empty = !(rows || []).length;
        document.getElementById('gender-empty').style.display = empty ? 'flex' : 'none';
        if (empty) { exportGender = null; noteBox.textContent = PIE_NOTE; return; }
        if (genderMode === 'bar') renderGenderBars(rows, noteBox); else { noteBox.textContent = PIE_NOTE; renderGenderPie(counts); }
    }

    function renderGenderPie(counts) {
        var labels = ['Male', 'Female', 'Not recorded'].filter(function (k) { return counts[k] > 0; });
        var values = labels.map(function (k) { return counts[k]; });
        var colours = labels.map(function (k) { return GENDER_COLOURS[k]; });
        var sumAll = values.reduce(function (a, b) { return a + b; }, 0);
        exportGender = { title: 'Intake by gender', headers: ['Gender', 'Entries', 'Share (%)'], rows: labels.map(function (k, i) { return [k, values[i], sumAll ? Math.round(values[i] * 1000 / sumAll) / 10 : 0]; }) };
        chartGender = new Chart(document.getElementById('chartGender'), {
            type: 'pie',
            data: { labels: labels, datasets: [{ data: values, backgroundColor: colours, borderColor: '#fff', borderWidth: 2 }] },
            plugins: window.ChartDataLabels ? [ChartDataLabels] : [],
            options: { responsive: true, maintainAspectRatio: false,
                onClick: function (evt, els) { var hit = els && els[0]; if (hit) openList({ gender: labels[hit.index] }); },
                onHover: function (evt, els) { evt.native.target.style.cursor = els && els.length ? 'pointer' : 'default'; },
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } },
                    tooltip: { callbacks: { label: function (ctx) {
                        var total = ctx.dataset.data.reduce(function (a, b) { return a + b; }, 0);
                        return ctx.label + ': ' + ctx.parsed + ' (' + (total ? Math.round(ctx.parsed * 100 / total) : 0) + '%)';
                    } } },
                    datalabels: { display: function (ctx) { return ctx.dataset.data[ctx.dataIndex] > 0; }, color: '#fff', font: { size: 11, weight: 'bold' }, formatter: function (v) { return v; } }
                } }
        });
    }

    // Bar view: one stacked bar per year (women at the bottom) with the total above every bar, a
    // headline and minimal axes. "Not recorded" is left out until it is switched on in the legend.
    var showUnrecorded = false;
    function renderGenderBars(rows, noteBox) {
        var byYear = {}, unrecorded = 0;
        rows.forEach(function (e) {
            var g = genderOf(e), y = byYear[e.year] || (byYear[e.year] = { Male: 0, Female: 0, 'Not recorded': 0 });
            y[g]++; if (g === 'Not recorded') unrecorded++;
        });
        var years = Object.keys(byYear).map(Number).sort(function (a, b) { return a - b; });
        var order = ['Female', 'Male'].concat(unrecorded ? ['Not recorded'] : []);
        var visible = order.filter(function (g) { return g !== 'Not recorded' || showUnrecorded; });
        var totals = years.map(function (y) { return visible.reduce(function (n, g) { return n + byYear[y][g]; }, 0); });
        var lastVisible = order.indexOf(visible[visible.length - 1]);
        var genderName = { Female: 'Women', Male: 'Men', 'Not recorded': 'Not recorded' };
        exportGender = {
            title: 'Intake by gender and year',
            headers: ['Year'].concat(visible.map(function (g) { return genderName[g]; }), ['Total']),
            rows: years.map(function (y, i) { return [y].concat(visible.map(function (g) { return byYear[y][g]; }), [totals[i]]); })
        };

        var barColours = { Female: '#3b6fd4', Male: '#c9cbbf', 'Not recorded': '#e3e5dc' };
        var datasets = order.map(function (g, di) {
            return {
                label: g === 'Female' ? 'Women' : (g === 'Male' ? 'Men' : 'Not recorded'), gender: g,
                data: years.map(function (y) { return byYear[y][g]; }), backgroundColor: barColours[g], borderWidth: 0, borderSkipped: false,
                hidden: g === 'Not recorded' && !showUnrecorded,
                datalabels: {
                    display: function (ctx) { return di === lastVisible; },
                    anchor: 'end', align: 'end', offset: 2, color: '#141d23', font: { size: 10, weight: 'bold' },
                    formatter: function (v, ctx) { return totals[ctx.dataIndex]; }
                }
            };
        });

        // Headline + one-line summary, built from the years shown (so it follows every filter).
        var first = years[0], last = years[years.length - 1], tf = totals[0], tl = totals[totals.length - 1];
        var sum = totals.reduce(function (a, b) { return a + b; }, 0), wl = byYear[last].Female, lastYearOpen = last === new Date().getFullYear();
        var head = years.length < 2 ? 'Intake in ' + first + ': ' + tf + ' entries'
            : 'Annual intake ' + (tl > tf ? 'grew' : (tl < tf ? 'fell' : 'held at ' + tf)) + (tl === tf ? ' from ' + first + ' to ' + last : ' from ' + tf + ' in ' + first + ' to ' + tl + ' in ' + last);
        noteBox.innerHTML = '<strong style="color:inherit;font-size:.95rem">' + esc(head) + '</strong><br>' + sum + ' entries since ' + first + '. Women were ' + wl + ' of ' + tl + ' (' + (tl ? Math.round(wl * 100 / tl) : 0) + '%) in ' + last + (lastYearOpen ? '; ' + last + ' is provisional' : '') + '. Click a bar to list them.'
            + (unrecorded && !showUnrecorded ? '<br><span class="ir-hint">' + unrecorded + ' entries have no gender recorded and are not shown. Click &ldquo;Not recorded&rdquo; in the legend to include them.</span>' : '');

        chartGender = new Chart(document.getElementById('chartGender'), {
            type: 'bar',
            data: { labels: years, datasets: datasets },
            plugins: window.ChartDataLabels ? [ChartDataLabels] : [],
            options: { responsive: true, maintainAspectRatio: false, categoryPercentage: 0.9, barPercentage: 0.88,
                layout: { padding: { top: 22 } },
                onClick: function (evt, els) {
                    var hit = els && els[0]; if (!hit) return;
                    openList({ year: years[hit.index], gender: datasets[hit.datasetIndex].gender });
                },
                onHover: function (evt, els) { evt.native.target.style.cursor = els && els.length ? 'pointer' : 'default'; },
                plugins: {
                    legend: {
                        position: 'top', align: 'start',
                        labels: { usePointStyle: true, pointStyle: 'rectRounded', boxWidth: 9, boxHeight: 9, font: { size: 11 } },
                        // Only "Not recorded" can be switched; Women and Men always stay.
                        onClick: function (e, item) { if (datasets[item.datasetIndex].gender === 'Not recorded') { showUnrecorded = !showUnrecorded; renderGender(entries); } },
                        onHover: function (e, item) { e.native.target.style.cursor = datasets[item.datasetIndex].gender === 'Not recorded' ? 'pointer' : 'default'; },
                        onLeave: function (e) { e.native.target.style.cursor = 'default'; }
                    },
                    tooltip: { mode: 'index', intersect: false, callbacks: { footer: function (items) { return 'Total: ' + totals[items[0].dataIndex]; } } }
                },
                scales: {
                    x: { stacked: true, grid: { display: false }, border: { color: '#9aa1ad' }, ticks: { font: { size: 10 }, maxRotation: 0, autoSkip: false } },
                    y: { stacked: true, display: false, beginAtZero: true, grace: '4%' }
                } }
        });
    }

    // Entries by country for one year: a ranked horizontal bar chart, the leader highlighted,
    // with a headline and summary built from the numbers.
    var NUM_WORDS = ['No', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine', 'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen', 'Twenty'];
    function renderCountries() {
        var sel = document.getElementById('country-year'), years = {};
        entries.forEach(function (e) { years[e.year] = true; });
        var list = Object.keys(years).map(Number).sort(function (a, b) { return b - a; });
        if (countryYear !== 'all' && list.indexOf(countryYear) === -1) countryYear = list.length ? list[0] : 'all';
        sel.innerHTML = '<option value="all">All years</option>' + list.map(function (y) { return '<option value="' + y + '">' + y + '</option>'; }).join('');
        sel.value = String(countryYear);

        if (chartCountry) { chartCountry.destroy(); chartCountry = null; }
        var rows = entries.filter(function (e) { return countryYear === 'all' || e.year === countryYear; });
        var empty = !rows.length, noteBox = document.getElementById('country-note');
        document.getElementById('country-empty').style.display = empty ? 'flex' : 'none';
        var label = countryYear === 'all' ? 'all years' : String(countryYear);
        if (empty) { exportCountry = null; document.getElementById('country-title').textContent = 'Entries by country'; noteBox.textContent = ''; return; }

        var counts = {};
        rows.forEach(function (e) { var c = e.country || 'Not recorded'; counts[c] = (counts[c] || 0) + 1; });
        var names = Object.keys(counts).sort(function (a, b) { return counts[b] - counts[a] || a.localeCompare(b); });
        var values = names.map(function (n) { return counts[n]; });
        var total = values.reduce(function (a, b) { return a + b; }, 0);

        // "Most" = the smallest group of top countries holding at least 80% of the entries.
        var k = 0, run = 0;
        while (k < values.length && run < total * 0.8) { run += values[k]; k++; }
        var topShare = Math.round(run * 100 / total);
        var title = k <= 1 ? names[0] + ' holds most COSECSA entries'
            : (NUM_WORDS[k] || k) + ' countries hold most COSECSA entries, led by ' + names[0];
        document.getElementById('country-title').textContent = title;
        noteBox.textContent = 'Entries by country, ' + label + ': ' + total.toLocaleString() + ' in ' + names.length + ' ' + (names.length === 1 ? 'country' : 'countries')
            + (k > 1 ? '; the top ' + (NUM_WORDS[k] || k).toLowerCase() + ' hold ' + run.toLocaleString() + ' (' + topShare + '%)' : '') + '. Click a bar to list them.';
        exportCountry = { title: 'Entries by country, ' + label, headers: ['Rank', 'Country', 'Entries', 'Share (%)'],
            rows: names.map(function (n, i) { return [i + 1, n, values[i], Math.round(values[i] * 1000 / total) / 10]; }) };

        document.getElementById('country-wrap').style.height = Math.max(160, names.length * 24 + 24) + 'px';
        chartCountry = new Chart(document.getElementById('chartCountry'), {
            type: 'bar',
            data: { labels: names, datasets: [{ data: values, backgroundColor: names.map(function (n, i) { return i === 0 ? '#3b6fd4' : '#c9cbbf'; }), borderWidth: 0, barPercentage: 0.78, categoryPercentage: 1 }] },
            plugins: window.ChartDataLabels ? [ChartDataLabels] : [],
            options: { indexAxis: 'y', responsive: true, maintainAspectRatio: false,
                layout: { padding: { right: 34 } },
                onClick: function (evt, els) { var hit = els && els[0]; if (hit) openList({ country: names[hit.index], year: countryYear === 'all' ? null : countryYear }); },
                onHover: function (evt, els) { evt.native.target.style.cursor = els && els.length ? 'pointer' : 'default'; },
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: function (ctx) { return ctx.parsed.x + ' entries (' + Math.round(ctx.parsed.x * 100 / total) + '%)'; } } },
                    datalabels: { anchor: 'end', align: 'end', color: '#141d23', font: { size: 11 }, formatter: function (v) { return v; } }
                },
                scales: {
                    x: { display: false, beginAtZero: true, grace: '4%' },
                    y: { grid: { display: false }, border: { display: false }, ticks: { autoSkip: false, font: function (ctx) { return { size: 11, weight: ctx.index === 0 ? 'bold' : 'normal' }; } } }
                } }
        });
    }
    document.getElementById('country-year').addEventListener('change', function () { countryYear = this.value === 'all' ? 'all' : +this.value; renderCountries(); });

    // Save a chart as a high-resolution PNG on a white background (the canvas itself is transparent,
    // which turns black in some viewers). The chart is redrawn at IMAGE_SCALE x the screen's pixel
    // density for the copy, then put back, so text and bars stay sharp when printed or zoomed.
    var IMAGE_SCALE = 4;
    function downloadChart(c, filename) {
        if (!c) return;
        var oldRatio = c.options.devicePixelRatio;
        c.options.devicePixelRatio = Math.max(window.devicePixelRatio || 1, 1) * IMAGE_SCALE;
        c.resize();
        var src = c.canvas, out = document.createElement('canvas');
        out.width = src.width; out.height = src.height;
        var ctx = out.getContext('2d');
        ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, out.width, out.height);
        ctx.drawImage(src, 0, 0);
        c.options.devicePixelRatio = oldRatio;
        c.resize();
        var a = document.createElement('a');
        a.download = filename + '.png';
        a.href = out.toDataURL('image/png');
        document.body.appendChild(a); a.click(); a.remove();
    }

    function renderChecks(checks) {
        var html = '';
        Object.keys(checks || {}).forEach(function (k) {
            var c = checks[k];
            var detail = c.detail ? '<ul class="mb-2 small">' + Object.keys(c.detail).map(function (l) { return '<li>' + esc(l) + ': <strong>' + c.detail[l] + '</strong></li>'; }).join('') + '</ul>' : '';
            var sample = '';
            if (c.sample && c.sample.length) {
                sample = '<table class="table table-sm mb-0 small"><tbody>' + c.sample.map(function (s) {
                    return '<tr><td>' + esc(s.name) + '</td><td>' + esc(s.journey || s.pen || '') + '</td><td>' + esc(s.programme || s.source || '') + (s.year ? ' ' + esc(s.year) : '') + '</td></tr>';
                }).join('') + '</tbody></table>' + (c.count > c.sample.length ? '<div class="ir-note mt-1">First ' + c.sample.length + ' of ' + c.count + '.</div>' : '');
            }
            var openable = detail || sample;
            html += '<div class="ir-check"><button type="button"' + (openable ? '' : ' style="cursor:default"') + '><span class="badge badge-' + (c.count ? 'secondary' : 'light') + '">' + c.count + '</span><span><strong>' + esc(c.label) + '</strong><br><span class="ir-note">' + esc(c.note) + '</span></span></button>' +
                (openable ? '<div class="ir-body">' + detail + sample + '</div>' : '') + '</div>';
        });
        var box = document.getElementById('checks');
        box.innerHTML = html;
        box.querySelectorAll('.ir-check > button').forEach(function (b) { b.addEventListener('click', function () { b.parentNode.classList.toggle('open'); }); });
    }

    function renderDetail() {
        if (dt) { dt.destroy(); $('#detail-table tbody').empty(); }
        dt = $('#detail-table').DataTable(tableOptions(entries, ''));
        document.getElementById('detail-count').textContent = entries.length + ' entries';
    }

    document.querySelectorAll('#kpi-row .ir-link').forEach(function (box) {
        var go = function () { var p = {}; try { p = JSON.parse(box.getAttribute('data-pick') || '{}'); } catch (e) {} openList(p); };
        box.addEventListener('click', go);
        box.addEventListener('keydown', function (ev) { if (ev.key === 'Enter' || ev.key === ' ') { ev.preventDefault(); go(); } });
    });
    document.getElementById('excel-country-chart').addEventListener('click', function () { exportExcel(exportCountry, 'intake-by-country'); });
    document.getElementById('download-country-chart').addEventListener('click', function () { downloadChart(chartCountry, 'intake-by-country'); });
    document.getElementById('excel-year-chart').addEventListener('click', function () { exportExcel(exportYear, chartMode === 'total' ? 'intake-total-by-year' : 'intake-by-year'); });
    document.getElementById('excel-gender-chart').addEventListener('click', function () { exportExcel(exportGender, genderMode === 'bar' ? 'intake-by-gender-and-year' : 'intake-by-gender'); });
    document.getElementById('download-year-chart').addEventListener('click', function () { downloadChart(chart, chartMode === 'total' ? 'intake-total-by-year' : 'intake-by-year'); });
    [].forEach.call(document.querySelectorAll('#chart-mode button'), function (b) {
        b.addEventListener('click', function () { chartMode = b.dataset.mode; if (lastData) renderChart(lastData); });
    });
    document.getElementById('download-gender-chart').addEventListener('click', function () { downloadChart(chartGender, genderMode === 'bar' ? 'intake-by-gender-bar' : 'intake-by-gender'); });
    [].forEach.call(document.querySelectorAll('#gender-mode button'), function (b) {
        b.addEventListener('click', function () { genderMode = b.dataset.mode; renderGender(entries); });
    });
    document.getElementById('filters-refresh').addEventListener('click', load);

    // Both events: a <select> fires "change" in every browser, "input" covers
    // keyboard/assistive selection. Debounced so changing one filter is one request.
    var filterTimer = null;
    function scheduleLoad() { clearTimeout(filterTimer); filterTimer = setTimeout(load, 120); }
    form.addEventListener('change', scheduleLoad);
    form.addEventListener('input', scheduleLoad);

    // Years after the current one (e.g. deferred trainees resuming next year) stay hidden until someone
    // picks them: "To year" starts on the current year; choose a later year or "Any" to include them.
    (function () {
        var to = form.elements.to_year, thisYear = String(new Date().getFullYear());
        if (to && to.options.length > 1 && +to.options[1].value > +thisYear && to.querySelector('option[value="' + thisYear + '"]')) to.value = thisYear;
    })();

    load();
});
</script>
@endif
@endpush
