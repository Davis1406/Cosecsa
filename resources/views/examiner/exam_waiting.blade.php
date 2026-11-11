@extends('layout.app')

@section('content')
  <div class="content-wrapper">
    <section class="content">
      <div class="container-fluid" style="max-width:640px;padding-top:7vh;">
        <div class="card" style="border-top:4px solid #FEC503;">
          <div class="card-body text-center" style="padding:36px 28px;">
            <div style="font-size:3rem;color:#a02626;"><i class="fas fa-hourglass-half"></i></div>
            <h3 class="mt-2 mb-2" style="font-size:1.6rem;">Waiting for the exam to start</h3>
            <p class="mb-3" style="font-size:1.15rem;line-height:1.5;">
              The exams officer opens marking a few minutes before the exam begins.
              @if(!empty($examState['scheduled_for']))
                <br>The exam starts at <strong>{{ \Carbon\Carbon::parse($examState['scheduled_for'])->format('H:i') }}</strong>
                ({{ \Carbon\Carbon::parse($examState['scheduled_for'])->format('d M Y') }}).
              @endif
            </p>
            <p class="text-muted" style="font-size:1rem;">This page opens the marking form by itself as soon as the exam is started. You do not need to refresh it.</p>
            <div class="mt-3" id="waitStatus" style="font-size:.95rem;color:#6b6b6b;">Checking…</div>
            <hr class="my-4">
            <p class="text-muted mb-2" style="font-size:.95rem;">Want to try the form first?</p>
            <a href="{{ url('examiner/examiner_form?practice=1') }}" class="btn btn-outline-secondary btn-lg">Practise with the test groups</a>
            <div class="mt-3"><a href="{{ url('examiner/dashboard') }}">Back to my dashboard</a></div>
          </div>
        </div>
      </div>
    </section>
  </div>
@endsection

@push('scripts')
<script>
(function () {
  var statusUrl = @json(url('examiner/exam-status')), formUrl = @json(url('examiner/examiner_form')), box = document.getElementById('waitStatus');
  function check() {
    fetch(statusUrl, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin', cache: 'no-store' })
      .then(function (r) { return r.ok ? r.json() : Promise.reject(); })
      .then(function (s) {
        if (s.open) { box.textContent = 'The exam has started — opening the form…'; location.replace(formUrl); return; }
        var t = new Date(); box.textContent = 'Not started yet · checked ' + t.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
      })
      .catch(function () { box.textContent = 'No connection — will keep trying.'; });
  }
  check(); setInterval(check, 8000);
})();
</script>
@endpush
