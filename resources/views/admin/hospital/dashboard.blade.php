@extends('layout.app')

@section('content')
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-6">
            <h1 style="font-size:1.4rem;">Hospital Accreditation</h1>
            <p class="text-muted mb-0" style="font-size:.85rem;">Follow up on accreditations across all hospitals — flagged {{ $warningDays }} days before expiry.</p>
          </div>
          <div class="col-sm-6 text-right">
            <a href="{{ url('admin/hospital/list') }}" class="btn btn-cosecsa-outline"><i class="fas fa-hospital mr-1"></i> All Hospitals</a>
            <a href="{{ url('admin/hospitalprogrammes/list') }}" class="btn btn-cosecsa-outline"><i class="fas fa-list mr-1"></i> All Accreditations</a>
            <a href="{{ url('admin/hospitalprogrammes/add') }}" class="btn btn-cosecsa"><i class="fas fa-plus mr-1"></i> Accredit Programme</a>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        @include('_message')

        <div class="row">
          <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
              <div class="inner"><h3>{{ $totalHospitals }}</h3><p>Hospitals</p></div>
              <div class="icon"><i class="ion ion-medkit"></i></div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
              <div class="inner"><h3>{{ $countActive }}</h3><p>Active Accreditations</p></div>
              <div class="icon"><i class="ion ion-checkmark-circled"></i></div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box" style="background:#FEC503;color:#3a2a00;">
              <div class="inner"><h3>{{ $countExpiringSoon }}</h3><p>Expiring Soon</p></div>
              <div class="icon"><i class="ion ion-alert-circled"></i></div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
              <div class="inner"><h3>{{ $countExpired }}</h3><p>Expired</p></div>
              <div class="icon"><i class="ion ion-close-circled"></i></div>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-body">
            <form method="GET" action="{{ url('admin/hospital/dashboard') }}" class="form-row align-items-end">
              <div class="form-group col-md-3">
                <label>Country</label>
                <select name="country_id" class="form-control">
                  <option value="">All</option>
                  @foreach($countries as $c)
                    <option value="{{ $c->id }}" {{ ($filters['country_id'] ?? '') == $c->id ? 'selected' : '' }}>{{ $c->country_name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="form-group col-md-3">
                <label>Programme</label>
                <select name="programme_id" class="form-control">
                  <option value="">All</option>
                  @foreach($programmes as $p)
                    <option value="{{ $p->id }}" {{ ($filters['programme_id'] ?? '') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="form-group col-md-2">
                <label>Flag</label>
                <select name="flag" class="form-control">
                  <option value="">All</option>
                  <option value="active" {{ ($filters['flag'] ?? '') == 'active' ? 'selected' : '' }}>Active</option>
                  <option value="expiring_soon" {{ ($filters['flag'] ?? '') == 'expiring_soon' ? 'selected' : '' }}>Expiring Soon</option>
                  <option value="expired" {{ ($filters['flag'] ?? '') == 'expired' ? 'selected' : '' }}>Expired</option>
                </select>
              </div>
              <div class="form-group col-md-3">
                <label>Search Hospital</label>
                <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}">
              </div>
              <div class="form-group col-md-1">
                <button type="submit" class="btn btn-cosecsa-outline">Filter</button>
              </div>
            </form>
          </div>
        </div>

        <form method="POST" action="{{ url('admin/hospital/reminders/send-bulk') }}" id="bulkReminderForm">
          @csrf
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h3 class="card-title">Accreditations ({{ $rows->count() }})</h3>
              <button type="submit" class="btn btn-sm btn-cosecsa" onclick="return confirm('Send a reminder email to every checked accreditation\'s hospital contact?')">
                <i class="fas fa-paper-plane mr-1"></i> Send Reminders to Selected
              </button>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-striped table-sm mb-0">
                  <thead>
                    <tr>
                      <th style="width:3%;"><input type="checkbox" id="checkAll"></th>
                      <th>Hospital</th><th>Country</th><th>Programme</th><th>PD Contact</th>
                      <th>Accredited</th><th>Expiry</th><th>Status</th><th>Last Reminder</th><th></th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($rows as $r)
                      <tr>
                        <td>
                          @if(count($r->reminder_emails))
                            <input type="checkbox" name="hospital_programme_ids[]" value="{{ $r->id }}">
                          @endif
                        </td>
                        <td><a href="{{ url('admin/hospital/view_hospital/'.$r->hospital_id) }}">{{ $r->hospital_name }}</a></td>
                        <td>{{ $r->country_name ?: '—' }}</td>
                        <td>{{ $r->programme_name }}</td>
                        <td style="font-size:.8rem;">{{ $r->pd_names ?: 'No PD on file' }}</td>
                        <td>{{ \Carbon\Carbon::parse($r->accredited_date)->format('d M Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($r->expiry_date)->format('d M Y') }}</td>
                        <td>
                          @if($r->flag === 'expired')
                            <span class="badge badge-danger">Expired</span>
                          @elseif($r->flag === 'expiring_soon')
                            <span class="badge" style="background:#FEC503;color:#3a2a00;">Expiring Soon</span>
                          @else
                            <span class="badge badge-success">Active</span>
                          @endif
                        </td>
                        <td style="font-size:.8rem;">
                          {{ $r->last_reminder_sent_at ? \Carbon\Carbon::parse($r->last_reminder_sent_at)->diffForHumans() : '—' }}
                        </td>
                        <td>
                          <a href="{{ url('admin/hospitalprogrammes/edit/'.$r->id) }}" class="btn btn-sm btn-cosecsa-outline">Edit</a>
                          @if(count($r->reminder_emails))
                            <form method="POST" action="{{ url('admin/hospital/reminders/'.$r->id.'/send') }}" style="display:inline;">
                              @csrf
                              <button type="submit" class="btn btn-sm btn-cosecsa" style="background:#FEC503;border-color:#FEC503;color:#3a2a00;">Remind</button>
                            </form>
                          @endif
                        </td>
                      </tr>
                    @endforeach
                    @if($rows->isEmpty())
                      <tr><td colspan="9" class="text-center text-muted py-3">No accreditations match these filters.</td></tr>
                    @endif
                  </tbody>
                </table>
              </div>
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
  document.querySelectorAll('input[name="hospital_programme_ids[]"]').forEach(cb => cb.checked = this.checked);
});
</script>
@endpush
