@extends('layout.app')

@section('content')
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>AdminLTE 3 | General Form Elements</title>

        <!-- Google Font: Source Sans Pro -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
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
                                <h1>Edit Hospital Programme</h1>
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
                                        @csrf
                                        <div class="card-body">

                                            <div class="form-group">
                                                <label>Hospital Name</label>
                                                <select name="hospital_id" class="form-control" required disabled> 
                                                    <option value="">Select Hospital</option>
                                                    @foreach($getHospital as $hospital)
                                                        <option value="{{ $hospital->id }}" {{ $hospitalProgramme->hospital_id == $hospital->id ? 'selected' : '' }}>
                                                            {{ $hospital->name }} - {{ $hospital->country_name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="programme_id">Programme Name</label>
                                                <select name="programme_id" id="programme_id" class="form-control" required disabled>
                                                    <option value="">Select Programme</option>
                                                    @foreach($getProgramme as $programme)
                                                        <option value="{{ $programme->id }}" {{ in_array($programme->id, (array)$hospitalProgramme->programme_id) ? 'selected' : '' }}>
                                                            {{ $programme->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            
                                            
                                            <div class="form-row">

                                                <div class="form-group col-md-6">
                                                    <label for="accredited_date">Accredited Date</label>
                                                    <input type="date" name="accredited_date" class="form-control" value="{{ $hospitalProgramme->accredited_date }}">
                                                </div>
                                                <div class="form-group col-md-6">
                                                    <label for="expiry_date">Expiry Date</label>
                                                    <input type="date" name="expiry_date" class="form-control" id="expiry_date" value="{{ $hospitalProgramme->expiry_date }}">
                                                </div>
    
                                            </div>

                                            <div class="form-group">
                                                <label>Status</label>
                                                <select name="status" class="form-control">
                                                    <option value="0" {{ $hospitalProgramme->status == 0 ? 'selected' : '' }}>Active</option>
                                                    <option value="1" {{ $hospitalProgramme->status == 1 ? 'selected' : '' }}>Inactive</option>
                                                </select>
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
