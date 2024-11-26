@extends('layout.app')

@section('content')
    <div class="wrapper">

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">

            <div class="col-md-12">
                @include('_message')
            </div>

            <!-- Main content -->
            <section class="content">
                <div class="container-wrapper">
                    <div class="row">
                        <div class="col-12">
                            <div class="card" style="margin-top: 20px">
                                <div class="card-header">
                                    <h3 class="card-title">Candidates Results</h3>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <table id="resultstable" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Candidate ID</th>
                                                <th>Group ID</th>
                                                <th>Question 1</th>
                                                <th>Question 2</th>
                                                <th>Question 3</th>
                                                <th>Question 4</th>
                                                <th>Question 5</th>
                                                <th>Total Marks</th>
                                                <th>Overall Grade</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($getRecord as $value)
                                                <tr>
                                                    <td>{{ $value->candidate_name }}</td>
                                                    <td>Group {{ $value->group_name }}</td>

                                                    @php
                                                        $marks = json_decode($value->question_mark, true);
                                                    @endphp
                                                    @for ($i = 0; $i < 5; $i++)
                                                        <td>{{ isset($marks[$i]) ? $marks[$i] : '-' }}</td>
                                                        <!-- Display each mark -->
                                                    @endfor
                                                    <td><b>{{ $value->total }}</b></td>
                                                    <td>{{ $value->overall }}</td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a class="btn btn-info btn-sm" style="margin-right: 5px"
                                                                href="{{ url('examiner/view_results/' . $value->candidate_id . '/' . $value->station_id) }}">
                                                                View
                                                            </a>
                                                            <a class="btn btn-warning btn-sm"
                                                                href="{{ url('examiner/resubmit/' . $value->candidate_id . '/' . $value->station_id) }}">
                                                                Resubmit
                                                            </a>
                                                        </div>
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
