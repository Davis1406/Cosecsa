@extends('layout.app')

@section('content')
<div class="wrapper">
  <div class="content-wrapper" style="padding-bottom:20px;">

    <section class="content-header py-2">
      <div class="container-fluid">
        <div class="row align-items-center">
          <div class="col-sm-6">
            <h4 class="m-0" style="color:#a02626;">
              <i class="fas fa-chart-bar mr-2"></i>Fellows Analytics
            </h4>
          </div>
          <div class="col-sm-6 text-right">
            <button id="btnPrintReport" class="btn btn-sm btn-secondary mr-1">
              <i class="fas fa-print mr-1"></i>Print
            </button>
            <a href="{{ url('admin/associates/fellows/list') }}" class="btn btn-sm btn-outline-secondary">
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
                <label class="small mb-0 font-weight-bold">Fellowship Type</label>
                <select id="rfType" class="form-control form-control-sm">
                  <option value="">All Types</option>
                  @foreach($filterTypes as $t)
                  <option value="{{ $t->id }}">{{ $t->category_name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-6 col-md-2 px-1 mb-1">
                <label class="small mb-0 font-weight-bold">Year</label>
                <select id="rfYear" class="form-control form-control-sm">
                  <option value="">All Years</option>
                  @foreach($filterYears as $y)
                  <option value="{{ $y }}">{{ $y }}</option>
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
                <label class="small mb-0 font-weight-bold">Alumni</label>
                <select id="rfAlumni" class="form-control form-control-sm">
                  <option value="">All Fellows</option>
                  <option value="1">Alumni Only</option>
                  <option value="0">Non-Alumni Only</option>
                </select>
              </div>
            </div>
            <div class="text-right mt-1">
              <button id="btnApplyFilters" class="btn btn-sm btn-primary mr-1" style="background:#a02626;border-color:#a02626;">
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
              <span class="info-box-icon" style="background:#a02626;font-size:1.2rem;width:60px;"><i class="fas fa-users"></i></span>
              <div class="info-box-content">
                <span class="info-box-text" style="font-size:.75rem;">Total Fellows</span>
                <span class="info-box-number" id="kpiTotal" style="font-size:1.4rem;font-weight:700;">—</span>
              </div>
            </div>
          </div>
          <div class="col-6 col-md-3 px-1">
            <div class="info-box mb-2 shadow-sm">
              <span class="info-box-icon bg-success" style="font-size:1.2rem;width:60px;"><i class="fas fa-user-check"></i></span>
              <div class="info-box-content">
                <span class="info-box-text" style="font-size:.75rem;">Active</span>
                <span class="info-box-number" id="kpiActive" style="font-size:1.4rem;font-weight:700;">—</span>
              </div>
            </div>
          </div>
          <div class="col-6 col-md-3 px-1">
            <div class="info-box mb-2 shadow-sm">
              <span class="info-box-icon bg-info" style="font-size:1.2rem;width:60px;"><i class="fas fa-male"></i></span>
              <div class="info-box-content">
                <span class="info-box-text" style="font-size:.75rem;">Male</span>
                <span class="info-box-number" id="kpiMale" style="font-size:1.4rem;font-weight:700;">—</span>
              </div>
            </div>
          </div>
          <div class="col-6 col-md-3 pl-1">
            <div class="info-box mb-2 shadow-sm">
              <span class="info-box-icon bg-warning" style="font-size:1.2rem;width:60px;"><i class="fas fa-female"></i></span>
              <div class="info-box-content">
                <span class="info-box-text" style="font-size:.75rem;">Female</span>
                <span class="info-box-number" id="kpiFemale" style="font-size:1.4rem;font-weight:700;">—</span>
              </div>
            </div>
          </div>
        </div>

        {{-- ── Row 2: Country + Type Donut + Gender Donut ──────────────── --}}
        <div class="row mb-2">
          <div class="col-md-6 pr-1">
            <div class="card shadow-sm h-100 mb-2">
              <div class="card-header py-2 px-3"><h3 class="card-title mb-0"><i class="fas fa-globe-africa mr-1 text-muted"></i>Fellows by Country <small class="text-muted">(Top 15)</small></h3></div>
              <div class="card-body p-2"><canvas id="chartCountry" height="220"></canvas></div>
            </div>
          </div>
          <div class="col-md-3 px-1">
            <div class="card shadow-sm h-100 mb-2">
              <div class="card-header py-2 px-3"><h3 class="card-title mb-0"><i class="fas fa-id-badge mr-1 text-muted"></i>Fellowship Type</h3></div>
              <div class="card-body p-2 d-flex align-items-center justify-content-center"><canvas id="chartType" height="220"></canvas></div>
            </div>
          </div>
          <div class="col-md-3 pl-1">
            <div class="card shadow-sm h-100 mb-2">
              <div class="card-header py-2 px-3"><h3 class="card-title mb-0"><i class="fas fa-venus-mars mr-1 text-muted"></i>Gender</h3></div>
              <div class="card-body p-2 d-flex align-items-center justify-content-center"><canvas id="chartGender" height="220"></canvas></div>
            </div>
          </div>
        </div>

        {{-- ── Row 3: Year Trend + Specialty ────────────────────────────── --}}
        <div class="row mb-2">
          <div class="col-md-7 pr-1">
            <div class="card shadow-sm h-100 mb-2">
              <div class="card-header py-2 px-3"><h3 class="card-title mb-0"><i class="fas fa-chart-line mr-1 text-muted"></i>Fellowship Year Trend</h3></div>
              <div class="card-body p-2"><canvas id="chartYear" height="160"></canvas></div>
            </div>
          </div>
          <div class="col-md-5 pl-1">
            <div class="card shadow-sm h-100 mb-2">
              <div class="card-header py-2 px-3"><h3 class="card-title mb-0"><i class="fas fa-stethoscope mr-1 text-muted"></i>Top Specialties</h3></div>
              <div class="card-body p-2"><canvas id="chartSpecialty" height="160"></canvas></div>
            </div>
          </div>
        </div>

        {{-- ── Row 4: Subscriptions + Exam Results ──────────────────────── --}}
        <div class="row mb-2">
          <div class="col-md-7 pr-1">
            <div class="card shadow-sm h-100 mb-2">
              <div class="card-header py-2 px-3"><h3 class="card-title mb-0"><i class="fas fa-dollar-sign mr-1 text-muted"></i>Annual Subscriptions by Year</h3></div>
              <div class="card-body p-2"><canvas id="chartSubs" height="160"></canvas></div>
            </div>
          </div>
          <div class="col-md-5 pl-1">
            <div class="card shadow-sm h-100 mb-2">
              <div class="card-header py-2 px-3"><h3 class="card-title mb-0"><i class="fas fa-clipboard-check mr-1 text-muted"></i>Exam Pass / Fail</h3></div>
              <div class="card-body p-2"><canvas id="chartExams" height="160"></canvas></div>
            </div>
          </div>
        </div>

        {{-- ── Row 5: Country Summary Table ─────────────────────────────── --}}
        <div class="row">
          <div class="col-md-12">
            <div class="card shadow-sm mb-2">
              <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0"><i class="fas fa-table mr-1 text-muted"></i>Country Summary <small class="text-muted">(Top 20)</small></h3>
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
                      <th>Active</th>
                    </tr>
                  </thead>
                  <tbody id="countryTableBody"></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

      </div>
    </section>
  </div>
</div>
@endsection

@push('styles')
<style>
  .card          { border-radius:6px; }
  .card-header   { background:#fff; border-bottom:1px solid #f0f0f0; }
  .card-title    { font-weight:600; font-size:.88rem; color:#444; }
  .info-box      { min-height:60px; }
  #reportFilters label { color:#555; }
  @@media print {
    .main-sidebar,.main-footer,.content-header { display:none !important; }
    .content-wrapper { margin-left:0 !important; }
    #btnPrintReport { display:none; }
    .card { break-inside:avoid; }
  }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function () {

  var BRAND   = '#a02626';
  var PALETTE = ['#a02626','#c0392b','#e74c3c','#2980b9','#27ae60','#f39c12',
                 '#8e44ad','#16a085','#d35400','#2c3e50','#1abc9c','#f1c40f',
                 '#7f8c8d','#2ecc71','#e67e22'];

  if (window.ChartDataLabels) {
    Chart.defaults.set('plugins.datalabels', { display: false });
  }

  // Holds Chart.js instances so we can destroy/recreate on filter change
  var charts = {};
  var dtCountry = null;

  function buildUrl() {
    var params = {};
    var cid = $('#rfCountry').val();
    var tid = $('#rfType').val();
    var yr  = $('#rfYear').val();
    var gen = $('#rfGender').val();
    var alm = $('#rfAlumni').val();
    if (cid) params.country_id  = cid;
    if (tid) params.category_id = tid;
    if (yr)  params.year        = yr;
    if (gen) params.gender      = gen;
    if (alm !== '') params.is_alumni = alm;
    params._t = Date.now();
    var qs = $.param(params);
    return '{{ url("admin/associates/fellows/reports/data") }}' + '?' + qs;
  }

  function destroyChart(key) {
    if (charts[key]) { charts[key].destroy(); charts[key] = null; }
  }

  function loadReport() {
    fetch(buildUrl(), { cache: 'no-store' })
      .then(function(r){ if(!r.ok) throw new Error('HTTP '+r.status); return r.json(); })
      .then(function(d){
        // KPIs
        $('#kpiTotal').text(d.kpi.total.toLocaleString());
        $('#kpiActive').text(d.kpi.active.toLocaleString());
        $('#kpiMale').text(d.kpi.male.toLocaleString());
        $('#kpiFemale').text(d.kpi.female.toLocaleString());

        var chartOpts = { responsive:true, maintainAspectRatio:true };

        // 1. Country horizontal bar
        destroyChart('country');
        charts.country = new Chart($('#chartCountry')[0], {
          type:'bar',
          data:{
            labels: d.byCountry.map(function(x){return x.label;}),
            datasets:[{ label:'Fellows', data:d.byCountry.map(function(x){return x.value;}),
              backgroundColor:PALETTE, borderRadius:3 }]
          },
          options: $.extend(true,{}, chartOpts, {
            indexAxis:'y',
            plugins:{ legend:{display:false} },
            scales:{ x:{beginAtZero:true, grid:{color:'#f0f0f0'}, ticks:{font:{size:10}}},
                     y:{grid:{display:false}, ticks:{font:{size:10}}} }
          })
        });

        // 2. Fellowship Type donut
        destroyChart('type');
        charts.type = new Chart($('#chartType')[0], {
          type:'doughnut',
          data:{
            labels: d.byType.map(function(x){return x.label;}),
            datasets:[{ data:d.byType.map(function(x){return x.value;}),
              backgroundColor:PALETTE, borderWidth:2, borderColor:'#fff' }]
          },
          options:{ cutout:'60%', plugins:{
            legend:{position:'bottom', labels:{padding:6,boxWidth:10,font:{size:10}}},
            tooltip:{callbacks:{label:function(c){return ' '+c.label+': '+c.parsed.toLocaleString();}}}
          }}
        });

        // 3. Year Trend line
        destroyChart('year');
        charts.year = new Chart($('#chartYear')[0], {
          type:'line',
          data:{
            labels: d.byYear.map(function(x){return x.label;}),
            datasets:[{ label:'Inducted', data:d.byYear.map(function(x){return x.value;}),
              borderColor:BRAND, backgroundColor:'rgba(160,38,38,0.08)',
              borderWidth:2, fill:true, tension:0.4, pointRadius:3 }]
          },
          options: $.extend(true,{}, chartOpts, {
            plugins:{ legend:{display:false} },
            scales:{ x:{grid:{display:false}, ticks:{font:{size:10}}},
                     y:{beginAtZero:true, grid:{color:'#f0f0f0'}, ticks:{font:{size:10}}} }
          })
        });

        // 4. Gender donut
        destroyChart('gender');
        charts.gender = new Chart($('#chartGender')[0], {
          type:'doughnut',
          data:{
            labels: d.byGender.map(function(x){return x.label;}),
            datasets:[{ data:d.byGender.map(function(x){return x.value;}),
              backgroundColor:['#2980b9','#e74c3c','#95a5a6'], borderWidth:2, borderColor:'#fff' }]
          },
          options:{ cutout:'60%', plugins:{
            legend:{position:'bottom', labels:{padding:6,boxWidth:10,font:{size:10}}},
            tooltip:{callbacks:{label:function(c){return ' '+c.label+': '+c.parsed.toLocaleString();}}}
          }}
        });

        // 5. Specialty horizontal bar
        destroyChart('specialty');
        charts.specialty = new Chart($('#chartSpecialty')[0], {
          type:'bar',
          data:{
            labels: d.bySpecialty.map(function(x){return x.label;}),
            datasets:[{ label:'Fellows', data:d.bySpecialty.map(function(x){return x.value;}),
              backgroundColor:PALETTE, borderRadius:3 }]
          },
          options: $.extend(true,{}, chartOpts, {
            indexAxis:'y',
            plugins:{ legend:{display:false} },
            scales:{ x:{beginAtZero:true, grid:{color:'#f0f0f0'}, ticks:{font:{size:10}}},
                     y:{grid:{display:false}, ticks:{font:{size:10}}} }
          })
        });

        // 6. Subscriptions stacked bar
        destroyChart('subs');
        charts.subs = new Chart($('#chartSubs')[0], {
          type:'bar',
          data:{
            labels: d.subscriptions.map(function(x){return x.year;}),
            datasets:[
              { label:'Paid',   data:d.subscriptions.map(function(x){return x.Paid;}),   backgroundColor:'#27ae60', borderRadius:2 },
              { label:'Unpaid', data:d.subscriptions.map(function(x){return x.Unpaid;}), backgroundColor:'#e74c3c', borderRadius:2 },
              { label:'Waived', data:d.subscriptions.map(function(x){return x.Waived;}), backgroundColor:'#f39c12', borderRadius:2 }
            ]
          },
          options: $.extend(true,{}, chartOpts, {
            plugins:{ legend:{position:'top', labels:{boxWidth:10,font:{size:10}}},
                      tooltip:{mode:'index',intersect:false} },
            scales:{ x:{stacked:true, grid:{display:false}, ticks:{font:{size:10}}},
                     y:{stacked:true, beginAtZero:true, grid:{color:'#f0f0f0'}, ticks:{font:{size:10}}} }
          })
        });

        // 7. Exam pass/fail
        var grp={};
        $.each(d.examStats, function(_,e){
          var k=e.year+' P'+e.part;
          if(!grp[k]) grp[k]={PASS:0,FAIL:0};
          if(e.result==='PASS') grp[k].PASS=e.cnt;
          if(e.result==='FAIL') grp[k].FAIL=e.cnt;
        });
        var elabels=[],epass=[],efail=[];
        Object.keys(grp).sort().forEach(function(k){
          elabels.push(k); epass.push(grp[k].PASS); efail.push(grp[k].FAIL);
        });
        destroyChart('exams');
        charts.exams = new Chart($('#chartExams')[0], {
          type:'bar',
          data:{
            labels:elabels,
            datasets:[
              { label:'Pass', data:epass, backgroundColor:'#27ae60', borderRadius:3 },
              { label:'Fail', data:efail, backgroundColor:'#e74c3c', borderRadius:3 }
            ]
          },
          options: $.extend(true,{}, chartOpts, {
            plugins:{ legend:{position:'top', labels:{boxWidth:10,font:{size:10}}},
                      tooltip:{mode:'index',intersect:false} },
            scales:{ x:{grid:{display:false}, ticks:{font:{size:10}}},
                     y:{beginAtZero:true, grid:{color:'#f0f0f0'}, ticks:{font:{size:10}}} }
          })
        });

        // 8. Country table
        if (dtCountry) { dtCountry.destroy(); dtCountry = null; $('#tableButtons').empty(); }
        var rows='';
        $.each(d.countryTable, function(i,r){
          rows+='<tr><td>'+(i+1)+'</td><td>'+r.country_name+'</td><td>'+r.total+'</td><td>'+r.male+'</td><td>'+r.female+'</td><td>'+r.active+'</td></tr>';
        });
        $('#countryTableBody').html(rows);

        dtCountry = $('#countryTable').DataTable({
          paging:false, searching:false, info:false, order:[[2,'desc']],
          dom:'Brt',
          buttons:[
            { extend:'excelHtml5', text:'<i class="fas fa-file-excel mr-1"></i>Excel', className:'btn btn-success btn-sm', title:'Fellows by Country' },
            { extend:'pdfHtml5',   text:'<i class="fas fa-file-pdf mr-1"></i>PDF',   className:'btn btn-danger btn-sm',  title:'Fellows by Country', orientation:'landscape' },
            { extend:'csvHtml5',   text:'<i class="fas fa-file-csv mr-1"></i>CSV',   className:'btn btn-info btn-sm',    title:'Fellows by Country' }
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

  // Apply filters
  $('#btnApplyFilters').on('click', loadReport);

  // Clear filters
  $('#btnClearRFilters').on('click', function () {
    $('#rfCountry, #rfType, #rfYear, #rfGender, #rfAlumni').val('');
    loadReport();
  });

  $('#btnPrintReport').on('click', function(){ window.print(); });
});
</script>
@endpush
