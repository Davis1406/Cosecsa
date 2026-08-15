@extends('layout.app')

@section('content')

<div class="wrapper">
    <div class="content-wrapper">

        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-12">
                        <a href="{{ url('admin/associates/trainers/list') }}" class="btn btn-primary">
                            <span class="fas fa-arrow-left"></span> Trainers List
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <div class="col-md-12">
            @include('_message')
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Trainer Details</h3>
                    </div>
                    <div class="card-body">
                        @if ($trainer)
                        <table class="table table-borderless">
                            <tr>
                                <th style="width:220px;">Name</th>
                                <td>{{ $trainer->name }}</td>
                            </tr>
                            <tr>
                                <th>Organisation</th>
                                <td>{{ $trainer->organisation ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td>{{ $trainer->email ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th>Country Attended In</th>
                                <td>
                                    @php $countries = collect($trainer->countries ?? []); @endphp
                                    @forelse($countries as $c)
                                        <span class="badge badge-light border mr-1">{{ $c->name }}</span>
                                    @empty
                                        {{ $trainer->country_name_raw ?: '—' }}
                                    @endforelse
                                </td>
                            </tr>
                            <tr>
                                <th>Specialty</th>
                                <td>
                                    {{ $trainer->specialty ?: '—' }}
                                    @if(!empty($trainer->is_subspecialty))
                                        <span class="badge badge-light border">subspecialty</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>ToT Years Attended</th>
                                <td>
                                    {{-- Full cohort names (e.g. "Pre-2019 (Master Trainer ToT)", "SS2020")
                                         shown here only — the list page shows just the plain year. --}}
                                    @forelse($trainer->tot_years ?? [] as $year)
                                        <span class="badge badge-secondary mr-1">{{ $year->label_full }}</span>
                                    @empty
                                        —
                                    @endforelse
                                </td>
                            </tr>
                            <tr>
                                <th>Master Trainer</th>
                                <td>
                                    @if(!empty($trainer->is_master_trainer))
                                        <span class="badge badge-success">Yes</span>
                                    @else
                                        <span class="badge badge-light border">No</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>SS (Specialty Surgeon)</th>
                                <td>
                                    @if(!empty($trainer->is_specialty_surgeon))
                                        <span class="badge badge-success">Yes</span>
                                    @else
                                        <span class="badge badge-light border">No</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Comment</th>
                                <td>
                                    <span class="ie-field" data-ie="comment" data-ie-type="text"
                                          data-ie-value="{{ $trainer->comment ?? '' }}"
                                          data-ie-url="{{ url('admin/associates/trainers/'.$trainer->id.'/quick-update') }}"
                                          data-ie-csrf="{{ csrf_token() }}">
                                        <span class="ie-value">{{ $trainer->comment ?: '—' }}</span>
                                        <button class="ie-pencil" type="button" title="Edit comment"><i class="fas fa-pen"></i></button>
                                    </span>
                                </td>
                            </tr>
                        </table>
                        @else
                        <p>No Trainer Data found.</p>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

@endsection
