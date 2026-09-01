<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Request;

// Generic proxy for the notes log on any associate profile page (Fellow,
// Member, Country Rep, Trainee, Trainer, Programme Director, Candidate,
// Examiner) — see AssociateNoteController on the API side. One controller
// backs every associate type; routes/web.php binds a fixed `type` route
// default per associate so PermissionMiddleware's route_map (matched by URL
// prefix) still gates each one under its own module, same as every other
// associate route.
class AssociateNoteController extends Controller
{
    public function __construct(private ApiClient $api) {}

    public function store(Request $request, $id)
    {
        $type = $request->route('type');
        $back = $request->input('back', url()->previous());

        $request->validate([
            'note'       => 'required|string|max:5000',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,gif,webp|max:10240',
        ]);

        $files = $request->hasFile('attachment') ? ['attachment' => $request->file('attachment')] : [];

        $response = $this->api->postWithFile(
            "associate-notes/{$type}/{$id}",
            ['note' => $request->input('note')],
            $files
        );

        if ($response->failed()) {
            return redirect($back)->with('error', $response->json('message') ?? 'Failed to add note.');
        }

        return redirect($back)->with('success', 'Note added successfully.');
    }

    public function destroy(Request $request, $noteId)
    {
        $type = $request->route('type');
        $back = $request->input('back', url()->previous());

        $response = $this->api->delete("associate-notes/{$type}/{$noteId}");

        if ($response->failed()) {
            return redirect($back)->with('error', $response->json('message') ?? 'Failed to delete note.');
        }

        return redirect($back)->with('success', 'Note deleted.');
    }
}
