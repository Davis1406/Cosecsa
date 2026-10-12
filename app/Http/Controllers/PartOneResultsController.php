<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

// Candidates → Upload Part I Results: upload the examination officer's
// "Part I Results" spreadsheet, review how every row matches (preview), then
// confirm to save the results and promote passers to Part II. Parsing and
// matching happen in cosecsa-api (internal/candidates/part-one/*); the file is
// kept here between preview and confirm so it doesn't have to be re-selected.
class PartOneResultsController extends Controller
{
    private const DIR = 'part-one-uploads';

    public function __construct(private ApiClient $api) {}

    public function upload()
    {
        return view('admin.associates.candidates.part_one_upload', [
            'header_title' => 'Upload Part I Results',
        ]);
    }

    public function preview(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls|max:10240']);

        $file  = $request->file('file');
        $token = Str::random(32);
        Storage::disk('local')->putFileAs(self::DIR, $file, "{$token}.xlsx");
        session(["part_one_upload.{$token}" => $file->getClientOriginalName()]);

        $response = $this->api->postWithFile('candidates/part-one/preview', [], ['file' => $file]);
        if ($response->failed()) {
            $this->discard($token);
            return back()->with('error', $response->json('message') ?? 'Could not read the spreadsheet.');
        }

        return view('admin.associates.candidates.part_one_preview', [
            'header_title' => 'Review Part I Results',
            'token'        => $token,
            'fileName'     => $file->getClientOriginalName(),
            'columns'      => $response->json('columns'),
            'rows'         => $response->json('rows'),
            'summary'      => $response->json('summary'),
        ]);
    }

    public function import(Request $request)
    {
        $request->validate(['token' => 'required|alpha_num|size:32']);
        $token = $request->input('token');
        $name  = session("part_one_upload.{$token}");
        $path  = Storage::disk('local')->path(self::DIR . "/{$token}.xlsx");

        if (! $name || ! is_file($path)) {
            return redirect('admin/associates/candidates/part-one')
                ->with('error', 'That upload has expired — please upload the spreadsheet again.');
        }

        $response = $this->api->postWithFile('candidates/part-one/import',
            ['promote' => $request->boolean('promote') ? '1' : null, 'file_name' => $name],
            ['file' => new UploadedFile($path, $name, null, null, true)]
        );
        $this->discard($token);

        if ($response->failed()) {
            return redirect('admin/associates/candidates/part-one')
                ->with('error', $response->json('message') ?? 'The import failed — nothing was saved.');
        }

        return redirect('admin/associates/candidates/list')->with('success', $response->json('message'));
    }

    // Promote one candidate to Part II, or undo it.
    public function setPartTwo(Request $request, $candidateId)
    {
        $response = $this->api->post("candidates/{$candidateId}/part-two", ['promote' => $request->boolean('promote')]);

        return back()->with($response->failed() ? 'error' : 'success',
            $response->json('message') ?? ($response->failed() ? 'Could not update the Part II status.' : 'Updated.'));
    }

    private function discard(string $token): void
    {
        Storage::disk('local')->delete(self::DIR . "/{$token}.xlsx");
        session()->forget("part_one_upload.{$token}");
    }
}
