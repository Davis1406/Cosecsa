@extends('layout.app')

@section('content')

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6" style="text-align: left">
                    <a href="{{ url('admin/associates/members/list') }}" class="btn btn-primary" style="background-color: #a02626; border-color: #a02626;">
                        <span class="fas fa-arrow-left" ></span> Members List
                    </a>
                </div>
            </div>
        </div><!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <!-- general form elements -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Members Details</h3>
            </div>

            <div class="card-body">
                <div class="row">
                    @if ($member)
                    <div class="col-md-3">
                        <div class="text-center">
                            <img src="{{ asset('dist/img/user.png') }}" alt="Profile Image" class="img-fluid img-thumbnail" style="width: 50%; height:50%">
                            <h5 class="mt-2">{{ $member->member_name }}</h5>
                        </div>
                    </div>
                    <div class="col-md-9">
                        <table class="table table-bordered">
                            <tr>
                                <th>Full Name</th>
                                <td>{{ $member->member_name }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>
                                    <span class="ie-field" data-ie="personal_email" data-ie-type="email"
                                          data-ie-value="{{ $member->personal_email ?? '' }}"
                                          data-ie-url="{{ url('admin/associates/members/'.$member->members_id.'/quick-update') }}"
                                          data-ie-csrf="{{ csrf_token() }}">
                                        <span class="ie-value">{{ $member->personal_email ?? '—' }}</span>
                                        <button class="ie-pencil" type="button" title="Edit email"><i class="fas fa-pen"></i></button>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Country</th>
                                <td>
                                    <span class="ie-field" data-ie="country_id" data-ie-type="select"
                                          data-ie-value="{{ $member->country_id ?? '' }}"
                                          data-ie-options="{{ json_encode($countries->pluck('country_name','id')) }}"
                                          data-ie-url="{{ url('admin/associates/members/'.$member->members_id.'/quick-update') }}"
                                          data-ie-csrf="{{ csrf_token() }}">
                                        <span class="ie-value">
                                            @if($member->country_id)<a href="{{ url('admin/countries/view/'.$member->country_id) }}" style="color:#a02626;font-weight:500;">{{ $member->country_name }}</a>@else{{ $member->country_name }}@endif
                                        </span>
                                        <button class="ie-pencil" type="button" title="Edit country"><i class="fas fa-pen"></i></button>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Gender</th>
                                <td>
                                    <span class="ie-field" data-ie="gender" data-ie-type="select"
                                          data-ie-value="{{ $member->gender ?? '' }}"
                                          data-ie-options='{"Male":"Male","Female":"Female"}'
                                          data-ie-url="{{ url('admin/associates/members/'.$member->members_id.'/quick-update') }}"
                                          data-ie-csrf="{{ csrf_token() }}">
                                        <span class="ie-value">{{ $member->gender ?? '—' }}</span>
                                        <button class="ie-pencil" type="button" title="Edit gender"><i class="fas fa-pen"></i></button>
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <th>Mobile Number</th>
                                <td>
                                    <span class="ie-field" data-ie="phone_number" data-ie-type="text"
                                          data-ie-value="{{ $member->phone_number ?? '' }}"
                                          data-ie-url="{{ url('admin/associates/members/'.$member->members_id.'/quick-update') }}"
                                          data-ie-csrf="{{ csrf_token() }}">
                                        <span class="ie-value">{{ $member->phone_number ?? '—' }}</span>
                                        <button class="ie-pencil" type="button" title="Edit mobile number"><i class="fas fa-pen"></i></button>
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <th>Membership Type</th>
                                <td>{{ $member->membership_type }}</td>
                            </tr>

                            <tr>
                                <th>Member ID</th>
                                <td><strong>{{ $member->member_id_number ?? '—' }}</strong></td>
                            </tr>

                            <tr>
                                <th>Membership Year</th>
                                <td>
                                    <span class="ie-field" data-ie="membership_year" data-ie-type="number"
                                          data-ie-value="{{ $member->membership_year ?? '' }}"
                                          data-ie-url="{{ url('admin/associates/members/'.$member->members_id.'/quick-update') }}"
                                          data-ie-csrf="{{ csrf_token() }}">
                                        <span class="ie-value">{{ $member->membership_year ?? '—' }}</span>
                                        <button class="ie-pencil" type="button" title="Edit membership year"><i class="fas fa-pen"></i></button>
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <th>Admission Year</th>
                                <td>
                                    <span class="ie-field" data-ie="admission_year" data-ie-type="number"
                                          data-ie-value="{{ $member->admission_year ?? '' }}"
                                          data-ie-url="{{ url('admin/associates/members/'.$member->members_id.'/quick-update') }}"
                                          data-ie-csrf="{{ csrf_token() }}">
                                        <span class="ie-value">{{ $member->admission_year ?? '—' }}</span>
                                        <button class="ie-pencil" type="button" title="Edit admission year"><i class="fas fa-pen"></i></button>
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <th>Address</th>
                                <td>
                                    <span class="ie-field" data-ie="address" data-ie-type="text"
                                          data-ie-value="{{ $member->address ?? '' }}"
                                          data-ie-url="{{ url('admin/associates/members/'.$member->members_id.'/quick-update') }}"
                                          data-ie-csrf="{{ csrf_token() }}">
                                        <span class="ie-value">{{ $member->address ?? '—' }}</span>
                                        <button class="ie-pencil" type="button" title="Edit address"><i class="fas fa-pen"></i></button>
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <th>Status</th>
                                <td>
                                    <span class="ie-field" data-ie="status" data-ie-type="select"
                                          data-ie-value="{{ $member->status ?? '' }}"
                                          data-ie-options='{"Active":"Active","Inactive":"Inactive"}'
                                          data-ie-url="{{ url('admin/associates/members/'.$member->members_id.'/quick-update') }}"
                                          data-ie-csrf="{{ csrf_token() }}">
                                        <span class="ie-value">{{ $member->status ?? '—' }}</span>
                                        <button class="ie-pencil" type="button" title="Edit status"><i class="fas fa-pen"></i></button>
                                    </span>
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

        @include('partials.associate_notes', ['associateType' => 'member', 'associateId' => $member->members_id, 'notes' => $notes])
    </section>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

@endsection
