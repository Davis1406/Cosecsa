{{-- One Clinical or Viva block of the FCS "View marks" page. $records, $format ('clinical'|'viva'), $total --}}
@php
    $clinicalLabels = ['Overall Professional Capacity and Patient Care', 'Knowledge and Judgement', 'Quality of Response', 'Bedside Manner'];
    $vivaCaseHeadings = [1 => 'Knowledge & Judgement', 2 => 'Quality of Response'];
@endphp
<div class="vr-section">
    <h4>{{ $format === 'clinical' ? 'Clinical' : 'Viva' }}</h4>
    @forelse ($records as $rec)
        @php
            $marks = json_decode($rec->question_mark, true) ?? [];
            $cases = count($marks) <= 4 ? 1 : 2;
            $perCase = $cases === 1 ? count($marks) : intdiv(count($marks), $cases);
        @endphp
        <div class="vr-station"><span>Station {{ $rec->station_id }}</span><small>Total {{ $rec->total }}</small></div>

        @if ($cases === 1)
            @foreach ($marks as $i => $m)
                <div class="vr-row">
                    <span>{{ ($format === 'clinical' && isset($clinicalLabels[$i])) ? $clinicalLabels[$i] : 'Question ' . ($i + 1) }}</span>
                    <span>{{ $m }}</span>
                </div>
            @endforeach
        @else
            @for ($case = 1; $case <= $cases; $case++)
                <div class="vr-case">{{ $format === 'viva' ? ($vivaCaseHeadings[$case] ?? 'Case ' . $case) : 'Case ' . $case }}</div>
                @for ($i = 0; $i < $perCase; $i++)
                    @php $idx = ($case - 1) * $perCase + $i; @endphp
                    @if ($idx < count($marks))
                        <div class="vr-row"><span>Question {{ $i + 1 }}</span><span>{{ $marks[$idx] }}</span></div>
                    @endif
                @endfor
            @endfor
        @endif

        @if (trim((string) $rec->remarks) !== '')
            <p class="vr-note"><b>Remarks:</b> {{ $rec->remarks }}</p>
        @endif
    @empty
        <p class="vr-empty">No {{ $format }} marks recorded.</p>
    @endforelse
    <div class="vr-section-total"><span>{{ $format === 'clinical' ? 'Clinical' : 'Viva' }} total</span><span>{{ $total }}</span></div>
</div>
