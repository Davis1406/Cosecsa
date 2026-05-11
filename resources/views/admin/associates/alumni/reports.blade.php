@extends('layout.app')

@push('styles')
<style>
    .kpi-box{border-radius:8px;padding:14px 18px;color:#fff;display:flex;align-items:center;gap:14px;box-shadow:0 2px 10px rgba(0,0,0,.12)}
    .kpi-box .kpi-icon{font-size:2rem;opacity:.75}
    .kpi-box .kpi-val{font-size:1.5rem;font-weight:700;line-height:1}
    .kpi-box .kpi-lbl{font-size:.78rem;opacity:.88;margin-top:2px}
    .kpi-red{background:linear-gradient(135deg,#a02626,#7a1c1c)}
    .kpi-blue{background:linear-gradient(135deg,#1a6fa0,#124f72)}
    .kpi-gold{background:linear-gradient(135deg,#b8860b,#8a6408)}
    .kpi-green{background:linear-gradient(135deg,#2a7a3b,#1d5629)}
    .chart-card{background:#fff;border-radius:8px;box-shadow:0 1px 8px rgba(0,0,0,.08);padding:16px;margin-bottom:18px}
    .chart-card h6{font-weight:700;font-size:.82rem;text-transform:uppercase;letter-spacing:.5px;color:#555;margin-bottom:12px;border-bottom:2px solid #a02626;padding-bottom:6px}
    #error-banner{display:none}
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4 style="color:#a02626;font-weight:700;"><i class="fas fa-user-graduate mr-2"></i>Alumni Analytics</h4>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ url('admin/associates/alumni/list') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Alumni List
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content" style="padding:0 20px 20px;">
        <div id="error-banner" class="alert alert-danger"><i class="fas fa-exclamation-triangle mr-2"></i><span id="error-msg"></span></div>

        {{-- Row 1: KPIs --}}
        <div class="row mb-3">
            <div class="col-6 col-md-3 mb-2"><div class="kpi-box kpi-red"><i class="fas fa-user-graduate kpi-icon"></i><div><div class="kpi-val" id="kpi-total">…</div><div class="kpi-lbl">Total Alumni</div></div></div></div>
            <div class="col-6 col-md-3 mb-2"><div class="kpi-box kpi-green"><i class="fas fa-calendar-check kpi-icon"></i><div><div class="kpi-val" id="kpi-recent">…</div><div class="kpi-lbl">Graduated {{ date('Y') }}</div></div></div></div>
            <div class="col-6 col-md-3 mb-2"><div class="kpi-box kpi-blue"><i class="fas fa-mars kpi-icon"></i><div><div class="kpi-val" id="kpi-male">…</div><div class="kpi-lbl">Male</div></div></div></div>
            <div class="col-6 col-md-3 mb-2"><div class="kpi-box kpi-gold"><i class="fas fa-venus kpi-icon"></i><div><div class="kpi-val" id="kpi-female">…</div><div class="kpi-lbl">Female</div></div></div></div>
        </div>

        {{-- Row 2: Country + Fellowship Type + Gender --}}
        <div class="row">
            <div class="col-md-6">
                <div class="chart-card" style="height:320px;">
                    <h6><i class="fas fa-globe-africa mr-1"></i> Alumni by Country (Top 15)</h6>
                    <canvas id="chartCountry" style="max-height:265px;"></canvas>
                </div>
            </div>
            <div class="col-md-3">
                <div class="chart-card" style="height:320px;">
                    <h6><i class="fas fa-award mr-1"></i> Fellowship Type</h6>
                    <canvas id="chartType" style="max-height:265px;"></canvas>
                </div>
            </div>
            <div class="col-md-3">
                <div class="chart-card" style="height:320px;">
                    <h6><i class="fas fa-venus-mars mr-1"></i> Gender</h6>
                    <canvas id="chartGender" style="max-height:265px;"></canvas>
                </div>
            </div>
        </div>

        {{-- Row 3: Graduation Year trend + Programme --}}
        <div class="row">
            <div class="col-md-7">
                <div class="chart-card" style="height:280px;">
                    <h6><i class="fas fa-chart-line mr-1"></i> Graduations by Year (2004 – {{ date('Y') }})</h6>
                    <canvas id="chartYear" style="max-height:225px;"></canvas>
                </div>
            </div>
            <div class="col-md-5">
                <div class="chart-card" style="height:280px;">
                    <h6><i class="fas fa-graduation-cap mr-1"></i> By Programme / Specialty</h6>
                    <canvas id="chartProgramme" style="max-height:225px;"></canvas>
                </div>
            </div>
        </div>

        {{-- Row 4: Specialties bar + Country table --}}
        <div class="row">
            <div class="col-md-5">
                <div class="chart-card" style="height:280px;">
                    <h6><i class="fas fa-stethoscope mr-1"></i> Top 10 Specialties</h6>
                    <canvas id="chartSpecialty" style="max-height:225px;"></canvas>
                </div>
            </div>
            <div class="col-md-7">
                <div class="chart-card">
                    <h6><i class="fas fa-table mr-1"></i> Country Summary</h6>
                    <table id="countryTable" class="table table-sm table-bordered table-striped" style="font-size:.85rem;">
                        <thead class="thead-dark">
                            <tr><th>#</th><th>Country</th><th>Total</th><th>Male</th><th>Female</th><th>First Year</th><th>Latest Year</th></tr>
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
    var PALETTE = ['#a02626','#1a6fa0','#FEC503','#2a7a3b','#8e44ad','#e67e22','#16a085','#2980b9','#d35400','#27ae60','#f39c12','#1abc9c','#c0392b','#8e44ad','#e74c3c'];

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
                    backgroundColor: opts.single ? Array(values.length).fill(opts.single) : PALETTE.slice(0, values.length),
                    borderColor: type === 'line' ? '#a02626' : undefined,
                    borderWidth: type === 'line' ? 2 : 1,
                    fill: false,
                    tension: 0.35,
                    pointBackgroundColor: '#a02626'
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: { display: opts.legend !== false ? type !== 'bar' : false, position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } },
                    datalabels: { display: false }
                },
                scales: (type === 'bar' || type === 'line') ? {
                    x: { ticks: { font: { size: 10 }, maxRotation: 35 } },
                    y: { beginAtZero: true, ticks: { font: { size: 10 } } }
                } : {}
            }
        });
    }

    fetch('{{ url("admin/associates/alumni/reports/data") }}')
        .then(function (r) { return r.json(); })
        .then(function (d) {
            document.getElementById('kpi-total').textContent  = d.total.toLocaleString();
            document.getElementById('kpi-recent').textContent = d.recent.toLocaleString();
            document.getElementById('kpi-male').textContent   = d.male.toLocaleString();
            document.getElementById('kpi-female').textContent = d.female.toLocaleString();

            var lbl = function (a) { return a.map(function (i) { return i.label; }); };
            var val = function (a) { return a.map(function (i) { return i.value; }); };

            mkChart('chartCountry',  'bar',      lbl(d.byCountry),   val(d.byCountry),   { single: '#a02626' });
            mkChart('chartType',     'doughnut', lbl(d.byType),      val(d.byType));
            mkChart('chartGender',   'pie',      lbl(d.byGender),    val(d.byGender));
            mkChart('chartYear',     'line',     lbl(d.byYear),      val(d.byYear),      { legend: false });
            mkChart('chartProgramme','doughnut', lbl(d.byProgramme), val(d.byProgramme));
            mkChart('chartSpecialty','bar',      lbl(d.bySpecialty), val(d.bySpecialty), { single: '#1a6fa0' });

            // Country table
            var rows = '';
            d.countryTable.forEach(function (r, i) {
                rows += '<tr><td>'+(i+1)+'</td><td>'+r.country_name+'</td><td><strong>'+r.total+'</strong></td><td>'+r.male+'</td><td>'+r.female+'</td><td>'+r.first_year+'</td><td>'+r.last_year+'</td></tr>';
            });
            document.getElementById('country-tbody').innerHTML = rows;

            $('#countryTable').DataTable({
                paging: false, searching: false, info: false, order: [[2,'desc']],
                dom: '<"row"<"col-md-12 text-right"B>>t',
                buttons: [
                    { extend:'excelHtml5', text:'<i class="fas fa-file-excel mr-1"></i> Excel', className:'btn btn-success btn-sm', title:'Alumni by Country' },
                    { extend:'pdfHtml5',   text:'<i class="fas fa-file-pdf mr-1"></i> PDF',   className:'btn btn-danger btn-sm',  title:'Alumni by Country', orientation:'landscape' },
                    { extend:'csvHtml5',   text:'<i class="fas fa-file-csv mr-1"></i> CSV',   className:'btn btn-secondary btn-sm' }
                ]
            });
        })
        .catch(function (err) {
            document.getElementById('error-banner').style.display = 'block';
            document.getElementById('error-msg').textContent = 'Failed to load analytics: ' + err.message;
        });
});
</script>
@endpush
