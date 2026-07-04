@extends('layout.app')

@push('styles')
<style>
    .country-card { background:#fff; border:1px solid #e9ecef; border-radius:8px; padding:14px 16px;
                    transition:box-shadow .15s; cursor:pointer; text-decoration:none; display:block; color:inherit; }
    .country-card:hover { box-shadow:0 4px 14px rgba(160,38,38,.12); border-color:#d4aaaa; text-decoration:none; color:inherit; }
    .country-card h6 { font-weight:700; color:#222; margin:0 0 8px; font-size:.9rem; }
    .country-mini { display:flex; gap:8px; flex-wrap:wrap; }
    .country-mini-chip { font-size:.72rem; background:#f8f0f0; color:#a02626; border-radius:10px;
                         padding:2px 8px; font-weight:600; }
    .country-mini-chip.grey { background:#f0f0f0; color:#555; }
    .page-header-bar { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.2rem; flex-wrap:wrap; gap:.5rem; }
    body.dark-mode .country-card { background:#374151; border-color:#4a5568; }
    body.dark-mode .country-card h6 { color:#e0e0e0; }
    body.dark-mode .country-mini-chip { background:#4a5568; color:#f87171; }
    body.dark-mode .country-mini-chip.grey { background:#4a5568; color:#9ca3af; }
</style>
@endpush

@section('content')
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header"></section>
        <div class="col-md-12">@include('_message')</div>

        <section class="content">
            <div class="container-wrapper">
                <div class="page-header-bar">
                    <h5 class="mb-0 font-weight-bold" style="color:#a02626;">
                        <i class="fas fa-globe-africa mr-2"></i>Countries
                        <span class="badge badge-secondary ml-1">{{ count($countries) }}</span>
                    </h5>
                </div>

                <div class="row" style="row-gap:.75rem;">
                    @foreach($countries as $c)
                    <div class="col-sm-6 col-md-4 col-lg-3">
                        <a href="{{ url('admin/countries/view/'.$c->id) }}" class="country-card">
                            <h6><i class="fas fa-flag mr-1" style="color:#a02626;"></i>{{ $c->country_name }}</h6>
                            <div class="country-mini">
                                @if($c->hospital_count)
                                <span class="country-mini-chip"><i class="fas fa-hospital mr-1"></i>{{ $c->hospital_count }} Hospital{{ $c->hospital_count != 1 ? 's' : '' }}</span>
                                @endif
                                @if($c->trainee_count)
                                <span class="country-mini-chip grey"><i class="fas fa-user-graduate mr-1"></i>{{ $c->trainee_count }} Trainee{{ $c->trainee_count != 1 ? 's' : '' }}</span>
                                @endif
                                @if($c->fellow_count)
                                <span class="country-mini-chip grey"><i class="fas fa-award mr-1"></i>{{ $c->fellow_count }} Fellow{{ $c->fellow_count != 1 ? 's' : '' }}</span>
                                @endif
                                @if($c->member_count)
                                <span class="country-mini-chip grey"><i class="fas fa-users mr-1"></i>{{ $c->member_count }} Member{{ $c->member_count != 1 ? 's' : '' }}</span>
                                @endif
                                @if(!$c->hospital_count && !$c->trainee_count && !$c->fellow_count && !$c->member_count)
                                <span class="country-mini-chip grey">No records</span>
                                @endif
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
