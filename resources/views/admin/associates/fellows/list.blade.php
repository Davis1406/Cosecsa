@extends('layout.app')

@section('content')
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"></div>
                    <div class="col-sm-6 text-right">
                        <a href="{{ url('admin/associates/fellows/reports') }}"
                           class="btn btn-info mr-2">
                            <span class="fas fa-chart-bar mr-1"></span> Analytics
                        </a>
                        <a href="{{ url('admin/associates/fellows/import_fellows') }}"
                           class="btn btn-secondary mr-2"
                           style="color:#333; background-color:#FEC503; border-color:#FEC503;">
                            <span class="fas fa-upload mr-1"></span> Upload Fellows
                        </a>
                        <a href="{{ url('admin/associates/fellows/add') }}"
                           class="btn btn-primary"
                           style="background-color:#a02626; border-color:#a02626;">
                            <span class="fas fa-user-plus mr-1"></span> Add New Fellow
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
                        <div class="row align-items-end" id="fellowFilters">
                            <div class="col-6 col-md-2 pr-1 mb-1">
                                <label class="small mb-0 font-weight-bold">Programme</label>
                                <select id="filterProgramme" class="form-control form-control-sm">
                                    <option value="">All Programmes</option>
                                    @foreach($filterProgrammes as $p)
                                    <option value="{{ $p }}">{{ $p }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-md-2 px-1 mb-1">
                                <label class="small mb-0 font-weight-bold">Country</label>
                                <select id="filterCountry" class="form-control form-control-sm">
                                    <option value="">All Countries</option>
                                    @foreach($filterCountries as $c)
                                    <option value="{{ $c }}">{{ $c }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-md-2 px-1 mb-1">
                                <label class="small mb-0 font-weight-bold">Fellowship Type</label>
                                <select id="filterType" class="form-control form-control-sm">
                                    <option value="">All Types</option>
                                    @foreach($filterTypes as $t)
                                    <option value="{{ $t }}">{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6 col-md-2 px-1 mb-1">
                                <label class="small mb-0 font-weight-bold">Year</label>
                                <select id="filterYear" class="form-control form-control-sm">
                                    <option value="">All Years</option>
                                    @foreach($filterYears as $y)
                                    <option value="{{ $y }}">{{ $y }}</option>
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
                                <label class="small mb-0 font-weight-bold">Alumni</label>
                                <select id="filterAlumni" class="form-control form-control-sm">
                                    <option value="">All Fellows</option>
                                    <option value="1">Alumni Only</option>
                                    <option value="0">Non-Alumni Only</option>
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
                            <div class="card-header"><h3 class="card-title">Fellows</h3></div>
                            <div class="card-body">
                                <table id="fellowstable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Country</th>
                                            <th>Specialty</th>
                                            <th>Fellowship Type</th>
                                            <th>Fellowship Year</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($getFellows as $value)
                                        <tr data-country="{{ $value->country_name ?? '' }}"
                                            data-programme="{{ $value->programme_name ?? '' }}"
                                            data-ftype="{{ $value->fellowship_type ?? '' }}"
                                            data-year="{{ $value->fellowship_year ?? '' }}"
                                            data-gender="{{ $value->gender ?? '' }}"
                                            data-alumni="{{ $value->is_alumni ?? 0 }}">
                                            <td class="row-num"></td>
                                            <td>
                                                @php
                                                    $specCount = 1
                                                        + (!empty($value->second_fcs_specialty) ? 1 : 0)
                                                        + (!empty($value->third_fcs_specialty)  ? 1 : 0);
                                                    $multiSpec = $specCount > 1;
                                                @endphp
                                                <span style="position:relative; display:inline-block;">
                                                    @if($multiSpec)
                                                        <span style="
                                                            position:absolute; top:-8px; right:-14px;
                                                            background:#3a7a1a; color:#fff;
                                                            border-radius:50%; width:16px; height:16px;
                                                            font-size:.6rem; font-weight:700;
                                                            display:flex; align-items:center; justify-content:center;
                                                            line-height:1; z-index:1;
                                                        " title="{{ $specCount }} FCS specialties">{{ $specCount }}</span>
                                                    @endif
                                                    <a href="{{ url('admin/associates/fellows/view/' . ($value->fellow_id ?? 0)) }}"
                                                       style="{{ $multiSpec ? 'color:#3a7a1a; font-weight:600;' : 'color:#222;' }} text-decoration:none;"
                                                       onmouseover="this.style.textDecoration='underline'"
                                                       onmouseout="this.style.textDecoration='none'">{{ $value->fellow_name ?? '-' }}</a>
                                                </span>
                                            </td>
                                            <td>{{ $value->personal_email ?? '-' }}</td>
                                            <td>{{ $value->country_name ?? '-' }}</td>
                                            <td>{{ $value->current_specialty ?: ($value->programme_name ? preg_replace('/^FCS\s+/i','', $value->programme_name) : '-') }}</td>
                                            <td>{{ $value->fellowship_type ?? '-' }}</td>
                                            <td>{{ $value->fellowship_year ?? '-' }}</td>
                                            <td class="text-center" style="white-space:nowrap;">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-light border dropdown-toggle action-btn"
                                                            type="button" data-toggle="dropdown"
                                                            aria-haspopup="true" aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                                        <a class="dropdown-item"
                                                           href="{{ url('admin/associates/fellows/view/' . ($value->fellow_id ?? 0)) }}">
                                                            <i class="fas fa-eye text-info mr-2"></i> View
                                                        </a>
                                                        <a class="dropdown-item"
                                                           href="{{ url('admin/associates/fellows/edit/' . ($value->fellow_id ?? 0)) }}">
                                                            <i class="fas fa-edit text-warning mr-2"></i> Edit
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-danger"
                                                           href="{{ url('admin/associates/fellows/delete/' . ($value->f_id ?? 0)) }}"
                                                           onclick="return confirm('Delete this fellow?')">
                                                            <i class="fas fa-trash mr-2"></i> Delete
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div> <!-- /.card-body -->
                        </div> <!-- /.card -->
                    </div> <!-- /.col -->
                </div> <!-- /.row -->
            </div> <!-- /.container-wrapper -->
        </section>
    </div>
</div>
@endsection

@push('styles')
<style>
    #fellowstable td { vertical-align: middle; }
    .action-btn { padding: 2px 8px; line-height: 1.4; border-radius: 4px; }
    .action-btn:hover { background-color: #f0f0f0; }
    .dropdown-menu { min-width: 130px; font-size: .875rem; }
    .dropdown-item { padding: 6px 14px; }
    .dropdown-item:hover { background-color: #f8f0f0; }
    .paginate_button.active>.page-link { background-color: #a02626 !important; border-color: #a02626 !important; color: white; }
    .paginate_button>.page-link { color: #a02626; }
    .paginate_button>.page-link:focus, .paginate_button.active>.page-link:focus { box-shadow: none !important; outline: none !important; }
    #fellowFilters label { color: #555; }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function () {

    // Custom search filter for the fellows DataTable
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'fellowstable') return true;

        var $row      = $(settings.nTable).DataTable().row(dataIndex).node();
        var country   = $('#filterCountry').val();
        var programme = $('#filterProgramme').val();
        var ftype     = $('#filterType').val();
        var year      = $('#filterYear').val();
        var gender    = $('#filterGender').val();
        var alumni    = $('#filterAlumni').val();

        if (country   && $($row).data('country')   !== country)            return false;
        if (programme && $($row).data('programme') !== programme)          return false;
        if (ftype     && $($row).data('ftype')     !== ftype)              return false;
        if (year      && String($($row).data('year'))      !== year)        return false;
        if (gender    && $($row).data('gender')    !== gender)             return false;
        if (alumni    !== '' && String($($row).data('alumni')) !== alumni)  return false;

        return true;
    });

    // Trigger DataTable redraw on filter change
    $('#filterCountry, #filterProgramme, #filterType, #filterYear, #filterGender, #filterAlumni').on('change', function () {
        $('#fellowstable').DataTable().draw();
    });

    // Clear filters button
    $('#btnClearFilters').on('click', function () {
        $('#filterCountry, #filterProgramme, #filterType, #filterYear, #filterGender, #filterAlumni').val('');
        $('#fellowstable').DataTable().draw();
    });

    // Dropdowns are re-initialised in custom.js drawCallback
});
</script>
@endpush
