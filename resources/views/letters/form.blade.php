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

        <div class="card">
          <div class="card-body">
            <form method="POST" action="{{ $template ? url('admin/letters/'.$template->id.'/update') : url('admin/letters') }}">
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
                <strong>Available merge fields:</strong>
                @verbatim
                {{name}} {{first_name}} {{email}} {{country}} {{programme}} {{hospital}} {{entry_number}} {{exam_year}} {{admission_year}} {{sfs_username}} {{sfs_password}} {{date}}
                @endverbatim
              </div>

              <div class="form-group">
                <label>Letter Body (rendered on the letterhead PDF)</label>
                <textarea name="pdf_body" class="form-control" rows="14" required>{{ old('pdf_body', $template->pdf_body ?? '') }}</textarea>
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

              <div class="form-group form-check">
                <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" {{ old('is_active', $template->is_active ?? true) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_active">Active (selectable when dispatching)</label>
              </div>

              <button type="submit" class="btn btn-cosecsa">{{ $template ? 'Save Changes' : 'Create Template' }}</button>
            </form>
          </div>
        </div>
      </div>
    </section>
  </div>
@endsection
