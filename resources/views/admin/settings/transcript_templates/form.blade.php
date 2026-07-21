@extends('layout.app')

@section('content')
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-6">
            <h1 style="font-size:1.4rem;">{{ $header_title }}</h1>
          </div>
          @if($template)
          <div class="col-sm-6 text-right">
            <a href="{{ url('admin/settings/transcript-templates/preview/'.$template->id) }}" target="_blank" class="btn btn-outline-secondary">
              <i class="fas fa-eye mr-1"></i> Preview
            </a>
          </div>
          @endif
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        @include('_message')
        <div class="card">
          <div class="card-body">
            <form method="post" enctype="multipart/form-data"
                  action="{{ $template ? url('admin/settings/transcript-templates/edit/'.$template->id) : url('admin/settings/transcript-templates/add') }}">
              @csrf
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label>Template Name</label>
                  <input type="text" name="name" class="form-control" required value="{{ old('name', $template->name ?? '') }}">
                </div>
                <div class="form-group col-md-6">
                  <label>Document Title</label>
                  <input type="text" name="document_title" class="form-control" value="{{ old('document_title', $template->document_title ?? 'TRANSCRIPT OF TRAINING') }}">
                </div>
              </div>

              <h5 class="mt-3">Letterhead</h5>
              <div class="form-row">
                <div class="form-group col-md-3">
                  <label>Logo <small class="text-muted">(header)</small></label>
                  @if($template && $template->logo_path)
                    <div class="mb-1"><img src="{{ asset('storage/'.$template->logo_path) }}" style="height:50px;"></div>
                  @endif
                  <input type="file" name="logo_path" class="form-control-file" accept="image/*">
                </div>
                <div class="form-group col-md-3">
                  <label>Watermark <small class="text-muted">(faded background)</small></label>
                  @if($template && $template->watermark_path)
                    <div class="mb-1"><img src="{{ asset('storage/'.$template->watermark_path) }}" style="height:50px;"></div>
                  @endif
                  <input type="file" name="watermark_path" class="form-control-file" accept="image/*">
                </div>
                <div class="form-group col-md-3">
                  <label>Signature</label>
                  @if($template && $template->signature_path)
                    <div class="mb-1"><img src="{{ asset('storage/'.$template->signature_path) }}" style="height:50px;"></div>
                  @endif
                  <input type="file" name="signature_path" class="form-control-file" accept="image/*">
                </div>
                <div class="form-group col-md-3">
                  <label>Stamp / Seal</label>
                  @if($template && $template->stamp_path)
                    <div class="mb-1"><img src="{{ asset('storage/'.$template->stamp_path) }}" style="height:50px;"></div>
                  @endif
                  <input type="file" name="stamp_path" class="form-control-file" accept="image/*">
                </div>
              </div>

              <div class="form-group">
                <label>Address / Contact Text <small class="text-muted">(shown next to the logo)</small></label>
                <textarea name="address_text" class="form-control" rows="2">{{ old('address_text', $template->address_text ?? '') }}</textarea>
              </div>

              <div class="form-group">
                <label>Intro Text <small class="text-muted">(optional paragraph shown under the title)</small></label>
                <textarea name="intro_text" class="form-control" rows="2">{{ old('intro_text', $template->intro_text ?? '') }}</textarea>
              </div>

              <div class="form-row">
                <div class="form-group col-md-4">
                  <label>Closing Salutation</label>
                  <input type="text" name="closing_salutation" class="form-control" value="{{ old('closing_salutation', $template->closing_salutation ?? 'Yours Sincerely,') }}">
                </div>
                <div class="form-group col-md-4">
                  <label>Signatory Name</label>
                  <input type="text" name="signatory_name" class="form-control" required value="{{ old('signatory_name', $template->signatory_name ?? '') }}">
                </div>
                <div class="form-group col-md-4">
                  <label>Signatory Title</label>
                  <input type="text" name="signatory_title" class="form-control" required value="{{ old('signatory_title', $template->signatory_title ?? '') }}">
                </div>
              </div>

              <div class="form-group">
                <label>Institution Name</label>
                <input type="text" name="institution_name" class="form-control"
                       value="{{ old('institution_name', $template->institution_name ?? 'College of Surgeons of East, Central and Southern Africa') }}">
              </div>

              <div class="form-group">
                <label>Footer Text <small class="text-muted">
                  (shown at page bottom, one row per line, two columns per row separated by <code>||</code> —
                  e.g. <code>President: Prof X, Country||Secretary General: Prof Y, Country</code>)
                </small></label>
                <textarea name="footer_text" class="form-control" rows="3">{{ old('footer_text', $template->footer_text ?? '') }}</textarea>
              </div>

              <div class="form-group form-check">
                <input type="checkbox" class="form-check-input" name="is_default" id="is_default" value="1"
                       {{ old('is_default', $template->is_default ?? false) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_default">Use as the default template for new transcripts</label>
              </div>

              <button type="submit" class="btn btn-primary">Save Template</button>
              <a href="{{ url('admin/settings/transcript-templates') }}" class="btn btn-secondary">Back to List</a>
            </form>
          </div>
        </div>
      </div>
    </section>
  </div>
@endsection
