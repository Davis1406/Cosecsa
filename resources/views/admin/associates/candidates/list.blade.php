@extends('layout.app')

@section('content')
<div class="wrapper">
    <div class="content-wrapper">

        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"></div>
                    <div class="col-sm-6 text-right">
                        <a href="{{ url('admin/associates/candidates/reports') }}"
                           class="btn btn-sm btn-outline-secondary mr-1">
                            <i class="fas fa-chart-bar mr-1"></i> Analytics
                        </a>
                        <a href="{{ url('admin/associates/candidates/import') }}"
                           class="btn btn-sm mr-1"
                           style="color:#333; background-color:#FEC503; border-color:#FEC503;">
                            <i class="fas fa-upload mr-1"></i> Upload Candidates
                        </a>
                        <a href="{{ url('admin/associates/candidates/add') }}"
                           class="btn btn-sm"
                           style="background-color:#a02626; border-color:#a02626; color:#fff;">
                            <i class="fas fa-user-plus mr-1"></i> Add New Candidate
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <div class="col-md-12">@include('_message')</div>

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
                                <label class="small mb-0 font-weight-bold">Programme / Exam Type</label>
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
                                    <option value="{{ $y }}" {{ $y == date('Y') ? 'selected' : '' }}>{{ $y }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-md-2 px-1 mb-1">
                                <label class="small mb-0 font-weight-bold">Gender</label>
                                <select id="filterGender" class="form-control form-control-sm">
                                    <option value="">All Genders</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-2 pl-1 mb-1">
                                <label class="small mb-0 font-weight-bold">Fee Paid</label>
                                <select id="filterFeePaid" class="form-control form-control-sm">
                                    <option value="">All</option>
                                    <option value="Yes">Paid</option>
                                    <option value="No">Not Paid</option>
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
                                <h3 class="card-title mb-0">Candidates List {{ date('Y') }}</h3>
                                <small class="text-muted" id="filteredCount"></small>
                            </div>
                            <div class="card-body">
                                <table id="candidatestable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>PEN</th>
                                            <th>Cand. No.</th>
                                            <th>Exam Type</th>
                                            <th>Hospital</th>
                                            <th>Country</th>
                                            <th>Gender</th>
                                            <th>Fee Paid</th>
                                            <th>Action</th>
                                            {{-- Hidden columns for export / search --}}
                                            <th>Email</th>
                                            <th>Repeat P1</th>
                                            <th>Repeat P2</th>
                                            <th>MMed</th>
                                            <th>Sponsor</th>
                                            <th>Exam Year</th>
                                            <th>Mode of Payment</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($getRecord as $value)
                                        @php $cid = $value->candidates_id ?? 0; @endphp
                                        <tr data-country="{{ $value->country_name ?? '' }}"
                                            data-programme="{{ $value->programme_name ?? '' }}"
                                            data-gender="{{ $value->gender ?? '' }}"
                                            data-year="{{ $value->exam_year ?? '' }}"
                                            data-feepaid="{{ $value->fee_paid ?? 'No' }}">
                                            <td class="row-num"></td>
                                            {{-- Clickable name → view --}}
                                            <td>
                                                <a href="{{ url('admin/associates/candidates/view/' . $cid) }}"
                                                   class="candidate-name-link font-weight-500">
                                                    {{ $value->name ?? '-' }}
                                                </a>
                                            </td>
                                            <td>{{ $value->entry_number ?? '-' }}</td>
                                            <td>
                                                @if(!empty($value->candidate_id))
                                                    <span class="badge badge-secondary" style="font-size:.78rem;letter-spacing:.5px;">{{ $value->candidate_id }}</span>
                                                @else
                                                    <span class="text-muted" style="font-size:.78rem;">—</span>
                                                @endif
                                            </td>
                                            <td>{{ $value->programme_name ?? '-' }}</td>
                                            <td>{{ $value->hospital_name ?? '-' }}</td>
                                            <td>{{ $value->country_name ?? '-' }}</td>
                                            <td>
                                                @if(($value->gender ?? '') === 'Female')
                                                    <span>Female</span>
                                                @elseif(($value->gender ?? '') === 'Male')
                                                    <span>Male</span>
                                                @else -
                                                @endif
                                            </td>
                                            <td>
                                                @if(($value->fee_paid ?? 'No') === 'Yes')
                                                    <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Paid</span>
                                                @else
                                                    <span class="badge badge-danger">Unpaid</span>
                                                @endif
                                            </td>
                                            {{-- Dropdown action button --}}
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
                                                           href="{{ url('admin/associates/candidates/view/' . $cid) }}">
                                                            <i class="fas fa-eye text-info mr-2"></i> View
                                                        </a>
                                                        <a class="dropdown-item"
                                                           href="{{ url('admin/associates/candidates/edit/' . $cid) }}">
                                                            <i class="fas fa-edit text-warning mr-2"></i> Edit
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-danger"
                                                           href="{{ url('admin/associates/candidates/delete/' . ($value->c_id ?? 0)) }}"
                                                           onclick="return confirm('Delete this candidate?')">
                                                            <i class="fas fa-trash mr-2"></i> Delete
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $value->personal_email ?? '-' }}</td>
                                            <td>{{ $value->repeat_paper_one ?? 'No' }}</td>
                                            <td>{{ $value->repeat_paper_two ?? 'No' }}</td>
                                            <td>{{ $value->mmed ?? 'No' }}</td>
                                            <td>{{ $value->sponsor ?? '-' }}</td>
                                            <td>{{ $value->exam_year ?? '-' }}</td>
                                            <td>{{ $value->mode_of_payment ?? '-' }}</td>
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

