@extends('layout.app')

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Hospital Programme</title>

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
                                <form method="POST" action="{{ url('admin/hospitalprogrammes/edit/' . $hospitalProgramme->id) }}" onsubmit="return validateForm()">
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
                                            <!-- Hidden input to pass hospital_id value -->
                                            <input type="hidden" name="hospital_id" value="{{ $hospitalProgramme->hospital_id }}">
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="programme_id">Programme Name</label>
                                            @foreach($getProgramme as $programme)
                                            <div>
                                                <label style="font-weight: normal;">
                                                    <input type="checkbox" name="programme_id[]" value="{{ $programme->id }}"
                                                           {{ in_array($programme->id, $assignedProgrammes) ? 'checked' : '' }}>
                                                    {{ $programme->name }}
                                                </label>
                                                @if(in_array($programme->id, $assignedProgrammes))
                                                    <input type="hidden" name="existing_programme_id[]" value="{{ $programme->id }}">
                                                @endif
                                            </div>
                                            @endforeach
                                        </div>
                                                              
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                <label for="accredited_date">Accredited Date</label>
                                                <input type="month" name="accredited_date" class="form-control" value="{{ \Carbon\Carbon::parse($hospitalProgramme->accredited_date)->format('Y-m') }}">
                                            </div>
                                            <div class="form-group col-md-6">
                                                <label for="expiry_date">Expiry Date</label>
                                                <input type="month" name="expiry_date" class="form-control" id="expiry_date" value="{{ \Carbon\Carbon::parse($hospitalProgramme->expiry_date)->format('Y-m') }}">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Status</label>
                                            <select name="status" class="form-control">
                                                <option value="Active" {{ $hospitalProgramme->status == 'Active' ? 'selected' : '' }}>Active</option>
                                                <option value="Expired" {{ $hospitalProgramme->status == 'Expired' ? 'selected' : '' }}>Expired</option>
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
        function validateForm() {
            const checkboxes = document.querySelectorAll('input[name="programme_id[]"]');
            let isChecked = false;
            checkboxes.forEach(checkbox => {
                if (checkbox.checked) {
                    isChecked = true;
                }
            });

            if (!isChecked) {
                alert('Please select at least one programme.');
                return false;
            }
            return true;
        }

        $(function() {
            bsCustomFileInput.init();
        });
    </script>
</body>
</html>
@endsection
