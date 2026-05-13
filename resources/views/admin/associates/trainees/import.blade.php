@extends('layout.app')

@section('content')
<div class="wrapper">
    <div class="content-wrapper">

        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Import Trainees</h1>
                    </div>
                    <div class="col-sm-6 text-right">
                        <a href="{{ url('admin/associates/trainees/trainees') }}"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Trainees
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <div class="col-md-12">@include('_message')</div>

        <section class="content">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-md-7">

                        {{-- Upload Card --}}
                        <div class="card card-outline" style="border-top:3px solid #a02626;">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-file-upload mr-2" style="color:#a02626;"></i>
                                    Upload 2026 Intake CSV / Excel
                                </h3>
                            </div>
                            <div class="card-body">

                                <div class="mb-4 p-3 rounded" style="background:#1c4f82;color:#ffffff;border-left:4px solid #4a9fd4;">
                                    <div class="mb-1">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        <strong>How it works:</strong>
                                        Upload the secretariat's intake spreadsheet. The system will automatically:
                                    </div>
                                    <ul class="mb-0 pl-4" style="color:#d6eaf8;">
                                        <li>Map columns to the correct database fields</li>
                                        <li>Skip trainees already registered in the system</li>
                                        <li>Skip rejected applications</li>
                                        <li>Show a detailed report of every row processed</li>
                                    </ul>
                                </div>

                                <form id="importForm" method="POST"
                                      action="{{ route('trainees.import.data') }}"
                                      enctype="multipart/form-data">
                                    @csrf

                                    <div class="form-group">
                                        <label class="font-weight-bold">Select File</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="importFile"
                                                   name="file" accept=".csv,.xls,.xlsx" required>
                                            <label class="custom-file-label" for="importFile">
                                                Choose file&hellip;
                                            </label>
                                        </div>
                                        <small class="text-muted">Accepted: .csv, .xls, .xlsx &mdash; Max 10 MB</small>
                                    </div>

                                    {{-- File preview strip --}}
                                    <div id="filePreview" class="d-none mb-3">
                                        <div class="d-flex align-items-center p-2 rounded"
                                             style="background:#f8f9fa;border:1px solid #dee2e6;">
                                            <i class="fas fa-file-excel fa-2x mr-3" style="color:#1d6f42;"></i>
                                            <div>
                                                <div id="fileName" class="font-weight-bold" style="font-size:.9rem;"></div>
                                                <div id="fileSize" class="text-muted" style="font-size:.8rem;"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" id="btnUpload"
                                            class="btn btn-block btn-lg"
                                            style="background:#a02626;color:#fff;border-color:#a02626;">
                                        <i class="fas fa-upload mr-2"></i> Upload &amp; Import
                                    </button>

                                    <div id="uploadSpinner" class="text-center mt-3 d-none">
                                        <div class="spinner-border text-danger" role="status"></div>
                                        <div class="mt-2 text-muted small">
                                            Processing rows &mdash; this may take a minute for large files&hellip;
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Column mapping reference --}}
                        <div class="card card-outline card-secondary">
                            <div class="card-header" style="cursor:pointer;"
                                 data-toggle="collapse" data-target="#colMap">
                                <h3 class="card-title mb-0">
                                    <i class="fas fa-table mr-2"></i> Expected Column Headers
                                    <i class="fas fa-chevron-down float-right mt-1" style="font-size:.8rem;"></i>
                                </h3>
                            </div>
                            <div id="colMap" class="collapse">
                                <div class="card-body p-0">
                                    <table class="table table-sm table-bordered mb-0" style="font-size:.82rem;">
                                        <thead class="thead-light">
                                            <tr><th>CSV Column</th><th>Maps To</th></tr>
                                        </thead>
                                        <tbody>
                                            <tr><td>PE Number</td><td>PE / Entry Number</td></tr>
                                            <tr><td>SFS Username</td><td>System Login Email</td></tr>
                                            <tr><td>SFS Password</td><td>System Password</td></tr>
                                            <tr><td>First Name</td><td>First Name</td></tr>
                                            <tr><td>Middle Name</td><td>Middle Name</td></tr>
                                            <tr><td>Last Name</td><td>Last Name</td></tr>
                                            <tr><td>Email</td><td>Personal Email</td></tr>
                                            <tr><td>Gender</td><td>Gender</td></tr>
                                            <tr><td>Organization/Hospital</td><td>Hospital (matched by name)</td></tr>
                                            <tr><td>Country</td><td>Country (matched by name)</td></tr>
                                            <tr><td>COSECSA Programme</td><td>Programme (matched by name)</td></tr>
                                            <tr><td>Application Status</td><td>Status</td></tr>
                                            <tr><td>Exam Year</td><td>Exam Year</td></tr>
                                            <tr><td>Programme Start</td><td>Admission Year</td></tr>
                                            <tr><td>Programme Period</td><td>Programme Period</td></tr>
                                            <tr><td>Sponsor</td><td>Sponsor</td></tr>
                                            <tr><td>Invoice #</td><td>Invoice Number</td></tr>
                                            <tr><td>Invoice date</td><td>Invoice Date</td></tr>
                                            <tr><td>Status</td><td>Invoice Status</td></tr>
                                            <tr><td>Mode of Payment</td><td>Mode of Payment</td></tr>
                                            <tr><td>Amount Paid</td><td>Amount Paid</td></tr>
                                            <tr><td>Date Paid</td><td>Payment Date</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════════════════
     IMPORT REPORT MODAL — auto-opens after successful import
