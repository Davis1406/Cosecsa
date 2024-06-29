@extends('layout.app')

@section('content')

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

    <!-- Main content -->
    <section class="content">
        <!-- general form elements -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Hospital Details</h3>
            </div>

            <div class="card-body">
                <div class="row">
                    @if ($hospital)
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <th>Hospital Name</th>
                                <td>{{ $hospital->name }}</td>
                            </tr>
                            <tr>
                                <th>Country</th>
                                <td>{{ $hospital->country_name }}</td>
                            </tr>
                            <tr>
                                <th>Hospital Type</th>
                                <td>
                                    @if($hospital->hospital_type == 1)
                                    Government Hospital
                                    @elseif($hospital->hospital_type == 2)
                                    NGO / Faith based Hospital
                                    @elseif($hospital->hospital_type == 3)
                                    Private Hospital
                                    @elseif($hospital->hospital_type == 4)
                                    University Teaching Hospital	
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Status</th>
                                <td>
                                    @if($hospital->status == 0)
                                    Active
                                    @else
                                    Inactive
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    @else
                    <div class="col-md-12">
                        <p>No hospital data found.</p>
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
