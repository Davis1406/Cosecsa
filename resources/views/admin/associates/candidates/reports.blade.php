@extends('layout.app')

@section('content')
<div class="wrapper">
  <div class="content-wrapper" style="padding-bottom:20px;">

    <section class="content-header py-2">
      <div class="container-fluid">
        <div class="row align-items-center">
          <div class="col-sm-6">
            <h4 class="m-0" style="color:#a02626;">
              <i class="fas fa-chart-bar mr-2"></i>Candidates Analytics
            </h4>
          </div>
          <div class="col-sm-6 text-right">
            <button id="btnPrintReport" class="btn btn-sm btn-secondary mr-1">
              <i class="fas fa-print mr-1"></i>Print
            </button>
            <a href="{{ url('admin/associates/candidates/list') }}" class="btn btn-sm btn-outline-secondary">
              <i class="fas fa-arrow-left mr-1"></i>Back
            </a>
          </div>
        </div>
      </div>
    </section>

    <section class="content pt-1">
      <div class="container-fluid" id="reportBody">

        {{-- ── Filter Panel ──────────────────────────────────────────────── --}}
        <div class="card card-outline card-secondary mb-2 shadow-sm">
          <div class="card-body py-2">
            <div class="row align-items-end" id="reportFilters">
              <div class="col-6 col-md-3 pr-1 mb-1">
                <label class="small mb-0 font-weight-bold">Country</label>
                <select id="rfCountry" class="form-control form-control-sm">
                  <option value="">All Countries</option>
                  @foreach($filterCountries as $c)
                  <option value="{{ $c->id }}">{{ $c->country_name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-6 col-md-3 px-1 mb-1">
                <label class="small mb-0 font-weight-bold">Programme</label>
                <select id="rfProgramme" class="form-control form-control-sm">
                  <option value="">All Programmes</option>
                  @foreach($filterProgrammes as $p)
                  <option value="{{ $p->id }}">{{ $p->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-6 col-md-2 px-1 mb-1">
                <label class="small mb-0 font-weight-bold">Exam Year</label>
                <select id="rfYear" class="form-control form-control-sm">
                  <option value="">All Years</option>
                  @foreach($filterYears as $y)
                  <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-6 col-md-2 px-1 mb-1">
                <label class="small mb-0 font-weight-bold">Gender</label>
                <select id="rfGender" class="form-control form-control-sm">
                  <option value="">All Genders</option>
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
                </select>
              </div>
              <div class="col-6 col-md-2 pl-1 mb-1">
                <label class="small mb-0 font-weight-bold">Fee Paid</label>
                <select id="rfFeePaid" class="form-control form-control-sm">
                  <option value="">All</option>
                  <option value="Yes">Paid</option>
                  <option value="No">Unpaid</option>
                </select>
              </div>
            </div>
            <div class="text-right mt-1">
              <button id="btnApplyFilters" class="btn btn-sm" style="background:#a02626;border-color:#a02626;color:#fff;">
                <i class="fas fa-filter mr-1"></i>Apply
              </button>
              <button id="btnClearRFilters" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-times mr-1"></i>Clear
              </button>
            </div>
          </div>
        </div>

        {{-- ── Row 1: KPI Cards ─────────────────────────────────────────── --}}
        <div class="row mb-2">
          <div class="col-6 col-md-3 pr-1">
            <div class="info-box mb-2 shadow-sm">
              <span class="info-box-icon" style="background:#a02626;font-size:1.2rem;width:60px;">
                <i class="fas fa-user-graduate"></i>
              </span>
              <div class="info-box-content">
                <span class="info-box-text" style="font-size:.75rem;">Total Candidates</span>
                <span class="info-box-number" id="kpiTotal" style="font-size:1.4rem;font-weight:700;">—</span>
              </div>
            </div>
          </div>
          <div class="col-6 col-md-3 px-1">
            <div class="info-box mb-2 shadow-sm">
              <span class="info-box-icon bg-success" style="font-size:1.2rem;width:60px;">
                <i class="fas fa-check-circle"></i>
              </span>
              <div class="info-box-content">
                <span class="info-box-text" style="font-size:.75rem;">Fee Paid</span>
                <span class="info-box-number" id="kpiFeePaid" style="font-size:1.4rem;font-weight:700;">—</span>
              </div>
            </div>
          </div>
          <div class="col-6 col-md-3 px-1">
            <div class="info-box mb-2 shadow-sm">
              <span class="info-box-icon bg-info" style="font-size:1.2rem;width:60px;">
                <i class="fas fa-male"></i>
              </span>
              <div class="info-box-content">
                <span class="info-box-text" style="font-size:.75rem;">Male</span>
                <span class="info-box-number" id="kpiMale" style="font-size:1.4rem;font-weight:700;">—</span>
              </div>
            </div>
          </div>
          <div class="col-6 col-md-3 pl-1">
            <div class="info-box mb-2 shadow-sm">
              <span class="info-box-icon bg-warning" style="font-size:1.2rem;width:60px;">
                <i class="fas fa-female"></i>
              </span>
              <div class="info-box-content">
                <span class="info-box-text" style="font-size:.75rem;">Female</span>
                <span class="info-box-number" id="kpiFemale" style="font-size:1.4rem;font-weight:700;">—</span>
              </div>
            </div>
          </div>
        </div>

        {{-- ── Row 2: Country Bar + Programme Donut + Gender Donut ─────── --}}
        <div class="row mb-2">
          <div class="col-md-6 pr-1">
            <div class="card shadow-sm h-100 mb-2">
              <div class="card-header py-2 px-3">
                <h3 class="card-title mb-0">
                  <i class="fas fa-globe-africa mr-1 text-muted"></i>Candidates by Country
                  <small class="text-muted">(Top 15)</small>
                </h3>
              </div>
              <div class="card-body p-2"><canvas id="chartCountry" height="220"></canvas></div>
            </div>
          </div>
          <div class="col-md-3 px-1">
            <div class="card shadow-sm h-100 mb-2">
              <div class="card-header py-2 px-3">
                <h3 class="card-title mb-0">
                  <i class="fas fa-stethoscope mr-1 text-muted"></i>Programme
                </h3>
              </div>
              <div class="card-body p-2 d-flex align-items-center justify-content-center">
                <canvas id="chartProgramme" height="220"></canvas>
              </div>
            </div>
          </div>
          <div class="col-md-3 pl-1">
            <div class="card shadow-sm h-100 mb-2">
              <div class="card-header py-2 px-3">
                <h3 class="card-title mb-0">
                  <i class="fas fa-venus-mars mr-1 text-muted"></i>Gender
                </h3>
              </div>
              <div class="card-body p-2 d-flex align-items-center justify-content-center">
                <canvas id="chartGender" height="220"></canvas>
              </div>
            </div>
          </div>
        </div>

        {{-- ── Row 3: Exam Year Trend + Fee Paid ──────────────────────── --}}
        <div class="row mb-2">
          <div class="col-md-7 pr-1">
            <div class="card shadow-sm h-100 mb-2">
              <div class="card-header py-2 px-3">
                <h3 class="card-title mb-0">
                  <i class="fas fa-chart-line mr-1 text-muted"></i>Candidates by Exam Year
                </h3>
              </div>
              <div class="card-body p-2"><canvas id="chartYear" height="160"></canvas></div>
            </div>
          </div>
          <div class="col-md-5 pl-1">
            <div class="card shadow-sm h-100 mb-2">
              <div class="card-header py-2 px-3">
                <h3 class="card-title mb-0">
                  <i class="fas fa-dollar-sign mr-1 text-muted"></i>Fee Payment Status
                </h3>
              </div>
              <div class="card-body p-2 d-flex align-items-center justify-content-center">
                <canvas id="chartFeePaid" height="160"></canvas>
              </div>
            </div>
          </div>
        </div>

        {{-- ── Row 4: Repeat / MMed stats ──────────────────────────────── --}}
        <div class="row mb-2">
          <div class="col-md-12">
            <div class="card shadow-sm mb-2">
              <div class="card-header py-2 px-3">
                <h3 class="card-title mb-0">
                  <i class="fas fa-redo mr-1 text-muted"></i>Repeat &amp; MMed Qualification
                </h3>
              </div>
              <div class="card-body p-3">
                <div class="row text-center">
                  <div class="col-md-4">
                    <div class="p-3" style="border:1px solid #f0f0f0;border-radius:8px;">
                      <div id="kpiRepeatP1" style="font-size:2rem;font-weight:700;color:#e74c3c;">—</div>
                      <div class="text-muted" style="font-size:.82rem;">Repeating Paper I</div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="p-3" style="border:1px solid #f0f0f0;border-radius:8px;">
                      <div id="kpiRepeatP2" style="font-size:2rem;font-weight:700;color:#f39c12;">—</div>
                      <div class="text-muted" style="font-size:.82rem;">Repeating Paper II</div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="p-3" style="border:1px solid #f0f0f0;border-radius:8px;">
                      <div id="kpiMmed" style="font-size:2rem;font-weight:700;color:#27ae60;">—</div>
                      <div class="text-muted" style="font-size:.82rem;">MMed Qualified</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- ── Row 5: Country Summary Table ─────────────────────────────── --}}
        <div class="row">
          <div class="col-md-12">
            <div class="card shadow-sm mb-2">
              <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                  <i class="fas fa-table mr-1 text-muted"></i>Country Summary
                  <small class="text-muted">(Top 20)</small>
                </h3>
                <div id="tableButtons"></div>
              </div>
              <div class="card-body p-2">
                <table id="countryTable" class="table table-sm table-bordered table-striped mb-0" style="font-size:.85rem;">
                  <thead class="thead-light">
                    <tr>
                      <th style="width:40px;">#</th>
                      <th>Country</th>
                      <th>Total</th>
                      <th>Male</th>
                      <th>Female</th>
                      <th>Fee Paid</th>
                    </tr>
                  </thead>
                  <tbody id="countryTableBody"></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

      </div>{{-- /reportBody --}}
    </section>
  </div>
</div>
@endsection

@push('styles')
<style>
  .card        { border-radius:6px; }
  .card-header { background:#fff; border-bottom:1px solid #f0f0f0; }
  .card-title  { font-weight:600; font-size:.88rem; color:#444; }
  .info-box    { min-height:60px; }
  @@media print {
    .main-sidebar,.main-footer,.content-header { display:none !important; }
    .content-wrapper { margin-left:0 !important; }
    #btnPrintReport  { display:none; }
    .card { break-inside:avoid; }
  }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function () {

  var BRAND   = '#a02626';
  var PALETTE = ['#a02626','#2980b9','#27ae60','#f39c12','#8e44ad','#16a085',
                 '#d35400','#2c3e50','#c0392b','#1abc9c','#e74c3c','#f1c40f',
                 '#7f8c8d','#2ecc71','#e67e22'];

  if (window.ChartDataLabels) {
    Chart.defaults.set('plugins.datalabels', { display: false });
  }

  var charts    = {};
  var dtCountry = null;

  function buildUrl() {
    var params = {};
    var cid = $('#rfCountry').val();
    var pid = $('#rfProgramme').val();
    var yr  = $('#rfYear').val();
    var gen = $('#rfGender').val();
    var fp  = $('#rfFeePaid').val();
    if (cid) params.country_id   = cid;
    if (pid) params.programme_id = pid;
    if (yr)  params.year         = yr;
    if (gen) params.gender       = gen;
    if (fp !== '') params.fee_paid = fp;
    var qs = $.param(params);
    return '{{ url("admin/associates/candidates/reports/data") }}' + (qs ? '?' + qs : '');
  }

  function destroyChart(key) {
    if (charts[key]) { charts[key].destroy(); charts[key] = null; }
  }

  function loadReport() {
    fetch(buildUrl())
      .then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
      .then(function(d){

        // KPIs
        $('#kpiTotal').text(d.kpi.total.toLocaleString());
        $('#kpiFeePaid').text(d.kpi.feePaidC.toLocaleString());
        $('#kpiMale').text(d.kpi.male.toLocaleString());
        $('#kpiFemale').text(d.kpi.female.toLocaleString());

        // Repeat / MMed
        var rs = d.repeatStats;
        $('#kpiRepeatP1').text(rs ? rs.repeat_p1 : '—');
        $('#kpiRepeatP2').text(rs ? rs.repeat_p2 : '—');
        $('#kpiMmed').text(rs ? rs.mmed_qualified : '—');

        var chartOpts = { responsive:true, maintainAspectRatio:true };

        // 1. Country horizontal bar
        destroyChart('country');
        charts.country = new Chart($('#chartCountry')[0], {
          type: 'bar',
          data: {
            labels: d.byCountry.map(function(x){ return x.label; }),
            datasets: [{ label:'Candidates', data: d.byCountry.map(function(x){ return x.value; }),
              backgroundColor: PALETTE, borderRadius: 3 }]
          },
          options: $.extend(true, {}, chartOpts, {
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero:true, grid:{color:'#f0f0f0'}, ticks:{font:{size:10}} },
                      y: { grid:{display:false}, ticks:{font:{size:10}} } }
          })
        });

        // 2. Programme doughnut
        destroyChart('programme');
        charts.programme = new Chart($('#chartProgramme')[0], {
          type: 'doughnut',
          data: {
            labels: d.byProgramme.map(function(x){ return x.label; }),
            datasets: [{ data: d.byProgramme.map(function(x){ return x.value; }),
              backgroundColor: PALETTE, borderWidth:2, borderColor:'#fff' }]
          },
          options: { cutout:'60%', plugins:{
            legend:{ position:'bottom', labels:{padding:5,boxWidth:8,font:{size:9}} },
            tooltip:{ callbacks:{ label:function(c){ return ' '+c.label+': '+c.parsed.toLocaleString(); } } }
          }}
        });

        // 3. Gender doughnut
        destroyChart('gender');
        charts.gender = new Chart($('#chartGender')[0], {
          type: 'doughnut',
          data: {
            labels: d.byGender.map(function(x){ return x.label; }),
            datasets: [{ data: d.byGender.map(function(x){ return x.value; }),
              backgroundColor: ['#2980b9','#e74c3c','#95a5a6'], borderWidth:2, borderColor:'#fff' }]
          },
          options: { cutout:'60%', plugins:{
            legend:{ position:'bottom', labels:{padding:6,boxWidth:10,font:{size:10}} },
            tooltip:{ callbacks:{ label:function(c){ return ' '+c.label+': '+c.parsed.toLocaleString(); } } }
          }}
        });

        // 4. Exam Year bar
        destroyChart('year');
        charts.year = new Chart($('#chartYear')[0], {
          type: 'bar',
          data: {
            labels: d.byYear.map(function(x){ return x.label; }),
            datasets: [{ label:'Candidates', data: d.byYear.map(function(x){ return x.value; }),
              backgroundColor: BRAND, borderRadius: 4 }]
          },
          options: $.extend(true, {}, chartOpts, {
            plugins: { legend:{display:false} },
            scales: { x: { grid:{display:false}, ticks:{font:{size:10}} },
                      y: { beginAtZero:true, grid:{color:'#f0f0f0'}, ticks:{font:{size:10}} } }
          })
        });

        // 5. Fee Paid doughnut
        destroyChart('feePaid');
        charts.feePaid = new Chart($('#chartFeePaid')[0], {
          type: 'doughnut',
          data: {
            labels: d.byFeePaid.map(function(x){ return x.label; }),
            datasets: [{ data: d.byFeePaid.map(function(x){ return x.value; }),
              backgroundColor: ['#27ae60','#e74c3c','#95a5a6'], borderWidth:2, borderColor:'#fff' }]
          },
          options: { cutout:'60%', plugins:{
            legend:{ position:'bottom', labels:{padding:6,boxWidth:10,font:{size:10}} },
            tooltip:{ callbacks:{ label:function(c){ return ' '+c.label+': '+c.parsed.toLocaleString(); } } }
          }}
        });

        // 6. Country table
        if (dtCountry) { dtCountry.destroy(); dtCountry = null; $('#tableButtons').empty(); }
        var rows = '';
        $.each(d.countryTable, function(i, r){
          rows += '<tr><td>'+(i+1)+'</td><td>'+r.country_name+'</td><td>'+r.total+'</td><td>'+r.male+'</td><td>'+r.female+'</td><td>'+r.fee_paid+'</td></tr>';
        });
        $('#countryTableBody').html(rows);

        dtCountry = $('#countryTable').DataTable({
          paging: false, searching: false, info: false, order: [[2,'desc']],
          dom: 'Brt',
          buttons: [
            { extend:'excelHtml5', text:'<i class="fas fa-file-excel mr-1"></i>Excel', className:'btn btn-success btn-sm', title:'Candidates by Country' },
            { extend:'pdfHtml5',   text:'<i class="fas fa-file-pdf mr-1"></i>PDF',   className:'btn btn-danger btn-sm',  title:'Candidates by Country', orientation:'landscape' },
            { extend:'csvHtml5',   text:'<i class="fas fa-file-csv mr-1"></i>CSV',   className:'btn btn-info btn-sm',    title:'Candidates by Country' }
          ]
        });
        dtCountry.buttons().container().appendTo('#tableButtons');

      })
      .catch(function(err){
        console.error('Report error:', err);
        $('#reportBody').prepend('<div class="alert alert-danger mx-2 mt-2">Could not load report data — please refresh.</div>');
      });
  }

  // Initial load
  loadReport();

  $('#btnApplyFilters').on('click', loadReport);

  $('#btnClearRFilters').on('click', function () {
    $('#rfCountry, #rfProgramme, #rfYear, #rfGender, #rfFeePaid').val('');
    loadReport();
  });

  $('#btnPrintReport').on('click', function(){ window.print(); });
});
</script>
@endpush
