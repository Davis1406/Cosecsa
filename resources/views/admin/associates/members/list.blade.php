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
                        <a href="{{url('admin/associates/members/import_members')}}" class="btn btn-secondary" style="color:black; background-color: #FEC503; border-color: #FEC503;">Upload Members <span class="fas fa-upload"></span></a>
                        <a href="{{url('admin/associates/members/add')}}" class="btn btn-primary" style="background-color: #a02626; border-color: #a02626;">Add New Member</a>
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
                                <h3 class="card-title">Members</h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <table id="memberstable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Country</th>
                                            <th>Membership Type</th>
                                            <th>Membership Year</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($getMembers as $value)
                                        <tr>
                                            <td>{{$value->members_id}}</td>
                                            <td>{{$value->member_name}}</td>
                                            <td>{{$value->personal_email}}</td>
                                            <td>{{$value->country_name}}</td>
                                            <td>{{$value->membership_type}}</td>
                                            <td>{{$value->membership_year}}</td>     
                                            <td>
                                                <a href="#" data-id="{{$value->members_id}}" data-name="{{$value->name}}" class="action-icon" data-toggle="popover" data-html="true" data-content='
                                                    <a href="{{url('admin/associates/members/view/'.$value->members_id)}}"><i class="fa fa-eye action-icon"></i> View</a>
                                                    <a href="{{url('admin/associates/members/edit/'.$value->members_id)}}"><i class="fa fa-edit action-icon"></i> Edit</a>
                                                    <a href="{{url('admin/associates/members/delete/'.$value->m_id)}}"><i class="fa fa-trash action-icon"></i> Delete</a>'>
                                                    <i class="fa fa-bars" aria-hidden="true" style="color: #5a6268"></i>
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

@push('styles')
    <style>
        .popover-content a,
        .popover-body a {
            display: block;
            padding: 5px 10px;
            color: #5a6268;
            text-decoration: none;
            border-radius: 3px;
            margin-bottom: 2px;
            transition: all 0.3s ease;
        }

        .popover-content a:hover,
        .popover-body a:hover {
            background-color: #a02626 !important;
            color: #fff !important;
            text-decoration: none;
        }

        .popover-content a i,
        .popover-body a i {
            margin-right: 6px;
            color: inherit;
        }

        .popover-content a:hover i,
        .popover-body a:hover i {
            color: #fff !important;
        }

        .popover-header {
            background-color: #a02626;
            color: #fff;
            border-bottom-color: #a02626;
        }

        .action-icon {
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .action-icon:hover {
            color: #a02626 !important;
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
    $(document).ready(function(){
        $('.filter-button').click(function(){
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
