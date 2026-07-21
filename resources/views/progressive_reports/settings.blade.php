@extends('layout.app')

@section('content')
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-6">
            <h1 style="font-size:1.4rem;">Progressive Reports — Settings</h1>
          </div>
          <div class="col-sm-6 text-right">
            <a href="{{ url('progressive-reports') }}" class="btn btn-cosecsa-outline">
              <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        @include('_message')

        <div class="card">
          <div class="card-body">
            <form method="POST" action="{{ url('progressive-reports/settings') }}">
              @csrf
              <div class="form-group">
                <label>Due day of each month</label>
                <input type="number" name="due_day" class="form-control" style="max-width:150px;" min="1" max="28" value="{{ $settings->due_day }}" required>
                <small class="text-muted">Reports for a given month are due by this day of the following/current cycle.</small>
              </div>
              <div class="form-group">
                <label>Send reminder this many days before the due date</label>
                <input type="number" name="reminder_days_before" class="form-control" style="max-width:150px;" min="0" max="27" value="{{ $settings->reminder_days_before }}" required>
              </div>
              <div class="form-group form-check">
                <input type="checkbox" name="reminder_enabled" id="reminder_enabled" class="form-check-input" value="1" {{ $settings->reminder_enabled ? 'checked' : '' }}>
                <label class="form-check-label" for="reminder_enabled">Automated reminder emails enabled</label>
              </div>
              <button type="submit" class="btn btn-cosecsa">Save Settings</button>
            </form>
          </div>
        </div>
      </div>
    </section>
  </div>
@endsection
