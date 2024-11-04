@extends('layout.app')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6" style="text-align: left">
                    <a href="{{url('admin/associates/candidates/list')}}" class="btn btn-primary" style="background-color: #a02626; border-color:#a02626"> <span class="fas fa-arrow-left"></span> Candidates List</a>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <!-- general form elements -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Candidates Details</h3>
            </div>

            <div class="card-body">
                <div class="row">
                    @if ($candidate)
                    <div class="col-md-12">
                        <table class="table table-bordered">
                            <tr>
                                <th>Full Name</th>
                                <td>{{ $candidate->name }}</td>
                            </tr>

                            <tr>
                                <th>Gender</th>
                                <td>{{ $candidate->gender }}</td>
                            </tr>

                            <tr>
                                <th>Email</th>
                                <td>{{ $candidate->personal_email }}</td>
                            </tr>

                            <tr>
                                <th>Admission Number</th>
                                <td>{{ $candidate->entry_number }}</td>
                            </tr>

                            <tr>
                                <th>Candidate Number</th>
                                <td>{{ $candidate->candidate_id }}</td>
                            </tr>


                            <tr>
                                <th>Group Name</th>
                                <td><span>Group </span>{{ $candidate->group_name }}</td>
                            </tr>

                            <tr>
                                <th>Programme</th>
                                <td>{{ $candidate->programme_name }}</td>
                            </tr>
                            <tr>
                                <th>Hospital Name</th>
                                <td>{{ $candidate->hospital_name }}</td>
                            </tr>
                            <tr>
                                <th>Country</th>
                                <td>{{ $candidate->country_name }}</td>
                            </tr>
                            <tr>
                                <th>MMed Qualified</th>
                                <td>{{ $candidate->mmed }}</td>
                            </tr>

                            <tr>
                                <th>SFS Username</th>
                                <td>{{ $candidate->user_email }}</td>
                            </tr>
                            <tr>
                                <th>SFS Username</th>
                                <td>{{ $candidate->user_password }}</td>
                            </tr>

                            <tr>
                                <th>Repeat P1</th>
                                <td>{{ $candidate->repeat_paper_one }}</td>
                            </tr>

                            <tr>
                                <th>Repeat P2</th>
                                <td>{{ $candidate->repeat_paper_two }}</td>
                            </tr>

                            <tr>
                                <th>User Type</th>
                                <td>
                                    @if ($candidate->user_type == 2)
                                        Trainee
                                    @elseif ($candidate->user_type == 3)
                                        Candidate
                                    @else
                                        Unknown
                                    @endif
                                </td>                            </tr>

                            <tr>
                                <th>Admission Year</th>
                                <td>{{ $candidate->admission_year }}</td>
                            </tr>
                            <tr>
                                <th>Exam Year</th>
                                <td>{{ $candidate->exam_year }}</td>
                            </tr>

                            <tr>
                                <th>Invoice Number</th>
                                <td>{{ $candidate->invoice_number}} </td>
                            </tr>

                            <tr>
                                <th>Invoice Date</th>
                                <td>{{ $candidate->invoice_date }} </td>
                            </tr>

                            <tr>
                                <th>Invoice Status</th>
                                <td>{{ $candidate->invoice_status}}</td>
                            </tr>

                            <tr>
                                <th>Sponsor</th>
                                <td>
                                    @if($candidate->sponsor='null')
                                        <span> - </span>
                                     @else
                                    {{ $candidate->sponsor}} 
                                    @endif
                                </td>
                            </tr>

                            {{-- <tr>
                                <th>Mode Of Payment</th>
                                <td>{{ $trainee->mode_of_payment }} </td>
                            </tr> --}}

                            <tr>
                                <th>Amount Paid</th>
                                <td>{{ $candidate->amount_paid }} </td>
                            </tr>

                            {{-- <tr>
                                <th>Date Paid</th>
                                <td>{{ $trainee->payment_date }} </td>
                            </tr> --}}

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
