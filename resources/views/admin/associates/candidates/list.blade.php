@extends('layout.app')

@section('content')
@include('admin._dt_prefetch', ['url' => url('admin/associates/candidates/list-rows')])
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
                        <a href="{{ url('admin/associates/candidates/part-one') }}" class="btn btn-sm btn-cosecsa mr-1">
                            <i class="fas fa-file-import mr-1"></i> Upload Part I Results
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
                        @php
                        $filterDefs = [
                            ['id'=>'filterCountry',   'label'=>'Country',   'options'=>$filterCountries,         'default'=>[], 'optLabels'=>[]],
                            ['id'=>'filterProgramme', 'label'=>'Programme', 'options'=>$filterProgrammes,        'default'=>[], 'optLabels'=>[]],
                            ['id'=>'filterYear',      'label'=>'Exam Year', 'options'=>$filterYears,             'default'=>[(string)date('Y')], 'optLabels'=>[]],
                            ['id'=>'filterGender',    'label'=>'Gender',    'options'=>collect(['Male','Female']),'default'=>[], 'optLabels'=>[]],
                            ['id'=>'filterFeePaid',   'label'=>'Fee Paid',  'options'=>collect(['Yes','No']),     'default'=>[], 'optLabels'=>['Yes'=>'Paid','No'=>'Not Paid']],
                            ['id'=>'filterPartOne',   'label'=>'Part I',    'options'=>collect(['Pass','Fail','None']), 'default'=>[], 'optLabels'=>['None'=>'No result yet']],
                            ['id'=>'filterPartTwo',   'label'=>'Part II',   'options'=>collect(['Yes','No']),     'default'=>[], 'optLabels'=>['Yes'=>'Promoted','No'=>'Not promoted']],
                        ];
                        @endphp
                        <div class="d-flex flex-wrap align-items-center" style="gap:.5rem;">
                            @foreach($filterDefs as $fd)
                            <div class="chk-filter-wrap" data-filter="{{ $fd['id'] }}">
                                <button type="button" class="btn btn-sm btn-outline-secondary chk-filter-btn" data-filter="{{ $fd['id'] }}">
                                    {{ $fd['label'] }}
                                    <span class="badge badge-danger chk-badge ml-1" style="display:none;font-size:.65rem;"></span>
                                    <i class="fas fa-caret-down ml-1" style="font-size:.7rem;"></i>
                                </button>
                                <div class="chk-filter-panel shadow" id="{{ $fd['id'] }}-panel" style="display:none;">
                                    @if(collect($fd['options'])->count() > 6)
                                    <input type="text" class="form-control form-control-sm chk-search mb-1" placeholder="Search…" autocomplete="off">
                                    @endif
                                    <div class="chk-list">
                                        @foreach($fd['options'] as $opt)
                                        <label class="chk-item">
                                            <input type="checkbox" class="chk-option" data-filter="{{ $fd['id'] }}" value="{{ $opt }}"
                                                   {{ in_array((string)$opt, array_map('strval', $fd['default'])) ? 'checked' : '' }}>
                                            {{ !empty($fd['optLabels'][$opt]) ? $fd['optLabels'][$opt] : $opt }}
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
                                <h3 class="card-title mb-0">Candidates List <span id="cardYearLabel">{{ date('Y') }}</span></h3>
                                <small class="text-muted" id="filteredCount"></small>
                            </div>
                            <div class="card-body">
                                <table id="candidatestable" class="table table-bordered table-striped"
                                       data-rows-url="{{ url('admin/associates/candidates/list-rows') }}"
                                       data-base-url="{{ url('admin/associates/candidates') }}"
                                       data-app-url="{{ url('') }}"
                                       data-can-delete="{{ Auth::user()->isSuperAdmin() ? 1 : 0 }}"
                                       data-can-manage="{{ Auth::user()->isSuperAdmin() || Auth::user()->hasPermission('candidates.manage') ? 1 : 0 }}"
                                       data-csrf="{{ csrf_token() }}">
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
                                            <th>Part I</th>
                                            <th>Part II</th>
                                            {{-- Hidden columns for export / search --}}
                                            <th>Email</th>
                                            <th>Repeat P1</th>
                                            <th>Repeat P2</th>
                                            <th>MMed</th>
                                            <th>Sponsor</th>
                                            <th>Exam Year</th>
                                            <th>Mode of Payment</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
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
    #candidatestable td { vertical-align: middle; }
    #candidatestable th.text-center { white-space: nowrap; }
    .candidate-name-link {
        color: #333;
        text-decoration: none;
        font-weight: 500;
    }
    .candidate-name-link:hover {
        color: #a02626;
        text-decoration: none;
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

    function getChecked(filterId) {
        return $('.chk-option[data-filter="' + filterId + '"]:checked')
               .map(function () { return this.value; }).get();
    }

    function updateBadge(filterId) {
        var checked = getChecked(filterId);
        var $badge  = $('.chk-filter-btn[data-filter="' + filterId + '"] .chk-badge');
        if (checked.length) $badge.text(checked.length).show();
        else $badge.hide();
    }

    function updateCardTitle() {
        var yrs = getChecked('filterYear');
        if (yrs.length === 0)      $('#cardYearLabel').text('All Years');
        else if (yrs.length === 1) $('#cardYearLabel').text(yrs[0]);
        else                       $('#cardYearLabel').text(yrs[0] + ' (+' + (yrs.length - 1) + ' more)');
    }

    function redraw() {
        var dt   = $('#candidatestable').DataTable();
        dt.draw();
        var info = dt.page.info();
        $('#filteredCount').text(
            info.recordsDisplay < info.recordsTotal
                ? 'Showing ' + info.recordsDisplay + ' of ' + info.recordsTotal : ''
        );
        updateCardTitle();
    }

    // DataTable custom search filter
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex, row) {
        if (settings.nTable.id !== 'candidatestable') return true;

        var chkCountry   = getChecked('filterCountry');
        var chkProgramme = getChecked('filterProgramme');
        var chkYear      = getChecked('filterYear');
        var chkGender    = getChecked('filterGender');
        var chkFeePaid   = getChecked('filterFeePaid');

        if (chkCountry.length   && chkCountry.indexOf(String(row.country_name   || '')) === -1) return false;
        if (chkProgramme.length && chkProgramme.indexOf(String(row.programme_name || '')) === -1) return false;
        if (chkYear.length      && chkYear.indexOf(String(row.exam_year           || '')) === -1) return false;
        if (chkGender.length    && chkGender.indexOf(String(row.gender            || '')) === -1) return false;
        if (chkFeePaid.length   && chkFeePaid.indexOf(String(row.fee_paid || 'No'))      === -1) return false;
        var chkPartOne = getChecked('filterPartOne'), chkPartTwo = getChecked('filterPartTwo');
        if (chkPartOne.length   && chkPartOne.indexOf(row.part1_result || 'None')       === -1) return false;
        if (chkPartTwo.length   && chkPartTwo.indexOf(row.part_two_promoted_at ? 'Yes' : 'No') === -1) return false;
        return true;
    });

    // Panel open/close
    $(document).on('click', '.chk-filter-btn', function (e) {
        e.stopPropagation();
        var filterId = $(this).data('filter');
        var $panel   = $('#' + filterId + '-panel');
        $('.chk-filter-panel').not($panel).hide();
        $panel.toggle();
    });
    $(document).on('click', '.chk-filter-panel', function (e) { e.stopPropagation(); });
    $(document).on('click', function () { $('.chk-filter-panel').hide(); });

    // In-panel search
    $(document).on('input', '.chk-search', function () {
        var q = $(this).val().toLowerCase();
        $(this).closest('.chk-filter-panel').find('.chk-item').each(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(q) !== -1);
        });
    });

    // Checkbox change
    $(document).on('change', '.chk-option', function () {
        updateBadge($(this).data('filter'));
        redraw();
    });

    // Select All / Clear per panel
    $(document).on('click', '.chk-select-all', function (e) {
        e.preventDefault();
        var $panel = $(this).closest('.chk-filter-panel');
        $panel.find('.chk-item:visible .chk-option').prop('checked', true);
        updateBadge($panel.closest('.chk-filter-wrap').data('filter'));
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

    // Clear All
    $('#btnClearFilters').on('click', function () {
        $('.chk-option').prop('checked', false);
        $('.chk-badge').hide();
        redraw();
        $('#filteredCount').text('');
    });

    // Apply default year filter once the rows have loaded
    $('#candidatestable').on('init.dt', redraw);
    updateCardTitle();
});
</script>
@endpush
