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
                        <a href="{{url('admin/associates/trainees/import')}}" class="btn btn-secondary" style="color:black; background-color: #FEC503; border-color: #FEC503;">Upload Trainees <span class="fas fa-upload"></span></a>
                        <a href="{{url('admin/associates/trainees/add')}}" class="btn btn-primary" style="background-color: #a02626; border-color: #a02626;">Add New Trainee</a>
                    </div>
                </div>
            </div><!-- /.container-fluid -->
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-wrapper">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Trainees List</h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <table id="traineestable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Gender</th>
                                            <th>Admission Number</th>
                                            <th>Email</th>
                                            <th>Programme</th>
                                            <th>Hospital Name</th>
                                            <th>Country</th>
                                            <th>Trainee Status</th>
                                            <th>Action</th>
                                            <th>SFS Username</th>
                                            <th>SFS Password</th>
                                            <th>Admission Letter Status</th>
                                            <th>Invitation Letter Status</th>
                                            <th>Admission Year</th>
                                            <th>Exam Year</th>
                                            <th>Programme Duration</th>
                                            <th>Invoice Number</th>
                                            <th>Invoice Date</th>
                                            <th>Invoice Status</th>
                                            <th>Sponsor</th>
                                            <th>Mode of Payment</th>
                                            <th>Amount Paid</th>
                                            <th>Date Paid</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($getRecord as $value)
                                        <tr>
                                            <td>{{$value->id}}</td>
                                            <td>{{$value->name}}</td>
                                            <td>{{$value->gender}}</td>
                                            <td>{{$value->entry_number}}</td>
                                            <td>{{$value->personal_email}}</td>
                                            <td>{{$value->programme_name}}</td>
                                            <td>{{$value->hospital_name}}</td>
                                            <td>{{$value->country_name}}</td>
                                            <td>{{$value->status}}</td>
                                            <td>
                                                <a href="#" data-id="{{$value->id}}" data-name="{{$value->name}}" class="action-icon" data-toggle="popover" data-html="true" data-content='
                                                    <a href="/admin/associates/trainees/view/{{$value->id}}"><i class="fa fa-eye action-icon"></i> View</a>
                                                    <a href="/admin/associates/trainees/edit/{{$value->id}}"><i class="fa fa-edit action-icon"></i> Edit</a>
                                                    <a href="/admin/associates/trainees/delete/{{$value->id}}"><i class="fa fa-trash action-icon"></i> Delete</a>'>
                                                    <i class="fa fa-bars" aria-hidden="true" style="color: #5a6268"></i>
                                                </a>
                                            </td>
                                            <td>{{$value->user_email}}</td>
                                            <td>{{$value->user_password}}</td>
                                            <td>{{$value->admission_letter_status}}</td>
                                            <td>{{$value->invitation_letter_status}}</td>
                                            <td>{{$value->admission_year}}</td>
                                            <td>{{$value->exam_year}}</td>
                                            <td>{{$value->programme_period}}<span> Years</span></td>
                                            <td>{{$value->invoice_number}}</td>
                                            <td>{{$value->invoice_date}}</td>
                                            <td>{{$value->invoice_status}}</td>
                                            <td>{{$value->sponsor}}</td>
                                            <td>{{$value->mode_of_payment}}</td>
                                            <td>{{$value->amount_paid}}</td>
                                            <td>{{$value->payment_date}}</td>
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
