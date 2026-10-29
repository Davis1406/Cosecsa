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
                    <select name="year_basis" class="form-control form-control-sm" title="Trainees use the admission year on their trainee record, plus the year they resume if they deferred. Candidates and fellows use the year in their PEN. Completed records and deferred trainees from past years are left out; Active and Inactive are kept.">
                        <option value="admission">Admission year (Active + Inactive; current deferrals)</option><option value="smart">PEN year, later programmes by admission year</option><option value="pen">PEN year only</option></select></div>
            </form>
            <div class="text-right">
                <button type="button" id="filters-refresh" class="btn btn-outline-secondary" style="font-size:.75rem;padding:2px 10px;"><i class="fas fa-sync-alt mr-1"></i>Refresh</button>
            </div>
        </div>

        <div class="row mb-3" id="kpi-row">
            <div class="col-6 col-md mb-2"><div class="kpi-box kpi-red"><i class="fas fa-id-card kpi-icon"></i><div><div class="kpi-val" id="kpi-entries">–</div><div class="kpi-lbl">Intake entries</div></div></div></div>
            <div class="col-6 col-md mb-2"><div class="kpi-box kpi-slate" style="background:linear-gradient(135deg,#4a5568,#2d3748)"><i class="fas fa-users kpi-icon"></i><div><div class="kpi-val" id="kpi-people">–</div><div class="kpi-lbl">Different people</div></div></div></div>
            <div class="col-6 col-md mb-2"><div class="kpi-box kpi-blue"><i class="fas fa-user-graduate kpi-icon"></i><div><div class="kpi-val" id="kpi-mcs">–</div><div class="kpi-lbl">MCS entries</div></div></div></div>
            <div class="col-6 col-md mb-2"><div class="kpi-box kpi-green"><i class="fas fa-user-md kpi-icon"></i><div><div class="kpi-val" id="kpi-fcs">–</div><div class="kpi-lbl">FCS entries</div></div></div></div>
            <div class="col-6 col-md mb-2"><div class="kpi-box kpi-purple"><i class="fas fa-route kpi-icon"></i><div><div class="kpi-val" id="kpi-multi">–</div><div class="kpi-lbl">People in several intakes</div></div></div></div>
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
                    <h6>MCS and FCS intake by year
                        <button type="button" id="download-year-chart" class="btn btn-xs btn-outline-secondary float-right ir-chart-btn"><i class="fas fa-download mr-1"></i>Image</button>
                    </h6>
                    <div class="ir-note mb-2">The numbers on the bars are the entries for the filters above.</div>
                    <div style="height:340px;position:relative">
                        <canvas id="chartYear"></canvas>
                        <div id="chart-empty" class="ir-note text-center" style="display:none;position:absolute;top:0;right:0;bottom:0;left:0;align-items:center;justify-content:center;">No entries match this selection.</div>
                    </div>
                </div>
                <div class="ir-card">
                    <h6>Intake by gender
                        <button type="button" id="download-gender-chart" class="btn btn-xs btn-outline-secondary float-right ir-chart-btn"><i class="fas fa-download mr-1"></i>Image</button>
                    </h6>
                    <div class="ir-note mb-2">Entries by gender for the filters above; a missing gender is shown as "Not recorded".</div>
                    <div style="height:300px;position:relative">
                        <canvas id="chartGender"></canvas>
                        <div id="gender-empty" class="ir-note text-center" style="display:none;position:absolute;top:0;right:0;bottom:0;left:0;align-items:center;justify-content:center;">No entries match this selection.</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="ir-card" id="detail-card">
            <div class="d-flex align-items-center flex-wrap mb-2">
                <h6 class="mb-0" id="detail-title" style="border:none;padding:0;margin:0;">Associates in the intake</h6>
                <span class="ml-2 badge badge-secondary" id="detail-count"></span>
                <button id="detail-clear" class="btn btn-xs btn-outline-secondary ml-2" style="display:none;font-size:.75rem;"><i class="fas fa-times mr-1"></i>Show all</button>
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
@endsection

