@extends('layout.app')

@push('styles')
<style>
    /* ── Stat chips ── */
    .hp-stat { border-radius:8px; padding:12px 16px; display:flex; align-items:center; gap:12px;
               background:#fff; border:1px solid #e9ecef; }
    .hp-stat-icon { width:38px; height:38px; border-radius:8px; display:flex; align-items:center;
                    justify-content:center; font-size:1rem; flex-shrink:0; }
    .hp-stat-lbl { font-size:.68rem; color:#999; margin-bottom:1px; }
    .hp-stat-val { font-size:1rem; font-weight:700; color:#222; }

    /* ── Visual report panel ── */
    #hpReportPanel { display:none; }
    .chart-card { background:#fff; border:1px solid #e9ecef; border-radius:8px; padding:16px; height:100%; }
    .chart-card-title { font-size:.78rem; font-weight:700; text-transform:uppercase;
                        letter-spacing:.07em; color:#a02626; margin-bottom:12px; }

    /* ── Filter bar ── */
    .chk-filter-wrap  { position:relative; display:inline-block; }
    .chk-filter-panel { position:absolute; top:calc(100% + 4px); left:0; z-index:1055;
                        background:#fff; border:1px solid #ced4da; border-radius:6px;
                        min-width:200px; max-width:280px; padding:8px;
                        box-shadow:0 4px 12px rgba(0,0,0,.12); }
    .chk-list  { max-height:220px; overflow-y:auto; }
    .chk-item  { display:flex; align-items:center; gap:6px; padding:3px 2px;
                 font-size:.82rem; font-weight:normal; cursor:pointer; white-space:nowrap; margin:0; }
    .chk-item:hover { background:#f8f0f0; border-radius:4px; }
    .chk-item input[type="checkbox"] { margin:0; cursor:pointer; accent-color:#a02626; }
    .chk-footer { display:flex; justify-content:space-between; border-top:1px solid #eee;
                  margin-top:6px; padding-top:5px; font-size:.78rem; }
    .chk-footer a { color:#6c757d; }
    .chk-footer a:hover { color:#a02626; text-decoration:none; }
    .chk-filter-btn { white-space:nowrap; }

    /* ── Table ── */
    #hospitalProgrammesTable td { vertical-align:middle; font-size:.875rem; }
    .dot { display:inline-block; width:8px; height:8px; border-radius:50%; margin-right:5px; }
    .dot-active  { background:#22c55e; }
    .dot-expired { background:#ef4444; }

    .action-btn { padding:2px 8px; line-height:1.4; border-radius:4px; }
    .action-btn:hover { background-color:#f0f0f0; }
    .dropdown-menu { min-width:130px; font-size:.875rem; }
    .dropdown-item { padding:6px 14px; }
    .dropdown-item:hover { background-color:#f8f0f0; }
    .paginate_button.active>.page-link { background-color:#a02626 !important; border-color:#a02626 !important; color:white; }
    .paginate_button>.page-link { color:#a02626; }
    .paginate_button>.page-link:focus, .paginate_button.active>.page-link:focus { box-shadow:none !important; outline:none !important; }

    /* dark mode */
    body.dark-mode .hp-stat      { background:#374151 !important; border-color:#4a5568 !important; }
    body.dark-mode .hp-stat-lbl  { color:#9ca3af !important; }
    body.dark-mode .hp-stat-val  { color:#e0e0e0 !important; }
    body.dark-mode .chart-card   { background:#374151 !important; border-color:#4a5568 !important; }
    body.dark-mode .chk-filter-panel { background:#374151 !important; border-color:#4a5568 !important; }
    body.dark-mode .chk-item     { color:#e0e0e0 !important; }
    body.dark-mode .chk-item:hover { background:#4a5568 !important; }
    body.dark-mode .chk-footer   { border-top-color:#4a5568 !important; }
    body.dark-mode .chk-footer a { color:#9ca3af !important; }
    body.dark-mode .dropdown-item:hover { background-color:#4a5568 !important; color:#fff !important; }
</style>
@endpush

@section('content')
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header"></section>
        <div class="col-md-12">@include('_message')</div>

        <section class="content">
            <div class="container-wrapper">

                {{-- ── Header bar ── --}}
                <div class="d-flex align-items-center justify-content-between flex-wrap mb-3" style="gap:.5rem;">
                    <h5 class="mb-0 font-weight-bold" style="color:#a02626;">
                        <i class="fas fa-hospital-alt mr-2"></i>Hospital Programmes
                        <span class="badge badge-secondary ml-1" style="font-size:.75rem;">{{ $totalAccreditations }}</span>
                    </h5>
                    <div class="d-flex flex-wrap" style="gap:.5rem;">
                        <button id="btnToggleReport" class="btn btn-sm" style="background:#a02626;color:#fff;border:none;">
                            <i class="fas fa-chart-bar mr-1"></i> Visual Report
                        </button>
                        <a href="{{ url('admin/hospitalprogrammes/import') }}" class="btn btn-sm btn-warning" style="color:#000;">
                            <i class="fas fa-upload mr-1"></i> Import
                        </a>
                        <a href="{{ url('admin/hospitalprogrammes/add') }}" class="btn btn-sm btn-primary" style="background:#a02626;border-color:#a02626;">
                            <i class="fas fa-plus mr-1"></i> Assign Programme
                        </a>
                    </div>
                </div>

                {{-- ── Visual Report Panel ── --}}
                <div id="hpReportPanel" class="mb-3">
                    {{-- Stat chips --}}
                    <div class="row mb-3" style="row-gap:.75rem;">
                        @php
                        $hpStats = [
                            ['icon'=>'fas fa-hospital-alt', 'bg'=>'#f0d4d4','ic'=>'#a02626', 'label'=>'Total Accreditations', 'val'=>$totalAccreditations],
                            ['icon'=>'fas fa-circle',        'bg'=>'#e8f5e9','ic'=>'#388e3c', 'label'=>'Active',               'val'=>$totalActive],
                            ['icon'=>'fas fa-circle',        'bg'=>'#fce8e8','ic'=>'#c62828', 'label'=>'Expired',              'val'=>$totalExpired],
                        ];
                        @endphp
                        @foreach($hpStats as $s)
                        <div class="col-6 col-sm-4 col-md-3">
                            <div class="hp-stat">
                                <div class="hp-stat-icon" style="background:{{ $s['bg'] }};color:{{ $s['ic'] }};">
                                    <i class="{{ $s['icon'] }}"></i>
                                </div>
                                <div>
                                    <div class="hp-stat-lbl">{{ $s['label'] }}</div>
                                    <div class="hp-stat-val">{{ $s['val'] }}</div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Charts --}}
                    <div class="row" style="row-gap:.75rem;">
                        <div class="col-md-6">
                            <div class="chart-card">
                                <div class="chart-card-title"><i class="fas fa-stethoscope mr-1"></i>Accreditations by Programme</div>
                                <canvas id="chartByProgramme" height="220"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="chart-card">
                                <div class="chart-card-title"><i class="fas fa-globe-africa mr-1"></i>Accreditations by Country</div>
                                <canvas id="chartByCountry" height="220"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Filter Bar ── --}}
                @php
                $programmes = $getHospitalProgrammes->pluck('programme_name')->filter()->unique()->sort()->values();
                $countries  = $getHospitalProgrammes->pluck('country_name')->filter()->unique()->sort()->values();
                $hpFilterDefs = [
                    ['id'=>'hpFilterProgramme', 'label'=>'Programme', 'options'=>$programmes],
                    ['id'=>'hpFilterCountry',   'label'=>'Country',   'options'=>$countries],
                    ['id'=>'hpFilterStatus',    'label'=>'Status',    'options'=>collect(['Active','Expired'])],
                ];
                @endphp
                <div class="card card-outline card-secondary mb-2 shadow-sm">
                    <div class="card-body py-2">
                        <div class="d-flex flex-wrap align-items-center" style="gap:.5rem;">
                            @foreach($hpFilterDefs as $fd)
                            <div class="chk-filter-wrap" data-filter="{{ $fd['id'] }}">
                                <button type="button" class="btn btn-sm btn-outline-secondary chk-filter-btn" data-filter="{{ $fd['id'] }}">
                                    {{ $fd['label'] }}
                                    <span class="badge badge-danger chk-badge ml-1" style="display:none;font-size:.65rem;"></span>
                                    <i class="fas fa-caret-down ml-1" style="font-size:.7rem;"></i>
                                </button>
                                <div class="chk-filter-panel shadow" id="{{ $fd['id'] }}-panel" style="display:none;">
                                    @if(count($fd['options']) > 6)
                                    <input type="text" class="form-control form-control-sm chk-search mb-1" placeholder="Search…" autocomplete="off">
                                    @endif
                                    <div class="chk-list">
                                        @foreach($fd['options'] as $opt)
                                        <label class="chk-item">
                                            <input type="checkbox" class="chk-option" data-filter="{{ $fd['id'] }}" value="{{ $opt }}">
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
                            <button id="hpBtnClear" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-times mr-1"></i>Clear All
                            </button>
                            <small class="text-muted ml-auto" id="hpFilteredCount"></small>
                        </div>
                    </div>
                </div>

                {{-- ── Table ── --}}
                <div class="card">
                    <div class="card-body">
                        <table id="hospitalProgrammesTable" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Hospital</th>
                                    <th>Programme</th>
                                    <th>Country</th>
                                    <th>Accredited</th>
                                    <th>Expires</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($getHospitalProgrammes as $index => $data)
                                @php $statusLow = strtolower($data->status); @endphp
                                <tr
                                    data-programme="{{ $data->programme_name }}"
                                    data-country="{{ $data->country_name }}"
                                    data-status="{{ $data->status }}"
                                >
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $data->hospital_name }}</td>
                                    <td>{{ $data->programme_name }}</td>
                                    <td>{{ $data->country_name }}</td>
                                    <td>{{ $data->accredited_date }}</td>
                                    <td>{{ $data->expiry_date }}</td>
                                    <td>
                                        <span class="dot dot-{{ $statusLow }}"></span>{{ $data->status }}
                                    </td>
                                    <td class="text-center" style="white-space:nowrap;">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border dropdown-toggle action-btn"
                                                    type="button" data-toggle="dropdown"
                                                    aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                                <a class="dropdown-item" href="{{ url('admin/hospitalprogrammes/edit/' . $data->id) }}">
                                                    <i class="fas fa-edit text-warning mr-2"></i> Edit
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <a class="dropdown-item text-danger"
                                                   href="{{ url('admin/hospitalprogrammes/delete/' . $data->id) }}"
                                                   onclick="return confirm('Delete this programme record?')">
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
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function () {

    // ── Visual report toggle ─────────────────────────────────────────────────
    var reportVisible = false;
    $('#btnToggleReport').on('click', function () {
        reportVisible = !reportVisible;
        $('#hpReportPanel').slideToggle(220);
        $(this).html(reportVisible
            ? '<i class="fas fa-times mr-1"></i> Close Report'
            : '<i class="fas fa-chart-bar mr-1"></i> Visual Report');
        if (reportVisible) initCharts();
    });

    var chartsInited = false;
    function initCharts() {
        if (chartsInited) return;
        chartsInited = true;

        var progLabels = {!! json_encode($byProgramme->keys()->values()) !!};
        var progData   = {!! json_encode($byProgramme->values()->values()) !!};
        var ctyLabels  = {!! json_encode($byCountry->keys()->values()) !!};
        var ctyData    = {!! json_encode($byCountry->values()->values()) !!};

        // Bar — by programme
        new Chart(document.getElementById('chartByProgramme').getContext('2d'), {
            type: 'bar',
            data: {
                labels: progLabels,
                datasets: [{
                    label: 'Accreditations',
                    data: progData,
                    backgroundColor: 'rgba(160,38,38,.75)',
                    borderColor: '#a02626',
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false }, datalabels: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { stepSize: 5 } } }
            }
        });

        // Bar — by country
        new Chart(document.getElementById('chartByCountry').getContext('2d'), {
            type: 'bar',
            data: {
                labels: ctyLabels,
                datasets: [{
                    label: 'Accreditations',
                    data: ctyData,
                    backgroundColor: 'rgba(57,73,171,.7)',
                    borderColor: '#3949ab',
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: { legend: { display: false }, datalabels: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { stepSize: 5 } } }
            }
        });
    }

    // ── Filter helpers ───────────────────────────────────────────────────────
    function getChecked(filterId) {
        return $('.chk-option[data-filter="' + filterId + '"]:checked')
               .map(function () { return String(this.value); }).get();
    }
    function updateBadge(filterId) {
        var n = getChecked(filterId).length;
        var $b = $('.chk-filter-btn[data-filter="' + filterId + '"] .chk-badge');
        n ? $b.text(n).show() : $b.hide();
    }
    function redraw() {
        var dt = $('#hospitalProgrammesTable').DataTable();
        dt.draw();
        var info = dt.page.info();
        $('#hpFilteredCount').text(
            info.recordsDisplay < info.recordsTotal
                ? 'Showing ' + info.recordsDisplay + ' of ' + info.recordsTotal : ''
        );
    }

    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        if (settings.nTable.id !== 'hospitalProgrammesTable') return true;
        var $row = $($(settings.nTable).DataTable().row(dataIndex).node());
        var chkProg    = getChecked('hpFilterProgramme');
        var chkCountry = getChecked('hpFilterCountry');
        var chkStatus  = getChecked('hpFilterStatus');
        if (chkProg.length    && chkProg.indexOf(String($row.data('programme') || '')) === -1) return false;
        if (chkCountry.length && chkCountry.indexOf(String($row.data('country')   || '')) === -1) return false;
        if (chkStatus.length  && chkStatus.indexOf(String($row.data('status')     || '')) === -1) return false;
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
    $('#hpBtnClear').on('click', function () {
        $('.chk-option').prop('checked', false);
        $('.chk-badge').hide();
        redraw();
        $('#hpFilteredCount').text('');
    });
});
</script>
@endpush
