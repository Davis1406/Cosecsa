@extends('layout.app')

@section('content')
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6"></div>
                    <div class="col-sm-6 text-right">
                        <a href="{{ url('admin/associates/alumni/reports') }}" class="btn btn-info mr-2">
                            <i class="fas fa-chart-bar mr-1"></i> Analytics
                        </a>
                        <a href="{{ url('admin/associates/alumni/import') }}" class="btn btn-secondary mr-2"
                           style="color:#333; background-color:#FEC503; border-color:#FEC503;">
                            <i class="fas fa-upload mr-1"></i> Import Alumni
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
                            <div class="card-header"><h3 class="card-title">Alumni — COSECSA Fellows</h3></div>
                            <div class="card-body">
                                <table id="alumnitable" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Country</th>
                                            <th>Specialty / Programme</th>
                                            <th>Fellowship Type</th>
                                            <th>Year</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($alumni as $a)
                                        <tr>
                                            <td class="row-num"></td>
                                            <td>{{ $a->fellow_name ?? '-' }}</td>
                                            <td>{{ $a->personal_email ?? '-' }}</td>
                                            <td>{{ $a->country_name ?? '-' }}</td>
                                            <td>{{ $a->current_specialty ?: ($a->programme_name ? preg_replace('/^FCS\s+/i','', $a->programme_name) : '-') }}</td>
                                            <td>{{ $a->fellowship_type ?? '-' }}</td>
                                            <td>{{ $a->fellowship_year ?? '-' }}</td>
                                            <td class="text-center" style="white-space:nowrap;">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-light border dropdown-toggle action-btn"
                                                            type="button" data-toggle="dropdown"
                                                            aria-haspopup="true" aria-expanded="false">
                                                        <i class="fas fa-ellipsis-v"></i>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                                        <a class="dropdown-item"
                                                           href="{{ url('admin/associates/fellows/view/' . ($a->fellow_id ?? 0)) }}">
                                                            <i class="fas fa-eye text-info mr-2"></i> View
                                                        </a>
                                                        <a class="dropdown-item"
                                                           href="{{ url('admin/associates/fellows/edit/' . ($a->fellow_id ?? 0)) }}">
                                                            <i class="fas fa-edit text-warning mr-2"></i> Edit
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
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
    #alumnitable td { vertical-align: middle; }
    .action-btn { padding: 2px 8px; line-height: 1.4; border-radius: 4px; }
    .action-btn:hover { background-color: #f0f0f0; }
    .dropdown-menu { min-width: 130px; font-size: .875rem; }
    .dropdown-item { padding: 6px 14px; }
    .dropdown-item:hover { background-color: #f8f0f0; }
    .paginate_button.active>.page-link{background-color:#a02626!important;border-color:#a02626!important;color:white}
    .paginate_button>.page-link{color:#a02626}
    .paginate_button>.page-link:focus,.paginate_button.active>.page-link:focus{box-shadow:none!important;outline:none!important}
</style>
@endpush

{{-- DataTable initialised in custom.js (includes skeleton loader + stateSave) --}}
