<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: Arial, sans-serif; font-size: 11px; color: #222; }
    .generated { text-align: right; font-size: 9px; color: #666; margin-bottom: 10px; }
    h1 { text-align: center; font-size: 16px; letter-spacing: 1px; margin-bottom: 18px; }
    h2 { font-size: 12px; text-transform: uppercase; letter-spacing: .5px; border-bottom: 1px solid #999; padding-bottom: 3px; margin-top: 22px; }
    table.details { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
    table.details td { border: 1px solid #999; padding: 5px 8px; vertical-align: top; }
    table.details td.label { width: 30%; font-weight: bold; background: #f5f5f5; }
    table.courses { width: 100%; border-collapse: collapse; margin-top: 6px; }
    table.courses th, table.courses td { border: 1px solid #999; padding: 5px 8px; text-align: left; }
    table.courses th { background: #f5f5f5; }
    tr.section-row td { font-weight: bold; background: #eee; }
    .closing { margin-top: 40px; }
    .signatory { margin-top: 36px; }
    .signatory .name { font-weight: bold; }
</style>
</head>
<body>
    <div class="generated">Generated on: {{ now()->format('d/m/Y') }}</div>

    <h1>{{ $template->document_title ?? 'TRANSCRIPT OF TRAINING' }}</h1>

    @if(!empty($template->intro_text))
        <p>{{ $template->intro_text }}</p>
    @endif

    <h2>Candidate Details</h2>
    <table class="details">
        <tr><td class="label">Name</td><td>{{ $record->full_name }}</td></tr>
        <tr><td class="label">Gender</td><td>{{ $record->gender ?: '—' }}</td></tr>
        <tr><td class="label">Programme Entry Number</td><td>{{ $record->programme_entry_number ?: '—' }}</td></tr>
        <tr><td class="label">Medium of Instruction</td><td>{{ $record->medium_of_instruction ?: 'English' }}</td></tr>
        <tr><td class="label">Programme</td><td>{{ $record->programme ?: '—' }}</td></tr>
        <tr><td class="label">Entry Year</td><td>{{ $record->entry_period ?: '—' }}</td></tr>
        <tr><td class="label">Completion Year</td><td>{{ $record->completion_period ?: '—' }}</td></tr>
        <tr><td class="label">Final Score (%)</td><td>{{ $record->final_score ?: '—' }}</td></tr>
    </table>

    <h2>Programme Details</h2>
    <table class="courses">
        <thead>
            <tr><th>Course Name</th><th style="width:22%;">Academic Year</th><th style="width:18%;">Result</th></tr>
        </thead>
        <tbody>
            @forelse($grouped as $key => $rows)
                @php [$section, $subsection] = explode('|', $key); @endphp
                @if($section || $subsection)
                    <tr class="section-row">
                        <td colspan="3">{{ trim($section) }}{{ $subsection ? ' — '.trim($subsection) : '' }}</td>
                    </tr>
                @endif
                @foreach($rows as $row)
                    <tr>
                        <td>{{ $row->course_name }}</td>
                        <td>{{ $row->academic_year ?: '—' }}</td>
                        <td>{{ $row->result ?: '—' }}</td>
                    </tr>
                @endforeach
            @empty
                <tr><td colspan="3">No course records on file.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="closing">
        <p>{{ $template->closing_salutation ?? 'Yours Sincerely,' }}</p>
        <div class="signatory">
            <p class="name">{{ $template->signatory_name ?? '' }}</p>
            <p>{{ $template->signatory_title ?? '' }}</p>
            <p>{{ $template->institution_name ?? 'College of Surgeons of East, Central and Southern Africa' }}</p>
        </div>
    </div>
</body>
</html>
