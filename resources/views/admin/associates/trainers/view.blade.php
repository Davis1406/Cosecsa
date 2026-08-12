@extends('layout.app')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6" style="text-align: left">
                    <a href="{{ url('admin/associates/trainers/list') }}" class="btn btn-primary">
                        <span class="fas fa-arrow-left"></span> Trainers List
                    </a>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            @include('admin._role_switcher', ['relatedProfiles' => $relatedProfiles ?? null, 'currentRole' => 'trainer'])
        </div>
        <!-- general form elements -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Trainer Details</h3>
            </div>

            <div class="card-body">
                <div class="row">
                    @if ($trainer)
                    <div class="col-md-3">
                        <div class="text-center">
                            <img src="{{ \App\Support\ApiAsset::url($trainer->profile_image) }}" alt="Profile Image" class="img-fluid img-thumbnail">
                            <h5 class="mt-2">{{ $trainer->name }}</h5>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <table class="table table-bordered">
                            <tr>
                                <th>Full Name</th>
                                <td>{{ $trainer->name }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ $trainer->user_email }}</td>
                            </tr>

                            <tr>
                                <th>Hospital Name</th>
                                <td>
                                    <span class="ie-field" data-ie="hospital_id" data-ie-type="select"
                                          data-ie-value="{{ $trainer->hospital_id ?? '' }}"
                                          data-ie-options="{{ json_encode($hospitals->pluck('name','id')) }}"
                                          data-ie-url="{{ url('admin/associates/trainers/'.$trainer->trainer_id.'/quick-update') }}"
                                          data-ie-csrf="{{ csrf_token() }}">
                                        <span class="ie-value">
                                            @if($trainer->hospital_id)<a href="{{ url('admin/hospital/view_hospital/'.$trainer->hospital_id) }}" style="color:#a02626;font-weight:500;">{{ $trainer->hospital_name }}</a>@else{{ $trainer->hospital_name }}@endif
                                        </span>
                                        <button class="ie-pencil" type="button" title="Edit hospital"><i class="fas fa-pen"></i></button>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Country</th>
                                <td>
                                    @if($trainer->country_id)
                                    <a href="{{ url('admin/countries/view/'.$trainer->country_id) }}" style="color:#a02626;font-weight:500;">
                                        {{ $trainer->country_name }}
                                    </a>
                                    @else {{ $trainer->country_name }} @endif
                                    <small class="text-muted d-block">(derived from hospital)</small>
                                </td>
                            </tr>
                            <tr>
                                <th>PD Phone Number</th>
                                <td>
                                    <span class="ie-field" data-ie="phone_number" data-ie-type="text"
                                          data-ie-value="{{ $trainer->phone_number ?? '' }}"
                                          data-ie-url="{{ url('admin/associates/trainers/'.$trainer->trainer_id.'/quick-update') }}"
                                          data-ie-csrf="{{ csrf_token() }}">
                                        <span class="ie-value">{{ $trainer->phone_number ?? '—' }}</span>
                                        <button class="ie-pencil" type="button" title="Edit phone number"><i class="fas fa-pen"></i></button>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Assistant PD Name</th>
                                <td>
                                    <span class="ie-field" data-ie="assistant_pd" data-ie-type="text"
                                          data-ie-value="{{ $trainer->assistant_pd ?? '' }}"
                                          data-ie-url="{{ url('admin/associates/trainers/'.$trainer->trainer_id.'/quick-update') }}"
                                          data-ie-csrf="{{ csrf_token() }}">
                                        <span class="ie-value">{{ $trainer->assistant_pd ?? '—' }}</span>
                                        <button class="ie-pencil" type="button" title="Edit assistant PD name"><i class="fas fa-pen"></i></button>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Assistant PD Email</th>
                                <td>
                                    <span class="ie-field" data-ie="assistant_email" data-ie-type="email"
                                          data-ie-value="{{ $trainer->assistant_email ?? '' }}"
                                          data-ie-url="{{ url('admin/associates/trainers/'.$trainer->trainer_id.'/quick-update') }}"
                                          data-ie-csrf="{{ csrf_token() }}">
                                        <span class="ie-value">{{ $trainer->assistant_email ?? '—' }}</span>
                                        <button class="ie-pencil" type="button" title="Edit assistant PD email"><i class="fas fa-pen"></i></button>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Mobile Number</th>
                                <td>
                                    <span class="ie-field" data-ie="mobile_no" data-ie-type="text"
                                          data-ie-value="{{ $trainer->mobile_no ?? '' }}"
                                          data-ie-url="{{ url('admin/associates/trainers/'.$trainer->trainer_id.'/quick-update') }}"
                                          data-ie-csrf="{{ csrf_token() }}">
                                        <span class="ie-value">{{ $trainer->mobile_no ?? '—' }}</span>
                                        <button class="ie-pencil" type="button" title="Edit mobile number"><i class="fas fa-pen"></i></button>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>User Type</th>
                                <td>
                                    @if ($trainer->user_type == 4)
                                        Trainer
                                    @else
                                        Unknown
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    @else
                    <div class="col-md-12">
                        <p>No Trainer Data found.</p>
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
