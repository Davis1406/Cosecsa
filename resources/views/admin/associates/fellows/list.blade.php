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
                        <a href="{{url('admin/associates/fellows/import_fellows')}}" class="btn btn-secondary" style="color:black; background-color: #FEC503; border-color: #FEC503;">Upload Fellows <span class="fas fa-upload"></span></a>
                        <a href="{{url('admin/associates/fellows/add')}}" class="btn btn-primary" style="background-color: #a02626; border-color: #a02626;">Add New Fellows</a>
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
                                <h3 class="card-title">Fellows</h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <table id="fellowstable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Country</th>
                                            <th>Fellowship Type</th>
                                            <th>Fellowship Year</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($getFellows as $value)
                                        <tr>
                                            <td>{{$value->fellow_id}}</td>
                                            <td>{{$value->fellow_name}}</td>
                                            <td>{{$value->personal_email}}</td>
                                            <td>{{$value->country_name}}</td>
                                            <td>{{$value->fellowship_type}}</td>
                                            <td>{{$value->fellowship_year}}</td>
                                 
                                            <td>
                                                <a href="#" data-id="{{$value->trainer_id}}" data-name="{{$value->name}}" class="action-icon" data-toggle="popover" data-html="true" data-content='
                                                    <a href="{{url('admin/associates/fellows/view/'.$value->fellow_id)}}"><i class="fa fa-eye action-icon"></i> View</a>
                                                    <a href="{{url('admin/associates/fellows/edit/'.$value->fellow_id)}}"><i class="fa fa-edit action-icon"></i> Edit</a>
                                                    <a href="{{url('admin/associates/fellows/delete/'.$value->f_id)}}"><i class="fa fa-trash action-icon"></i> Delete</a>'>
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

@section('scripts')
<script>
    $(document).ready(function(){
        $('.filter-button').click(function(){
            var filter = $(this).attr('data-filter');
            if (filter == 'all') {
                $('.user-row').show();
            } else {
                $('.user-row').hide();
                $('.user-row[data-user-type="' + filter + '"]').show();
            }
        });
    });
</script>
@endsection
