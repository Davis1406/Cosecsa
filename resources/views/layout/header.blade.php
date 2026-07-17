<!-- Inline script to prevent flash of light mode -->
<script>
    (function() {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.documentElement.classList.add('dark-mode');
            document.body.classList.add('dark-mode');
        }
    })();
</script>

<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light" id="main-navbar">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>

    @if(Auth::check() && Auth::user()->user_type == 1)
    <!-- Global Search (admin only) -->
    <div class="navbar-search-wrapper ml-3" style="position:relative;flex:1;max-width:420px;">
        <div class="input-group input-group-sm">
            <input type="text" id="globalSearchInput" class="form-control"
                   placeholder="Search trainees, candidates, examiners…"
                   autocomplete="off"
                   style="border-radius:20px 0 0 20px;border-right:0;">
            <div class="input-group-append">
                <span class="input-group-text" style="border-radius:0 20px 20px 0;background:#fff;border-left:0;cursor:pointer;" id="globalSearchBtn">
                    <i class="fas fa-search text-muted"></i>
                </span>
            </div>
        </div>
        <!-- Results dropdown -->
        <div id="globalSearchResults"
             style="display:none;position:absolute;top:calc(100% + 4px);left:0;right:0;
                    background:#fff;border:1px solid #ddd;border-radius:8px;
                    box-shadow:0 4px 20px rgba(0,0,0,.12);z-index:9999;max-height:420px;overflow-y:auto;">
        </div>
    </div>
    @endif

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- Dark Mode Toggle Button -->
        <li class="nav-item">
            <a class="nav-link" href="#" role="button" id="darkModeToggle" title="Toggle Dark Mode">
                <i class="fas fa-moon" id="darkModeIcon"></i>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>
    </ul>
</nav>

@if(Auth::check() && Auth::user()->user_type == 1)
<style>
#globalSearchResults .gs-section-title {
    font-size:.68rem;font-weight:700;text-transform:uppercase;
    letter-spacing:.07em;color:#a02626;padding:6px 12px 2px;
    border-top:1px solid #f0f0f0;
}
#globalSearchResults .gs-section-title:first-child { border-top:none; }
#globalSearchResults .gs-item {
    display:block;padding:7px 12px;color:#333;font-size:.85rem;
    text-decoration:none;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
