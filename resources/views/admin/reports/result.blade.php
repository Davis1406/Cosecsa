@extends('layout.app')

@section('content')
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-6">
            <h1 style="font-size:1.4rem;">{{ $typeLabel }} Report</h1>
            <p class="text-muted mb-0" style="font-size:.85rem;">{{ $rows->count() }} record(s)</p>
          </div>
          <div class="col-sm-6 text-right">
            <a href="{{ url('admin/reports') }}" class="btn btn-secondary">
              <i class="fas fa-arrow-left mr-1"></i> New Report
            </a>
            <form method="get" action="{{ url('admin/reports/export') }}" style="display:inline;">
              <input type="hidden" name="type" value="{{ $type }}">
              <input type="hidden" name="fields" value="{{ implode(',', $selectedFields) }}">
              @foreach($filters as $k => $v)
                <input type="hidden" name="filters[{{ $k }}]" value="{{ $v }}">
              @endforeach
              <button type="submit" class="btn btn-success">
                <i class="fas fa-file-excel mr-1"></i> Export to Excel
              </button>
            </form>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        @include('_message')

        <div class="card">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-sliders-h mr-1"></i> Adjust This Report</h3>
          </div>
          <div class="card-body">
            <form method="post" action="{{ url('admin/reports/generate') }}">
              @csrf
              <input type="hidden" name="type" value="{{ $type }}">

              <div class="form-group">
                <label style="font-size:.85rem;">Fields Shown</label>
                <div class="border rounded p-2" style="columns:3; font-size:.85rem;">
                  @foreach($fields as $key => $label)
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="fields[]" value="{{ $key }}"
                             id="rf_{{ $key }}" {{ in_array($key, $selectedFields) ? 'checked' : '' }}>
                      <label class="form-check-label" for="rf_{{ $key }}">{{ $label }}</label>
                    </div>
                  @endforeach
                </div>
              </div>

              @if(count($filterDefs))
                <div class="form-group">
                  <label style="font-size:.85rem;">Filters</label>
                  <div class="form-row">
                    @foreach($filterDefs as $key => $def)
                      <div class="form-group col-md-3">
                        <label style="font-size:.78rem;" class="text-muted">{{ $def['label'] }}</label>
                        <select class="form-control form-control-sm" name="filters[{{ $key }}]">
                          <option value="">All</option>
                          @foreach($def['options'] as $val => $optLabel)
                            <option value="{{ $val }}" {{ (string)($filters[$key] ?? '') === (string)$val ? 'selected' : '' }}>{{ $optLabel }}</option>
                          @endforeach
                        </select>
                      </div>
                    @endforeach
                  </div>
                </div>
              @endif

              <div class="form-row">
                <div class="form-group col-md-4">
                  <label style="font-size:.85rem;">Group / Chart By</label>
                  <select class="form-control form-control-sm" name="group_by" id="adjustGroupBy">
                    <option value="">No chart — table only</option>
                    @foreach($groupByOptions as $key)
                      <option value="{{ $key }}" {{ $groupBy === $key ? 'selected' : '' }}>{{ $fields[$key] }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="form-group col-md-8" id="adjustChartTypeWrap" style="{{ $groupBy ? '' : 'display:none;' }}">
                  <label style="font-size:.85rem;">Chart Type</label>
                  <div class="d-flex flex-wrap" style="gap:6px; padding-top:2px;">
                    @foreach(['bar'=>'fa-chart-bar Bar','horizontalBar'=>'fa-align-left Horizontal','line'=>'fa-chart-line Line','pie'=>'fa-chart-pie Pie','doughnut'=>'fa-circle Doughnut'] as $ct => $info)
                      @php [$icon,$ctLabel] = explode(' ',$info,2); @endphp
                      <label class="chart-type-pill">
                        <input type="radio" name="chart_type" value="{{ $ct }}" {{ ($chartType ?? 'bar') === $ct ? 'checked' : '' }}>
                        <i class="fas {{ $icon }} mr-1"></i>{{ $ctLabel }}
                      </label>
                    @endforeach
                  </div>
                </div>
              </div>

              <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-sync mr-1"></i> Update Report
              </button>
            </form>
          </div>
        </div>

        @if($chart && $chart->isNotEmpty())
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">By {{ $fields[$groupBy] }}</h3>
            <button type="button" id="btnSaveChart" class="btn btn-sm btn-outline-secondary">
              <i class="fas fa-image mr-1"></i> Save as PNG
            </button>
          </div>
          <div class="card-body">
            <div style="position:relative; height:{{ in_array($chartType, ['pie','doughnut']) ? '340px' : '360px' }};">
              <canvas id="reportChart"></canvas>
            </div>
          </div>
        </div>
        @endif

        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title mb-0">Data</h3>
            <div id="reportTableButtons"></div>
          </div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table id="reportDataTable" class="table table-striped table-sm" style="width:100%;">
                <thead>
                  <tr>
                    <th>#</th>
                    @foreach($selectedFields as $f)
                      <th>{{ $fields[$f] }}</th>
                    @endforeach
                  </tr>
                </thead>
                <tbody>
                  @foreach($rows as $row)
                    <tr>
                      <td>{{ $loop->iteration }}</td>
                      @foreach($selectedFields as $f)
                        <td>{{ $row->{$f} ?? '—' }}</td>
                      @endforeach
                    </tr>
                  @endforeach
                  @if($rows->isEmpty())
                    <tr><td colspan="{{ count($selectedFields) + 1 }}" class="text-center text-muted py-3">No records matched.</td></tr>
                  @endif
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

  // DataTable with export buttons
  var dt = $('#reportDataTable').DataTable({
    pageLength: 25,
    dom: 'Bfrtip',
    buttons: [
      { extend: 'excelHtml5', text: '<i class="fas fa-file-excel mr-1"></i>Excel', className: 'btn btn-success btn-sm',
        title: '{{ addslashes($typeLabel) }} Report' },
      { extend: 'csvHtml5',   text: '<i class="fas fa-file-csv mr-1"></i>CSV',   className: 'btn btn-info btn-sm' },
      { extend: 'pdfHtml5',   text: '<i class="fas fa-file-pdf mr-1"></i>PDF',   className: 'btn btn-danger btn-sm',
        orientation: 'landscape', title: '{{ addslashes($typeLabel) }} Report' }
    ]
  });
  dt.buttons().container().appendTo('#reportTableButtons');

@if($chart && $chart->isNotEmpty())
  var chartType = '{{ $chartType ?? "bar" }}';
  var isHorizontal = chartType === 'horizontalBar';
  var isPolar = (chartType === 'pie' || chartType === 'doughnut');
  var PALETTE = ['#a02626','#2980b9','#27ae60','#f39c12','#8e44ad','#16a085',
                 '#d35400','#2c3e50','#c0392b','#1abc9c','#e74c3c','#f1c40f',
                 '#7f8c8d','#2ecc71','#e67e22'];
  var count = {!! $chart->count() !!};

  var reportChart = new Chart(document.getElementById('reportChart').getContext('2d'), {
    type: isHorizontal ? 'bar' : chartType,
    data: {
      labels: {!! json_encode($chart->keys()) !!},
      datasets: [{
        label: 'Count',
        data: {!! json_encode($chart->values()) !!},
        backgroundColor: isPolar ? PALETTE.slice(0, count) : '#a02626',
        borderColor:     isPolar ? '#fff' : '#a02626',
        borderWidth:     isPolar ? 2 : 0,
        borderRadius:    (!isPolar && !isHorizontal) ? 3 : 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      indexAxis: isHorizontal ? 'y' : 'x',
      plugins: {
        legend: { display: isPolar, position: 'bottom' }
      },
      scales: isPolar ? {} : {
        x: { beginAtZero: true, grid: { color: isHorizontal ? '#f0f0f0' : 'transparent' }, ticks: { font: { size: 11 } } },
        y: { grid: { display: isHorizontal }, ticks: { font: { size: 11 } } }
      }
    }
  });

  // Save chart as PNG
  document.getElementById('btnSaveChart').addEventListener('click', function () {
    var link = document.createElement('a');
    link.download = 'report-chart.png';
    link.href = document.getElementById('reportChart').toDataURL('image/png');
    link.click();
  });
@endif

  // Show/hide chart type pills when group-by changes
  document.getElementById('adjustGroupBy').addEventListener('change', function () {
    var wrap = document.getElementById('adjustChartTypeWrap');
    if (wrap) wrap.style.display = this.value ? '' : 'none';
  });

});
</script>
@endpush

@push('styles')
<style>
  .chart-type-pill {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 12px; border: 1px solid #ccc; border-radius: 20px;
    font-size: .8rem; cursor: pointer; user-select: none; transition: all .15s; margin: 0;
  }
  .chart-type-pill:has(input:checked) {
    background: #a02626; border-color: #a02626; color: #fff; font-weight: 600;
  }
  .chart-type-pill input { display: none; }
</style>
@endpush
