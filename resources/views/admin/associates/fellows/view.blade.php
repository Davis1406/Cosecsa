@extends('layout.app')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6" style="text-align: left">
                    <a href="{{ url('admin/associates/fellows/list') }}" class="btn btn-primary" style="background-color: #a02626; border-color:#a02626">
                        <span class="fas fa-arrow-left"></span> Fellows List
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
                <h3 class="card-title">Fellow Details</h3>
            </div>

            <div class="card-body">
                <div class="row">
                    @if ($fellow)
                    <div class="col-md-3">
                        <div class="text-center">
                            <img src="{{ url('public/dist/img/user.png') }}" alt="Profile Image" class="img-fluid img-thumbnail" style="width: 50%; height:50%">
                            <h5 class="mt-2">{{ $fellow->fellow_name }}</h5>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <table class="table table-bordered">
                            <tr>
                                <th>Full Name</th>
                                <td>{{ $fellow->fellow_name }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ $fellow->personal_email }}</td>
                            </tr>
                            <tr>
                                <th>Country</th>
                                <td>{{ $fellow->country_name }}</td>
                            </tr>
                            <tr>
                                <th>Gender</th>
                                <td>{{ $fellow->gender }}</td>
                            </tr>

                            <tr>
                                <th>Mobile Number</th>
                                <td>{{ $fellow->phone_number }}</td>
                            </tr>
                            <tr>
                                <th>Fellowship Type</th>
                                <td>{{ $fellow->fellowship_type }}</td>
                            </tr>

                            <tr>
                                <th>Fellowship Year</th>
                                <td>{{ $fellow->fellowship_year }}</td>
                            </tr>

                            <tr>
                                <th>Fellowship Programme</th>
                                <td>{{ $fellow->programme_name }}</td>
                            </tr>

                            <tr>
                                <th>Current Specialty</th>
                                <td>{{ $fellow->current_specialty }}</td>
                            </tr>


                            <tr>
                                <th>Current Hospital</th>
                                <td>{{ $fellow->organization }}</td>
                            </tr>
                            <tr>
                                <th>Admission Year</th>
                                <td>{{ $fellow->admission_year }}</td>
                            </tr>

                            <tr>
                                <th>Address</th>
                                <td>{{ $fellow->address }}</td>
                            </tr>

                            <tr>
                                <th>Status</th>
                                <td>{{ $fellow->status }}</td>
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
