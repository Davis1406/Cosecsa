@extends('layout.app')

@section('content')
  <style>
    .accred-table .dropdown-menu { font-size:.82rem; min-width:180px; }
    .accred-table .dropdown-item { padding:.4rem .9rem; }
    .accred-table .dropdown-item i { width:16px; }

    #hospHubTabs.nav-tabs .nav-link { color:#a02626; border-color:transparent; }
    #hospHubTabs.nav-tabs .nav-link:hover { color:#841f1f; border-color:#eee #eee #dee2e6; }
    #hospHubTabs.nav-tabs .nav-link.active { color:#fff; background:#a02626; border-color:#a02626 #a02626 #a02626; font-weight:600; }
    body.dark-mode #hospHubTabs.nav-tabs .nav-link { color:#e0a5a5 !important; }
    body.dark-mode #hospHubTabs.nav-tabs .nav-link.active { color:#fff !important; background:#a02626 !important; }

    .fchk-filter-wrap  { position:relative; display:inline-block; }
    .fchk-filter-panel { position:absolute; top:calc(100% + 4px); left:0; z-index:1055;
                        background:#fff; border:1px solid #ced4da; border-radius:6px;
                        min-width:200px; max-width:260px; padding:8px;
                        box-shadow:0 4px 12px rgba(0,0,0,.12); }
    .fchk-list  { max-height:220px; overflow-y:auto; }
    .fchk-item  { display:flex; align-items:center; gap:6px; padding:3px 2px;
                 font-size:.82rem; font-weight:normal; cursor:pointer; white-space:nowrap; margin:0; }
    .fchk-item:hover { background:#f8f0f0; border-radius:4px; }
    .fchk-item input[type="checkbox"] { margin:0; cursor:pointer; accent-color:#a02626; }
    .fchk-footer { display:flex; justify-content:space-between; border-top:1px solid #eee;
                  margin-top:6px; padding-top:5px; font-size:.78rem; }
    .fchk-footer a { color:#6c757d; }
    .fchk-footer a:hover { color:#a02626; text-decoration:none; }
    .fchk-filter-btn { white-space:nowrap; }
    body.dark-mode .fchk-filter-panel { background:#374151 !important; border-color:#4a5568 !important; }
    body.dark-mode .fchk-item { color:#e0e0e0 !important; }
    body.dark-mode .fchk-item:hover { background:#4a5568 !important; }
    body.dark-mode .fchk-footer { border-top-color:#4a5568 !important; }
    body.dark-mode .fchk-footer a { color:#9ca3af !important; }
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
            <a href="{{ url('admin/hospitalprogrammes/add') }}" class="btn btn-cosecsa"><i class="fas fa-plus mr-1"></i> Accredit Programme</a>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        @include('_message')

        <ul class="nav nav-tabs mb-3" id="hospHubTabs" role="tablist">
          <li class="nav-item">
            <a class="nav-link active" id="tab-followup-trigger" data-toggle="tab" href="#tab-followup" role="tab">
              <i class="fas fa-bell mr-1"></i> Follow Up
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="tab-hospitals-trigger" data-toggle="tab" href="#tab-hospitals" role="tab">
              <i class="fas fa-hospital mr-1"></i> All Hospitals
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="tab-accreditations-trigger" data-toggle="tab" href="#tab-accreditations" role="tab">
              <i class="fas fa-list mr-1"></i> All Accreditations
            </a>
          </li>
        </ul>

        <div class="tab-content" id="hospHubTabContent">
        <div class="tab-pane fade show active" id="tab-followup" role="tabpanel">

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

        @php
          $selectedCountryIds = array_map('strval', (array) ($filters['country_id'] ?? []));
          $selectedProgrammeIds = array_map('strval', (array) ($filters['programme_id'] ?? []));
          $selectedFlags = (array) ($filters['flag'] ?? []);
          $flagOptions = ['active' => 'Active', 'expiring_soon' => 'Expiring Soon', 'expired' => 'Expired'];
        @endphp
        <div class="card card-outline card-secondary mb-2 shadow-sm">
          <div class="card-body py-2">
            <form method="GET" action="{{ url('admin/hospital/dashboard') }}" id="followUpFilterForm" class="d-flex flex-wrap align-items-center" style="gap:.5rem;">
              <div class="fchk-filter-wrap">
                <button type="button" class="btn btn-sm btn-outline-secondary fchk-filter-btn" data-filter="fFilterCountry">
                  Country
                  <span class="badge badge-danger fchk-badge ml-1" style="{{ count($selectedCountryIds) ? '' : 'display:none;' }}font-size:.65rem;">{{ count($selectedCountryIds) }}</span>
                  <i class="fas fa-caret-down ml-1" style="font-size:.7rem;"></i>
                </button>
                <div class="fchk-filter-panel shadow" id="fFilterCountry-panel" style="display:none;">
                  <input type="text" class="form-control form-control-sm fchk-search mb-1" placeholder="Search…" autocomplete="off">
                  <div class="fchk-list">
                    @foreach($countries as $c)
                      <label class="fchk-item">
                        <input type="checkbox" name="country_id[]" value="{{ $c->id }}" {{ in_array((string) $c->id, $selectedCountryIds) ? 'checked' : '' }}>
                        {{ $c->country_name }}
                      </label>
                    @endforeach
                  </div>
                  <div class="fchk-footer">
                    <a href="#" class="fchk-select-all small">All</a>
                    <a href="#" class="fchk-clear small text-danger">Clear</a>
                  </div>
                </div>
              </div>

              <div class="fchk-filter-wrap">
                <button type="button" class="btn btn-sm btn-outline-secondary fchk-filter-btn" data-filter="fFilterProgramme">
                  Programme
                  <span class="badge badge-danger fchk-badge ml-1" style="{{ count($selectedProgrammeIds) ? '' : 'display:none;' }}font-size:.65rem;">{{ count($selectedProgrammeIds) }}</span>
                  <i class="fas fa-caret-down ml-1" style="font-size:.7rem;"></i>
                </button>
                <div class="fchk-filter-panel shadow" id="fFilterProgramme-panel" style="display:none;">
                  <input type="text" class="form-control form-control-sm fchk-search mb-1" placeholder="Search…" autocomplete="off">
                  <div class="fchk-list">
                    @foreach($programmes as $p)
                      <label class="fchk-item">
                        <input type="checkbox" name="programme_id[]" value="{{ $p->id }}" {{ in_array((string) $p->id, $selectedProgrammeIds) ? 'checked' : '' }}>
                        {{ $p->name }}
                      </label>
                    @endforeach
                  </div>
                  <div class="fchk-footer">
                    <a href="#" class="fchk-select-all small">All</a>
                    <a href="#" class="fchk-clear small text-danger">Clear</a>
                  </div>
                </div>
              </div>

              <div class="fchk-filter-wrap">
                <button type="button" class="btn btn-sm btn-outline-secondary fchk-filter-btn" data-filter="fFilterFlag">
                  Flag
                  <span class="badge badge-danger fchk-badge ml-1" style="{{ count($selectedFlags) ? '' : 'display:none;' }}font-size:.65rem;">{{ count($selectedFlags) }}</span>
                  <i class="fas fa-caret-down ml-1" style="font-size:.7rem;"></i>
                </button>
                <div class="fchk-filter-panel shadow" id="fFilterFlag-panel" style="display:none;">
                  <div class="fchk-list">
                    @foreach($flagOptions as $val => $label)
                      <label class="fchk-item">
                        <input type="checkbox" name="flag[]" value="{{ $val }}" {{ in_array($val, $selectedFlags) ? 'checked' : '' }}>
                        {{ $label }}
                      </label>
                    @endforeach
                  </div>
                  <div class="fchk-footer">
                    <a href="#" class="fchk-select-all small">All</a>
                    <a href="#" class="fchk-clear small text-danger">Clear</a>
                  </div>
                </div>
              </div>

              <input type="text" name="search" class="form-control form-control-sm" style="max-width:220px;" placeholder="Search hospital…" value="{{ $filters['search'] ?? '' }}">

              <button type="submit" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-filter mr-1"></i>Apply
              </button>
              <a href="{{ url('admin/hospital/dashboard') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-times mr-1"></i>Clear All
              </a>
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
                        <td><a href="{{ url('admin/hospital/view_hospital/'.$r->hospital_id) }}" class="entity-link">{{ $r->hospital_name }}</a></td>
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

        </div>
        {{-- /#tab-followup --}}

        <div class="tab-pane fade" id="tab-hospitals" role="tabpanel">
          @include('admin.hospital._list_content', $hospListData)
        </div>

        <div class="tab-pane fade" id="tab-accreditations" role="tabpanel">
          @include('admin.hospitalprogrammes._list_content', $hpListData)
        </div>

        </div>
        {{-- /.tab-content --}}

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

// The "All Hospitals" / "All Accreditations" tables are initialized while
// their tab-pane is still hidden (display:none), so DataTables can get the
// column widths wrong until the pane is actually shown — recalculate once
// each tab becomes visible.
$('#hospHubTabs a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
  var target = $(e.target).attr('href');
  $(target).find('table').each(function () {
    if ($.fn.DataTable.isDataTable(this)) {
      $(this).DataTable().columns.adjust().responsive.recalc();
    }
  });
});

// Follow Up filter panel — same checkbox-dropdown look as the other two
// tabs, but submits the existing GET filter form (server-side) since this
// table already needs a full query for the flag/reminder logic.
$(document).on('click', '.fchk-filter-btn', function (e) {
  e.stopPropagation();
  var filterId = $(this).data('filter');
  var $panel = $('#' + filterId + '-panel');
  $('.fchk-filter-panel').not($panel).hide();
  $panel.toggle();
});
$(document).on('click', '.fchk-filter-panel', function (e) { e.stopPropagation(); });
$(document).on('click', function () { $('.fchk-filter-panel').hide(); });
$(document).on('input', '.fchk-search', function () {
  var q = $(this).val().toLowerCase();
  $(this).closest('.fchk-filter-panel').find('.fchk-item').each(function () {
    $(this).toggle($(this).text().toLowerCase().indexOf(q) !== -1);
  });
});
$(document).on('click', '.fchk-select-all', function (e) {
  e.preventDefault();
  $(this).closest('.fchk-filter-panel').find('.fchk-item:visible input[type="checkbox"]').prop('checked', true);
});
$(document).on('click', '.fchk-clear', function (e) {
  e.preventDefault();
  $(this).closest('.fchk-filter-panel').find('input[type="checkbox"]').prop('checked', false);
});
</script>
@endpush
