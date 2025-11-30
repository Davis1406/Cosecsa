<!-- Inline script to prevent flash of light mode -->
<script>
    // Apply dark mode immediately if it was previously selected
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
                    <img src="{{ url('public/dist/img/user.png') }}" class="img-circle elevation-2" alt="User Image">
                </div>
                <div class="info">
                    <a href="#" class="d-block">{{ Auth::user()->name }}</a>
                </div>
            </div>

            <!-- Sidebar Menu -->
            <nav class="mt-2">
                <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                    data-accordion="false">
                    <!-- Add icons to the links using the .nav-icon class with font-awesome or any other icon font library -->

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

                        <li class="nav-item">
                            <a href="{{ url('admin/list') }}"
                                class="nav-link @if (Request::segment(2) == 'list') active @endif">
                                <i class="nav-icon fas fa-user"></i>
                                <p>
                                    Admin
                                </p>
                            </a>
                        </li>

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

                        <li class="nav-item @if (Request::segment(2) == 'associates') menu-open @endif">
                            <a href="#" class="nav-link @if (Request::segment(2) == 'associates') active @endif">
                                <i class="nav-icon fas fa-users"></i>
                                <p>
                                    Associates
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview">
                                <li class="nav-item">
                                    <a href="{{ url('admin/associates/trainees/trainees') }}"
                                        class="nav-link @if (Request::segment(3) == 'trainees') active @endif">
                                        <i class="fas fa-user-md nav-icon"></i>
                                        <p>Trainees</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('admin/associates/candidates/list') }}"
                                        class="nav-link @if (Request::segment(3) == 'candidates') active @endif">
                                        <i class="fas fa-graduation-cap nav-icon"></i>
                                        <p>Candidates</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('admin/associates/members/list') }}"
                                        class="nav-link @if (Request::segment(3) == 'members') active @endif">
                                        <i class="fas fa-user nav-icon"></i>
                                        <p>Members</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('admin/associates/fellows/list') }}"
                                        class="nav-link @if (Request::segment(3) == 'fellows') active @endif">
                                        <i class="fas fa-user nav-icon"></i>
                                        <p>Fellows</p>
                                    </a>
                                </li>
                                <li class="nav-item @if (Request::segment(3) == 'trainers' || Request::segment(3) == 'reps') menu-open @endif">
                                    <a href="#" class="nav-link">
                                        <i class="fas fa-stethoscope nav-icon"></i>
                                        <p>PD's & Country Reps<i class="right fas fa-angle-left"></i></p>
                                    </a>
                                    <ul class="nav nav-treeview">
                                        <li class="nav-item">
                                            <a href="{{ url('admin/associates/trainers/list') }}"
                                                class="nav-link @if (Request::segment(3) == 'trainers') active @endif">
                                                <i class="fas fa-chalkboard-teacher nav-icon"></i>
                                                <p>Programme Directors</p>
                                            </a>
                                        </li>
                                        <li class="nav-item">
                                            <a href="{{ url('admin/associates/reps/list') }}"
                                                class="nav-link @if (Request::segment(3) == 'reps') active @endif">
                                                <i class="fas fa-flag nav-icon"></i>
                                                <p>Country Representatives</p>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
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
                                            <a href="{{ url('admin/associates/promotion/promote_candidates') }}"
                                                class="nav-link @if (Request::segment(4) == 'promote_candidates') active @endif">
                                                <i class="fas fa-upload nav-icon"></i>
                                                <p>Promote Candidates</p>
                                            </a>
                                            <a href="{{ url('admin/promotion/manage') }}"
                                                class="nav-link @if (Request::segment(3) == 'manage') active @endif">
                                                <i class="fas fa-cogs nav-icon"></i>
                                                <p>Manage Promotions</p>
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                            </ul>
                        </li>
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
                                <i class="nav-icon fas fa-cog "></i>
                                <p>
                                    Profile Settings
                                </p>
                            </a>
                        </li>
                    @endif

                    <li class="nav-item">
                        <a href="{{ url('logout') }}" class="nav-link">
                            <i class="nav-icon fa fa-sign-out-alt"></i>
                            <p>
                                Logout
                            </p>
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
    .nav-pills .nav-link.active,
    .nav-pills .show>.nav-link {
        color: #fff;
        background-color: #a02626 !important;
    }

    .nav-treeview .nav-link.active {
        color: #fff !important;
        background-color: #495057 !important;
    }

    .nav-treeview .nav-item .nav-treeview {
        padding-left: 20px;
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
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const darkModeToggle = document.getElementById('darkModeToggle');
        const darkModeIcon = document.getElementById('darkModeIcon');
        const body = document.body;
        const html = document.documentElement;

        // Check for saved theme preference or default to light mode
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
