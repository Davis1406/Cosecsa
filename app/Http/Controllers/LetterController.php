<?php

namespace App\Http\Controllers;

use App\Mail\LetterDispatchMail;
use App\Models\CollegeLetterheadSetting;
use App\Models\LetterDispatch;
use App\Models\LetterDispatchRecipient;
use App\Models\LetterTemplate;
use App\Services\LetterRecipientResolver;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class LetterController extends Controller
{
    public function index()
    {
        $templates = LetterTemplate::withCount('dispatches')->orderBy('name')->get();
        $recentDispatches = LetterDispatch::with(['template', 'sender'])->orderByDesc('sent_at')->limit(10)->get();

        return view('letters.index', [
            'header_title' => 'College Letters',
            'templates'    => $templates,
            'recentDispatches' => $recentDispatches,
        ]);
    }

    public function create()
    {
        return view('letters.form', [
            'header_title' => 'New Letter Template',
            'template'     => null,
            'sources'      => LetterRecipientResolver::SOURCES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateTemplate($request);
        $data['created_by'] = Auth::id();
        LetterTemplate::create($data);

        return redirect('admin/letters')->with('success', 'Letter template created.');
    }

    public function edit($id)
    {
        $template = LetterTemplate::findOrFail($id);

        return view('letters.form', [
            'header_title' => 'Edit Letter Template',
            'template'     => $template,
            'sources'      => LetterRecipientResolver::SOURCES,
        ]);
    }

    public function update(Request $request, $id)
    {
        $template = LetterTemplate::findOrFail($id);
        $template->update($this->validateTemplate($request));

        return redirect('admin/letters')->with('success', 'Letter template updated.');
    }

    public function destroy($id)
    {
        LetterTemplate::findOrFail($id)->delete();

        return redirect('admin/letters')->with('success', 'Letter template deleted.');
    }

    protected function validateTemplate(Request $request): array
    {
        $data = $request->validate([
            'name'                 => 'required|string|max:255',
            'subject'              => 'required|string|max:255',
            'pdf_body'             => 'required|string',
            'email_body'           => 'required|string',
            'recipient_source'     => 'required|in:' . implode(',', array_keys(LetterRecipientResolver::SOURCES)),
            'legacy_status_field'  => 'nullable|in:admission_letter_status,invitation_letter_status',
            'is_active'            => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);

        return $data;
    }

    // ── Letterhead settings ───────────────────────────────────────────

    public function letterheadEdit()
    {
        return view('letters.letterhead', [
            'header_title' => 'College Letterhead',
            'settings'     => CollegeLetterheadSetting::current(),
        ]);
    }

    public function letterheadUpdate(Request $request)
    {
        $request->validate([
            'institution_name' => 'required|string|max:255',
            'address_text'     => 'nullable|string|max:2000',
            'footer_text'      => 'nullable|string|max:2000',
            'logo'              => 'nullable|image|max:4096',
            'watermark'         => 'nullable|image|max:4096',
        ]);

        $settings = CollegeLetterheadSetting::current();
        $settings->institution_name = $request->institution_name;
        $settings->address_text = $request->address_text;
        $settings->footer_text = $request->footer_text;

        if ($request->hasFile('logo')) {
            if ($settings->logo_path && Storage::disk('public')->exists($settings->logo_path)) {
                Storage::disk('public')->delete($settings->logo_path);
            }
            $settings->logo_path = $request->file('logo')->store('letterhead', 'public');
        }
        if ($request->hasFile('watermark')) {
            if ($settings->watermark_path && Storage::disk('public')->exists($settings->watermark_path)) {
                Storage::disk('public')->delete($settings->watermark_path);
            }
            $settings->watermark_path = $request->file('watermark')->store('letterhead', 'public');
        }

        $settings->updated_by = Auth::id();
        $settings->save();

        return redirect('admin/letters/letterhead')->with('success', 'Letterhead updated.');
    }

    // ── Recipient selection + dispatch ───────────────────────────────

    public function recipients(Request $request, $id, LetterRecipientResolver $resolver)
    {
        $template = LetterTemplate::findOrFail($id);

        $filters = $request->only(['country_id', 'programme_id', 'year', 'search', 'unsent_only']);
        $recipients = $resolver->query($template->recipient_source, $filters, $template->legacy_status_field);

        // Mark anyone who's already received THIS letter (via the generic
        // dispatch log, not just the trainees legacy status field), so the
        // "hasn't received yet" story works for every recipient source.
        $recipients = $recipients->map(function ($r) use ($resolver, $template) {
            $r->already_sent = $resolver->alreadySent($template->id, $r->source, $r->id);
            return $r;
        });

        return view('letters.recipients', [
            'header_title' => 'Send: ' . $template->name,
            'template'     => $template,
            'recipients'   => $recipients,
            'countries'    => $resolver->countries(),
            'programmes'   => $resolver->programmes(),
            'filters'      => $filters,
        ]);
    }

    public function dispatch(Request $request, $id, LetterRecipientResolver $resolver)
    {
        $template = LetterTemplate::findOrFail($id);
        $request->validate([
            'recipient_ids'   => 'required|array|min:1',
            'letter_date'     => 'nullable|date',
        ]);

        $letterDate = $request->filled('letter_date') ? \Carbon\Carbon::parse($request->letter_date) : now();
        $filters = $request->only(['country_id', 'programme_id', 'year', 'search', 'unsent_only']);
        $allMatching = $resolver->query($template->recipient_source, $filters, $template->legacy_status_field);
        $selectedIds = array_map('intval', $request->input('recipient_ids'));
        $selected = $allMatching->whereIn('id', $selectedIds);

        $letterhead = CollegeLetterheadSetting::current();
        $sender = Auth::user();

        $dispatch = LetterDispatch::create([
            'letter_template_id' => $template->id,
            'sent_by'            => Auth::id(),
            'sent_at'            => now(),
        ]);

        $sentCount = 0;
        foreach ($selected as $r) {
            $fields = $resolver->mergeFields($r, $letterDate);
            $subject = $resolver->render($template->subject, $fields);
            $pdfBody = $resolver->render($template->pdf_body, $fields);
            $emailBody = $resolver->render($template->email_body, $fields);

            $status = 'sent';
            $error = null;
            $pdfPath = null;

            try {
                $pdf = Pdf::loadView('letters.pdf', [
                    'letterhead'  => $letterhead,
                    'letterDate'  => $letterDate,
                    'recipient'   => $r,
                    'bodyHtml'    => $pdfBody,
                    'sender'      => $sender,
                ])->setPaper('a4');

                $pdfContent = $pdf->output();
                $safeName = preg_replace('/[^A-Za-z0-9]+/', '_', $r->name ?: 'recipient');
                $pdfPath = "letters/dispatch/{$template->id}/{$dispatch->id}_{$safeName}.pdf";
                Storage::disk('public')->put($pdfPath, $pdfContent);

                if ($r->email) {
                    Mail::to($r->email)->send(new LetterDispatchMail($subject, $emailBody, $pdfContent, $safeName . '.pdf', $sender));
                }

                if ($template->legacy_status_field && $r->source === 'trainees') {
                    \Illuminate\Support\Facades\DB::table('trainees')->where('id', $r->id)
                        ->update([$template->legacy_status_field => 'Sent']);
                }

                $sentCount++;
            } catch (\Throwable $e) {
                $status = 'failed';
                $error = $e->getMessage();
            }

            LetterDispatchRecipient::create([
                'dispatch_id'        => $dispatch->id,
                'letter_template_id' => $template->id,
                'recipient_source'   => $r->source,
                'recipient_id'       => $r->id,
                'recipient_name'     => $r->name,
                'recipient_email'    => $r->email,
                'pdf_path'           => $pdfPath,
                'status'             => $status,
                'error_message'      => $error,
                'sent_at'            => now(),
            ]);
        }

        $dispatch->update(['recipient_count' => $sentCount]);

        return redirect('admin/letters')->with('success', "Dispatched {$sentCount} of " . $selected->count() . ' letter(s).');
    }

    // ── Report ────────────────────────────────────────────────────────

    public function report(Request $request)
    {
        $q = LetterDispatchRecipient::with(['template', 'dispatch.sender'])->orderByDesc('sent_at');

        if ($request->filled('template_id')) $q->where('letter_template_id', $request->template_id);
        if ($request->filled('status')) $q->where('status', $request->status);
        if ($request->filled('search')) {
            $like = '%' . $request->search . '%';
            $q->where(function ($w) use ($like) {
                $w->where('recipient_name', 'like', $like)->orWhere('recipient_email', 'like', $like);
            });
        }

        return view('letters.report', [
            'header_title' => 'Sent Letters Report',
            'rows'         => $q->paginate(50)->withQueryString(),
            'templates'    => LetterTemplate::orderBy('name')->get(),
            'filters'      => $request->only(['template_id', 'status', 'search']),
        ]);
    }

    public function downloadSentPdf($id)
    {
        $row = LetterDispatchRecipient::findOrFail($id);
        abort_unless($row->pdf_path && Storage::disk('public')->exists($row->pdf_path), 404);

        return Storage::disk('public')->download($row->pdf_path, ($row->recipient_name ?: 'letter') . '.pdf');
    }
}
