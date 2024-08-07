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
                        <a href="{{url('admin/hospital/add')}}" class="btn btn-primary">Add New Hospital</a>
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
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Accredited Hospitals</h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <table id="hospitalTable" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Hospital Name</th>
                                            <th>Country</th>
                                            <th>Hospital Type</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($getRecord as $value)
                                        <tr>
                                            <td>{{$value->id}}</td>
                                            <td>{{$value->name}}</td>
                                            <td>{{$value->country_name}}</td>
                                            <td>
                                                @if($value->hospital_type == 1)
                                                Government Hospital
                                                @elseif($value->hospital_type == 2)
                                                NGO / Faith based Hospital
                                                @elseif($value->hospital_type == 3)
                                                Private Hospital
                                                @elseif($value->hospital_type == 4)
                                                University Teaching Hospital
                                                @endif
                                            </td>
                                            <td>
                                                @if($value->status == 0)
                                                Active
                                                @else
                                                Inactive
                                                @endif
                                            </td>
                                            <td>
                                                <a href="#" data-id="{{$value->id}}" data-name="{{$value->name}}" class="action-icon" data-toggle="popover" data-html="true" data-content='
                                                    <a href="{{url('admin/hospital/view_hospital/'.$value->id)}}"><i class="fa fa-eye action-icon"></i> View</a>
                                                    <a href="{{url('admin/hospital/edit_hospital/'.$value->id)}}"><i class="fa fa-edit action-icon"></i> Edit</a>
                                                    <a href="{{url('admin/hospital/delete/'.$value->id)}}"><i class="fa fa-trash action-icon"></i> Delete</a>'>
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
