@extends('layout.app')

@section('content')
<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">

            <div class="col-md-12 p-0">@include('_message')</div>

            {{-- Hero header --}}
            <div class="de-hero">
                <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:.75rem;">
                    <div>
                        <h4>
                            <i class="fas {{ $draftEmail ? 'fa-edit' : 'fa-paper-plane' }} mr-2"></i>
                            {{ $draftEmail ? 'Edit Draft Email' : 'New Draft Email' }}
                        </h4>
                        <div class="meta">Use <code>[Name]</code> to personalise (preview shows "Dr. Example") and <code>@{{number}}</code> for the pending-application count (preview shows "25"). Emails are sent from your account, so replies come back to you.</div>
                    </div>
                    <a href="{{ url('admin/draft-emails') }}" class="btn btn-new">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Draft Emails
                    </a>
                </div>
            </div>

            <div class="row">

                {{-- ── Editor column ── --}}
                <div class="col-lg-6">
                    <div class="card de-card-panel">
                        <div class="card-body">
                            <form method="POST" action="{{ $draftEmail ? url('admin/draft-emails/edit/'.$draftEmail->id) : url('admin/draft-emails/add') }}">
                                @csrf

                                <div class="form-group">
                                    <label class="de-label">Draft Name</label>
                                    <input type="text" name="name" class="form-control de-input"
                                           value="{{ old('name', $draftEmail->name ?? '') }}"
                                           placeholder="e.g. Trainee Welcome Email" required>
                                    <small class="text-muted">An internal label to find this draft again later.</small>
                                </div>

                                <div class="form-group">
                                    <label class="de-label">Subject Line</label>
                                    <input type="text" name="subject" id="subjectInput" class="form-control de-input"
                                           value="{{ old('subject', $draftEmail->subject ?? '') }}"
                                           placeholder="e.g. Welcome to COSECSA, [Name]" required>
                                </div>

                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="de-label mb-0">Email Body</label>
                                        <button type="button" class="btn btn-xs de-insert-btn" id="insertNameBtn">
                                            <i class="fas fa-magic mr-1"></i> Insert [Name]
                                        </button>
                                    </div>
                                    <textarea name="body" id="bodyEditor" class="form-control" rows="14">{{ old('body', $draftEmail->body ?? '') }}</textarea>
                                </div>

                                <hr class="my-4">

                                {{-- ── Who can see this draft ── --}}
                                <div class="form-group">
                                    <label class="de-label">Who Can View This Draft</label>
                                    @php $visibility = old('visibility', $draftEmail->visibility ?? 'all'); @endphp
                                    <div class="de-radio-row">
                                        <label class="de-radio">
                                            <input type="radio" name="visibility" value="all" {{ $visibility === 'all' ? 'checked' : '' }}>
                                            <span>Everyone in the secretariat</span>
                                        </label>
                                        <label class="de-radio">
                                            <input type="radio" name="visibility" value="selected" {{ $visibility === 'selected' ? 'checked' : '' }}>
                                            <span>Specific people only</span>
                                        </label>
                                    </div>
                                    @php $visibleIds = old('visible_user_ids', $draftEmail->visible_user_ids ?? []); @endphp
                                    <div id="visibleUsersBox" class="de-chip-list {{ $visibility === 'selected' ? '' : 'd-none' }}">
                                        @forelse($admins as $a)
                                        <label class="de-chip">
                                            <input type="checkbox" name="visible_user_ids[]" value="{{ $a->id }}"
                                                   {{ in_array($a->id, $visibleIds) ? 'checked' : '' }}>
                                            <span>{{ $a->name }}</span>
                                        </label>
                                        @empty
                                        <div class="text-muted small">No other admin users found.</div>
                                        @endforelse
                                    </div>
                                </div>

                                {{-- ── Who it gets sent to ── --}}
                                <div class="form-group">
                                    <label class="de-label">Send To (recipient group)</label>
                                    @php $recipientGroup = old('recipient_group', $draftEmail->recipient_group ?? ''); @endphp
                                    <select name="recipient_group" id="recipientGroup" class="form-control de-input">
                                        <option value="" {{ $recipientGroup === '' ? 'selected' : '' }}>— None (this is just a content draft) —</option>
                                        <option value="fellows" {{ $recipientGroup === 'fellows' ? 'selected' : '' }}>Fellows</option>
                                        <option value="members" {{ $recipientGroup === 'members' ? 'selected' : '' }}>Members</option>
                                        <option value="trainees" {{ $recipientGroup === 'trainees' ? 'selected' : '' }}>Trainees</option>
                                        <option value="candidates" {{ $recipientGroup === 'candidates' ? 'selected' : '' }}>Candidates</option>
                                        <option value="programme_directors" {{ $recipientGroup === 'programme_directors' ? 'selected' : '' }}>Programme Directors</option>
                                        <option value="trainers" {{ $recipientGroup === 'trainers' ? 'selected' : '' }}>Trainers (ToT)</option>
                                        <option value="country_reps" {{ $recipientGroup === 'country_reps' ? 'selected' : '' }}>Country Representatives (one email per country)</option>
                                        <option value="examiners" {{ $recipientGroup === 'examiners' ? 'selected' : '' }}>Examiners</option>
                                        <option value="custom" {{ $recipientGroup === 'custom' ? 'selected' : '' }}>Custom list (type in or import emails)</option>
                                    </select>
                                    <div class="custom-control custom-checkbox mt-2" id="ccBox">
                                        @php $cc = old('cc_personal_email', $draftEmail->cc_personal_email ?? true); @endphp
                                        <input type="checkbox" class="custom-control-input" id="ccPersonal" name="cc_personal_email" value="1" {{ $cc ? 'checked' : '' }}>
                                        <label class="custom-control-label small" for="ccPersonal">CC each recipient's personal login email (where on file)</label>
                                    </div>

                                    {{-- ── Custom list: type in or import ── --}}
                                    @php
                                        $customLines = old('custom_recipients_text');
                                        if ($customLines === null) {
                                            $customLines = collect($draftEmail->custom_recipients ?? [])
                                                ->map(fn ($r) => trim(($r['name'] ?? '') . ' <' . ($r['email'] ?? '') . '>'))
                                                ->implode("\n");
                                        }
                                    @endphp
                                    <div id="customRecipientsBox" class="de-auto-box {{ $recipientGroup === 'custom' ? '' : 'd-none' }} mt-2">
                                        <label class="small mb-1">
                                            One recipient per line — <code>Name &lt;email@example.com&gt;</code> or just an email.
                                        </label>
                                        <textarea name="custom_recipients_text" id="customRecipientsText" class="form-control de-input" rows="6"
                                                  placeholder="Jane Doe <jane@example.com>&#10;john@example.com">{{ $customLines }}</textarea>
                                        <div class="d-flex align-items-center mt-2" style="gap:.6rem;">
                                            <label class="btn btn-sm btn-outline-secondary mb-0" for="customRecipientsFile">
                                                <i class="fas fa-file-import mr-1"></i> Import CSV (name, email)
                                            </label>
                                            <input type="file" id="customRecipientsFile" accept=".csv,text/csv" class="d-none">
                                            <small class="text-muted" id="customImportStatus"></small>
                                        </div>
                                    </div>
                                </div>

                                {{-- ── Manual vs automatic ── --}}
                                <div class="form-group" id="sendModeGroup">
                                    <label class="de-label">Sending</label>
                                    @php $sendMode = old('send_mode', $draftEmail->send_mode ?? 'manual'); @endphp
                                    <div class="de-radio-row">
                                        <label class="de-radio">
                                            <input type="radio" name="send_mode" value="manual" {{ $sendMode === 'manual' ? 'checked' : '' }}>
                                            <span>Manual — I'll click "Send Now"</span>
                                        </label>
                                        <label class="de-radio">
                                            <input type="radio" name="send_mode" value="automatic" {{ $sendMode === 'automatic' ? 'checked' : '' }}>
                                            <span>Automatic — send when a condition is met</span>
                                        </label>
                                    </div>

                                    @php
                                        $trigger = old('automation_trigger', $draftEmail->automation_trigger ?? 'applications_threshold_per_country');
                                        $threshold = old('automation_threshold', $draftEmail->automation_threshold ?? 10);
                                    @endphp
                                    <div id="automationBox" class="de-auto-box {{ $sendMode === 'automatic' ? '' : 'd-none' }}">
                                        <div class="form-group mb-2">
                                            <label class="small mb-1">Condition</label>
                                            <select name="automation_trigger" class="form-control de-input">
                                                <option value="applications_threshold_per_country" {{ $trigger === 'applications_threshold_per_country' ? 'selected' : '' }}>
                                                    Applications for a country exceed a threshold (Salesforce)
                                                </option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label class="small mb-1">Threshold (applications per country)</label>
                                            <input type="number" min="1" name="automation_threshold" class="form-control de-input" value="{{ $threshold }}">
                                            <small class="text-muted">Checked hourly. Each country only ever triggers this draft once.</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <a href="{{ url('admin/draft-emails') }}" class="btn btn-light border">Cancel</a>
                                    <button type="submit" class="btn de-save-btn">
                                        <i class="fas fa-save mr-1"></i> Save Draft
                                    </button>
                                </div>
                            </form>

                            @if($draftEmail)
                            <form method="POST" action="{{ url('admin/draft-emails/'.$draftEmail->id.'/send-now') }}"
                                  onsubmit="return confirm('Send this draft to every recipient in the selected group right now?');" class="mt-3 pt-3 border-top">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm" {{ $recipientGroup === '' ? 'disabled' : '' }}>
                                    <i class="fas fa-paper-plane mr-1"></i> Send Now to {{ $recipientGroup === 'country_reps' ? 'all Country Representatives' : 'recipient group' }}
                                </button>
                                @if($recipientGroup === '')
                                <small class="text-muted d-block mt-1">Select a recipient group above and save first.</small>
                                @endif
                            </form>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ── Preview column ── --}}
                <div class="col-lg-6">
                    <div class="de-preview-sticky">
                        <div class="card de-card-panel">
                            <div class="de-preview-header">
                                <div>
                                    <i class="fas fa-eye mr-2"></i>Live Preview
                                </div>
                                <div class="de-preview-toggle" role="group">
                                    <button type="button" class="active" data-width="580" title="Desktop">
                                        <i class="fas fa-desktop"></i>
                                    </button>
                                    <button type="button" data-width="360" title="Mobile">
                                        <i class="fas fa-mobile-alt"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="de-preview-frame-wrap">
                                <iframe id="previewFrame" style="border:none;" srcdoc=""></iframe>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/summernote/summernote-bs4.min.css') }}">
