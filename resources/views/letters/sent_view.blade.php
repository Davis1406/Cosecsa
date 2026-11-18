@extends('layout.app')

@section('content')
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

        <div class="card">
          <div class="card-header d-flex flex-wrap align-items-center" style="gap:.5rem;">
            <ul class="nav nav-pills" id="sentTabs">
              <li class="nav-item"><a class="nav-link active" href="#sentLetterPane" data-toggle="pill">Letter</a></li>
              <li class="nav-item"><a class="nav-link" href="#sentEmailPane" data-toggle="pill">E-mail</a></li>
            </ul>
            <div class="ml-auto d-flex flex-wrap" style="gap:.4rem;">
              <a class="btn btn-sm btn-outline-secondary" href="{{ url('admin/letters/sent/' . $id . '/pdf') }}"><i class="fas fa-file-pdf mr-1"></i> Download PDF (current details)</a>
              @if(!empty($letter['has_original']))
                <a class="btn btn-sm btn-outline-secondary" href="{{ url('admin/letters/sent/' . $id . '/download') }}" title="The PDF exactly as it was e-mailed"><i class="fas fa-download mr-1"></i> Original PDF as sent</a>
              @endif
              @if(Auth::user()->hasPermission('letters.manage'))
                <button type="button" class="btn btn-sm btn-cosecsa" id="btnResend"><i class="fas fa-paper-plane mr-1"></i> Send again</button>
              @endif
            </div>
          </div>
          <div class="card-body p-0">
            <div class="tab-content">
              <div class="tab-pane fade show active" id="sentLetterPane">
                <iframe title="Letter" sandbox="allow-scripts" srcdoc="{{ $letter['letter_html'] }}" style="width:100%;height:78vh;border:0;background:#e9ecef;"></iframe>
              </div>
              <div class="tab-pane fade" id="sentEmailPane">
                <div class="px-3 pt-3 small text-muted">
                  To: <strong>{{ $letter['sent_to'] ?: ($letter['recipient']['email'] ?: '—') }}</strong><br>
                  Subject: <strong>{{ $letter['subject'] }}</strong><br>
                  Attached: the letter as a PDF
                </div>
                <iframe title="E-mail" sandbox srcdoc="{{ $letter['email_html'] }}" style="width:100%;height:70vh;border:0;"></iframe>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  @include('letters._resend_modal')
@endsection

@push('scripts')
<script>
  var btn = document.getElementById('btnResend');
  if (btn) btn.addEventListener('click', function () { openLetterResend({{ (int) $id }}, @json($letter['recipient']['email'] ?? ''), function () { }); });
</script>
@endpush
