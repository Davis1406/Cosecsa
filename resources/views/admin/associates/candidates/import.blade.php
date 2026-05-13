@extends('layout.app')

@section('content')
<div class="wrapper">
  <div class="content-wrapper">

    <section class="content-header py-2">
      <div class="container-fluid">
        <div class="row align-items-center">
          <div class="col-sm-7">
            <h5 class="m-0" style="color:#a02626;">
              <i class="fas fa-file-upload mr-2"></i>Import Candidates
            </h5>
            <small class="text-muted">Upload a CSV, XLS, or XLSX file to bulk-import candidate records</small>
          </div>
          <div class="col-sm-5 text-right">
            <a href="{{ url('admin/associates/candidates/list') }}" class="btn btn-sm btn-outline-secondary">
              <i class="fas fa-arrow-left mr-1"></i>Back to Candidates
            </a>
          </div>
        </div>
      </div>
    </section>

    <section class="content pt-1">
      <div class="container-fluid">

        @if(session('success'))
        <div class="alert alert-success alert-dismissible shadow-sm mb-3" style="border-left:4px solid #27ae60;">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible shadow-sm mb-3" style="border-left:4px solid #e74c3c;">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
        @endif

        <div class="row justify-content-center">
          <div class="col-md-7">
            <div class="card shadow-sm" style="border-radius:8px;">
              <div class="card-header py-2 px-3" style="background:#fff;border-bottom:2px solid #a02626;">
                <h6 class="mb-0 font-weight-bold" style="color:#a02626;">
                  <i class="fas fa-upload mr-2"></i>Upload Candidates Data
                </h6>
              </div>
              <div class="card-body">

                <div class="alert alert-info mb-3" style="border-left:4px solid #2980b9;font-size:.88rem;">
                  <i class="fas fa-info-circle mr-1"></i>
                  <strong>File requirements:</strong>
                  Accepted formats: <code>.csv</code>, <code>.xls</code>, <code>.xlsx</code>. Max size: 2 MB.
                  The first row must be a header row. Required columns:
                  <code>firstname</code>, <code>lastname</code>, <code>personal_email</code>,
                  <code>programme_id</code>, <code>hospital_id</code>, <code>country_id</code>, <code>entry_number</code>.
                </div>

                <form method="POST" action="{{ route('candidates.import.data') }}" enctype="multipart/form-data" id="importForm">
                  @csrf
                  <div class="form-group">
                    <label class="font-weight-bold">Select File</label>
                    <div class="custom-file">
                      <input type="file" class="custom-file-input" id="importFile" name="file"
                             accept=".csv,.xls,.xlsx" required>
                      <label class="custom-file-label" for="importFile">No file chosen</label>
                    </div>
                    <small class="text-muted">.csv, .xls, .xlsx — max 2 MB</small>
                  </div>

                  <div class="mt-3">
                    <button type="submit" class="btn btn-block" id="btnUpload"
                            style="background:#a02626;border-color:#a02626;color:#fff;">
                      <i class="fas fa-upload mr-2"></i>Upload &amp; Import
                    </button>
                  </div>
                </form>

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
$(document).ready(function () {
  // Show filename in label
  $('#importFile').on('change', function () {
    var name = $(this).val().split('\\').pop() || 'No file chosen';
    $(this).siblings('.custom-file-label').text(name);
  });

  // Show loading state on submit
  $('#importForm').on('submit', function () {
    var btn = $('#btnUpload');
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Importing…');
  });
});
</script>
@endpush
