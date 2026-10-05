{{--
    The Examiner Training certificate (1440 x 810). Expects $cert (wording with
    {name}/{course}/{date} already filled in), $logo. The admin page re-fills
    these elements live from the form via the data-cert attributes.
--}}
@php
    $show = fn ($key) => trim((string) ($cert[$key] ?? '')) !== '' ? '' : 'display:none;';
@endphp
<div class="lmscert-fit" id="lmscertFit">
    <div class="lmscert-stage" id="lmscertStage">
        <div class="lmscert" id="lmscert">
            <div class="lmscert-bar"></div>
            <div class="lmscert-inner">
                <div>
                    <img class="lmscert-logo" src="{{ $logo }}" alt="COSECSA" crossorigin="anonymous">
                    <div class="lmscert-org" data-cert="org_name" style="{{ $show('org_name') }}">{{ $cert['org_name'] }}</div>
                </div>
                <div class="lmscert-divider"></div>
                <div class="lmscert-heading" data-cert="heading">{{ $cert['heading'] }}</div>
                <div class="lmscert-subtitle" data-cert="subtitle" style="{{ $show('subtitle') }}">{{ $cert['subtitle'] }}</div>
                <div class="lmscert-name" data-cert-name>{{ $name }}</div>
                <div class="lmscert-name-rule"></div>
                <div class="lmscert-body" data-cert="body_text" style="{{ $show('body_text') }}">{{ $cert['body_text'] }}</div>
                <div class="lmscert-course" data-cert="course_name" style="{{ $show('course_name') }}">{{ $cert['course_name'] }}</div>
                <div class="lmscert-detail" data-cert="detail_text" style="{{ $show('detail_text') }}">{{ $cert['detail_text'] }}</div>
                <div class="lmscert-divider"></div>
                <div class="lmscert-sigs">
                    @foreach([1, 2] as $n)
                        <div class="lmscert-sig" data-cert-sig="{{ $n }}" style="{{ trim(($cert["sig{$n}_name"] ?? '').($cert["sig{$n}_title"] ?? '')) === '' ? 'visibility:hidden;' : '' }}">
                            <div class="lmscert-sig-line"></div>
                            <div class="lmscert-sig-name" data-cert="sig{{ $n }}_name">{{ $cert["sig{$n}_name"] }}</div>
                            <div class="lmscert-sig-title" data-cert="sig{{ $n }}_title">{{ $cert["sig{$n}_title"] }}</div>
                        </div>
                    @endforeach
                </div>
                <div class="lmscert-cpd" data-cert-cpd style="{{ $show('cpd_points') }}">
                    <div class="lmscert-cpd-value" data-cert="cpd_points">{{ $cert['cpd_points'] }}</div>
                    <div class="lmscert-cpd-label">CPD Points</div>
                </div>
            </div>
        </div>
    </div>
</div>
