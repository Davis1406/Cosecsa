{{-- How each column of the "Part I Results" spreadsheet is used. Shared by
     the upload and preview pages; $present (optional) = headers actually found. --}}
@php
    $groups = [
        'key' => ['label' => 'Match key', 'hint' => 'finds the candidate (entry number + exam year from Class)',
                  'cols' => ['External Ref', 'Class']],
        'imported' => ['label' => 'Imported', 'hint' => 'saved as the candidate\'s Part I result',
                  'cols' => ['P I Points', 'P I Percent', 'P II Points', 'P II Percent', 'Total P (P I + II)', 'Ovrll (avg) (%)', 'Pass Mark', 'Remark', 'Student ID']],
        'compared' => ['label' => 'Compared only', 'hint' => 'differences are flagged, the record is not changed',
                  'cols' => ['Repeat PI', 'MMED', 'Sponsor']],
        'unused' => ['label' => 'Not used', 'hint' => 'shown for reference',
                  'cols' => ['Other Names', 'Last Name', 'Email', 'Organization', 'Gender', 'Country']],
    ];
    $present = isset($present) ? array_map(fn ($h) => strtolower(trim($h)), $present) : null;
@endphp
<div class="p1-legend">
    @foreach($groups as $key => $g)
        <div class="p1-legend-row">
            <div class="p1-legend-label">
                <span class="p1-chip p1-{{ $key }}">{{ $g['label'] }}</span>
                <small class="text-muted d-block">{{ $g['hint'] }}</small>
            </div>
            <div class="p1-legend-cols">
                @foreach($g['cols'] as $c)
                    @php $missing = $present !== null && ! in_array(strtolower($c), $present, true); @endphp
                    <span class="p1-col p1-{{ $key }} {{ $missing ? 'p1-missing' : '' }}" title="{{ $missing ? 'Not in this file' : '' }}">{{ $c }}</span>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

@once
@push('styles')
<style>
    .p1-legend-row { display:flex; gap:1rem; padding:.45rem 0; border-bottom:1px solid #eef2f7; align-items:flex-start; }
    .p1-legend-row:last-child { border-bottom:0; }
    .p1-legend-label { width:210px; flex-shrink:0; font-size:.8rem; }
    .p1-legend-cols { display:flex; flex-wrap:wrap; gap:.35rem; }
    .p1-chip, .p1-col { display:inline-block; font-size:.75rem; font-weight:600; border-radius:4px; padding:.15rem .5rem; border:1px solid transparent; }
    .p1-key      { background:#a02626; color:#fff; border-color:#a02626; }
    .p1-imported { background:#FEC503; color:#3a2a00; border-color:#FEC503; }
    .p1-compared { background:#fff; color:#2d3748; border-color:#2d3748; }
    .p1-unused   { background:#f1f3f5; color:#6b7280; border-color:#e5e7eb; font-weight:500; }
    .p1-missing  { opacity:.45; text-decoration:line-through; }
    body.dark-mode .p1-legend-row { border-bottom-color:#2d3748; }
    body.dark-mode .p1-compared { background:transparent; color:#e2e8f0; border-color:#a0aec0; }
    body.dark-mode .p1-unused { background:#2d3748; color:#a0aec0; border-color:#4a5568; }
</style>
@endpush
@endonce
