@extends('layout.app')

@section('content')
<div class="content-wrapper" style="background:#f4f6f9;">
  <section class="content">
    <div class="container-fluid" style="padding-top:1rem;">
      @include('_message')
      @include('portal._style')
      @php $m = $current; @endphp
      @include('portal._hello', ['roleLabel' => 'Member', 'subtitle' => $m ? ($m->category_name ?: null) : null])

      @if(! $m)
        @include('portal._empty', ['title' => 'No membership record found', 'text' => 'Your login is not linked to a member record yet.'])
      @else
        <div class="row">
          <div class="col-md-4"><div class="portal-tile"><div><p class="label">Status</p><p class="value" style="font-size:1.4rem;">
            <span class="portal-pill {{ $m->status === 'Active' ? 'ok' : ($m->status === 'Deceased' ? 'bad' : '') }}">{{ $m->status ?: '—' }}</span></p></div><div class="icon"><i class="fas fa-check-circle"></i></div></div></div>
          <div class="col-md-4"><div class="portal-tile"><div><p class="label">Member since</p><p class="value">{{ $m->membership_year ?: ($m->admission_year ?: '—') }}</p></div><div class="icon"><i class="fas fa-calendar-alt"></i></div></div></div>
          <div class="col-md-4"><div class="portal-tile"><div><p class="label">Member ID</p><p class="value" style="font-size:1.4rem;">{{ $m->member_id_number ?: '—' }}</p></div><div class="icon"><i class="fas fa-id-badge"></i></div></div></div>
        </div>

        <div class="portal-card">
          <div class="portal-card-head"><i class="fas fa-id-badge"></i> My membership</div>
          <div class="portal-card-body">
            <dl class="portal-kv">
              <dt>Name</dt><dd>{{ $m->full_name }}</dd>
              <dt>Category</dt><dd>{{ $m->category_name ?: '—' }}</dd>
              <dt>Country</dt><dd>{{ $m->country_name ?: '—' }}</dd>
              <dt>Admission year</dt><dd>{{ $m->admission_year ?: '—' }}</dd>
              <dt>Email on file</dt><dd>{{ $m->personal_email ?: '—' }}</dd>
              <dt>Phone</dt><dd>{{ $m->phone_number ?: '—' }}</dd>
              <dt>Address</dt><dd>{{ $m->address ?: '—' }}</dd>
            </dl>
          </div>
        </div>

        <p class="text-muted" style="font-size:.85rem;">To change any of these details write to <a href="mailto:communications@cosecsa.org">communications@cosecsa.org</a>, or use Messages in the menu.</p>
      @endif
    </div>
  </section>
</div>
@endsection
