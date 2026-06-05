@extends('layout.app')

@section('content')
    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6"></div>
                        <div class="col-sm-6 text-right">
                            <a href="{{ url('admin/associates/trainees/reports') }}" class="btn btn-sm btn-outline-secondary mr-1">
                                <span class="fas fa-chart-bar mr-1"></span> Analytics
                            </a>
                            <a href="{{ url('admin/associates/trainees/bulk-update') }}" class="btn btn-sm mr-1"
                                style="color:#fff; background-color: #1d6f42; border-color: #1d6f42;">
                                <span class="fas fa-file-excel mr-1"></span> Bulk Update
                            </a>
                            <a href="{{ url('admin/associates/trainees/import') }}" class="btn btn-sm mr-1"
                                style="color:black; background-color: #FEC503; border-color: #FEC503;">
                                <span class="fas fa-upload mr-1"></span> Upload Trainees
                            </a>
                            <a href="{{ url('admin/associates/trainees/add') }}" class="btn btn-sm"
                                style="background-color: #a02626; border-color: #a02626; color:#fff;">
                                <span class="fas fa-user-plus mr-1"></span> Add New Trainee
                            </a>
                        </div>
                    </div>
                </div>
            </section>

            <div class="col-md-12">
                @include('_message')
            </div>

            <section class="content">
                <div class="container-wrapper">

                    {{-- Filter Bar --}}
                    <div class="card card-outline card-secondary mb-2 shadow-sm">
                        <div class="card-body py-2">
                            <div class="row align-items-end">
                                <div class="col-6 col-md-2 pr-1 mb-1">
                                    <label class="small mb-0 font-weight-bold">Country</label>
                                    <select id="filterCountry" class="form-control form-control-sm">
                                        <option value="">All Countries</option>
                                        @foreach($filterCountries as $c)
                                        <option value="{{ $c }}">{{ $c }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 col-md-3 px-1 mb-1">
                                    <label class="small mb-0 font-weight-bold">Programme</label>
                                    <select id="filterProgramme" class="form-control form-control-sm">
                                        <option value="">All Programmes</option>
                                        @foreach($filterProgrammes as $p)
                                        <option value="{{ $p }}">{{ $p }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 col-md-2 px-1 mb-1">
                                    <label class="small mb-0 font-weight-bold">Exam Year</label>
                                    <select id="filterYear" class="form-control form-control-sm">
                                        <option value="">All Years</option>
                                        @foreach($filterYears as $y)
                                        <option value="{{ $y }}">{{ $y }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 col-md-2 px-1 mb-1">
                                    <label class="small mb-0 font-weight-bold">Status</label>
                                    <select id="filterStatus" class="form-control form-control-sm">
                                        <option value="">All Statuses</option>
                                        @foreach($filterStatuses as $s)
                                        <option value="{{ $s }}">{{ $s }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 col-md-2 pl-1 mb-1">
                                    <label class="small mb-0 font-weight-bold">Gender</label>
                                    <select id="filterGender" class="form-control form-control-sm">
                                        <option value="">All Genders</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                            </div>
                            <div class="text-right mt-1">
                                <button id="btnClearFilters" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-times mr-1"></i>Clear Filters
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h3 class="card-title mb-0">Trainees List</h3>
                                    <small class="text-muted" id="filteredCount"></small>
                                </div>
                                <div class="card-body">
                                    <table id="traineestable" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Gender</th>
                                                <th>Admission Number</th>
                                                <th>Email</th>
                                                <th>Programme</th>
                                                <th>Hospital Name</th>
                                                <th>Country</th>
                                                <th>Trainee Status</th>
                                                {{-- Hidden columns for export --}}
                                                <th>SFS Username</th>
                                                <th>SFS Password</th>
                                                <th>Admission Letter Status</th>
                                                <th>Invitation Letter Status</th>
                                                <th>Admission Year</th>
                                                <th>Programme Year</th>
                                                <th>Exam Year</th>
                                                <th>Programme Duration</th>
                                                <th>Invoice Number</th>
                                                <th>Invoice Date</th>
                                                <th>Invoice Status</th>
                                                <th>Sponsor</th>
                                                <th>Mode of Payment</th>
                                                <th>Amount Paid</th>
                                                <th>Date Paid</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($getRecord as $value)
                                                <tr data-country="{{ $value->country_name ?? '' }}"
                                                    data-programme="{{ $value->programme_name ?? '' }}"
                                                    data-year="{{ $value->exam_year ?? '' }}"
                                                    data-status="{{ $value->status ?? '' }}"
                                                    data-gender="{{ $value->gender ?? '' }}">
                                                    <td class="row-num"></td>
                                                    <td>
                                                        <a href="{{ url('admin/associates/trainees/view/' . $value->trainee_id) }}"
                                                           class="trainee-name-link font-weight-500">
                                                            {{ $value->name }}
                                                        </a>
                                                    </td>
                                                    <td>{{ $value->gender }}</td>
                                                    <td>{{ $value->entry_number }}</td>
                                                    <td>{{ $value->personal_email }}</td>
                                                    <td>{{ $value->programme_name }}</td>
                                                    <td>{{ $value->hospital_name }}</td>
                                                    <td>{{ $value->country_name }}</td>
                                                    <td>{{ $value->status }}</td>
                                                    {{-- Hidden columns --}}
                                                    <td>{{ $value->user_email }}</td>
                                                    <td>{{ $value->user_password }}</td>
                                                    <td>{{ $value->admission_letter_status }}</td>
                                                    <td>{{ $value->invitation_letter_status }}</td>
                                                    <td>{{ $value->admission_year }}</td>
                                                    <td>{{ $value->programme_year }}</td>
                                                    <td>{{ $value->exam_year }}</td>
                                                    <td>{{ $value->programme_period }} Years</td>
                                                    <td>{{ $value->invoice_number }}</td>
                                                    <td>{{ $value->invoice_date }}</td>
                                                    <td>{{ $value->invoice_status }}</td>
                                                    <td>{{ $value->sponsor }}</td>
                                                    <td>{{ $value->mode_of_payment }}</td>
                                                    <td>{{ $value->amount_paid }}</td>
                                                    <td>{{ $value->payment_date }}</td>
                                                    {{-- Dropdown action --}}
                                                    <td class="text-center" style="white-space:nowrap;">
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-light border dropdown-toggle action-btn"
                                                                    type="button"
                                                                    data-toggle="dropdown"
                                                                    aria-haspopup="true"
                                                                    aria-expanded="false">
                                                                <i class="fas fa-ellipsis-v"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                                                <a class="dropdown-item"
                                                                   href="{{ url('admin/associates/trainees/view/' . $value->trainee_id) }}">
                                                                    <i class="fas fa-eye text-info mr-2"></i> View
                                                                </a>
                                                                <a class="dropdown-item"
                                                                   href="{{ url('admin/associates/trainees/edit/' . $value->trainee_id) }}">
                                                                    <i class="fas fa-edit text-warning mr-2"></i> Edit
                                                                </a>
                                                                <div class="dropdown-divider"></div>
                                                                <a class="dropdown-item text-danger"
                                                                   href="{{ url('admin/associates/trainees/delete/' . $value->t_id) }}"
                                                                   onclick="return confirm('Delete this trainee?')">
                                                                    <i class="fas fa-trash mr-2"></i> Delete
                                                                </a>
                                                            </div>
                                                        </div>
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
                </div>
            </section>
        </div>
    </div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'traineestable') return true;
        var $row      = $(settings.nTable).DataTable().row(dataIndex).node();
        var country   = $('#filterCountry').val();
        var programme = $('#filterProgramme').val();
        var year      = $('#filterYear').val();
        var status    = $('#filterStatus').val();
        var gender    = $('#filterGender').val();

        if (country   && $($row).data('country')   !== country)              return false;
        if (programme && $($row).data('programme') !== programme)             return false;
        if (year      && String($($row).data('year'))  !== String(year))      return false;
        if (status    && $($row).data('status')    !== status)                return false;
        if (gender    && $($row).data('gender')    !== gender)                return false;
        return true;
    });

    $('#filterCountry, #filterProgramme, #filterYear, #filterStatus, #filterGender').on('change', function () {
        var dt = $('#traineestable').DataTable();
        dt.draw();
        $('#filteredCount').text('Showing ' + dt.page.info().recordsDisplay + ' of ' + dt.page.info().recordsTotal);
    });

    $('#btnClearFilters').on('click', function () {
        $('#filterCountry, #filterProgramme, #filterYear, #filterStatus, #filterGender').val('');
        $('#traineestable').DataTable().draw();
        $('#filteredCount').text('');
    });
});
</script>
@endpush

@push('styles')
<style>
    #traineestable td { vertical-align: middle; }
    .trainee-name-link {
        color: #333;
        text-decoration: none;
        font-weight: 500;
    }
    .trainee-name-link:hover {
        color: #a02626;
        text-decoration: underline;
    }
    .action-btn { padding: 2px 8px; line-height: 1.4; border-radius: 4px; }
    .action-btn:hover { background-color: #f0f0f0; }
    .dropdown-menu { min-width: 130px; font-size: .875rem; }
    .dropdown-item { padding: 6px 14px; }
    .dropdown-item:hover { background-color: #f8f0f0; }
    .paginate_button.active>.page-link { background-color: #a02626 !important; border-color: #a02626 !important; color: white; }
    .paginate_button>.page-link { color: #a02626; }
    .paginate_button>.page-link:focus, .paginate_button.active>.page-link:focus { box-shadow: none !important; outline: none !important; }
</style>
@endpush
