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
                                            <th>Exam Type</th>
                                            <th>Hospital</th>
                                            <th>Country</th>
                                            <th>Gender</th>
                                            <th>Fee Paid</th>
                                            <th>Invoice #</th>
                                            <th>Amount</th>
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
                                        <tr data-country="{{ $value->country_name ?? '' }}"
                                            data-programme="{{ $value->programme_name ?? '' }}"
                                            data-gender="{{ $value->gender ?? '' }}"
                                            data-year="{{ $value->exam_year ?? '' }}"
                                            data-feepaid="{{ $value->fee_paid ?? 'No' }}">
                                            <td class="row-num"></td>
                                            <td>{{ $value->name ?? '-' }}</td>
                                            <td>{{ $value->entry_number ?? '-' }}</td>
                                            <td>
                                                @php $prog = $value->programme_name ?? '-'; @endphp
                                                <span>
                                                    {{ $prog }}
                                                </span>
                                            </td>
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
                                            <td style="font-size:.8rem;">{{ $value->invoice_number ?? '-' }}</td>
                                            <td>
                                                @if(!empty($value->invoice_amount))
                                                    ${{ number_format($value->invoice_amount) }}
                                                @elseif(!empty($value->amount_paid))
                                                    ${{ number_format($value->amount_paid) }}
                                                @else -
                                                @endif
                                            </td>
                                            <td>
                                                <a href="#" class="action-icon" data-toggle="popover" data-html="true" data-content='
                                                    <a href="{{ url("admin/associates/candidates/view/" . ($value->candidates_id ?? 0)) }}">
                                                        <i class="fa fa-eye action-icon"></i> View
                                                    </a>
                                                    <a href="{{ url("admin/associates/candidates/edit/" . ($value->candidates_id ?? 0)) }}">
                                                        <i class="fa fa-edit action-icon"></i> Edit
                                                    </a>
                                                    <a href="{{ url("admin/associates/candidates/delete/" . ($value->c_id ?? 0)) }}"
                                                       onclick="return confirm(&quot;Delete this candidate?&quot;)">
                                                        <i class="fa fa-trash action-icon"></i> Delete
                                                    </a>'>
                                                    <i class="fa fa-bars" style="color:#5a6268"></i>
                                                </a>
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
    .popover-content a, .popover-body a {
        display: block; padding: 5px 10px; color: #5a6268;
        text-decoration: none; border-radius: 3px; margin-bottom: 2px; transition: all 0.3s ease;
    }
    .popover-content a:hover, .popover-body a:hover {
        background-color: #a02626 !important; color: #fff !important;
    }
    .popover-content a i, .popover-body a i { margin-right: 6px; color: inherit; }
    .popover-content a:hover i, .popover-body a:hover i { color: #fff !important; }
    .popover-header { background-color: #a02626; color: #fff; border-bottom-color: #a02626; }
    .action-icon { cursor: pointer; transition: color 0.3s ease; }
    .action-icon:hover { color: #a02626 !important; }
    .paginate_button.active>.page-link { background-color: #a02626 !important; border-color: #a02626 !important; color: white; }
    .paginate_button>.page-link { color: #a02626; }
    .paginate_button>.page-link:focus, .paginate_button.active>.page-link:focus { box-shadow: none !important; outline: none !important; }
    #candidatestable td { vertical-align: middle; }
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

    // Re-init popovers after draw
    $(document).on('draw.dt', '#candidatestable', function () {
        $('[data-toggle="popover"]').each(function () {
            if (!$(this).data('bs.popover')) {
                $(this).popover({ trigger: 'focus', html: true });
            }
        });
    });
});
</script>
@endpush
