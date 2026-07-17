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

              <div class="form-group" style="max-width:300px;">
                <label style="font-size:.85rem;">Group / Chart By</label>
                <select class="form-control form-control-sm" name="group_by">
                  <option value="">No chart — table only</option>
                  @foreach($groupByOptions as $key)
                    <option value="{{ $key }}" {{ $groupBy === $key ? 'selected' : '' }}>{{ $fields[$key] }}</option>
                  @endforeach
                </select>
              </div>

              <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-sync mr-1"></i> Update Report
              </button>
            </form>
          </div>
        </div>

        @if($chart && $chart->isNotEmpty())
        <div class="card">
          <div class="card-header"><h3 class="card-title">By {{ $fields[$groupBy] }}</h3></div>
          <div class="card-body">
            <div style="max-height:360px;">
              <canvas id="reportChart"></canvas>
            </div>
          </div>
        </div>
        @endif

        <div class="card">
          <div class="card-header"><h3 class="card-title">Data</h3></div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-sm">
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

@if($chart && $chart->isNotEmpty())
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  new Chart(document.getElementById('reportChart').getContext('2d'), {
    type: 'bar',
    data: {
      labels: {!! json_encode($chart->keys()) !!},
      datasets: [{
        label: 'Count',
        data: {!! json_encode($chart->values()) !!},
        backgroundColor: '#a02626'
      }]
    },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
  });
});
</script>
@endpush
@endif
