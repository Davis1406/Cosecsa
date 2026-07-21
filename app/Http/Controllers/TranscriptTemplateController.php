<?php

namespace App\Http\Controllers;

use App\Models\TranscriptTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TranscriptTemplateController extends Controller
{
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

        TranscriptTemplate::create($request->only([
            'name', 'document_title', 'intro_text', 'closing_salutation',
            'signatory_name', 'signatory_title', 'institution_name',
        ]) + ['is_default' => $request->boolean('is_default')]);

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

        $template->update($request->only([
            'name', 'document_title', 'intro_text', 'closing_salutation',
            'signatory_name', 'signatory_title', 'institution_name',
        ]) + ['is_default' => $request->boolean('is_default')]);

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

    // Only one template can be the default at a time.
    protected function handleDefault(Request $request, $exceptId = null): void
    {
        if ($request->boolean('is_default')) {
            TranscriptTemplate::when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
                ->update(['is_default' => false]);
        }
    }
}
