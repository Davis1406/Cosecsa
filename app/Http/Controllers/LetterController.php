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
            'name', 'subject', 'pdf_body', 'email_body', 'recipient_block', 'recipient_source', 'legacy_status_field',
            'signatory_name', 'signatory_title', 'remove_signature', 'remove_stamp', 'use_college_stamp', 'stamp_pos',
        ]) + ['is_active_form' => '1', 'recipient_block_form' => '1', 'is_active' => $request->boolean('is_active') ? '1' : null];
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
        $payload = array_filter($request->only([
            'pdf_body', 'template_id', 'signatory_name', 'signatory_title', 'signature_data', 'stamp_data',
            'clear_signature', 'clear_stamp', 'use_college_stamp', 'stamp_pos',
        ]), fn ($v) => $v !== null && $v !== '');
        // sent even when empty: an emptied "To" section means the standard one, not the saved one
        $payload['recipient_block'] = (string) $request->input('recipient_block', '');
        $response = $this->api->post('letters/preview-html', $payload);

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
        $query = $request->only(['country_id', 'programme_id', 'year', 'search', 'unsent_only', 'source', 'conditions']);
        // The year box submitted empty means "every year"; a page opened without it starts on the current intake (the API decides).
        // (An empty value would be dropped from the query string, so it is sent as an explicit empty string.)
        if ($request->has('year')) {
            $query['year'] = (string) $request->input('year', '');
        }
        $response = $this->api->get("letters/{$id}/recipients", $query);
        $data = $response->object();

        return view('letters.recipients', [
            'header_title' => 'Send: ' . ($data->template->name ?? ''),
            'template'     => $data->template,
            'recipients'   => collect($data->recipients ?? []),
            'countries'    => collect($data->countries ?? []),
            'programmes'   => collect($data->programmes ?? []),
            'filters'      => json_decode(json_encode($data->filters ?? []), true) ?: [],
            'default_year' => $data->default_year ?? null,
            'source'       => $data->source ?? ($data->template->recipient_source ?? ''),
            'sources'      => (array) ($data->sources ?? []),
            'columns'      => json_decode(json_encode($data->columns ?? []), true) ?: [],
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

    // One batch of a larger sending, answered as JSON. The page sends a few letters at a time (each takes about three
    // seconds to make and e-mail) so no request outlasts the 30-second wait on the API; the first batch creates the
    // dispatch and the rest pass its id (dispatch_id) to add to it.
    public function dispatchBatch(Request $request, $id)
    {
        $user = Auth::user();
        $fields = array_merge(
            $request->except('_token', 'attachments'),
            ['sender_name' => $user?->name, 'sender_email' => $user?->email]
        );

        $attachments = $request->file('attachments', []);
        try {
            $response = $attachments
                ? $this->api->postWithFile("letters/{$id}/dispatch", $fields, ['attachments' => $attachments])
                : $this->api->post("letters/{$id}/dispatch", $fields);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json(['message' => 'The server took too long to answer, so it is not known whether this batch went out. Check the Sent Letters report before sending again.'], 504);
        }

        if ($response->failed()) {
            return response()->json(['message' => $response->json('message') ?? 'This batch could not be sent.'], $response->status() ?: 502);
        }

        return response()->json($response->json());
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

    // ── Preview, test send and the letters kept on a profile ──────────

    // The e-mail and the letter exactly as one recipient would receive them (JSON for the page's preview window).
    public function compose(Request $request, $id)
    {
        $response = $this->api->get("letters/{$id}/compose", array_filter($request->only(['source', 'recipient_id', 'letter_date']), fn ($v) => $v !== null && $v !== ''));

        return response()->json($response->json() ?? ['message' => 'No answer from the server.'], $response->status());
    }

    // A test to one address (the admin's own by default); nothing is recorded and no one is marked as sent.
    public function sendTest(Request $request, $id)
    {
        $fields = array_filter($request->only(['to', 'source', 'recipient_id', 'letter_date']), fn ($v) => $v !== null && $v !== '');
        $attachments = $request->file('attachments', []);
        $response = $attachments
            ? $this->api->postWithFile("letters/{$id}/test", $fields, ['attachments' => $attachments])
            : $this->api->post("letters/{$id}/test", $fields);

        return response()->json($response->json() ?? ['message' => 'No answer from the server.'], $response->status());
    }

    // The letters sent to one person, for the Letters tab of their profile.
    public function forProfile($source, $recordId)
    {
        $response = $this->api->get("letters/for/{$source}/{$recordId}");

        return response()->json($response->json() ?? ['letters' => []], $response->status());
    }

    // A sent letter as a web page, built from the person's current details.
    public function sentView(Request $request, $id)
    {
        $response = $this->api->get("letters/sent/{$id}/view", array_filter($request->only('letter_date')));
        if ($response->failed()) {
            return redirect('admin/letters/report')->with('error', $response->json('message') ?? 'This letter cannot be shown.');
        }

        return view('letters.sent_view', ['id' => $id, 'letter' => $response->json(), 'header_title' => ($response->json('letter') ?? 'Letter')]);
    }

    // The letter as a PDF made now from the current details.
    public function sentPdf(Request $request, $id)
    {
        $response = $this->api->get("letters/sent/{$id}/pdf", array_filter($request->only('letter_date')));
        if ($response->failed()) {
            return back()->with('error', $response->json('message') ?? 'The PDF could not be made.');
        }

        $filename = 'letter.pdf';
        if (($cd = $response->header('Content-Disposition')) && preg_match('/filename=["\']?([^"\';\s]+)/', $cd, $m)) {
            $filename = $m[1];
        }

        return response($response->body(), 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => "attachment; filename=\"{$filename}\""]);
    }

    // The edit panel of a sent letter: the letter as it would look with the changes being typed (nothing is saved).
    public function previewSentEdits(Request $request, $id)
    {
        $response = $this->api->post("letters/sent/{$id}/preview", $this->editPayload($request));

        return response()->json($response->json() ?? ['message' => 'No answer from the server.'], $response->status());
    }

    // Keep the changes to this one letter, or ("reset") go back to the template wording.
    public function saveSentEdits(Request $request, $id)
    {
        $payload = $request->boolean('reset') ? ['reset' => true] : $this->editPayload($request);
        $response = $this->api->post("letters/sent/{$id}/edits", $payload);

        if ($response->successful()) {
            session()->flash('success', $response->json('message'));
        }

        return response()->json($response->json() ?? ['message' => 'No answer from the server.'], $response->status());
    }

    // Every field is sent even when empty: an emptied "To" section or signatory is a change.
    private function editPayload(Request $request): array
    {
        $payload = [];
        foreach (['letter_date', 'recipient_block', 'pdf_body', 'signatory_name', 'signatory_title', 'stamp_pos'] as $field) {
            $payload[$field] = (string) $request->input($field, '');
        }

        return $payload;
    }

    // The PDF served inline so the writing editor can render it on the page: the written-on copy, else the edited
    // letter, else the PDF as it was e-mailed (the API decides).
    public function sentPdfSource($id)
    {
        $response = $this->api->get("letters/sent/{$id}/source");
        if ($response->failed()) {
            abort($response->status() === 404 ? 404 : 502, $response->json('message') ?? 'The PDF could not be loaded.');
        }

        return response($response->body(), 200, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline; filename="letter.pdf"']);
    }

    // The PDF exactly as it was e-mailed, kept even after writing is saved on a copy.
    public function downloadOriginalSentPdf($id)
    {
        $response = $this->api->get("letters/sent/{$id}/original");
        if ($response->status() === 404) {
            abort(404);
        }
        if ($response->failed()) {
            return back()->with('error', $response->json('message') ?? 'The original PDF could not be loaded.');
        }

        return response($response->body(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="letter-original.pdf"',
        ]);
    }

    // Save the PDF the page made after the admin wrote on it. The finished PDF arrives as a multipart file.
    public function saveSentPdf(Request $request, $id)
    {
        $request->validate(['pdf' => 'required|file|max:20480']);
        $file = $request->file('pdf');
        $content = file_get_contents($file->getRealPath());
        if (strtolower($file->getClientOriginalExtension()) !== 'pdf' || ! is_string($content) || ! str_starts_with($content, '%PDF-')) {
            return response()->json(['message' => 'That file is not a PDF.'], 422);
        }

        $response = $this->api->postWithFile("letters/sent/{$id}/pdf", [], ['pdf' => $file]);
        if ($response->failed()) {
            return response()->json(['message' => $response->json('message') ?? 'The writing could not be saved.'], $response->status());
        }

        session()->flash('success', $response->json('message') ?? 'The writing was saved on this letter.');

        return response()->json($response->json() ?? ['message' => 'The writing was saved on this letter.']);
    }

    // Send the letter again, composed from the current details.
    public function resend(Request $request, $id)
    {
        $response = $this->api->post("letters/sent/{$id}/resend", array_filter($request->only(['to', 'letter_date']), fn ($v) => $v !== null && $v !== ''));

        return response()->json($response->json() ?? ['message' => 'No answer from the server.'], $response->status());
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