#globalSearchResults .gs-item:hover { background:#fdf0f0;color:#a02626; }
#globalSearchResults .gs-item .gs-sub { color:#888;font-size:.75rem;margin-left:6px; }
#globalSearchResults .gs-empty { padding:12px;color:#888;font-size:.85rem;text-align:center; }
#globalSearchResults .gs-loading { padding:12px;color:#888;font-size:.85rem;text-align:center; }
.navbar-search-wrapper input:focus { box-shadow:none;border-color:#a02626; }
</style>
@push('scripts')
{{-- Global search — deferred until after jQuery loads --}}
<script>
(function () {
    var timer = null;
    var $input  = $('#globalSearchInput');
    var $box    = $('#globalSearchResults');

    $input.on('input', function () {
        clearTimeout(timer);
        var q = $.trim($(this).val());
        if (q.length < 2) { $box.hide().empty(); return; }
        timer = setTimeout(function () { doSearch(q); }, 300);
    });

    $('#globalSearchBtn').on('click', function () {
        var q = $.trim($input.val());
        if (q.length >= 2) doSearch(q);
    });

    $input.on('keydown', function (e) {
        if (e.key === 'Enter') { var q = $.trim($(this).val()); if (q.length >= 2) doSearch(q); }
        if (e.key === 'Escape') { $box.hide().empty(); }
    });

    $(document).on('click', function (e) {
        if (!$(e.target).closest('.navbar-search-wrapper').length) $box.hide();
    });

    function doSearch(q) {
        $box.html('<div class="gs-loading"><i class="fas fa-spinner fa-spin mr-1"></i> Searching…</div>').show();
        $.getJSON('{{ url("admin/global-search") }}', { q: q })
            .done(function (data) { renderResults(data, q); })
            .fail(function () { $box.html('<div class="gs-empty">Search failed. Please try again.</div>'); });
    }

    function renderResults(data, q) {
        var html = '';
        var sections = [
            { key: 'trainees',   label: 'Trainees',   icon: 'fas fa-user-graduate' },
            { key: 'candidates', label: 'Candidates',  icon: 'fas fa-user-check' },
            { key: 'examiners',  label: 'Examiners',   icon: 'fas fa-user-md' },
            { key: 'fellows',    label: 'Fellows',      icon: 'fas fa-award' },
        ];
        var total = 0;
        sections.forEach(function (s) {
            var rows = data[s.key] || [];
            if (!rows.length) return;
            total += rows.length;
            html += '<div class="gs-section-title"><i class="' + s.icon + ' mr-1"></i>' + s.label
                  + ' <span style="font-weight:400;color:#aaa;">(' + rows.length + ')</span></div>';
            rows.forEach(function (r) {
                html += '<a class="gs-item" href="' + r.url + '">'
                      + '<strong>' + escHtml(r.name) + '</strong>'
                      + (r.sub ? '<span class="gs-sub">' + escHtml(r.sub) + '</span>' : '')
                      + '</a>';
            });
        });
        if (!total) html = '<div class="gs-empty">No results for "<strong>' + escHtml(q) + '</strong>"</div>';
        $box.html(html).show();
    }

    function escHtml(s) {
        return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }
})();
</script>
@endpush
@endif
<!-- /.navbar -->

<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4" id="main-sidebar">
    <!-- Brand Logo -->
    <a href="" class="brand-link">
        <img src="{{ url('public/dist/img/Cosecsa_Logo.png') }}" alt="Cosecsa Logo"
            class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">COSECSA-MIS</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        @if (Auth::check())
            <!-- Sidebar user panel (optional) -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="image">
                    @php $authPhoto = Auth::user()->profile_image ?? null; @endphp
                    <img src="{{ $authPhoto ? asset('storage/' . $authPhoto) : url('public/dist/img/user.png') }}"
                         class="img-circle elevation-2" alt="User Image"
                         style="width:34px;height:34px;object-fit:cover;">
                </div>
                <div class="info">
                    <a href="#" class="d-block">{{ Auth::user()->name }}</a>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                    data-accordion="false">
                    <li class="nav-header">Navigation</li>

                    @if (Auth::user()->user_type == 1)
                        <li class="nav-item">
                            <a href="{{ url('admin/dashboard') }}"
                                class="nav-link @if (Request::segment(2) == 'dashboard') active @endif">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>
                                    Dashboard
                                </p>
                            </a>
                        </li>

                        @if (Auth::user()->hasPermission('admin_users.view'))
                        <li class="nav-item">
                            <a href="{{ url('admin/list') }}"
                                class="nav-link @if (Request::segment(2) == 'list') active @endif">
                                <i class="nav-icon fas fa-user"></i>
                                <p>
                                    Admin
                                </p>
                            </a>
                        </li>
                        @endif

                        @if (Auth::user()->hasPermission('roles.view'))
                        <li class="nav-item">
                            <a href="{{ url('admin/roles/list') }}"
                                class="nav-link @if (Request::segment(2) == 'roles') active @endif">
                                <i class="nav-icon fas fa-user-shield"></i>
                                <p>
                                    Roles &amp; Permissions
                                </p>
                            </a>
                        </li>
                        @endif

                        @if (Auth::user()->hasPermission('system_logs.view'))
                        <li class="nav-item">
                            <a href="{{ url('admin/logs') }}"
                                class="nav-link @if (Request::segment(2) == 'logs') active @endif">
                                <i class="nav-icon fas fa-clipboard-list"></i>
                                <p>
                                    System Logs
                                </p>
                            </a>
                        </li>
                        @endif

                        @if (Auth::user()->hasPermission('reports.view'))
                        <li class="nav-item">
                            <a href="{{ url('admin/reports') }}"
                                class="nav-link @if (Request::segment(2) == 'reports') active @endif">
                                <i class="nav-icon fas fa-chart-pie"></i>
                                <p>
                                    College Reports
                                </p>
                            </a>
                        </li>
                        @endif

                        @if (Auth::user()->hasPermission('lookups.view'))
                        <li class="nav-item">
                            <a href="{{ url('admin/hospital/list') }}"
                                class="nav-link @if (Request::segment(2) == 'hospital') active @endif">
                                <i class="nav-icon fas fa-hospital"></i>
                                <p>
                                    Accredited Hospitals
                                </p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ url('admin/programmes/list') }}"
                                class="nav-link @if (Request::segment(2) == 'programmes') active @endif">
                                <i class="nav-icon fas fa-book"></i>
                                <p>
                                    Programmes
                                </p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ url('admin/hospitalprogrammes/list') }}"
                                class="nav-link @if (Request::segment(2) == 'hospitalprogrammes') active @endif">
                                <i class="nav-icon fas fa-edit"></i>
                                <p>
                                    Hospital Programmes
                                </p>
                            </a>
                        </li>
                        @endif

                        @if (Auth::user()->hasPermission('trainees.view') || Auth::user()->hasPermission('candidates.view') || Auth::user()->hasPermission('members.view') || Auth::user()->hasPermission('fellows.view') || Auth::user()->hasPermission('trainers.view') || Auth::user()->hasPermission('country_reps.view') || Auth::user()->hasPermission('promotions.view'))
                        <li class="nav-item @if (Request::segment(2) == 'associates') menu-open @endif">
                            <a href="#" class="nav-link @if (Request::segment(2) == 'associates') active @endif">
                                <i class="nav-icon fas fa-users"></i>
                                <p>
                                    Associates
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                @if (Auth::user()->hasPermission('trainees.view'))
                                <li class="nav-item">
                                    <a href="{{ url('admin/associates/trainees/trainees') }}"
                                        class="nav-link @if (Request::segment(3) == 'trainees') active @endif">
                                        <i class="fas fa-user-md nav-icon"></i>
                                        <p>Trainees</p>
                                    </a>
                                </li>
                                @endif
                                @if (Auth::user()->hasPermission('candidates.view'))
                                <li class="nav-item">
                                    <a href="{{ url('admin/associates/candidates/list') }}"
                                       class="nav-link @if (Request::segment(3) == 'candidates') active @endif">
                                        <i class="fas fa-graduation-cap nav-icon"></i>
                                        <p>Candidates</p>
                                    </a>
                                </li>
                                @endif
                                @if (Auth::user()->hasPermission('members.view'))
                                <li class="nav-item">
                                    <a href="{{ url('admin/associates/members/list') }}"
                                        class="nav-link @if (Request::segment(3) == 'members') active @endif">
                                        <i class="fas fa-user nav-icon"></i>
                                        <p>Members</p>
                                    </a>
                                </li>
                                @endif
                                @if (Auth::user()->hasPermission('fellows.view'))
                                <li class="nav-item">
                                    <a href="{{ url('admin/associates/fellows/list') }}"
                                        class="nav-link @if (Request::segment(3) == 'fellows') active @endif">
                                        <i class="fas fa-user nav-icon"></i>
                                        <p>Fellows</p>
                                    </a>
                                </li>
                                @endif
                                @if (Auth::user()->hasPermission('trainers.view') || Auth::user()->hasPermission('country_reps.view'))
                                <li class="nav-item @if (Request::segment(3) == 'trainers' || Request::segment(3) == 'reps') menu-open @endif">
                                    <a href="#" class="nav-link">
                                        <i class="fas fa-stethoscope nav-icon"></i>
                                        <p>PD's & Country Reps<i class="right fas fa-angle-left"></i></p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        @if (Auth::user()->hasPermission('trainers.view'))
                                        <li class="nav-item">
                                            <a href="{{ url('admin/associates/trainers/list') }}"
                                                class="nav-link @if (Request::segment(3) == 'trainers') active @endif">
                                                <i class="fas fa-chalkboard-teacher nav-icon"></i>
                                                <p>Programme Directors</p>
                                            </a>
                                        </li>
                                        @endif
                                        @if (Auth::user()->hasPermission('country_reps.view'))
                                        <li class="nav-item">
                                            <a href="{{ url('admin/associates/reps/list') }}"
                                                class="nav-link @if (Request::segment(3) == 'reps') active @endif">
                                                <i class="fas fa-flag nav-icon"></i>
                                                <p>Country Reps</p>
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                </li>
                                @endif
                                @if (Auth::user()->hasPermission('promotions.view'))
                                <li class="nav-item @if (Request::segment(3) == 'promotion') menu-open @endif">
                                    <a href="#" class="nav-link">
                                        <i class="fas fa-tasks nav-icon"></i>
                                        <p>Associate Promotions<i class="right fas fa-angle-left"></i></p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        <li class="nav-item">
                                            <a href="{{ url('admin/associates/promotion/promote_trainees') }}"
                                                class="nav-link @if (Request::segment(4) == 'promote_trainees') active @endif">
                                                <i class="fas fa-paper-plane nav-icon"></i>
                                                <p>Promote Trainees</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ url('admin/associates/promotion/promote-to-candidates') }}"
                                                class="nav-link @if (Request::segment(4) == 'promote-to-candidates') active @endif">
                                                <i class="fas fa-graduation-cap nav-icon"></i>
                                                <p>Trainees → Candidates</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ url('admin/associates/promotion/promote_candidates') }}"
                                                class="nav-link @if (Request::segment(4) == 'promote_candidates') active @endif">
                                                <i class="fas fa-upload nav-icon"></i>
                                                <p>Promote Candidates</p>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                @endif

                            </ul>
                        </li>
                        @endif
                        @if (Auth::user()->hasPermission('examiners.view'))
                        <li class="nav-item @if (Request::segment(2) == 'exams') menu-open @endif">
                            <a href="#" class="nav-link @if (Request::segment(2) == 'exams') active @endif">
                                <i class="nav-icon fas fa-book-open"></i>
                                <p>
                                    Examinations
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview" style="padding-left: 20px;">
                                <!-- Examiners Section -->
                                <li class="nav-item">
                                    <a href="{{ url('admin/exams/examiners') }}"
                                        class="nav-link @if (Request::segment(3) == 'examiners' ||
                                                (Request::segment(3) == 'view_examiner' && request('from') == 'admin/exams/examiners') ||
                                                (Request::segment(3) == 'edit_examiner' && request('from') == 'admin/exams/examiners')) active @endif">
                                        <i class="fas fa-chalkboard-teacher nav-icon"></i>
                                        <p>Examiners</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ url('admin/exams/examiner-confirmation') }}"
                                        class="nav-link
                                            @if (
                                                Request::segment(3) == 'examiner-confirmation' ||
                                                (Request::segment(3) == 'view_examiner' && request('from') == 'admin/exams/examiner-confirmation') ||
                                                (Request::segment(3) == 'edit_examiner' && request('from') == 'admin/exams/examiner-confirmation') ||
                                                Request::segment(3) == 'visual_report'
                                            )
                                                active
                                            @endif">
                                        <i class="fas fa-check nav-icon"></i>
                                        <p>Examiner Confirmation</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ url('admin/exams/attendance') }}"
                                        class="nav-link @if(Request::segment(3) == 'attendance') active @endif">
                                        <i class="fas fa-clipboard-check nav-icon"></i>
                                        <p>Attendance</p>
                                    </a>
                                </li>

                                <!-- Results Section (Parent) -->
                                <li class="nav-item @if (Request::segment(3) == 'exam_results' ||
                        Request::segment(3) == 'gs_results' ||
                        Request::segment(3) == 'station_results' ||
                        Request::segment(3) == 'gs_station_results' ||
                        Request::segment(3) == 'fcs_cardiothoracic_results' ||
                        Request::segment(3) == 'fcs_urology_results' ||
                        Request::segment(3) == 'fcs_paediatric_results' ||
                        Request::segment(3) == 'fcs_orthopaedics_results' ||
                        Request::segment(3) == 'fcs_paediatric_ortho_results' ||
                        Request::segment(3) == 'fcs_ent_results' ||
                        Request::segment(3) == 'fcs_plastic_surgery_results' ||
                        Request::segment(3) == 'fcs_neurosurgery_results' ||
                        Request::segment(3) == 'fcs-station-results') menu-open @endif">
                                    <a href="#" class="nav-link">
                                        <i class="fas fa-chart-line nav-icon"></i>
                                        <p>
                                            Results
                                            <i class="right fas fa-angle-left"></i>
                                        </p>
                                    </a>
                                    <ul class="nav nav-treeview" style="padding-left: 20px;">
                                        <!-- Overall Results (Capsule CRM historical) -->
                                        <li class="nav-item">
                                            <a href="{{ url('admin/exams/overall_results') }}"
                                               class="nav-link @if (Request::segment(3) == 'overall_results') active @endif">
                                                <i class="fas fa-chart-bar nav-icon"></i>
                                                <p>Overall Results</p>
                                            </a>
                                        </li>

                                        <!-- MCS Results -->
                                        <li class="nav-item">
                                            <a href="{{ url('admin/exams/exam_results') }}"
                                               class="nav-link @if (Request::segment(3) == 'exam_results' || Request::segment(3) == 'station_results') active @endif">
                                                <i class="fas fa-microscope nav-icon"></i>
                                                <p>MCS Results</p>
                                            </a>
                                        </li>

                                        <!-- FCS General Surgery Results -->
                                        <li class="nav-item">
                                            <a href="{{ url('admin/exams/gs_results') }}"
                                               class="nav-link @if (Request::segment(3) == 'gs_results' || Request::segment(3) == 'gs_station_results') active @endif">
                                                <i class="fas fa-user-md nav-icon"></i>
                                                <p>FCS General Surgery</p>
                                            </a>
                                        </li>

                                        <!-- FCS Cardiothoracic Results -->
                                        <li class="nav-item">
                                            <a href="{{ url('admin/exams/fcs_cardiothoracic_results') }}"
                                               class="nav-link @if (Request::segment(3) == 'fcs_cardiothoracic_results' || (Request::segment(3) == 'fcs-station-results' && Request::segment(6) == 'cardiothoracic_results')) active @endif">
                                                <i class="fas fa-heartbeat nav-icon"></i>
                                                <p>FCS Cardiothoracic</p>
                                            </a>
                                        </li>

                                        <!-- FCS Urology Results -->
                                        <li class="nav-item">
                                            <a href="{{ url('admin/exams/fcs_urology_results') }}"
                                               class="nav-link @if (Request::segment(3) == 'fcs_urology_results' || (Request::segment(3) == 'fcs-station-results' && Request::segment(6) == 'urology_results')) active @endif">
                                                <i class="fas fa-procedures nav-icon"></i>
                                                <p>FCS Urology</p>
                                            </a>
                                        </li>

                                        <!-- FCS Paediatric Surgery Results -->
                                        <li class="nav-item">
                                            <a href="{{ url('admin/exams/fcs_paediatric_results') }}"
                                               class="nav-link @if (Request::segment(3) == 'fcs_paediatric_results' || (Request::segment(3) == 'fcs-station-results' && Request::segment(6) == 'paediatric_results')) active @endif">
                                                <i class="fas fa-baby nav-icon"></i>
                                                <p>FCS Paediatric Surgery</p>
                                            </a>
                                        </li>

                                        <!-- FCS Orthopaedics Results -->
                                        <li class="nav-item">
                                            <a href="{{ url('admin/exams/fcs_orthopaedics_results') }}"
                                               class="nav-link @if (Request::segment(3) == 'fcs_orthopaedics_results' || (Request::segment(3) == 'fcs-station-results' && Request::segment(6) == 'ortho_results')) active @endif">
                                                <i class="fas fa-bone nav-icon"></i>
                                                <p>FCS Orthopaedics</p>
                                            </a>
                                        </li>

                                        <!-- FCS Paediatric Orthopaedics Results -->
                                        <li class="nav-item">
                                            <a href="{{ url('admin/exams/fcs_paediatric_ortho_results') }}"
                                               class="nav-link @if (Request::segment(3) == 'fcs_paediatric_ortho_results' || (Request::segment(3) == 'fcs-station-results' && Request::segment(6) == 'paediatric_orthopaedics_results')) active @endif">
                                                <i class="fas fa-child nav-icon"></i>
                                                <p>FCS Paediatric Ortho</p>
                                            </a>
                                        </li>

                                        <!-- FCS ENT Results -->
                                        <li class="nav-item">
                                            <a href="{{ url('admin/exams/fcs_ent_results') }}"
                                               class="nav-link @if (Request::segment(3) == 'fcs_ent_results' || (Request::segment(3) == 'fcs-station-results' && Request::segment(6) == 'ent_results')) active @endif">
                                                <i class="fas fa-head-side-virus nav-icon"></i>
                                                <p>FCS ENT</p>
                                            </a>
                                        </li>

                                        <!-- FCS Plastic Surgery Results -->
                                        <li class="nav-item">
                                            <a href="{{ url('admin/exams/fcs_plastic_surgery_results') }}"
                                               class="nav-link @if (Request::segment(3) == 'fcs_plastic_surgery_results' || (Request::segment(3) == 'fcs-station-results' && Request::segment(6) == 'plastic_surgery_results')) active @endif">
                                                <i class="fas fa-hand-holding-medical nav-icon"></i>
                                                <p>FCS Plastic Surgery</p>
                                            </a>
                                        </li>

                                        <!-- FCS Neurosurgery Results -->
                                        <li class="nav-item">
                                            <a href="{{ url('admin/exams/fcs_neurosurgery_results') }}"
                                               class="nav-link @if (Request::segment(3) == 'fcs_neurosurgery_results' || (Request::segment(3) == 'fcs-station-results' && Request::segment(6) == 'neurosurgery_results')) active @endif">
                                                <i class="fas fa-brain nav-icon"></i>
                                                <p>FCS Neurosurgery</p>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </li>
                        <!-- /.Examinations -->
                        @endif

                        @if (Auth::user()->hasPermission('salesforce.view'))
                        <li class="nav-item">
                            <a href="{{ url('admin/salesforce') }}"
                                class="nav-link @if (Request::segment(2) == 'salesforce') active @endif">
                                <i class="nav-icon fas fa-cloud"></i>
                                <p>
                                    Salesforce Application
                                </p>
                            </a>
                        </li>
                        @endif

                        @if (Auth::user()->hasPermission('fees.view'))
                        <li class="nav-item @if (Request::segment(2) == 'fees') menu-open @endif">
                            <a href="#" class="nav-link @if (Request::segment(2) == 'fees') active @endif">
                                <i class="nav-icon fas fa-money-check-alt"></i>
                                <p>
                                    Fees
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ route('admin.fees.catalogue') }}"
                                        class="nav-link @if (Request::segment(2) == 'fees' && Request::segment(3) == 'catalogue') active @endif">
                                        <i class="fas fa-list-ul nav-icon"></i>
                                        <p>Fee Catalogues</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('admin/fees') }}"
                                        class="nav-link @if (Request::segment(2) == 'fees' && Request::segment(3) == null) active @endif">
                                        <i class="fas fa-hand-holding-usd nav-icon"></i>
                                        <p>Manage Fees</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        @endif

                        <li class="nav-item">
                            <a href="{{ url('profile/change_password') }}"
                                class="nav-link @if (Request::segment(2) == 'change_password') active @endif">
                                <i class="nav-icon fas fa-cog"></i>
                                <p>
                                    Profile Settings
                                </p>
                            </a>
                        </li>
                    @elseif (Auth::user()->user_type == 2)
                        <li class="nav-item">
                            <a href="{{ url('trainee/dashboard') }}"
                                class="nav-link @if (Request::segment(2) == 'dashboard') active @endif">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>
                                    Dashboard
                                </p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('admin/list') }}"
                                class="nav-link @if (Request::segment(2) == 'admin') active @endif">
                                <i class="nav-icon fas fa-user"></i>
                                <p>
                                    Trainee
                                </p>
                            </a>
                        </li>

                        {{-- Examiner Section --}}
                    @elseif (Auth::user()->user_type == 7)
                        {{-- ── Fellow Navigation ── --}}
                        <li class="nav-item">
                            <a href="{{ url('fellow/dashboard') }}"
                               class="nav-link @if (Request::segment(1) == 'fellow') active @endif">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('fellow/dashboard') }}#tab-account"
                               class="nav-link"
                               onclick="localStorage.setItem('fellowActiveTab','#tab-account');">
                                <i class="nav-icon fas fa-cog"></i>
                                <p>Profile Settings</p>
                            </a>
                        </li>

                    @elseif (Auth::user()->user_type == 9)
                        <li class="nav-item">
                            <a href="{{ url('examiner/dashboard') }}"
                                class="nav-link @if (Request::segment(2) == 'dashboard' ||
                                        Request::segment(2) == 'examiner_form' ||
                                        Request::segment(2) == 'general_surgery') active @endif">
                                <i class="nav-icon fas fa-tachometer-alt"></i>
                                <p>
                                    Dashboard
                                </p>
                            </a>
                        </li>

