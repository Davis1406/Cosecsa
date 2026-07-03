@extends('layout.app')

@push('styles')
<style>
    .doc-input-wrap { position: relative; }
    .doc-input-wrap input[type="file"] {
        font-size: .78rem;
        padding: 2px 4px;
        max-width: 180px;
    }
    .badge-has  { background:#1b7a34; color:#fff; font-size:.7rem; padding:2px 7px; border-radius:10px; }
    .badge-miss { background:#c0392b; color:#fff; font-size:.7rem; padding:2px 7px; border-radius:10px; }
    #bulkdocstable td { vertical-align: middle; }
    .file-staged { border-color: #28a745 !important; background:#f0fff4; }
    .upload-bar {
        position: sticky; bottom: 0; z-index: 100;
        background: #fff; border-top: 2px solid #dee2e6;
        padding: 10px 20px;
        display: flex; align-items: center; gap: 1rem;
    }
    .filter-tabs .btn { font-size:.82rem; }
    .filter-tabs .btn.active { background:#a02626; border-color:#a02626; color:#fff; }
</style>
@endpush

@section('content')
<div class="wrapper">
<div class="content-wrapper">

    <section class="content-header">
        <div class="container-fluid">
            <div class="row align-items-center mb-2">
                <div class="col-sm-6">
                    <h4 class="mb-0">Bulk CV &amp; Photo Upload</h4>
                    <small class="text-muted">
                        <span class="text-danger font-weight-bold">{{ $totalNoCv }}</span> missing CV &nbsp;·&nbsp;
                        <span class="text-danger font-weight-bold">{{ $totalNoPhoto }}</span> missing photo
                    </small>
                </div>
                <div class="col-sm-6 text-right">
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

            {{-- Filter tabs --}}
            <div class="filter-tabs btn-group btn-group-sm mb-3" role="group">
                <a href="{{ route('examiners.bulk.upload.docs', ['filter'=>'both']) }}"
                   class="btn btn-outline-secondary {{ $filter === 'both' ? 'active' : '' }}">
                    Missing CV or Photo
                </a>
                <a href="{{ route('examiners.bulk.upload.docs', ['filter'=>'cv']) }}"
                   class="btn btn-outline-secondary {{ $filter === 'cv' ? 'active' : '' }}">
                    Missing CV only
                </a>
                <a href="{{ route('examiners.bulk.upload.docs', ['filter'=>'photo']) }}"
                   class="btn btn-outline-secondary {{ $filter === 'photo' ? 'active' : '' }}">
                    Missing Photo only
                </a>
            </div>

            <form method="POST" action="{{ route('examiners.bulk.upload.docs.process') }}"
                  enctype="multipart/form-data" id="bulkUploadForm">
                @csrf

                <div class="card">
                    <div class="card-header d-flex align-items-center" style="gap:.5rem;">
                        <div class="custom-control custom-checkbox mr-2">
                            <input type="checkbox" class="custom-control-input" id="chkSelectAll">
                            <label class="custom-control-label font-weight-bold" for="chkSelectAll"
                                   style="font-size:.82rem;cursor:pointer;">Select All</label>
                        </div>
                        <button type="button" id="btnDeselectAll" class="btn btn-sm btn-outline-secondary"
                                style="font-size:.78rem;padding:2px 8px;">
                            <i class="fas fa-square mr-1"></i>Deselect All
                        </button>
                        <small class="text-muted ml-auto">
                            Showing <strong>{{ $examiners->count() }}</strong> examiners —
                            attach files then click <strong>Upload Selected</strong>.
                        </small>
                    </div>
                    <div class="card-body p-0">
                        <table id="bulkdocstable" class="table table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    <th style="width:36px;"></th>
                                    <th>#</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th style="width:110px;">CV</th>
                                    <th style="width:200px;">Upload CV <small class="text-muted">(PDF/DOC/DOCX · 10MB)</small></th>
                                    <th style="width:110px;">Photo</th>
                                    <th style="width:200px;">Upload Photo <small class="text-muted">(JPG/PNG · 5MB)</small></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($examiners as $i => $ex)
                                <tr class="doc-row" data-id="{{ $ex->id }}">
                                    <td>
                                        <input type="checkbox" class="row-chk" name="selected[]" value="{{ $ex->id }}">
                                    </td>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $ex->name }}</td>
                                    <td>{{ $ex->email }}</td>
                                    <td>
                                        @if(!empty($ex->curriculum_vitae))
                                            <span class="badge-has"><i class="fas fa-check mr-1"></i>Has CV</span>
                                        @else
                                            <span class="badge-miss"><i class="fas fa-times mr-1"></i>Missing</span>
                                        @endif
                                    </td>
                                    <td class="doc-input-wrap">
                                        <input type="file"
                                               name="cv[{{ $ex->id }}]"
                                               class="form-control-file cv-file"
                                               accept=".pdf,.doc,.docx"
                                               data-id="{{ $ex->id }}">
                                    </td>
                                    <td>
                                        @if(!empty($ex->passport_image))
                                            <span class="badge-has"><i class="fas fa-check mr-1"></i>Has Photo</span>
                                        @else
                                            <span class="badge-miss"><i class="fas fa-times mr-1"></i>Missing</span>
                                        @endif
                                    </td>
                                    <td class="doc-input-wrap">
                                        <input type="file"
                                               name="photo[{{ $ex->id }}]"
                                               class="form-control-file photo-file"
                                               accept=".jpg,.jpeg,.png"
                                               data-id="{{ $ex->id }}">
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Sticky upload bar --}}
                <div class="upload-bar shadow-sm">
                    <div>
                        <span class="font-weight-bold" id="stagedCount">0</span> file(s) staged
                        (<span id="stagedCv">0</span> CV · <span id="stagedPhoto">0</span> photo)
                    </div>
                    <button type="submit" id="btnUpload" class="btn btn-danger" disabled>
                        <i class="fas fa-upload mr-1"></i> Upload Selected
                    </button>
                    <small class="text-muted">Only rows with files attached will be processed.</small>
                </div>

            </form>
        </div>
    </section>
