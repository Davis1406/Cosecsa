@extends('layout.app')

@section('content')
    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6" style="text-align: left">
                        <a href="{{ url('admin/exams/examiners') }}" class="btn btn-primary"
                            style="background-color: #a02626; border-color:#a02626">
                            <span class="fas fa-arrow-left"></span> Examiners List
                        </a>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>
        <!-- Main content -->
        <section class="content">
            <!-- general form elements -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Examiners Details</h3>
                </div>

                <div class="card-body">
                    <div class="row">
                        @if ($examiner)
                            <div class="col-md-3">
                                <div class="text-center">
                                    <img src="{{ url('public/dist/img/user.png') }}" alt="Profile Image"
                                        class="img-fluid img-thumbnail" style="width: 50%; height:50%">
                                    <h5 class="mt-2">{{ $examiner->examiner_name }}</h5>
                                </div>
                            </div>
                            <div class="col-md-9">
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Full Name</th>
                                        <td>{{ $examiner->examiner_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Email</th>
                                        <td>{{ $examiner->email }}</td>
                                    </tr>
                                    <tr>
                                        <th>Country</th>
                                        <td>{{ $examiner->country_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Gender</th>
                                        <td>{{ $examiner->gender }}</td>
                                    </tr>

                                    <tr>
                                        <th>Mobile Number</th>
                                        <td>{{ $examiner->mobile }}</td>
                                    </tr>
                                    <tr>
                                        <th>examiner ID</th>
                                        <td>{{ $examiner->examiner_id }}</td>
                                    </tr>

                                    <tr>
                                        <th>Group Name</th>
                                        <td>{{ $examiner->group_name }}</td>
                                    </tr>

                                    <tr>
                                        <th>Current Specialty</th>
                                        <td>{{ $examiner->specialty }}</td>
                                    </tr>


                                    <tr>
                                        <th>Shift</th>
                                        <td>{{ $examiner->shift }}</td>
                                    </tr>
                                </table>
                            </div>
                        @else
                            <div class="col-md-12">
                                <p>No Trainer Data found.</p>
                            </div>
                        @endif
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
@endsection
