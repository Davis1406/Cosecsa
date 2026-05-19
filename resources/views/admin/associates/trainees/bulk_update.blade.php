@extends('layout.app')

@section('content')
<div class="wrapper">
    <div class="content-wrapper">

        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Bulk Update Trainees</h1>
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
                    <div class="col-md-8">

                        {{-- Upload Card --}}
                        <div class="card card-outline" style="border-top:3px solid #a02626;">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-file-excel mr-2" style="color:#1d6f42;"></i>
                                    Upload COSECSA Trainees Excel (SFS Format)
                                </h3>
                            </div>
                            <div class="card-body">

                                <div class="mb-4 p-3 rounded" style="background:#1c4f82;color:#fff;border-left:4px solid #4a9fd4;">
                                    <div class="mb-1">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        <strong>What this does:</strong> Updates <em>existing</em> trainee records from the SFS master Excel file.
                                    </div>
                                    <ul class="mb-0 pl-4" style="color:#d6eaf8;">
                                        <li>Matches trainees by <strong>PE Number</strong> (entry_number)</li>
                                        <li>Updates name, login email &amp; password, programme, country, hospital</li>
                                        <li><strong>MCS PE Fees</strong> → stored in the trainees table (admission fee)</li>
                                        <li><strong>MCS Exam Fees</strong> → stored in the candidates table (2027 exam sitting)</li>
                                        <li><strong>MCS Repeat Fees</strong> → stored in candidates table, marked as repeat</li>
                                        <li>Trainees whose PE Number is not found are listed in the report</li>
                                        <li><em>No new trainee records are created</em> — update only</li>
                                    </ul>
                                </div>

                                <form id="bulkForm" method="POST"
                                      action="{{ route('trainees.bulk.update.process') }}"
                                      enctype="multipart/form-data">
                                    @csrf

                                    <div class="form-group">
                                        <label class="font-weight-bold">Select Excel File</label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" id="bulkFile"
                                                   name="file" accept=".xls,.xlsx" required>
                                            <label class="custom-file-label" for="bulkFile">
                                                Choose file&hellip;
                                            </label>
                                        </div>
                                        <small class="text-muted">Accepted: .xls, .xlsx &mdash; Max 20 MB</small>
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
                                        <i class="fas fa-upload mr-2"></i> Upload &amp; Update
                                    </button>

                                    <div id="uploadSpinner" class="text-center mt-3 d-none">
                                        <div class="spinner-border text-danger" role="status"></div>
                                        <div class="mt-2 text-muted small">
                                            Processing rows &mdash; this may take a minute&hellip;
                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>

                        {{-- Expected format reference --}}
                        <div class="card card-outline card-secondary">
                            <div class="card-header" style="cursor:pointer;"
                                 data-toggle="collapse" data-target="#colMap">
                                <h3 class="card-title mb-0">
                                    <i class="fas fa-table mr-2"></i> Expected Excel Format
                                    <i class="fas fa-chevron-down float-right mt-1" style="font-size:.8rem;"></i>
                                </h3>
                            </div>
                            <div id="colMap" class="collapse show">
                                <div class="card-body p-0">

                                    <div class="px-3 pt-3 pb-1">
                                        <p class="text-muted small mb-1">
                                            The file must have <strong>two header rows</strong>: row 1 contains section
                                            labels, row 2 contains column names. Data starts on row 3.
                                        </p>
                                    </div>

                                    <table class="table table-sm table-bordered mb-0" style="font-size:.82rem;">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Section (Row 1)</th>
                                                <th>Column (Row 2)</th>
                                                <th>Updates</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="table-light">
                                                <td rowspan="12"><strong>Trainee Details</strong></td>
                                                <td>PEN</td>
                                                <td><span class="badge badge-primary">Match Key</span> — entry_number</td>
                                            </tr>
                                            <tr><td>SFS Username</td><td>Login e-mail (users.email)</td></tr>
                                            <tr><td>SFS Password</td><td>Login password (hashed)</td></tr>
                                            <tr><td>First Name</td><td>firstname</td></tr>
                                            <tr><td>Middle Name</td><td>middlename</td></tr>
                                            <tr><td>Last Name</td><td>lastname</td></tr>
                                            <tr><td>Gender</td><td>gender</td></tr>
                                            <tr><td>Organisation</td><td>hospital_id (fuzzy name match)</td></tr>
                                            <tr><td>Country</td><td>country_id (name match)</td></tr>
                                            <tr><td>Email</td><td>personal_email</td></tr>
                                            <tr><td>Exam Type</td><td>programme_id (name match)</td></tr>
                                            <tr><td>Exam Year (upcoming)</td><td>exam_year</td></tr>

                                            <tr class="table-warning">
                                                <td rowspan="2"><strong>MCS PE Fees</strong></td>
                                                <td>Date Paid</td>
                                                <td>trainees.payment_date</td>
                                            </tr>
                                            <tr class="table-warning">
                                                <td>Amount Paid</td>
                                                <td>trainees.amount_paid + fee_paid = Yes</td>
                                            </tr>

                                            <tr class="table-info">
                                                <td rowspan="3"><strong>MCS Exam Fees</strong></td>
                                                <td>Date Paid</td>
                                                <td>candidates.payment_date (exam_year=2027)</td>
                                            </tr>
                                            <tr class="table-info">
                                                <td>Mode of Payment</td>
                                                <td>candidates.mode_of_payment</td>
                                            </tr>
                                            <tr class="table-info">
                                                <td>Amount Paid</td>
                                                <td>candidates.amount_paid + fee_paid = Yes</td>
                                            </tr>

                                            <tr class="table-danger" style="background:#fff0f0;">
                                                <td rowspan="3"><strong>MCS Repeat Fees</strong></td>
                                                <td>Date Paid</td>
                                                <td>candidates.payment_date (repeat_paper_one = Yes)</td>
                                            </tr>
                                            <tr class="table-danger" style="background:#fff0f0;">
                                                <td>Mode of Payment</td>
                                                <td>candidates.mode_of_payment</td>
                                            </tr>
                                            <tr class="table-danger" style="background:#fff0f0;">
                                                <td>Amount Paid</td>
                                                <td>candidates.amount_paid (repeat exam fee)</td>
                                            </tr>
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
     RESULT MODAL — auto-opens after successful bulk update
