@extends('layout.app')

@section('content')

<div class="wrapper">

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">

        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2 align-items-center">
                    <div class="col-sm-6">
                        <h1 class="m-0">Trainers <small class="text-muted">COSECSA Master Trainer ToT roster</small></h1>
                    </div>
                    <div class="col-sm-6 text-right">
                        <button type="button" id="btnPrint" class="btn btn-sm btn-secondary">
                            <i class="fas fa-print mr-1"></i>Print
                        </button>
                        <button type="button" id="btnExport" class="btn btn-sm" style="background-color:#FEC503;border-color:#FEC503;color:#000;">
                            <i class="fas fa-file-excel mr-1"></i>Export CSV
                        </button>
                        <a href="{{ url('admin/reports?type=trainers') }}" class="btn btn-sm btn-outline-secondary" title="Build a custom field/grouping report and chart">
                            <i class="fas fa-chart-bar mr-1"></i>College Reports
                        </a>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <div class="col-md-12">
            @include('_message')
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-wrapper">

                {{-- ── Stat cards ── --}}
                @php
                    $total = count($getRecord);
                    $masterCount = collect($getRecord)->where('is_master_trainer', true)->count();
                    $ssCount = collect($getRecord)->where('is_specialty_surgeon', true)->count();
                    $subCount = collect($getRecord)->where('is_subspecialty', true)->count();
                @endphp
                <div class="row" id="statCards">
                    <div class="col-6 col-md-3">
                        <div class="mini-stat" id="statTotal" title="Show all trainers">
                            <div class="mini-stat-icon" style="background:#f0d4d4;color:#a02626;"><i class="fas fa-user-tie"></i></div>
                            <div><div class="mini-stat-val">{{ $total }}</div><div class="mini-stat-lbl">Total Trainers</div></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mini-stat" id="statMaster" title="Filter: Master Trainers only">
                            <div class="mini-stat-icon" style="background:#e6f7ea;color:#28a745;"><i class="fas fa-medal"></i></div>
                            <div><div class="mini-stat-val">{{ $masterCount }}</div><div class="mini-stat-lbl">Master Trainers</div></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mini-stat" id="statSS" title="Filter: Specialty Surgeons only">
                            <div class="mini-stat-icon" style="background:#e6f0fc;color:#007bff;"><i class="fas fa-user-md"></i></div>
                            <div><div class="mini-stat-val">{{ $ssCount }}</div><div class="mini-stat-lbl">Specialty Surgeons</div></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="mini-stat" id="statSub" title="Filter: unmatched subspecialty only">
                            <div class="mini-stat-icon" style="background:#eee;color:#6c757d;"><i class="fas fa-stethoscope"></i></div>
                            <div><div class="mini-stat-val">{{ $subCount }}</div><div class="mini-stat-lbl">Subspecialty (unmatched)</div></div>
                        </div>
                    </div>
                </div>

                {{-- ── Filter Bar ── --}}
                @php
                $trFilterDefs = [
                    ['id'=>'trFilterYear',      'label'=>'ToT Year',  'options'=>collect($meta->tot_years ?? [])->map(fn($y) => ['value'=>$y->id, 'label'=>$y->label_short, 'title'=>$y->label_full])],
                    ['id'=>'trFilterCountry',   'label'=>'Country',   'options'=>collect($meta->countries ?? [])->map(fn($c) => ['value'=>$c->id, 'label'=>$c->country_name, 'title'=>$c->country_name])],
                    ['id'=>'trFilterSpecialty', 'label'=>'Specialty', 'options'=>collect($meta->programmes ?? [])->map(fn($p) => ['value'=>$p->id, 'label'=>$p->name, 'title'=>$p->name])],
                ];
                @endphp
                <div class="card card-outline card-secondary mb-2 shadow-sm no-print">
                    <div class="card-body py-2">
                        <div class="d-flex flex-wrap align-items-center" style="gap:.5rem;">
                            @foreach($trFilterDefs as $fd)
                            <div class="chk-filter-wrap" data-filter="{{ $fd['id'] }}">
                                <button type="button" class="btn btn-sm btn-outline-secondary chk-filter-btn" data-filter="{{ $fd['id'] }}">
                                    {{ $fd['label'] }}
                                    <span class="badge badge-danger chk-badge ml-1" style="display:none;font-size:.65rem;"></span>
                                    <i class="fas fa-caret-down ml-1" style="font-size:.7rem;"></i>
                                </button>
                                <div class="chk-filter-panel shadow" id="{{ $fd['id'] }}-panel" style="display:none;">
                                    @if($fd['options']->count() > 6)
                                    <input type="text" class="form-control form-control-sm chk-search mb-1" placeholder="Search…" autocomplete="off">
                                    @endif
                                    <div class="chk-list">
                                        @foreach($fd['options'] as $opt)
                                        <label class="chk-item" title="{{ $opt['title'] }}">
                                            <input type="checkbox" class="chk-option" data-filter="{{ $fd['id'] }}" value="{{ $opt['value'] }}">
                                            {{ $opt['label'] }}
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
                            <label class="chk-item" style="margin-left:.25rem;">
                                <input type="checkbox" id="trFilterMaster">
                                Master Trainers only
                            </label>
                            <button id="trBtnClear" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-times mr-1"></i>Clear All
                            </button>
                            <small class="text-muted ml-auto" id="trFilteredCount"></small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Trainers</h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <table id="trtable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Organisation</th>
                                            <th>Country</th>
                                            <th>Specialty</th>
                                            <th>ToT Years Attended</th>
                                            <th>Master Trainer</th>
                                            <th>SS</th>
                                            <th class="no-print">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($getRecord as $value)
                                        @php
                                            $countries = collect($value->countries ?? []);
                                            $countryNames = $countries->pluck('name');
                                            $countryIds = $countries->pluck('id');
                                            $yearIds = collect($value->tot_years ?? [])->pluck('id');
                                            $yearShorts = collect($value->tot_years ?? [])->pluck('label_short');
                                            $specialtyIds = $value->programme_id ? [$value->programme_id] : [];
                                        @endphp
                                        <tr class="user-row"
                                            data-country="{{ $countryIds->implode(',') }}"
                                            data-year="{{ $yearIds->implode(',') }}"
                                            data-specialty="{{ implode(',', $specialtyIds) }}"
                                            data-master="{{ !empty($value->is_master_trainer) ? '1' : '0' }}"
                                            data-ss="{{ !empty($value->is_specialty_surgeon) ? '1' : '0' }}"
                                            data-sub="{{ !empty($value->is_subspecialty) ? '1' : '0' }}">
                                            <td>{{ $value->id }}</td>
                                            <td>
                                                <a href="{{ url('admin/associates/trainers/view/' . $value->id) }}" style="color:#a02626;font-weight:500;text-decoration:none;">
                                                    {{ $value->name }}
                                                </a>
                                            </td>
                                            <td>{{ $value->email ?: '—' }}</td>
                                            <td>
                                                @if(!empty($value->hospital))
                                                    <a href="{{ url('admin/hospital/view_hospital/' . $value->hospital->id) }}" class="entity-link">{{ $value->hospital->name }}</a>
                                                @else
                                                    {{ $value->organisation ?: '—' }}
                                                @endif
                                            </td>
                                            <td>
                                                @forelse($countries as $c)
                                                    <a href="{{ url('admin/countries/view/' . $c->id) }}" class="entity-link">{{ $c->name }}</a>@if(!$loop->last), @endif
                                                @empty
                                                    {{ $value->country_name_raw ?: '—' }}
                                                @endforelse
                                            </td>
                                            <td>
                                                @if($value->programme_id)
                                                    <a href="{{ url('admin/programmes/view/' . $value->programme_id) }}" class="entity-link">{{ $value->specialty }}</a>
                                                @else
                                                    {{ $value->specialty ?: '—' }}
                                                    @if(!empty($value->is_subspecialty))
                                                        <span class="badge badge-light border">subspecialty</span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                @forelse($yearShorts as $year)
                                                    <span class="badge badge-secondary">{{ $year }}</span>
                                                @empty
                                                    —
                                                @endforelse
                                            </td>
                                            <td class="text-center">
                                                @if(!empty($value->is_master_trainer))<i class="fas fa-check-circle text-success"></i>@else <span class="text-muted">—</span> @endif
                                            </td>
                                            <td class="text-center">
                                                @if(!empty($value->is_specialty_surgeon))<i class="fas fa-check-circle text-success"></i>@else <span class="text-muted">—</span> @endif
                                            </td>
                                            <td class="text-center no-print" style="white-space:nowrap;">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                                        <a class="dropdown-item" href="{{ url('admin/associates/trainers/view/' . $value->id) }}">
                                                            <i class="fas fa-eye text-info mr-2"></i> View
                                                        </a>
                                                        <a class="dropdown-item" href="{{ url('admin/associates/trainers/edit/' . $value->id) }}">
                                                            <i class="fas fa-edit text-warning mr-2"></i> Edit
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-danger" href="{{ url('admin/associates/trainers/delete/' . $value->id) }}"
                                                           onclick="return confirm('Delete {{ addslashes($value->name) }} from the Trainers roster?')">
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
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </div>
                    <!-- /.col -->
                </div>
                <!-- /.row -->
            </div>
            <!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

