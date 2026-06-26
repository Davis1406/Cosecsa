@extends('layout.app')

@section('content')
<div class="wrapper">
<div class="content-wrapper">

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-8">
                    <h4 class="mb-0">Examiner Email Template</h4>
                    <small class="text-muted">
                        This template is used when sending bulk emails to examiners.
                        Use <code>[Name]</code> anywhere to insert the recipient's name.
                    </small>
                </div>
                <div class="col-sm-4 text-right">
                    <a href="{{ url('admin/exams/examiners') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Examiners
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="col-md-12">@include('_message')</div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">

                {{-- ── Editor column ── --}}
                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-header" style="background:#a02626; color:#fff;">
                            <h3 class="card-title">
                                <i class="fas fa-edit mr-2"></i> Edit Template
                            </h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('exams.email.template.save') }}" id="templateForm">
                                @csrf

                                <div class="form-group">
                                    <label class="font-weight-bold">Subject Line</label>
                                    <input type="text" name="subject" class="form-control"
                                           value="{{ old('subject', $template->subject) }}" required>
                                    <small class="text-muted">You can change this before sending; this is just the default.</small>
                                </div>

                                <div class="form-group">
                                    <label class="font-weight-bold">
                                        Email Body
                                        <span class="badge badge-secondary ml-1" style="font-size:.72rem;">
                                            Use [Name] to personalise
                                        </span>
                                    </label>
                                    <textarea name="body" id="bodyEditor" class="form-control"
                                              rows="14">{{ old('body', $template->body) }}</textarea>
                                </div>

                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-save mr-1"></i> Save Template
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- ── Preview column ── --}}
                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-eye mr-2"></i> Live Preview</h3>
                            <div class="card-tools">
                                <small class="text-muted">[Name] shown as "Dr. Example"</small>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <iframe id="previewFrame"
                                    style="width:100%; height:560px; border:none; border-radius:0 0 4px 4px;"
                                    srcdoc=""></iframe>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ url('public/plugins/summernote/summernote-bs4.min.css') }}">
<style>
    .note-editor.note-frame { border: 1px solid #ced4da; border-radius: .25rem; }
    .note-toolbar { background: #f8f9fa; border-bottom: 1px solid #dee2e6; }
    code { background:#f0f0f0; padding:1px 5px; border-radius:3px; font-size:.85em; color:#a02626; }
</style>
@endpush

@push('scripts')
<script src="{{ url('public/plugins/summernote/summernote-bs4.min.js') }}"></script>
<script>
$(function () {

    // ── Summernote editor ─────────────────────────────────────────────────────
    $('#bodyEditor').summernote({
        height: 340,
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

    // ── Live preview ─────────────────────────────────────────────────────────
    var headerHtml = `
      <table width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#a02626;">
        <tr>
          <td style="padding:18px 28px; vertical-align:middle; width:76px;">
            <img src="{{ url('public/dist/img/Cosecsa_Logo.png') }}" width="56" height="56" style="display:block; border:0;">
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
            <img src="{{ url('public/dist/img/Cosecsa_Logo.png') }}" width="34" height="34" style="display:block;">
          </td>
          <td style="vertical-align:middle; font-size:13px; font-weight:700; color:#a02626;">
            COSECSA — Examinations Office
          </td>
        </tr></table>
        <hr style="border:none; border-top:1px solid #e0e0e0; margin:10px 0;">
        <p style="font-size:11px; color:#888; margin:0; line-height:1.6;">
          College of Surgeons of East, Central and Southern Africa<br>
          Email: <a href="mailto:{{ config('mail.from.address') }}" style="color:#a02626;">{{ config('mail.from.address') }}</a>
          &nbsp;|&nbsp; Web: <a href="https://www.cosecsa.org" style="color:#a02626;">www.cosecsa.org</a>
        </p>
      </div>`;

    function updatePreview() {
        var body = $('#bodyEditor').summernote('code')
                        .replace(/\[Name\]/g, '<strong>Dr. Example</strong>');

        var full = `<!DOCTYPE html><html><head><meta charset="UTF-8">
          <style>
            body { margin:0; background:#f4f4f4; font-family:Arial,sans-serif; }
            .container { max-width:580px; margin:16px auto; background:#fff; border-radius:6px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,.1); }
            .body { padding:28px 28px 20px; color:#2d2d2d; font-size:14px; line-height:1.7; }
            .body p { margin:0 0 12px; }
          </style></head><body>
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
