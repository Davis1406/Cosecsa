<!-- Navbar -->
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
    </ul>
  
    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li>
    </ul>
  </nav>
  <!-- /.navbar -->
  
  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="" class="brand-link">
        <img src="{{ url('public/dist/img/Cosecsa_Logo.png') }}" alt="Cosecsa Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">COSECSA</span>
    </a>
  
    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ url('public/dist/img/user2-160x160.jpg') }}" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ Auth::user()->name }}</a>
            </div>
        </div>
  
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <!-- Add icons to the links using the .nav-icon class with font-awesome or any other icon font library -->
  
                @if (Auth::user()->user_type == 1)
  
                <li class="nav-item">
                    <a href="{{ url('admin/dashboard') }}" class="nav-link @if (Request::segment(2) == 'dashboard') active @endif">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                            Dashboard
                        </p>
                    </a>
                </li>
  
                <li class="nav-item">
                    <a href="{{ url('admin/list') }}" class="nav-link @if (Request::segment(2) == 'list') active @endif">
                        <i class="nav-icon fas fa-user"></i>
                        <p>
                            Admin
                        </p>
                    </a>
                </li>
  
                <li class="nav-item">
                    <a href="{{ url('admin/hospital/list') }}" class="nav-link @if (Request::segment(2) == 'hospital') active @endif">
                        <i class="nav-icon fas fa-hospital"></i>
                        <p>
                            Accredited Hospitals
                        </p>
                    </a>
                </li>
  
                <li class="nav-item">
                    <a href="{{ url('admin/programmes/list') }}" class="nav-link @if (Request::segment(2) == 'programmes') active @endif">
                        <i class="nav-icon fas fa-book"></i>
                        <p>
                            Programmes
                        </p>
                    </a>
                </li>
  
                <li class="nav-item">
                    <a href="{{ url('admin/hospitalprogrammes/list') }}" class="nav-link @if (Request::segment(2) == 'hospitalprogrammes') active @endif">
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
                            <a href="{{ url('admin/associates/trainees/trainees') }}" class="nav-link @if (Request::segment(3) == 'trainees') active @endif">
                                <i class="fas fa-user-md nav-icon"></i>
                                <p>Trainees</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ url('admin/associates/candidates/list') }}" class="nav-link @if (Request::segment(3) == 'candidates') active @endif">
                                <i class="fas fa-graduation-cap nav-icon"></i>
                                <p>Candidates</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="./index3.html" class="nav-link">
                                <i class="fas fa-user nav-icon"></i>
                                <p>Members</p>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="./index3.html" class="nav-link">
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
                                    <a href="{{ url('admin/associates/trainers/list') }}" class="nav-link @if (Request::segment(3) == 'trainers') active @endif">
                                        <i class="fas fa-chalkboard-teacher nav-icon"></i>
                                        <p>Programme Directors</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ url('admin/associates/reps/list') }}" class="nav-link @if (Request::segment(3) == 'reps') active @endif">
                                        <i class="fas fa-flag nav-icon"></i>
                                        <p>Country Representatives</p>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </li>
  
                <li class="nav-item">
                    <a href="{{ url('admin/examinations/list') }}" class="nav-link @if (Request::segment(2) == 'examinations') active @endif">
                        <i class="nav-icon fas fa-book-open"></i>
                        <p>
                            Examinations
                        </p>
                    </a>
                </li>
  
                <li class="nav-item">
                    <a href="{{ url('profile/change_password') }}" class="nav-link @if (Request::segment(2) == 'change_password') active @endif">
                        <i class="nav-icon fas fa-cog"></i>
                        <p>
                            Profile Settings
                        </p>
                    </a>
                </li>
  
                @elseif (Auth::user()->user_type == 2)
  
                <li class="nav-item">
                    <a href="{{ url('trainee/dashboard') }}" class="nav-link @if (Request::segment(2) == 'dashboard') active @endif">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>
                            Dashboard
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('admin/list') }}" class="nav-link @if (Request::segment(2) == 'admin') active @endif">
                        <i class="nav-icon fas fa-user"></i>
                        <p>
                            Trainee
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
    </div>
    <!-- /.sidebar -->
  </aside>
  
  <style>
  .nav-pills .nav-link.active,
  .nav-pills .show > .nav-link {
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
  </style>
  