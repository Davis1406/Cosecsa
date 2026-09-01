@extends('layout.app')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6" style="text-align: left">
                    <a href="{{ url('admin/associates/programme-directors/list') }}" class="btn btn-primary">
                        <span class="fas fa-arrow-left"></span> Programme Directors List
                    </a>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            @include('admin._role_switcher', ['relatedProfiles' => $relatedProfiles ?? null, 'currentRole' => 'programme_director'])
        </div>
        <!-- general form elements -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Programme Director Details</h3>
            </div>

            <div class="card-body">
                <div class="row">
                    @if ($pd)
                    <div class="col-md-3">
                        <div class="text-center">
                            <img src="{{ \App\Support\ApiAsset::url($pd->profile_image) }}" alt="Profile Image" class="img-fluid img-thumbnail">
                            <h5 class="mt-2">{{ $pd->name }}</h5>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <table class="table table-bordered">
                            <tr>
                                <th>Full Name</th>
                                <td>{{ $pd->name }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ $pd->user_email }}</td>
                            </tr>

                            <tr>
                                <th>Hospital Name</th>
                                <td>
                                    <span class="ie-field" data-ie="hospital_id" data-ie-type="select"
                                          data-ie-value="{{ $pd->hospital_id ?? '' }}"
                                          data-ie-options="{{ json_encode($hospitals->pluck('name','id')) }}"
                                          data-ie-url="{{ url('admin/associates/programme-directors/'.$pd->programme_director_id.'/quick-update') }}"
                                          data-ie-csrf="{{ csrf_token() }}">
                                        <span class="ie-value">
                                            @if($pd->hospital_id)<a href="{{ url('admin/hospital/view_hospital/'.$pd->hospital_id) }}" style="color:#a02626;font-weight:500;">{{ $pd->hospital_name }}</a>@else{{ $pd->hospital_name }}@endif
                                        </span>
                                        <button class="ie-pencil" type="button" title="Edit hospital"><i class="fas fa-pen"></i></button>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Country</th>
                                <td>
                                    @if($pd->country_id)
                                    <a href="{{ url('admin/countries/view/'.$pd->country_id) }}" style="color:#a02626;font-weight:500;">
                                        {{ $pd->country_name }}
                                    </a>
                                    @else {{ $pd->country_name }} @endif
                                    <small class="text-muted d-block">(derived from hospital)</small>
                                </td>
                            </tr>
                            <tr>
                                <th>PD Phone Number</th>
                                <td>
                                    <span class="ie-field" data-ie="phone_number" data-ie-type="text"
                                          data-ie-value="{{ $pd->phone_number ?? '' }}"
                                          data-ie-url="{{ url('admin/associates/programme-directors/'.$pd->programme_director_id.'/quick-update') }}"
                                          data-ie-csrf="{{ csrf_token() }}">
                                        <span class="ie-value">{{ $pd->phone_number ?? '—' }}</span>
                                        <button class="ie-pencil" type="button" title="Edit phone number"><i class="fas fa-pen"></i></button>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Assistant PD Name</th>
                                <td>
                                    <span class="ie-field" data-ie="assistant_pd" data-ie-type="text"
                                          data-ie-value="{{ $pd->assistant_pd ?? '' }}"
                                          data-ie-url="{{ url('admin/associates/programme-directors/'.$pd->programme_director_id.'/quick-update') }}"
                                          data-ie-csrf="{{ csrf_token() }}">
                                        <span class="ie-value">{{ $pd->assistant_pd ?? '—' }}</span>
                                        <button class="ie-pencil" type="button" title="Edit assistant PD name"><i class="fas fa-pen"></i></button>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Assistant PD Email</th>
                                <td>
                                    <span class="ie-field" data-ie="assistant_email" data-ie-type="email"
                                          data-ie-value="{{ $pd->assistant_email ?? '' }}"
                                          data-ie-url="{{ url('admin/associates/programme-directors/'.$pd->programme_director_id.'/quick-update') }}"
                                          data-ie-csrf="{{ csrf_token() }}">
                                        <span class="ie-value">{{ $pd->assistant_email ?? '—' }}</span>
                                        <button class="ie-pencil" type="button" title="Edit assistant PD email"><i class="fas fa-pen"></i></button>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Mobile Number</th>
                                <td>
                                    <span class="ie-field" data-ie="mobile_no" data-ie-type="text"
                                          data-ie-value="{{ $pd->mobile_no ?? '' }}"
                                          data-ie-url="{{ url('admin/associates/programme-directors/'.$pd->programme_director_id.'/quick-update') }}"
                                          data-ie-csrf="{{ csrf_token() }}">
                                        <span class="ie-value">{{ $pd->mobile_no ?? '—' }}</span>
                                        <button class="ie-pencil" type="button" title="Edit mobile number"><i class="fas fa-pen"></i></button>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>User Type</th>
                                <td>
                                    @if ($pd->user_type == 4)
                                        Programme Director
                                    @else
                                        Unknown
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    @else
                    <div class="col-md-12">
                        <p>No Programme Director Data found.</p>
                    </div>
                    @endif
                </div>
            </div>
            <!-- /.card-body -->
        </div>

        @include('partials.associate_notes', ['associateType' => 'programme_director', 'associateId' => $pd->programme_director_id, 'notes' => $notes])
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

@endsection
