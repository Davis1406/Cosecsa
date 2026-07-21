@extends('layout.app')

@section('content')
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-6"><h1 style="font-size:1.4rem;">{{ $header_title }}</h1></div>
          <div class="col-sm-6 text-right">
            <a href="{{ url('admin/letters') }}" class="btn btn-cosecsa-outline"><i class="fas fa-arrow-left mr-1"></i> Back</a>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        @include('_message')

        <div class="card">
          <div class="card-header"><h3 class="card-title">Filter Recipients</h3></div>
          <div class="card-body">
            <form method="GET" action="{{ url('admin/letters/'.$template->id.'/recipients') }}" class="form-row align-items-end">
              <div class="form-group col-md-2">
                <label>Country</label>
                <select name="country_id" class="form-control">
                  <option value="">All</option>
                  @foreach($countries as $id => $name)
                    <option value="{{ $id }}" {{ ($filters['country_id'] ?? '') == $id ? 'selected' : '' }}>{{ $name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="form-group col-md-2">
                <label>Programme</label>
                <select name="programme_id" class="form-control">
                  <option value="">All</option>
                  @foreach($programmes as $id => $name)
                    <option value="{{ $id }}" {{ ($filters['programme_id'] ?? '') == $id ? 'selected' : '' }}>{{ $name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="form-group col-md-2">
                <label>Year</label>
                <input type="number" name="year" class="form-control" value="{{ $filters['year'] ?? '' }}" placeholder="e.g. 2026">
              </div>
              <div class="form-group col-md-3">
                <label>Search Name</label>
                <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}">
              </div>
              <div class="form-group col-md-2 form-check ml-2">
                <input type="checkbox" name="unsent_only" id="unsent_only" class="form-check-input" value="1" {{ !empty($filters['unsent_only']) ? 'checked' : '' }}>
                <label class="form-check-label" for="unsent_only">Not sent yet</label>
              </div>
              <div class="form-group col-md-1">
                <button type="submit" class="btn btn-cosecsa-outline">Filter</button>
              </div>
            </form>
          </div>
        </div>

        <form method="POST" action="{{ url('admin/letters/'.$template->id.'/dispatch') }}" id="dispatchForm">
          @csrf
          <input type="hidden" name="country_id" value="{{ $filters['country_id'] ?? '' }}">
          <input type="hidden" name="programme_id" value="{{ $filters['programme_id'] ?? '' }}">
          <input type="hidden" name="year" value="{{ $filters['year'] ?? '' }}">
          <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
          <input type="hidden" name="unsent_only" value="{{ !empty($filters['unsent_only']) ? '1' : '' }}">

          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h3 class="card-title">Recipients ({{ $recipients->count() }})</h3>
              <div>
                <label class="mr-2 mb-0" style="font-size:.85rem;">Letter date</label>
                <input type="date" name="letter_date" class="form-control d-inline-block" style="width:170px;" value="{{ now()->format('Y-m-d') }}">
              </div>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive" style="max-height:480px; overflow-y:auto;">
                <table class="table table-striped table-sm mb-0">
                  <thead>
                    <tr>
                      <th style="width:3%;"><input type="checkbox" id="checkAll"></th>
                      <th>Name</th><th>Email</th><th>Country</th><th>Programme</th><th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($recipients as $r)
                      <tr>
                        <td><input type="checkbox" name="recipient_ids[]" value="{{ $r->id }}" class="recip-check"></td>
                        <td>{{ $r->name }}</td>
                        <td>{{ $r->email ?: '—' }}</td>
                        <td>{{ $r->country ?: '—' }}</td>
                        <td>{{ $r->programme ?: '—' }}</td>
                        <td>
                          @if($r->already_sent)
                            <span class="badge badge-success">Already Sent</span>
                          @else
                            <span class="badge badge-secondary">Not Sent</span>
                          @endif
                        </td>
                      </tr>
                    @endforeach
                    @if($recipients->isEmpty())
                      <tr><td colspan="6" class="text-center text-muted py-3">No recipients match these filters.</td></tr>
                    @endif
                  </tbody>
                </table>
              </div>
            </div>
            <div class="card-footer text-right">
              <button type="submit" class="btn btn-cosecsa" onclick="return confirm('Dispatch this letter to the selected recipients?')">
                <i class="fas fa-paper-plane mr-1"></i> Dispatch Selected
              </button>
            </div>
          </div>
        </form>
      </div>
    </section>
  </div>
@endsection

@push('scripts')
<script>
document.getElementById('checkAll').addEventListener('change', function () {
  document.querySelectorAll('.recip-check').forEach(cb => cb.checked = this.checked);
});
</script>
@endpush
