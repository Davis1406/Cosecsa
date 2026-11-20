@extends('layout.app')

@section('title', 'Fellowship by Election application')

@push('styles')
@include('admin.election_applications._style')
@include('admin.election_applications._detail_style')
@endpush

@section('content')
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header"></section>
        <div class="col-md-12">@include('_message')</div>

        <section class="content">
            <div class="container-wrapper">
            <div class="fe">
@include('admin.election_applications._detail')
            </div>
            </div>
        </section>
    </div>
</div>
@endsection
