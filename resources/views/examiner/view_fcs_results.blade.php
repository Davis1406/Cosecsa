@extends('layout.app')

@section('content')
@include('examiner._view_style')

<div class="content-wrapper">
    <section class="content" style="padding-top:18px">
        <div class="container-fluid">
            <div class="vr">
                <a class="vr-back" href="{{ url('examiner/results') }}"><span class="fas fa-arrow-left"></span> Back to results</a>

                @if ($candidateResult)
                    <div class="vr-card">
                        <div class="vr-head">
                            <div class="vr-eyebrow">Candidate</div>
                            <div class="vr-name">{{ $candidateResult->candidate_name }}</div>
                            <div class="vr-meta">Group {{ $candidateResult->group_name }}</div>
                        </div>

                        <div class="vr-score">
                            <div>
                                <div class="vr-eyebrow">Overall total</div>
                                <div class="vr-total">{{ $candidateResult->overall_total }}</div>
                            </div>
                            <div class="vr-subs">
                                <div><div class="vr-eyebrow">Clinical</div><div class="vr-sub">{{ $candidateResult->clinical_total }}</div></div>
                                <div><div class="vr-eyebrow">Viva</div><div class="vr-sub">{{ $candidateResult->viva_total }}</div></div>
                            </div>
                        </div>

                        @include('examiner._fcs_records', ['records' => $candidateResult->clinical_records, 'format' => 'clinical', 'total' => $candidateResult->clinical_total])
                        @include('examiner._fcs_records', ['records' => $candidateResult->viva_records, 'format' => 'viva', 'total' => $candidateResult->viva_total])

                        <div class="vr-actions">
                            <a class="vr-btn primary" href="{{ url('examiner/fcs-resubmit/' . $candidateResult->candidate_id) }}">Resubmit</a>
                            <a class="vr-btn ghost" href="{{ url('examiner/results') }}">All results</a>
                        </div>
                    </div>
                @else
                    <div class="vr-card"><div class="vr-section"><p class="vr-empty">No result data found for this candidate.</p></div></div>
                @endif
            </div>
        </div>
    </section>
</div>

    @include('examiner._offline')
@endsection
