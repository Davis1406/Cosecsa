@extends('layout.app')

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <div class="row align-items-center">
        <div class="col-sm-8">
          <h4 class="m-0" style="color:#a02626;"><i class="fas fa-chart-bar mr-2"></i>College Reports</h4>
          <p class="text-muted mb-0" style="font-size:.83rem;">Select a category to view its analytics dashboard, then build a custom report below.</p>
        </div>
        <div class="col-sm-4 text-right">
          <button id="btnPrintReport" class="btn btn-sm btn-secondary" style="display:none;">
            <i class="fas fa-print mr-1"></i>Print
          </button>
        </div>
      </div>
    </div>
  </section>

  <section class="content">
    <div class="container-fluid">
      @include('_message')

      {{-- ── Category Tabs ──────────────────────────────────────────────── --}}
      <div class="card mb-3 shadow-sm">
        <div class="card-body py-2 px-3">
          <div class="d-flex flex-wrap" id="categoryTabs" style="gap:6px;">
            @foreach($types as $key => $t)
              <button type="button"
                      class="btn btn-sm btn-outline-secondary cat-tab"
                      data-type="{{ $key }}"
                      style="font-size:.82rem; border-radius:20px; padding:4px 14px;">
                {{ $t['label'] }}
              </button>
            @endforeach
          </div>
        </div>
      </div>

      {{-- ── Visual Dashboard Panel ─────────────────────────────────────── --}}
      <div id="vd-panel" style="display:none;">

        {{-- Header bar --}}
        <div class="d-flex align-items-center justify-content-between mb-2">
          <h5 id="vd-title" class="mb-0" style="color:#a02626; font-weight:700;"></h5>
          <div>
            <a id="vd-fulllink" href="#" class="btn btn-sm btn-outline-secondary mr-2" style="font-size:.8rem;">
              <i class="fas fa-external-link-alt mr-1"></i>Open Full Dashboard
            </a>
            <button id="btnScrollCustom" class="btn btn-sm btn-secondary" style="font-size:.8rem;background:#a02626;border-color:#a02626;color:#fff;">
              <i class="fas fa-sliders-h mr-1"></i>Build Custom Report
            </button>
          </div>
        </div>

        {{-- Loading spinner --}}
        <div id="vd-loading" class="text-center py-5 text-muted">
          <i class="fas fa-spinner fa-spin fa-2x mb-2"></i><br>Loading analytics…
        </div>

        {{-- Error banner --}}
        <div id="vd-error" class="alert alert-danger" style="display:none;"></div>

        {{-- KPI Row --}}
        <div id="vd-kpis" class="row mb-2" style="display:none;"></div>

        {{-- Charts area (injected per category) --}}
        <div id="vd-charts" style="display:none;"></div>

        {{-- Country table --}}
        <div id="vd-table-wrap" class="row mb-3" style="display:none;">
          <div class="col-12">
            <div class="card shadow-sm">
              <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0"><i class="fas fa-table mr-1 text-muted"></i>Country Summary</h3>
                <div id="vd-table-buttons"></div>
              </div>
              <div class="card-body p-2">
                <table id="vd-country-table" class="table table-sm table-bordered table-striped mb-0" style="font-size:.85rem;width:100%;">
                  <thead class="thead-light" id="vd-table-head"></thead>
                  <tbody id="vd-table-body"></tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

      </div>{{-- /vd-panel --}}

      {{-- ── Custom Report Builder ──────────────────────────────────────── --}}
      <div id="custom-panel">
        <div class="card shadow-sm">
          <div class="card-header py-2 px-3" id="customToggleHeader" style="cursor:pointer; user-select:none;">
            <div class="d-flex align-items-center justify-content-between">
              <h3 class="card-title mb-0" style="font-size:.92rem;">
                <i class="fas fa-sliders-h mr-2 text-muted"></i>
                <span id="customPanelTitle">Custom Report Builder</span>
              </h3>
              <i class="fas fa-chevron-down" id="customToggleIcon" style="font-size:.8rem; color:#888;"></i>
            </div>
          </div>
          <div class="card-body" id="customBody">

            {{-- Initial state: prompt to select a category --}}
            <div id="customPrompt" class="text-muted text-center py-3" style="font-size:.88rem;">
              <i class="fas fa-arrow-up mr-1"></i> Select a category above to load the custom report builder.
            </div>

            {{-- Loading spinner --}}
            <div id="customLoading" style="display:none;" class="text-center py-3 text-muted">
              <i class="fas fa-spinner fa-spin mr-1"></i> Loading fields…
            </div>

            {{-- Field / filter form --}}
            <form id="customForm" method="post" action="{{ url('admin/reports/generate') }}" style="display:none;">
              @csrf
              <input type="hidden" name="type" id="customType">

              {{-- Fields checkboxes --}}
              <div class="mb-3">
                <label class="font-weight-bold mb-1" style="font-size:.85rem;">
                  Fields to include <small class="text-muted font-weight-normal">(check all that apply)</small>
                </label>
                <div id="customFields" class="row" style="font-size:.83rem;"></div>
              </div>

              {{-- Filters --}}
              <div id="customFiltersWrap" class="mb-3" style="display:none;">
                <label class="font-weight-bold mb-1" style="font-size:.85rem;">Filters <small class="text-muted font-weight-normal">(optional)</small></label>
                <div id="customFilters" class="row" style="font-size:.83rem;"></div>
              </div>

              {{-- Group-by --}}
              <div id="customGroupWrap" class="mb-3" style="display:none;">
                <label class="font-weight-bold mb-1" style="font-size:.85rem;">Group by (chart)</label>
                <select name="group_by" id="customGroupBy" class="form-control form-control-sm" style="max-width:260px;"></select>
              </div>

              {{-- Chart type --}}
              <div id="customChartTypeWrap" class="mb-3" style="display:none;">
                <label class="font-weight-bold mb-1" style="font-size:.85rem;">Chart Type</label>
                <div class="d-flex flex-wrap" style="gap:6px;">
                  <label class="chart-type-pill"><input type="radio" name="chart_type" value="bar" checked> <i class="fas fa-chart-bar mr-1"></i>Bar</label>
                  <label class="chart-type-pill"><input type="radio" name="chart_type" value="horizontalBar"> <i class="fas fa-align-left mr-1"></i>Horizontal</label>
                  <label class="chart-type-pill"><input type="radio" name="chart_type" value="line"> <i class="fas fa-chart-line mr-1"></i>Line</label>
                  <label class="chart-type-pill"><input type="radio" name="chart_type" value="pie"> <i class="fas fa-chart-pie mr-1"></i>Pie</label>
                  <label class="chart-type-pill"><input type="radio" name="chart_type" value="doughnut"> <i class="fas fa-circle mr-1"></i>Doughnut</label>
                </div>
              </div>

              <div class="d-flex align-items-center">
                <button type="submit" class="btn btn-sm mr-2" style="background:#a02626;border-color:#a02626;color:#fff;">
                  <i class="fas fa-table mr-1"></i>Generate Tabular Report
                </button>
                <button type="button" id="btnSelectAll" class="btn btn-sm btn-outline-secondary mr-1">All</button>
                <button type="button" id="btnClearAll"  class="btn btn-sm btn-outline-secondary">None</button>
              </div>
            </form>

          </div>{{-- /customBody --}}
        </div>
      </div>{{-- /custom-panel --}}

    </div>
  </section>
