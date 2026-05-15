@extends('layout.app')

@section('content')
<div class="wrapper">
<div class="content-wrapper">

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-8">
                    <h4 class="mb-0">Upload Examiner Confirmation</h4>
                    <small class="text-muted">
                        Upload an Excel file of examiners who participated but did not record marks in the system.
                        The file will be matched against existing examiner records and a full report will be generated.
                    </small>
                </div>
                <div class="col-sm-4 text-right">
                    <a href="{{ url('admin/exams/examiners') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Examiners
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="col-md-12">@include('_message')</div>

    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-header" style="background:#a02626; color:#fff;">
                            <h3 class="card-title">
                                <i class="fas fa-clipboard-check mr-2"></i> Upload File
                            </h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('exams.upload.confirmation.process') }}"
                                  enctype="multipart/form-data" id="uploadForm">
                                @csrf

                                <div class="form-group">
                                    <label class="font-weight-bold">Exam Year <span class="text-danger">*</span></label>
                                    <select name="year_id" class="form-control" required>
                                        @foreach($years as $yr)
                                            <option value="{{ $yr->id }}"
                                                {{ $yr->id == $defaultYear ? 'selected' : '' }}>
                                                {{ $yr->year_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Select the year this examination took place.</small>
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold">Excel File <span class="text-danger">*</span></label>
                                    <div id="drop-zone" class="drop-zone">
                                        <div class="drop-zone-inner" id="drop-inner">
                                            <i class="fas fa-file-excel fa-3x mb-2" style="color:#1e7e34;"></i>
                                            <p class="mb-1">Drag &amp; drop your file here, or <strong>click to browse</strong></p>
                                            <small class="text-muted">Accepted: .xlsx, .xls, .csv — max 5 MB</small>
                                        </div>
                                        <div id="file-selected" class="d-none">
                                            <i class="fas fa-check-circle fa-2x text-success mb-1"></i>
                                            <p class="mb-0 font-weight-bold" id="file-name"></p>
                                            <small class="text-muted" id="file-size"></small>
                                        </div>
                                        <input type="file" name="file" id="file-input"
                                               accept=".xlsx,.xls,.csv" class="drop-zone-file-input" required>
                                    </div>
                                    @error('file')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>

                                <div class="alert alert-info py-2">
                                    <strong>Expected columns (in order):</strong><br>
                                    <code>Full Name</code> &nbsp;·&nbsp;
                                    <code>Email</code> &nbsp;·&nbsp;
                                    <code>Specialty</code> &nbsp;·&nbsp;
                                    <code>Country</code> &nbsp;·&nbsp;
                                    <code>Role</code> &nbsp;·&nbsp;
                                    <code>Fellowship #</code> &nbsp;·&nbsp;
                                    <code>Sub Specialty</code>
                                </div>

                                <button type="submit" class="btn btn-danger btn-block" id="submit-btn">
                                    <i class="fas fa-upload mr-1"></i> Process &amp; Generate Report
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Info panel --}}
                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i> How Matching Works</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0">
                                <tbody>
                                    <tr>
                                        <td style="width:36px;" class="text-center text-success"><i class="fas fa-check-circle"></i></td>
                                        <td><strong>Updated</strong><br><small>Examiner matched and participation recorded for the selected year.</small></td>
                                    </tr>
                                    <tr>
                                        <td class="text-center" style="color:#856404;"><i class="fas fa-clone"></i></td>
                                        <td><strong>Already Exists</strong><br><small>A record for this examiner, year and specialty already exists in the system.</small></td>
                                    </tr>
                                    <tr>
                                        <td class="text-center text-info"><i class="fas fa-copy"></i></td>
                                        <td><strong>Duplicate in File</strong><br><small>The same examiner + specialty appears more than once in the uploaded file.</small></td>
                                    </tr>
                                    <tr>
                                        <td class="text-center text-danger"><i class="fas fa-user-slash"></i></td>
                                        <td><strong>Not Found</strong><br><small>No matching examiner found in the system (by email or name).</small></td>
                                    </tr>
                                    <tr>
                                        <td class="text-center text-warning"><i class="fas fa-exclamation-triangle"></i></td>
                                        <td><strong>Error</strong><br><small>Row is missing required data (email or specialty).</small></td>
                                    </tr>
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

@push('styles')
<style>
.drop-zone {
    position: relative;
    border: 2px dashed #adb5bd;
    border-radius: 8px;
    padding: 36px 20px;
    text-align: center;
    cursor: pointer;
    transition: border-color .2s, background .2s;
    background: #fafafa;
}
.drop-zone:hover, .drop-zone.drag-over { border-color: #a02626; background: #fdf5f5; }
.drop-zone-file-input {
    position: absolute; inset: 0; width: 100%; height: 100%;
    opacity: 0; cursor: pointer;
}
</style>
@endpush

@push('scripts')
<script>
$(function () {
    var $zone  = $('#drop-zone');
    var $input = $('#file-input');

    $zone.on('dragover dragenter', function(e) {
        e.preventDefault(); e.stopPropagation();
        $zone.addClass('drag-over');
    }).on('dragleave drop', function(e) {
        e.preventDefault(); e.stopPropagation();
        $zone.removeClass('drag-over');
    }).on('drop', function(e) {
        var files = e.originalEvent.dataTransfer.files;
        if (files.length) {
            $input[0].files = files; // assign to input
            showFile(files[0]);
        }
    });

    $input.on('change', function() {
        if (this.files.length) showFile(this.files[0]);
    });

    function showFile(f) {
        $('#drop-inner').addClass('d-none');
        $('#file-name').text(f.name);
        $('#file-size').text((f.size / 1024).toFixed(1) + ' KB');
        $('#file-selected').removeClass('d-none');
    }

    $('#uploadForm').on('submit', function() {
        $('#submit-btn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Processing…');
    });
});
</script>
@endpush
