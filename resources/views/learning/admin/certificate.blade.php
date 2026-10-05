@extends('layout.app')

@php
    $fields = [
        'org_name'    => ['Organisation name', 'Shown under the logo.'],
        'heading'     => ['Heading', 'The large title.'],
        'subtitle'    => ['Line above the name', ''],
        'body_text'   => ['Line below the name', ''],
        'course_name' => ['Course line', 'Use {course} for the course title.'],
        'detail_text' => ['Detail line', 'Use {date} for the date the examiner completed the course.'],
        'sig1_name'   => ['Left signature — name', ''],
        'sig1_title'  => ['Left signature — title', ''],
        'sig2_name'   => ['Right signature — name', ''],
        'sig2_title'  => ['Right signature — title', ''],
        'cpd_points'  => ['CPD points', 'Leave blank to hide the badge.'],
    ];
    $cert = \App\Support\LearningView::certificateText($settings, $sampleName, $course['title'], $sampleDate);
@endphp

@section('content')
<div class="content-wrapper">
    @include('learning.admin._head', ['title' => 'Examiner Training', 'subtitle' => 'Completion certificate'])
    <section class="content">
        <div class="container-fluid lms">
            @include('learning.partials.flash')
            @include('learning.admin._tabs')

            <div class="card card-pad mb-3">
                <p class="muted mb-3" style="font-size:13px;">
                    Examiners receive this certificate when they finish the course. Edit the wording below — the preview updates as you type.
                    Leave a line blank to hide it. In any line you can use
                    <code>{name}</code> (the examiner), <code>{course}</code> and <code>{date}</code> (completion date).
                    The preview uses <strong>{{ $sampleName }}</strong> and today's date.
                </p>
                <form method="POST" action="{{ route('admin.exams.learning.certificate.save') }}" id="certForm">
                    @csrf
                    <div class="row">
                        @foreach($fields as $key => [$label, $hint])
                            <div class="col-md-6 mb-3">
                                <label for="cf-{{ $key }}" style="font-weight:600; font-size:13px; margin-bottom:4px;">{{ $label }}</label>
                                <input type="text" class="form-control" id="cf-{{ $key }}" name="{{ $key }}"
                                       value="{{ old($key, $settings[$key]) }}" data-default="{{ $defaults[$key] }}" data-field="{{ $key }}">
                                @if($hint)<small class="text-muted">{{ $hint }}</small>@endif
                                @error($key)<div class="text-danger" style="font-size:12px;">{{ $message }}</div>@enderror
                            </div>
                        @endforeach
                    </div>
                    <div class="d-flex" style="gap:10px;">
                        <button type="submit" class="btn btn-primary">Save wording</button>
                        <button type="button" class="btn btn-light" id="certReset">Reset to default wording</button>
                    </div>
                </form>
            </div>

            <div class="card card-pad mb-3">
                <h5 style="font-size:16px; font-weight:700; margin-bottom:12px;">Preview</h5>
                @include('learning.partials.certificate', ['cert' => $cert, 'name' => $sampleName, 'logo' => asset('dist/img/Cosecsa_Logo.png')])
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
@include('learning.partials.styles')
@include('learning.admin._styles')
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
<link href="{{ asset('dist/css/lms-certificate.css') }}" rel="stylesheet">
@endpush

@push('scripts')
@include('learning.partials.certificate-script')
<script>
(function () {
    var vars = {
        '{name}': @json($sampleName),
        '{course}': @json($course['title']),
        '{date}': @json(\Illuminate\Support\Carbon::parse($sampleDate)->format('j F Y'))
    };
    function fill(text) {
        return String(text).replace(/\{(name|course|date)\}/g, function (m) { return vars[m]; });
    }

    function refresh() {
        document.querySelectorAll('#certForm [data-field]').forEach(function (input) {
            var key = input.getAttribute('data-field');
            var text = fill(input.value).trim();
            var el = document.querySelector('#lmscert [data-cert="' + key + '"]');
            if (!el) { return; }
            el.textContent = text;
            if (key === 'cpd_points') {
                document.querySelector('[data-cert-cpd]').style.display = text ? '' : 'none';
            } else if (key.indexOf('sig') !== 0) {
                el.style.display = text ? '' : 'none';
            }
        });
        [1, 2].forEach(function (n) {
            var has = document.getElementById('cf-sig' + n + '_name').value.trim() || document.getElementById('cf-sig' + n + '_title').value.trim();
            document.querySelector('[data-cert-sig="' + n + '"]').style.visibility = has ? '' : 'hidden';
        });
    }

    document.getElementById('certForm').addEventListener('input', refresh);
    document.getElementById('certReset').addEventListener('click', function () {
        document.querySelectorAll('#certForm [data-field]').forEach(function (input) {
            input.value = input.getAttribute('data-default');
        });
        refresh();
    });
    refresh();
})();
</script>
@endpush
