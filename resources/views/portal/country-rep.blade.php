@extends('layout.app')

@section('content')
<div class="content-wrapper" style="background:#f4f6f9;">
  <section class="content">
    <div class="container-fluid" style="padding-top:1rem;">
      @include('_message')
      @include('portal._style')
      @include('portal._hello', ['roleLabel' => 'Country Representative', 'subtitle' => $countries->pluck('country_name')->implode(' · ') ?: null])

      @if($countries->isEmpty())
        @include('portal._empty', ['title' => 'No country found', 'text' => 'Your login is not linked to a country yet.'])
      @endif

      @foreach($countries as $c)
        <h4 class="mb-3" style="color:#2d2d2d;"><i class="fas fa-globe-africa mr-2" style="color:#a02626;"></i>{{ $c->country_name }}</h4>

        <div class="row">
          @foreach([
            ['Trainees in training', $c->counts['trainees_active'], 'fa-user-graduate'],
            ['Completed training', $c->counts['trainees_completed'], 'fa-award'],
            ['Candidates '.date('Y'), $c->counts['candidates'], 'fa-user-edit'],
            ['Active fellows', $c->counts['fellows'], 'fa-medal'],
            ['Members', $c->counts['members'], 'fa-id-badge'],
            ['Accredited hospitals', $c->counts['hospitals'], 'fa-hospital'],
          ] as [$label, $value, $icon])
            <div class="col-xl-2 col-md-4 col-6"><div class="portal-tile"><div><p class="label">{{ $label }}</p><p class="value">{{ number_format($value) }}</p></div><div class="icon d-none d-sm-flex"><i class="fas {{ $icon }}"></i></div></div></div>
          @endforeach
        </div>

        <div class="row">
          <div class="col-lg-5">
            <div class="portal-card">
              <div class="portal-card-head"><i class="fas fa-user-graduate"></i> Trainees in training, by programme</div>
              <div class="table-responsive">
                <table class="table table-sm table-hover">
                  <thead><tr><th>Programme</th><th class="text-right">Trainees</th><th class="text-right">Female</th></tr></thead>
                  <tbody>
                    @forelse($c->by_programme as $p)
                      <tr><td>{{ $p->programme_name ?: 'No programme' }}</td><td class="text-right">{{ $p->total }}</td><td class="text-right">{{ (int) $p->female }}</td></tr>
                    @empty
                      <tr><td colspan="3" class="text-center text-muted py-3">No trainees currently in training.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <div class="col-lg-7">
            <div class="portal-card">
              <div class="portal-card-head">
                <i class="fas fa-hospital"></i> Hospitals
                <div class="ml-auto"><input type="search" class="form-control form-control-sm portal-filter" data-target="hospitals-{{ $c->id }}" placeholder="Search hospital or director"></div>
              </div>
              <div class="table-responsive">
                <table class="table table-sm table-hover" id="hospitals-{{ $c->id }}">
                  <thead><tr><th>Hospital</th><th>Programme director</th><th class="text-right">Programmes</th><th class="text-right">Trainees</th></tr></thead>
                  <tbody>
                    @forelse($c->hospitals as $h)
                      <tr>
                        <td>{{ $h->name }}@if(! $h->status) <span class="portal-pill">inactive</span>@endif</td>
                        <td>{{ $h->directors ?: '—' }}</td>
                        <td class="text-right">{{ $h->programme_count }}</td>
                        <td class="text-right">{{ $h->active_trainees }}</td>
                      </tr>
                    @empty
                      <tr><td colspan="4" class="text-center text-muted py-3">No hospitals recorded for this country.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div class="portal-card">
          <div class="portal-card-head">
            <i class="fas fa-users"></i> Trainees in training ({{ $c->trainees->count() }})
            <div class="ml-auto"><input type="search" class="form-control form-control-sm portal-filter" data-target="trainees-{{ $c->id }}" placeholder="Search name, programme or hospital"></div>
          </div>
          <div class="table-responsive" style="max-height:520px; overflow-y:auto;">
            <table class="table table-sm table-hover" id="trainees-{{ $c->id }}">
              <thead><tr><th>Name</th><th>Programme</th><th>Hospital</th><th>Entry no.</th><th>Admitted</th></tr></thead>
              <tbody>
                @forelse($c->trainees as $t)
                  <tr><td>{{ $t->full_name }}</td><td>{{ $t->programme_name ?: '—' }}</td><td>{{ $t->hospital_name ?: '—' }}</td><td>{{ $t->entry_number ?: '—' }}</td><td>{{ $t->admission_year ?: '—' }}</td></tr>
                @empty
                  <tr><td colspan="5" class="text-center text-muted py-3">No trainees currently in training.</td></tr>
                @endforelse
              </tbody>
            </table>
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
