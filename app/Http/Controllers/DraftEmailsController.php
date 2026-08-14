<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Request;

class DraftEmailsController extends Controller
{
    public function __construct(private ApiClient $api) {}

    public function list(Request $request)
    {
        $response = $this->api->get('admin/draft-emails', $request->only(['search']));
        $data = $response->object();

        return view('admin.draft_emails.list', [
            'header_title' => 'Draft Emails',
            'draftEmails'  => collect($data->draft_emails ?? []),
        ]);
    }

    public function add()
    {
        return view('admin.draft_emails.form', [
            'header_title' => 'New Draft Email',
            'draftEmail'   => null,
        ]);
    }

    public function insert(Request $request)
    {
        $response = $this->api->post('admin/draft-emails', $request->only(['name', 'subject', 'body']));

        if ($response->failed()) {
            return back()->withInput()->with('error', $response->json('message', 'Failed to create draft email.'));
        }

        return redirect('admin/draft-emails')->with('success', 'Draft email created successfully.');
    }

    public function edit($id)
    {
        $response = $this->api->get("admin/draft-emails/{$id}");

        if ($response->status() === 404) {
            return redirect('admin/draft-emails')->with('error', 'Draft email not found.');
        }

        $data = $response->object();

        return view('admin.draft_emails.form', [
            'header_title' => 'Edit Draft Email',
            'draftEmail'   => $data->draft_email,
        ]);
    }

    public function update(Request $request, $id)
    {
        $response = $this->api->put("admin/draft-emails/{$id}", $request->only(['name', 'subject', 'body']));

        if ($response->failed()) {
            return back()->withInput()->with('error', $response->json('message', 'Failed to update draft email.'));
        }

        return redirect('admin/draft-emails')->with('success', 'Draft email updated successfully.');
    }

    public function delete($id)
    {
        $response = $this->api->delete("admin/draft-emails/{$id}");

        if ($response->failed()) {
            return back()->with('error', $response->json('message', 'Failed to delete draft email.'));
        }

        return redirect('admin/draft-emails')->with('success', 'Draft email deleted.');
    }
}
