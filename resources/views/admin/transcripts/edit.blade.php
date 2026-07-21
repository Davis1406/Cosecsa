@extends('layout.app')

@section('content')
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-6">
            <h1 style="font-size:1.4rem;">Issue Transcript — {{ $record->full_name }}</h1>
          </div>
          <div class="col-sm-6 text-right">
            <a href="{{ url('admin/transcripts') }}" class="btn btn-secondary">
              <i class="fas fa-arrow-left mr-1"></i> Back to Search
            </a>
            @if($record->exists)
              <a href="{{ url('admin/transcripts/pdf/'.$userId) }}" target="_blank" class="btn btn-success">
                <i class="fas fa-file-pdf mr-1"></i> View / Download PDF
              </a>
            @endif
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        @include('_message')

        @if(!$record->exists)
          <div class="alert alert-info" style="font-size:.85rem;">
            <i class="fas fa-info-circle mr-1"></i> Candidate details pre-filled from their existing record where available,
            and the course table below pre-filled with the standard MCS/FCS course list — review, edit, add, or remove
            rows as needed, then save. The PDF option appears once saved.
          </div>
        @endif

        <form method="post" action="{{ url('admin/transcripts/edit/'.$userId) }}">
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
            <div class="card-header d-flex justify-content-between align-items-center">
              <h3 class="card-title">Course / Qualification Rows</h3>
              <button type="button" class="btn btn-sm btn-outline-primary" id="addRowBtn">
                <i class="fas fa-plus mr-1"></i> Add Row
              </button>
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

          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i> Save Transcript
          </button>
        </form>
      </div>
    </section>
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
});
</script>
@endpush
