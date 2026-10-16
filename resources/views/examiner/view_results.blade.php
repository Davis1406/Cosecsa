@extends('layout.app')

@section('content')
@php
    $marks = $candidateResult ? json_decode($candidateResult->question_mark, true) : null;
    $grade = ($candidateResult && $candidateResult->source_table === 'mcs_results') ? strtolower(trim((string) $candidateResult->overall)) : null;
    $gradeClass = in_array($grade, ['pass', 'borderline', 'fail'], true) ? $grade : 'none';
@endphp

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
                            <div class="vr-meta">Group {{ $candidateResult->group_name }} &middot; Station {{ $candidateResult->station_id }}</div>
                        </div>

                        <div class="vr-score">
                            <div>
                                <div class="vr-eyebrow">Total marks</div>
                                <div class="vr-total">{{ $candidateResult->total }}</div>
                            </div>
                            @if ($gradeClass !== 'none')
                                <span class="vr-grade {{ $gradeClass }}">{{ $grade }}</span>
                            @endif
                        </div>

                        <div class="vr-section">
                            <h4>Marks per question</h4>
                            @if (is_array($marks) && count($marks))
                                @foreach ($marks as $index => $mark)
                                    <div class="vr-row"><span>Question {{ $index + 1 }}</span><span>{{ $mark }}</span></div>
                                @endforeach
                            @else
                                <p class="vr-empty">No question marks available.</p>
                            @endif
                        </div>

                        <div class="vr-section">
                            <h4>Remarks</h4>
                            @if (trim((string) $candidateResult->remarks) !== '')
                                <p class="vr-remarks">{{ $candidateResult->remarks }}</p>
                            @else
                                <p class="vr-empty">No remarks.</p>
                            @endif
                        </div>

                        <div class="vr-actions">
                            <a class="vr-btn primary" href="{{ url('examiner/resubmit/' . $candidateResult->candidate_id . '/' . $candidateResult->station_id) }}">Resubmit</a>
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
