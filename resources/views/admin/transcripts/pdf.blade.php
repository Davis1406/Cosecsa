<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    @page { margin: 90px 50px 70px 50px; }
    body { font-family: Arial, sans-serif; font-size: 11px; color: #222; }

    .watermark { position: fixed; top: 260px; left: 150px; width: 300px; opacity: 0.08; z-index: -10; }

    .letterhead { position: fixed; top: -80px; left: -30px; right: -30px; }
    .letterhead table { width: 100%; border-collapse: collapse; }
    .letterhead .logo-cell { width: 70px; }
    .letterhead .logo-cell img { width: 60px; }
    .letterhead .title-cell { text-align: center; }
    .letterhead .name { font-weight: bold; font-size: 13px; color: #a02626; }
    .letterhead .address { font-size: 9px; color: #444; }
    .letterhead .rule { border-bottom: 2px solid #a02626; margin-top: 4px; }

    .page-footer { position: fixed; bottom: -50px; left: -30px; right: -30px; font-size: 8px; color: #666; text-align: center; border-top: 1px solid #ccc; padding-top: 4px; }

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
    .sign-block { margin-top: 10px; }
    .sign-block img.signature { height: 45px; }
    .sign-block img.stamp { height: 70px; margin-left: 30px; }
    .signatory { margin-top: 6px; }
    .signatory .name { font-weight: bold; }
</style>
</head>
<body>
    @if(!empty($template->watermark_path))
        <img class="watermark" src="{{ storage_path('app/public/'.$template->watermark_path) }}">
    @endif

    @if(!empty($template->logo_path) || !empty($template->institution_name))
    <div class="letterhead">
        <table>
            <tr>
                @if(!empty($template->logo_path))
                <td class="logo-cell"><img src="{{ storage_path('app/public/'.$template->logo_path) }}"></td>
                @endif
                <td class="title-cell">
                    <div class="name">{{ $template->institution_name }}</div>
                    @if(!empty($template->address_text))
                        <div class="address">{{ $template->address_text }}</div>
                    @endif
                </td>
                @if(!empty($template->logo_path))
                <td class="logo-cell"></td>
                @endif
            </tr>
        </table>
        <div class="rule"></div>
    </div>
    @endif

    @if(!empty($template->footer_text))
    <div class="page-footer">{{ $template->footer_text }}</div>
    @endif

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
        <div class="sign-block">
            @if(!empty($template->signature_path))
                <img class="signature" src="{{ storage_path('app/public/'.$template->signature_path) }}">
            @endif
            @if(!empty($template->stamp_path))
                <img class="stamp" src="{{ storage_path('app/public/'.$template->stamp_path) }}">
            @endif
        </div>
        <div class="signatory">
            <p class="name">{{ $template->signatory_name ?? '' }}</p>
            <p>{{ $template->signatory_title ?? '' }}</p>
            <p>{{ $template->institution_name ?? 'College of Surgeons of East, Central and Southern Africa' }}</p>
        </div>
    </div>
</body>
</html>
