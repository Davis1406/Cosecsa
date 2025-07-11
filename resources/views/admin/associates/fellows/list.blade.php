@extends('layout.app')

@section('content')
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"></div>
                    <div class="col-sm-6 text-right">
                        <a href="{{ url('admin/associates/fellows/import_fellows') }}" class="btn btn-secondary" style="color:black; background-color: #FEC503; border-color: #FEC503;">
                            Upload Fellows <span class="fas fa-upload"></span>
                        </a>
                        <a href="{{ url('admin/associates/fellows/add') }}" class="btn btn-primary" style="background-color: #a02626; border-color: #a02626;">
                            Add New Fellows
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <div class="col-md-12">@include('_message')</div>

        <section class="content">
            <div class="container-wrapper">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header"><h3 class="card-title">Fellows</h3></div>
                            <div class="card-body">
                                <table id="fellowstable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Country</th>
                                            <th>Fellowship Type</th>
                                            <th>Fellowship Year</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($getFellows as $value)
                                        <tr>
                                            <td>{{ $value->fellow_id ?? '-' }}</td>
                                            <td>{{ $value->fellow_name ?? '-' }}</td>
                                            <td>{{ $value->personal_email ?? '-' }}</td>
                                            <td>{{ $value->country_name ?? '-' }}</td>
                                            <td>{{ $value->fellowship_type ?? '-' }}</td>
                                            <td>{{ $value->fellowship_year ?? '-' }}</td>
                                            <td>
                                                <a href="#" class="action-icon" data-toggle="popover" data-html="true" data-content='
                                                    <a href="{{ url("admin/associates/fellows/view/" . ($value->fellow_id ?? 0)) }}">
                                                        <i class="fa fa-eye action-icon"></i> View
                                                    </a>
                                                    <a href="{{ url("admin/associates/fellows/edit/" . ($value->fellow_id ?? 0)) }}">
                                                        <i class="fa fa-edit action-icon"></i> Edit
                                                    </a>
                                                    <a href="{{ url("admin/associates/fellows/delete/" . ($value->f_id ?? 0)) }}"
                                                       onclick="return confirm(&quot;Are you sure you want to delete this fellow?&quot;)">
                                                        <i class="fa fa-trash action-icon"></i> Delete
                                                    </a>'>
                                                    <i class="fa fa-bars" style="color: #5a6268"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div> <!-- /.card-body -->
                        </div> <!-- /.card -->
                    </div> <!-- /.col -->
                </div> <!-- /.row -->
            </div> <!-- /.container-wrapper -->
        </section>
    </div>
</div>
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
    $(document).ready(function () {
        // Filter button logic (optional if used)
        $('.filter-button').click(function () {
            var filter = $(this).attr('data-filter');
            if (filter === 'all') {
                $('.user-row').show();
            } else {
                $('.user-row').hide();
                $('.user-row[data-user-type="' + filter + '"]').show();
            }
        });

        // Only initialize popovers that have content
        $('[data-toggle="popover"]').each(function () {
            var content = $(this).attr('data-content');
            if (content && content.trim() !== '') {
                $(this).popover();
            }
        });
    });
</script>
@endsection
