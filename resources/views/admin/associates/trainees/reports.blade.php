@extends('layout.app')

@push('styles')
<style>
    .kpi-box { border-radius: 8px; padding: 14px 18px; color: #fff; display: flex; align-items: center; gap: 14px; box-shadow: 0 2px 10px rgba(0,0,0,.12); cursor: pointer; transition: transform .15s, box-shadow .15s; }
    .kpi-box:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,.22); }
    .kpi-box.kpi-active-tile { outline: 3px solid #fff; outline-offset: 2px; transform: translateY(-2px); }
    .kpi-box .kpi-icon { font-size: 2rem; opacity: .75; }
    .kpi-box .kpi-val  { font-size: 1.5rem; font-weight: 700; line-height: 1; }
    .kpi-box .kpi-lbl  { font-size: .78rem; opacity: .88; margin-top: 2px; }
    .kpi-red    { background: linear-gradient(135deg,#a02626,#7a1c1c); }
    .kpi-blue   { background: linear-gradient(135deg,#1a6fa0,#124f72); }
    .kpi-gold   { background: linear-gradient(135deg,#b8860b,#8a6408); }
    .kpi-green  { background: linear-gradient(135deg,#2a7a3b,#1d5629); }
    .kpi-slate  { background: linear-gradient(135deg,#4a5568,#2d3748); }
    .chart-card { background:#fff; border-radius:8px; box-shadow:0 1px 8px rgba(0,0,0,.08); padding:16px; margin-bottom:18px; }
    .chart-card h6 { font-weight:700; font-size:.82rem; text-transform:uppercase; letter-spacing:.5px; color:#555; margin-bottom:12px; border-bottom:2px solid #a02626; padding-bottom:6px; }
    #error-banner { display:none; }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4 style="color:#a02626; font-weight:700;"><i class="fas fa-chart-bar mr-2"></i>Trainees Analytics</h4>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ url('admin/associates/trainees/trainees') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content" style="padding:0 20px 20px;">
        <div id="error-banner" class="alert alert-danger"><i class="fas fa-exclamation-triangle mr-2"></i><span id="error-msg"></span></div>

        {{-- Row 1: KPIs --}}
        <div class="row mb-2" id="kpi-row">
            <div class="col-6 col-md mb-2"><div class="kpi-box kpi-red"   data-filter="all"     title="Show all trainees"><i class="fas fa-users kpi-icon"></i><div><div class="kpi-val" id="kpi-total">…</div><div class="kpi-lbl">Total Trainees</div></div></div></div>
            <div class="col-6 col-md mb-2"><div class="kpi-box kpi-green" data-filter="active"   title="Show active trainees"><i class="fas fa-user-check kpi-icon"></i><div><div class="kpi-val" id="kpi-active">…</div><div class="kpi-lbl">Active</div></div></div></div>
            <div class="col-6 col-md mb-2"><div class="kpi-box kpi-slate" data-filter="inactive" title="Show inactive trainees"><i class="fas fa-user-slash kpi-icon"></i><div><div class="kpi-val" id="kpi-inactive">…</div><div class="kpi-lbl">Inactive</div></div></div></div>
            <div class="col-6 col-md mb-2"><div class="kpi-box kpi-blue"  data-filter="male"     title="Show male trainees"><i class="fas fa-mars kpi-icon"></i><div><div class="kpi-val" id="kpi-male">…</div><div class="kpi-lbl">Male</div></div></div></div>
            <div class="col-6 col-md mb-2"><div class="kpi-box kpi-gold"  data-filter="female"   title="Show female trainees"><i class="fas fa-venus kpi-icon"></i><div><div class="kpi-val" id="kpi-female">…</div><div class="kpi-lbl">Female</div></div></div></div>
        </div>

        {{-- Drill-down panel (hidden until a tile is clicked) --}}
        <div id="drilldown-panel" style="display:none;" class="mb-3">
            <div class="chart-card" style="padding:12px 16px;">
                <div class="d-flex align-items-center mb-2">
                    <h6 class="mb-0" id="drilldown-title" style="border:none;padding:0;margin:0;"></h6>
                    <span class="ml-2 badge badge-secondary" id="drilldown-count"></span>
                    <button id="drilldown-close" class="btn btn-xs btn-outline-secondary ml-auto" style="font-size:.75rem;">
                        <i class="fas fa-times mr-1"></i>Close
                    </button>
                </div>
                <div id="drilldown-loading" class="text-center py-4 text-muted" style="display:none;">
                    <i class="fas fa-spinner fa-spin mr-1"></i> Loading…
                </div>
                <div class="table-responsive" id="drilldown-table-wrap" style="display:none;">
                    <table id="drilldown-table" class="table table-sm table-bordered table-striped" style="font-size:.82rem;width:100%;">
                        <thead class="thead-dark">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>PEN</th>
                                <th>Programme</th>
                                <th>Country</th>
                                <th>Status</th>
                                <th>Gender</th>
                                <th>Admission</th>
                                <th>Exam Year</th>
                                <th>Hospital</th>
                            </tr>
                        </thead>
                        <tbody id="drilldown-tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Row 2: Country bar + Programme donut + Status donut --}}
        <div class="row">
            <div class="col-md-6">
                <div class="chart-card" style="height:320px;">
                    <h6><i class="fas fa-globe-africa mr-1"></i> Trainees by Country (Top 15)</h6>
                    <canvas id="chartCountry" style="max-height:265px;"></canvas>
                </div>
            </div>
            <div class="col-md-3">
                <div class="chart-card" style="height:320px;">
                    <h6><i class="fas fa-graduation-cap mr-1"></i> By Programme</h6>
                    <canvas id="chartProgramme" style="max-height:265px;"></canvas>
                </div>
            </div>
            <div class="col-md-3">
                <div class="chart-card" style="height:320px;">
                    <h6><i class="fas fa-heartbeat mr-1"></i> By Status</h6>
                    <canvas id="chartStatus" style="max-height:265px;"></canvas>
                </div>
            </div>
        </div>

        {{-- Row 3: Admission Year trend + Gender --}}
        <div class="row">
            <div class="col-md-8">
                <div class="chart-card" style="height:280px;">
                    <h6><i class="fas fa-chart-line mr-1"></i> Admissions by Year (2015–2026)</h6>
                    <canvas id="chartYear" style="max-height:225px;"></canvas>
                </div>
            </div>
            <div class="col-md-4">
                <div class="chart-card" style="height:280px;">
                    <h6><i class="fas fa-venus-mars mr-1"></i> Gender Distribution</h6>
                    <canvas id="chartGender" style="max-height:225px;"></canvas>
                </div>
            </div>
        </div>

        {{-- Row 4: Study Year + Invoice Status --}}
        <div class="row">
            <div class="col-md-7">
                <div class="chart-card" style="height:280px;">
                    <h6><i class="fas fa-book-open mr-1"></i> Current Study Year Distribution</h6>
                    <canvas id="chartStudyYear" style="max-height:225px;"></canvas>
                </div>
            </div>
            <div class="col-md-5">
                <div class="chart-card" style="height:280px;">
                    <h6><i class="fas fa-file-invoice-dollar mr-1"></i> Invoice Status</h6>
                    <canvas id="chartInvoice" style="max-height:225px;"></canvas>
                </div>
            </div>
        </div>

        {{-- Row 5: Country summary table --}}
        <div class="row">
            <div class="col-12">
                <div class="chart-card">
                    <h6><i class="fas fa-table mr-1"></i> Country Summary</h6>
                    <table id="countryTable" class="table table-sm table-bordered table-striped" style="font-size:.88rem;">
                        <thead class="thead-dark">
                            <tr><th>#</th><th>Country</th><th>Total</th><th>Male</th><th>Female</th><th>Active</th></tr>
                        </thead>
                        <tbody id="country-tbody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {

    var PALETTE = [
        '#a02626','#c0392b','#1a6fa0','#FEC503','#2a7a3b',
        '#8e44ad','#e67e22','#16a085','#2980b9','#d35400',
        '#27ae60','#8e44ad','#f39c12','#1abc9c','#c0392b'
    ];

    function mkChart(id, type, labels, values, opts) {
        var ctx = document.getElementById(id);
        if (!ctx) return;
        opts = opts || {};
        return new Chart(ctx, {
            type: type,
            data: {
                labels: labels,
                datasets: [{
                    data: values,
                    backgroundColor: opts.singleColor
                        ? Array(values.length).fill(opts.singleColor)
                        : PALETTE.slice(0, values.length),
                    borderColor: type === 'line' ? '#a02626' : undefined,
                    borderWidth: type === 'line' ? 2 : 1,
                    fill: type === 'line' ? false : undefined,
                    tension: 0.3,
                    pointBackgroundColor: '#a02626'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: opts.legend !== false ? (type !== 'bar') : false, position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } },
                    datalabels: { display: false }
                },
                scales: (type === 'bar' || type === 'line') ? {
                    x: { ticks: { font: { size: 10 }, maxRotation: 35 } },
                    y: { beginAtZero: true, ticks: { font: { size: 10 } } }
                } : {}
            }
        });
    }

    // ── Tile drill-down ─────────────────────────────────────────────────────────
    var drillDt      = null;
    var activeFilter = null;

    var filterLabels = {
        all: 'All Trainees', active: 'Active Trainees',
        inactive: 'Inactive Trainees', male: 'Male Trainees', female: 'Female Trainees'
    };

    function loadDrilldown(filter) {
        if (filter === activeFilter) {
            // Second click on same tile → collapse
            $('#drilldown-panel').slideUp(200);
            $('.kpi-box').removeClass('kpi-active-tile');
            activeFilter = null;
            return;
        }
        activeFilter = filter;

        $('.kpi-box').removeClass('kpi-active-tile');
        $('[data-filter="' + filter + '"]').addClass('kpi-active-tile');

        $('#drilldown-title').text(filterLabels[filter] || filter);
        $('#drilldown-count').text('');
        $('#drilldown-loading').show();
        $('#drilldown-table-wrap').hide();
        $('#drilldown-panel').slideDown(200);

        // Destroy existing DataTable before replacing tbody
        if (drillDt) { drillDt.destroy(); drillDt = null; }

        $.getJSON('{{ url("admin/associates/trainees/reports/list") }}', { filter: filter })
            .done(function (rows) {
                var html = '';
                rows.forEach(function (r, i) {
                    html += '<tr>' +
                        '<td>' + (i + 1) + '</td>' +
                        '<td><a href="/admin/associates/trainees/view/' + r.id + '" target="_blank" style="color:#a02626;">' + (r.name || '—') + '</a></td>' +
                        '<td>' + (r.entry_number || '—') + '</td>' +
                        '<td>' + (r.programme_name || '—') + '</td>' +
                        '<td>' + (r.country_name || '—') + '</td>' +
                        '<td><span class="badge" style="background:' + (r.status==='Active'?'#d4edda':r.status==='Inactive'?'#e2e3e5':'#fff3cd') + ';color:#333;">' + (r.status || '—') + '</span></td>' +
                        '<td>' + (r.gender || '—') + '</td>' +
                        '<td>' + (r.admission_year || '—') + '</td>' +
                        '<td>' + (r.exam_year || '—') + '</td>' +
                        '<td>' + (r.hospital_name || '—') + '</td>' +
                        '</tr>';
                });
                $('#drilldown-tbody').html(html);
                $('#drilldown-count').text(rows.length + ' records');
                $('#drilldown-loading').hide();
                $('#drilldown-table-wrap').show();

                drillDt = $('#drilldown-table').DataTable({
                    destroy: true,
                    pageLength: 25,
                    order: [[1, 'asc']],
                    columnDefs: [{ orderable: false, targets: 0 }],
                    dom: '<"row"<"col-md-6"l><"col-md-6 text-right"B>>frtip',
                    buttons: [
                        { extend:'excelHtml5', text:'<i class="fas fa-file-excel mr-1"></i>Excel', className:'btn btn-success btn-sm', title: filterLabels[filter] },
                        { extend:'csvHtml5',   text:'<i class="fas fa-file-csv mr-1"></i>CSV',   className:'btn btn-secondary btn-sm' }
                    ]
                });
            })
            .fail(function () {
                $('#drilldown-loading').html('<span class="text-danger"><i class="fas fa-exclamation-triangle mr-1"></i>Failed to load data.</span>');
            });
    }

    // Tile click handler
    $(document).on('click', '.kpi-box[data-filter]', function () {
        loadDrilldown($(this).data('filter'));
    });

    // Close button
    $('#drilldown-close').on('click', function () {
        $('#drilldown-panel').slideUp(200);
        $('.kpi-box').removeClass('kpi-active-tile');
        activeFilter = null;
    });

    // ── Analytics data ───────────────────────────────────────────────────────────
    fetch('{{ url("admin/associates/trainees/reports/data") }}')
        .then(function (r) { return r.json(); })
        .then(function (d) {
            // KPIs
            document.getElementById('kpi-total').textContent    = d.total.toLocaleString();
            document.getElementById('kpi-active').textContent   = d.active.toLocaleString();
            document.getElementById('kpi-inactive').textContent = d.inactive.toLocaleString();
            document.getElementById('kpi-male').textContent     = d.male.toLocaleString();
            document.getElementById('kpi-female').textContent   = d.female.toLocaleString();

            var lbl = function (arr) { return arr.map(function (i) { return i.label; }); };
            var val = function (arr) { return arr.map(function (i) { return i.value; }); };

            mkChart('chartCountry',   'bar',  lbl(d.byCountry),   val(d.byCountry),  { singleColor: '#a02626' });
            mkChart('chartProgramme', 'doughnut', lbl(d.byProgramme), val(d.byProgramme));
            mkChart('chartStatus',    'doughnut', lbl(d.byStatus),    val(d.byStatus));
            mkChart('chartYear',      'line', lbl(d.byYear),      val(d.byYear),     { legend: false });
            mkChart('chartGender',    'pie',  lbl(d.byGender),    val(d.byGender),   { legend: true });
            mkChart('chartStudyYear', 'bar',  lbl(d.byStudyYear), val(d.byStudyYear),{ singleColor: '#1a6fa0' });
            mkChart('chartInvoice',   'doughnut', lbl(d.byInvoice), val(d.byInvoice));

            // Country table with DataTable + export
            var tbody = '';
            d.countryTable.forEach(function (r, i) {
                tbody += '<tr><td>' + (i+1) + '</td><td>' + r.country_name + '</td><td><strong>' + r.total + '</strong></td><td>' + r.male + '</td><td>' + r.female + '</td><td>' + r.active + '</td></tr>';
            });
            document.getElementById('country-tbody').innerHTML = tbody;

            $('#countryTable').DataTable({
                paging: false,
                searching: false,
                info: false,
                order: [[2,'desc']],
                dom: '<"row"<"col-md-12 text-right"B>>t',
                buttons: [
                    { extend:'excelHtml5', text:'<i class="fas fa-file-excel mr-1"></i> Excel', className:'btn btn-success btn-sm', title:'Trainees by Country' },
                    { extend:'pdfHtml5',   text:'<i class="fas fa-file-pdf mr-1"></i> PDF',   className:'btn btn-danger btn-sm',  title:'Trainees by Country', orientation:'landscape' },
                    { extend:'csvHtml5',   text:'<i class="fas fa-file-csv mr-1"></i> CSV',   className:'btn btn-secondary btn-sm' }
                ]
            });
        })
        .catch(function (err) {
            document.getElementById('error-banner').style.display = 'block';
            document.getElementById('error-msg').textContent = 'Failed to load analytics data: ' + err.message;
        });
});
</script>
@endpush
