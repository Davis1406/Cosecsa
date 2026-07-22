@extends('layout.app')

@section('content')
  <style>
    .accred-table a.hospital-link { color:#a02626; font-weight:600; }
    .accred-table a.hospital-link:hover { color:#841f1f; text-decoration:underline; }
    .accred-table .dropdown-menu { font-size:.82rem; min-width:180px; }
    .accred-table .dropdown-item { padding:.4rem .9rem; }
    .accred-table .dropdown-item i { width:16px; }
  </style>
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
                <table class="table table-striped table-sm mb-0 accred-table">
                  <thead>
                    <tr>
                      <th style="width:3%;"><input type="checkbox" id="checkAll"></th>
                      <th>Hospital</th><th>Country</th><th>Programme</th><th>PD Contact</th>
                      <th>Accredited</th><th>Expiry</th><th>Status</th><th></th>
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
                        <td><a href="{{ url('admin/hospital/view_hospital/'.$r->hospital_id) }}" class="hospital-link">{{ $r->hospital_name }}</a></td>
                        <td>{{ $r->country_name ?: '—' }}</td>
                        <td>{{ $r->programme_name }}</td>
                        <td style="font-size:.8rem;">
                          {{ $r->pd_names ?: 'No PD on file' }}
                          @if($r->pd_names && !$r->pd_is_specific)
                            <span class="text-muted d-block" style="font-size:.72rem;">(hospital-wide)</span>
                          @endif
                        </td>
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
                        <td>
                          <div class="dropdown">
                            <button type="button" class="btn btn-sm btn-cosecsa-outline" data-toggle="dropdown" aria-expanded="false">
                              <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                              <a class="dropdown-item" href="{{ url('admin/hospitalprogrammes/edit/'.$r->id) }}">
                                <i class="fas fa-edit mr-1"></i> Edit Accreditation
                              </a>
                              <a class="dropdown-item pd-modal-trigger" href="#" data-toggle="modal" data-target="#pdModal"
                                 data-hp-id="{{ $r->id }}"
                                 data-hospital="{{ $r->hospital_name }}"
                                 data-programme="{{ $r->programme_name }}"
                                 data-trainer-id="{{ $r->assigned_trainer_id }}"
                                 data-name="{{ $r->assigned_trainer_name }}"
                                 data-email="{{ $r->assigned_trainer_email }}"
                                 data-phone="{{ $r->assigned_trainer_phone }}"
                                 data-assistant-pd="{{ $r->assigned_trainer_assistant_pd }}"
                                 data-assistant-email="{{ $r->assigned_trainer_assistant_email }}">
                                <i class="fas fa-user-md mr-1"></i> {{ $r->assigned_trainer_id ? 'Edit' : 'Add' }} PD
                              </a>
                              @if(count($r->reminder_emails))
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ url('admin/hospital/reminders/'.$r->id.'/send') }}">
                                  @csrf
                                  <button type="submit" class="dropdown-item" style="color:#a05a00;">
                                    <i class="fas fa-paper-plane mr-1"></i> Send Reminder
                                  </button>
                                </form>
                              @endif
                            </div>
                          </div>
                        </td>
                      </tr>
                    @endforeach
                    @if($rows->isEmpty())
                      <tr><td colspan="8" class="text-center text-muted py-3">No accreditations match these filters.</td></tr>
                    @endif
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </form>

        <!-- Shared Edit/Add PD modal, populated per-row via JS -->
        <div class="modal fade" id="pdModal" tabindex="-1" role="dialog">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <form method="POST" id="pdModalForm" action="">
                @csrf
                <input type="hidden" name="trainer_id" id="pdTrainerId">
                <div class="modal-header" style="background:#a02626;color:#fff;">
                  <h5 class="modal-title" id="pdModalTitle">Programme Director</h5>
                  <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                  <p class="text-muted" id="pdModalSubtitle" style="font-size:.85rem;"></p>
                  <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="name" id="pdName" class="form-control" required>
                  </div>
                  <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" id="pdEmail" class="form-control" required>
                  </div>
                  <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" id="pdPhone" class="form-control">
                  </div>

                  <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="pdHasAssistant">
                    <label class="form-check-label" for="pdHasAssistant">This PD has an Assistant PD</label>
                  </div>
                  <div id="pdAssistantFields" style="display:none;">
                    <div class="form-group">
                      <label>Assistant PD Name</label>
                      <input type="text" name="assistant_pd" id="pdAssistantName" class="form-control">
                    </div>
                    <div class="form-group">
                      <label>Assistant PD Email</label>
                      <input type="email" name="assistant_email" id="pdAssistantEmail" class="form-control">
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="submit" class="btn btn-cosecsa">Save</button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
@endsection

@push('scripts')
<script>
document.getElementById('checkAll').addEventListener('change', function () {
  document.querySelectorAll('input[name="hospital_programme_ids[]"]').forEach(cb => cb.checked = this.checked);
});

document.querySelectorAll('.pd-modal-trigger').forEach(function (link) {
  link.addEventListener('click', function (e) {
    e.preventDefault();
    const d = this.dataset;
    document.getElementById('pdModalForm').action = "{{ url('admin/hospital/pd') }}/" + d.hpId + "/save";
    document.getElementById('pdModalTitle').textContent = (d.trainerId ? 'Edit' : 'Add') + ' Programme Director';
    document.getElementById('pdModalSubtitle').textContent = d.hospital + ' — ' + d.programme;
    document.getElementById('pdTrainerId').value = d.trainerId || '';
    document.getElementById('pdName').value = d.name || '';
    document.getElementById('pdEmail').value = d.email || '';
    document.getElementById('pdPhone').value = d.phone || '';

    const hasAssistant = !!(d.assistantPd || d.assistantEmail);
    document.getElementById('pdHasAssistant').checked = hasAssistant;
    document.getElementById('pdAssistantFields').style.display = hasAssistant ? 'block' : 'none';
    document.getElementById('pdAssistantName').value = d.assistantPd || '';
    document.getElementById('pdAssistantEmail').value = d.assistantEmail || '';
  });
});

document.getElementById('pdHasAssistant').addEventListener('change', function () {
  document.getElementById('pdAssistantFields').style.display = this.checked ? 'block' : 'none';
  if (!this.checked) {
    document.getElementById('pdAssistantName').value = '';
    document.getElementById('pdAssistantEmail').value = '';
  }
});
</script>
@endpush
