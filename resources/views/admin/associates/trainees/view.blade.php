@extends('layout.app')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6" style="text-align: left">
                    <a href="{{url('admin/associates/trainees/trainees')}}" class="btn btn-primary"> <span class="fas fa-arrow-left"></span> Trainees List</a>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
        <!-- general form elements -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Trainee Details</h3>
            </div>

            <div class="card-body">
                <div class="row">
                    @if ($trainee)
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <th>Full Name</th>
                                <td>{{ $trainee->name }}</td>
                            </tr>

                            <tr>
                                <th>Gender</th>
                                <td>{{ $trainee->gender }}</td>
                            </tr>

                            <tr>
                                <th>Email</th>
                                <td>{{ $trainee->personal_email }}</td>
                            </tr>

                            <tr>
                                <th>Admission Number</th>
                                <td>{{ $trainee->entry_number }}</td>
                            </tr>

                            <tr>
                                <th>Programme</th>
                                <td>{{ $trainee->programme_name }}</td>
                            </tr>
                            <tr>
                                <th>Hospital Name</th>
                                <td>{{ $trainee->hospital_name }}</td>
                            </tr>
                            <tr>
                                <th>Country</th>
                                <td>{{ $trainee->country_name }}</td>
                            </tr>
                            <tr>
                                <th>Trainee Status</th>
                                <td>{{ $trainee->status }}</td>
                            </tr>

                            <tr>
                                <th>SFS Username</th>
                                <td>{{ $trainee->user_email }}</td>
                            </tr>
                            <tr>
                                <th>SFS Username</th>
                                <td>{{ $trainee->user_password }}</td>
                            </tr>

                            <tr>
                                <th>Admission Letter Status</th>
                                <td>{{ $trainee->admission_letter_status }}</td>
                            </tr>

                            <tr>
                                <th>Invitation Letter Status</th>
                                <td>{{ $trainee->invitation_letter_status }}</td>
                            </tr>

                            <tr>
                                <th>Admission Year</th>
                                <td>{{ $trainee->admission_year }}</td>
                            </tr>
                            <tr>
                                <th>Exam Year</th>
                                <td>{{ $trainee->exam_year }}</td>
                            </tr>
                            <tr>
                                <th>Programme Duration</th>
                                <td>{{ $trainee->programme_period }} Years</td>
                            </tr>

                            <tr>
                                <th>Invoice Number</th>
                                <td>{{ $trainee->invoice_number}} </td>
                            </tr>

                            <tr>
                                <th>Invoice Date</th>
                                <td>{{ $trainee->invoice_date }} </td>
                            </tr>

                            <tr>
                                <th>Invoice Status</th>
                                <td>{{ $trainee->invoice_status}}</td>
                            </tr>

                            <tr>
                                <th>Sponsor</th>
                                <td>
                                    @if($trainee->sponsor='null')
                                        <span> - </span>
                                     @else
                                    {{ $trainee->sponsor}} 
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <th>Mode Of Payment</th>
                                <td>{{ $trainee->mode_of_payment }} </td>
                            </tr>

                            <tr>
                                <th>Amount Paid</th>
                                <td>{{ $trainee->amount_paid }} </td>
                            </tr>

                            <tr>
                                <th>Date Paid</th>
                                <td>{{ $trainee->payment_date }} </td>
                            </tr>

                        </table>
                    </div>
                    @else
                    <div class="col-md-12">
                        <p>NoTrainee Data found.</p>
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
