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
