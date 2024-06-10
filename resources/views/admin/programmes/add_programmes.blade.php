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
                                <h1>Add New Programme</h1>
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
                                <!-- general form elements -->
                                <div class="card card-primary">
                                    <div class="card-header" style="background-color: darkred">
                                        <h3 class="card-title">Fill in details</h3>
                                    </div>
                                    <!-- /.card-header -->
                                    <!-- form start -->

                                    <form method="POST" action="">
                                        {{ csrf_field() }}
                                        <div class="card-body">

                                            <div class="form-group">
                                                <label for="name">Programme Name:</label>
                                                <input type="text" name="name" class="form-control" required>
                                            </div>

                                            <div class="form-group">
                                                <label for="programme_type">Programme Type:</label>
                                                <input type="text" name="programme_type" class="form-control" required>
                                            </div>
                                
                                            <div class="form-group">
                                                <label for="duration">Duration:</label>
                                                <input type="text" name="duration" class="form-control" required>
                                            </div>
                                     
                                          <div class="form-group">
                                             <label for="entry_fee">Entry Fee:</label>
                                             <input type="number" name="entry_fee" class="form-control" required>
                                          </div>
                                
                                          <div class="form-group">
                                             <label for="exam_fee">Exam Fee:</label>
                                             <input type="number" name="exam_fee" class="form-control" required>
                                          </div>
                                
                                          <div class="form-group">
                                             <label for="repeat_fee">Repeat Fee:</label>
                                             <input type="number" name="repeat_fee" class="form-control" required>
                                          </div>

                                        </div>
                                        <!-- /.card-body -->

                                        <div class="card-footer" style="background-color: white">
                                            <button type="submit" class="btn btn-primary"
                                                style="background-color: #FEC503;border-color:#FEC503">Submit</button>
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

