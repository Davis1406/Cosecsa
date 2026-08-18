@extends('layout.app')

@push('styles')
<style>
    .page-header-bar { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.2rem; flex-wrap:wrap; gap:.5rem; }

    /* ── Stat tiles ── */
    .ctry-stat { background:#fff; border:1px solid #e9ecef; border-radius:8px; padding:12px 16px;
                 display:flex; align-items:center; gap:12px; }
    .ctry-stat-icon { width:36px; height:36px; border-radius:8px; display:flex; align-items:center;
                       justify-content:center; font-size:1rem; flex-shrink:0; background:#f0d4d4; color:#a02626; }
    .ctry-stat-lbl { font-size:.65rem; color:#999; text-transform:uppercase; letter-spacing:.04em; margin-bottom:1px; }
    .ctry-stat-val { font-size:1.1rem; font-weight:700; color:#222; }
    body.dark-mode .ctry-stat { background:#374151 !important; border-color:#4a5568 !important; }
    body.dark-mode .ctry-stat-val { color:#e0e0e0 !important; }

    /* ── Filter bar ── */
    .ctry-filter-bar { display:flex; align-items:center; flex-wrap:wrap; gap:.6rem; margin:1rem 0; }
    .ctry-filter-bar .form-control { max-width:260px; }
    body.dark-mode .ctry-filter-bar .form-control { background:#374151; border-color:#4a5568; color:#e0e0e0; }
    body.dark-mode .ctry-filter-bar .custom-control-label { color:#cbd5e0; }

    /* ── Visual report panel ── */
    #ctryReportPanel { display:none; }
    .chart-card { background:#fff; border:1px solid #e9ecef; border-radius:8px; padding:16px; height:100%; }
    .chart-card-title { font-size:.78rem; font-weight:700; text-transform:uppercase;
                        letter-spacing:.07em; color:#a02626; margin-bottom:12px; }
    body.dark-mode .chart-card { background:#374151 !important; border-color:#4a5568 !important; }

    /* ── Country cards — one restrained accent color, differentiated by icon ── */
    .country-card { background:#fff; border:1px solid #e9ecef; border-radius:10px; padding:16px 18px;
                    transition:box-shadow .15s, border-color .15s; text-decoration:none; display:block; color:inherit; }
    .country-card:hover { box-shadow:0 6px 18px rgba(160,38,38,.12); border-color:#e3bcbc; text-decoration:none; color:inherit; }
    .country-card h6 { font-weight:700; color:#222; margin:0 0 10px; font-size:.92rem; display:flex; align-items:center; gap:7px; }
    .country-mini { display:flex; gap:6px; flex-wrap:wrap; }
    .country-mini-chip { font-size:.71rem; background:#f8f0f0; color:#a02626; border-radius:5px;
                         padding:3px 8px; font-weight:600; display:inline-flex; align-items:center; gap:4px; }
    .country-mini-empty { font-size:.72rem; color:#bbb; font-style:italic; }
    body.dark-mode .country-card { background:#374151; border-color:#4a5568; }
    body.dark-mode .country-card h6 { color:#e0e0e0; }
    body.dark-mode .country-mini-chip { background:#4a5568; color:#f0a8a8; }
    body.dark-mode .country-mini-empty { color:#8794a8; }
</style>
@endpush

@section('content')
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header"></section>
        <div class="col-md-12">
            @include('_message')
        </div>

        <section class="content">
            <div class="container-wrapper">
                <div class="page-header-bar">
                    <h5 class="mb-0 font-weight-bold" style="color:#a02626;">
                        <i class="fas fa-globe-africa mr-2"></i>Countries
                        <span class="badge badge-secondary ml-1">{{ count($countries) }}</span>
                    </h5>
                    <div class="d-flex flex-wrap" style="gap:.5rem;">
                        <button id="ctryBtnToggleReport" class="btn btn-sm" style="background:#a02626;color:#fff;border:none;">
                            <i class="fas fa-chart-bar mr-1"></i> Visual Report
                        </button>
                        <button id="ctryBtnExportCsv" class="btn btn-sm btn-warning">
                            <i class="fas fa-file-csv mr-1"></i> Export CSV
                        </button>
                        <button id="ctryBtnPrint" class="btn btn-sm btn-secondary">
                            <i class="fas fa-print mr-1"></i> Print
                        </button>
                    </div>
                </div>

                {{-- ── Stat tiles ── --}}
                @php
                    $totalCountries   = count($countries);
                    $withHospitals    = collect($countries)->where('hospital_count', '>', 0)->count();
                    $withRecords      = collect($countries)->filter(fn($c) => $c->hospital_count || $c->trainee_count || $c->fellow_count || $c->member_count)->count();
                    $totalFellows     = collect($countries)->sum('fellow_count');
                @endphp
                <div class="row mb-2" style="row-gap:.6rem;">
                    <div class="col-6 col-md-3">
                        <div class="ctry-stat">
                            <div class="ctry-stat-icon"><i class="fas fa-globe-africa"></i></div>
                            <div><div class="ctry-stat-lbl">Total Countries</div><div class="ctry-stat-val">{{ $totalCountries }}</div></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="ctry-stat">
                            <div class="ctry-stat-icon"><i class="fas fa-check-circle"></i></div>
                            <div><div class="ctry-stat-lbl">With Records</div><div class="ctry-stat-val">{{ $withRecords }}</div></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="ctry-stat">
                            <div class="ctry-stat-icon"><i class="fas fa-hospital-alt"></i></div>
                            <div><div class="ctry-stat-lbl">With Hospitals</div><div class="ctry-stat-val">{{ $withHospitals }}</div></div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="ctry-stat">
                            <div class="ctry-stat-icon"><i class="fas fa-award"></i></div>
                            <div><div class="ctry-stat-lbl">Total Fellows</div><div class="ctry-stat-val">{{ $totalFellows }}</div></div>
                        </div>
                    </div>
                </div>

                {{-- ── Visual report panel ── --}}
                <div id="ctryReportPanel" class="mb-3">
                    <div class="row" style="row-gap:.75rem;">
                        <div class="col-md-6">
                            <div class="chart-card">
                                <div class="chart-card-title"><i class="fas fa-award mr-1"></i>Top 10 Countries by Fellows</div>
                                <canvas id="ctryChartFellows" height="220"></canvas>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="chart-card">
                                <div class="chart-card-title"><i class="fas fa-hospital-alt mr-1"></i>Top 10 Countries by Hospitals</div>
                                <canvas id="ctryChartHospitals" height="220"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ── Filter bar ── --}}
                <div class="ctry-filter-bar">
                    <input type="text" id="ctrySearch" class="form-control form-control-sm" placeholder="Search countries…" autocomplete="off">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="ctryShowEmpty">
                        <label class="custom-control-label" for="ctryShowEmpty" style="font-size:.85rem;">Show countries with no records</label>
                    </div>
                    <small class="text-muted ml-auto" id="ctryFilteredCount"></small>
                </div>

                <div class="row" id="ctryCardRow" style="row-gap:.75rem;">
                    @foreach($countries as $c)
                    @php
                        $hasRecords = $c->hospital_count || $c->trainee_count || $c->fellow_count || $c->member_count;
                    @endphp
                    <div class="col-sm-6 col-md-4 col-lg-3 ctry-card-col" data-name="{{ strtolower($c->country_name) }}" data-has-records="{{ $hasRecords ? 1 : 0 }}">
                        <a href="{{ url('admin/countries/view/'.$c->id) }}" class="country-card">
                            <h6><i class="fas fa-flag" style="color:#a02626;"></i>{{ $c->country_name }}</h6>
                            <div class="country-mini">
                                @if($c->hospital_count)
                                <span class="country-mini-chip"><i class="fas fa-hospital-alt"></i>{{ $c->hospital_count }} Hospital{{ $c->hospital_count != 1 ? 's' : '' }}</span>
                                @endif
                                @if($c->trainee_count)
                                <span class="country-mini-chip"><i class="fas fa-user-graduate"></i>{{ $c->trainee_count }} Trainee{{ $c->trainee_count != 1 ? 's' : '' }}</span>
                                @endif
                                @if($c->fellow_count)
                                <span class="country-mini-chip"><i class="fas fa-award"></i>{{ $c->fellow_count }} Fellow{{ $c->fellow_count != 1 ? 's' : '' }}</span>
                                @endif
                                @if($c->member_count)
                                <span class="country-mini-chip"><i class="fas fa-users"></i>{{ $c->member_count }} Member{{ $c->member_count != 1 ? 's' : '' }}</span>
                                @endif
                                @if(!$hasRecords)
                                <span class="country-mini-empty">No records yet</span>
                                @endif
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    var $rows = $('.ctry-card-col');

    function applyFilters() {
        var q = $('#ctrySearch').val().toLowerCase().trim();
        var showEmpty = $('#ctryShowEmpty').is(':checked');
        var visible = 0;

        $rows.each(function () {
            var $row = $(this);
            var matchesSearch = !q || $row.data('name').indexOf(q) !== -1;
            var matchesEmpty = showEmpty || $row.data('has-records') == 1;
            var show = matchesSearch && matchesEmpty;
            $row.toggle(show);
            if (show) visible++;
        });

        $('#ctryFilteredCount').text('Showing ' + visible + ' of ' + $rows.length);
    }

    $('#ctrySearch').on('input', applyFilters);
    $('#ctryShowEmpty').on('change', applyFilters);
    applyFilters();

    // ── Visual report ──
    var reportVisible = false;
    var chartsInited = false;
    $('#ctryBtnToggleReport').on('click', function () {
        reportVisible = !reportVisible;
        $('#ctryReportPanel').slideToggle(220);
        $(this).html(reportVisible
            ? '<i class="fas fa-times mr-1"></i> Close Report'
            : '<i class="fas fa-chart-bar mr-1"></i> Visual Report');
        if (reportVisible && !chartsInited) {
            chartsInited = true;
            initCharts();
        }
    });

    function initCharts() {
        var countries = @json($countries->map(fn($c) => ['name' => $c->country_name, 'fellows' => (int) $c->fellow_count, 'hospitals' => (int) $c->hospital_count]));

        var byFellows = countries.slice().sort(function (a, b) { return b.fellows - a.fellows; }).slice(0, 10);
        var byHospitals = countries.slice().sort(function (a, b) { return b.hospitals - a.hospitals; }).slice(0, 10);

        new Chart(document.getElementById('ctryChartFellows').getContext('2d'), {
            type: 'bar',
            data: {
                labels: byFellows.map(function (c) { return c.name; }),
                datasets: [{ label: 'Fellows', data: byFellows.map(function (c) { return c.fellows; }),
                             backgroundColor: 'rgba(160,38,38,.75)', borderColor: '#a02626', borderWidth: 1, borderRadius: 4 }]
            },
            options: { indexAxis: 'y', responsive: true,
                       plugins: { legend: { display: false }, datalabels: { display: false } },
                       scales: { x: { beginAtZero: true } } }
        });

        new Chart(document.getElementById('ctryChartHospitals').getContext('2d'), {
            type: 'bar',
            data: {
                labels: byHospitals.map(function (c) { return c.name; }),
                datasets: [{ label: 'Hospitals', data: byHospitals.map(function (c) { return c.hospitals; }),
                             backgroundColor: 'rgba(57,73,171,.7)', borderColor: '#3949ab', borderWidth: 1, borderRadius: 4 }]
            },
            options: { indexAxis: 'y', responsive: true,
                       plugins: { legend: { display: false }, datalabels: { display: false } },
                       scales: { x: { beginAtZero: true } } }
        });
    }

    // ── Export CSV (only the currently-filtered/visible countries) ──
    $('#ctryBtnExportCsv').on('click', function () {
        var rows = [['Country', 'Hospitals', 'Trainees', 'Fellows', 'Members']];
        $rows.filter(':visible').each(function () {
            var $card = $(this);
            rows.push([
                $card.find('h6').text().trim(),
                $card.find('.country-mini-chip:contains("Hospital")').text().replace(/\D+/g, '') || 0,
                $card.find('.country-mini-chip:contains("Trainee")').text().replace(/\D+/g, '') || 0,
                $card.find('.country-mini-chip:contains("Fellow")').text().replace(/\D+/g, '') || 0,
                $card.find('.country-mini-chip:contains("Member")').text().replace(/\D+/g, '') || 0,
            ]);
        });
        var csv = rows.map(function (r) {
            return r.map(function (v) { return '"' + String(v).replace(/"/g, '""') + '"'; }).join(',');
        }).join('\n');
        var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
        var link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'countries-' + new Date().toISOString().slice(0, 10) + '.csv';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    $('#ctryBtnPrint').on('click', function () { window.print(); });
});
</script>
@endpush