@push('scripts')
@if($filters)
<script>
$(function () {
    var URLS = { data: @json(route('intake.reports.data')), entries: @json(route('intake.reports.entries')) };
    var form = document.getElementById('filters');
    var chart = null, chartGender = null, dt = null, entries = [], pick = { year: null, programme: null }, seq = 0;

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
            pick = { year: null, programme: null };
            renderKpis(res[0]); renderMatrix(res[0]); renderChart(res[0]); renderGender(entries); renderChecks(res[0].checks); renderDetail();
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
        cols.forEach(function (c) { head += '<th title="' + esc(c.name) + '">' + esc(c.short) + '</th>'; });
        head += '<th>Entries</th><th>People</th></tr>';
        var body = '';
        (d.matrix || []).forEach(function (r) {
            body += '<tr><td class="ir-year" data-year="' + r.year + '">' + r.year + '</td>';
            cols.forEach(function (c) {
                var n = r.cells[c.id] || 0;
                body += n ? '<td class="ir-cell" data-year="' + r.year + '" data-programme="' + c.id + '">' + n + '</td>' : '<td class="ir-zero">·</td>';
            });
            body += '<td><strong>' + r.entries + '</strong></td><td>' + r.people + '</td></tr>';
        });
        var foot = '<tr><td>All years</td>';
        cols.forEach(function (c) { foot += '<td>' + (d.column_totals[c.id] || 0) + '</td>'; });
        foot += '<td>' + d.totals.entries + '</td><td>' + d.totals.people + '</td></tr>';
        var m = document.getElementById('matrix');
        m.querySelector('thead').innerHTML = head; m.querySelector('tbody').innerHTML = body; m.querySelector('tfoot').innerHTML = foot;
        m.querySelectorAll('td.ir-cell, td.ir-year').forEach(function (td) {
            td.addEventListener('click', function () {
                pick = { year: +td.dataset.year, programme: td.dataset.programme ? +td.dataset.programme : null };
                renderDetail();
                document.getElementById('detail-card').scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    }

    function renderChart(d) {
        var rows = (d.matrix || []).slice().reverse();
        var empty = !rows.length;
        var emptyBox = document.getElementById('chart-empty');
        emptyBox.style.display = empty ? 'flex' : 'none';
        if (chart) { chart.destroy(); chart = null; }
        if (empty) return;

        var data = {
            labels: rows.map(function (r) { return r.year; }),
            datasets: [
                { label: 'MCS', data: rows.map(function (r) { return r.membership; }), backgroundColor: '#1a6fa0' },
                { label: 'FCS', data: rows.map(function (r) { return r.fellowship; }), backgroundColor: '#a02626' }
            ]
        };
        chart = new Chart(document.getElementById('chartYear'), {
            type: 'bar', data: data,
            plugins: window.ChartDataLabels ? [ChartDataLabels] : [],
            options: { responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } },
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

    function renderGender(rows) {
        var counts = { Male: 0, Female: 0, 'Not recorded': 0 };
        (rows || []).forEach(function (e) {
            counts[e.gender === 'Male' || e.gender === 'Female' ? e.gender : 'Not recorded']++;
        });
        var labels = ['Male', 'Female', 'Not recorded'].filter(function (k) { return counts[k] > 0; });
        var values = labels.map(function (k) { return counts[k]; });
        var colours = labels.map(function (k) { return k === 'Male' ? '#1a6fa0' : (k === 'Female' ? '#a02626' : '#8a94a6'); });
        var empty = !values.length;
        document.getElementById('gender-empty').style.display = empty ? 'flex' : 'none';
        if (chartGender) { chartGender.destroy(); chartGender = null; }
        if (empty) return;

        chartGender = new Chart(document.getElementById('chartGender'), {
            type: 'pie',
            data: { labels: labels, datasets: [{ data: values, backgroundColor: colours, borderColor: '#fff', borderWidth: 2 }] },
            plugins: window.ChartDataLabels ? [ChartDataLabels] : [],
            options: { responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } },
                    tooltip: { callbacks: { label: function (ctx) {
                        var total = ctx.dataset.data.reduce(function (a, b) { return a + b; }, 0);
                        var pct = total ? Math.round(ctx.parsed * 100 / total) : 0;
                        return ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                    } } },
                    datalabels: {
                        display: function (ctx) { return ctx.dataset.data[ctx.dataIndex] > 0; },
                        color: '#fff', font: { size: 11, weight: 'bold' },
                        formatter: function (v) { return v; }
                    }
                } }
        });
    }

    // Save a chart as a PNG on a white background (the canvas itself is transparent,
    // which turns black in some viewers).
    function downloadChart(c, filename) {
        if (!c) return;
        var src = c.canvas, out = document.createElement('canvas');
        out.width = src.width; out.height = src.height;
        var ctx = out.getContext('2d');
        ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, out.width, out.height);
        ctx.drawImage(src, 0, 0);
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
        var shown = entries.filter(function (e) { return (pick.year == null || e.year === pick.year) && (pick.programme == null || e.programme_id === pick.programme); });
        var rows = shown.map(function (e) {
            var basis = e.year_basis && e.year_basis !== 'PEN' ? ' <i class="fas fa-info-circle text-muted" title="PEN year ' + esc(e.pen_year) + '. ' + esc(e.year_basis) + '"></i>' : '';
            return [e.year + basis, esc(e.programme), esc(e.pens.join(', ')), esc(e.name), esc(e.gender || ''), esc(e.country || ''), esc(e.hospital || ''), esc(e.status || ''),
                e.sources.map(function (s) { return '<span class="ir-chip ' + s + '">' + s + '</span>'; }).join(''),
                esc((e.other_intakes || []).join(', '))];
        });
        if (dt) { dt.destroy(); $('#detail-table tbody').empty(); }
        dt = $('#detail-table').DataTable({
            data: rows, deferRender: true, pageLength: 25, order: [],
            columnDefs: [{ targets: 0, type: 'html-num' }],
            dom: '<"row"<"col-md-6"l><"col-md-6 text-right"fB>>rt<"row"<"col-md-5"i><"col-md-7"p>>',
            buttons: [
                { extend: 'excelHtml5', text: '<i class="fas fa-file-excel mr-1"></i> Excel', className: 'btn btn-success btn-sm', title: 'Intake report', exportOptions: { format: { body: function (d) { return String(d).replace(/<[^>]+>/g, ''); } } } },
                { extend: 'csvHtml5', text: '<i class="fas fa-file-csv mr-1"></i> CSV', className: 'btn btn-secondary btn-sm', exportOptions: { format: { body: function (d) { return String(d).replace(/<[^>]+>/g, ''); } } } }
            ]
        });
        var parts = [];
        if (pick.year != null) parts.push(pick.year);
        if (pick.programme != null) { var f = entries.find(function (e) { return e.programme_id === pick.programme; }); if (f) parts.push(f.programme); }
        document.getElementById('detail-title').textContent = parts.length ? 'Associates who joined: ' + parts.join(', ') : 'Associates in the intake';
        document.getElementById('detail-count').textContent = shown.length + ' entries';
        document.getElementById('detail-clear').style.display = parts.length ? 'inline-block' : 'none';
    }

    document.getElementById('detail-clear').addEventListener('click', function () { pick = { year: null, programme: null }; renderDetail(); });
    document.getElementById('download-year-chart').addEventListener('click', function () { downloadChart(chart, 'intake-by-year'); });
    document.getElementById('download-gender-chart').addEventListener('click', function () { downloadChart(chartGender, 'intake-by-gender'); });
    document.getElementById('filters-refresh').addEventListener('click', load);

    // Both events: a <select> fires "change" in every browser, "input" covers
    // keyboard/assistive selection. Debounced so changing one filter is one request.
    var filterTimer = null;
    function scheduleLoad() { clearTimeout(filterTimer); filterTimer = setTimeout(load, 120); }
    form.addEventListener('change', scheduleLoad);
    form.addEventListener('input', scheduleLoad);

    load();
});
</script>
@endif
@endpush
