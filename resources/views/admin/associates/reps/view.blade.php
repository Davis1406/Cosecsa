@extends('layout.app')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6" style="text-align: left">
                    <a href="{{ url('admin/associates/reps/list') }}" class="btn btn-primary">
                        <span class="fas fa-arrow-left"></span> CR's List
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
                <h3 class="card-title">CR Details</h3>
            </div>

            <div class="card-body">
                <div class="row">
                    @if ($countryRep)
                    <div class="col-md-3">
                        <div class="text-center">
                            <img src="{{ asset('storage/' . $countryRep->profile_image) }}" alt="Profile Image" class="img-fluid img-thumbnail">
                            <h5 class="mt-2">{{ $countryRep->name }}</h5>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <table class="table table-bordered">
                            <tr>
                                <th>Full Name</th>
                                <td>{{ $countryRep->name }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ $countryRep->user_email }}</td>
                            </tr>
                            <tr>
                                <th>Country</th>
                                <td>{{ $countryRep->country_name }}</td>
                            </tr>
                            <tr>
                                <th>Cosecsa Email</th>
                                <td>{{ $countryRep->cosecsa_email }}</td>
                            </tr>

                            <tr>
                                <th>Mobile Number</th>
                                <td>{{ $countryRep->mobile_no }}</td>
                            </tr>
                            <tr>
                                <th>User Type</th>
                                <td>
                                    @if ($countryRep->user_type == 5)
                                        Country Representative
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