════════════════════════════════════════════════════════════════════════════ --}}
@if(session('bulk_done') && !empty($report))
@php $t = $report['totals']; @endphp

<div class="modal fade" id="reportModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">

            <div class="modal-header" style="background:#a02626;color:#fff;">
                <h5 class="modal-title">
                    <i class="fas fa-chart-bar mr-2"></i> Bulk Update Report
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body p-0">

                {{-- KPI Summary Bar --}}
                <div class="row no-gutters text-center" style="border-bottom:1px solid #dee2e6;">
                    <div class="col py-3" style="border-right:1px solid #dee2e6;background:#f0fff4;">
                        <div style="font-size:2rem;font-weight:700;color:#28a745;">{{ $t['updated'] }}</div>
                        <div class="text-muted small">Trainees Updated</div>
                    </div>
                    <div class="col py-3" style="border-right:1px solid #dee2e6;background:#e8f4fd;">
                        <div style="font-size:2rem;font-weight:700;color:#0077b6;">{{ $t['examUpdated'] }}</div>
                        <div class="text-muted small">Exam Fee Records</div>
                    </div>
                    <div class="col py-3" style="border-right:1px solid #dee2e6;background:#fff8e6;">
                        <div style="font-size:2rem;font-weight:700;color:#b45309;">{{ $t['repeatUpdated'] }}</div>
                        <div class="text-muted small">Repeat Fee Records</div>
                    </div>
                    <div class="col py-3" style="background:#fff5f5;">
                        <div style="font-size:2rem;font-weight:700;color:#a02626;">{{ $t['notFound'] }}</div>
                        <div class="text-muted small">PE Not Found</div>
                    </div>
                </div>

                {{-- Tabs --}}
                <ul class="nav nav-tabs px-3 pt-2" id="reportTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#pane-updated">
                            <i class="fas fa-check-circle text-success mr-1"></i>
                            Updated <span class="badge badge-success">{{ $t['updated'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#pane-exam">
                            <i class="fas fa-file-invoice-dollar mr-1" style="color:#0077b6;"></i>
                            Exam Fees <span class="badge" style="background:#0077b6;color:#fff;">{{ $t['examUpdated'] }}</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#pane-repeat">
                            <i class="fas fa-redo mr-1" style="color:#b45309;"></i>
                            Repeat Fees <span class="badge" style="background:#b45309;color:#fff;">{{ $t['repeatUpdated'] }}</span>
                        </a>
                    </li>
                    @if($t['notFound'] > 0)
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#pane-notfound">
                            <i class="fas fa-exclamation-triangle text-danger mr-1"></i>
                            Not Found <span class="badge badge-danger">{{ $t['notFound'] }}</span>
                        </a>
                    </li>
                    @endif
                </ul>

                <div class="tab-content px-3 pb-3 pt-2" style="max-height:420px;overflow-y:auto;">

                    {{-- Updated Trainees --}}
                    <div class="tab-pane fade show active" id="pane-updated">
                        @if($t['updated'] > 0)
                            <div class="alert alert-success py-2 mb-2" style="font-size:.85rem;">
                                <i class="fas fa-check-circle mr-1"></i>
                                <strong>{{ $t['updated'] }}</strong> trainee record(s) updated successfully
                                (trainees + users tables).
                            </div>
                            <table class="table table-sm table-bordered table-hover" style="font-size:.82rem;">
                                <thead class="thead-light">
                                    <tr><th>#</th><th>PE Number</th><th>Name</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($report['updated'] as $i => $r)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td><code>{{ $r['pen'] }}</code></td>
                                        <td>{{ $r['name'] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-muted mt-3">No trainees were updated.</p>
                        @endif
                    </div>

                    {{-- Exam Fee Records --}}
                    <div class="tab-pane fade" id="pane-exam">
                        @if($t['examUpdated'] > 0)
                            <div class="alert py-2 mb-2" style="background:#e8f4fd;border-color:#90cdf4;font-size:.85rem;">
                                <i class="fas fa-file-invoice-dollar mr-1" style="color:#0077b6;"></i>
                                <strong>{{ $t['examUpdated'] }}</strong> candidates record(s) updated/created
                                with 2027 exam fee data.
                            </div>
                            <table class="table table-sm table-bordered table-hover" style="font-size:.82rem;">
                                <thead class="thead-light">
                                    <tr><th>#</th><th>PE Number</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($report['examUpdated'] as $i => $pen)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td><code>{{ $pen }}</code></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-muted mt-3">No exam fee records in this file.</p>
                        @endif
                    </div>

                    {{-- Repeat Fee Records --}}
                    <div class="tab-pane fade" id="pane-repeat">
                        @if($t['repeatUpdated'] > 0)
                            <div class="alert py-2 mb-2" style="background:#fff8e6;border-color:#f59e0b;font-size:.85rem;">
                                <i class="fas fa-redo mr-1" style="color:#b45309;"></i>
                                <strong>{{ $t['repeatUpdated'] }}</strong> trainee(s) with repeat exam fee data
                                &mdash; candidates records marked <code>repeat_paper_one = Yes</code>.
                            </div>
                            <table class="table table-sm table-bordered table-hover" style="font-size:.82rem;">
                                <thead class="thead-light">
                                    <tr><th>#</th><th>PE Number</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($report['repeatUpdated'] as $i => $pen)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td><code>{{ $pen }}</code></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-muted mt-3">No repeat fee records in this file.</p>
                        @endif
                    </div>

                    {{-- Not Found --}}
                    @if($t['notFound'] > 0)
                    <div class="tab-pane fade" id="pane-notfound">
                        <div class="alert alert-danger py-2 mb-2" style="font-size:.85rem;">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            <strong>{{ $t['notFound'] }}</strong> PE Number(s) were not found in the trainees table.
                            These rows were skipped.
                        </div>
                        <table class="table table-sm table-bordered" style="font-size:.82rem;">
                            <thead class="thead-light">
                                <tr><th>#</th><th>PE Number (not matched)</th></tr>
                            </thead>
                            <tbody>
                                @foreach($report['notFound'] as $i => $pen)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td class="text-danger"><code>{{ $pen }}</code></td>
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
                <a href="{{ url('admin/associates/candidates/list') }}"
                   class="btn btn-primary">
                    <i class="fas fa-user-graduate mr-1"></i> View Candidates
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
    .nav-tabs .nav-link.active { font-weight: 600; }
    .table td, .table th { vertical-align: middle; }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function () {
    $('#bulkFile').on('change', function () {
        var file = this.files[0];
        if (!file) return;
        $(this).next('.custom-file-label').text(file.name);
        $('#fileName').text(file.name);
        $('#fileSize').text((file.size / 1024).toFixed(1) + ' KB');
        $('#filePreview').removeClass('d-none');
    });

    $('#bulkForm').on('submit', function () {
        $('#btnUpload').prop('disabled', true).html(
            '<span class="spinner-border spinner-border-sm mr-2" role="status"></span> Updating&hellip;'
        );
        $('#uploadSpinner').removeClass('d-none');
    });

    @if(session('bulk_done') && !empty($report))
        $('#reportModal').modal({ backdrop: 'static', keyboard: false });
    @endif
});
</script>
@endpush