</div>
</div>
@endsection

@push('scripts')
<script>
$(function () {

    // Init DataTable (no pagination — we need all rows accessible for file inputs)
    var table = $('#bulkdocstable').DataTable({
        responsive: false,
        lengthChange: false,
        paging: false,
        info: false,
        autoWidth: false,
        buttons: [],
        columnDefs: [
            { targets: 0, orderable: false, searchable: false },
            { targets: [4, 5, 6, 7], orderable: false }
        ]
    });

    // ── File staging counter ──────────────────────────────────────────────────
    function updateStagedCount() {
        var cvCount    = $('.cv-file').filter(function () { return this.files && this.files.length > 0; }).length;
        var photoCount = $('.photo-file').filter(function () { return this.files && this.files.length > 0; }).length;
        var total = cvCount + photoCount;
        $('#stagedCount').text(total);
        $('#stagedCv').text(cvCount);
        $('#stagedPhoto').text(photoCount);
        $('#btnUpload').prop('disabled', total === 0);
    }

    // Highlight row when file is attached; clear styling when removed
    $(document).on('change', '.cv-file, .photo-file', function () {
        if (this.files && this.files.length > 0) {
            $(this).addClass('file-staged');
        } else {
            $(this).removeClass('file-staged');
        }
        updateStagedCount();
    });

    // ── Select All / Deselect All ─────────────────────────────────────────────
    $('#chkSelectAll').on('change', function () {
        $('.row-chk').prop('checked', this.checked);
    });

    $('#btnDeselectAll').on('click', function () {
        $('.row-chk').prop('checked', false);
        $('#chkSelectAll').prop('checked', false);
    });

    // ── Before submit: disable file inputs for unchecked rows ────────────────
    // (prevents uploading files for rows the admin didn't deliberately select,
    //  but still allows rows with files that weren't checked)
    // Strategy: submit all rows that have at least one file — ignore checkbox if file is present.
    $('#bulkUploadForm').on('submit', function (e) {
        var staged = $('.cv-file, .photo-file').filter(function () {
            return this.files && this.files.length > 0;
        }).length;
        if (staged === 0) {
            e.preventDefault();
            alert('Please attach at least one file before uploading.');
            return;
        }
        // Disable inputs on rows with no files to reduce payload
        $('.cv-file, .photo-file').each(function () {
            if (!this.files || this.files.length === 0) {
                $(this).prop('disabled', true);
            }
        });
    });

});
</script>
@endpush
