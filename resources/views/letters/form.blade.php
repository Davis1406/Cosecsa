@extends('layout.app')

@section('content')
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-6"><h1 style="font-size:1.4rem;">{{ $header_title }}</h1></div>
          <div class="col-sm-6 text-right">
            <a href="{{ url('admin/letters') }}" class="btn btn-cosecsa-outline"><i class="fas fa-arrow-left mr-1"></i> Back</a>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        @include('_message')

        <div class="lt-split">
        <div class="lt-left">
        <div class="card">
          <div class="card-body">
            <form method="POST" id="letterForm" enctype="multipart/form-data" action="{{ $template ? url('admin/letters/'.$template->id.'/update') : url('admin/letters') }}">
              @csrf
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label>Template Name</label>
                  <input type="text" name="name" class="form-control" value="{{ old('name', $template->name ?? '') }}" required>
                </div>
                <div class="form-group col-md-6">
                  <label>Recipient Source</label>
                  <select name="recipient_source" class="form-control" required>
                    @foreach($sources as $key => $label)
                      <option value="{{ $key }}" {{ old('recipient_source', $template->recipient_source ?? '') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                  </select>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group col-md-6">
                  <label>Email Subject</label>
                  <input type="text" name="subject" class="form-control" value="{{ old('subject', $template->subject ?? '') }}" required>
                </div>
                <div class="form-group col-md-6">
                  <label>Linked Trainee Status (optional)</label>
                  <select name="legacy_status_field" class="form-control">
                    <option value="">— None —</option>
                    <option value="admission_letter_status" {{ old('legacy_status_field', $template->legacy_status_field ?? '') == 'admission_letter_status' ? 'selected' : '' }}>Admission Letter Status</option>
                    <option value="invitation_letter_status" {{ old('legacy_status_field', $template->legacy_status_field ?? '') == 'invitation_letter_status' ? 'selected' : '' }}>Invitation Letter Status</option>
                  </select>
                  <small class="text-muted">If set, sending this letter (to a trainee) also flips that trainee's status to "Sent".</small>
                </div>
              </div>

              <div class="alert alert-light border" style="font-size:.82rem;">
                <strong>Merge fields</strong> <span class="text-muted">&mdash; click one to put it into the letter body at the cursor</span><br>
                @foreach(['name','first_name','email','country','programme','hospital','entry_number','exam_year','admission_year','sfs_username','sfs_password','date'] as $mf)
                  <button type="button" class="lt-chip" data-field="{{ $mf }}">&#123;&#123;{{ $mf }}&#125;&#125;</button>
                @endforeach
              </div>

              @php
                // Letters saved before the editor are plain text (line breaks only): show them in the editor with
                // the same line breaks. Anything already HTML is shown as it is.
                $rawBody = old('pdf_body', $template->pdf_body ?? '');
                $bodyForEditor = preg_match('/<\/?(p|br|b|strong|i|em|u|s|span|div|ul|ol|li|h[1-4]|a|table|hr|blockquote)\b[^>]*>/i', $rawBody)
                    ? $rawBody
                    : ($rawBody === '' ? '' : '<p>' . nl2br(e($rawBody)) . '</p>');
              @endphp
              <div class="form-group">
                <label for="recipientBlock">&ldquo;To&rdquo; section <span class="text-muted font-weight-normal">(the name and details above the letter; one line each. Leave empty for the standard block)</span></label>
                <textarea name="recipient_block" id="recipientBlock" class="form-control" rows="4" maxlength="1000" placeholder="Dr. &#123;&#123;name&#125;&#125;&#10;Email: &#123;&#123;email&#125;&#125;&#10;Country: &#123;&#123;country&#125;&#125;">{{ old('recipient_block', $template->recipient_block ?? '') }}</textarea>
                <div class="to-tools mt-1">
                  <span class="small text-muted mr-1">Drag a field into the box above (or click it to put it at the cursor):</span>
                  @foreach(['name' => 'Full name', 'first_name' => 'First name', 'email' => 'E-mail', 'country' => 'Country', 'programme' => 'Programme', 'hospital' => 'Hospital', 'entry_number' => 'Entry number', 'admission_year' => 'Admission year', 'exam_year' => 'Exam year'] as $mf => $label)
                    <span class="lt-chip to-chip" draggable="true" role="button" tabindex="0" data-field="{{ $mf }}" title="{{ $label }}">&#123;&#123;{{ $mf }}&#125;&#125;</span>
                  @endforeach
                </div>
                <div class="to-tools mt-1">
                  <span class="small text-muted mr-1">Title before the name:</span>
                  @foreach(['Dr.', 'Prof.', 'Mr.', 'Ms.', 'Mrs.'] as $t)
                    <span class="lt-chip to-title" role="button" tabindex="0" data-title="{{ $t }}">{{ $t }}</span>
                  @endforeach
                  <a href="#" id="toReset" class="small ml-2">Back to the standard block</a>
                </div>
                <small class="text-muted d-block mt-1">The first line is shown in bold. A line whose detail a person does not have (no e-mail) is left out for that person. Add your own words around the fields, for example <em>Entry no. &#123;&#123;entry_number&#125;&#125;</em>.</small>
              </div>

              <div class="form-group">
                <label>Letter Body <span class="text-muted font-weight-normal">(rendered on the letterhead PDF &mdash; select text to make it bold, change size, colour or alignment)</span></label>
                <textarea name="pdf_body" id="pdfBody" class="form-control">{{ $bodyForEditor }}</textarea>
              </div>

              @php
                // Kept out of the inline Blade echo below — a literal
                // "{{first_name}}" merge-field token inside a {{ }} Blade
                // expression breaks the compiler, since it finds the first
                // "}}" it sees (right after "first_name") and truncates the
                // expression there.
                $defaultEmailBody = $template->email_body ?? ('Dear ' . '{{first_name}}' . ",\n\nPlease find attached your letter from COSECSA.");
              @endphp
              <div class="form-group">
                <label>Accompanying Email Body</label>
                <textarea name="email_body" class="form-control" rows="6" required>{{ old('email_body', $defaultEmailBody) }}</textarea>
              </div>

              <div class="card card-outline card-secondary mb-3" id="signatureCard">
                <div class="card-header py-2"><strong>Signature block</strong> <span class="text-muted small">&mdash; shown under &ldquo;Kind Regards,&rdquo;. Leave anything blank to sign as the person sending the letter.</span></div>
                <div class="card-body pb-1">
                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label>Name</label>
                      <input type="text" name="signatory_name" id="sigName" class="form-control" maxlength="255" placeholder="e.g. Mr. Davis Kondamwali" value="{{ old('signatory_name', $template->signatory_name ?? '') }}">
                    </div>
                    <div class="form-group col-md-6">
                      <label>Role</label>
                      <input type="text" name="signatory_title" id="sigTitle" class="form-control" maxlength="255" placeholder="e.g. Admission Assistant" value="{{ old('signatory_title', $template->signatory_title ?? '') }}">
                    </div>
                  </div>
                  <div class="form-row">
                    <div class="form-group col-md-6">
                      <label>Signature image</label>
                      @if(!empty($template->signature_path))
                        <div class="mb-2"><img src="{{ \App\Support\ApiAsset::url($template->signature_path) }}" style="height:46px;background:#f6f6f6;border:1px solid #e3e6ea;padding:2px;"></div>
                      @endif
                      <input type="file" name="signature" id="sigFile" class="form-control-file" accept="image/png,image/jpeg,image/webp">
                      <small class="text-muted d-block">PNG or JPG, up to 2&nbsp;MB. A white background is made transparent so it can sit over the stamp.</small>
                      @if(!empty($template->signature_path))
                        <div class="custom-control custom-checkbox mt-1"><input type="checkbox" class="custom-control-input" id="rmSig" name="remove_signature" value="1"><label class="custom-control-label small" for="rmSig">Remove the saved signature</label></div>
                      @endif
                    </div>
                    <div class="form-group col-md-6">
                      <label>Stamp</label>
                      @if(!empty($template->stamp_path))
                        <div class="mb-2"><img src="{{ \App\Support\ApiAsset::url($template->stamp_path) }}" style="height:60px;background:#f6f6f6;border:1px solid #e3e6ea;padding:2px;"></div>
                      @endif
                      <input type="file" name="stamp" id="stampFile" class="form-control-file" accept="image/png,image/jpeg,image/webp">
                      <small class="text-muted d-block">PNG with a transparent background works best, up to 2&nbsp;MB.</small>
                      <small class="d-block"><i class="fas fa-arrows-alt mr-1"></i>Drag the stamp in the preview to place it. <a href="#" id="resetStamp" class="{{ isset($template->stamp_x) ? '' : 'd-none' }}">Reset position</a></small>
                      <input type="hidden" name="stamp_pos" id="stampPos" value="{{ old('stamp_pos', isset($template->stamp_x, $template->stamp_y) ? $template->stamp_x . ',' . $template->stamp_y : '') }}">
                      @if(!empty($collegeStampAvailable))
                        <div class="custom-control custom-checkbox mt-1"><input type="checkbox" class="custom-control-input" id="useCollegeStamp" name="use_college_stamp" value="1"><label class="custom-control-label small" for="useCollegeStamp">Use the college stamp (the one already on the transcripts)</label></div>
                      @endif
                      @if(!empty($template->stamp_path))
                        <div class="custom-control custom-checkbox mt-1"><input type="checkbox" class="custom-control-input" id="rmStamp" name="remove_stamp" value="1"><label class="custom-control-label small" for="rmStamp">Remove the saved stamp</label></div>
                      @endif
                    </div>
                  </div>
                </div>
              </div>

              <div class="form-group form-check">
                <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active (selectable when dispatching)</label>
              </div>

              <button type="submit" class="btn btn-cosecsa">{{ $template ? 'Save Changes' : 'Create Template' }}</button>
            </form>
          </div>
        </div>
        </div>{{-- /lt-left --}}

        <div class="lt-right">
          <div class="lt-pv-head"><span>Live preview</span><span class="text-muted">The letter on the college letterhead &mdash; updates as you type</span></div>
          <iframe id="ltFrame" class="lt-frame" title="Live preview of the letter"></iframe>
        </div>
        </div>{{-- /lt-split --}}
      </div>
    </section>
  </div>
@endsection

@push('styles')
<style>
  .lt-split { display: flex; gap: 16px; align-items: flex-start; }
  .lt-left { flex: 1 1 52%; min-width: 0; }
  .lt-right { flex: 1 1 46%; min-width: 0; position: sticky; top: 70px; background: #fff; border: 1px solid #e3e6ea; border-radius: 6px; overflow: hidden; }
  .lt-pv-head { display: flex; justify-content: space-between; align-items: center; gap: 8px; padding: 8px 14px; font-size: 13px; font-weight: 700; border-bottom: 1px solid #eef0f3; background: #f8fafc; }
  .lt-pv-head .text-muted { font-weight: 400; font-size: 12px; }
  .lt-frame { display: block; width: 100%; height: calc(100vh - 190px); min-height: 520px; border: 0; background: #e9ecef; }
  .lt-chip { border: 1px solid #ced4da; background: #fff; border-radius: 12px; padding: 0 8px; margin: 2px 2px 0 0; font: 12px/20px monospace; cursor: pointer; }
  .lt-chip:hover { background: #fdf6dd; border-color: #F0C129; }
  .to-chip { display: inline-block; cursor: grab; user-select: none; }
  .to-chip:active { cursor: grabbing; }
  .to-title { display: inline-block; font-family: inherit; }
  #recipientBlock.to-over { border-color: #a02626; background: #fff8e1; box-shadow: 0 0 0 .15rem rgba(160,38,38,.15); }
  .note-editor.note-frame { border-color: #ced4da; }
  .note-editable { font-family: Arial, sans-serif; font-size: 14px; line-height: 1.5; }
  @media (max-width: 1100px) { .lt-split { flex-direction: column; } .lt-left, .lt-right { flex: none; width: 100%; position: static; } .lt-frame { height: 70vh; } }
</style>
@endpush

@push('scripts')
<script>
$(function () {
    var $body = $('#pdfBody'), frame = document.getElementById('ltFrame'), timer = null, seq = 0;
    var CSRF = $('meta[name="csrf-token"]').attr('content') || $('input[name=_token]').first().val();

    $body.summernote({
        height: 380,
        dialogsInBody: true,
        fontSizes: ['9', '10', '11', '12', '14', '16', '18', '20', '24'],
        toolbar: [
            ['style',  ['style']],
            ['font',   ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
            ['fontsize', ['fontsize']],
            ['color',  ['color']],
            ['para',   ['ul', 'ol', 'paragraph']],
            ['insert', ['link', 'hr']],
            ['view',   ['undo', 'redo', 'codeview']]
        ],
        styleTags: ['p', 'h2', 'h3', 'h4', 'blockquote'],
        callbacks: { onChange: schedule, onKeyup: schedule, onPaste: schedule }
    });

    function schedule() { clearTimeout(timer); timer = setTimeout(refresh, 400); }

    // a signature / stamp picked in the form but not saved yet is sent with the preview request as a data URI
    var picks = { signature: null, stamp: null };
    function readPick(input, key) {
        var f = input.files && input.files[0];
        picks[key] = null;
        if (f && /^image\/(png|jpeg|webp)$/.test(f.type) && f.size <= 2 * 1024 * 1024) {
            var r = new FileReader();
            r.onload = function () { picks[key] = r.result; schedule(); };
            r.readAsDataURL(f);
        } else {
            if (f) { alert('Please choose a PNG, JPG or WEBP image of up to 2 MB.'); input.value = ''; }
            schedule();
        }
    }
    $('#sigFile').on('change', function () { readPick(this, 'signature'); });
    $('#stampFile').on('change', function () { readPick(this, 'stamp'); });
    $('#sigName, #sigTitle, #rmSig, #rmStamp, #useCollegeStamp, #recipientBlock').on('input change', schedule);

    // the preview reports where the stamp was dragged to; keep it in the form (saved with the template) and re-draw
    window.addEventListener('message', function (e) {
        if (!frame || e.source !== frame.contentWindow || !e.data || e.data.type !== 'letter-stamp-pos') return;
        $('#stampPos').val(Math.round(e.data.x) + ',' + Math.round(e.data.y));
        $('#resetStamp').removeClass('d-none');
        schedule();
    });
    $('#resetStamp').on('click', function (ev) {
        ev.preventDefault();
        $('#stampPos').val('');
        $(this).addClass('d-none');
        schedule();
    });

    function previewPayload() {
        var tid = @json($template->id ?? null);
        return {
            pdf_body: $body.summernote('code'),
            recipient_block: $('#recipientBlock').val(),
            template_id: tid,
            signatory_name: $('#sigName').val(),
            signatory_title: $('#sigTitle').val(),
            signature_data: picks.signature,
            stamp_data: picks.stamp,
            clear_signature: $('#rmSig').is(':checked') ? 1 : 0,
            clear_stamp: $('#rmStamp').is(':checked') ? 1 : 0,
            use_college_stamp: $('#useCollegeStamp').is(':checked') ? 1 : 0,
            stamp_pos: $('#stampPos').val() || 'default'   // empty = the standard spot (and overrides a saved position in the preview)
        };
    }

    function refresh() {
        var my = ++seq;
        fetch(@json(route('admin.letters.live-preview')), {
            method: 'POST', credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: JSON.stringify(previewPayload())
        }).then(function (r) { return r.json(); }).then(function (d) {
            if (my !== seq || !d.html) return;
            var y = 0; try { y = frame.contentWindow.scrollY || 0; } catch (e) {}
            frame.onload = function () { try { frame.contentWindow.scrollTo(0, y); } catch (e) {} };
            frame.srcdoc = d.html;
        }).catch(function () {});
    }

    // "To" section: fields are dragged into the box (the browser drops the text where the pointer is) or clicked to go at the cursor
    var $to = $('#recipientBlock');
    function insertIntoTo(text) {
        var el = $to[0], a = el.selectionStart, b = el.selectionEnd, v = el.value;
        if (a == null) { a = b = v.length; }
        el.value = v.slice(0, a) + text + v.slice(b);
        el.focus(); el.selectionStart = el.selectionEnd = a + text.length;
        $to.trigger('input');
    }
    $('.to-chip').on('dragstart', function (ev) {
        var f = '\u007b\u007b' + $(this).data('field') + '\u007d\u007d';
        ev.originalEvent.dataTransfer.setData('text/plain', f); ev.originalEvent.dataTransfer.effectAllowed = 'copy';
    }).on('click keydown', function (ev) {
        if (ev.type === 'keydown' && ev.key !== 'Enter' && ev.key !== ' ') return;
        ev.preventDefault(); insertIntoTo('\u007b\u007b' + $(this).data('field') + '\u007d\u007d');
    });
    // (the browser itself drops the text where the pointer is: only the highlight is ours)
    $to.on('dragenter dragover', function () { $to.addClass('to-over'); }).on('dragleave drop dragend', function () { $to.removeClass('to-over'); });
    $('.to-title').on('click keydown', function (ev) {
        if (ev.type === 'keydown' && ev.key !== 'Enter' && ev.key !== ' ') return;
        ev.preventDefault();
        var t = $(this).data('title'), lines = ($to.val() || '').split('\n');
        if (!lines[0].trim()) lines[0] = '\u007b\u007bname\u007d\u007d';
        lines[0] = lines[0].replace(/^(Dr\.|Prof\.|Mr\.|Ms\.|Mrs\.)\s+/i, '');   // a title already there is replaced, not doubled
        lines[0] = t + ' ' + lines[0];
        $to.val(lines.join('\n')).trigger('input');
    });
    $('#toReset').on('click', function (ev) { ev.preventDefault(); $to.val('').trigger('input'); });

    // merge-field chips: insert at the cursor in the editor
    $('.lt-chip').not('.to-chip, .to-title').on('click', function () {
        $body.summernote('focus');
        $body.summernote('editor.insertText', '\u007b\u007b' + $(this).data('field') + '\u007d\u007d');
        schedule();
    });

    // the textarea is hidden by the editor, so check it here rather than with the browser's "required"
    $('#letterForm').on('submit', function (e) {
        var text = (new DOMParser().parseFromString($body.summernote('code'), 'text/html').body.textContent || '').replace(/\s+/g, '');
        if (!text) { e.preventDefault(); alert('The letter body is empty.'); $body.summernote('focus'); }
    });

    refresh();
});
</script>
@endpush