{{--                        <li class="nav-item">--}}

                        <li class="nav-item">
                            {{-- <a href="{{ url('examiner/results') }}" --}}
                            <a href="{{ url('examiner/results') }}" class="nav-link @if (Request::segment(2) == 'results' || Request::segment(2) == 'view_results' || Request::segment(2) == 'resubmit'||Request::segment(2) == 'view_fcs_results'||Request::segment(2) == 'fcs-resubmit') active @endif">
                                <i class="fas fa-chart-line nav-icon"></i>
                                <p>Results</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ url('examiner/profile_settings') }}"
                                class="nav-link @if (Request::segment(2) == 'profile_settings' || Request::segment(2) == 'edit_info') active @endif">
                                <i class="nav-icon fas fa-user-circle"></i>
                                <p>Profile</p>
                            </a>
                        </li>
                    @endif

                                <li class="nav-header">Account</li>
                                <li class="nav-item">
                                    <a href="{{ url('logout') }}" class="nav-link">
                                        <i class="nav-icon fas fa-sign-out-alt"></i>
                                        <p>Logout</p>
                                    </a>
                                </li>
                </ul>

            </nav>
            <!-- /.sidebar-menu -->
        @else
            <!-- Show a simple message or minimal navigation for non-authenticated users -->
            <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                <div class="info text-center">
                    <p class="text-white">Please scan the QR code or authenticate to access the system.</p>
                </div>
            </div>
        @endif
    </div>
    <!-- /.sidebar -->
