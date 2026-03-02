@extends('layout.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1>Import Fellows</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ url('admin/associates/fellows/list') }}">Fellows</a></li>
                        <li class="breadcrumb-item active">Import</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                {{-- Upload Card --}}
                <div class="col-md-6">
                    <div class="card card-primary">
                        <div class="card-header" style="background-color:#a02626;">
                            <h3 class="card-title"><i class="fas fa-upload mr-2"></i>Upload Fellows File</h3>
                        </div>
                        <form method="POST" action="{{ route('fellows.import.data') }}" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            <div class="card-body">
                                @if(session('success'))
                                    <div class="alert alert-success">{{ session('success') }}</div>
                                @endif
                                @if(session('error'))
                                    <div class="alert alert-danger">{{ session('error') }}</div>
                                @endif
                                <div class="form-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="customFile" name="file" required>
                                        <label class="custom-file-label" for="customFile">No file selected</label>
                                    </div>
                                    <small class="text-muted d-block mt-1">Accepted: .csv, .xls, .xlsx — Max 2MB</small>
                                </div>
                                <div class="alert alert-info py-2 mb-0" style="font-size:.82rem;">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    The first row must be a <strong>header row</strong> with exact column names as shown in the template.
                                    All new rows create new user accounts with password from the <code>password</code> column.
                                </div>
                            </div>
                            <div class="card-footer d-flex justify-content-between align-items-center">
                                <a href="{{ url('admin/associates/fellows/import/template') }}"
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-download mr-1"></i> Download Template
                                </a>
                                <button type="submit" class="btn btn-primary"
                                        style="background-color:#FEC503; border-color:#FEC503; color:#333; font-weight:700;">
                                    Upload &amp; Import <i class="fas fa-upload ml-1"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Column Guide Card --}}
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header" style="background:#fff8e1; border-bottom:2px solid #FEC503;">
                            <h3 class="card-title" style="color:#856404;"><i class="fas fa-table mr-2"></i>Required Column Headers</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive" style="max-height:480px; overflow-y:auto;">
                                <table class="table table-sm table-striped mb-0" style="font-size:.78rem;">
                                    <thead style="background:#f5f5f5;">
                                        <tr><th>Column Name</th><th>Required?</th><th>Example</th></tr>
                                    </thead>
                                    <tbody>
                                        <tr><td><code>firstname</code></td><td><span class="badge badge-danger">Yes</span></td><td>Lucy</td></tr>
                                        <tr><td><code>middlename</code></td><td>No</td><td>Anne</td></tr>
                                        <tr><td><code>lastname</code></td><td><span class="badge badge-danger">Yes</span></td><td>Kaomba</td></tr>
                                        <tr><td><code>email</code></td><td><span class="badge badge-danger">Yes</span></td><td>l.kaomba@hospital.mw</td></tr>
                                        <tr><td><code>password</code></td><td><span class="badge badge-danger">Yes</span></td><td>Fellow@2024</td></tr>
                                        <tr><td><code>gender</code></td><td>No</td><td>Female</td></tr>
                                        <tr><td><code>status</code></td><td>No</td><td>Active</td></tr>
                                        <tr><td><code>candidate_number</code></td><td>No</td><td>MW/2015/04</td></tr>
                                        <tr><td><code>category_id</code></td><td>No</td><td>5 (see note)</td></tr>
                                        <tr><td><code>programme_id</code></td><td>No</td><td>2</td></tr>
                                        <tr><td><code>country_id</code></td><td>No</td><td>1</td></tr>
                                        <tr><td><code>cosecsa_region</code></td><td>No</td><td>Eastern Africa</td></tr>
                                        <tr><td><code>phone_number</code></td><td>No</td><td>+265 999 123456</td></tr>
                                        <tr><td><code>personal_email</code></td><td>No</td><td>lucy@gmail.com</td></tr>
                                        <tr><td><code>second_email</code></td><td>No</td><td>lucy@other.com</td></tr>
                                        <tr><td><code>address</code></td><td>No</td><td>P.O. Box 100, Blantyre</td></tr>
                                        <tr><td><code>organization</code></td><td>No</td><td>Queen Elizabeth Central Hospital</td></tr>
                                        <tr><td><code>current_specialty</code></td><td>No</td><td>General Surgery</td></tr>
                                        <tr><td><code>admission_year</code></td><td>No</td><td>2015</td></tr>
                                        <tr><td><code>mcs_qualification_year</code></td><td>No</td><td>2016</td></tr>
                                        <tr><td><code>fellowship_year</code></td><td>No</td><td>2018</td></tr>
                                        <tr><td><code>is_promoted</code></td><td>No</td><td>1</td></tr>
                                        <tr><td><code>supervised_by</code></td><td>No</td><td>Dr W. Mulwafu</td></tr>
                                        <tr><td><code>registered_by</code></td><td>No</td><td>Secretariat</td></tr>
                                        <tr><td><code>secretariat_registration_date</code></td><td>No</td><td>2015-01-15</td></tr>
                                        <tr><td><code>sponsored_by</code></td><td>No</td><td>NORHED</td></tr>
                                        <tr><td><code>prog_entry_fee_year</code></td><td>No</td><td>2015</td></tr>
                                        <tr><td><code>prog_entry_mode_payment</code></td><td>No</td><td>Bank Transfer</td></tr>
                                        <tr><td><code>exam_fee_year</code></td><td>No</td><td>2016</td></tr>
                                        <tr><td><code>exam_fee_date_paid</code></td><td>No</td><td>2016-03-10</td></tr>
                                        <tr><td><code>exam_fee_amount_paid</code></td><td>No</td><td>500.00</td></tr>
                                        <tr><td><code>exam_fee_mode_payment</code></td><td>No</td><td>Bank Transfer</td></tr>
                                        <tr><td><code>exam_fee_payment_verified</code></td><td>No</td><td>1</td></tr>
                                        <tr><td><code>country_mcs_training</code></td><td>No</td><td>Malawi</td></tr>
                                        <tr><td><code>exam_year_upcoming</code></td><td>No</td><td>2026</td></tr>
                                        <tr><td><code>exam_year_previous</code></td><td>No</td><td>2025</td></tr>
                                        <tr><td><code>profile_image</code></td><td>No</td><td>(leave blank)</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer" style="font-size:.75rem; background:#fffdf0;">
                            <i class="fas fa-info-circle text-warning mr-1"></i>
                            <strong>category_id:</strong> 5=Fellow by Exam, 6=Foundation, 7=By Election, 8=Honorary(ASEA), 9=Overseas, 10=Honorary(COSECSA)
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
$(document).ready(function () {
    bsCustomFileInput.init();
});
</script>

<style>
.custom-file-input ~ .custom-file-label::after {
    content: "Browse" !important;
    background-color: #a02626;
    border: none;
    padding: 0.375rem 0.75rem;
    color: white;
}
</style>
@endsection
