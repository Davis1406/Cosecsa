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
                        <a href="{{url('admin/hospital/add')}}" class="btn btn-primary">Add New Hospital</a>
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
                                <h3 class="card-title">Accredited Hospitals</h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <table id="hospitalTable" class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Hospital Name</th>
                                            <th>Country</th>
                                            <th>Hospital Type</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($getRecord as $value)
                                        <tr>
                                            <td>{{$value->id}}</td>
                                            <td>{{$value->name}}</td>
                                            <td>{{$value->country_name}}</td>
                                            <td>
                                                @if($value->hospital_type == 1)
                                                Government Hospital
                                                @elseif($value->hospital_type == 2)
                                                NGO / Faith based Hospital
                                                @elseif($value->hospital_type == 3)
                                                Private Hospital
                                                @elseif($value->hospital_type == 4)
                                                University Teaching Hospital
                                                @endif
                                            </td>
                                            <td>
                                                @if($value->status == 0)
                                                Active
                                                @else
                                                Inactive
                                                @endif
                                            </td>
                                            <td class="text-center" style="white-space:nowrap;">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-light border dropdown-toggle action-btn"
                                                            type="button" data-toggle="dropdown"
                                                            aria-haspopup="true" aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                                        <a class="dropdown-item" href="{{ url('admin/hospital/view_hospital/' . $value->id) }}">
                                                            <i class="fas fa-eye text-info mr-2"></i> View
                                                        </a>
                                                        <a class="dropdown-item" href="{{ url('admin/hospital/edit_hospital/' . $value->id) }}">
                                                            <i class="fas fa-edit text-warning mr-2"></i> Edit
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <a class="dropdown-item text-danger"
                                                           href="{{ url('admin/hospital/delete/' . $value->id) }}"
                                                           onclick="return confirm('Delete this hospital?')">
                                                            <i class="fas fa-trash mr-2"></i> Delete
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

@push('styles')
<style>
    #hospitalTable td { vertical-align: middle; }
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
@endsection
