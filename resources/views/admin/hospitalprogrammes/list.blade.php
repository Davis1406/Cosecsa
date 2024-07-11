@extends('layout.app')  

@section('content')
  
<div class="wrapper">

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">

        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                    </div>
                    <div class="col-sm-6" style="text-align: right">
                        <a href="{{ url('admin/hospitalprogrammes/import') }}" class="btn btn-primary" style="color:black; background-color: #FEC503; border-color: #FEC503;">Import Programmes <span class="fas fa-upload"></a>
                        <a href="{{ url('admin/hospitalprogrammes/add') }}" class="btn btn-primary" style="background-color: #a02626; border-color: #a02626;">Assign Programme</a>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <div class="col-md-12">
            @include('_message')
        </div>

        <!-- Main content -->
        <section class="content">
            <div class="container-wrapper">
                <div class="row">
                    <div class="col-12">
                        <!-- /.card -->

                        <!-- DataTable -->
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Hospital Programme List</h3>
                            </div>
                            <div class="card-body">
                                <table id="hospitalProgrammesTable" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Hospital Name</th>
                                            <th>Programme Name</th>
                                            <th>Country</th>
                                            <th>Accredited Date</th>
                                            <th>Expiry Date</th>
                                             <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($getHospitalProgrammes as $data)
                                        <tr>
                                            <td>{{ $data->id }}</td>
                                            <td>{{ $data->hospital_name }}</td>
                                            <td>{{ $data->programme_name }}</td>
                                            <td>{{ $data->country_name }}</td>
                                            <td>{{ $data->accredited_date }}</td>
                                            <td>{{ $data->expiry_date }}</td>
                                            <td>
                                                @if($data->status == 'Active')
                                                Active
                                                @else
                                                Expired
                                                @endif
                                            </td>
                                            <td>
                                                <a href="#" data-id="{{ $data->id }}" class="action-icon" data-toggle="popover" data-html="true" data-content='
                                                    <a href="{{ url('admin/hospitalprogrammes/edit/' . $data->id) }}"><i class="fa fa-edit action-icon"></i> Edit</a>
                                                    <a href="{{ url('admin/hospitalprogrammes/delete/' . $data->id) }}"><i class="fa fa-trash action-icon"></i> Delete</a>'>
                                                    <i class="fa fa-bars" aria-hidden="true" style="color: #5a6268"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </div>
                    <!-- /.col -->
                </div>
                <!-- /.row -->
            </div>
            <!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

</div>
<!-- ./wrapper -->

@endsection

