<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Request;

class TranscriptController extends Controller
{
    public function __construct(private ApiClient $api) {}

    public function search(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $results = collect();

        if ($q) {
            $response = $this->api->get('transcripts/search', ['q' => $q]);
            $results = collect($response->json('results') ?? []);
        }

        return view('admin.transcripts.search', [
            'header_title' => 'Transcripts',
            'q'            => $q,
            'results'      => $results,
        ]);
    }

    public function searchLive(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        if (! $q) return response()->json([]);

        $response = $this->api->get('transcripts/search-live', ['q' => $q]);
        return response()->json($response->json(), $response->status());
    }

    public function edit($userId)
    {
        $response = $this->api->get("transcripts/{$userId}");

        if ($response->status() === 422) {
            return redirect('admin/transcripts')->with('error', $response->json('message'));
        }

        $data = $response->object();

        return view('admin.transcripts.edit', [
            'header_title'  => 'Issue Transcript',
            'record'        => $data->record,
            'recordExists'  => $data->record_exists ?? false,
            'courses'       => collect($data->courses ?? []),
            'templates'     => collect($data->templates ?? []),
            'userId'        => $userId,
        ]);
    }

    // Share = save the details, then email the PDF to the fellow; the API keeps
    // the sent copy on their profile.
    public function save(Request $request, $userId)
    {
        $response = $this->api->post("transcripts/{$userId}", $request->except('_token'));

        if ($response->failed()) {
            return back()->withInput()->with('error', $response->json('message') ?? 'Error saving transcript.');
        }

        $sent = $this->api->post("transcripts/{$userId}/send");

        if ($sent->failed()) {
            return redirect("admin/transcripts/edit/{$userId}")
                ->with('error', 'Transcript details saved, but it was not shared: ' . ($sent->json('message') ?? 'unknown error.'));
        }

        return redirect("admin/transcripts/edit/{$userId}")->with('success', $sent->json('message') ?? 'Transcript shared.');
    }

    public function preview(Request $request, $userId)
    {
        $response = $this->api->post("transcripts/{$userId}/preview", $request->except('_token'));

        if ($response->failed()) {
            return response($response->json('message') ?? 'Could not render the preview.', $response->status());
        }

        return response($response->body(), 200, [
            'Content-Type'  => 'application/pdf',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function sentCopy($userId)
    {
        $response = $this->api->get("transcripts/{$userId}/copy");

        if ($response->failed()) {
            return back()->with('error', $response->json('message') ?? 'No sent transcript found.');
        }

        $filename = 'COSECSA Transcript.pdf';
        if ($cd = $response->header('Content-Disposition')) {
            if (preg_match('/filename=["\']?([^"\';\s]+)/', $cd, $m)) {
                $filename = $m[1];
            }
        }

        return response($response->body(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
            'Cache-Control'       => 'private, max-age=0, must-revalidate',
        ]);
    }

    public function pdf($userId)
    {
        $response = $this->api->get("transcripts/{$userId}/pdf");

        $filename = "COSECSA Transcript.pdf";
        if ($cd = $response->header('Content-Disposition')) {
            if (preg_match('/filename=["\']?([^"\';\s]+)/', $cd, $m)) {
                $filename = $m[1];
            }
        }

        return response($response->body(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
            'Cache-Control'       => 'private, max-age=0, must-revalidate',
        ]);
    }
}