<style>
    .de-hero { background:linear-gradient(135deg, #a02626, #7a1f1f); border-radius:10px; padding:24px 26px; color:#fff; margin-bottom:1.2rem; }
    .de-hero h4 { font-weight:700; margin:0 0 4px; }
    .de-hero .meta { font-size:.85rem; opacity:.9; }
    .de-hero code { background:rgba(255,255,255,.18); color:#fff; padding:1px 6px; border-radius:4px; }
    .de-hero .btn-new { background:rgba(255,255,255,.15); color:#fff; border:1px solid rgba(255,255,255,.35); }
    .de-hero .btn-new:hover { background:#fff; color:#a02626; }

    .de-card-panel { border:none; border-radius:12px; box-shadow:0 1px 4px rgba(0,0,0,.06); }
    .de-label { font-size:.78rem; font-weight:700; text-transform:uppercase; letter-spacing:.03em; color:#a02626; }
    .de-input { border-radius:8px; border:1px solid #e2d3d3; }
    .de-input:focus { border-color:#a02626; box-shadow:0 0 0 .15rem rgba(160,38,38,.15); }

    .de-insert-btn { background:#fdeeee; color:#a02626; border:1px solid #f0d5d5; border-radius:20px; font-size:.72rem; padding:3px 12px; }
    .de-insert-btn:hover { background:#a02626; color:#fff; }

    .de-save-btn { background:#a02626; color:#fff; border-radius:8px; padding:8px 22px; font-weight:600; }
    .de-save-btn:hover { background:#7a1f1f; color:#fff; }

    .note-editor.note-frame { border: 1px solid #e2d3d3; border-radius: 8px; overflow:hidden; }
    .note-toolbar { background: #fbf5f5; border-bottom: 1px solid #e2d3d3; }
    code { background:#f0f0f0; padding:1px 5px; border-radius:3px; font-size:.85em; color:#a02626; }

    .de-preview-sticky { position:sticky; top:16px; }
    .de-preview-header {
        display:flex; justify-content:space-between; align-items:center;
        padding:14px 18px; font-weight:700; color:#222; border-bottom:1px solid #f0e5e5;
    }
    .de-preview-toggle { display:flex; background:#f4f0f0; border-radius:8px; padding:2px; }
    .de-preview-toggle button {
        border:none; background:none; color:#999; width:32px; height:28px; border-radius:6px; font-size:.8rem;
    }
    .de-preview-toggle button.active { background:#fff; color:#a02626; box-shadow:0 1px 3px rgba(0,0,0,.1); }

    .de-preview-frame-wrap { background:#eef0f2; border-radius:0 0 12px 12px; padding:18px; display:flex; justify-content:center; }
    #previewFrame { width:580px; height:600px; max-width:100%; background:#fff; border-radius:8px; box-shadow:0 2px 10px rgba(0,0,0,.08); transition:width .2s; }

    body.dark-mode .de-card-panel { background:#1f2937; }
    body.dark-mode .de-input { background:#111827; border-color:#374151; color:#e5e7eb; }
    body.dark-mode .de-preview-header { color:#e5e7eb; border-color:#374151; }
    body.dark-mode .de-preview-toggle { background:#111827; }
    body.dark-mode .de-preview-frame-wrap { background:#0f172a; }
    body.dark-mode .note-editor.note-frame { border-color:#374151; }
    body.dark-mode .note-toolbar { background:#111827; }

    /* ── Delivery settings ── */
    .de-radio-row { display:flex; flex-wrap:wrap; gap:10px; margin-top:4px; }
    .de-radio {
        display:flex; align-items:center; gap:6px; font-size:.85rem; font-weight:500; color:#444;
        background:#f9f6f6; border:1px solid #eee; border-radius:8px; padding:8px 14px; cursor:pointer; margin:0;
        transition:border-color .15s, background .15s;
    }
    .de-radio input { accent-color:#a02626; margin:0; }
    .de-radio:has(input:checked) { border-color:#a02626; background:#fdeeee; color:#a02626; }
    .de-chip-list { display:flex; flex-wrap:wrap; gap:6px; margin-top:10px; max-height:160px; overflow-y:auto; padding:2px; }
    .de-chip {
        display:flex; align-items:center; gap:6px; font-size:.8rem; background:#f4f4f4; border:1px solid #e7e7e7;
        border-radius:20px; padding:5px 12px; cursor:pointer; margin:0;
    }
    .de-chip input { accent-color:#a02626; margin:0; }
    .de-chip:has(input:checked) { background:#fdeeee; border-color:#e8c5c5; color:#a02626; font-weight:600; }
    .de-auto-box { background:#fbf7f7; border:1px solid #f0e0e0; border-radius:10px; padding:14px 16px; margin-top:10px; }

    body.dark-mode .de-radio { background:#111827; border-color:#374151; color:#d1d5db; }
    body.dark-mode .de-radio:has(input:checked) { border-color:#f87171; background:#3a1f1f; color:#f87171; }
    body.dark-mode .de-chip { background:#111827; border-color:#374151; color:#d1d5db; }
    body.dark-mode .de-chip:has(input:checked) { background:#3a1f1f; border-color:#7a1f1f; color:#f87171; }
    body.dark-mode .de-auto-box { background:#111827; border-color:#374151; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
<script>
$(function () {

    // ── Summernote editor ─────────────────────────────────────────────────────
    $('#bodyEditor').summernote({
        height: 320,
        toolbar: [
            ['style',  ['bold', 'italic', 'underline', 'clear']],
            ['font',   ['strikethrough']],
            ['para',   ['ul', 'ol', 'paragraph']],
            ['insert', ['link', 'hr']],
            ['view',   ['codeview']],
        ],
        callbacks: {
            onChange: function () { updatePreview(); }
        }
    });
    $('#subjectInput').on('input', updatePreview);

    // ── Visibility / recipient / automation toggles ─────────────────────────
    $('input[name=visibility]').on('change', function () {
        $('#visibleUsersBox').toggleClass('d-none', $(this).val() !== 'selected');
    });
    $('input[name=send_mode]').on('change', function () {
        $('#automationBox').toggleClass('d-none', $(this).val() !== 'automatic');
    });
    $('#recipientGroup').on('change', function () {
        $('#customRecipientsBox').toggleClass('d-none', $(this).val() !== 'custom');
    });

    // ── Import CSV (name, email — either column order, with or without a
    //    header row) — parsed client-side and appended as "Name <email>"
    //    lines into the same textarea used for manually-typed recipients,
    //    so both paths feed the one field the form actually submits.
    document.getElementById('customRecipientsFile').addEventListener('change', function (e) {
        var file = e.target.files[0];
        if (!file) return;
        var status = document.getElementById('customImportStatus');
        var reader = new FileReader();
        reader.onload = function () {
            var lines = String(reader.result).split(/\r?\n/).filter(function (l) { return l.trim() !== ''; });
            var emailRe = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            var added = 0;
            var out = [];
            lines.forEach(function (line) {
                var cols = line.split(',').map(function (c) { return c.trim().replace(/^"|"$/g, ''); });
                var email = cols.find(function (c) { return emailRe.test(c); });
                if (!email) return; // skip header row / junk lines
                var name = cols.find(function (c) { return c && c !== email; }) || '';
                out.push((name ? name + ' ' : '') + '<' + email + '>');
                added++;
            });
            var $ta = $('#customRecipientsText');
            var existing = $ta.val().trim();
            $ta.val((existing ? existing + '\n' : '') + out.join('\n'));
            status.textContent = added + ' recipient(s) imported.';
            $('#recipientGroup').val('custom').trigger('change');
        };
        reader.readAsText(file);
    });

    $('#insertNameBtn').on('click', function () {
        $('#bodyEditor').summernote('editor.focus');
        $('#bodyEditor').summernote('editor.insertText', '[Name]');
        updatePreview();
    });

    // ── Desktop / mobile preview toggle ─────────────────────────────────────
    $('.de-preview-toggle button').on('click', function () {
        $('.de-preview-toggle button').removeClass('active');
        $(this).addClass('active');
        $('#previewFrame').css('width', $(this).data('width') + 'px');
    });

    // ── Live preview ─────────────────────────────────────────────────────────
    var headerHtml = `
      <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#a02626;">
        <tr>
          <td style="padding:18px 28px; vertical-align:middle; width:76px;">
            <img src="{{ asset('dist/img/Cosecsa_Logo.png') }}" width="56" height="56" style="display:block; border:0;">
          </td>
          <td style="padding:18px 12px 18px 0; vertical-align:middle;">
            <div style="color:#fff; font-family:Arial,sans-serif;">
              <div style="font-size:18px; font-weight:700;">COSECSA</div>
              <div style="font-size:11px; color:#f5c6c6; margin-top:2px;">College of Surgeons of East, Central and Southern Africa</div>
            </div>
          </td>
        </tr>
      </table>
      <div style="height:4px; background:#FEC503;"></div>`;

    var footerHtml = `
      <div style="background:#f9f9f9; border-top:1px solid #e8e8e8; padding:20px 28px; font-family:Arial,sans-serif;">
        <table cellpadding="0" cellspacing="0" border="0"><tr>
          <td style="width:44px; vertical-align:middle; padding-right:10px;">
            <img src="{{ asset('dist/img/Cosecsa_Logo.png') }}" width="34" height="34" style="display:block;">
          </td>
          <td style="vertical-align:middle; font-size:13px; font-weight:700; color:#a02626;">
            COSECSA
          </td>
        </tr></table>
        <hr style="border:none; border-top:1px solid #e0e0e0; margin:10px 0;">
        <p style="font-size:11px; color:#888; margin:0; line-height:1.6;">
          College of Surgeons of East, Central and Southern Africa<br>
          Email: <a href="mailto:{{ config('mail.from.address') }}" style="color:#a02626;">{{ config('mail.from.address') }}</a>
          &nbsp;|&nbsp; Web: <a href="https://www.cosecsa.org" style="color:#a02626;">www.cosecsa.org</a>
        </p>
      </div>`;

    function esc(s) {
        return $('<div>').text(s || '').html();
    }

    function updatePreview() {
        var subject = ($('#subjectInput').val() || '(no subject)').replace(/\[Name\]/g, 'Dr. Example');
        var body = $('#bodyEditor').summernote('code')
                        .replace(/\[Name\]/g, '<strong>Dr. Example</strong>')
                        .replace(/\{\{number\}\}/g, '<strong>25</strong>');

        var mailHead = `
          <div style="max-width:580px; margin:0 auto; background:#fff; border:1px solid #e6e6e6;
                      border-bottom:none; border-radius:8px 8px 0 0; font-family:Arial,sans-serif; overflow:hidden;">
            <div style="padding:10px 16px; border-bottom:1px solid #f0f0f0; font-size:12px; color:#888;">
              <strong style="color:#444;">From:</strong> {{ Auth::user()?->name ?? 'Secretariat' }} via COSECSA &lt;{{ config('mail.from.address') }}&gt;
            </div>
            <div style="padding:10px 16px; border-bottom:1px solid #f0f0f0; font-size:12px; color:#888;">
              <strong style="color:#444;">Reply-To:</strong> {{ Auth::user()?->email ?? config('mail.from.address') }}
            </div>
            <div style="padding:10px 16px; border-bottom:1px solid #f0f0f0; font-size:12px; color:#888;">
              <strong style="color:#444;">To:</strong> Dr. Example &lt;dr.example@email.com&gt;
            </div>
            <div style="padding:12px 16px;">
              <div style="font-size:10px; color:#999; text-transform:uppercase; letter-spacing:.04em;">Subject</div>
              <div style="font-size:14px; font-weight:600; color:#2d2d2d;">${esc(subject)}</div>
            </div>
          </div>`;

        var full = `<!DOCTYPE html><html><head><meta charset="UTF-8">
          <style>
            body { margin:0; background:#f4f4f4; font-family:Arial,sans-serif; }
            .container { max-width:580px; margin:0 auto 16px; background:#fff; border-radius:0 0 8px 8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.1); }
            .body { padding:28px 28px 20px; color:#2d2d2d; font-size:14px; line-height:1.7; }
            .body p { margin:0 0 12px; }
          </style></head><body>
          ${mailHead}
          <div class="container">
            ${headerHtml}
            <div class="body">${body}</div>
            ${footerHtml}
          </div></body></html>`;

        document.getElementById('previewFrame').srcdoc = full;
    }

    updatePreview(); // Initial render
});
</script>
@endpush
