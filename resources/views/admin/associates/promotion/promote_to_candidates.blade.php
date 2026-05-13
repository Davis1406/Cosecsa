@extends('layout.app')

@section('content')
<div class="wrapper">
  <div class="content-wrapper">

    <section class="content-header py-2">
      <div class="container-fluid">
        <div class="row align-items-center">
          <div class="col-sm-7">
            <h5 class="m-0" style="color:#a02626;">
              <i class="fas fa-graduation-cap mr-2"></i>Promote Trainees → {{ $examYear }} Candidates
            </h5>
            <small class="text-muted">Select a study year, pick the trainees to promote, then submit</small>
          </div>
          <div class="col-sm-5 text-right">
            <a href="{{ url('admin/associates/candidates/list') }}" class="btn btn-sm btn-outline-secondary">
              <i class="fas fa-users mr-1"></i> View Candidates
            </a>
          </div>
        </div>
      </div>
    </section>

    <section class="content pt-1">
      <div class="container-fluid">

        {{-- Flash messages --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissible shadow-sm mb-3" style="border-left:4px solid #27ae60;">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <i class="fas fa-check-circle mr-2"></i>{!! session('success') !!}
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible shadow-sm mb-3" style="border-left:4px solid #e74c3c;">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
        @endif

        {{-- Info banner --}}
        <div class="alert alert-info alert-dismissible mb-3" style="border-left:4px solid #2980b9;">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <i class="fas fa-info-circle mr-2"></i>
          <strong>How it works:</strong> Choose a study year group below. A list of trainees in that group will appear.
          Tick the ones you want to promote — or use <em>Select All</em>. Trainees already registered as
          <strong>{{ $examYear }}</strong> candidates are highlighted and pre-disabled.
        </div>

        <form action="{{ url('admin/associates/promotion/promote-to-candidates') }}" method="POST" id="promotionForm">
          @csrf

          <div class="row">

            {{-- ── Left: selector card ────────────────────────────────────── --}}
            <div class="col-md-4">
              <div class="card shadow-sm mb-3" style="border-radius:8px;">
                <div class="card-header py-2 px-3" style="background:#fff;border-bottom:2px solid #a02626;">
                  <h6 class="mb-0 font-weight-bold" style="color:#a02626;">
                    <i class="fas fa-filter mr-2"></i>Select Study Year Group
                  </h6>
                </div>
                <div class="card-body pb-2">

                  <div class="form-group mb-2">
                    <label class="font-weight-bold small">Study Year / Group</label>
                    <select name="study_year_id" id="studyYearSelect" class="form-control" required>
                      <option value="">— Choose a study year —</option>
                      @foreach($studyYears as $sy)
                      <option value="{{ $sy->id }}"
                              data-prog="{{ $sy->prog_name }}"
                              data-dur="{{ $sy->duration }}"
                              data-count="{{ $countMap[$sy->id] ?? 0 }}">
                        {{ $sy->sy_name }} — {{ $sy->prog_name }}
                        ({{ $countMap[$sy->id] ?? 0 }})
                      </option>
                      @endforeach
                    </select>
                    <small class="text-muted">Final-year groups are marked ★ in the table</small>
                  </div>

                  <div class="form-group mb-2">
                    <label class="font-weight-bold small">Exam Year</label>
                    <input type="text" class="form-control form-control-sm" value="{{ $examYear }}" readonly
                           style="background:#f8f8f8;font-weight:700;color:#a02626;">
                  </div>

                  {{-- Summary badge --}}
                  <div id="selectionSummary" class="alert alert-secondary py-2 px-3 mb-2" style="display:none;font-size:.85rem;">
                    <span id="summaryText"></span>
                  </div>

                  <button type="submit" id="btnPromote" class="btn btn-block mt-2"
                          style="background:#a02626;border-color:#a02626;color:#fff;" disabled>
                    <i class="fas fa-graduation-cap mr-2"></i>Promote Selected to {{ $examYear }}
                  </button>
                </div>
              </div>

              {{-- Reference table --}}
              <div class="card shadow-sm" style="border-radius:8px;">
                <div class="card-header py-2 px-3" style="background:#fff;border-bottom:1px solid #f0f0f0;">
                  <h6 class="mb-0 font-weight-bold text-muted" style="font-size:.82rem;">
                    <i class="fas fa-table mr-1"></i>All Groups &amp; Counts
                  </h6>
                </div>
                <div class="card-body p-0">
                  <table class="table table-sm table-striped mb-0" style="font-size:.78rem;">
                    <thead class="thead-light">
                      <tr>
                        <th>Year</th>
                        <th>Programme</th>
                        <th class="text-center">Trainees</th>
                        <th class="text-center">Final?</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($studyYears as $sy)
                      @php
                        $cnt = $countMap[$sy->id] ?? 0;
                        $yearWords = ['One'=>1,'Two'=>2,'Three'=>3,'Four'=>4];
                        preg_match('/Year\s+(\w+)/i', $sy->sy_name, $m);
                        $yearInt = $yearWords[$m[1] ?? ''] ?? 0;
                        $isFinal = ($yearInt === (int)$sy->duration && $sy->duration > 0);
                      @endphp
                      <tr class="{{ $isFinal ? 'table-warning' : '' }}">
                        <td>{{ $sy->sy_name }}</td>
                        <td style="max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $sy->prog_name }}">{{ $sy->prog_name }}</td>
                        <td class="text-center">
                          <span class="badge {{ $cnt > 0 ? 'badge-primary' : 'badge-light text-muted' }}">{{ $cnt }}</span>
                        </td>
                        <td class="text-center">
                          @if($isFinal)
                            <span class="badge badge-warning" style="color:#333;">★</span>
                          @else
                            <span class="text-muted">—</span>
                          @endif
                        </td>
                      </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>

            </div>{{-- /col-md-4 --}}

            {{-- ── Right: trainee preview table ───────────────────────────── --}}
            <div class="col-md-8">
              <div class="card shadow-sm mb-3" style="border-radius:8px;">
                <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center"
                     style="background:#fff;border-bottom:1px solid #f0f0f0;">
                  <h6 class="mb-0 font-weight-bold text-muted">
                    <i class="fas fa-users mr-1"></i>
                    Trainees in Selected Group
                    <span id="previewCountBadge" class="badge badge-secondary ml-1" style="display:none;"></span>
                  </h6>
                  <div id="selectAllWrap" style="display:none;">
                    <div class="custom-control custom-checkbox">
                      <input type="checkbox" class="custom-control-input" id="chkSelectAll">
                      <label class="custom-control-label font-weight-bold" for="chkSelectAll"
                             style="font-size:.82rem;cursor:pointer;">Select All</label>
                    </div>
                  </div>
                </div>
                <div class="card-body p-0">

                  {{-- Loading spinner --}}
                  <div id="previewLoading" style="display:none;" class="text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-muted"></i>
                    <div class="text-muted mt-2 small">Loading trainees…</div>
                  </div>

                  {{-- Placeholder --}}
                  <div id="previewPlaceholder" class="text-center py-5 text-muted">
                    <i class="fas fa-hand-point-left fa-2x mb-2"></i>
                    <div>Select a study year group to preview its trainees</div>
                  </div>

                  {{-- Trainee table --}}
                  <div id="previewTableWrap" style="display:none;">
                    <table class="table table-sm table-bordered table-hover mb-0" style="font-size:.83rem;">
                      <thead class="thead-light">
                        <tr>
                          <th style="width:36px;" class="text-center">
                            <i class="fas fa-check-square text-muted" title="Select"></i>
                          </th>
                          <th>#</th>
                          <th>Name</th>
                          <th>PEN</th>
                          <th>Gender</th>
                          <th>Programme</th>
                          <th>Hospital</th>
                          <th>Country</th>
                          <th class="text-center">Status</th>
                        </tr>
                      </thead>
                      <tbody id="previewTbody"></tbody>
                    </table>
                  </div>

                </div>
              </div>
            </div>{{-- /col-md-8 --}}

          </div>{{-- /row --}}
        </form>

      </div>
    </section>
  </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {

  var PREVIEW_URL = '{{ url("admin/associates/promotion/trainees-preview") }}';
  var currentData = [];

  function updateSummary() {
    var checked = $('input.chk-trainee:checked').length;
    var total   = $('input.chk-trainee').length;
    if (total === 0) {
      $('#selectionSummary').hide();
      $('#btnPromote').prop('disabled', true);
      return;
    }
    $('#summaryText').html(
      '<strong>' + checked + '</strong> of <strong>' + total +
      '</strong> trainees selected for promotion'
    );
    $('#selectionSummary').show();
    $('#btnPromote').prop('disabled', checked === 0);

    // Sync select-all checkbox state
    $('#chkSelectAll').prop('indeterminate', checked > 0 && checked < total);
    $('#chkSelectAll').prop('checked', checked === total && total > 0);
  }

  // Study year changed → fetch trainees
  $('#studyYearSelect').on('change', function () {
    var syId = $(this).val();
    if (!syId) {
      $('#previewPlaceholder').show();
      $('#previewTableWrap, #previewLoading, #selectAllWrap').hide();
      $('#previewCountBadge').hide();
      $('#selectionSummary').hide();
      $('#btnPromote').prop('disabled', true);
      return;
    }

    $('#previewPlaceholder').hide();
    $('#previewTableWrap').hide();
    $('#selectAllWrap').hide();
    $('#previewLoading').show();

    $.getJSON(PREVIEW_URL, { study_year_id: syId }, function (rows) {
      currentData = rows;
      $('#previewLoading').hide();

      if (!rows.length) {
        $('#previewPlaceholder').text('No trainees found in this study year group.').show();
        $('#previewCountBadge').hide();
        $('#btnPromote').prop('disabled', true);
        return;
      }

      var tbody = '';
      $.each(rows, function (i, t) {
        var fullName = [t.firstname, t.middlename, t.lastname].filter(Boolean).join(' ');
        var isExisting = t.already_candidate;
        var rowClass = isExisting ? 'table-secondary' : '';
        var badge = isExisting
          ? '<span class="badge badge-info" style="font-size:.72rem;">Already Candidate</span>'
          : '<span class="badge badge-light text-muted" style="font-size:.72rem;">Eligible</span>';
        tbody +=
          '<tr class="' + rowClass + '">' +
            '<td class="text-center">' +
              '<div class="custom-control custom-checkbox">' +
                '<input type="checkbox" class="custom-control-input chk-trainee" ' +
                  'id="ct_' + t.trainee_id + '" ' +
                  'name="trainee_ids[]" ' +
                  'value="' + t.trainee_id + '"' +
                  (isExisting ? ' disabled' : ' checked') + '>' +
                '<label class="custom-control-label" for="ct_' + t.trainee_id + '"></label>' +
              '</div>' +
            '</td>' +
            '<td>' + (i + 1) + '</td>' +
            '<td style="white-space:nowrap;">' + (fullName || '—') + '</td>' +
            '<td>' + (t.entry_number || '—') + '</td>' +
            '<td>' + (t.gender || '—') + '</td>' +
            '<td style="max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + (t.programme_name || '—') + '</td>' +
            '<td style="max-width:130px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">' + (t.hospital_name || '—') + '</td>' +
            '<td>' + (t.country_name || '—') + '</td>' +
            '<td class="text-center">' + badge + '</td>' +
          '</tr>';
      });

      $('#previewTbody').html(tbody);
      $('#previewCountBadge').text(rows.length).show();
      $('#previewTableWrap').show();
      $('#selectAllWrap').show();

      // Reset select-all state
      $('#chkSelectAll').prop('checked', true).prop('indeterminate', false);
      updateSummary();

    }).fail(function () {
      $('#previewLoading').hide();
      $('#previewPlaceholder').text('Failed to load trainees. Please try again.').show();
    });
  });

  // Select all / deselect all
  $('#chkSelectAll').on('change', function () {
    $('input.chk-trainee:not(:disabled)').prop('checked', $(this).is(':checked'));
    updateSummary();
  });

  // Individual checkbox
  $(document).on('change', 'input.chk-trainee', function () {
    updateSummary();
  });

  // Confirm before submit
  $('#promotionForm').on('submit', function (e) {
    var count = $('input.chk-trainee:checked').length;
    if (count === 0) {
      e.preventDefault();
      alert('Please select at least one trainee to promote.');
      return false;
    }
    return confirm('Promote ' + count + ' selected trainee(s) to {{ $examYear }} candidates?');
  });

});
</script>
@endpush
