<?php

namespace App\Http\Controllers;

use App\Models\TranscriptTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class TranscriptTemplateController extends Controller
{
    protected const TEXT_FIELDS = [
        'name', 'document_title', 'intro_text', 'closing_salutation',
        'signatory_name', 'signatory_title', 'institution_name', 'address_text', 'footer_text',
    ];

    protected const IMAGE_FIELDS = ['logo_path', 'watermark_path', 'signature_path', 'stamp_path'];

    public function list()
    {
        $data['header_title'] = 'Transcript Templates';
        $data['templates'] = TranscriptTemplate::orderBy('name')->get();
        return view('admin.settings.transcript_templates.list', $data);
    }

    public function add()
    {
        $data['header_title'] = 'Add Transcript Template';
        $data['template'] = null;
        return view('admin.settings.transcript_templates.form', $data);
    }

    public function insert(Request $request)
    {
        $request->validate([
            'name'            => 'required|string|max:255',
            'signatory_name'  => 'required|string|max:255',
            'signatory_title' => 'required|string|max:255',
        ]);

        $this->handleDefault($request);

        $data = $request->only(self::TEXT_FIELDS) + ['is_default' => $request->boolean('is_default')];
        $data += $this->handleUploads($request);

        TranscriptTemplate::create($data);

        return redirect('admin/settings/transcript-templates')->with('success', 'Template created');
    }

    public function edit($id)
    {
        $data['header_title'] = 'Edit Transcript Template';
        $data['template'] = TranscriptTemplate::findOrFail($id);
        return view('admin.settings.transcript_templates.form', $data);
    }

    public function update(Request $request, $id)
    {
        $template = TranscriptTemplate::findOrFail($id);

        $request->validate([
            'name'            => 'required|string|max:255',
            'signatory_name'  => 'required|string|max:255',
            'signatory_title' => 'required|string|max:255',
        ]);

        $this->handleDefault($request, $id);

        $data = $request->only(self::TEXT_FIELDS) + ['is_default' => $request->boolean('is_default')];
        $data += $this->handleUploads($request, $template);

        $template->update($data);

        return redirect('admin/settings/transcript-templates')->with('success', 'Template updated');
    }

    public function delete($id)
    {
        $template = TranscriptTemplate::findOrFail($id);
        if (DB::table('transcript_records')->where('template_id', $id)->exists()) {
            return back()->with('error', 'This template is already used on an issued transcript and cannot be deleted.');
        }
        $template->delete();
        return redirect('admin/settings/transcript-templates')->with('success', 'Template deleted');
    }

    // Renders the letter with placeholder candidate/course data so admin can
    // see the letterhead, watermark, and signature block without needing a
    // real transcript record.
    public function preview($id)
    {
        $template = TranscriptTemplate::findOrFail($id);

        $record = new \App\Models\TranscriptRecord([
            'full_name'              => 'Jane Sample Doe',
            'gender'                 => 'Female',
            'programme_entry_number' => 'KE/2019/04',
            'medium_of_instruction'  => 'English',
            'programme'              => 'Fellowship in General Surgery',
            'entry_period'           => '2019',
            'completion_period'      => '2023',
            'final_score'            => '79.0',
        ]);

        $courses = collect(\App\Http\Controllers\TranscriptController::DEFAULT_COURSES)
            ->map(fn ($c) => (object) $c);
        $grouped = $courses->groupBy(fn ($c) => $c->section . '|' . $c->subsection);

        $pdf = Pdf::loadView('admin.transcripts.pdf', [
            'record'   => $record,
            'template' => $template,
            'grouped'  => $grouped,
        ])->setPaper('a4');

        $response = $pdf->stream('Template-Preview.pdf');
        $response->headers->set('Cache-Control', 'private, max-age=0, must-revalidate');
        $response->headers->remove('Pragma');
        return $response;
    }

    protected function handleUploads(Request $request, ?TranscriptTemplate $template = null): array
    {
        $paths = [];
        foreach (self::IMAGE_FIELDS as $field) {
            if ($request->hasFile($field)) {
                if ($template && $template->{$field} && Storage::disk('public')->exists($template->{$field})) {
                    Storage::disk('public')->delete($template->{$field});
                }
                $paths[$field] = $request->file($field)->store('transcript_templates', 'public');
            }
        }
        return $paths;
    }

    // Only one template can be the default at a time.
    protected function handleDefault(Request $request, $exceptId = null): void
    {
        if ($request->boolean('is_default')) {
            TranscriptTemplate::when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
                ->update(['is_default' => false]);
        }
    }
}
