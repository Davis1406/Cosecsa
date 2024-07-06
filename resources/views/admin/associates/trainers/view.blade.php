@extends('layout.app')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6" style="text-align: left">
                    <a href="{{ url('admin/associates/trainers/list') }}" class="btn btn-primary">
                        <span class="fas fa-arrow-left"></span> Trainers List
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
                <h3 class="card-title">Trainer Details</h3>
            </div>

            <div class="card-body">
                <div class="row">
                    @if ($trainer)
                    <div class="col-md-3">
                        <div class="text-center">
                            <img src="{{ asset('storage/' . $trainer->profile_image) }}" alt="Profile Image" class="img-fluid img-thumbnail">
                            <h5 class="mt-2">{{ $trainer->name }}</h5>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <table class="table table-bordered">
                            <tr>
                                <th>Full Name</th>
                                <td>{{ $trainer->name }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ $trainer->user_email }}</td>
                            </tr>

                            <tr>
                                <th>Hospital Name</th>
                                <td>{{ $trainer->hospital_name }}</td>
                            </tr>
                            <tr>
                                <th>Country</th>
                                <td>{{ $trainer->country_name }}</td>
                            </tr>
                            <tr>
                                <th>PD Phone Number</th>
                                <td>{{ $trainer->phone_number }}</td>
                            </tr>
                            <tr>
                                <th>Assistant PD Name</th>
                                <td>{{ $trainer->assistant_pd }}</td>
                            </tr>
                            <tr>
                                <th>Assistant PD Email</th>
                                <td>{{ $trainer->assistant_email }}</td>
                            </tr>
                            <tr>
                                <th>Mobile Number</th>
                                <td>{{ $trainer->mobile_no }}</td>
                            </tr>
                            <tr>
                                <th>User Type</th>
                                <td>
                                    @if ($trainer->user_type == 4)
                                        Trainer
                                    @else
                                        Unknown
                                    @endif
                                </td>
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
