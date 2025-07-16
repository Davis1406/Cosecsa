<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ !empty('$header_title') ? $header_title : '' }}-Cosecsa</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    
    <!-- Bootstrap CSS - Added for proper grid system -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ url('public/plugins/fontawesome-free/css/all.min.css') }}">
    <!-- Ionicons -->
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">

    <!-- Tempusdominus Bootstrap 4 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/5.5.2/css/ionicons.min.css">

    <!--Select CSS-->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!--Toms Select-->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.0.0-rc.4/dist/css/tom-select.css" rel="stylesheet">

    <link rel="stylesheet"
        href="{{ url('public/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
    <!-- iCheck -->
    <link rel="stylesheet" href="{{ url('public/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
    <!-- JQVMap -->
    <link rel="stylesheet" href="{{ url('public/plugins/jqvmap/jqvmap.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ url('public/dist/css/adminlte.min.css') }}">

    <!-- Favicon -->
    <link rel="icon" href="{{ url('public/dist/img/Cosecsa_Logo.png') }}">

    <!-- DataTables -->
    <link rel="stylesheet" href="{{ url('public/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ url('public/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ url('public/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">

    <link rel="stylesheet" href="{{ url('public/dist/css/wizard.css') }}">
    <link rel="stylesheet" href="{{ url('public/dist/css/custom.css') }}">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="{{ url('public/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <!-- Daterange picker -->
    <link rel="stylesheet" href="{{ url('public/plugins/daterangepicker/daterangepicker.css') }}">
    <!-- summernote -->
    <link rel="stylesheet" href="{{ url('public/plugins/summernote/summernote-bs4.min.css') }}">
    
    <!-- Custom styles from individual pages -->
    @stack('styles')
</head>

<body class="hold-transition sidebar-mini layout-fixed">
    <div class="wrapper">

        {{-- <!-- Preloader -->
  <div class="preloader flex-column justify-content-center align-items-center">
    <img class="animation__shake" src="{{url('public/dist/img/Cosecsa_Logo.png')}}" alt="AdminLTELogo" height="60" width="60">
  </div> --}}

        @include('layout.header')

        @yield('content')

        @include('layout.footer')

    </div>
    <!-- ./wrapper -->

    <!-- jQuery - Single instance only -->
    <script src="{{ url('public/plugins/jquery/jquery.min.js') }}"></script>

    <!-- jQuery UI 1.11.4 -->
    <script src="{{ url('public/plugins/jquery-ui/jquery-ui.min.js') }}"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
        $.widget.bridge('uibutton', $.ui.button)
    </script>
    
    <!-- Bootstrap 4 -->
    <script src="{{ url('public/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Select2 -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- ChartJS -->
    {{-- <script src="{{ url('public/plugins/chart.js/Chart.min.js') }}"></script> --}}
    <!-- Sparkline -->
    <script src="{{ url('public/plugins/sparklines/sparkline.js') }}"></script>
    <!-- JQVMap -->
    <script src="{{ url('public/plugins/jqvmap/jquery.vmap.min.js') }}"></script>
    <script src="{{ url('public/plugins/jqvmap/maps/jquery.vmap.usa.js') }}"></script>
    <!-- jQuery Knob Chart -->
    <script src="{{ url('public/plugins/jquery-knob/jquery.knob.min.js') }}"></script>
    <!-- daterangepicker -->
    <script src="{{ url('public/plugins/moment/moment.min.js') }}"></script>
    <script src="{{ url('public/plugins/daterangepicker/daterangepicker.js') }}"></script>
    <!-- Tempusdominus Bootstrap 4 -->
    <script src="{{ url('public/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
    <!-- Summernote -->
    <script src="{{ url('public/plugins/summernote/summernote-bs4.min.js') }}"></script>
    <!-- overlayScrollbars -->
    <script src="{{ url('public/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>
    <!-- AdminLTE App -->
    <script src="{{ url('public/dist/js/adminlte.js') }}"></script>

    <!-- ChartJS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>


    <!-- DateTimePicker -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tempus-dominus/5.39.0/js/tempus-dominus.min.js"></script>

    <script src="{{ url('public/dist/js/pages/dashboard.js') }}"></script>
    <script src="{{ url('public/dist/js/pages/wizard.js') }}"></script>
    <script src="{{ url('public/dist/js/custom.js') }}"></script>

    <!-- DataTables  & Plugins -->
    <script src="{{ url('public/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ url('public/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ url('public/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ url('public/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ url('public/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ url('public/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ url('public/plugins/jszip/jszip.min.js') }}"></script>
    <script src="{{ url('public/plugins/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ url('public/plugins/pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ url('public/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ url('public/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ url('public/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>

    <!-- Custom scripts from individual pages -->
    @stack('scripts')

</body>

</html>