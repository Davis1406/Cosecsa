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
                                <h1>Add New Hospital</h1>
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
                                                <label>Hospital Name</label>
                                                <input type="text" class="form-control" value="" name="name"  required placeholder="Hospital name">
                                            </div>
                                            
                                            <div class="form-group">
                                                <label for="country_id">Country:</label>
                                                <select name="country_id" class="form-control" required>
                                                    @foreach($countries as $country)
                                                        <option value="{{ $country->id }}">{{ $country->country_name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label>Status</label>
                                                <select name="status" id="" class="form-control">
                                                    <option value="0">Active</option>
                                                    <option value="1">Inactive</option>
                                                </select>
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
