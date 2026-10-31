@extends('layout.app')

@section('content')
<div class="content-wrapper" style="background:#f4f6f9;">
  <section class="content">
    <div class="container-fluid" style="padding-top:1rem;">
      @include('_message')
      @include('portal._style')
      @include('portal._hello', ['roleLabel' => 'Programme Director', 'subtitle' => $hospitals->pluck('name')->implode(' · ') ?: null])

      @if($hospitals->isEmpty())
        @include('portal._empty', ['title' => 'No hospital found', 'text' => 'Your login is not linked to a hospital yet.'])
      @endif

      @foreach($hospitals as $h)
        <h4 class="mb-3" style="color:#2d2d2d;"><i class="fas fa-hospital mr-2" style="color:#a02626;"></i>{{ $h->name }}@if($h->country_name) <small class="text-muted">· {{ $h->country_name }}</small>@endif</h4>

        <div class="row">
          <div class="col-md-4"><div class="portal-tile"><div><p class="label">Trainees in training</p><p class="value">{{ $h->trainees->count() }}</p></div><div class="icon"><i class="fas fa-user-graduate"></i></div></div></div>
          <div class="col-md-4"><div class="portal-tile"><div><p class="label">Completed training</p><p class="value">{{ $h->completed_count }}</p></div><div class="icon"><i class="fas fa-award"></i></div></div></div>
          <div class="col-md-4"><div class="portal-tile"><div><p class="label">Accredited programmes</p><p class="value">{{ $h->programmes->where('status', 'Active')->count() }}</p></div><div class="icon"><i class="fas fa-stamp"></i></div></div></div>
        </div>

        <div class="row">
          <div class="col-lg-5">
            <div class="portal-card">
              <div class="portal-card-head"><i class="fas fa-stamp"></i> Accredited programmes</div>
              <div class="table-responsive">
                <table class="table table-sm table-hover">
                  <thead><tr><th>Programme</th><th>Status</th><th>Expires</th><th class="text-right">Trainees</th></tr></thead>
                  <tbody>
                    @forelse($h->programmes as $p)
                      <tr>
                        <td>{{ $p->name }}</td>
                        <td><span class="portal-pill {{ $p->status === 'Active' ? 'ok' : 'bad' }}">{{ $p->status }}</span></td>
                        <td>{{ $p->expiry_date ?: '—' }}</td>
                        <td class="text-right">{{ $h->by_programme[$p->name] ?? 0 }}</td>
                      </tr>
                    @empty
                      <tr><td colspan="4" class="text-center text-muted py-3">No accredited programmes recorded.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="col-lg-7">
            <div class="portal-card">
              <div class="portal-card-head">
                <i class="fas fa-users"></i> Trainees in training
                <div class="ml-auto"><input type="search" class="form-control form-control-sm portal-filter" data-target="trainees-{{ $h->id }}" placeholder="Search name or programme"></div>
              </div>
              <div class="table-responsive">
                <table class="table table-sm table-hover" id="trainees-{{ $h->id }}">
                  <thead><tr><th>Name</th><th>Programme</th><th>Entry no.</th><th>Admitted</th></tr></thead>
                  <tbody>
                    @forelse($h->trainees as $t)
                      <tr><td>{{ $t->full_name }}</td><td>{{ $t->programme_name ?: '—' }}</td><td>{{ $t->entry_number ?: '—' }}</td><td>{{ $t->admission_year ?: '—' }}</td></tr>
                    @empty
                      <tr><td colspan="4" class="text-center text-muted py-3">No trainees currently in training at this hospital.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </section>
</div>
@endsection

@push('scripts')
<script>
  document.querySelectorAll('.portal-filter').forEach(function (box) {
    box.addEventListener('input', function () {
      var q = box.value.toLowerCase();
      document.querySelectorAll('#' + box.dataset.target + ' tbody tr').forEach(function (tr) {
        tr.style.display = tr.textContent.toLowerCase().indexOf(q) === -1 ? 'none' : '';
      });
    });
  });
</script>
@endpush
