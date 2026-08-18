@extends('layout.app')

@push('styles')
<style>
    .page-header-bar { display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:1.4rem; flex-wrap:wrap; gap:1rem; }
    .page-header-bar h5 { font-size:1.35rem; }
    .page-subtitle { color:#888; font-size:.85rem; max-width:480px; margin:.3rem 0 0; }
    body.dark-mode .page-subtitle { color:#9ca3af !important; }

    #ctryBtnToggleReport { transition:.15s; }
    #ctryBtnToggleReport:hover { background:#870f0f !important; color:#FEC503 !important; }

    /* ── Globe / network map panel ── */
    .ctry-globe-panel { background:#fff; border:1px solid #e9ecef; border-radius:14px; padding:24px;
                         display:flex; align-items:center; gap:24px; flex-wrap:wrap; margin-bottom:1.4rem;
                         box-shadow:0 4px 18px rgba(0,0,0,.03); }
    .ctry-globe-panel h2 { font-size:1.15rem; font-weight:700; color:#222; margin:0 0 8px; }
    .ctry-globe-panel p { color:#888; font-size:.85rem; max-width:420px; margin:0 0 14px; line-height:1.5; }
    .ctry-globe-copy { flex:1 1 260px; min-width:220px; }
    .ctry-globe-canvas-wrap { flex:0 0 auto; display:flex; align-items:center; justify-content:center; margin:0 auto; }
    #ctryGlobe { cursor:grab; }
    #ctryGlobe:active { cursor:grabbing; }
    #ctryResetGlobe { transition:.15s; }
    #ctryResetGlobe:hover { background:#FEC503 !important; border-color:#e0c060 !important; color:#3a2a00 !important; }
    body.dark-mode #ctryResetGlobe:hover { background:#FEC503 !important; color:#3a2a00 !important; }
    body.dark-mode .ctry-globe-panel { background:#374151 !important; border-color:#4a5568 !important; }
    body.dark-mode .ctry-globe-panel h2 { color:#e0e0e0 !important; }
    body.dark-mode .ctry-globe-panel p { color:#9ca3af !important; }
    .globe-sphere { fill:#f6f7fb; stroke:#d7dbe6; stroke-width:1px; }
    body.dark-mode .globe-sphere { fill:#2b3040; stroke:#4a5568; }
    .globe-graticule { fill:none; stroke:#e3e6ee; stroke-width:.5px; }
    body.dark-mode .globe-graticule { stroke:#3d4260; }
    .globe-country { fill:#e6d3d3; stroke:#fff; stroke-width:.5px; cursor:pointer; transition:fill .15s; }
    body.dark-mode .globe-country { stroke:#374151; }
    .globe-country.has-network { fill:#f0cf7a; }
    .globe-country:hover { fill:#FEC503; }
    .globe-country.active { fill:#a02626; stroke:#FEC503; stroke-width:1.25px; }

    /* ── Stat tiles ── */
    .ctry-stat { background:#fff; border:1px solid #e9ecef; border-radius:12px; padding:14px 16px; }
    .ctry-stat-icon { width:32px; height:32px; border-radius:8px; display:flex; align-items:center;
                       justify-content:center; font-size:.9rem; margin-bottom:10px; background:#f0d4d4; color:#a02626; }
    .ctry-stat-lbl { font-size:.62rem; color:#999; text-transform:uppercase; letter-spacing:.06em; font-weight:700; margin-bottom:2px; }
    .ctry-stat-val { font-size:1.5rem; font-weight:700; color:#222; }
    body.dark-mode .ctry-stat { background:#374151 !important; border-color:#4a5568 !important; }
    body.dark-mode .ctry-stat-icon { background:#4a3030 !important; color:#f0a8a8 !important; }
    body.dark-mode .ctry-stat-val { color:#e0e0e0 !important; }

    /* ── Filter bar ── */
    .ctry-filter-bar { display:flex; align-items:center; flex-wrap:wrap; gap:.6rem; margin:1.4rem 0 .8rem; }
    .ctry-filter-bar .form-control { max-width:280px; border-radius:20px; }
    body.dark-mode .ctry-filter-bar .form-control { background:#374151; border-color:#4a5568; color:#e0e0e0; }
    body.dark-mode .ctry-filter-bar .custom-control-label { color:#cbd5e0; }

    /* ── Visual report panel ── */
    #ctryReportPanel { display:none; }
    .chart-card { background:#fff; border:1px solid #e9ecef; border-radius:8px; padding:16px; height:100%; }
    .chart-card-title { font-size:.78rem; font-weight:700; text-transform:uppercase;
                        letter-spacing:.07em; color:#a02626; margin-bottom:12px; }
    body.dark-mode .chart-card { background:#374151 !important; border-color:#4a5568 !important; }

    /* ── Directory ── */
    .ctry-directory-hd { display:flex; justify-content:space-between; align-items:center; margin-bottom:.8rem; }
    .ctry-directory-hd h6 { font-weight:700; color:#222; margin:0; font-size:1rem; }
    body.dark-mode .ctry-directory-hd h6 { color:#e0e0e0 !important; }
    .ctry-count-pill { font-size:.78rem; color:#888; background:#f4f4f6; padding:3px 10px; border-radius:20px; }
    body.dark-mode .ctry-count-pill { background:#4a5568 !important; color:#cbd5e0 !important; }

    #country-list { background:#fff; border:1px solid #e9ecef; border-radius:14px; overflow:hidden;
                    box-shadow:0 4px 18px rgba(0,0,0,.03); }
    body.dark-mode #country-list { background:#374151 !important; border-color:#4a5568 !important; }

    .country-row { display:flex; align-items:center; justify-content:space-between; gap:1rem; flex-wrap:wrap;
                   padding:16px 22px; border-bottom:1px solid #eee; border-left:3px solid transparent;
                   text-decoration:none; color:inherit; transition:background .15s, border-color .15s; }
    .country-row:last-child { border-bottom:none; }
    .country-row:hover { background:linear-gradient(90deg, rgba(254,197,3,.10), rgba(160,38,38,.03) 60%);
                          border-left-color:#FEC503; text-decoration:none; color:inherit; }
    body.dark-mode .country-row { border-bottom-color:#4a5568 !important; }
    body.dark-mode .country-row:hover { background:linear-gradient(90deg, rgba(254,197,3,.12), rgba(160,38,38,.10) 60%) !important; }

    .country-row-left { display:flex; align-items:center; gap:14px; min-width:200px; }
    .country-row-icon { width:38px; height:38px; border-radius:50%; background:#f4f0f0; display:flex;
                         align-items:center; justify-content:center; color:#a02626; flex-shrink:0; font-size:.95rem;
                         transition:background .2s, color .2s; }
    .country-row:hover .country-row-icon { background:linear-gradient(135deg, #a02626, #d68f00); color:#fff; }
    .country-row-name { font-weight:700; color:#222; font-size:1.02rem; margin:0; }
    body.dark-mode .country-row-name { color:#e0e0e0 !important; }
    .country-row-sub { font-size:.72rem; color:#aaa; }
    body.dark-mode .country-row-sub { color:#8794a8 !important; }

    .country-row-stats { display:flex; align-items:center; gap:1.8rem; flex-wrap:wrap; }
    .country-row-metric { display:flex; flex-direction:column; align-items:flex-end; min-width:56px; }
    .country-row-metric-lbl { font-size:.6rem; color:#aaa; text-transform:uppercase; letter-spacing:.05em; font-weight:700; margin-bottom:2px; }
    body.dark-mode .country-row-metric-lbl { color:#8794a8 !important; }
    .country-row-metric-val { display:flex; align-items:center; gap:5px; font-weight:600; color:#333; font-size:.9rem; }
    body.dark-mode .country-row-metric-val { color:#e0e0e0 !important; }
    .country-row-metric.is-zero { opacity:.35; }
    .country-row-actions { display:flex; align-items:center; gap:6px; padding-left:1rem; margin-left:.4rem;
                            border-left:1px solid #eee; }
    body.dark-mode .country-row-actions { border-color:#4a5568 !important; }
    .country-row-quickview { background:none; border:none; color:#bbb; padding:6px; border-radius:50%;
                              display:flex; align-items:center; justify-content:center; transition:.15s; }
    .country-row-quickview:hover { color:#a02626; background:#FEC503; }
    body.dark-mode .country-row-quickview { color:#8794a8; }
    body.dark-mode .country-row-quickview:hover { color:#3a2a00 !important; background:#FEC503 !important; }
    .country-row-chevron { color:#ccc; transition:color .15s; }
    .country-row:hover .country-row-chevron { color:#d68f00; }
    body.dark-mode .country-row-chevron { color:#6b7280 !important; }
    body.dark-mode .country-row:hover .country-row-chevron { color:#FEC503 !important; }

    #ctryLoadMoreWrap { text-align:center; margin:1.6rem 0 2.75rem; }
    #ctryLoadMoreWrap .btn { border-radius:20px; padding:.5rem 1.4rem; border-color:#e0c060; color:#a02626; font-weight:600; transition:.15s; }
    #ctryLoadMoreWrap .btn:hover { background:#FEC503; border-color:#FEC503; color:#3a2a00; }
    body.dark-mode #ctryLoadMoreWrap .btn { background:#374151 !important; border-color:#4a5568 !important; color:#f0a8a8 !important; }
    body.dark-mode #ctryLoadMoreWrap .btn:hover { background:#FEC503 !important; border-color:#FEC503 !important; color:#3a2a00 !important; }

    /* ── Quick-view slide panel ── */
    #ctryPanelBackdrop { position:fixed; inset:0; background:rgba(20,20,30,.4); z-index:1051; opacity:0;
                          pointer-events:none; transition:opacity .25s; }
    #ctryPanelBackdrop.show { opacity:1; pointer-events:auto; }
    #ctryPanel { position:fixed; top:0; right:0; height:100%; width:420px; max-width:92vw; background:#fff;
                 box-shadow:-8px 0 30px rgba(0,0,0,.18); z-index:1052; transform:translateX(100%);
                 transition:transform .28s ease; display:flex; flex-direction:column; }
    #ctryPanel.show { transform:translateX(0); }
    body.dark-mode #ctryPanel { background:#1f2937 !important; }
    .ctry-panel-hd { padding:26px 26px 20px; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:flex-start; }
    body.dark-mode .ctry-panel-hd { border-color:#4a5568 !important; }
    .ctry-panel-hd h4 { font-weight:700; margin:0; font-size:1.5rem; color:#222; }
    body.dark-mode .ctry-panel-hd h4 { color:#e0e0e0 !important; }
    .ctry-panel-hd p { color:#999; font-size:.82rem; margin:.3rem 0 0; }
    .ctry-panel-close { background:none; border:none; color:#999; padding:6px; border-radius:50%; }
    .ctry-panel-close:hover { background:#f4f4f6; color:#a02626; }
    body.dark-mode .ctry-panel-close:hover { background:#374151 !important; color:#f0a8a8 !important; }
    .ctry-panel-body { padding:26px; overflow-y:auto; flex:1; }
    .ctry-panel-stats { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:26px; }
    .ctry-panel-stat { background:#faf7f7; border:1px solid #eee; border-radius:12px; padding:16px; }
    body.dark-mode .ctry-panel-stat { background:#2b3040 !important; border-color:#4a5568 !important; }
    .ctry-panel-stat-lbl { font-size:.65rem; color:#999; text-transform:uppercase; letter-spacing:.05em; font-weight:700; margin-bottom:6px; }
    .ctry-panel-stat-val { font-size:1.5rem; font-weight:700; color:#222; }
    body.dark-mode .ctry-panel-stat-val { color:#e0e0e0 !important; }
    .ctry-panel-stat.accent .ctry-panel-stat-val,
    .ctry-panel-stat.accent .ctry-panel-stat-lbl { color:#a02626; }
    .ctry-panel-body h5 { font-size:.95rem; font-weight:700; color:#222; margin-bottom:12px; }
    body.dark-mode .ctry-panel-body h5 { color:#e0e0e0 !important; }
    .ctry-panel-action { width:100%; display:flex; align-items:center; justify-content:space-between;
                          padding:13px 16px; background:#fff; border:1px solid #eee; border-left:3px solid transparent;
                          border-radius:10px; margin-bottom:10px; text-decoration:none; color:#333; transition:.15s; }
    .ctry-panel-action:hover { border-color:#e0c060; border-left-color:#FEC503;
                                background:linear-gradient(90deg, rgba(254,197,3,.14), rgba(160,38,38,.03));
                                color:#a02626; text-decoration:none; }
    body.dark-mode .ctry-panel-action { background:#2b3040 !important; border-color:#4a5568 !important; color:#e0e0e0 !important; }
    body.dark-mode .ctry-panel-action:hover { background:linear-gradient(90deg, rgba(254,197,3,.16), rgba(160,38,38,.12)) !important;
                                               border-color:#4a5568 !important; border-left-color:#FEC503 !important; color:#FEC503 !important; }
    .ctry-panel-foot { padding:20px 26px; border-top:1px solid #eee; }
    body.dark-mode .ctry-panel-foot { border-color:#4a5568 !important; }
    .ctry-panel-foot .btn { width:100%; transition:.15s; }
    .ctry-panel-foot .btn:hover { background:#870f0f !important; color:#FEC503 !important; }
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
                    <div>
                        <h5 class="mb-0 font-weight-bold" style="color:#a02626;">
                            <i class="fas fa-globe-africa mr-2"></i>Countries
                            <span class="badge badge-secondary ml-1">{{ count($countries) }}</span>
                        </h5>
                        <p class="page-subtitle">Manage regions, associated hospitals, and track fellows across the COSECSA network.</p>
                    </div>
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

                {{-- ── Interactive network map ── --}}
                <div class="ctry-globe-panel d-print-none">
                    <div class="ctry-globe-copy">
                        <h2><i class="fas fa-globe-africa mr-2" style="color:#a02626;"></i>Interactive Network Map</h2>
                        <p>Drag the globe to rotate it. Click any highlighted country — or a row in the directory below — to see its hospitals, trainees and fellows at a glance.</p>
                        <button class="btn btn-sm btn-light border" id="ctryResetGlobe" type="button">
                            <i class="fas fa-sync-alt mr-1"></i> Reset View
                        </button>
                    </div>
                    <div class="ctry-globe-canvas-wrap">
                        <div id="ctryGlobe"></div>
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
                            <div class="ctry-stat-lbl">Total Countries</div>
                            <div class="ctry-stat-val">{{ $totalCountries }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="ctry-stat">
                            <div class="ctry-stat-icon"><i class="fas fa-check-circle"></i></div>
                            <div class="ctry-stat-lbl">With Records</div>
                            <div class="ctry-stat-val">{{ $withRecords }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="ctry-stat">
                            <div class="ctry-stat-icon"><i class="fas fa-hospital-alt"></i></div>
                            <div class="ctry-stat-lbl">With Hospitals</div>
                            <div class="ctry-stat-val">{{ $withHospitals }}</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="ctry-stat">
                            <div class="ctry-stat-icon"><i class="fas fa-award"></i></div>
                            <div class="ctry-stat-lbl">Total Fellows</div>
                            <div class="ctry-stat-val">{{ $totalFellows }}</div>
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
                <div class="ctry-filter-bar d-print-none">
                    <input type="text" id="ctrySearch" class="form-control form-control-sm" placeholder="Find a country…" autocomplete="off">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="ctryShowEmpty">
                        <label class="custom-control-label" for="ctryShowEmpty" style="font-size:.85rem;">Show countries with no records</label>
                    </div>
                </div>

                <div class="ctry-directory-hd">
                    <h6>Directory</h6>
                    <span class="ctry-count-pill">Showing <span id="visible-count">0</span> of {{ count($countries) }}</span>
                </div>

                {{-- ── Directory (list rows) ── --}}
                <div id="country-list">
                    @foreach($countries as $c)
                    @php
                        $hasRecords = $c->hospital_count || $c->trainee_count || $c->fellow_count || $c->member_count;
                    @endphp
                    <a href="{{ url('admin/countries/view/'.$c->id) }}"
                       class="country-row country-item"
                       data-id="{{ $c->id }}"
                       data-name="{{ $c->country_name }}"
                       data-hospitals="{{ (int) $c->hospital_count }}"
                       data-trainees="{{ (int) $c->trainee_count }}"
                       data-fellows="{{ (int) $c->fellow_count }}"
                       data-members="{{ (int) $c->member_count }}"
                       data-has-records="{{ $hasRecords ? 1 : 0 }}">
                        <div class="country-row-left">
                            <div class="country-row-icon"><i class="fas fa-flag"></i></div>
                            <div>
                                <p class="country-row-name country-name">{{ $c->country_name }}</p>
                                <p class="country-row-sub mb-0">{{ $hasRecords ? 'COSECSA network' : 'No records yet' }}</p>
                            </div>
                        </div>
                        <div class="country-row-stats">
                            <div class="country-row-metric {{ $c->hospital_count ? '' : 'is-zero' }}">
                                <span class="country-row-metric-lbl">Hospitals</span>
                                <span class="country-row-metric-val"><i class="fas fa-hospital-alt" style="font-size:.8rem;color:#999;"></i>{{ (int) $c->hospital_count }}</span>
                            </div>
                            <div class="country-row-metric {{ $c->trainee_count ? '' : 'is-zero' }}">
                                <span class="country-row-metric-lbl">Trainees</span>
                                <span class="country-row-metric-val"><i class="fas fa-user-graduate" style="font-size:.8rem;color:#999;"></i>{{ (int) $c->trainee_count }}</span>
                            </div>
                            <div class="country-row-metric {{ $c->fellow_count ? '' : 'is-zero' }}">
                                <span class="country-row-metric-lbl">Fellows</span>
                                <span class="country-row-metric-val"><i class="fas fa-award" style="font-size:.8rem;color:#a02626;"></i>{{ (int) $c->fellow_count }}</span>
                            </div>
                            <div class="country-row-actions">
                                <button type="button" class="country-row-quickview" title="Quick view" data-id="{{ $c->id }}">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <span class="country-row-chevron"><i class="fas fa-chevron-right"></i></span>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>

                <div id="ctryLoadMoreWrap" class="d-print-none">
                    <button class="btn btn-sm btn-light border" id="ctryLoadMore" type="button">
                        <i class="fas fa-chevron-down mr-1"></i> Load More
                    </button>
                </div>
            </div>
        </section>
    </div>
</div>

{{-- ── Quick-view slide panel ── --}}
<div id="ctryPanelBackdrop" class="d-print-none"></div>
<div id="ctryPanel" class="d-print-none">
    <div class="ctry-panel-hd">
        <div>
            <h4><i class="fas fa-flag mr-2" style="color:#a02626;"></i><span id="ctryPanelName">—</span></h4>
            <p>Network overview &amp; statistics</p>
        </div>
        <button type="button" class="ctry-panel-close" id="ctryPanelCloseBtn"><i class="fas fa-times"></i></button>
    </div>
    <div class="ctry-panel-body">
        <div class="ctry-panel-stats">
            <div class="ctry-panel-stat">
                <div class="ctry-panel-stat-lbl">Hospitals</div>
                <div class="ctry-panel-stat-val" id="ctryPanelHospitals">0</div>
            </div>
            <div class="ctry-panel-stat">
                <div class="ctry-panel-stat-lbl">Trainees</div>
                <div class="ctry-panel-stat-val" id="ctryPanelTrainees">0</div>
            </div>
            <div class="ctry-panel-stat accent">
                <div class="ctry-panel-stat-lbl">Fellows</div>
                <div class="ctry-panel-stat-val" id="ctryPanelFellows">0</div>
            </div>
            <div class="ctry-panel-stat">
                <div class="ctry-panel-stat-lbl">Members</div>
                <div class="ctry-panel-stat-val" id="ctryPanelMembers">0</div>
            </div>
        </div>
        <h5>Quick Actions</h5>
        <a href="#" id="ctryPanelActionHospitals" class="ctry-panel-action">
            <span><i class="fas fa-hospital-alt mr-2"></i>View Hospitals</span>
            <i class="fas fa-chevron-right"></i>
        </a>
        <a href="#" id="ctryPanelActionTrainees" class="ctry-panel-action">
            <span><i class="fas fa-user-graduate mr-2"></i>View Trainees</span>
            <i class="fas fa-chevron-right"></i>
        </a>
        <a href="#" id="ctryPanelActionFellows" class="ctry-panel-action">
            <span><i class="fas fa-award mr-2"></i>View Fellows</span>
            <i class="fas fa-chevron-right"></i>
        </a>
    </div>
    <div class="ctry-panel-foot">
        <a href="#" id="ctryPanelViewFull" class="btn" style="background:#a02626;color:#fff;">
            <i class="fas fa-arrow-right mr-1"></i> View Full Profile
        </a>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/d3@7/dist/d3.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/topojson-client@3/dist/topojson-client.min.js"></script>
<script>
$(function () {
    var $rows = $('.country-item');
    var PAGE_SIZE = 20;
    var visiblePageCount = PAGE_SIZE;

    function applyFilters() {
        var q = $('#ctrySearch').val().toLowerCase().trim();
        var showEmpty = $('#ctryShowEmpty').is(':checked');
        var filtering = !!q || showEmpty;
        var matched = 0;
        var shown = 0;

        $rows.each(function () {
            var $row = $(this);
            var matchesSearch = !q || $row.data('name').toLowerCase().indexOf(q) !== -1;
            var matchesEmpty = showEmpty || $row.data('has-records') == 1;
            var matches = matchesSearch && matchesEmpty;

            if (!matches) { $row.hide(); return; }
            matched++;

            // Paginate only while not actively filtering — filtering always shows every match.
            var show = filtering || matched <= visiblePageCount;
            $row.toggle(show);
            if (show) shown++;
        });

        $('#visible-count').text(shown);
        $('#ctryLoadMoreWrap').toggle(!filtering && matched > visiblePageCount);
    }

    $('#ctrySearch').on('input', function () { visiblePageCount = PAGE_SIZE; applyFilters(); });
    $('#ctryShowEmpty').on('change', function () { visiblePageCount = PAGE_SIZE; applyFilters(); });
    $('#ctryLoadMore').on('click', function () { visiblePageCount += PAGE_SIZE; applyFilters(); });
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
            var $row = $(this);
            rows.push([
                $row.data('name'),
                $row.data('hospitals'),
                $row.data('trainees'),
                $row.data('fellows'),
                $row.data('members'),
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

    // ── Quick-view slide panel ──
    var countryBaseUrl = @json(url('admin/countries/view'));

    function openCountryPanel(id) {
        var $row = $rows.filter('[data-id="' + id + '"]');
        if (!$row.length) return;

        var name = $row.data('name');
        $('#ctryPanelName').text(name);
        $('#ctryPanelHospitals').text($row.data('hospitals'));
        $('#ctryPanelTrainees').text($row.data('trainees'));
        $('#ctryPanelFellows').text($row.data('fellows'));
        $('#ctryPanelMembers').text($row.data('members'));

        var base = countryBaseUrl + '/' + id;
        $('#ctryPanelViewFull').attr('href', base);
        $('#ctryPanelActionHospitals').attr('href', base + '#ct-hospitals');
        $('#ctryPanelActionTrainees').attr('href', base + '#ct-trainees');
        $('#ctryPanelActionFellows').attr('href', base + '#ct-fellows');

        $('#ctryPanelBackdrop, #ctryPanel').addClass('show');

        d3.selectAll('.globe-country').classed('active', false);
        d3.selectAll('.globe-country').filter(function (d) { return matchesCountryName(d, name); })
            .classed('active', true);
    }

    function closeCountryPanel() {
        $('#ctryPanelBackdrop, #ctryPanel').removeClass('show');
        d3.selectAll('.globe-country').classed('active', false);
    }

    $('.country-row-quickview').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        openCountryPanel($(this).data('id'));
    });
    $('#ctryPanelBackdrop, #ctryPanelCloseBtn').on('click', closeCountryPanel);

    // ── D3 orthographic globe ──
    var width = 260, height = 260;
    var rotation = [-20, -12, 0];
    var speed = 0.012;
    var svg = d3.select('#ctryGlobe').append('svg').attr('width', width).attr('height', height);
    var projection = d3.geoOrthographic().scale(122).translate([width / 2, height / 2]).clipAngle(90);
    var path = d3.geoPath().projection(projection);

    svg.append('path').datum({ type: 'Sphere' }).attr('class', 'globe-sphere').attr('d', path);
    svg.append('path').datum(d3.geoGraticule()).attr('class', 'globe-graticule').attr('d', path);
    var g = svg.append('g');

    // Names as stored in our `countries` table, normalized for fuzzy matching against
    // the world-atlas/Natural-Earth English names (which differ for a handful of countries).
    var ourCountries = @json($countries->pluck('country_name'));

    function normalizeName(n) {
        return (n || '').toLowerCase()
            .replace(/[’']/g, "'")
            .replace(/^(the |republic of |democratic republic of (the )?|united republic of |federal democratic republic of |kingdom of |people's republic of )/, '')
            .replace(/[^a-z0-9]/g, '');
    }

    var aliasMap = {
        'drcongo': 'democraticrepublicofcongo', 'demrepcongo': 'democraticrepublicofcongo',
        'congokinshasa': 'democraticrepublicofcongo',
        'congobrazzaville': 'congo',
        'ivorycoast': 'cotedivoire', 'coteivoire': 'cotedivoire', 'ctedivoire': 'cotedivoire',
        'unitedrepublicoftanzania': 'tanzania',
        'swaziland': 'eswatini',
        'ssudan': 'southsudan',
        'car': 'centralafricanrepublic', 'centralafricanrep': 'centralafricanrepublic',
    };

    function canonical(n) {
        var norm = normalizeName(n);
        return aliasMap[norm] || norm;
    }

    var normalizedOurCountries = ourCountries.map(canonical);

    function matchesCountryName(d, ourName) {
        var target = canonical(ourName);
        var geoName = canonical(d.properties.name);
        return target === geoName || target.indexOf(geoName) !== -1 || geoName.indexOf(target) !== -1;
    }

    function hasNetworkPresence(d) {
        var geoName = canonical(d.properties.name);
        return normalizedOurCountries.some(function (n) {
            return n === geoName || n.indexOf(geoName) !== -1 || geoName.indexOf(n) !== -1;
        });
    }

    d3.json('https://cdn.jsdelivr.net/npm/world-atlas@2/countries-110m.json').then(function (world) {
        var countries = topojson.feature(world, world.objects.countries).features;

        g.selectAll('.globe-country')
            .data(countries)
            .enter().append('path')
            .attr('class', function (d) { return 'globe-country' + (hasNetworkPresence(d) ? ' has-network' : ''); })
            .attr('d', path)
            .on('click', function (event, d) {
                var geoName = d.properties.name;
                var match = $rows.filter(function () {
                    return matchesCountryName(d, $(this).data('name'));
                }).first();
                if (match.length) openCountryPanel(match.data('id'));
            })
            .append('title')
            .text(function (d) { return d.properties.name; });

        var drag = d3.drag().on('drag', function (event) {
            var r = projection.rotate();
            var k = 75 / projection.scale();
            projection.rotate([r[0] + event.dx * k, r[1] - event.dy * k]);
            svg.selectAll('path').attr('d', path);
        });
        svg.call(drag);

        var timer = d3.timer(function () {
            var r = projection.rotate();
            projection.rotate([r[0] + speed * 16, r[1], r[2]]);
            svg.selectAll('path').attr('d', path);
        });

        svg.on('mouseenter', function () { timer.stop(); })
           .on('mouseleave', function () {
                timer = d3.timer(function () {
                    var r = projection.rotate();
                    projection.rotate([r[0] + speed * 16, r[1], r[2]]);
                    svg.selectAll('path').attr('d', path);
                });
           });

        $('#ctryResetGlobe').on('click', function () {
            projection.rotate(rotation);
            svg.selectAll('path').transition().duration(600).attr('d', path);
        });
    }).catch(function () {
        $('.ctry-globe-canvas-wrap').html('<p class="text-muted small mb-0">Map unavailable — check your connection.</p>');
    });
});
</script>
@endpush
