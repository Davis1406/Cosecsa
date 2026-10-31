@extends('layout.app')

@section('content')
<div class="content-wrapper" style="background:#f4f6f9;">
  <section class="content">
    <div class="container-fluid" style="padding-top:1rem;">
      @include('_message')
      @include('portal._style')
      @php $c = $current; @endphp
      @include('portal._hello', ['roleLabel' => 'Candidate', 'subtitle' => $c ? ($c->programme_name ?: null) : null])

      @if(! $c)
        @include('portal._empty', ['title' => 'No exam entry found', 'text' => 'Your login is not linked to a candidate record yet.'])
      @else
        <div class="row">
          <div class="col-md-4"><div class="portal-tile"><div><p class="label">Exam year</p><p class="value">{{ $c->exam_year ?: '—' }}</p></div><div class="icon"><i class="fas fa-calendar-alt"></i></div></div></div>
          <div class="col-md-4"><div class="portal-tile"><div><p class="label">Entry number</p><p class="value" style="font-size:1.4rem;">{{ $c->entry_number ?: '—' }}</p></div><div class="icon"><i class="fas fa-id-card"></i></div></div></div>
          <div class="col-md-4"><div class="portal-tile"><div><p class="label">Exam fee</p>
            <p class="value" style="font-size:1.4rem;"><span class="portal-pill {{ $c->fee_status === 'Paid' ? 'ok' : 'warn' }}">{{ $c->fee_status }}</span></p></div><div class="icon"><i class="fas fa-receipt"></i></div></div></div>
        </div>

        <div class="portal-card">
          <div class="portal-card-head"><i class="fas fa-user-edit"></i> My details</div>
          <div class="portal-card-body">
            <dl class="portal-kv">
              <dt>Name</dt><dd>{{ $c->full_name }}</dd>
              <dt>Candidate ID</dt><dd>{{ $c->candidate_id ?: '—' }}</dd>
              <dt>Exam number</dt><dd>{{ $c->exam_number ?: '—' }}</dd>
              <dt>Programme</dt><dd>{{ $c->programme_name ?: '—' }}</dd>
              <dt>Hospital</dt><dd>{{ $c->hospital_name ?: '—' }}</dd>
              <dt>Country</dt><dd>{{ $c->country_name ?: '—' }}</dd>
              <dt>Admission year</dt><dd>{{ $c->admission_year ?: '—' }}</dd>
              @if($c->personal_email)<dt>Email on file</dt><dd>{{ $c->personal_email }}</dd>@endif
            </dl>
          </div>
        </div>

        @if($entries->count() > 1)
          <div class="portal-card">
            <div class="portal-card-head"><i class="fas fa-history"></i> All my exam entries</div>
            <div class="table-responsive">
              <table class="table table-sm table-hover">
                <thead><tr><th>Exam year</th><th>Programme</th><th>Entry no.</th><th>Exam no.</th><th>Hospital</th><th>Fee</th></tr></thead>
                <tbody>
                  @foreach($entries as $e)
                    <tr><td>{{ $e->exam_year ?: '—' }}</td><td>{{ $e->programme_name ?: '—' }}</td><td>{{ $e->entry_number ?: '—' }}</td><td>{{ $e->exam_number ?: '—' }}</td><td>{{ $e->hospital_name ?: '—' }}</td><td><span class="portal-pill {{ $e->fee_status === 'Paid' ? 'ok' : 'warn' }}">{{ $e->fee_status }}</span></td></tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        @endif

        <p class="text-muted" style="font-size:.85rem;">Something wrong? Write to <a href="mailto:communications@cosecsa.org">communications@cosecsa.org</a>, or use Messages in the menu.</p>
      @endif
    </div>
  </section>
</div>
@endsection
