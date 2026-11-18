{{-- One Clinical or Viva block of the FCS "View marks" page. $records, $format ('clinical'|'viva'), $total, $examType --}}
@php
    // the wording of the form the marks were given on (Exams Settings > Form labels)
    $formCfg = \App\Services\ExamFormConfig::get($examType ?? '', $format);
    // station names for this form (an FCS exam has its own list for Clinical and Viva)
    $stationNames = [];
    if (($examType ?? '') !== '' && \Illuminate\Support\Facades\Schema::hasTable('exam_stations')) {
        $stationNames = \Illuminate\Support\Facades\DB::table('exam_stations')->where('exam_type', $examType)
            ->when(\Illuminate\Support\Facades\Schema::hasColumn('exam_stations', 'exam_format'), fn ($q) => $q->where('exam_format', $format))->pluck('name', 'station_number')->all();
    }
@endphp
<div class="vr-section">
    <h4>{{ $format === 'clinical' ? 'Clinical' : 'Viva' }}</h4>
    @forelse ($records as $rec)
        @php
            $marks = json_decode($rec->question_mark, true) ?? [];
            $layout = \App\Services\ExamFormConfig::layout($examType ?? '', $format, $rec->case_layout ?? null, count($marks));
            $offset = 0;
        @endphp
        <div class="vr-station"><span>{{ $stationNames[$rec->station_id] ?? 'Station ' . $rec->station_id }}</span><small>Total {{ $rec->total }}</small></div>

        @foreach ($layout as $caseIndex => $questionCount)
            @if (count($layout) > 1)
                <div class="vr-case">{{ \App\Services\ExamFormConfig::caseTitle($formCfg, $caseIndex + 1) }}</div>
            @endif
            @for ($i = 0; $i < $questionCount; $i++)
                <div class="vr-row"><span>{{ \App\Services\ExamFormConfig::questionLabel($formCfg, $caseIndex + 1, $i + 1) }}</span><span>{{ $marks[$offset + $i] ?? '' }}</span></div>
            @endfor
            @php $offset += $questionCount; @endphp
        @endforeach

        @if (trim((string) $rec->remarks) !== '')
            <p class="vr-note"><b>Remarks:</b> {{ $rec->remarks }}</p>
        @endif
    @empty
        <p class="vr-empty">No {{ $format }} marks recorded.</p>
    @endforelse
    <div class="vr-section-total"><span>{{ $format === 'clinical' ? 'Clinical' : 'Viva' }} total</span><span>{{ $total }}</span></div>
</div>
