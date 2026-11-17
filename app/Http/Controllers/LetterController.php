<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use App\Services\LetterRecipientResolver;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class LetterController extends Controller
{
    public function __construct(private ApiClient $api) {}

    public function index()
    {
        $response = $this->api->get('letters/');
        $data = $response->object();

        return view('letters.index', [
            'header_title'     => 'College Letters',
            'templates'        => collect($data->templates ?? []),
            'recentDispatches' => collect($data->recent_dispatches ?? []),
        ]);
    }

    public function create()
    {
        $response = $this->api->get('letters/create');

        return view('letters.form', [
            'header_title' => 'New Letter Template',
            'template'     => null,
            'sources'      => LetterRecipientResolver::SOURCES,
            'collegeStampAvailable' => (bool) $response->json('college_stamp_available'),
        ]);
    }

    /**
     * What the template form sends to the API. Files (signature, stamp) go as a multipart upload, and since a browser
     * sends nothing for an unticked checkbox the API is told the "Active" box was on the page.
     */
    private function templatePayload(Request $request): array
    {
        return $request->only([
            'name', 'subject', 'pdf_body', 'email_body', 'recipient_source', 'legacy_status_field',
            'signatory_name', 'signatory_title', 'remove_signature', 'remove_stamp', 'use_college_stamp',
        ]) + ['is_active_form' => '1', 'is_active' => $request->boolean('is_active') ? '1' : null];
    }

    private function templateFiles(Request $request): array
    {
        $files = [];
        foreach (['signature', 'stamp'] as $field) {
            if ($request->hasFile($field)) {
                $files[$field] = $request->file($field);
            }
        }

        return $files;
    }

    public function store(Request $request)
    {
        $response = $this->api->postWithFile('letters/', $this->templatePayload($request), $this->templateFiles($request));

        if ($response->failed()) {
            return back()->withInput()->with('error', $response->json('message') ?? 'Error creating template.');
        }

        return redirect('admin/letters')->with('success', 'Letter template created.');
    }

    public function edit($id)
    {
        $response = $this->api->get("letters/{$id}/edit");
        $data = $response->object();

        return view('letters.form', [
            'header_title' => 'Edit Letter Template',
            'template'     => $data->template,
            'sources'      => (array) ($data->sources ?? []),
            'collegeStampAvailable' => (bool) ($data->college_stamp_available ?? false),
        ]);
    }

    public function update(Request $request, $id)
    {
        $response = $this->api->postWithFile("letters/{$id}/update", $this->templatePayload($request), $this->templateFiles($request));

        if ($response->failed()) {
            return back()->withInput()->with('error', $response->json('message') ?? 'Error updating template.');
        }

        return redirect('admin/letters')->with('success', 'Letter template updated.');
    }

    public function destroy($id)
    {
        $response = $this->api->post("letters/{$id}/delete", []);

        if ($response->failed()) {
            return back()->with('error', $response->json('message') ?? 'Could not delete template.');
        }

        return redirect('admin/letters')->with('success', 'Letter template deleted.');
    }

    // ── Letterhead settings ───────────────────────────────────────────

    public function letterheadEdit()
    {
        $response = $this->api->get('letters/letterhead');
        $data = $response->object();

        return view('letters.letterhead', [
            'header_title' => 'College Letterhead',
            'settings'     => $data->settings,
        ]);
    }

    public function letterheadUpdate(Request $request)
    {
        $files = [];
        foreach (['logo', 'watermark'] as $field) {
            if ($request->hasFile($field)) {
                $files[$field] = $request->file($field);
            }
        }

        $response = $this->api->postWithFile(
            'letters/letterhead',
            $request->only(['institution_name', 'address_text', 'footer_text']),
            $files
        );

        if ($response->failed()) {
            return back()->withInput()->with('error', $response->json('message') ?? 'Error updating letterhead.');
        }

        return redirect('admin/letters/letterhead')->with('success', 'Letterhead updated.');
    }

    public function letterheadPreview()
    {
        $response = $this->api->get('letters/letterhead/preview');

        return response($response->body(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Letterhead-Preview.pdf"',
            'Cache-Control'       => 'private, max-age=0, must-revalidate',
        ]);
    }

    /** Live preview for the template editor: the letter on the letterhead, from the body as typed (not yet saved). */
    public function livePreview(Request $request)
    {
        $response = $this->api->post('letters/preview-html', array_filter($request->only([
            'pdf_body', 'template_id', 'signatory_name', 'signatory_title', 'signature_data', 'stamp_data',
            'clear_signature', 'clear_stamp', 'use_college_stamp',
        ]), fn ($v) => $v !== null && $v !== ''));

        return response()->json(['html' => $response->json('html') ?? ''], $response->successful() ? 200 : 422);
    }

    public function templatePreview($id)
    {
        $response = $this->api->get("letters/{$id}/preview");

        return response($response->body(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Template-Preview.pdf"',
            'Cache-Control'       => 'private, max-age=0, must-revalidate',
        ]);
    }

    // ── Recipient selection + dispatch ───────────────────────────────

    public function recipients(Request $request, $id)
    {
        $response = $this->api->get(
            "letters/{$id}/recipients",
            $request->only(['country_id', 'programme_id', 'year', 'search', 'unsent_only'])
        );
        $data = $response->object();

        return view('letters.recipients', [
            'header_title' => 'Send: ' . ($data->template->name ?? ''),
            'template'     => $data->template,
            'recipients'   => collect($data->recipients ?? []),
            'countries'    => collect($data->countries ?? []),
            'programmes'   => collect($data->programmes ?? []),
            'filters'      => (array) ($data->filters ?? []),
        ]);
    }

    public function dispatch(Request $request, $id)
    {
        $user = Auth::user();
        $fields = array_merge(
            $request->except('_token', 'attachments'),
            [
                'sender_name'  => $user?->name,
                'sender_email' => $user?->email,
            ]
        );

        $attachments = $request->file('attachments', []);
        $response = $attachments
            ? $this->api->postWithFile("letters/{$id}/dispatch", $fields, ['attachments' => $attachments])
            : $this->api->post("letters/{$id}/dispatch", $fields);

        if ($response->failed()) {
            return back()->with('error', $response->json('message') ?? 'Dispatch failed.');
        }

        return redirect('admin/letters')->with('success', $response->json('message'));
    }

    // ── Report ────────────────────────────────────────────────────────

    public function report(Request $request)
    {
        $response = $this->api->get('letters/report', $request->only(['template_id', 'status', 'search']));
        $data = $response->object();

        $rawRows = $data->rows ?? null;
        $rows = new LengthAwarePaginator(
            collect($rawRows->data ?? []),
            $rawRows->total ?? 0,
            $rawRows->per_page ?? 50,
            $rawRows->current_page ?? 1,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('letters.report', [
            'header_title' => 'Sent Letters Report',
            'rows'         => $rows,
            'templates'    => collect($data->templates ?? []),
            'filters'      => (array) ($data->filters ?? []),
        ]);
    }

    public function downloadSentPdf($id)
    {
        $response = $this->api->get("letters/sent/{$id}/download");

        if ($response->status() === 404) {
            abort(404);
        }

        $filename = 'letter.pdf';
        if ($cd = $response->header('Content-Disposition')) {
            if (preg_match('/filename=["\']?([^"\';\s]+)/', $cd, $m)) {
                $filename = $m[1];
            }
        }

        return response($response->body(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
