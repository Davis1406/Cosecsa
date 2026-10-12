@extends('layout.app')

@section('content')
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-6">
            <h1 style="font-size:1.4rem;">Issue Transcript — {{ $record->full_name }}</h1>
            <p class="text-muted mb-0" style="font-size:.85rem;">
              @if(!$recordExists)
                Pre-filled from the fellow's record and the standard MCS/FCS course list — review before sharing.
              @else
                Preview shows the form as filled in; Share saves it, emails it to the fellow and keeps a copy on their profile.
              @endif
            </p>
          </div>
          <div class="col-sm-6 text-right">
            <a href="{{ url('admin/transcripts') }}" class="btn btn-cosecsa-outline">
              <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
            <button type="button" class="btn btn-cosecsa-outline" data-toggle="modal" data-target="#transcriptPreviewModal">
              <i class="fas fa-eye mr-1"></i> Preview
            </button>
            <a href="{{ url('admin/transcripts/pdf/'.$userId) }}" target="_blank" class="btn btn-cosecsa-outline">
              <i class="fas fa-file-pdf mr-1"></i> Download PDF
            </a>
            <button type="submit" form="transcriptForm" class="btn btn-cosecsa">
              <i class="fas fa-share-square mr-1"></i> Share Transcript
            </button>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        @include('_message')

        @if($recordExists && !empty($record->sent_at))
          <div class="alert alert-success d-flex justify-content-between align-items-center flex-wrap" style="font-size:.85rem;">
            <span><i class="fas fa-check-circle mr-1"></i>
              Shared with <strong>{{ $record->sent_to }}</strong> on
              {{ \Illuminate\Support\Carbon::parse($record->sent_at)->format('d M Y, H:i') }}.
            </span>
            <a href="{{ url('admin/transcripts/copy/'.$userId) }}" target="_blank" class="btn btn-sm btn-cosecsa-outline mt-1">
              <i class="fas fa-download mr-1"></i> Sent copy
            </a>
          </div>
        @endif

        <form method="post" action="{{ url('admin/transcripts/edit/'.$userId) }}" id="transcriptForm">
          @csrf

          <div class="card">
            <div class="card-header"><h3 class="card-title">Candidate Details</h3></div>
            <div class="card-body">
              <div class="form-row">
                <div class="form-group col-md-4">
                  <label>Full Name</label>
                  <input type="text" name="full_name" class="form-control" required value="{{ old('full_name', $record->full_name) }}">
                </div>
                <div class="form-group col-md-2">
                  <label>Gender</label>
                  <select name="gender" class="form-control">
                    <option value="">—</option>
                    <option value="Male" {{ old('gender', $record->gender)==='Male'?'selected':'' }}>Male</option>
                    <option value="Female" {{ old('gender', $record->gender)==='Female'?'selected':'' }}>Female</option>
                  </select>
                </div>
                <div class="form-group col-md-3">
                  <label>Programme Entry Number</label>
                  <input type="text" name="programme_entry_number" class="form-control" value="{{ old('programme_entry_number', $record->programme_entry_number) }}">
                </div>
                <div class="form-group col-md-3">
                  <label>Medium of Instruction</label>
                  <input type="text" name="medium_of_instruction" class="form-control" value="{{ old('medium_of_instruction', $record->medium_of_instruction ?: 'English') }}">
                </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-4">
                  <label>Programme</label>
                  <input type="text" name="programme" class="form-control" value="{{ old('programme', $record->programme) }}">
                </div>
                <div class="form-group col-md-2">
                  <label>Entry Year</label>
                  <input type="text" name="entry_period" class="form-control" value="{{ old('entry_period', $record->entry_period) }}">
                </div>
                <div class="form-group col-md-2">
                  <label>Completion Year</label>
                  <input type="text" name="completion_period" class="form-control" value="{{ old('completion_period', $record->completion_period) }}">
                </div>
                <div class="form-group col-md-2">
                  <label>Final Score (%)</label>
                  <input type="text" name="final_score" class="form-control" value="{{ old('final_score', $record->final_score) }}">
                </div>
                <div class="form-group col-md-2">
                  <label>Template</label>
                  <select name="template_id" class="form-control">
                    <option value="">Default</option>
                    @foreach($templates as $t)
                      <option value="{{ $t->id }}" {{ old('template_id', $record->template_id)==$t->id?'selected':'' }}>{{ $t->name }}</option>
                    @endforeach
                  </select>
                </div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Course / Qualification Rows</h3>
              <div class="card-tools">
                <button type="button" class="btn btn-sm btn-cosecsa-outline" id="addRowBtn">
                  <i class="fas fa-plus mr-1"></i> Add Row
                </button>
              </div>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-bordered table-sm" id="coursesTable">
                  <thead>
                    <tr>
                      <th style="width:20%;">Section (e.g. "MCS")</th>
                      <th style="width:20%;">Subsection (e.g. "MCS (2019-2020)")</th>
                      <th style="width:24%;">Course Name</th>
                      <th style="width:14%;">Academic Year</th>
                      <th style="width:14%;">Result</th>
                      <th style="width:8%;"></th>
                    </tr>
                  </thead>
                  <tbody id="coursesBody">
                    @foreach($courses as $c)
                      <tr>
                        <td><input type="text" class="form-control form-control-sm" name="courses[{{ $loop->index }}][section]" value="{{ $c->section }}"></td>
                        <td><input type="text" class="form-control form-control-sm" name="courses[{{ $loop->index }}][subsection]" value="{{ $c->subsection }}"></td>
                        <td><input type="text" class="form-control form-control-sm" name="courses[{{ $loop->index }}][course_name]" value="{{ $c->course_name }}"></td>
                        <td><input type="text" class="form-control form-control-sm" name="courses[{{ $loop->index }}][academic_year]" value="{{ $c->academic_year }}"></td>
                        <td><input type="text" class="form-control form-control-sm" name="courses[{{ $loop->index }}][result]" value="{{ $c->result }}"></td>
                        <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger removeRowBtn"><i class="fas fa-times"></i></button></td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <button type="submit" class="btn btn-cosecsa mb-4">
            <i class="fas fa-share-square mr-1"></i> Share Transcript
          </button>
        </form>
      </div>
    </section>
  </div>

  <div class="modal fade" id="transcriptPreviewModal" tabindex="-1" role="dialog" aria-labelledby="transcriptPreviewLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document" style="max-width:900px;">
      <div class="modal-content" style="height:85vh;">
        <div class="modal-header">
          <h5 class="modal-title" id="transcriptPreviewLabel"><i class="fas fa-file-pdf mr-1"></i> Transcript Preview</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body p-0" style="flex:1 1 auto;">
          <iframe id="transcriptPreviewFrame" src="about:blank" title="Transcript preview" style="width:100%; height:100%; border:0;"></iframe>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  let idx = {{ $courses->count() }};
  const body = document.getElementById('coursesBody');

  function rowHtml(i) {
    return `<tr>
      <td><input type="text" class="form-control form-control-sm" name="courses[${i}][section]"></td>
      <td><input type="text" class="form-control form-control-sm" name="courses[${i}][subsection]"></td>
      <td><input type="text" class="form-control form-control-sm" name="courses[${i}][course_name]"></td>
      <td><input type="text" class="form-control form-control-sm" name="courses[${i}][academic_year]"></td>
      <td><input type="text" class="form-control form-control-sm" name="courses[${i}][result]"></td>
      <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger removeRowBtn"><i class="fas fa-times"></i></button></td>
    </tr>`;
  }

  document.getElementById('addRowBtn').addEventListener('click', function () {
    body.insertAdjacentHTML('beforeend', rowHtml(idx++));
  });

  body.addEventListener('click', function (e) {
    const btn = e.target.closest('.removeRowBtn');
    if (btn) btn.closest('tr').remove();
  });

  const form = document.getElementById('transcriptForm');

  // ── Preview modal: renders the form as it is now (nothing is saved) ──
  // Bootstrap 4 fires modal events through jQuery only, so bind with $().on.
  const frame = document.getElementById('transcriptPreviewFrame');
  let previewBlobUrl = null;
  @if(Auth::user()->hasPermission('transcripts.manage'))
  $('#transcriptPreviewModal').on('show.bs.modal', function () {
    fetch(@json(url('admin/transcripts/preview/'.$userId)), {
      method: 'POST',
      body: new FormData(form),
      headers: { 'Accept': 'application/pdf' },
      credentials: 'same-origin',
    })
      .then(r => r.ok ? r.blob() : r.text().then(t => Promise.reject(t)))
      .then(blob => {
        previewBlobUrl = URL.createObjectURL(blob);
        frame.src = previewBlobUrl;
      })
      .catch(msg => {
        frame.srcdoc = '<p style="font-family:sans-serif;padding:1rem;color:#a02626;">Could not load the preview. '
          + String(msg || '').replace(/</g, '&lt;').slice(0, 300) + '</p>';
      });
  });
  @else
  $('#transcriptPreviewModal').on('show.bs.modal', function () {
    frame.src = @json(url('admin/transcripts/pdf/'.$userId));
  });
  @endif
  $('#transcriptPreviewModal').on('hidden.bs.modal', function () {
    frame.removeAttribute('srcdoc');
    frame.src = 'about:blank';
    if (previewBlobUrl) { URL.revokeObjectURL(previewBlobUrl); previewBlobUrl = null; }
  });

  // ── Share = save + email to the fellow + keep a copy on their profile ──
  form.addEventListener('submit', function (e) {
    if (!confirm('Save this transcript, email it to the fellow and keep a copy on their profile?')) {
      e.preventDefault();
    }
  });
});
</script>
@endpush