════════════════════════════════════════════════════════════════════════════ --}}
@if(session('import_done') && !empty($report))
@php $t = $report['totals']; @endphp

<div class="modal fade" id="reportModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">

            <div class="modal-header" style="background:#a02626;color:#fff;">
                <h5 class="modal-title">
                    <i class="fas fa-chart-bar mr-2"></i> Import Report
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body p-0">

                {{-- KPI Summary Bar --}}
                <div class="row no-gutters text-center" style="border-bottom:1px solid #dee2e6;">
                    <div class="col py-3" style="border-right:1px solid #dee2e6;">
                        <div style="font-size:1.9rem;font-weight:700;color:#333;">{{ $t['total'] }}</div>
                        <div class="text-muted small">Total Rows</div>
                    </div>
                    <div class="col py-3" style="border-right:1px solid #dee2e6;background:#f0fff4;">
                        <div style="font-size:1.9rem;font-weight:700;color:#28a745;">{{ $t['imported'] }}</div>
                        <div class="text-muted small">Imported</div>
                    </div>
                    <div class="col py-3" style="border-right:1px solid #dee2e6;background:#fffbea;">
                        <div style="font-size:1.9rem;font-weight:700;color:#0077b6;">{{ $t['updated'] }}</div>
                        <div class="text-muted small">Updated</div>
                    </div>
                    <div class="col py-3" style="border-right:1px solid #dee2e6;background:#fff5f5;">
                        <div style="font-size:1.9rem;font-weight:700;color:#a02626;">{{ $t['rejected'] }}</div>
                        <div class="text-muted small">Rejected</div>
                    </div>
                    <div class="col py-3" style="border-right:1px solid #dee2e6;background:#fff8e6;">
                        <div style="font-size:1.9rem;font-weight:700;color:#b45309;">{{ $t['pending'] }}</div>
                        <div class="text-muted small">Not Complete</div>
                    </div>
                    <div class="col py-3 {{ $t['errors'] > 0 ? 'border-right' : '' }}" style="border-right-color:#dee2e6;background:#f5f0ff;">
                        <div style="font-size:1.9rem;font-weight:700;color:#6f42c1;">{{ $t['incomplete'] }}</div>
                        <div class="text-muted small">Incomplete</div>
                    </div>
                    @if($t['errors'] > 0)
                    <div class="col py-3" style="background:#fff0f0;">
                        <div style="font-size:1.9rem;font-weight:700;color:#dc3545;">{{ $t['errors'] }}</div>
                        <div class="text-muted small">Errors</div>
                    </div>
                    @endif
                </div>

                {{-- Tabs --}}
                <ul class="nav nav-tabs px-3 pt-2" id="reportTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#pane-imported">
                            <i class="fas fa-check-circle text-success mr-1"></i>
                            Imported <span class="badge badge-success">{{ $t['imported'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#pane-updated">
                            <i class="fas fa-sync-alt mr-1" style="color:#0077b6;"></i>
                            Updated <span class="badge" style="background:#0077b6;color:#fff;">{{ $t['updated'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#pane-rejected">
                            <i class="fas fa-ban text-danger mr-1"></i>
                            Rejected <span class="badge badge-danger">{{ $t['rejected'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#pane-pending">
                            <i class="fas fa-clock mr-1" style="color:#b45309;"></i>
                            Not Complete <span class="badge" style="background:#b45309;color:#fff;">{{ $t['pending'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#pane-incomplete">
                            <i class="fas fa-exclamation-triangle mr-1" style="color:#6f42c1;"></i>
                            Incomplete <span class="badge" style="background:#6f42c1;color:#fff;">{{ $t['incomplete'] }}</span>
                        </a>
                    </li>
                    @if($t['errors'] > 0)
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#pane-errors">
                            <i class="fas fa-times-circle text-danger mr-1"></i>
                            Errors <span class="badge badge-danger">{{ $t['errors'] }}</span>
                        </a>
                    </li>
                    @endif
                </ul>

                <div class="tab-content px-3 pb-3 pt-2" style="max-height:420px;overflow-y:auto;">

                    {{-- Imported --}}
                    <div class="tab-pane fade show active" id="pane-imported">
                        @if($t['imported'] > 0)
                            @php $fuzzyCount = collect($report['imported'])->filter(fn($r) => !empty($r['note']))->count(); @endphp
                            <div class="alert alert-success py-2 mb-2" style="font-size:.85rem;">
                                <i class="fas fa-check-circle mr-1"></i>
                                <strong>{{ $t['imported'] }}</strong> trainee(s) successfully added to the system.
                                @if($fuzzyCount > 0)
                                    <span class="ml-2 badge badge-info">{{ $fuzzyCount }} fuzzy-matched</span>
                                @endif
                            </div>
                            <table class="table table-sm table-bordered table-hover" style="font-size:.82rem;">
                                <thead class="thead-light">
                                    <tr><th>#</th><th>Name</th><th>PE Number</th><th>Programme</th><th>Country</th><th>Email</th><th>Match Notes</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($report['imported'] as $i => $r)
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td>{{ $r['name'] }}</td>
                                        <td>{{ $r['pe'] ?: '—' }}</td>
                                        <td>{{ $r['programme'] }}</td>
                                        <td>{{ $r['country'] }}</td>
                                        <td>{{ $r['email'] ?: '—' }}</td>
                                        <td>
                                            @if(!empty($r['note']))
                                                <small class="text-info"><i class="fas fa-info-circle mr-1"></i>{{ $r['note'] }}</small>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-muted mt-3">No new trainees were imported.</p>
                        @endif
                    </div>

                    {{-- Updated --}}
                    <div class="tab-pane fade" id="pane-updated">
                        @if($t['updated'] > 0)
                            @php
                                $withChanges    = collect($report['updated'])->filter(fn($r) => !empty($r['changes']))->count();
                                $noChanges      = $t['updated'] - $withChanges;
                            @endphp
                            <div class="alert py-2 mb-2" style="background:#e8f4fd;border-color:#90cdf4;font-size:.85rem;">
                                <i class="fas fa-sync-alt mr-1" style="color:#0077b6;"></i>
                                <strong>{{ $t['updated'] }}</strong> existing trainee(s) found —
                                <strong>{{ $withChanges }}</strong> updated with new data,
                                <strong>{{ $noChanges }}</strong> already up to date.
                            </div>
                            <table class="table table-sm table-bordered table-hover" style="font-size:.82rem;">
                                <thead class="thead-light">
                                    <tr><th>#</th><th>Name</th><th>PE Number</th><th>Programme</th><th>Country</th><th>Fields Updated</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($report['updated'] as $i => $r)
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td>{{ $r['name'] }}</td>
                                        <td>{{ $r['pe'] ?: '—' }}</td>
                                        <td>{{ $r['programme'] }}</td>
                                        <td>{{ $r['country'] }}</td>
                                        <td>
                                            @if(!empty($r['changes']))
                                                @foreach($r['changes'] as $change)
                                                    <div style="font-size:.78rem;color:#0077b6;">
                                                        <i class="fas fa-arrow-right mr-1"></i>{{ $change }}
                                                    </div>
                                                @endforeach
                                            @else
                                                <span class="text-muted small">No changes needed</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-muted mt-3">No existing trainees were found in this file.</p>
                        @endif
                    </div>

                    {{-- Rejected --}}
                    <div class="tab-pane fade" id="pane-rejected">
                        @if($t['rejected'] > 0)
                            <div class="alert alert-danger py-2 mb-2" style="font-size:.85rem;">
                                <i class="fas fa-ban mr-1"></i>
                                <strong>{{ $t['rejected'] }}</strong> application(s) skipped — status is <em>Rejected</em>.
                            </div>
                            <table class="table table-sm table-bordered table-hover" style="font-size:.82rem;">
                                <thead class="thead-light">
                                    <tr><th>#</th><th>Name</th><th>PE Number</th><th>Programme</th><th>Country</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($report['rejected'] as $i => $r)
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td>{{ $r['name'] }}</td>
                                        <td>{{ $r['pe'] ?: '—' }}</td>
                                        <td>{{ $r['programme'] }}</td>
                                        <td>{{ $r['country'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-muted mt-3">No rejected applications found.</p>
                        @endif
                    </div>

                    {{-- Not Complete (Pending / Invoiced / Question / Application Received) --}}
                    <div class="tab-pane fade" id="pane-pending">
                        @if($t['pending'] > 0)
                            <div class="alert py-2 mb-2" style="background:#fff8e6;border-color:#f59e0b;font-size:.85rem;">
                                <i class="fas fa-clock mr-1" style="color:#b45309;"></i>
                                <strong>{{ $t['pending'] }}</strong> application(s) were not imported — status is not
                                <em>Complete</em>. Re-upload once their application has been finalised.
                            </div>
                            <table class="table table-sm table-bordered table-hover" style="font-size:.82rem;">
                                <thead class="thead-light">
                                    <tr><th>#</th><th>Name</th><th>PE Number</th><th>Programme</th><th>Country</th><th>Status</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($report['pending'] as $i => $r)
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td>{{ $r['name'] }}</td>
                                        <td>{{ $r['pe'] ?: '—' }}</td>
                                        <td>{{ $r['programme'] }}</td>
                                        <td>{{ $r['country'] }}</td>
                                        <td>
                                            @php $status = explode(' (', $r['reason'])[0]; $status = str_replace('Status: ', '', $status); @endphp
                                            <span class="badge badge-warning">{{ $status }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-muted mt-3">All applications had a Complete status.</p>
                        @endif
                    </div>

                    {{-- Incomplete / Unmatched --}}
                    <div class="tab-pane fade" id="pane-incomplete">
                        @if($t['incomplete'] > 0)
                            <div class="alert py-2 mb-2" style="background:#f3e8ff;border-color:#c084fc;font-size:.85rem;">
                                <i class="fas fa-exclamation-triangle mr-1" style="color:#6f42c1;"></i>
                                <strong>{{ $t['incomplete'] }}</strong> row(s) could not be imported.
                                These may need manual entry (e.g. hospital name doesn't match the database).
                            </div>
                            <table class="table table-sm table-bordered table-hover" style="font-size:.82rem;">
                                <thead class="thead-light">
                                    <tr><th>#</th><th>Name</th><th>PE Number</th><th>Programme</th><th>Country</th><th>Reason</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($report['incomplete'] as $i => $r)
                                    <tr>
                                        <td>{{ $i+1 }}</td>
                                        <td>{{ $r['name'] }}</td>
                                        <td>{{ $r['pe'] ?: '—' }}</td>
                                        <td>{{ $r['programme'] }}</td>
                                        <td>{{ $r['country'] }}</td>
                                        <td><small style="color:#6f42c1;">{{ $r['reason'] }}</small></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-muted mt-3">All rows had sufficient data.</p>
                        @endif
                    </div>

                    {{-- Errors --}}
                    @if($t['errors'] > 0)
                    <div class="tab-pane fade" id="pane-errors">
                        <div class="alert alert-danger py-2 mb-2" style="font-size:.85rem;">
                            <i class="fas fa-times-circle mr-1"></i>
                            <strong>{{ $t['errors'] }}</strong> row(s) threw an unexpected error.
                        </div>
                        <table class="table table-sm table-bordered" style="font-size:.82rem;">
                            <thead class="thead-light">
                                <tr><th>#</th><th>Name</th><th>PE Number</th><th>Error</th></tr>
                            </thead>
                            <tbody>
                                @foreach($report['errors'] as $i => $r)
                                <tr>
                                    <td>{{ $i+1 }}</td>
                                    <td>{{ $r['name'] }}</td>
                                    <td>{{ $r['pe'] ?: '—' }}</td>
                                    <td class="text-danger small">{{ $r['reason'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @endif

                </div>{{-- /tab-content --}}
            </div>{{-- /modal-body --}}

            <div class="modal-footer" style="background:#f8f9fa;">
                <a href="{{ url('admin/associates/trainees/trainees') }}"
                   class="btn btn-success">
                    <i class="fas fa-users mr-1"></i> View Trainees List
                </a>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>
@endif

@endsection

@push('styles')
<style>
    .custom-file-input ~ .custom-file-label::after {
        content: "Browse" !important;
        background-color: #a02626;
        color: #fff;
        border: none;
        padding: .375rem .75rem;
    }
    .nav-tabs .nav-link.active { font-weight: 600; border-bottom-color: #fff; }
    .table td, .table th { vertical-align: middle; }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function () {

    // File picker — update label + show preview strip
    $('#importFile').on('change', function () {
        var file = this.files[0];
        if (!file) return;
        $(this).next('.custom-file-label').text(file.name);
        $('#fileName').text(file.name);
        $('#fileSize').text((file.size / 1024).toFixed(1) + ' KB');
        $('#filePreview').removeClass('d-none');
    });

    // Show spinner, disable button on submit
    $('#importForm').on('submit', function () {
        $('#btnUpload').prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm mr-2" role="status"></span> Uploading&hellip;'
        );
        $('#uploadSpinner').removeClass('d-none');
    });

    // Auto-open report modal after import
    @if(session('import_done') && !empty($report))
        $('#reportModal').modal({ backdrop: 'static', keyboard: false });
    @endif

});
</script>
@endpush
