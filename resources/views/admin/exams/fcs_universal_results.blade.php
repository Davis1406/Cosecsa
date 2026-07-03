@extends('layout.app')

@section('content')
    <div class="wrapper">
        <div class="content-wrapper">
            <section class="content-header">
            </section>

            <div class="col-md-12">
                @include('_message')
            </div>

            <section class="content">
                <div class="container-wrapper">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap:.5rem;">
                                    <h3 class="card-title mb-0">{{ $programmeName }} Results — {{ $selectedYearName }}</h3>
                                    <form method="GET" action="{{ request()->url() }}" class="d-flex align-items-center flex-shrink-0" style="gap:.4rem;">
                                        <select name="year_id" class="form-control form-control-sm flex-shrink-0" style="width:110px;">
                                            @foreach($allYears as $yr)
                                                <option value="{{ $yr->id }}" {{ $selectedYearId == $yr->id ? 'selected' : '' }}>
                                                    {{ $yr->year_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="btn btn-danger flex-shrink-0" style="white-space:nowrap;padding:4px 12px;font-size:.85rem;">
                                            Filter
                                        </button>
                                    </form>
                                </div>

                                <div class="card-body">
                                    {{-- Group filter --}}
                                    @php
                                    $fcsGroups = $getResults->pluck('group_name')->filter()->unique()->sort()->values();
                                    @endphp
                                    @if($fcsGroups->count())
                                    <div class="mb-3 d-flex flex-wrap align-items-center" style="gap:.5rem;">
                                        <div class="chk-filter-wrap" data-filter="fcsFilterGroup">
                                            <button type="button" class="btn btn-sm btn-outline-secondary chk-filter-btn" data-filter="fcsFilterGroup">
                                                Group
                                                <span class="badge badge-danger chk-badge ml-1" style="display:none;font-size:.65rem;"></span>
                                                <i class="fas fa-caret-down ml-1" style="font-size:.7rem;"></i>
                                            </button>
                                            <div class="chk-filter-panel shadow" id="fcsFilterGroup-panel" style="display:none;">
                                                <div class="chk-list">
                                                    @foreach($fcsGroups as $g)
                                                    <label class="chk-item">
                                                        <input type="checkbox" class="chk-option" data-filter="fcsFilterGroup" value="{{ $g }}">
                                                        {{ $g }}
                                                    </label>
                                                    @endforeach
                                                </div>
                                                <div class="chk-footer">
                                                    <a href="#" class="chk-select-all small">All</a>
                                                    <a href="#" class="chk-clear small text-danger">Clear</a>
                                                </div>
                                            </div>
                                        </div>
                                        <button id="fcsBtnClear" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-times mr-1"></i>Clear
                                        </button>
                                        <small class="text-muted ml-auto" id="fcsFilteredCount"></small>
                                    </div>
                                    @endif

                                    <table id="fcsresultstable" class="table table-bordered table-striped">
                                        <thead>
                                        <tr>
                                            <th rowspan="2">Candidate ID</th>
                                            <th rowspan="2">Name</th>
                                            <th rowspan="2">Group</th>
                                            <th colspan="8" class="text-center" style="background-color: #a02626; color: white;">Clinical Stations</th>
                                            <th colspan="8" class="text-center" style="background-color: #FEC503; color: #a02626;">Viva Stations</th>
                                            <th rowspan="2">Clinical Total</th>
                                            <th rowspan="2">Viva Total</th>
                                            <th rowspan="2">Overall Total</th>
                                        </tr>
                                        <tr>
                                            <!-- Clinical Station Headers -->
                                            @for ($i = 1; $i <= 8; $i++)
                                                <th style="background-color: #d94545; color: white;">S{{ $i }}</th>
                                            @endfor
                                            <!-- Viva Station Headers -->
                                            @for ($i = 1; $i <= 8; $i++)
                                                <th style="background-color: #ffd966; color: #a02626;">S{{ $i }}</th>
                                            @endfor
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($getResults as $value)
                                            <tr data-group="{{ $value->group_name ?? '' }}">
                                                <td>{{ $value->candidate_id }}</td>
                                                <td>{{ $value->fullname }}</td>
                                                <td>{{ $value->group_name }}</td>

                                                <!-- Clinical Stations -->
                                                @for ($i = 1; $i <= 8; $i++)
                                                    <td style="background-color: #ffe6e6;">
                                                        @if(isset($value->clinical_stations[$i]))
                                                            <a href="{{ url('admin/exams/fcs-station-results/' . $value->cnd_id . '/' . $i . '/clinical/' . $tableRoute) }}"
                                                               style="color: inherit; text-decoration: none;">
                                                                {{ $value->clinical_stations[$i] }}
                                                            </a>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                @endfor

                                                <!-- Viva Stations -->
                                                @for ($i = 1; $i <= 8; $i++)
                                                    <td style="background-color: #fff9e6;">
                                                        @if(isset($value->viva_stations[$i]))
                                                            <a href="{{ url('admin/exams/fcs-station-results/' . $value->cnd_id . '/' . $i . '/viva/' . $tableRoute) }}"
                                                               style="color: inherit; text-decoration: none;">
                                                                {{ $value->viva_stations[$i] }}
                                                            </a>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                @endfor

                                                <td><b style="color: #a02626;">{{ $value->clinical_total }}</b></td>
                                                <td><b style="color: #FEC503;">{{ $value->viva_total }}</b></td>
                                                <td><b>{{ $value->overall_total }}</b></td>
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
    .chk-filter-wrap { position: relative; display: inline-block; }
    .chk-filter-panel {
        position: absolute; top: calc(100% + 4px); left: 0; z-index: 1055;
        background: #fff; border: 1px solid #ced4da; border-radius: 6px;
        min-width: 160px; max-width: 240px; padding: 8px;
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
        var dt   = $('#fcsresultstable').DataTable();
        dt.draw();
        var info = dt.page.info();
        $('#fcsFilteredCount').text(
            info.recordsDisplay < info.recordsTotal
                ? 'Showing ' + info.recordsDisplay + ' of ' + info.recordsTotal : ''
        );
    }

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'fcsresultstable') return true;
        var $row     = $($(settings.nTable).DataTable().row(dataIndex).node());
        var chkGroup = getChecked('fcsFilterGroup');
        if (chkGroup.length && chkGroup.indexOf(String($row.data('group') || '')) === -1) return false;
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
    $('#fcsBtnClear').on('click', function () {
        $('.chk-option').prop('checked', false);
        $('.chk-badge').hide();
        redraw();
        $('#fcsFilteredCount').text('');
    });
});
</script>
@endpush