</div>
@endsection

@push('styles')
<style>
  .chart-type-pill {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 12px; border: 1px solid #ccc; border-radius: 20px;
    font-size: .8rem; cursor: pointer; user-select: none; transition: all .15s;
  }
  .chart-type-pill:has(input:checked) {
    background: #a02626; border-color: #a02626; color: #fff; font-weight: 600;
  }
  .chart-type-pill input { display: none; }
  .cat-tab.active-cat {
    background: #a02626 !important;
    border-color: #a02626 !important;
    color: #fff !important;
    font-weight: 600;
  }
  #vd-kpis .info-box    { min-height: 60px; }
  .vd-card              { border-radius: 6px; box-shadow: 0 1px 6px rgba(0,0,0,.08); background: #fff; }
  .vd-card-header       { padding: 8px 14px; border-bottom: 1px solid #f0f0f0; font-weight: 600; font-size: .87rem; color: #444; }
  .vd-card-body         { padding: 10px; }
  @@media print {
    .main-sidebar,.main-footer,.content-header,.cat-tab,#btnScrollCustom,#vd-fulllink,#btnPrintReport { display:none !important; }
    .content-wrapper { margin-left: 0 !important; }
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

  var charts     = {};
  var dtCountry  = null;
  var currentType = null;

  // ── Visual categories and their data/page URLs ──────────────────────────────
  var VISUAL = {
    fellows:    { dataUrl: '{{ url("admin/associates/fellows/reports/data") }}',    pageUrl: '{{ url("admin/associates/fellows/reports") }}',    title: 'Fellows Analytics' },
    trainees:   { dataUrl: '{{ url("admin/associates/trainees/reports/data") }}',   pageUrl: '{{ url("admin/associates/trainees/reports") }}',   title: 'Trainees Analytics' },
    candidates: { dataUrl: '{{ url("admin/associates/candidates/reports/data") }}', pageUrl: '{{ url("admin/associates/candidates/reports") }}', title: 'Candidates Analytics' }
  };

  // ── Chart helpers ────────────────────────────────────────────────────────────
  function destroyChart(key) {
    if (charts[key]) { charts[key].destroy(); charts[key] = null; }
  }
  function destroyAllCharts() {
    Object.keys(charts).forEach(destroyChart);
  }

  function mkBar(id, labels, values, opts) {
    opts = opts || {};
    var ctx = document.getElementById(id);
    if (!ctx) return;
    destroyChart(id);
    charts[id] = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{ label: opts.label || '', data: values,
          backgroundColor: PALETTE.slice(0, labels.length), borderRadius: 3 }]
      },
      options: { responsive: true, maintainAspectRatio: true,
        indexAxis: opts.horizontal ? 'y' : 'x',
        plugins: { legend: { display: false } },
        scales: {
          x: { beginAtZero: true, grid: { color: opts.horizontal ? '#f0f0f0' : 'transparent' }, ticks: { font: { size: 10 } } },
          y: { grid: { display: !opts.horizontal }, ticks: { font: { size: 10 } } }
        }
      }
    });
  }

  function mkDonut(id, labels, values) {
    var ctx = document.getElementById(id);
    if (!ctx) return;
    destroyChart(id);
    charts[id] = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: labels,
        datasets: [{ data: values, backgroundColor: PALETTE.slice(0, labels.length), borderWidth: 2, borderColor: '#fff' }]
      },
      options: { cutout: '60%', plugins: {
        legend: { position: 'bottom', labels: { padding: 5, boxWidth: 8, font: { size: 9 } } },
        tooltip: { callbacks: { label: function(c){ return ' '+c.label+': '+c.parsed.toLocaleString(); } } }
      }}
    });
  }

  function mkLine(id, labels, values, label) {
    var ctx = document.getElementById(id);
    if (!ctx) return;
    destroyChart(id);
    charts[id] = new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{ label: label || '', data: values,
          borderColor: BRAND, backgroundColor: 'rgba(160,38,38,0.08)',
          borderWidth: 2, fill: true, tension: 0.4, pointRadius: 3 }]
      },
      options: { responsive: true, maintainAspectRatio: true,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { display: false }, ticks: { font: { size: 10 } } },
          y: { beginAtZero: true, grid: { color: '#f0f0f0' }, ticks: { font: { size: 10 } } }
        }
      }
    });
  }

  function lbl(arr) { return arr.map(function(x){ return x.label; }); }
  function val(arr) { return arr.map(function(x){ return x.value; }); }

  function kpiBox(icon, text, value, color) {
    return '<div class="col-6 col-md-3 mb-2 px-1">' +
      '<div class="info-box shadow-sm mb-0">' +
      '<span class="info-box-icon" style="background:'+color+';font-size:1.1rem;width:55px;"><i class="fas fa-'+icon+'"></i></span>' +
      '<div class="info-box-content">' +
      '<span class="info-box-text" style="font-size:.72rem;">'+text+'</span>' +
      '<span class="info-box-number" style="font-size:1.35rem;font-weight:700;">'+value+'</span>' +
      '</div></div></div>';
  }

  function chartCard(id, title, icon, height) {
    return '<div class="vd-card mb-2"><div class="vd-card-header"><i class="fas fa-'+icon+' mr-1 text-muted"></i>'+title+'</div>' +
           '<div class="vd-card-body"><canvas id="'+id+'" height="'+(height||200)+'"></canvas></div></div>';
  }

  // ── Country table helper ─────────────────────────────────────────────────────
  function renderCountryTable(rows, cols) {
    if (dtCountry) { dtCountry.destroy(); dtCountry = null; $('#vd-table-buttons').empty(); }

    var head = '<tr><th style="width:36px;">#</th>';
    cols.forEach(function(c){ head += '<th>'+c.label+'</th>'; });
    head += '</tr>';
    $('#vd-table-head').html(head);

    var body = '';
    rows.forEach(function(r, i){
      body += '<tr><td>'+(i+1)+'</td>';
      cols.forEach(function(c){ body += '<td>'+(r[c.key] !== undefined ? r[c.key] : '—')+'</td>'; });
      body += '</tr>';
    });
    $('#vd-table-body').html(body);

    $('#vd-table-wrap').show();

    dtCountry = $('#vd-country-table').DataTable({
      destroy: true, paging: false, searching: false, info: false, order: [[2,'desc']],
      dom: 'Brt',
      buttons: [
        { extend:'excelHtml5', text:'<i class="fas fa-file-excel mr-1"></i>Excel', className:'btn btn-success btn-sm' },
        { extend:'pdfHtml5',   text:'<i class="fas fa-file-pdf mr-1"></i>PDF',   className:'btn btn-danger btn-sm', orientation:'landscape' },
        { extend:'csvHtml5',   text:'<i class="fas fa-file-csv mr-1"></i>CSV',   className:'btn btn-info btn-sm' }
      ]
    });
    dtCountry.buttons().container().appendTo('#vd-table-buttons');
  }

  // ── Per-category renderers ───────────────────────────────────────────────────
  function renderFellows(d) {
    // KPIs
    $('#vd-kpis').html(
      kpiBox('users',      'Total Fellows', d.kpi.total.toLocaleString(),  BRAND)      +
      kpiBox('user-check', 'Active',        d.kpi.active.toLocaleString(), '#28a745')  +
      kpiBox('mars',       'Male',          d.kpi.male.toLocaleString(),   '#17a2b8')  +
      kpiBox('venus',      'Female',        d.kpi.female.toLocaleString(), '#ffc107')
    ).show();

    // Chart grid
    $('#vd-charts').html(
      '<div class="row mb-2">' +
        '<div class="col-md-6 pr-1">'+chartCard('vdChartCountry','Fellows by Country (Top 15)','globe-africa',220)+'</div>' +
        '<div class="col-md-3 px-1">'+chartCard('vdChartType','Fellowship Type','id-badge',220)+'</div>' +
        '<div class="col-md-3 pl-1">'+chartCard('vdChartGender','Gender','venus-mars',220)+'</div>' +
      '</div>' +
      '<div class="row mb-2">' +
        '<div class="col-md-7 pr-1">'+chartCard('vdChartYear','Fellowship Year Trend','chart-line',160)+'</div>' +
        '<div class="col-md-5 pl-1">'+chartCard('vdChartSpecialty','Top Specialties','stethoscope',160)+'</div>' +
      '</div>'
    ).show();

    mkBar('vdChartCountry', lbl(d.byCountry), val(d.byCountry), { horizontal: true });
    mkDonut('vdChartType', lbl(d.byType), val(d.byType));
    mkDonut('vdChartGender', lbl(d.byGender), val(d.byGender));
    mkLine('vdChartYear', lbl(d.byYear), val(d.byYear));
    mkBar('vdChartSpecialty', lbl(d.bySpecialty), val(d.bySpecialty), { horizontal: true });

    renderCountryTable(d.countryTable, [
      {key:'country_name',label:'Country'},{key:'total',label:'Total'},
      {key:'male',label:'Male'},{key:'female',label:'Female'},{key:'active',label:'Active'}
    ]);
  }

  function renderTrainees(d) {
    $('#vd-kpis').html(
      kpiBox('users',      'Total Trainees', (d.total||0).toLocaleString(),    BRAND)      +
      kpiBox('user-check', 'Active',         (d.active||0).toLocaleString(),   '#28a745')  +
      kpiBox('mars',       'Male',           (d.male||0).toLocaleString(),     '#17a2b8')  +
      kpiBox('venus',      'Female',         (d.female||0).toLocaleString(),   '#ffc107')
    ).show();

    $('#vd-charts').html(
      '<div class="row mb-2">' +
        '<div class="col-md-6 pr-1">'+chartCard('vdChartCountry','Trainees by Country (Top 15)','globe-africa',220)+'</div>' +
        '<div class="col-md-3 px-1">'+chartCard('vdChartProgramme','By Programme','graduation-cap',220)+'</div>' +
        '<div class="col-md-3 pl-1">'+chartCard('vdChartGender','Gender','venus-mars',220)+'</div>' +
      '</div>' +
      '<div class="row mb-2">' +
        '<div class="col-md-7 pr-1">'+chartCard('vdChartYear','Admissions by Year','chart-line',160)+'</div>' +
        '<div class="col-md-5 pl-1">'+chartCard('vdChartStudyYear','Study Year Distribution','book-open',160)+'</div>' +
      '</div>'
    ).show();

    mkBar('vdChartCountry', lbl(d.byCountry), val(d.byCountry), { horizontal: true });
    if (d.byProgramme) mkDonut('vdChartProgramme', lbl(d.byProgramme), val(d.byProgramme));
    if (d.byGender) mkDonut('vdChartGender', lbl(d.byGender), val(d.byGender));
    if (d.byYear) mkLine('vdChartYear', lbl(d.byYear), val(d.byYear));
    if (d.byStudyYear) mkBar('vdChartStudyYear', lbl(d.byStudyYear), val(d.byStudyYear));

    if (d.countryTable) renderCountryTable(d.countryTable, [
      {key:'country_name',label:'Country'},{key:'total',label:'Total'},
      {key:'male',label:'Male'},{key:'female',label:'Female'},{key:'active',label:'Active'}
    ]);
  }

  function renderCandidates(d) {
    $('#vd-kpis').html(
      kpiBox('user-graduate', 'Total Candidates', d.kpi.total.toLocaleString(),       BRAND)     +
      kpiBox('check-circle',  'Fee Paid',         d.kpi.feePaidC.toLocaleString(),     '#28a745') +
      kpiBox('mars',          'Male',             d.kpi.male.toLocaleString(),          '#17a2b8') +
      kpiBox('venus',         'Female',           d.kpi.female.toLocaleString(),        '#ffc107')
    ).show();

    $('#vd-charts').html(
      '<div class="row mb-2">' +
        '<div class="col-md-6 pr-1">'+chartCard('vdChartCountry','Candidates by Country (Top 15)','globe-africa',220)+'</div>' +
        '<div class="col-md-3 px-1">'+chartCard('vdChartProgramme','Programme','stethoscope',220)+'</div>' +
        '<div class="col-md-3 pl-1">'+chartCard('vdChartGender','Gender','venus-mars',220)+'</div>' +
      '</div>' +
      '<div class="row mb-2">' +
        '<div class="col-md-7 pr-1">'+chartCard('vdChartYear','Candidates by Exam Year','chart-line',160)+'</div>' +
        '<div class="col-md-5 pl-1">'+chartCard('vdChartFeePaid','Fee Payment Status','dollar-sign',160)+'</div>' +
      '</div>'
    ).show();

    mkBar('vdChartCountry', lbl(d.byCountry), val(d.byCountry), { horizontal: true });
    mkDonut('vdChartProgramme', lbl(d.byProgramme), val(d.byProgramme));
    mkDonut('vdChartGender', lbl(d.byGender), val(d.byGender));
    mkLine('vdChartYear', lbl(d.byYear), val(d.byYear));
    if (d.byFeePaid) mkDonut('vdChartFeePaid', lbl(d.byFeePaid), val(d.byFeePaid));

    if (d.countryTable) renderCountryTable(d.countryTable, [
      {key:'country_name',label:'Country'},{key:'total',label:'Total'},
      {key:'male',label:'Male'},{key:'female',label:'Female'},{key:'fee_paid',label:'Fee Paid'}
    ]);
  }

  // ── Load visual dashboard ────────────────────────────────────────────────────
  function loadVisualDashboard(type) {
    var info = VISUAL[type];

    destroyAllCharts();
    if (dtCountry) { dtCountry.destroy(); dtCountry = null; $('#vd-table-buttons').empty(); }

    $('#vd-kpis').hide().empty();
    $('#vd-charts').hide().empty();
    $('#vd-table-wrap').hide();
    $('#vd-table-body').empty();
    $('#vd-error').hide();
    $('#vd-loading').show();
    $('#vd-title').text(info.title);
    $('#vd-fulllink').attr('href', info.pageUrl);
    $('#btnPrintReport').show();
    $('#vd-panel').show();

    fetch(info.dataUrl, { cache: 'no-store' })
      .then(function(r){ if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
      .then(function(d){
        $('#vd-loading').hide();
        if      (type === 'fellows')    renderFellows(d);
        else if (type === 'trainees')   renderTrainees(d);
        else if (type === 'candidates') renderCandidates(d);
      })
      .catch(function(err){
        $('#vd-loading').hide();
        $('#vd-error').text('Could not load analytics data: ' + err.message).show();
      });
  }

  // ── Load custom builder ──────────────────────────────────────────────────────
  function loadCustomBuilder(type, label) {
    $('#customPanelTitle').text('Custom Report — ' + label);
    $('#customPrompt').hide();
    $('#customForm').hide();
    $('#customLoading').show();
    $('#customType').val(type);

    fetch('{{ url("admin/reports/fields") }}/' + type)
      .then(function(r){ return r.json(); })
      .then(function(data){
        $('#customLoading').hide();

        // Fields
        var fhtml = '';
        Object.keys(data.fields).forEach(function(key){
          fhtml += '<div class="col-6 col-md-4 col-lg-3 mb-1">' +
            '<div class="custom-control custom-checkbox">' +
            '<input type="checkbox" class="custom-control-input" id="cf_'+key+'" name="fields[]" value="'+key+'" checked>' +
            '<label class="custom-control-label" for="cf_'+key+'">'+data.fields[key]+'</label>' +
            '</div></div>';
        });
        $('#customFields').html(fhtml);

        // Filters
        if (data.filters && Object.keys(data.filters).length) {
          var filterHtml = '';
          Object.keys(data.filters).forEach(function(fkey){
            var f = data.filters[fkey];
            var options = '<option value="">All</option>';
            Object.keys(f.options).forEach(function(v){
              options += '<option value="'+v+'">'+f.options[v]+'</option>';
            });
            filterHtml += '<div class="col-6 col-md-3 mb-2">' +
              '<label class="small mb-0 font-weight-bold">'+f.label+'</label>' +
              '<select name="filters['+fkey+']" class="form-control form-control-sm">'+options+'</select>' +
              '</div>';
          });
          $('#customFilters').html(filterHtml);
          $('#customFiltersWrap').show();
        } else {
          $('#customFiltersWrap').hide();
        }

        // Group-by
        if (data.group_by && data.group_by.length) {
          var gbOpts = '<option value="">— No chart —</option>';
          data.group_by.forEach(function(g){
            var label = data.fields[g] || g;
            gbOpts += '<option value="'+g+'">'+label+'</option>';
          });
          $('#customGroupBy').html(gbOpts);
          $('#customGroupWrap').show();
          $('#customChartTypeWrap').show();
        } else {
          $('#customGroupWrap').hide();
          $('#customChartTypeWrap').hide();
        }

        $('#customForm').show();
      })
      .catch(function(){
        $('#customLoading').hide();
        $('#customPrompt').text('Failed to load fields. Please try again.').show();
      });
  }

  // ── Select/None buttons ──────────────────────────────────────────────────────
  $('#btnSelectAll').on('click', function(){ $('#customFields input[type=checkbox]').prop('checked', true); });
  $('#btnClearAll').on('click',  function(){ $('#customFields input[type=checkbox]').prop('checked', false); });

  // Show/hide chart type when group-by selection changes
  $(document).on('change', '#customGroupBy', function(){
    if ($(this).val()) $('#customChartTypeWrap').show();
    else               $('#customChartTypeWrap').hide();
  });

  // ── Custom panel toggle ──────────────────────────────────────────────────────
  var customExpanded = true;
  $('#customToggleHeader').on('click', function(){
    customExpanded = !customExpanded;
    $('#customBody').slideToggle(180);
    $('#customToggleIcon').toggleClass('fa-chevron-down fa-chevron-up');
  });

  // ── Scroll to custom builder ─────────────────────────────────────────────────
  $('#btnScrollCustom').on('click', function(){
    if (!customExpanded) {
      customExpanded = true;
      $('#customBody').slideDown(180);
      $('#customToggleIcon').removeClass('fa-chevron-up').addClass('fa-chevron-down');
    }
    $('html,body').animate({ scrollTop: $('#custom-panel').offset().top - 70 }, 350);
  });

  // ── Print ────────────────────────────────────────────────────────────────────
  $('#btnPrintReport').on('click', function(){ window.print(); });

  // ── Category tab click ───────────────────────────────────────────────────────
  $('.cat-tab').on('click', function(){
    var type  = $(this).data('type');
    var label = $(this).text().trim();

    // Highlight active tab
    $('.cat-tab').removeClass('active-cat');
    $(this).addClass('active-cat');

    currentType = type;

    if (VISUAL[type]) {
      loadVisualDashboard(type);
    } else {
      // No visual dashboard — hide visual panel, show just the custom builder
      destroyAllCharts();
      $('#vd-panel').hide();
      $('#btnPrintReport').hide();
    }

    loadCustomBuilder(type, label);

    // Expand custom panel
    if (!customExpanded) {
      customExpanded = true;
      $('#customBody').slideDown(180);
      $('#customToggleIcon').removeClass('fa-chevron-up').addClass('fa-chevron-down');
    }
  });

  // ── Deep link: ?type=trainers pre-selects a category tab (e.g. linked
  // from that roster's own list page "Export to Excel" button) instead of
  // landing on the blank picker.
  var preselectType = new URLSearchParams(window.location.search).get('type');
  if (preselectType) {
    $('.cat-tab[data-type="' + preselectType + '"]').trigger('click');
  }

});
</script>
@endpush
