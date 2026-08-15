<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DraftEmailsController extends Controller
{
    public function __construct(private ApiClient $api) {}

    private const FIELDS = [
        'name', 'subject', 'body', 'visibility', 'visible_user_ids',
        'recipient_group', 'cc_personal_email', 'send_mode',
        'automation_trigger', 'automation_threshold',
    ];

    public function list(Request $request)
    {
        $response = $this->api->get('admin/draft-emails', $request->only(['search']));
        $data = $response->object();

        $draftEmails = collect($data->draft_emails ?? [])->filter(fn ($d) => $this->canView($d))->values();

        return view('admin.draft_emails.list', [
            'header_title' => 'Draft Emails',
            'draftEmails'  => $draftEmails,
        ]);
    }

    public function add()
    {
        return view('admin.draft_emails.form', [
            'header_title' => 'New Draft Email',
            'draftEmail'   => null,
            'admins'       => $this->admins(),
        ]);
    }

    public function insert(Request $request)
    {
        $response = $this->api->post('admin/draft-emails', $this->payload($request));

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

        if (! $this->canView($data->draft_email)) {
            abort(403, 'This draft email is only visible to specific people in the secretariat.');
        }

        return view('admin.draft_emails.form', [
            'header_title' => 'Edit Draft Email',
            'draftEmail'   => $data->draft_email,
            'admins'       => $this->admins(),
        ]);
    }

    public function update(Request $request, $id)
    {
        $response = $this->api->put("admin/draft-emails/{$id}", $this->payload($request));

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

    // Manual "Send Now" — sends immediately to whatever recipient_group is
    // configured, no automation condition checked.
    public function sendNow($id)
    {
        $response = $this->api->post("admin/draft-emails/{$id}/send-now");

        if ($response->failed()) {
            return back()->with('error', $response->json('message', 'Failed to send.'));
        }

        return back()->with('success', $response->json('message', 'Sent.'));
    }

    private function payload(Request $request): array
    {
        $data = $request->only(self::FIELDS);
        $data['visible_user_ids'] = $request->input('visible_user_ids', []);
        $data['cc_personal_email'] = $request->boolean('cc_personal_email');
        $data['custom_recipients'] = $data['recipient_group'] === 'custom'
            ? $this->parseCustomRecipients($request->input('custom_recipients_text', ''))
            : [];
        return $data;
    }

    // "Name <email@x.com>", "email@x.com, Name"/"Name, email@x.com", or a
    // bare email per line — whatever a human pastes in or a CSV import
    // produces (the form's client-side CSV parser also just writes into
    // this same textarea, so both paths go through this one parser).
    private function parseCustomRecipients(string $text): array
    {
        $out = [];
        foreach (preg_split('/\r?\n/', $text) as $line) {
            $line = trim($line);
            if ($line === '') continue;

            if (preg_match('/^(.*?)<\s*([^<>\s]+@[^<>\s]+)\s*>$/', $line, $m)) {
                $out[] = ['name' => trim($m[1], " \t,"), 'email' => trim($m[2])];
                continue;
            }
            if (preg_match('/^([^\s@]+@[^\s@]+\.[^\s@]+)$/', $line, $m)) {
                $out[] = ['name' => '', 'email' => $m[1]];
                continue;
            }
            $parts = array_map('trim', explode(',', $line));
            $email = null;
            foreach ($parts as $p) {
                if (preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $p)) { $email = $p; break; }
            }
            if ($email) {
                $name = trim(implode(' ', array_diff($parts, [$email])));
                $out[] = ['name' => $name, 'email' => $email];
            }
        }

        return $out;
    }

    // "Anyone in the secretariat" = every logged-in admin/staff user — since
    // this app's admin panel has no login other than secretariat staff, that
    // is simply every entry the Admin List already shows.
    private function admins()
    {
        $response = $this->api->get('admin/users');
        return collect($response->object()->admins ?? []);
    }

    private function canView($draft): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }
        if (($draft->visibility ?? 'all') !== 'selected') {
            return true;
        }
        if ($user->isSuperAdmin() || $user->isMasterAdmin()) {
            return true;
        }
        $allowed = $draft->visible_user_ids ?? [];
        return in_array($user->id, $allowed, false);
    }
}
