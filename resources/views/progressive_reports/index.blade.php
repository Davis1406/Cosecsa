@extends('layout.app')

@section('content')
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-6">
            <h1 style="font-size:1.4rem;">Progressive Reports</h1>
            <p class="text-muted mb-0" style="font-size:.85rem;">Monthly Secretariat progress reports — due on day {{ $settings->due_day }} of each month.</p>
          </div>
          <div class="col-sm-6 text-right">
            @if($canManage)
              <a href="{{ url('progressive-reports/templates') }}" class="btn btn-cosecsa-outline">
                <i class="fas fa-list-check mr-1"></i> Recurring Tasks
              </a>
              <a href="{{ url('progressive-reports/settings') }}" class="btn btn-cosecsa-outline">
                <i class="fas fa-cog mr-1"></i> Settings
              </a>
            @endif
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        @include('_message')

        @if($canManage && !$currentPeriod)
          <div class="card">
            <div class="card-body">
              <form method="POST" action="{{ url('progressive-reports/open') }}" class="form-inline">
                @csrf
                <label class="mr-2">Open a new report period for</label>
                <input type="month" name="period_month" class="form-control mr-2" value="{{ now()->format('Y-m') }}" required>
                <button type="submit" class="btn btn-cosecsa">Open Period</button>
              </form>
            </div>
          </div>
        @endif

        <div class="card">
          <div class="card-header"><h3 class="card-title">Report Periods</h3></div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-sm">
                <thead>
                  <tr><th>Month</th><th>Due Date</th><th>Submitted</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                  @foreach($periods as $p)
                    <tr>
                      <td>{{ $p->period_month->format('F Y') }}</td>
                      <td>{{ $p->due_date->format('d M Y') }}</td>
                      <td>{{ $p->submitted_count }} / {{ $p->total_participants }}</td>
                      <td>
                        <span class="badge {{ $p->status === 'consolidated' ? 'badge-success' : 'badge-secondary' }}">
                          {{ ucfirst($p->status) }}
                        </span>
                      </td>
                      <td>
                        <a href="{{ url('progressive-reports/'.$p->id) }}" class="btn btn-sm btn-cosecsa-outline">Open</a>
                      </td>
                    </tr>
                  @endforeach
                  @if($periods->isEmpty())
                    <tr><td colspan="5" class="text-center text-muted py-3">No report periods yet.</td></tr>
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
