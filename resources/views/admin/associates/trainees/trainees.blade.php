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
                            <div class="d-flex flex-wrap align-items-center" style="gap:.5rem;">

                                @php
                                $filterDefs = [
                                    ['id'=>'filterCountry',       'label'=>'Country',         'options'=>$filterCountries],
                                    ['id'=>'filterProgramme',     'label'=>'Programme',       'options'=>$filterProgrammes],
                                    ['id'=>'filterYear',          'label'=>'Exam Year',       'options'=>$filterYears],
                                    ['id'=>'filterStatus',        'label'=>'Status',          'options'=>$filterStatuses],
                                    ['id'=>'filterAdmissionYear', 'label'=>'Admission Year',  'options'=>$filterAdmissionYears],
                                    ['id'=>'filterGender',        'label'=>'Gender',          'options'=>collect(['Male','Female'])],
                                    ['id'=>'filterHospital',      'label'=>'Hospital',        'options'=>$filterHospitals],
                                ];
                                @endphp

                                @foreach($filterDefs as $fd)
                                <div class="chk-filter-wrap" data-filter="{{ $fd['id'] }}">
                                    <button type="button"
                                            class="btn btn-sm btn-outline-secondary chk-filter-btn"
                                            data-filter="{{ $fd['id'] }}">
                                        {{ $fd['label'] }}
                                        <span class="badge badge-danger chk-badge ml-1" style="display:none;font-size:.65rem;"></span>
                                        <i class="fas fa-caret-down ml-1" style="font-size:.7rem;"></i>
                                    </button>
                                    <div class="chk-filter-panel shadow" id="{{ $fd['id'] }}-panel" style="display:none;">
                                        @if($fd['options']->count() > 6)
                                        <input type="text" class="form-control form-control-sm chk-search mb-1"
                                               placeholder="Search…" autocomplete="off">
                                        @endif
                                        <div class="chk-list">
                                            @foreach($fd['options'] as $opt)
                                            <label class="chk-item">
                                                <input type="checkbox"
                                                       class="chk-option"
                                                       data-filter="{{ $fd['id'] }}"
                                                       value="{{ $opt }}">
                                                {{ $opt }}
                                            </label>
                                            @endforeach
                                        </div>
                                        <div class="chk-footer">
                                            <a href="#" class="chk-select-all small">All</a>
                                            <a href="#" class="chk-clear small text-danger">Clear</a>
                                        </div>
                                    </div>
                                </div>
                                @endforeach

                                <button id="btnClearFilters" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-times mr-1"></i>Clear All
                                </button>
                                <small class="text-muted ml-auto" id="filteredCount"></small>
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
                                                    data-admissionyear="{{ $value->admission_year ?? '' }}"
                                                    data-status="{{ $value->status ?? '' }}"
                                                    data-gender="{{ $value->gender ?? '' }}"
                                                    data-hospital="{{ $value->hospital_name ?? '' }}">
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
                                                    <td>@if($value->programme_id)<a href="{{ url('admin/programmes/view/'.$value->programme_id) }}" style="color:#a02626;font-weight:500;text-decoration:none;">{{ $value->programme_name }}</a>@else{{ $value->programme_name }}@endif</td>
                                                    <td>@if($value->hospital_id)<a href="{{ url('admin/hospital/view_hospital/'.$value->hospital_id) }}" style="color:#a02626;font-weight:500;text-decoration:none;">{{ $value->hospital_name }}</a>@else{{ $value->hospital_name }}@endif</td>
                                                    <td>@if($value->country_id)<a href="{{ url('admin/countries/view/'.$value->country_id) }}" style="color:#a02626;font-weight:500;text-decoration:none;">{{ $value->country_name }}</a>@else{{ $value->country_name }}@endif</td>
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

    // ── Checkbox filter state: { filterId: Set of checked values } ────────────
    var filterState = {};

    function getChecked(filterId) {
        return $('.chk-option[data-filter="' + filterId + '"]:checked')
               .map(function () { return this.value; }).get();
    }

    function redraw() {
        var dt = $('#traineestable').DataTable();
        dt.draw();
        var info = dt.page.info();
        $('#filteredCount').text(
            info.recordsDisplay < info.recordsTotal
                ? 'Showing ' + info.recordsDisplay + ' of ' + info.recordsTotal : ''
        );
    }

    function updateBadge(filterId) {
        var checked = getChecked(filterId);
        var $badge  = $('.chk-filter-btn[data-filter="' + filterId + '"] .chk-badge');
        if (checked.length) {
            $badge.text(checked.length).show();
        } else {
            $badge.hide();
        }
    }

    // ── DataTable custom search ───────────────────────────────────────────────
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'traineestable') return true;
        var $row     = $($(settings.nTable).DataTable().row(dataIndex).node());
        var filters  = [
            { id: 'filterCountry',       val: String($row.data('country')      || '') },
            { id: 'filterProgramme',     val: String($row.data('programme')    || '') },
            { id: 'filterYear',          val: String($row.data('year')         || '') },
            { id: 'filterAdmissionYear', val: String($row.data('admissionyear')|| '') },
            { id: 'filterStatus',        val: String($row.data('status')       || '') },
            { id: 'filterGender',        val: String($row.data('gender')       || '') },
            { id: 'filterHospital',      val: String($row.data('hospital')     || '') },
        ];
        for (var i = 0; i < filters.length; i++) {
            var checked = getChecked(filters[i].id);
            if (checked.length && checked.indexOf(filters[i].val) === -1) return false;
        }
        return true;
    });

    // ── Open / close panels ───────────────────────────────────────────────────
    $(document).on('click', '.chk-filter-btn', function (e) {
        e.stopPropagation();
        var filterId = $(this).data('filter');
        var $panel   = $('#' + filterId + '-panel');
        $('.chk-filter-panel').not($panel).hide(); // close others
        $panel.toggle();
    });

    $(document).on('click', '.chk-filter-panel', function (e) {
        e.stopPropagation(); // keep panel open when clicking inside
    });

    $(document).on('click', function () {
        $('.chk-filter-panel').hide();
    });

    // ── Checkbox change ───────────────────────────────────────────────────────
    $(document).on('change', '.chk-option', function () {
        updateBadge($(this).data('filter'));
        redraw();
    });

    // ── In-panel search ───────────────────────────────────────────────────────
    $(document).on('input', '.chk-search', function () {
        var q      = $(this).val().toLowerCase();
        var $panel = $(this).closest('.chk-filter-panel');
        $panel.find('.chk-item').each(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(q) !== -1);
        });
    });

    // ── Select All / Clear links ──────────────────────────────────────────────
    $(document).on('click', '.chk-select-all', function (e) {
        e.preventDefault();
        var $panel = $(this).closest('.chk-filter-panel');
        $panel.find('.chk-item:visible .chk-option').prop('checked', true);
        var filterId = $panel.closest('.chk-filter-wrap').data('filter');
        updateBadge(filterId);
        redraw();
    });

    $(document).on('click', '.chk-clear', function (e) {
        e.preventDefault();
        var $panel   = $(this).closest('.chk-filter-panel');
        var filterId = $panel.closest('.chk-filter-wrap').data('filter');
        $panel.find('.chk-option').prop('checked', false);
        updateBadge(filterId);
        redraw();
    });

    // ── Clear All ─────────────────────────────────────────────────────────────
    $('#btnClearFilters').on('click', function () {
        $('.chk-option').prop('checked', false);
        $('.chk-badge').hide();
        redraw();
        $('#filteredCount').text('');
    });
});
</script>
@endpush

