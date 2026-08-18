@extends('layout.app')

@section('content')
  <style>
    .accred-table .action-btn { padding:2px 8px; line-height:1.4; border-radius:4px; }
    .accred-table .action-btn:hover { background-color:#f0f0f0; }
    .accred-table .dropdown-menu { min-width:190px; font-size:.875rem; }
    .accred-table .dropdown-item { padding:6px 14px; }
    .accred-table .dropdown-item:hover { background-color:#f8f0f0; }
    body.dark-mode .accred-table .action-btn:hover { background-color:#4a5568 !important; }
    body.dark-mode .accred-table .dropdown-item:hover { background-color:#4a5568 !important; color:#fff !important; }

    #hospHubTabs.nav-tabs .nav-link { color:#a02626; border-color:transparent; font-size:.82rem; padding:.35rem .85rem; }
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
    .tile-clickable { cursor:pointer; transition:transform .15s, box-shadow .15s, outline .15s; }
    .tile-clickable:hover { transform:translateY(-2px); box-shadow:0 6px 18px rgba(0,0,0,.22); }
    .tile-clickable.tile-active { outline:3px solid rgba(255,255,255,.85); outline-offset:2px; transform:translateY(-2px); }
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

        <div class="row mb-3">
          <div class="col-lg-3 col-6 mb-3">
            <div class="stitch-tile stitch-tile-teal tile-clickable" data-tile="hospitals" title="Click to view all hospitals">
              <div class="stitch-tile-label">Hospitals <i class="fas fa-mouse-pointer ml-1" style="font-size:.65rem;opacity:.6;"></i></div>
              <div class="stitch-tile-value">{{ $totalHospitals }}</div>
              <div class="stitch-tile-bar"><div class="stitch-tile-fill" style="width:100%"></div></div>
            </div>
          </div>
          <div class="col-lg-3 col-6 mb-3">
            <div class="stitch-tile stitch-tile-green tile-clickable" data-tile="active" title="Click to filter active accreditations">
              <div class="stitch-tile-label">Active Accreditations <i class="fas fa-mouse-pointer ml-1" style="font-size:.65rem;opacity:.6;"></i></div>
              <div class="stitch-tile-value">{{ $countActive }}</div>
              <div class="stitch-tile-bar"><div class="stitch-tile-fill" style="width:{{ $totalHospitals > 0 ? round($countActive/$totalHospitals*100) : 0 }}%"></div></div>
            </div>
          </div>
          <div class="col-lg-3 col-6 mb-3">
            <div class="stitch-tile stitch-tile-gold tile-clickable" data-tile="expiring_soon" title="Click to filter expiring soon">
              <div class="stitch-tile-label">Expiring Soon <i class="fas fa-mouse-pointer ml-1" style="font-size:.65rem;opacity:.6;"></i></div>
              <div class="stitch-tile-value">{{ $countExpiringSoon }}</div>
              <div class="stitch-tile-bar"><div class="stitch-tile-fill" style="width:{{ $totalHospitals > 0 ? round($countExpiringSoon/$totalHospitals*100) : 0 }}%"></div></div>
            </div>
          </div>
          <div class="col-lg-3 col-6 mb-3">
            <div class="stitch-tile stitch-tile-maroon tile-clickable" data-tile="expired" title="Click to filter expired">
              <div class="stitch-tile-label">Expired <i class="fas fa-mouse-pointer ml-1" style="font-size:.65rem;opacity:.6;"></i></div>
              <div class="stitch-tile-value">{{ $countExpired }}</div>
              <div class="stitch-tile-bar"><div class="stitch-tile-fill" style="width:{{ $totalHospitals > 0 ? round($countExpired/$totalHospitals*100) : 0 }}%"></div></div>
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
                <table id="followUpTable" class="table table-striped table-sm mb-0 accred-table">
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
                          <button type="button" class="btn btn-xs toggle-status-btn ml-1
                            @if($r->flag === 'expired') btn-outline-success @else btn-outline-danger @endif"
                            data-hp-id="{{ $r->id }}"
                            data-hospital-id="{{ $r->hospital_id }}"
                            data-programme-id="{{ $r->programme_id }}"
                            data-hospital="{{ $r->hospital_name }}"
                            data-programme="{{ $r->programme_name }}"
                            data-accredited="{{ $r->accredited_date }}"
                            data-expiry="{{ $r->expiry_date }}"
                            data-flag="{{ $r->flag }}"
                            title="@if($r->flag === 'expired') Activate accreditation @else Mark as expired @endif">
                            <i class="fas fa-sync-alt"></i>
                          </button>
                        </td>
                        <td>
                          <div class="dropdown">
                            <button class="btn btn-sm btn-light border dropdown-toggle action-btn"
                                    type="button" data-toggle="dropdown"
                                    aria-haspopup="true" aria-expanded="false">
                              <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right shadow-sm">
                              <a class="dropdown-item" href="{{ url('admin/hospitalprogrammes/edit/'.$r->id) }}">
                                <i class="fas fa-edit text-warning mr-2"></i> Edit Accreditation
                              </a>
                              <a class="dropdown-item pd-modal-trigger" href="#" data-toggle="modal" data-target="#pdModal"
                                 data-hp-id="{{ $r->id }}"
                                 data-hospital="{{ $r->hospital_name }}"
                                 data-programme="{{ $r->programme_name }}"
                                 data-pd-id="{{ $r->assigned_pd_id }}"
                                 data-name="{{ $r->assigned_pd_name }}"
                                 data-email="{{ $r->assigned_pd_email }}"
                                 data-phone="{{ $r->assigned_pd_phone }}"
                                 data-assistant-pd="{{ $r->assigned_pd_assistant_pd }}"
                                 data-assistant-email="{{ $r->assigned_pd_assistant_email }}">
                                <i class="fas fa-user-md text-primary mr-2"></i> {{ $r->assigned_pd_id ? 'Edit' : 'Add' }} PD
                              </a>
                              @if(count($r->reminder_emails))
                                <div class="dropdown-divider"></div>
                                <form method="POST" action="{{ url('admin/hospital/reminders/'.$r->id.'/send') }}">
                                  @csrf
                                  <button type="submit" class="dropdown-item">
                                    <i class="fas fa-paper-plane mr-2" style="color:#a05a00;"></i> Send Reminder
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
                <input type="hidden" name="programme_director_id" id="pdId">
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

        <!-- Toggle Status modal -->
        <div class="modal fade" id="toggleStatusModal" tabindex="-1" role="dialog">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <form method="POST" action="{{ url('admin/hospital/reaccredit') }}" id="toggleStatusForm">
                @csrf
                <input type="hidden" name="hospital_programme_id" id="toggleHpId">
                <input type="hidden" name="hospital_id" id="toggleHospitalId">
                <div class="modal-header" id="toggleModalHeader" style="background:#a02626;color:#fff;">
                  <h5 class="modal-title" id="toggleModalTitle">Set Accreditation Duration</h5>
                  <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                  <p class="text-muted" id="toggleModalSubtitle" style="font-size:.85rem;"></p>

                  <div class="row mb-3">
                    <div class="col-6">
                      <small class="text-muted d-block">Currently Accredited</small>
                      <strong id="toggleAccreditedDisplay">—</strong>
                    </div>
                    <div class="col-6">
                      <small class="text-muted d-block">Current Expiry</small>
                      <strong id="toggleExpiryDisplay">—</strong>
                    </div>
                  </div>

                  <div id="toggleDeactivateMsg" style="display:none;" class="alert alert-warning py-2 mb-3">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    This will mark the accreditation as <strong>Expired</strong>.
                  </div>

                  <div id="toggleActivateFields">
                    <div class="form-group">
                      <label>Programmes being (re)accredited</label>
                      <div id="toggleProgrammeList" class="border rounded p-2" style="max-height:160px;overflow-y:auto;">
                        <small class="text-muted">Loading…</small>
                      </div>
                      <small class="form-text text-muted">Defaults to the programme you clicked — check any others being renewed in the same cycle.</small>
                    </div>
                    <div class="form-row">
                      <div class="form-group col-md-6">
                        <label>Reaccreditation Month</label>
                        <select name="month" id="toggleMonth" class="form-control">
                          @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $i => $m)
                            <option value="{{ $i + 1 }}">{{ $m }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="form-group col-md-6">
                        <label>Reaccreditation Year</label>
                        <select name="year" id="toggleYear" class="form-control">
                          @foreach(range(date('Y') - 1, date('Y') + 10) as $y)
                            <option value="{{ $y }}">{{ $y }}</option>
                          @endforeach
                        </select>
                      </div>
                    </div>
                    <div class="form-group">
                      <label>Duration</label>
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="duration_mode" id="toggleDurationStandard" value="standard" checked>
                        <label class="form-check-label" for="toggleDurationStandard">Standard — 5 years</label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input" type="radio" name="duration_mode" id="toggleDurationCustom" value="custom">
                        <label class="form-check-label" for="toggleDurationCustom">Custom — conditional accreditation, shorter than 5 years</label>
                      </div>
                      <div id="toggleCustomMonthsWrap" class="mt-2" style="display:none;">
                        <label class="small mb-1">Number of months (1–59)</label>
                        <input type="number" name="duration_months" id="toggleCustomMonths" class="form-control" min="1" max="59" value="6">
                      </div>
                    </div>
                    <div class="form-group">
                      <label>New Expiry Date</label>
                      <input type="text" id="toggleNewExpiry" class="form-control" readonly style="background:#f8f9fa;">
                      <small class="form-text text-muted" id="toggleExpiryNote"></small>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                  <button type="submit" class="btn btn-cosecsa" id="toggleSubmitBtn">Activate</button>
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
$(function () {
  var followUpDt = $('#followUpTable').DataTable({
    pageLength: 25,
    lengthMenu: [10, 25, 50, 100],
    order: [],
    columnDefs: [
      { orderable: false, targets: [0, 8] }
    ],
    language: { search: '', searchPlaceholder: 'Search…' },
    initComplete: function () { $('#followUpTable').css('opacity', 1); }
  });

  // Tile click → instant filter (no page reload)
  $('.tile-clickable').on('click', function () {
    var tile = $(this).data('tile');

    $('.tile-clickable').removeClass('tile-active');
    $(this).addClass('tile-active');

    if (tile === 'hospitals') {
      // Switch to the All Hospitals tab
      $('#tab-hospitals-trigger').tab('show');
      return;
    }

    var searchTerm = tile === 'active'       ? 'Active'
                   : tile === 'expiring_soon' ? 'Expiring'
                   : tile === 'expired'       ? 'Expired'
                   : '';

    followUpDt.column(7).search(searchTerm).draw();

    // Scroll to table
    $('html, body').animate({ scrollTop: $('#followUpTable').closest('.card').offset().top - 80 }, 300);
  });
});

document.getElementById('checkAll').addEventListener('change', function () {
  document.querySelectorAll('input[name="hospital_programme_ids[]"]').forEach(cb => cb.checked = this.checked);
});

document.querySelectorAll('.pd-modal-trigger').forEach(function (link) {
  link.addEventListener('click', function (e) {
    e.preventDefault();
    const d = this.dataset;
    document.getElementById('pdModalForm').action = "{{ url('admin/hospital/pd') }}/" + d.hpId + "/save";
    document.getElementById('pdModalTitle').textContent = (d.pdId ? 'Edit' : 'Add') + ' Programme Director';
    document.getElementById('pdModalSubtitle').textContent = d.hospital + ' — ' + d.programme;
    document.getElementById('pdId').value = d.pdId || '';
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

// Toggle Status modal
var toggleMonthNames = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
var STANDARD_CYCLE_MONTHS = 60; // 5 years — keep in sync with HospitalController::STANDARD_CYCLE_MONTHS

// The month/year picked is the REACCREDITATION date (accredited_date) —
// expiry is that date plus either the standard 5-year cycle or a shorter
// custom duration (months), computed here just for display.
function calcToggleExpiry() {
  var m = parseInt($('#toggleMonth').val());
  var y = parseInt($('#toggleYear').val());
  var isCustom = $('#toggleDurationCustom').is(':checked');
  var months = isCustom ? parseInt($('#toggleCustomMonths').val()) : STANDARD_CYCLE_MONTHS;

  if (!m || !y || !months || months < 1) {
    $('#toggleNewExpiry').val('');
    $('#toggleExpiryNote').text('Select the month, year, and duration for this reaccreditation.');
    return;
  }

  var total = (m - 1) + months;
  var expiryYear = y + Math.floor(total / 12);
  var expiryMonth = (total % 12) + 1;
  var lastDay = new Date(expiryYear, expiryMonth, 0).getDate();
  var mm = String(expiryMonth).padStart(2, '0');
  var dd = String(lastDay).padStart(2, '0');
  $('#toggleNewExpiry').val(expiryYear + '-' + mm + '-' + dd);

  var cycleLabel = isCustom ? (months + '-month conditional term') : '5-year standard cycle';
  $('#toggleExpiryNote').text('Accredited ' + toggleMonthNames[m - 1] + ' ' + y + ' — expires ' + lastDay + ' ' + toggleMonthNames[expiryMonth - 1] + ' ' + expiryYear + ' (' + cycleLabel + ').');
}

function formatMmYy(dateStr) {
  if (!dateStr) return '—';
  var d = new Date(dateStr);
  return toggleMonthNames[d.getMonth()] + ' ' + d.getFullYear();
}

$('#toggleMonth, #toggleYear, #toggleCustomMonths').on('change input', calcToggleExpiry);
$('input[name="duration_mode"]').on('change', function () {
  $('#toggleCustomMonthsWrap').toggle($('#toggleDurationCustom').is(':checked'));
  calcToggleExpiry();
});

function renderProgrammeChecklist(programmes, checkedProgrammeId) {
  var $list = $('#toggleProgrammeList');
  if (!programmes.length) {
    $list.html('<small class="text-muted">No other accredited programmes at this hospital.</small>');
    return;
  }
  var html = '';
  programmes.forEach(function (p) {
    var checked = (String(p.programme_id) === String(checkedProgrammeId)) ? 'checked' : '';
    html += '<div class="form-check">'
          + '<input class="form-check-input" type="checkbox" name="programme_id[]" value="' + p.programme_id + '" id="tp-' + p.programme_id + '" ' + checked + '>'
          + '<label class="form-check-label" for="tp-' + p.programme_id + '">' + p.programme_name + '</label>'
          + '</div>';
  });
  $list.html(html);
}

$(document).on('click', '.toggle-status-btn', function () {
  var btn = $(this);
  var hpId = btn.data('hpId');
  var hospitalId = btn.data('hospitalId');
  var programmeId = btn.data('programmeId');
  var hospital = btn.data('hospital');
  var programme = btn.data('programme');
  var accredited = btn.data('accredited');
  var expiry = btn.data('expiry');
  var flag = btn.data('flag');
  var isExpired = (flag === 'expired');

  $('#toggleHpId').val(hpId);
  $('#toggleHospitalId').val(hospitalId);
  $('#toggleModalSubtitle').text(hospital + ' — ' + programme);
  $('#toggleAccreditedDisplay').text(formatMmYy(accredited));
  $('#toggleExpiryDisplay').text(formatMmYy(expiry));

  if (isExpired) {
    $('#toggleStatusForm').attr('action', "{{ url('admin/hospital/reaccredit') }}");
    $('#toggleActivateFields').show();
    $('#toggleDeactivateMsg').hide();
    $('#toggleModalHeader').css('background', '#28a745');
    $('#toggleModalTitle').text('Activate / Reaccredit');
    $('#toggleSubmitBtn').text('Activate').removeClass('btn-danger').addClass('btn-cosecsa');

    // Default: today's month/year — this is when the reaccreditation
    // is being recorded, not when it will expire. Duration resets to
    // Standard each time the modal opens.
    var def = new Date();
    $('#toggleMonth').val(def.getMonth() + 1);
    $('#toggleYear').val(def.getFullYear());
    $('#toggleDurationStandard').prop('checked', true);
    $('#toggleCustomMonthsWrap').hide();
    calcToggleExpiry();

    $('#toggleProgrammeList').html('<small class="text-muted">Loading…</small>');
    $.getJSON("{{ url('admin/hospital') }}/" + hospitalId + "/programmes-json")
      .done(function (data) { renderProgrammeChecklist(data.programmes || [], programmeId); })
      .fail(function () { $('#toggleProgrammeList').html('<small class="text-danger">Could not load programmes.</small>'); });
  } else {
    $('#toggleStatusForm').attr('action', "{{ url('admin/hospital/mark-expired') }}");
    $('#toggleActivateFields').hide();
    $('#toggleDeactivateMsg').show();
    $('#toggleModalHeader').css('background', '#dc3545');
    $('#toggleModalTitle').text('Deactivate Accreditation');
    $('#toggleSubmitBtn').text('Mark as Expired').removeClass('btn-cosecsa').addClass('btn-danger');
  }

  $('#toggleStatusModal').modal('show');
});
</script>
@endpush
