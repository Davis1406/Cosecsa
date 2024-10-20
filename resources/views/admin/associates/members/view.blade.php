@extends('layout.app')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6" style="text-align: left">
                    <a href="{{ url('admin/associates/members/list') }}" class="btn btn-primary">
                        <span class="fas fa-arrow-left"></span> Members List
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
                <h3 class="card-title">Members Details</h3>
            </div>

            <div class="card-body">
                <div class="row">
                    @if ($member)
                    <div class="col-md-3">
                        <div class="text-center">
                            <img src="{{ url('public/dist/img/user.png') }}" alt="Profile Image" class="img-fluid img-thumbnail" style="width: 50%; height:50%">
                            <h5 class="mt-2">{{ $member->member_name }}</h5>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <table class="table table-bordered">
                            <tr>
                                <th>Full Name</th>
                                <td>{{ $member->member_name }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ $member->personal_email }}</td>
                            </tr>
                            <tr>
                                <th>Country</th>
                                <td>{{ $member->country_name }}</td>
                            </tr>
                            <tr>
                                <th>Gender</th>
                                <td>{{ $member->gender }}</td>
                            </tr>

                            <tr>
                                <th>Mobile Number</th>
                                <td>{{ $member->phone_number }}</td>
                            </tr>
                            <tr>
                                <th>Membership Type</th>
                                <td>{{ $member->membership_type }}</td>
                            </tr>

                            <tr>
                                <th>Membership Year</th>
                                <td>{{ $member->membership_year }}</td>
                            </tr>

                            <tr>
                                <th>Admission Year</th>
                                <td>{{ $member->admission_year }}</td>
                            </tr>

                            <tr>
                                <th>Address</th>
                                <td>{{ $member->address }}</td>
                            </tr>

                            <tr>
                                <th>Status</th>
                                <td>{{ $member->status }}</td>
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
