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
                        @php
                        $filterDefs = [
                            ['id'=>'filterProgramme', 'label'=>'Programme',       'options'=>$filterProgrammes,         'default'=>[], 'optLabels'=>[]],
                            ['id'=>'filterCountry',   'label'=>'Country',         'options'=>$filterCountries,          'default'=>[], 'optLabels'=>[]],
                            ['id'=>'filterType',      'label'=>'Fellowship Type', 'options'=>$filterTypes,              'default'=>[], 'optLabels'=>[]],
                            ['id'=>'filterYear',      'label'=>'Year',            'options'=>$filterYears,              'default'=>[], 'optLabels'=>[]],
                            ['id'=>'filterGender',    'label'=>'Gender',          'options'=>collect(['Male','Female']), 'default'=>[], 'optLabels'=>[]],
                            ['id'=>'filterAlumni',    'label'=>'Alumni',          'options'=>collect(['unique','all','0']), 'default'=>[], 'optLabels'=>[
                                'unique' => 'Unique Alumni (' . number_format($uniqueAlumniCount ?? 0) . ')',
                                'all'    => 'All Alumni (' . number_format($allAlumniCount ?? 0) . ')',
                                '0'      => 'Non-Alumni Only',
                            ]],
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
                                    @if($fd['id'] === 'filterAlumni')
                                    <div class="small text-muted mb-2" style="border-bottom:1px solid #eee;padding-bottom:.4rem;">
                                        "Unique" = one row per person. "All" also lists each additional FCS specialty as its own row, matching the source alumni spreadsheet.
                                    </div>
                                    @endif
                                    @if(collect($fd['options'])->count() > 6)
                                    <input type="text" class="form-control form-control-sm chk-search mb-1" placeholder="Search…" autocomplete="off">
                                    @endif
                                    <div class="chk-list">
                                        @foreach($fd['options'] as $opt)
                                        <label class="chk-item">
                                            <input type="checkbox" class="chk-option" data-filter="{{ $fd['id'] }}" value="{{ $opt }}">
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
                                        <tr data-row-kind="primary"
                                            data-country="{{ $value->country_name ?? '' }}"
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
                                                       style="{{ $multiSpec ? 'color:#3a7a1a; font-weight:600;' : 'color:#222;' }} text-decoration:none;">{{ $value->fellow_name ?? '-' }}</a>
                                                </span>
                                            </td>
                                            <td>{{ $value->personal_email ?? '-' }}</td>
                                            <td>@if(!empty($value->country_id))<a href="{{ url('admin/countries/view/'.$value->country_id) }}" style="color:#a02626;font-weight:500;text-decoration:none;">{{ $value->country_name ?? '-' }}</a>@else{{ $value->country_name ?? '-' }}@endif</td>
                                            <td>@if(!empty($value->programme_id))<a href="{{ url('admin/programmes/view/'.$value->programme_id) }}" style="color:#a02626;font-weight:500;text-decoration:none;">{{ $value->current_specialty ?: ($value->programme_name ? preg_replace('/^FCS\s+/i','', $value->programme_name) : '-') }}</a>@else{{ $value->current_specialty ?: ($value->programme_name ? preg_replace('/^FCS\s+/i','', $value->programme_name) : '-') }}@endif</td>
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
<script type="application/json" id="extraAlumniRowsData">{!! json_encode($extraAlumniRows ?? []) !!}</script>
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

    function redraw() {
        var dt   = $('#fellowstable').DataTable();
        dt.draw();
        var info = dt.page.info();
        $('#filteredCount').text(
            info.recordsDisplay < info.recordsTotal
                ? 'Showing ' + info.recordsDisplay + ' of ' + info.recordsTotal : ''
        );
    }

    // DataTable custom search filter
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'fellowstable') return true;
        var $row = $($(settings.nTable).DataTable().row(dataIndex).node());

        var chkProgramme = getChecked('filterProgramme');
        var chkCountry   = getChecked('filterCountry');
        var chkType      = getChecked('filterType');
        var chkYear      = getChecked('filterYear');
        var chkGender    = getChecked('filterGender');
        var chkAlumni    = getChecked('filterAlumni');

        if (chkProgramme.length && chkProgramme.indexOf(String($row.data('programme') || '')) === -1) return false;
        if (chkCountry.length   && chkCountry.indexOf(String($row.data('country')     || '')) === -1) return false;
        if (chkType.length      && chkType.indexOf(String($row.data('ftype')          || '')) === -1) return false;
        if (chkYear.length      && chkYear.indexOf(String($row.data('year')           || '')) === -1) return false;
        if (chkGender.length    && chkGender.indexOf(String($row.data('gender')       || '')) === -1) return false;
        if (chkAlumni.length) {
            var wantsYes = chkAlumni.indexOf('unique') !== -1 || chkAlumni.indexOf('all') !== -1;
            var wantsNo  = chkAlumni.indexOf('0') !== -1;
            var rowAlumni = String($row.data('alumni'));
            if (!((wantsYes && rowAlumni === '1') || (wantsNo && rowAlumni === '0'))) return false;
        }
        return true;
    });

    // ── "All Alumni" split-specialty rows — added/removed from the table
    // (not just hidden), so the default row count never changes unless this
    // view is actively selected. ──
    var extraAlumniRows = JSON.parse(document.getElementById('extraAlumniRowsData').textContent || '[]');
    var extraRowsAdded  = false;

    function buildExtraRowHtml(r) {
        var name = (r.name || '-').replace(/</g, '&lt;');
        var email = (r.email || '-').replace(/</g, '&lt;');
        var country = (r.country_name || '-').replace(/</g, '&lt;');
        var specialty = (r.specialty || '-').replace(/^FCS\s+/i, '').replace(/</g, '&lt;');
        return '<tr data-row-kind="extra" data-country="' + country + '" data-programme="" ' +
            'data-ftype="Fellow by Examination" data-year="' + (r.year || '') + '" data-gender="" data-alumni="1">' +
            '<td class="row-num"></td>' +
            '<td><a href="' + '{{ url("admin/associates/fellows/view") }}' + '/' + r.fellow_id + '" style="color:#3a7a1a;text-decoration:none;">' + name + ' <span class="text-muted small">(add\'l specialty)</span></a></td>' +
            '<td>' + email + '</td>' +
            '<td>' + country + '</td>' +
            '<td>' + specialty + '</td>' +
            '<td>Fellow by Examination</td>' +
            '<td>' + (r.year || '-') + '</td>' +
            '<td></td>' +
            '</tr>';
    }

    function syncExtraAlumniRows() {
        var dt = $('#fellowstable').DataTable();
        var wantsAll = getChecked('filterAlumni').indexOf('all') !== -1;
        if (wantsAll && !extraRowsAdded) {
            var nodes = extraAlumniRows.map(function (r) { return $(buildExtraRowHtml(r))[0]; });
            dt.rows.add(nodes).draw(false);
            extraRowsAdded = true;
        } else if (!wantsAll && extraRowsAdded) {
            dt.rows(function (idx, d, node) { return $(node).data('row-kind') === 'extra'; }).remove().draw(false);
            extraRowsAdded = false;
        }
    }

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
        var filterId = $(this).data('filter');
        // "Unique Alumni" and "All Alumni" are two views of the same set, not
        // combinable filters — selecting one clears the other.
        if (filterId === 'filterAlumni' && this.checked && (this.value === 'unique' || this.value === 'all')) {
            var other = this.value === 'unique' ? 'all' : 'unique';
            $('.chk-option[data-filter="filterAlumni"][value="' + other + '"]').prop('checked', false);
        }
        updateBadge(filterId);
        if (filterId === 'filterAlumni') syncExtraAlumniRows();
        redraw();
    });

    // Select All / Clear per panel
    $(document).on('click', '.chk-select-all', function (e) {
        e.preventDefault();
        var $panel   = $(this).closest('.chk-filter-panel');
        var filterId = $panel.closest('.chk-filter-wrap').data('filter');
        $panel.find('.chk-item:visible .chk-option').prop('checked', true);
        if (filterId === 'filterAlumni') {
            // "all" supersedes "unique" when both would otherwise be checked
            $('.chk-option[data-filter="filterAlumni"][value="unique"]').prop('checked', false);
        }
        updateBadge(filterId);
        if (filterId === 'filterAlumni') syncExtraAlumniRows();
        redraw();
    });
    $(document).on('click', '.chk-clear', function (e) {
        e.preventDefault();
        var $panel   = $(this).closest('.chk-filter-panel');
        var filterId = $panel.closest('.chk-filter-wrap').data('filter');
        $panel.find('.chk-option').prop('checked', false);
        updateBadge(filterId);
        if (filterId === 'filterAlumni') syncExtraAlumniRows();
        redraw();
    });

    // Clear All
    $('#btnClearFilters').on('click', function () {
        $('.chk-option').prop('checked', false);
        $('.chk-badge').hide();
        syncExtraAlumniRows();
        redraw();
        $('#filteredCount').text('');
    });
});
</script>
@endpush
