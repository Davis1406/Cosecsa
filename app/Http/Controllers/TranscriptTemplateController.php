<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Request;

class TranscriptTemplateController extends Controller
{
    protected const TEXT_FIELDS = [
        'name', 'document_title', 'intro_text', 'closing_salutation',
        'signatory_name', 'signatory_title', 'institution_name', 'address_text', 'footer_text',
        'is_default',
    ];

    protected const IMAGE_FIELDS = ['logo_path', 'watermark_path', 'signature_path', 'stamp_path'];

    public function __construct(private ApiClient $api) {}

    public function list()
    {
        $response = $this->api->get('settings/transcript-templates');
        $data = $response->object();

        return view('admin.settings.transcript_templates.list', [
            'header_title' => 'Transcript Templates',
            'templates'    => collect($data->templates ?? []),
        ]);
    }

    public function add()
    {
        return view('admin.settings.transcript_templates.form', [
            'header_title' => 'Add Transcript Template',
            'template'     => null,
        ]);
    }

    public function insert(Request $request)
    {
        $files = [];
        foreach (self::IMAGE_FIELDS as $field) {
            if ($request->hasFile($field)) {
                $files[$field] = $request->file($field);
            }
        }

        $response = $this->api->postWithFile(
            'settings/transcript-templates',
            $request->only(self::TEXT_FIELDS),
            $files
        );

        if ($response->failed()) {
            return back()->withInput()->with('error', $response->json('message') ?? 'Error creating template.');
        }

        return redirect('admin/settings/transcript-templates')->with('success', 'Template created');
    }

    public function edit($id)
    {
        $response = $this->api->get("settings/transcript-templates/{$id}");
        $data = $response->object();

        return view('admin.settings.transcript_templates.form', [
            'header_title' => 'Edit Transcript Template',
            'template'     => $data->template,
        ]);
    }

    public function update(Request $request, $id)
    {
        $files = [];
        foreach (self::IMAGE_FIELDS as $field) {
            if ($request->hasFile($field)) {
                $files[$field] = $request->file($field);
            }
        }

        $response = $this->api->postWithFile(
            "settings/transcript-templates/{$id}",
            $request->only(self::TEXT_FIELDS),
            $files
        );

        if ($response->failed()) {
            return back()->withInput()->with('error', $response->json('message') ?? 'Error updating template.');
        }

        return redirect('admin/settings/transcript-templates')->with('success', 'Template updated');
    }

    public function delete($id)
    {
        $response = $this->api->delete("settings/transcript-templates/{$id}");

        if ($response->failed()) {
            return back()->with('error', $response->json('message') ?? 'Could not delete template.');
        }

        return redirect('admin/settings/transcript-templates')->with('success', 'Template deleted');
    }

    public function preview($id)
    {
        $response = $this->api->get("settings/transcript-templates/{$id}/preview");

        return response($response->body(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Template-Preview.pdf"',
            'Cache-Control'       => 'private, max-age=0, must-revalidate',
        ]);
    }
}
