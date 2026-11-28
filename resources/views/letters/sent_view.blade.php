@extends('layout.app')

@section('content')
  @php
    $canManage = Auth::user()->hasPermission('letters.manage');
    $editor = $letter['editor'] ?? null;
    $original = $letter['original'] ?? null;
    $canEdit = $canManage && $editor;
    $hasEdits = !empty($letter['has_edits']);
    $hasWriting = !empty($letter['has_annotation']);
  @endphp
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-7">
            <h1 style="font-size:1.4rem;">{{ $letter['letter'] }}</h1>
            <p class="text-muted mb-0" style="font-size:.85rem;">
              {{ $letter['recipient']['name'] }} · sent {{ $letter['sent_at'] ? \Carbon\Carbon::parse($letter['sent_at'])->format('d M Y, H:i') : '' }}
              @if($letter['sent_to']) to {{ $letter['sent_to'] }}@endif · letter dated {{ \Carbon\Carbon::parse($letter['letter_date'])->format('d M Y') }}
            </p>
          </div>
          <div class="col-sm-5 text-right">
            <a href="javascript:history.back()" class="btn btn-cosecsa-outline"><i class="fas fa-arrow-left mr-1"></i> Back</a>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        @include('_message')

        <div class="alert alert-info py-2" style="font-size:.85rem;">
          <i class="fas fa-info-circle mr-1"></i>
          This letter is shown with the person's <strong>current</strong> details, so a correction made on their profile appears here and in the PDF.
          @if(($letter['source'] ?? '') === 'trainees')
            <a href="{{ url('admin/associates/trainees/edit/' . $letter['recipient_id']) }}" class="alert-link">Edit the trainee profile</a>.
          @endif
        </div>

        @if($hasEdits)
          <div class="alert alert-warning py-2" style="font-size:.85rem;">
            <i class="fas fa-edit mr-1"></i>
            This letter was edited{{ !empty($letter['edited_at']) ? ' on ' . \Carbon\Carbon::parse($letter['edited_at'])->format('d M Y, H:i') : '' }}. Downloads and <strong>Send again</strong> use the edited letter{{ !empty($letter['has_original']) ? '; the PDF as it was e-mailed is still available' : '' }}.
          </div>
        @endif

        @if($hasWriting)
          <div class="alert alert-success py-2" style="font-size:.85rem;">
            <i class="fas fa-pen mr-1"></i>
            Writing saved on the PDF{{ !empty($letter['annotated_at']) ? ' on ' . \Carbon\Carbon::parse($letter['annotated_at'])->format('d M Y, H:i') : '' }}. <strong>Download PDF with writing</strong> and <strong>Send again</strong> use that written-on PDF.
          </div>
        @endif

        <div class="d-flex flex-wrap justify-content-end mb-2" style="gap:.4rem;">
          <a class="btn btn-sm btn-outline-secondary sl-saved-only" href="{{ url('admin/letters/sent/' . $id . '/pdf') }}"><i class="fas fa-file-pdf mr-1"></i> Download PDF (current details)</a>
          @if(!empty($letter['has_original']))
            <a class="btn btn-sm btn-outline-secondary sl-saved-only" href="{{ url('admin/letters/sent/' . $id . '/download') }}" title="{{ $hasWriting ? 'The PDF with the writing saved on it' : 'The PDF exactly as it was e-mailed' }}"><i class="fas fa-download mr-1"></i> {{ $hasWriting ? 'Download PDF with writing' : 'Original PDF as sent' }}</a>
          @endif
          @if($hasWriting)
            <a class="btn btn-sm btn-outline-secondary" href="{{ url('admin/letters/sent/' . $id . '/original') }}" title="The PDF exactly as it was e-mailed, before any writing"><i class="fas fa-file mr-1"></i> Original before writing</a>
          @endif
          @if($canManage)
            <button type="button" class="btn btn-sm btn-outline-primary sl-saved-only" id="btnWritePdf"><i class="fas fa-pen mr-1"></i> Write on PDF</button>
            <button type="button" class="btn btn-sm btn-cosecsa sl-saved-only" id="btnResend"><i class="fas fa-paper-plane mr-1"></i> Send again</button>
          @endif
        </div>

        <div class="sl-split">
          @if($canEdit)
            <div class="sl-panel sl-edit">
              <div class="sl-head"><span>Edit &amp; format</span><span class="text-muted">Changes this letter only, not the template</span></div>
              <div class="sl-body">
                <div class="form-group">
                  <label for="slDate">Date on the letter</label>
                  <input type="date" id="slDate" class="form-control sl-field" style="max-width:200px;" value="{{ $editor['letter_date'] }}">
                </div>

                <div class="form-group">
                  <label for="slTo">&ldquo;To&rdquo; section <span class="text-muted font-weight-normal">(one line each; the first line is bold)</span></label>
                  <textarea id="slTo" class="form-control sl-field" rows="4" maxlength="1000">{{ $editor['recipient_block'] }}</textarea>
                </div>

                <div class="form-group">
                  <label for="slBody">Letter body <span class="text-muted font-weight-normal">(select text to make it bold, change size, colour or alignment)</span></label>
                  <div class="mb-1">
                    <span class="small text-muted mr-1">Insert at the cursor:</span>
                    @foreach(['name','first_name','email','country','programme','hospital','entry_number','exam_year','admission_year','date'] as $mf)
                      <button type="button" class="sl-chip" data-field="{{ $mf }}">&#123;&#123;{{ $mf }}&#125;&#125;</button>
                    @endforeach
                  </div>
                  <textarea id="slBody" class="form-control">{{ $editor['pdf_body'] }}</textarea>
                  <small class="text-muted">Fields in double braces are filled from the person's profile. Replace one with your own words if this letter should say something else.</small>
                </div>

                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label for="slSigName">Signatory name</label>
                    <input type="text" id="slSigName" class="form-control sl-field" maxlength="255" placeholder="Blank: the person sending" value="{{ $editor['signatory_name'] }}">
                  </div>
                  <div class="form-group col-md-6">
                    <label for="slSigTitle">Signatory role</label>
                    <input type="text" id="slSigTitle" class="form-control sl-field" maxlength="255" placeholder="Blank: the sender's role" value="{{ $editor['signatory_title'] }}">
                  </div>
                </div>

                @if(!empty($letter['has_stamp']))
                  <p class="small mb-0"><i class="fas fa-arrows-alt mr-1"></i> Drag the stamp in the preview to move it. <a href="#" id="slStampReset">Put it back</a></p>
                @endif
              </div>
              <div class="sl-foot">
                <button type="button" class="btn btn-sm btn-cosecsa" id="slSave" disabled><i class="fas fa-save mr-1"></i> Save changes</button>
                <button type="button" class="btn btn-sm btn-light" id="slDiscard" disabled>Discard unsaved changes</button>
                @if($hasEdits)
                  <button type="button" class="btn btn-sm btn-link ml-auto" id="slReset">Use the template wording again</button>
                @endif
                <div id="slMsg" class="small text-muted w-100" role="status" style="min-height:1.2em;"></div>
              </div>
            </div>
          @endif

          <div class="sl-panel sl-preview">
            <div class="sl-head">
              <ul class="nav nav-pills" id="sentTabs">
                <li class="nav-item"><a class="nav-link active py-1" href="#sentLetterPane" data-toggle="pill">Letter preview</a></li>
                <li class="nav-item"><a class="nav-link py-1" href="#sentEmailPane" data-toggle="pill">E-mail</a></li>
              </ul>
              @if($canEdit)<span class="text-muted">Updates as you type</span>@endif
            </div>
            <div class="tab-content">
              <div class="tab-pane fade show active" id="sentLetterPane">
                <iframe id="slFrame" class="sl-frame" title="Letter preview" sandbox="allow-scripts" srcdoc="{{ $letter['letter_html'] }}"></iframe>
              </div>
              <div class="tab-pane fade" id="sentEmailPane">
                <div class="px-3 pt-3 small text-muted">
                  To: <strong>{{ $letter['sent_to'] ?: ($letter['recipient']['email'] ?: '—') }}</strong><br>
                  Subject: <strong>{{ $letter['subject'] }}</strong><br>
                  Attached: the letter as a PDF
                </div>
                <iframe class="sl-frame" title="E-mail" sandbox srcdoc="{{ $letter['email_html'] }}" style="background:#fff;"></iframe>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  @include('letters._resend_modal')
  @if($canManage)
    @include('letters._pdf_write_modal')
  @endif
