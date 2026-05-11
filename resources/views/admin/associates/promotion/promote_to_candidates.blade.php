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
            <small class="text-muted">Select the study year group to register as {{ $examYear }} exam candidates</small>
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
        <div class="col-md-12 mb-2">@include('_message')</div>

        {{-- Info alert --}}
        <div class="alert alert-info alert-dismissible mb-3" style="border-left:4px solid #2980b9;">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <i class="fas fa-info-circle mr-2"></i>
          <strong>How promotion works:</strong> When you promote a group of trainees, the system creates
          <em>candidate records</em> for them for the <strong>{{ $examYear }}</strong> exam year using their existing
          trainee data (PEN, hospital, programme, country). Trainees already registered as {{ $examYear }} candidates
          will be skipped automatically.
        </div>

        <div class="row">

          {{-- Promotion Form --}}
          <div class="col-md-5">
            <div class="card shadow-sm" style="border-radius:8px;">
              <div class="card-header py-2 px-3" style="background:#fff;border-bottom:2px solid #a02626;">
                <h6 class="mb-0 font-weight-bold" style="color:#a02626;">
                  <i class="fas fa-arrow-right mr-2"></i>Select Group to Promote
                </h6>
              </div>
              <div class="card-body">
                <form action="{{ url('admin/associates/promotion/promote-to-candidates') }}" method="POST">
                  @csrf
                  <div class="form-group">
                    <label class="font-weight-bold">Study Year / Group</label>
                    <select name="study_year_id" id="studyYearSelect" class="form-control" required>
                      <option value="">— Select a Study Year —</option>
                      @foreach($studyYears as $sy)
                      <option value="{{ $sy->id }}"
                              data-prog="{{ $sy->prog_name }}"
                              data-dur="{{ $sy->duration }}"
                              data-count="{{ $countMap[$sy->id] ?? 0 }}">
                        {{ $sy->sy_name }}
                        ({{ $countMap[$sy->id] ?? 0 }} trainees)
                      </option>
                      @endforeach
                    </select>
                    <small class="text-muted">Tip: look for <em>Year Two</em> (2-yr programmes) or <em>Year Three</em> / <em>Year Four</em> for final-year groups.</small>
                  </div>

                  {{-- Preview panel --}}
                  <div id="previewPanel" class="alert alert-secondary py-2 px-3 mb-3" style="display:none;">
                    <strong id="previewName"></strong><br>
                    <span class="text-muted" id="previewCount"></span>
                  </div>

                  <div class="form-group mb-0">
                    <label class="font-weight-bold">Exam Year</label>
                    <input type="text" class="form-control" value="{{ $examYear }}" readonly
                           style="background:#f8f8f8; font-weight:bold; color:#a02626;">
                  </div>

                  <div class="mt-3">
                    <button type="submit" class="btn btn-block"
                            style="background:#a02626;border-color:#a02626;color:#fff;"
                            onclick="return confirm('Promote all trainees in the selected group to {{ $examYear }} candidates?')">
                      <i class="fas fa-graduation-cap mr-2"></i>
                      Promote to {{ $examYear }} Candidates
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          {{-- Study Year reference table --}}
          <div class="col-md-7">
            <div class="card shadow-sm" style="border-radius:8px;">
              <div class="card-header py-2 px-3" style="background:#fff;border-bottom:1px solid #f0f0f0;">
                <h6 class="mb-0 font-weight-bold text-muted">
                  <i class="fas fa-table mr-2"></i>All Study Year Groups &amp; Trainee Counts
                </h6>
              </div>
              <div class="card-body p-0">
                <table class="table table-sm table-striped mb-0" style="font-size:.85rem;">
                  <thead class="thead-light">
                    <tr>
                      <th>Study Year</th>
                      <th>Programme</th>
                      <th class="text-center">Duration</th>
                      <th class="text-center">Trainees</th>
                      <th class="text-center">Final Year?</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($studyYears as $sy)
                    @php
                      $cnt = $countMap[$sy->id] ?? 0;
                      // Detect if this is the final year row (sy_name contains the duration year number)
                      $yearNum = preg_match('/Year\s+(\w+)/i', $sy->sy_name, $m) ? $m[1] : '';
                      $yearWords = ['One'=>1,'Two'=>2,'Three'=>3,'Four'=>4];
                      $yearInt = $yearWords[$yearNum] ?? 0;
                      $isFinal = ($yearInt === (int)$sy->duration && $sy->duration > 0);
                    @endphp
                    <tr class="{{ $isFinal ? 'table-warning' : '' }}">
                      <td>{{ $sy->sy_name }}</td>
                      <td>{{ $sy->prog_name }}</td>
                      <td class="text-center">{{ $sy->duration }} yr</td>
                      <td class="text-center">
                        <span class="badge {{ $cnt > 0 ? 'badge-primary' : 'badge-light text-muted' }}">{{ $cnt }}</span>
                      </td>
                      <td class="text-center">
                        @if($isFinal)
                          <span class="badge badge-warning" style="color:#333;"><i class="fas fa-star mr-1"></i>Final</span>
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
          </div>

        </div>
      </div>
    </section>
  </div>
</div>
@endsection

@push('scripts')
<script>
$('#studyYearSelect').on('change', function () {
    var opt   = $(this).find(':selected');
    var prog  = opt.data('prog');
    var cnt   = opt.data('count');
    var dur   = opt.data('dur');
    if ($(this).val()) {
        $('#previewName').text(prog + ' — ' + opt.text().replace(/\(.*\)/,'').trim());
        $('#previewCount').text(cnt + ' trainee(s) will be promoted to {{ $examYear }} candidates');
        $('#previewPanel').show();
    } else {
        $('#previewPanel').hide();
    }
});
</script>
@endpush