</div>
<!-- ./wrapper -->

@endsection

@push('styles')
<style>
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
    #trtable td { vertical-align: middle; }
    .entity-link { color:#a02626; font-weight:500; text-decoration:none; }
    .entity-link:hover { text-decoration:underline; }
    .mini-stat {
        display: flex; align-items: center; gap: 10px; background: #fff; border: 1px solid #eee;
        border-radius: 8px; padding: 8px 12px; margin-bottom: 12px; cursor: pointer; transition: box-shadow .15s;
    }
    .mini-stat:hover { box-shadow: 0 2px 8px rgba(0,0,0,.08); border-color: #ddd; }
    .mini-stat.active { border-color: #a02626; box-shadow: 0 0 0 1px #a02626 inset; }
    .mini-stat-icon {
        width: 30px; height: 30px; border-radius: 7px; display: flex; align-items: center;
        justify-content: center; font-size: .85rem; flex-shrink: 0;
    }
    .mini-stat-val { font-size: 1.15rem; font-weight: 700; line-height: 1.1; color: #222; }
    .mini-stat-lbl { font-size: .68rem; color: #888; text-transform: uppercase; letter-spacing: .03em; }
    body.dark-mode .mini-stat { background:#374151 !important; border-color:#4a5568 !important; }
    body.dark-mode .mini-stat-val { color:#e0e0e0 !important; }
    .paginate_button.active>.page-link { background-color: #a02626 !important; border-color: #a02626 !important; color: white; }
    .paginate_button>.page-link { color: #a02626; }
    .paginate_button>.page-link:focus, .paginate_button.active>.page-link:focus { box-shadow: none !important; outline: none !important; }
    @media print {
        .main-sidebar, .main-header, .main-footer, .no-print, .content-header, .card-header,
        #trFilteredCount, .dataTables_length, .dataTables_filter, .dataTables_paginate, .dataTables_info { display: none !important; }
        .content-wrapper { margin-left: 0 !important; }
        .card { border: none !important; box-shadow: none !important; }
        table.dataTable { width: 100% !important; }
    }
</style>
@endpush
@push('scripts')
<script>
$(document).ready(function () {

    var dt = $('#trtable').DataTable({ pageLength: 50 });

    function getChecked(filterId) {
        return $('.chk-option[data-filter="' + filterId + '"]:checked')
               .map(function () { return String(this.value); }).get();
    }

    function updateBadge(filterId) {
        var checked = getChecked(filterId);
        var $badge  = $('.chk-filter-btn[data-filter="' + filterId + '"] .chk-badge');
        if (checked.length) $badge.text(checked.length).show();
        else $badge.hide();
    }

    function redraw() {
        dt.draw();
        var info = dt.page.info();
        $('#trFilteredCount').text(
            info.recordsDisplay < info.recordsTotal
                ? 'Showing ' + info.recordsDisplay + ' of ' + info.recordsTotal : ''
        );
    }

    // A row's data-country/-year/-specialty holds a comma-separated id list
    // (a trainer can have more than one country or ToT year) — match if ANY
    // checked filter value is present in that row's list.
    function rowMatchesAny(rowValueCsv, checkedValues) {
        if (!checkedValues.length) return true;
        var rowValues = String(rowValueCsv || '').split(',');
        return checkedValues.some(function (v) { return rowValues.indexOf(v) !== -1; });
    }

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'trtable') return true;
        var $row = $($(settings.nTable).DataTable().row(dataIndex).node());
        if (!rowMatchesAny($row.data('year'),      getChecked('trFilterYear')))      return false;
        if (!rowMatchesAny($row.data('country'),   getChecked('trFilterCountry')))   return false;
        if (!rowMatchesAny($row.data('specialty'), getChecked('trFilterSpecialty'))) return false;
        if ($('#trFilterMaster').is(':checked') && String($row.data('master')) !== '1') return false;
        return true;
    });

    $(document).on('click', '.chk-filter-btn', function (e) {
        e.stopPropagation();
        var filterId = $(this).data('filter');
        var $panel   = $('#' + filterId + '-panel');
        $('.chk-filter-panel').not($panel).hide();
        $panel.toggle();
    });
    $(document).on('click', '.chk-filter-panel', function (e) { e.stopPropagation(); });
    $(document).on('click', function () { $('.chk-filter-panel').hide(); });
    $(document).on('input', '.chk-search', function () {
        var q = $(this).val().toLowerCase();
        $(this).closest('.chk-filter-panel').find('.chk-item').each(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(q) !== -1);
        });
    });
    $(document).on('change', '.chk-option', function () {
        updateBadge($(this).data('filter'));
        redraw();
    });
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
    $('#trFilterMaster').on('change', function () { refreshStatActive(); redraw(); });
    $('#trBtnClear').on('click', function () {
        $('.chk-option').prop('checked', false);
        $('#trFilterMaster').prop('checked', false);
        $('.chk-badge').hide();
        trStatFilter = null;
        refreshStatActive();
        redraw();
        $('#trFilteredCount').text('');
    });

    // Stat tiles double as quick filters — clicking one toggles that slice
    // on/off (Master-only / SS-only / unmatched-subspecialty-only), reusing
    // each row's data-* attributes; Total clears every filter back to the
    // full roster.
    var trStatFilter = null; // null | 'ss' | 'sub'
    function refreshStatActive() {
        $('.mini-stat').removeClass('active');
        if ($('#trFilterMaster').is(':checked')) $('#statMaster').addClass('active');
        if (trStatFilter === 'ss')  $('#statSS').addClass('active');
        if (trStatFilter === 'sub') $('#statSub').addClass('active');
    }
    $('#statTotal').on('click', function () {
        $('.chk-option').prop('checked', false);
        $('.chk-badge').hide();
        $('#trFilterMaster').prop('checked', false);
        trStatFilter = null;
        refreshStatActive();
        redraw();
    });
    $('#statMaster').on('click', function () {
        $('#trFilterMaster').prop('checked', !$('#trFilterMaster').is(':checked'));
        refreshStatActive();
        redraw();
    });
    $('#statSS').on('click', function () {
        trStatFilter = (trStatFilter === 'ss') ? null : 'ss';
        refreshStatActive();
        redraw();
    });
    $('#statSub').on('click', function () {
        trStatFilter = (trStatFilter === 'sub') ? null : 'sub';
        refreshStatActive();
        redraw();
    });

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'trtable') return true;
        var $row = $($(settings.nTable).DataTable().row(dataIndex).node());
        if (trStatFilter === 'ss'  && String($row.data('ss'))  !== '1') return false;
        if (trStatFilter === 'sub' && String($row.data('sub')) !== '1') return false;
        return true;
    });

    $('#btnPrint').on('click', function () { window.print(); });

    // Export respects whatever's currently checked, as real query params —
    // the server-side export endpoint applies the same filters.
    $('#btnExport').on('click', function () {
        var params = new URLSearchParams();
        getChecked('trFilterYear').forEach(function (v) { params.append('tot_year_id[]', v); });
        getChecked('trFilterCountry').forEach(function (v) { params.append('country_id[]', v); });
        getChecked('trFilterSpecialty').forEach(function (v) { params.append('programme_id[]', v); });
        if ($('#trFilterMaster').is(':checked')) params.append('master_only', '1');
        window.location = "{{ url('admin/associates/trainers/export') }}?" + params.toString();
    });
});
</script>
@endpush
