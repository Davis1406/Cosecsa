@extends('layout.app')

@php
    $fields = [
        'org_name'    => ['Organisation name', 'Shown under the logo.'],
        'heading'     => ['Heading', 'The large title.'],
        'subtitle'    => ['Line above the name', ''],
        'body_text'   => ['Line below the name', ''],
        'course_name' => ['Course line', 'Use {course} for the course title.'],
        'detail_text' => ['Detail line', 'Use {date} for the date the examiner completed the course.'],
        'sig1_name'   => ['Signature — name', 'Shown centred under a signature line.'],
        'sig1_title'  => ['Signature — title', 'Optional, e.g. a role. Leave blank to show the name only.'],
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

            <div class="row">
                <div class="col-xl-4 mb-3">
                    <div class="card card-pad">
                        <p class="muted mb-3" style="font-size:13px;">
                            Examiners receive this certificate when they finish the course. Edit the wording — the preview updates as you type — then press <strong>Save certificate</strong>.
                            Leave a line blank to hide it. In any line you can use
                            <code>{name}</code> (the examiner), <code>{course}</code> and <code>{date}</code> (completion date).
                            The preview uses <strong>{{ $sampleName }}</strong> and today's date.
                        </p>
                        <form method="POST" action="{{ route('admin.exams.learning.certificate.save') }}" id="certForm">
                            @csrf
                            <input type="hidden" name="sig1_x" id="sigX" value="{{ old('sig1_x', $settings['sig1_x']) }}">
                            <input type="hidden" name="sig1_y" id="sigY" value="{{ old('sig1_y', $settings['sig1_y']) }}">
                            @foreach($fields as $key => [$label, $hint])
                                <div class="mb-3">
                                    <label for="cf-{{ $key }}" style="font-weight:600; font-size:13px; margin-bottom:4px;">{{ $label }}</label>
                                    <input type="text" class="form-control" id="cf-{{ $key }}" name="{{ $key }}"
                                           value="{{ old($key, $settings[$key]) }}" data-default="{{ $defaults[$key] }}" data-field="{{ $key }}">
                                    @if($hint)<small class="text-muted">{{ $hint }}</small>@endif
                                    @error($key)<div class="text-danger" style="font-size:12px;">{{ $message }}</div>@enderror
                                </div>
                            @endforeach
                            @if($signature)
                                <div class="mb-3" id="sigPlacement">
                                    <label for="sigScale" style="font-weight:600; font-size:13px; margin-bottom:4px;">Signature size and position</label>
                                    <div class="d-flex align-items-center" style="gap:10px;">
                                        <input type="range" class="custom-range" id="sigScale" name="sig1_scale" min="30" max="250" step="1" value="{{ old('sig1_scale', $settings['sig1_scale']) }}">
                                        <span id="sigScaleLabel" style="font-size:12px; min-width:40px;"></span>
                                        <button type="button" class="btn btn-light btn-sm" id="sigPosReset">Reset</button>
                                    </div>
                                    <small class="text-muted">Drag the signature on the preview to place it (for example across the line), then press <strong>Save certificate</strong>.</small>
                                    <div class="mt-2"><button type="submit" form="certForm" class="btn btn-primary btn-sm">Save certificate</button></div>
                                </div>
                            @endif
                            <div class="d-flex flex-wrap" style="gap:10px;">
                                <button type="submit" class="btn btn-primary">Save certificate</button>
                                <button type="button" class="btn btn-light" id="certReset">Reset to default wording</button>
                            </div>
                        </form>

                        <hr>
                        <div class="mb-1" style="font-weight:600; font-size:13px;">Signature image</div>
                        <small class="text-muted d-block mb-2">
                            Appears above the signature line. Use a PNG with a transparent background if you can (max 2 MB; PNG, JPG or WebP).
                        </small>
                        @if($signature)
                            <div class="mb-2"><img src="{{ $signature }}" alt="Current signature" style="max-height:70px; max-width:100%; background:#f8f9fa; border:1px solid #e5e7eb; border-radius:6px; padding:6px;"></div>
                        @endif
                        <form method="POST" action="{{ route('admin.exams.learning.certificate.signature') }}" enctype="multipart/form-data" class="d-flex flex-wrap align-items-center" style="gap:10px;">
                            @csrf
                            <input type="file" name="signature" id="sigFile" accept="image/png,image/jpeg,image/webp" required class="form-control-file" style="max-width:100%;">
                            <button type="submit" class="btn btn-primary btn-sm">{{ $signature ? 'Replace signature' : 'Upload signature' }}</button>
                        </form>
                        @error('signature')<div class="text-danger" style="font-size:12px;">{{ $message }}</div>@enderror
                        @if($signature)
                            <form method="POST" action="{{ route('admin.exams.learning.certificate.signature.remove') }}" class="mt-2">
                                @csrf
                                <button type="submit" class="btn btn-light btn-sm" onclick="return confirm('Remove the signature image?');">Remove signature</button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="col-xl-8 mb-3">
                    <div class="cert-preview-col">
                        <div class="d-flex flex-wrap align-items-center justify-content-between mb-2" style="gap:8px;">
                            <h5 style="font-size:16px; font-weight:700; margin:0;">Preview</h5>
                            <div class="d-flex" style="gap:8px;">
                                <button type="button" class="btn btn-light btn-sm" onclick="lmsOpenCertificateModal('{{ route('admin.exams.learning.certificate.modal') }}').catch(function () { alert('The demo could not be loaded.'); })">▶ Demo as examiner</button>
                                <button type="button" class="btn btn-light btn-sm" onclick="downloadCertificatePng(this, 'COSECSA-Examiner-Training-Certificate-preview.png')">Download PNG</button>
                                <button type="submit" form="certForm" class="btn btn-primary btn-sm">Save certificate</button>
                            </div>
                        </div>
                        @include('learning.partials.certificate', ['cert' => $cert, 'name' => $sampleName, 'signature' => $signature, 'logo' => asset('dist/img/Cosecsa_Logo.png'), 'fitTop' => 122])
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@include('learning.partials.certificate-modal-shell')
@endsection

@push('styles')
@include('learning.partials.styles')
@include('learning.admin._styles')
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
<link href="{{ asset('dist/css/lms-certificate.css') }}" rel="stylesheet">
<style>
    /* Keep the preview in view while editing on wide screens */
    @media (min-width: 1200px) { .cert-preview-col { position: sticky; top: 70px; } }
</style>
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
            } else if (key !== 'sig1_name') {
                el.style.display = text ? '' : 'none';
            }
        });
        var hasSig = document.getElementById('cf-sig1_name').value.trim() || document.getElementById('cf-sig1_title').value.trim();
        var sigImg = document.querySelector('[data-cert-sigimg]');
        document.querySelector('[data-cert-sig]').style.visibility = (hasSig || (sigImg && sigImg.style.display !== 'none')) ? '' : 'hidden';
    }

    // Preview a chosen signature file on the certificate before it is uploaded.
    document.getElementById('sigFile').addEventListener('change', function () {
        var file = this.files && this.files[0];
        var img = document.querySelector('[data-cert-sigimg]');
        if (!file || !img) { return; }
        var reader = new FileReader();
        reader.onload = function (e) {
            img.src = e.target.result;
            img.style.display = '';
            document.querySelector('[data-cert-sig]').style.visibility = '';
        };
        reader.readAsDataURL(file);
    });

    // ── Drag / resize the signature ───────────────────────────────────────────
    (function () {
        var img = document.querySelector('[data-cert-sigimg]');
        var xIn = document.getElementById('sigX'), yIn = document.getElementById('sigY');
        var scaleIn = document.getElementById('sigScale'), label = document.getElementById('sigScaleLabel');
        if (!img || !xIn) { return; }

        function clamp(v, lo, hi) { return Math.max(lo, Math.min(hi, v)); }
        function apply() {
            img.style.setProperty('--sx', (parseFloat(xIn.value) || 0) + 'px');
            img.style.setProperty('--sy', (parseFloat(yIn.value) || 0) + 'px');
            if (scaleIn) {
                img.style.setProperty('--ss', (parseFloat(scaleIn.value) || 100) / 100);
                if (label) { label.textContent = Math.round(scaleIn.value) + '%'; }
            }
        }
        apply();
        img.classList.add('is-draggable');

        var start = null;
        img.addEventListener('pointerdown', function (e) {
            e.preventDefault();
            img.setPointerCapture(e.pointerId);
            img.classList.add('is-dragging');
            // the on-screen scale of the 1440px certificate
            var shown = document.getElementById('lmscert').getBoundingClientRect().width / 1440;
            start = { cx: e.clientX, cy: e.clientY, x: parseFloat(xIn.value) || 0, y: parseFloat(yIn.value) || 0, k: shown || 1 };
        });
        img.addEventListener('pointermove', function (e) {
            if (!start) { return; }
            xIn.value = clamp(Math.round(start.x + (e.clientX - start.cx) / start.k), -400, 400);
            yIn.value = clamp(Math.round(start.y + (e.clientY - start.cy) / start.k), -250, 250);
            apply();
        });
        function end() { start = null; img.classList.remove('is-dragging'); }
        img.addEventListener('pointerup', end);
        img.addEventListener('pointercancel', end);

        if (scaleIn) { scaleIn.addEventListener('input', apply); }
        var reset = document.getElementById('sigPosReset');
        if (reset) {
            reset.addEventListener('click', function () {
                xIn.value = 0; yIn.value = 0; scaleIn.value = 100; apply();
            });
        }
    })();

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