@endsection

@push('styles')
<style>
  .sl-split { display: flex; gap: 16px; align-items: flex-start; padding-bottom: 16px; }
  .sl-panel { min-width: 0; background: #fff; border: 1px solid #e3e6ea; border-radius: 6px; }
  .sl-edit { flex: 1 1 48%; }
  .sl-preview { flex: 1 1 52%; position: sticky; top: 70px; overflow: hidden; }
  .sl-head { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px; padding: 8px 14px; font-size: 13px; font-weight: 700; border-bottom: 1px solid #eef0f3; background: #f8fafc; }
  .sl-head .text-muted { font-weight: 400; font-size: 12px; }
  .sl-body { padding: 14px; }
  .sl-foot { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; padding: 10px 14px; border-top: 1px solid #eef0f3; background: #fff; position: sticky; bottom: 0; z-index: 5; border-radius: 0 0 6px 6px; }
  .sl-frame { display: block; width: 100%; height: calc(100vh - 160px); min-height: 520px; border: 0; background: #e9ecef; }
  .sl-chip { border: 1px solid #ced4da; background: #fff; border-radius: 12px; padding: 0 8px; margin: 2px 2px 0 0; font: 12px/20px monospace; cursor: pointer; }
  .sl-chip:hover { background: #fdf6dd; border-color: #F0C129; }
  .sl-edit .note-editable { font-family: Arial, sans-serif; font-size: 14px; line-height: 1.5; }
  body.dark-mode .sl-panel, body.dark-mode .sl-foot { background: #343a40; border-color: #4b545c; }
  body.dark-mode .sl-head { background: #3b4658; border-color: #4b545c; }
  body.dark-mode .sl-chip { background: #3b4658; border-color: #4b545c; color: #e2e8f0; }
  @media (max-width: 1100px) { .sl-split { flex-direction: column; } .sl-edit, .sl-preview { flex: none; width: 100%; position: static; } .sl-frame { height: 70vh; } }
</style>
@endpush

@push('scripts')
<script>
  var btn = document.getElementById('btnResend');
  if (btn) btn.addEventListener('click', function () { openLetterResend({{ (int) $id }}, @json($letter['recipient']['email'] ?? ''), function () { }, @json($hasEdits && $editor && $editor['letter_date'] !== $original['letter_date'] ? $editor['letter_date'] : null)); });

  var writeBtn = document.getElementById('btnWritePdf');
  if (writeBtn) writeBtn.addEventListener('click', function () { openLetterWrite(); });
</script>
@if($canEdit)
<script>
$(function () {
  var previewUrl = @json(url('admin/letters/sent/' . $id . '/preview'));
  var saveUrl = @json(url('admin/letters/sent/' . $id . '/edits'));
  var csrf = @json(csrf_token());
  var originalStamp = @json($original['stamp_pos']);
  var stampPos = @json($editor['stamp_pos']);
  var hasWriting = @json($hasWriting);
  var $body = $('#slBody'), frame = document.getElementById('slFrame');
  var saveBtn = document.getElementById('slSave'), discardBtn = document.getElementById('slDiscard'), msg = document.getElementById('slMsg');
  var timer = null, seq = 0, scrollY = 0, saved = null, leaving = false;

  function say(text, bad) {
    msg.className = 'small w-100 ' + (bad ? 'text-danger' : 'text-muted');
    msg.textContent = text;
  }

  function payload() {
    return {
      letter_date: $('#slDate').val(),
      recipient_block: $('#slTo').val(),
      pdf_body: $body.summernote('code'),
      signatory_name: $('#slSigName').val(),
      signatory_title: $('#slSigTitle').val(),
      stamp_pos: stampPos
    };
  }

  function dirty() { return saved !== null && JSON.stringify(payload()) !== saved; }

  function post(url, data) {
    return fetch(url, {
      method: 'POST', credentials: 'same-origin', body: JSON.stringify(data),
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    }).then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); });
  }

  function refresh() {
    var my = ++seq;
    post(previewUrl, payload()).then(function (res) {
      if (my !== seq) return;
      if (!res.ok || !res.d.html) { say((res.d && res.d.message) || 'The preview could not be updated.', true); return; }
      // the new page goes back to where the old one was scrolled to
      frame.srcdoc = res.d.html.replace('</body>', '<script>window.scrollTo(0, ' + Math.round(scrollY) + ');<\/script></body>');
      say(dirty() ? 'Unsaved changes.' : '');
    }).catch(function () { if (my === seq) say('The preview could not be updated. Check your connection.', true); });
  }

  function changed() {
    var d = dirty();
    saveBtn.disabled = discardBtn.disabled = !d;
    say(d ? 'Unsaved changes.' : '');
    clearTimeout(timer);
    timer = setTimeout(refresh, 400);
  }

  $body.summernote({
    height: 340,
    dialogsInBody: true,
    fontSizes: ['9', '10', '11', '12', '14', '16', '18', '20', '24'],
    toolbar: [
      ['style',  ['style']],
      ['font',   ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
      ['fontsize', ['fontsize']],
      ['color',  ['color']],
      ['para',   ['ul', 'ol', 'paragraph']],
      ['insert', ['link', 'table', 'hr']],
      ['view',   ['undo', 'redo', 'codeview']]
    ],
    styleTags: ['p', 'h2', 'h3', 'h4', 'blockquote'],
    callbacks: { onChange: changed, onKeyup: changed, onChangeCodeview: changed }
  });
  saved = JSON.stringify(payload());

  $('.sl-field').on('input change', changed);

  $('.sl-chip').on('click', function () {
    $body.summernote('focus');
    // the braces are built apart so Blade does not read them as its own
    $body.summernote('editor.insertText', '{'.repeat(2) + $(this).data('field') + '}'.repeat(2));
  });

  // the preview (a sandboxed page) reports where it is scrolled to and where the stamp was dragged
  window.addEventListener('message', function (e) {
    if (e.source !== frame.contentWindow || !e.data) return;
    if (e.data.type === 'letter-scroll') scrollY = Number(e.data.y) || 0;
    if (e.data.type === 'letter-stamp-pos') { stampPos = Math.round(e.data.x) + ',' + Math.round(e.data.y); changed(); }
  });
  $('#slStampReset').on('click', function (e) { e.preventDefault(); stampPos = originalStamp; changed(); });

  function keep(data) {
    saveBtn.disabled = true;
    say('Saving…');
    post(saveUrl, data).then(function (res) {
      if (!res.ok) { saveBtn.disabled = false; say((res.d && res.d.message) || 'Your changes could not be saved.', true); return; }
      leaving = true;
      window.location.reload();
    }).catch(function () { saveBtn.disabled = false; say('No connection. Your changes were not saved.', true); });
  }

  var writingWarning = '\n\nThe writing saved on the PDF was made on the earlier version, so it will be removed.';

  saveBtn.addEventListener('click', function () {
    var text = (new DOMParser().parseFromString($body.summernote('code'), 'text/html').body.textContent || '').replace(/\s+/g, '');
    if (!text) { say('The letter body is empty.', true); $body.summernote('focus'); return; }
    if (hasWriting && !window.confirm('Save these changes to the letter?' + writingWarning)) return;
    keep(payload());
  });

  discardBtn.addEventListener('click', function () {
    if (window.confirm('Discard the changes you have not saved?')) { leaving = true; window.location.reload(); }
  });

  $('#slReset').on('click', function () {
    if (window.confirm('Remove all changes made to this letter and use the template wording again?' + (hasWriting ? writingWarning : ''))) keep({ reset: true });
  });

  // downloads, writing and sending use the saved letter, so unsaved changes are saved (or discarded) first
  document.querySelectorAll('.sl-saved-only').forEach(function (el) {
    el.addEventListener('click', function (e) {
      if (!dirty()) return;
      e.preventDefault();
      e.stopImmediatePropagation();
      window.alert('You have unsaved changes. Save them (or discard them) first, so the PDF matches what you see.');
    }, true);
  });

  window.addEventListener('beforeunload', function (e) {
    if (!leaving && dirty()) { e.preventDefault(); e.returnValue = ''; }
  });
});
</script>
@endif
@endpush
