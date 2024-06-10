@extends('layout.app')

@section('content')
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>AdminLTE 3 | General Form Elements</title>

        <!-- Google Font: Source Sans Pro -->
        <link rel="stylesheet"
            href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
        <!-- Theme style -->
        <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
    </head>

    <body class="hold-transition sidebar-mini">
        <div class="wrapper">
            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <h1>Change Password</h1>
                            </div>
                        </div>
                    </div><!-- /.container-fluid -->
                </section>
                
                <!-- Main content -->
                <section class="content">
                    <div class="container-fluid">
                        <div class="row">
                            <!-- left column -->
                            <div class="col-md-12">
                                @include('_message')
                                <!-- general form elements -->
                                <div class="card card-primary">
                                    <div class="card-header" style="background-color: darkred">
                                        <h3 class="card-title">Fill in details</h3>
                                    </div>
                                    <!-- /.card-header -->
                                    <!-- form start -->

                                    <form method="POST" action="{{ url('profile/change_password') }}">
                                        @csrf
                                        <div class="card-body">

                                            <div class="form-group">
                                                <label for="password">Old Password:</label>
                                                <input type="password" name="old_password" class="form-control" required placeholder="Old Password">
                                            </div>

                                            <div class="form-group">
                                                <label for="programme_type">New Password:</label>
                                                <input type="password" name="new_password" class="form-control" required placeholder="New Password">
                                            </div>
                            
                                        </div>
                                        <!-- /.card-body -->

                                        <div class="card-footer" style="background-color: white">
                                            <button type="submit" class="btn btn-primary" style="background-color: #FEC503;border-color:#FEC503">Submit</button>
                                        </div>
                                    </form>
                                </div>
                                <!-- /.card -->
                            </div>
                            <!--/.col (left) -->
                        </div>
                        <!-- /.row -->
                    </div>
                    <!-- /.container-fluid -->
                </section>
                <!-- /.content -->
            </div>


        </div>

        <script>
            $(function() {
                bsCustomFileInput.init();
            });
        </script>
    </body>

    </html>
@endsection