@push('styles')
<style>
    #candidatestable td { vertical-align: middle; }
    .candidate-name-link {
        color: #333;
        text-decoration: none;
        font-weight: 500;
    }
    .candidate-name-link:hover {
        color: #a02626;
        text-decoration: underline;
    }
    .action-btn {
        padding: 2px 8px;
        line-height: 1.4;
        border-radius: 4px;
    }
    .action-btn:hover { background-color: #f0f0f0; }
    .dropdown-menu { min-width: 130px; font-size: .875rem; }
    .dropdown-item { padding: 6px 14px; }
    .dropdown-item:hover { background-color: #f8f0f0; }
    .paginate_button.active>.page-link { background-color: #a02626 !important; border-color: #a02626 !important; color: white; }
    .paginate_button>.page-link { color: #a02626; }
    .paginate_button>.page-link:focus, .paginate_button.active>.page-link:focus { box-shadow: none !important; outline: none !important; }
    .badge-pill { padding: .35em .65em; font-size: .7rem; }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function () {

    // Custom DataTable search filter
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'candidatestable') return true;
        var $row     = $(settings.nTable).DataTable().row(dataIndex).node();
        var country  = $('#filterCountry').val();
        var programme= $('#filterProgramme').val();
        var year     = $('#filterYear').val();
        var gender   = $('#filterGender').val();
        var feePaid  = $('#filterFeePaid').val();

        if (country   && $($row).data('country')   !== country)   return false;
        if (programme && $($row).data('programme') !== programme)  return false;
        if (year      && String($($row).data('year')) !== year)    return false;
        if (gender    && $($row).data('gender')    !== gender)     return false;
        if (feePaid   && $($row).data('feepaid')   !== feePaid)    return false;
        return true;
    });

    $('#filterCountry, #filterProgramme, #filterYear, #filterGender, #filterFeePaid').on('change', function () {
        var dt = $('#candidatestable').DataTable();
        dt.draw();
        $('#filteredCount').text('Showing ' + dt.page.info().recordsDisplay + ' of ' + dt.page.info().recordsTotal);
    });

    $('#btnClearFilters').on('click', function () {
        $('#filterCountry, #filterProgramme, #filterYear, #filterGender, #filterFeePaid').val('');
        $('#candidatestable').DataTable().draw();
        $('#filteredCount').text('');
    });

    // Dropdown init + outside-click handling is managed in custom.js
});
</script>
@endpush
