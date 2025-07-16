@extends('layout.app')

@push('styles')
    <style>
        .action-icon {
            display: block;
            padding: 2px 0;
            color: #333;
            font-size: 14px;
            text-decoration: none;
        }

        .action-icon:hover {
            color: #a02626;
            text-decoration: none;
        }

        .popover {
            min-width: 100px;
        }

        .report-buttons {
            margin-bottom: 20px;
        }

        .report-btn {
            background-color: #a02626;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
            font-size: 14px;
        }

        .report-btn:hover {
            background-color: #8b1f1f;
            color: white;
            text-decoration: none;
        }

        .report-btn i {
            margin-right: 5px;
        }

        .export-btn {
            background-color: #28a745;
        }

        .export-btn:hover {
            background-color: #218838;
        }
    </style>
@endpush

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
                                    <!-- Report Buttons -->
                                    <div class="report-buttons">
                                        <a href="{{ url('admin/exams/visual_report') }}" class="report-btn">
                                            <i class="fa fa-chart-bar"></i> Visual Report
                                        </a>
                                    </div>

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
                                                                $decoded = json_decode($value->exam_availability, true);

                                                                if (is_string($decoded)) {
                                                                    $availability = json_decode($decoded, true) ?: [];
                                                                } elseif (is_array($decoded)) {
                                                                    $availability = $decoded;
                                                                } else {
                                                                    $cleaned = str_replace(
                                                                        '\\"',
                                                                        '"',
                                                                        $value->exam_availability,
                                                                    );
                                                                    $availability = json_decode($cleaned, true) ?: [];
                                                                }
                                                            }
                                                        @endphp

                                                        @if (in_array('Not Available', $availability))
                                                            <span style="color: #a02626; font-weight: 600;">Not
                                                                Available</span>
                                                        @elseif(count($availability))
                                                            {{ implode(', ', $availability) }}
                                                        @else
                                                            -
                                                        @endif
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
                                                    <td>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-light dropdown-toggle"
                                                                type="button" data-toggle="dropdown">
                                                                <i class="fa fa-bars" style="color: #5a6268;"></i>
                                                            </button>
                                                            <div class="dropdown-menu">
                                                                <!-- View form -->
                                                                <form
                                                                    action="{{ url("admin/exams/view_examiner/{$value->id}") }}"
                                                                    method="POST" style="display:inline;">
                                                                    @csrf
                                                                    <input type="hidden" name="from"
                                                                        value="{{ request()->path() }}">
                                                                    @foreach (request()->query() as $key => $val)
                                                                        <input type="hidden" name="{{ $key }}"
                                                                            value="{{ $val }}">
                                                                    @endforeach
                                                                    <button type="submit" class="dropdown-item">
                                                                        <i class="fa fa-eye"></i> View
                                                                    </button>
                                                                </form>

                                                                <a href="{{ url("admin/exams/edit_examiner/$value->id") }}"
                                                                    class="dropdown-item">
                                                                    <i class="fa fa-edit"></i> Edit
                                                                </a>

                                                                <a href="{{ url("admin/exams/delete/$value->id") }}"
                                                                    class="dropdown-item"
                                                                    onclick="return confirm('Are you sure?')">
                                                                    <i class="fa fa-trash"></i> Delete
                                                                </a>
                                                            </div>
                                                        </div>

                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <!-- /.card-body -->
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .dropdown-menu .dropdown-item:hover {
        background-color: #f8f9fa;
        color: #a02626;
    }

    .dropdown-menu .dropdown-item i {
        color: #5a6268;
        margin-right: 6px;
    }

    .dropdown-menu .dropdown-item:hover i {
        color: #a02626;
    }


    .paginate_button.active>.page-link {
        background-color: #a02626 !important;
        border-color: #a02626 !important;
        color: white;
    }

    .paginate_button>.page-link {
        color: #a02626;
    }

    .paginate_button>.page-link:focus,
    .paginate_button.active>.page-link:focus {
        box-shadow: none !important;
        outline: none !important;
    }
</style>
@endpush

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.filter-button').click(function() {
                var filter = $(this).attr('data-filter');
                if (filter === 'all') {
                    $('.user-row').show();
                } else {
                    $('.user-row').hide();
                    $('.user-row[data-user-type="' + filter + '"]').show();
                }
            });

            $('[data-toggle="popover"]').popover({
                placement: 'right',
                trigger: 'focus'
            });
        });
    </script>
@endsection