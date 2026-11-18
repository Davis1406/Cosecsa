@extends('layout.app')

@section('content')
@php
    $rowsUrl = url('admin/associates/candidates/list-rows') . (($showInactive ?? false) ? '?inactive=1' : '');
@endphp
@include('admin._dt_prefetch', ['url' => $rowsUrl])
<div class="wrapper">
    <div class="content-wrapper">

        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        @if($showInactive ?? false)
                        <a href="{{ url('admin/associates/candidates/list') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Back to active candidates
                        </a>
                        @else
                        <a href="{{ url('admin/associates/candidates/list?inactive=1') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-user-slash mr-1"></i> Inactive candidates
                        </a>
                        @endif
                    </div>
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
                            ['id'=>'filterYear',      'label'=>'Exam Year', 'options'=>$filterYears,             'default'=>($showInactive ?? false) ? [] : [(string)date('Y')], 'optLabels'=>[]],
                            ['id'=>'filterGender',    'label'=>'Gender',    'options'=>collect(['Male','Female']),'default'=>[], 'optLabels'=>[]],
                            ['id'=>'filterFeePaid',   'label'=>'Fee Paid',  'options'=>collect(['Yes','No']),     'default'=>[], 'optLabels'=>['Yes'=>'Paid','No'=>'Not Paid']],
                            ['id'=>'filterPartOne',   'label'=>'Part I',    'options'=>collect(['Pass','Fail','None']), 'default'=>[], 'optLabels'=>['None'=>'No result yet']],
                            ['id'=>'filterPartTwo',   'label'=>'Part II',   'options'=>collect(['Yes','No']),     'default'=>[], 'optLabels'=>['Yes'=>'Promoted','No'=>'Not promoted']],
                            ['id'=>'filterRepeat',    'label'=>'Repeating', 'options'=>collect(['Yes','No']),     'default'=>[], 'optLabels'=>['Yes'=>'Repeating a paper','No'=>'Not repeating']],
                            ['id'=>'filterTags',      'label'=>'Tags',      'options'=>$filterTags,              'default'=>[], 'optLabels'=>[]],
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

                @if(Auth::user()->isSuperAdmin() || Auth::user()->hasPermission('candidates.manage'))
                <form id="bulkPartTwoForm" method="POST" action="{{ url('admin/associates/candidates/part-two-bulk') }}" class="d-none">
                    @csrf
                    <input type="hidden" name="promote" id="bulkPromote" value="1">
                    <div id="bulkIds"></div>
                </form>
                <div class="card card-outline card-info mb-2 shadow-sm" id="bulkBar">
                    <div class="card-body py-2 d-flex flex-wrap align-items-center" style="gap:.6rem;">
                        <strong class="mr-1"><i class="fas fa-check-double mr-1"></i> Bulk Part II</strong>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="bulkSelectFiltered">Select everyone shown</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="bulkClear" style="display:none;">Clear selection</button>
                        <span class="text-muted small" id="bulkCount">Tick candidates in the list, or filter (e.g. Repeating) and select everyone shown.</span>
                        <span class="ml-auto" id="bulkActions" style="display:none;">
                            <button type="button" class="btn btn-sm btn-success" id="bulkPromoteBtn"><i class="fas fa-level-up-alt mr-1"></i> Promote to Part II</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="bulkUnpromoteBtn"><i class="fas fa-undo mr-1"></i> Remove promotion</button>
                        </span>
                    </div>
                </div>
                @endif

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title mb-0">@if($showInactive ?? false)Inactive Candidates<span class="d-none" id="cardYearLabel"></span>@else Candidates List <span id="cardYearLabel">{{ date('Y') }}</span>@endif</h3>
                                <small class="text-muted" id="filteredCount"></small>
                            </div>
                            <div class="card-body">
                                <table id="candidatestable" class="table table-bordered table-striped"
                                       data-rows-url="{{ $rowsUrl }}"
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
        var chkTags = getChecked('filterTags');
        if (chkTags.length) {
            var rowTags = row.tags || [];
            if (!chkTags.some(function (t) { return rowTags.indexOf(t) !== -1; })) return false;
        }
        var chkRepeat = getChecked('filterRepeat');
        if (chkRepeat.length) {
            var repeating = (row.repeat_paper_one === 'Yes' || row.repeat_paper_two === 'Yes') ? 'Yes' : 'No';
            if (chkRepeat.indexOf(repeating) === -1) return false;
        }
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

    // ── Bulk Part II: tick candidates (or select everyone the filters show), then promote them together ──
    window.candSel = window.candSel || {};
    function selCount() { return Object.keys(window.candSel).length; }
    function refreshBulk() {
        var n = selCount();
        $('#bulkCount').text(n ? n + ' selected' : 'Tick candidates in the list, or filter (e.g. Repeating) and select everyone shown.');
        $('#bulkActions, #bulkClear').toggle(n > 0);
    }
    $(document).on('change', '.cand-pick', function () {
        if (this.checked) window.candSel[this.value] = 1; else delete window.candSel[this.value];
        refreshBulk();
    });
    $('#bulkSelectFiltered').on('click', function () {
        var dt = $('#candidatestable').DataTable();
        dt.rows({ search: 'applied' }).data().each(function (r) { if (r.candidates_id) window.candSel[r.candidates_id] = 1; });
        $('.cand-pick').prop('checked', true);
        refreshBulk();
    });
    $('#bulkClear').on('click', function () {
        window.candSel = {};
        $('.cand-pick').prop('checked', false);
        refreshBulk();
    });
    function bulkSubmit(promote) {
        var ids = Object.keys(window.candSel), n = ids.length;
        if (!n) return;
        CosecsaAlert.confirm((promote ? 'Promote ' : 'Remove the Part II promotion of ') + n + ' candidate' + (n === 1 ? '' : 's') + (promote ? ' to Part II?' : '?'), { variant: 'info' })
            .then(function (ok) {
                if (!ok) return;
                $('#bulkPromote').val(promote ? 1 : 0);
                $('#bulkIds').html(ids.map(function (id) { return '<input type="hidden" name="ids[]" value="' + id + '">'; }).join(''));
                document.getElementById('bulkPartTwoForm').submit();
            });
    }
    $('#bulkPromoteBtn').on('click', function () { bulkSubmit(true); });
    $('#bulkUnpromoteBtn').on('click', function () { bulkSubmit(false); });

    // Apply default year filter once the rows have loaded
    $('#candidatestable').on('init.dt', redraw);
    updateCardTitle();
});
</script>
@endpush
