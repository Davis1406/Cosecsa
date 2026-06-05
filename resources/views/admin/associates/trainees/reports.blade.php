@extends('layout.app')

@push('styles')
<style>
    .kpi-box { border-radius: 8px; padding: 14px 18px; color: #fff; display: flex; align-items: center; gap: 14px; box-shadow: 0 2px 10px rgba(0,0,0,.12); }
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
        <div class="row mb-3" id="kpi-row">
            <div class="col-6 col-md mb-2"><div class="kpi-box kpi-red"><i class="fas fa-users kpi-icon"></i><div><div class="kpi-val" id="kpi-total">…</div><div class="kpi-lbl">Total Trainees</div></div></div></div>
            <div class="col-6 col-md mb-2"><div class="kpi-box kpi-green"><i class="fas fa-user-check kpi-icon"></i><div><div class="kpi-val" id="kpi-active">…</div><div class="kpi-lbl">Active</div></div></div></div>
            <div class="col-6 col-md mb-2"><div class="kpi-box kpi-slate"><i class="fas fa-user-slash kpi-icon"></i><div><div class="kpi-val" id="kpi-inactive">…</div><div class="kpi-lbl">Inactive</div></div></div></div>
            <div class="col-6 col-md mb-2"><div class="kpi-box kpi-blue"><i class="fas fa-mars kpi-icon"></i><div><div class="kpi-val" id="kpi-male">…</div><div class="kpi-lbl">Male</div></div></div></div>
            <div class="col-6 col-md mb-2"><div class="kpi-box kpi-gold"><i class="fas fa-venus kpi-icon"></i><div><div class="kpi-val" id="kpi-female">…</div><div class="kpi-lbl">Female</div></div></div></div>
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