@push('styles')
<style>
    /* ── Checkbox filter dropdowns ── */
    .chk-filter-wrap { position: relative; display: inline-block; }
    .chk-filter-panel {
        position: absolute; top: calc(100% + 4px); left: 0; z-index: 1055;
        background: #fff; border: 1px solid #ced4da; border-radius: 6px;
        min-width: 190px; max-width: 260px; padding: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,.12);
    }
    .chk-list { max-height: 220px; overflow-y: auto; }
    .chk-item {
        display: flex; align-items: center; gap: 6px;
        padding: 3px 2px; font-size: .82rem; font-weight: normal;
        cursor: pointer; white-space: nowrap; margin: 0;
    }
    .chk-item:hover { background: #f8f0f0; border-radius: 4px; }
    .chk-item input[type="checkbox"] { margin: 0; cursor: pointer; accent-color: #a02626; }
    .chk-footer {
        display: flex; justify-content: space-between;
        border-top: 1px solid #eee; margin-top: 6px; padding-top: 5px;
        font-size: .78rem;
    }
    .chk-footer a { color: #6c757d; }
    .chk-footer a:hover { color: #a02626; text-decoration: none; }
    .chk-filter-btn { white-space: nowrap; }
    /* ── Table ── */
    #traineestable td { vertical-align: middle; }
    .trainee-name-link {
        color: #333;
        text-decoration: none;
        font-weight: 500;
    }
    .trainee-name-link:hover {
        color: #a02626;
        text-decoration: none;
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
