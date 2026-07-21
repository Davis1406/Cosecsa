@extends('layout.app')

@section('content')
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-6"><h1 style="font-size:1.4rem;">College Letterhead</h1></div>
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
            <form method="POST" action="{{ url('admin/letters/letterhead') }}" enctype="multipart/form-data">
              @csrf
              <div class="form-group">
                <label>Institution Name</label>
                <input type="text" name="institution_name" class="form-control" value="{{ old('institution_name', $settings->institution_name) }}" required>
              </div>
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label>Address Block</label>
                  <textarea name="address_text" class="form-control" rows="5">{{ old('address_text', $settings->address_text) }}</textarea>
                  <small class="text-muted">One line per row — shown top-right of every letter.</small>
                </div>
                <div class="form-group col-md-6">
                  <label>Footer (Leadership) Block</label>
                  <textarea name="footer_text" class="form-control" rows="5">{{ old('footer_text', $settings->footer_text) }}</textarea>
                  <small class="text-muted">Format: <code>Label: Value||Label: Value</code> — one table row per line.</small>
                </div>
              </div>
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label>Logo</label><br>
                  @if($settings->logo_path)
                    <img src="{{ asset('storage/'.$settings->logo_path) }}" style="height:70px;display:block;margin-bottom:8px;">
                  @endif
                  <input type="file" name="logo" class="form-control-file" accept="image/*">
                </div>
                <div class="form-group col-md-6">
                  <label>Watermark</label><br>
                  @if($settings->watermark_path)
                    <img src="{{ asset('storage/'.$settings->watermark_path) }}" style="height:70px;display:block;margin-bottom:8px;">
                  @endif
                  <input type="file" name="watermark" class="form-control-file" accept="image/*">
                </div>
              </div>
              <button type="submit" class="btn btn-cosecsa">Save Letterhead</button>
            </form>
          </div>
        </div>
      </div>
    </section>
  </div>
@endsection
