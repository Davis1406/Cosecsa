@extends('layout.app')

@section('content')
@php
    $marks = $candidateResult ? json_decode($candidateResult->question_mark, true) : null;
    $grade = ($candidateResult && $candidateResult->source_table === 'mcs_results') ? strtolower(trim((string) $candidateResult->overall)) : null;
    $gradeClass = in_array($grade, ['pass', 'borderline', 'fail'], true) ? $grade : 'none';
@endphp

<style>
    .vr { --vr-surface:#fff; --vr-subtle:#f7f4f3; --vr-border:#ece6e4; --vr-text:#251917; --vr-muted:#6f605d;
          --vr-pass:#1b6b3a; --vr-pass-bg:#e5f3ea; --vr-border-c:#f0d9d6;
          --vr-borderline:#8a5a00; --vr-borderline-bg:#fdf1d6; --vr-fail:#a02626; --vr-fail-bg:#fbe4e2; }
    body.dark-mode .vr { --vr-surface:#1e2330; --vr-subtle:#252c3b; --vr-border:#2d3748; --vr-text:#e2e8f0; --vr-muted:#94a3b8;
          --vr-pass:#7ad69a; --vr-pass-bg:rgba(122,214,154,.14); --vr-borderline:#f3c969; --vr-borderline-bg:rgba(243,201,105,.14);
          --vr-fail:#f48a8a; --vr-fail-bg:rgba(244,138,138,.14); }
    .vr { max-width:720px; margin:0 auto; padding:8px 0 32px; color:var(--vr-text); }
    .vr-back { display:inline-flex; align-items:center; gap:6px; font-size:.9rem; color:var(--vr-muted); text-decoration:none; margin:4px 0 14px; }
    .vr-back:hover { color:#a02626; text-decoration:none; }
    .vr-card { background:var(--vr-surface); border:1px solid var(--vr-border); border-radius:14px; overflow:hidden; }
    .vr-head { padding:22px 24px 18px; }
    .vr-eyebrow { font-size:.72rem; letter-spacing:.08em; text-transform:uppercase; color:var(--vr-muted); }
    .vr-name { font-size:1.45rem; font-weight:700; margin:2px 0 10px; color:var(--vr-text); }
    .vr-chips { display:flex; flex-wrap:wrap; gap:8px; }
    .vr-chip { font-size:.8rem; padding:3px 10px; border-radius:999px; background:var(--vr-subtle); color:var(--vr-muted); }
    .vr-score { display:flex; align-items:flex-end; justify-content:space-between; gap:12px; padding:18px 24px; background:var(--vr-subtle); border-top:1px solid var(--vr-border); border-bottom:1px solid var(--vr-border); }
    .vr-total { font-size:2.6rem; line-height:1; font-weight:700; color:#a02626; }
    body.dark-mode .vr-total { color:#f48a8a; }
    .vr-total small { font-size:.85rem; font-weight:500; color:var(--vr-muted); margin-left:6px; }
    .vr-grade { font-size:.82rem; font-weight:600; text-transform:capitalize; padding:5px 14px; border-radius:999px; }
    .vr-grade.pass { color:var(--vr-pass); background:var(--vr-pass-bg); }
    .vr-grade.borderline { color:var(--vr-borderline); background:var(--vr-borderline-bg); }
    .vr-grade.fail { color:var(--vr-fail); background:var(--vr-fail-bg); }
    .vr-section { padding:18px 24px 6px; }
    .vr-section h4 { font-size:.72rem; letter-spacing:.08em; text-transform:uppercase; color:var(--vr-muted); margin:0 0 6px; font-weight:600; }
    .vr-row { display:flex; justify-content:space-between; align-items:center; padding:11px 0; border-bottom:1px solid var(--vr-border); }
    .vr-row:last-child { border-bottom:0; }
    .vr-row span:first-child { color:var(--vr-muted); }
    .vr-row span:last-child { font-weight:600; font-variant-numeric:tabular-nums; }
    .vr-remarks { margin:0; padding:2px 0 18px; line-height:1.6; white-space:pre-wrap; overflow-wrap:anywhere; color:var(--vr-text); }
    .vr-empty { color:var(--vr-muted); font-style:italic; }
    .vr-actions { display:flex; gap:10px; padding:16px 24px 22px; border-top:1px solid var(--vr-border); }
    .vr-btn { display:inline-block; padding:9px 18px; border-radius:8px; font-weight:600; font-size:.9rem; text-decoration:none; }
    .vr-btn.primary { background:#FEC503; color:#000; }
    .vr-btn.primary:hover { color:#000; text-decoration:none; filter:brightness(.96); }
    .vr-btn.ghost { background:transparent; color:var(--vr-text); border:1px solid var(--vr-border); }
    .vr-btn.ghost:hover { color:#a02626; text-decoration:none; }
    @media (max-width:575px) { .vr-head, .vr-score, .vr-section, .vr-actions { padding-left:16px; padding-right:16px; } .vr-name { font-size:1.2rem; } .vr-total { font-size:2.1rem; } }
</style>

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
                            <div class="vr-chips">
                                <span class="vr-chip">Group {{ $candidateResult->group_name }}</span>
                                <span class="vr-chip">Station {{ $candidateResult->station_id }}</span>
                            </div>
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
