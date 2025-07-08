@extends('layout.app')

@section('content')
    <div class="wrapper">

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">

            <!-- Content Header (Page header) -->
            <section class="content-header">
       
            </section>
            <div class="col-md-12">
                @include('_message')
            </div>

            <!-- Main content -->
            <section class="content">
                <div class="container-wrapper">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Confirmed Examiners - {{ now()->year }}</h3>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <table id="examinerconfirmationtable" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Country</th>
                                                <th>Specialty</th>
                                                <th>Availability</th>
                                                <th>MCS Shift</th>
                                                <th>Participation</th>
                                                <th>Hospital</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($getExaminers as $index => $value)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $value->examiner_name ?? '-' }}</td>
                                                    <td>{{ $value->email ?? '-' }}</td>
                                                    <td>{{ $value->country_name ?? '-' }}</td>
                                                    <td>{{ $value->specialty ?? '-' }}</td>
                                                    <td>
                                                        @php
                                                            $availability = [];

                                                            if (!empty($value->exam_availability)) {
                                                                // Try double decode first, then fallback to string cleaning
                                                                $decoded = json_decode($value->exam_availability, true);

                                                                if (is_string($decoded)) {
                                                                    $availability = json_decode($decoded, true) ?: [];
                                                                } elseif (is_array($decoded)) {
                                                                    $availability = $decoded;
                                                                } else {
                                                                    // Fallback: clean escaped quotes and decode
                                                                    $cleaned = str_replace(
                                                                        '\\"',
                                                                        '"',
                                                                        $value->exam_availability,
                                                                    );
                                                                    $availability = json_decode($cleaned, true) ?: [];
                                                                }
                                                            }
                                                        @endphp
                                                        {{ count($availability) ? implode(', ', $availability) : '-' }}
                                                    </td>
                                                    <td>
                                                        @if ($value->shift)
                                                            {{ App\Models\User::getShiftName($value->shift) }}
                                                        @else
                                                            No shifts assigned
                                                        @endif
                                                    </td>
                                                    <td>{{ $value->participation_type ?? '-' }}</td>
                                                    <td>{{ $value->hospital_name ?? '-' }}</td>
                                                    </td>
                                                    <td>
                                                        <a href="{{ url('admin/exams/view_examiner/' . $value->id) }}"
                                                            class="btn btn-sm btn-info">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                        <a href="{{ url('admin/exams/edit_examiner/' . $value->id) }}"
                                                            class="btn btn-sm btn-warning">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                        <a href="{{ url('admin/exams/delete/' . $value->id) }}"
                                                            class="btn btn-sm btn-danger"
                                                            onclick="return confirm('Are you sure?')">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
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

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.filter-button').click(function() {
                var filter = $(this).attr('data-filter');
                if (filter == 'all') {
                    $('.user-row').show();
                } else {
                    $('.user-row').hide();
                    $('.user-row[data-user-type="' + filter + '"]').show();
                }
            });
        });
    </script>
@endsection