</aside>

<style>
    /* ── Modern Minimal Sidebar ── */
    .main-sidebar {
        background-color: #363840 !important;
        box-shadow: 2px 0 10px rgba(0,0,0,0.18) !important;
    }

    .brand-link {
        background-color: #2e3035 !important;
        border-bottom: 1px solid rgba(255,255,255,0.09) !important;
        padding: 13px 16px !important;
    }

    .brand-text {
        font-size: .88rem !important;
        font-weight: 700 !important;
        letter-spacing: .4px;
        color: #fff !important;
    }

    .brand-image {
        border-radius: 6px !important;
        width: 28px !important;
        height: 28px !important;
        margin-top: 0 !important;
    }

    /* User panel */
    .user-panel {
        border-bottom: 1px solid rgba(255,255,255,0.07) !important;
        margin: 0 !important;
        padding: 10px 16px !important;
    }

    .user-panel .image img {
        width: 38px !important;
        height: 38px !important;
        border: 2px solid rgba(160,38,38,0.55) !important;
        border-radius: 50% !important;
    }

    .user-panel .info a {
        font-size: .88rem !important;
        font-weight: 600 !important;
        color: rgba(255,255,255,0.85) !important;
    }

    /* Section header labels */
    .nav-sidebar > .nav-header {
        font-size: .58rem !important;
        font-weight: 700 !important;
        letter-spacing: 1.3px !important;
        color: rgba(255,255,255,0.28) !important;
        padding: 14px 16px 4px !important;
        text-transform: uppercase !important;
    }

    /* Nav links — default */
    .nav-sidebar .nav-link {
        color: rgba(255,255,255,0.72) !important;
        border-radius: 0 !important;
        padding: 9px 16px !important;
        font-size: .875rem !important;
        font-weight: 500 !important;
        border-left: 3px solid transparent !important;
        transition: all .15s ease !important;
    }

    .nav-sidebar .nav-link .nav-icon {
        color: rgba(255,255,255,0.48) !important;
        font-size: .9rem !important;
        width: 1.4rem !important;
        margin-right: 8px !important;
    }

    .nav-sidebar .nav-link:hover {
        background: rgba(255,255,255,0.05) !important;
        color: #fff !important;
        border-left-color: rgba(160,38,38,0.45) !important;
    }

    .nav-sidebar .nav-link:hover .nav-icon {
        color: rgba(255,255,255,0.65) !important;
    }

    /* Active top-level link */
    .nav-pills .nav-link.active,
    .nav-pills .show > .nav-link {
        background: rgba(160,38,38,0.16) !important;
        color: #fff !important;
        border-left: 3px solid #a02626 !important;
        font-weight: 600 !important;
    }

    .nav-pills .nav-link.active .nav-icon,
    .nav-pills .show > .nav-link .nav-icon {
        color: #d96060 !important;
    }

    /* Active sub-item */
    .nav-treeview .nav-link.active {
        background: rgba(160,38,38,0.13) !important;
        color: #fff !important;
        border-left: 3px solid #a02626 !important;
        font-weight: 600 !important;
    }

    .nav-treeview .nav-link.active .nav-icon {
        color: #d96060 !important;
    }

    /* Treeview indent */
    .nav-treeview .nav-link {
        padding-left: 2.4rem !important;
        font-size: .84rem !important;
    }

    .nav-treeview .nav-item .nav-treeview .nav-link {
        padding-left: 3.2rem !important;
    }

    .nav-treeview .nav-item .nav-treeview {
        padding-left: 0 !important;
    }

    /* Sidebar thin scrollbar */
    .sidebar {
        scrollbar-width: thin;
        scrollbar-color: rgba(255,255,255,0.08) transparent;
    }

    /* Dark Mode Styles */
    body.dark-mode,
    html.dark-mode {
        background-color: #1a1a1a !important;
        color: #e0e0e0 !important;
    }

    /* Dark mode navbar */
    body.dark-mode .navbar-white,
    body.dark-mode .navbar-light,
    html.dark-mode .navbar-white,
    html.dark-mode .navbar-light {
        background-color: #2d3748 !important;
        border-color: #4a5568 !important;
    }

    body.dark-mode .navbar-nav .nav-link,
    html.dark-mode .navbar-nav .nav-link {
        color: #e0e0e0 !important;
    }

    body.dark-mode .navbar-nav .nav-link:hover,
    html.dark-mode .navbar-nav .nav-link:hover {
        color: #ffffff !important;
    }

    /* Dark mode sidebar adjustments */
    body.dark-mode .main-sidebar,
    html.dark-mode .main-sidebar {
        background-color: #1a202c !important;
    }

    body.dark-mode .brand-link,
    html.dark-mode .brand-link {
        background-color: #2d3748 !important;
        border-bottom-color: #4a5568 !important;
    }

    body.dark-mode .brand-text,
    html.dark-mode .brand-text {
        color: #e0e0e0 !important;
    }

    /* Dark mode content wrapper */
    body.dark-mode .content-wrapper {
        background-color: #1a1a1a !important;
        color: #e0e0e0;
    }

    /* Dark mode cards and boxes */
    body.dark-mode .card {
        background-color: #2d3748 !important;
        border-color: #4a5568;
        color: #e0e0e0;
    }

    body.dark-mode .card-header {
        background-color: #374151 !important;
        border-bottom-color: #4a5568;
        color: #e0e0e0;
    }

    body.dark-mode .card-body {
        background-color: #2d3748 !important;
        color: #e0e0e0;
    }

    /* Dark mode tables */
    body.dark-mode .table {
        background-color: #2d3748;
        color: #e0e0e0;
    }

    body.dark-mode .table th,
    body.dark-mode .table td {
        border-color: #4a5568;
        color: #e0e0e0;
    }

    body.dark-mode .table-striped tbody tr:nth-of-type(odd) {
        background-color: #374151;
    }

    body.dark-mode .table-hover tbody tr:hover {
        background-color: #4a5568;
    }

    /* Dark mode buttons */
    body.dark-mode .btn-secondary {
        background-color: #4a5568;
        border-color: #4a5568;
        color: #e0e0e0;
    }

    body.dark-mode .btn-secondary:hover {
        background-color: #5a6578;
        border-color: #5a6578;
    }

    /* Dark mode forms */
    body.dark-mode .form-control {
        background-color: #374151;
        border-color: #4a5568;
        color: #e0e0e0;
    }

    body.dark-mode .form-control:focus {
        background-color: #374151;
        border-color: #6366f1;
        color: #e0e0e0;
        box-shadow: 0 0 0 0.2rem rgba(99, 102, 241, 0.25);
    }

    body.dark-mode .form-control::placeholder {
        color: #9ca3af;
    }

    /* Dark mode select */
    body.dark-mode select.form-control option {
        background-color: #374151;
        color: #e0e0e0;
    }

    /* Dark mode modal */
    body.dark-mode .modal-content {
        background-color: #2d3748;
        color: #e0e0e0;
        border-color: #4a5568;
    }

    body.dark-mode .modal-header {
        background-color: #374151;
        border-bottom-color: #4a5568;
    }

    body.dark-mode .modal-footer {
        background-color: #374151;
        border-top-color: #4a5568;
    }

    /* Dark mode breadcrumbs */
    body.dark-mode .breadcrumb {
        background-color: #374151;
    }

    body.dark-mode .breadcrumb-item a {
        color: #93c5fd;
    }

    body.dark-mode .breadcrumb-item.active {
        color: #e0e0e0;
    }

    /* Dark mode alerts */
    body.dark-mode .alert-info {
        background-color: #1e3a8a;
        border-color: #3b82f6;
        color: #dbeafe;
    }

    body.dark-mode .alert-success {
        background-color: #166534;
        border-color: #22c55e;
        color: #dcfce7;
    }

    body.dark-mode .alert-warning {
        background-color: #92400e;
        border-color: #f59e0b;
        color: #fef3c7;
    }

    body.dark-mode .alert-danger {
        background-color: #991b1b;
        border-color: #ef4444;
        color: #fee2e2;
    }

    /* Dark mode pagination */
    body.dark-mode .page-link {
        background-color: #374151;
        border-color: #4a5568;
        color: #e0e0e0;
    }

    body.dark-mode .page-link:hover {
        background-color: #4a5568;
        border-color: #6b7280;
        color: #ffffff;
    }

    body.dark-mode .page-item.active .page-link {
        background-color: #a02626;
        border-color: #a02626;
    }

    /* Dark mode toggle button styling */
    #darkModeToggle {
        transition: all 0.3s ease;
    }

    #darkModeToggle:hover {
        background-color: rgba(255, 255, 255, 0.1);
        border-radius: 4px;
    }

    body.dark-mode #darkModeToggle:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }

    /* Transition for smooth mode switching */
    body {
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .navbar,
    .main-sidebar,
    .content-wrapper,
    .card,
    .form-control {
        transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
    }

    /* ── Dark mode: text utilities ── */
    body.dark-mode .text-muted,
    body.dark-mode .text-secondary { color: #9ca3af !important; }

    body.dark-mode .text-dark { color: #e0e0e0 !important; }

    body.dark-mode label,
    body.dark-mode .col-form-label,
    body.dark-mode .form-label { color: #d1d5db; }

    body.dark-mode small,
    body.dark-mode .small { color: #9ca3af; }

    body.dark-mode h1, body.dark-mode h2, body.dark-mode h3,
    body.dark-mode h4, body.dark-mode h5, body.dark-mode h6,
    body.dark-mode .card-title, body.dark-mode .card-subtitle { color: #e0e0e0; }

    body.dark-mode p { color: #d1d5db; }

    /* ── Dark mode: links ── */
    body.dark-mode a:not(.btn):not(.nav-link):not(.dropdown-item):not(.page-link):not(.breadcrumb-item a) {
        color: #93c5fd;
    }
    body.dark-mode a:not(.btn):not(.nav-link):not(.dropdown-item):not(.page-link):hover {
        color: #bfdbfe;
    }

    /* ── Dark mode: dropdown menus ── */
    body.dark-mode .dropdown-menu {
        background-color: #374151 !important;
        border-color: #4a5568 !important;
        color: #e0e0e0;
    }
    body.dark-mode .dropdown-item {
        color: #e0e0e0 !important;
        background-color: transparent;
    }
    body.dark-mode .dropdown-item:hover,
    body.dark-mode .dropdown-item:focus {
        background-color: #4a5568 !important;
        color: #ffffff !important;
    }
    body.dark-mode .dropdown-item.text-danger { color: #f87171 !important; }
    body.dark-mode .dropdown-item.text-warning { color: #fbbf24 !important; }
    body.dark-mode .dropdown-item.text-info { color: #67e8f9 !important; }
    body.dark-mode .dropdown-divider { border-color: #4a5568; }
    body.dark-mode .dropdown-header { color: #9ca3af; }

    /* ── Dark mode: nav tabs ── */
    body.dark-mode .nav-tabs {
        border-bottom-color: #4a5568;
    }
    body.dark-mode .nav-tabs .nav-link {
        color: #9ca3af;
        border-color: transparent;
        background-color: transparent;
    }
    body.dark-mode .nav-tabs .nav-link:hover {
        color: #e0e0e0;
        border-color: #4a5568 #4a5568 transparent;
    }
    body.dark-mode .nav-tabs .nav-link.active,
    body.dark-mode .nav-tabs .nav-item.show .nav-link {
        color: #e0e0e0;
        background-color: #2d3748;
        border-color: #4a5568 #4a5568 #2d3748;
    }
    body.dark-mode .tab-content,
    body.dark-mode .tab-pane {
        background-color: #2d3748;
        color: #e0e0e0;
    }
    body.dark-mode .nav-pills .nav-link { color: #9ca3af; }
    body.dark-mode .nav-pills .nav-link.active,
    body.dark-mode .nav-pills .show > .nav-link {
        background-color: #a02626;
        color: #ffffff;
    }

    /* ── Dark mode: DataTables ── */
    body.dark-mode .dataTables_wrapper,
    body.dark-mode .dataTables_info,
    body.dark-mode .dataTables_paginate { color: #d1d5db; }

    body.dark-mode .dataTables_filter input,
    body.dark-mode .dataTables_length select {
        background-color: #374151 !important;
        border-color: #4a5568 !important;
        color: #e0e0e0 !important;
    }

    body.dark-mode .dataTables_wrapper .dataTables_filter label,
    body.dark-mode .dataTables_wrapper .dataTables_length label { color: #d1d5db; }

    body.dark-mode table.dataTable thead th,
    body.dark-mode table.dataTable thead td {
        background-color: #374151 !important;
        color: #e0e0e0 !important;
        border-color: #4a5568 !important;
    }

    body.dark-mode table.dataTable thead .sorting,
    body.dark-mode table.dataTable thead .sorting_asc,
    body.dark-mode table.dataTable thead .sorting_desc {
        background-color: #374151 !important;
        color: #e0e0e0 !important;
    }

    body.dark-mode .dt-buttons .btn,
    body.dark-mode .dt-button,
    body.dark-mode .buttons-copy,
    body.dark-mode .buttons-csv,
    body.dark-mode .buttons-excel,
    body.dark-mode .buttons-pdf,
    body.dark-mode .buttons-colvis {
        background-color: #4a5568 !important;
        border-color: #4a5568 !important;
        color: #e0e0e0 !important;
    }
    body.dark-mode .dt-buttons .btn:hover,
    body.dark-mode .dt-button:hover {
        background-color: #5a6578 !important;
        color: #ffffff !important;
    }

    body.dark-mode div.dt-button-collection {
        background-color: #374151 !important;
        border-color: #4a5568 !important;
    }
    body.dark-mode div.dt-button-collection .dt-button {
        color: #e0e0e0 !important;
        background-color: #374151 !important;
    }
    body.dark-mode div.dt-button-collection .dt-button:hover {
        background-color: #4a5568 !important;
        color: #ffffff !important;
    }
    body.dark-mode div.dt-button-collection .dt-button.active {
        background-color: #a02626 !important;
        color: #ffffff !important;
    }

    /* ── Dark mode: outline & light buttons ── */
    body.dark-mode .btn-outline-secondary {
        color: #d1d5db !important;
        border-color: #6b7280 !important;
        background-color: transparent !important;
    }
    body.dark-mode .btn-outline-secondary:hover {
        background-color: #4a5568 !important;
        border-color: #6b7280 !important;
        color: #ffffff !important;
    }
    body.dark-mode .btn-outline-primary {
        color: #93c5fd !important;
        border-color: #3b82f6 !important;
        background-color: transparent !important;
    }
    body.dark-mode .btn-outline-primary:hover {
        background-color: #1d4ed8 !important;
        color: #ffffff !important;
    }
    body.dark-mode .btn-outline-danger {
        color: #f87171 !important;
        border-color: #ef4444 !important;
        background-color: transparent !important;
    }
    body.dark-mode .btn-outline-danger:hover {
        background-color: #991b1b !important;
        color: #ffffff !important;
    }
    body.dark-mode .btn-outline-info {
        color: #67e8f9 !important;
        border-color: #06b6d4 !important;
        background-color: transparent !important;
    }
    body.dark-mode .btn-outline-warning {
        color: #fbbf24 !important;
        border-color: #f59e0b !important;
        background-color: transparent !important;
    }
    body.dark-mode .btn-light {
        background-color: #374151 !important;
        border-color: #4a5568 !important;
        color: #e0e0e0 !important;
    }
    body.dark-mode .btn-light:hover {
        background-color: #4a5568 !important;
        color: #ffffff !important;
    }

    /* ── Dark mode: badges ── */
    body.dark-mode .badge-secondary { background-color: #4a5568; color: #e0e0e0; }
    body.dark-mode .badge-light { background-color: #374151; color: #e0e0e0; }

    /* ── Dark mode: input group ── */
    body.dark-mode .input-group-text {
        background-color: #374151;
        border-color: #4a5568;
        color: #d1d5db;
    }

    /* ── Dark mode: custom controls ── */
    body.dark-mode .custom-control-label { color: #d1d5db; }
    body.dark-mode .custom-control-label::before {
        background-color: #374151;
        border-color: #4a5568;
    }

    /* ── Dark mode: list groups ── */
    body.dark-mode .list-group-item {
        background-color: #374151;
        border-color: #4a5568;
        color: #e0e0e0;
    }
    body.dark-mode .list-group-item:hover { background-color: #4a5568; }

    /* ── Dark mode: popovers & tooltips ── */
    body.dark-mode .popover {
        background-color: #374151;
        border-color: #4a5568;
    }
    body.dark-mode .popover-header {
        background-color: #4a5568;
        border-bottom-color: #4a5568;
        color: #e0e0e0;
    }
    body.dark-mode .popover-body { color: #e0e0e0; }
    body.dark-mode .bs-popover-auto .arrow::after,
    body.dark-mode .bs-popover-top .arrow::after { border-top-color: #374151; }
    body.dark-mode .bs-popover-bottom .arrow::after { border-bottom-color: #374151; }

    /* ── Dark mode: checkbox filter dropdowns (custom component) ── */
    body.dark-mode .chk-filter-panel {
        background-color: #374151 !important;
        border-color: #4a5568 !important;
        color: #e0e0e0 !important;
    }
    body.dark-mode .chk-item { color: #e0e0e0; }
    body.dark-mode .chk-item:hover { background-color: #4a5568 !important; }
    body.dark-mode .chk-footer { border-top-color: #4a5568; }
    body.dark-mode .chk-footer a { color: #9ca3af !important; }
    body.dark-mode .chk-footer a:hover { color: #e0e0e0 !important; }
    body.dark-mode .chk-search {
        background-color: #2d3748 !important;
        border-color: #4a5568 !important;
        color: #e0e0e0 !important;
    }

    /* ── Dark mode: filter bar card (card-outline card-secondary) ── */
    body.dark-mode .card-outline.card-secondary,
    body.dark-mode .card-outline.card-primary,
    body.dark-mode .card-outline.card-info {
        background-color: #2d3748 !important;
        border-color: #4a5568 !important;
    }

    /* ── Dark mode: table striped rows fix for dark bg ── */
    body.dark-mode .table-striped tbody tr:nth-of-type(even) {
        background-color: #2d3748;
    }

    /* ── Dark mode: iCheck / custom radio-checkbox labels ── */
    body.dark-mode .icheck-primary label,
    body.dark-mode .icheck-danger label { color: #d1d5db; }

    /* ── Dark mode: pre / code blocks ── */
    body.dark-mode pre, body.dark-mode code {
        background-color: #1e2938;
        color: #e0e0e0;
        border-color: #4a5568;
    }

    /* ── Dark mode: hr dividers ── */
    body.dark-mode hr { border-color: #4a5568; }

    /* ── Dark mode: scrollbars (WebKit) ── */
    body.dark-mode ::-webkit-scrollbar { width: 8px; height: 8px; }
    body.dark-mode ::-webkit-scrollbar-track { background: #1a1a1a; }
    body.dark-mode ::-webkit-scrollbar-thumb { background: #4a5568; border-radius: 4px; }
    body.dark-mode ::-webkit-scrollbar-thumb:hover { background: #6b7280; }

    /* ── Dark mode: profile page custom classes ── */
    /* Admin action bar */
    body.dark-mode .admin-action-bar {
        background: #374151 !important;
        border-color: #4a5568 !important;
    }
    /* Stat chips (top summary cards on profile) */
    body.dark-mode .stat-chip {
        background: #374151 !important;
        border-color: #4a5568 !important;
    }
    body.dark-mode .chip-label { color: #9ca3af !important; }
    body.dark-mode .chip-val   { color: #e0e0e0 !important; }
    body.dark-mode .chip-icon  { background: #4a5568 !important; }

    /* Profile name & subtitle */
    body.dark-mode .tp-name { color: #e0e0e0 !important; }
    body.dark-mode .tp-sub  { color: #9ca3af !important; }

    /* Info rows (left panel) */
    body.dark-mode .info-row  { border-bottom-color: #374151 !important; }
    body.dark-mode .info-label { color: #9ca3af !important; }
    body.dark-mode .info-text  { color: #d1d5db !important; }

    /* Section dividers */
    body.dark-mode .sect-div {
        color: #f87171 !important;
        border-bottom-color: #4a3030 !important;
    }

    /* Field rows (tab panel details) */
    body.dark-mode .field-row { border-bottom-color: #374151 !important; }
    body.dark-mode .field-lbl { color: #9ca3af !important; }
    body.dark-mode .field-val { color: #e0e0e0 !important; }

    /* Tag pills */
    body.dark-mode .tag-pill  { background: #374151 !important; color: #d1d5db !important; }
    body.dark-mode .tag-red   { background: #4a1f1f !important; color: #fca5a5 !important; }
    body.dark-mode .tag-green { background: #14532d !important; color: #86efac !important; }
    body.dark-mode .tag-blue  { background: #1e3a5f !important; color: #93c5fd !important; }
    body.dark-mode .tag-gold  { background: #451a03 !important; color: #fde68a !important; }
    body.dark-mode .tag-grey  { background: #374151 !important; color: #9ca3af !important; }
    body.dark-mode .tag-purple { background: #3b2a6e !important; color: #c4b5fd !important; }

    /* Trainee profile tabs (.tp-tabs overrides bootstrap .nav-tabs) */
    body.dark-mode .tp-tabs .nav-link {
        background: #374151 !important;
        color: #d1d5db !important;
        border-color: #4a5568 !important;
    }
    body.dark-mode .tp-tabs .nav-link:hover {
        background: #4a5568 !important;
        color: #ffffff !important;
        border-color: #4a5568 !important;
    }
    body.dark-mode .tp-tabs .nav-link.active {
        background: #a02626 !important;
        color: #fff !important;
        border-color: #a02626 !important;
    }

    /* Profile link card */
    body.dark-mode .profile-link-card {
        background: #2d3030 !important;
        border-color: #856404 !important;
    }
    body.dark-mode .profile-link-card .plink-icon { color: #fde68a !important; }

    /* Examiner / fellow profile header section */
    body.dark-mode .exm-profile-header,
    body.dark-mode .profile-header-card,
    body.dark-mode [class*="profile-header"] {
        background: #1f2937 !important;
        color: #e0e0e0 !important;
    }

    /* Generic white bg containers that escape card coverage */
    body.dark-mode .bg-white { background-color: #2d3748 !important; }
    body.dark-mode .bg-light { background-color: #374151 !important; }

    /* Inline style overrides for hardcoded light backgrounds on rows/divs */
    body.dark-mode [style*="background-color: #fff"],
    body.dark-mode [style*="background-color:#fff"],
    body.dark-mode [style*="background-color: white"],
    body.dark-mode [style*="background:#fff"],
    body.dark-mode [style*="background: #fff"],
    body.dark-mode [style*="background-color: #f8f9fa"],
    body.dark-mode [style*="background-color:#f8f9fa"],
    body.dark-mode [style*="background-color: #fff8e1"],
    body.dark-mode [style*="background-color:#fff8e1"] {
        background-color: #2d3748 !important;
    }

    /* Inline hardcoded dark text colors */
    body.dark-mode [style*="color: #333"],
    body.dark-mode [style*="color:#333"],
    body.dark-mode [style*="color: #222"],
    body.dark-mode [style*="color:#222"],
    body.dark-mode [style*="color: #555"],
    body.dark-mode [style*="color:#555"],
    body.dark-mode [style*="color: #495057"],
    body.dark-mode [style*="color:#495057"] {
        color: #e0e0e0 !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const darkModeToggle = document.getElementById('darkModeToggle');
        const darkModeIcon = document.getElementById('darkModeIcon');
        const body = document.body;
        const html = document.documentElement;

        // Default to light mode
        const currentTheme = localStorage.getItem('theme') || 'light';

        // Apply the saved theme and set the correct icon
        if (currentTheme === 'dark') {
            body.classList.add('dark-mode');
            html.classList.add('dark-mode');
            darkModeIcon.classList.remove('fa-moon');
            darkModeIcon.classList.add('fa-sun');
            darkModeToggle.setAttribute('title', 'Switch to Light Mode');
        }

        // Toggle dark mode
        darkModeToggle.addEventListener('click', function(e) {
            e.preventDefault();

            body.classList.toggle('dark-mode');
            html.classList.toggle('dark-mode');

            if (body.classList.contains('dark-mode')) {
                // Switch to dark mode
                darkModeIcon.classList.remove('fa-moon');
                darkModeIcon.classList.add('fa-sun');
                darkModeToggle.setAttribute('title', 'Switch to Light Mode');
                localStorage.setItem('theme', 'dark');
            } else {
                // Switch to light mode
                darkModeIcon.classList.remove('fa-sun');
                darkModeIcon.classList.add('fa-moon');
                darkModeToggle.setAttribute('title', 'Toggle Dark Mode');
                localStorage.setItem('theme', 'light');
            }
        });
    });
</script>
