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
                            <a href="{{ url('admin/exams/import') }}" class="btn btn-secondary"
                                style="color:black; background-color: #FEC503; border-color: #FEC503;">Upload Examiners
                                <span class="fas fa-upload"></span></a>
                            <a href="{{ url('admin/exams/add_examiner') }}" class="btn btn-primary"
                                style="background-color: #a02626; border-color: #a02626;">Add New Examiner</a>
                        </div>
                    </div>
                </div><!-- /.container-fluid -->
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
                                    <h3 class="card-title">Examiners - {{ now()->year }}</h3>
                                </div>
                                <!-- /.card-header -->
                                <div class="card-body">
                                    <table id="examinerstable" class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Country</th>
                                                <th>Examiner ID</th>
                                                <th>Exam Group</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($getExaminers as $value)
                                                <tr>
                                                    <td>{{ $value->id }}</td>
                                                    <td>{{ $value->examiner_name }}</td>
                                                    <td>{{ $value->email }}</td>
                                                    <td>{{ $value->country_name }}</td>
                                                    <td>{{ $value->examiner_id }}</td>
                                                    <td>{{ $value->group_name }}</td>
                                                    <td>
                                                        <div class="dropdown">
                                                            <button class="btn btn-sm btn-light border dropdown-toggle action-btn"
                                                                type="button" data-toggle="dropdown"
                                                                aria-haspopup="true" aria-expanded="false">
                                                                <i class="fas fa-ellipsis-v"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                                                <!-- View form -->
                                                                <form
                                                                    action="{{ url("admin/exams/view_examiner/{$value->examin_id}") }}"
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

                                                                <a href="{{ url('admin/exams/edit_examiner/' . $value->examin_id) }}"
                                                                    class="dropdown-item">
                                                                    <i class="fa fa-edit"></i> Edit
                                                                </a>

                                                                <a href="{{ url('admin/exams/delete/' . $value->ex_id) }}"
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

@push('styles')
<style>
    #examinerstable td { vertical-align: middle; }
    .action-btn { padding: 2px 8px; line-height: 1.4; border-radius: 4px; }
    .action-btn:hover { background-color: #f0f0f0; }
    .dropdown-menu { min-width: 130px; font-size: .875rem; }
    .dropdown-item { padding: 6px 14px; }
    .dropdown-item:hover { background-color: #f8f0f0; }
    .paginate_button.active>.page-link { background-color: #a02626 !important; border-color: #a02626 !important; color: white; }
    .paginate_button>.page-link { color: #a02626; }
    .paginate_button>.page-link:focus, .paginate_button.active>.page-link:focus { box-shadow: none !important; outline: none !important; }
</style>
@endpush

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
