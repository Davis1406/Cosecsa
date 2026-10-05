@extends('layout.app')

@section('content')
<div class="content-wrapper">
    <section class="content pt-3">
        <div class="container-fluid">
            @include('learning.partials.flash')

            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3" style="gap:12px;">
                <div>
                    <h4 class="mb-0">Your certificate</h4>
                    <div class="text-muted" style="font-size:13px;">{{ $course['title'] }}</div>
                </div>
                <div class="d-flex" style="gap:10px;">
                    <a href="{{ route('examiner.learning') }}" class="btn btn-light">Back to course</a>
                    <button type="button" class="btn btn-primary" onclick="window.print()">Print / Save as PDF</button>
                    <button type="button" class="btn btn-primary" onclick="downloadCertificatePng(this, 'COSECSA-Examiner-Training-Certificate-{{ \Illuminate\Support\Str::slug($name) }}.png')">Download PNG</button>
                </div>
            </div>

            @include('learning.partials.certificate', ['cert' => $cert, 'name' => $name, 'signature' => $signature, 'logo' => asset('dist/img/Cosecsa_Logo.png')])
        </div>
    </section>
</div>
@endsection

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
<link href="{{ asset('dist/css/lms-certificate.css') }}" rel="stylesheet">
@endpush

@push('scripts')
@include('learning.partials.certificate-script')
@endpush
