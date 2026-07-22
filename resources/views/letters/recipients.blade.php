@extends('layout.app')

@section('content')
  <style>
    .rchk-filter-wrap  { position:relative; display:inline-block; }
    .rchk-filter-panel { position:absolute; top:calc(100% + 4px); left:0; z-index:1055;
                        background:#fff; border:1px solid #ced4da; border-radius:6px;
                        min-width:200px; max-width:260px; padding:8px;
                        box-shadow:0 4px 12px rgba(0,0,0,.12); }
    .rchk-list  { max-height:220px; overflow-y:auto; }
    .rchk-item  { display:flex; align-items:center; gap:6px; padding:3px 2px;
                 font-size:.82rem; font-weight:normal; cursor:pointer; white-space:nowrap; margin:0; }
    .rchk-item:hover { background:#f8f0f0; border-radius:4px; }
    .rchk-item input[type="checkbox"] { margin:0; cursor:pointer; accent-color:#a02626; }
    .rchk-footer { display:flex; justify-content:space-between; border-top:1px solid #eee;
                  margin-top:6px; padding-top:5px; font-size:.78rem; }
    .rchk-footer a { color:#6c757d; }
    .rchk-footer a:hover { color:#a02626; text-decoration:none; }
    .rchk-filter-btn { white-space:nowrap; }
    body.dark-mode .rchk-filter-panel { background:#374151 !important; border-color:#4a5568 !important; }
    body.dark-mode .rchk-item { color:#e0e0e0 !important; }
    body.dark-mode .rchk-item:hover { background:#4a5568 !important; }
    body.dark-mode .rchk-footer { border-top-color:#4a5568 !important; }
    body.dark-mode .rchk-footer a { color:#9ca3af !important; }
  </style>

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

        @php
          $selectedCountryIds = array_map('strval', (array) ($filters['country_id'] ?? []));
          $selectedProgrammeIds = array_map('strval', (array) ($filters['programme_id'] ?? []));
        @endphp

        <div class="card card-outline card-secondary mb-2 shadow-sm">
          <div class="card-body py-2">
            <form method="GET" action="{{ url('admin/letters/'.$template->id.'/recipients') }}" id="recipFilterForm" class="d-flex flex-wrap align-items-center" style="gap:.5rem;">
              <div class="rchk-filter-wrap">
                <button type="button" class="btn btn-sm btn-outline-secondary rchk-filter-btn" data-filter="rFilterCountry">
                  Country
                  <span class="badge badge-danger rchk-badge ml-1" style="{{ count($selectedCountryIds) ? '' : 'display:none;' }}font-size:.65rem;">{{ count($selectedCountryIds) }}</span>
                  <i class="fas fa-caret-down ml-1" style="font-size:.7rem;"></i>
                </button>
                <div class="rchk-filter-panel shadow" id="rFilterCountry-panel" style="display:none;">
                  <input type="text" class="form-control form-control-sm rchk-search mb-1" placeholder="Search…" autocomplete="off">
                  <div class="rchk-list">
                    @foreach($countries as $id => $name)
                      <label class="rchk-item">
                        <input type="checkbox" name="country_id[]" value="{{ $id }}" {{ in_array((string) $id, $selectedCountryIds) ? 'checked' : '' }}>
                        {{ $name }}
                      </label>
                    @endforeach
                  </div>
                  <div class="rchk-footer">
                    <a href="#" class="rchk-select-all small">All</a>
                    <a href="#" class="rchk-clear small text-danger">Clear</a>
                  </div>
                </div>
              </div>

              <div class="rchk-filter-wrap">
                <button type="button" class="btn btn-sm btn-outline-secondary rchk-filter-btn" data-filter="rFilterProgramme">
                  Programme
                  <span class="badge badge-danger rchk-badge ml-1" style="{{ count($selectedProgrammeIds) ? '' : 'display:none;' }}font-size:.65rem;">{{ count($selectedProgrammeIds) }}</span>
                  <i class="fas fa-caret-down ml-1" style="font-size:.7rem;"></i>
                </button>
                <div class="rchk-filter-panel shadow" id="rFilterProgramme-panel" style="display:none;">
                  <input type="text" class="form-control form-control-sm rchk-search mb-1" placeholder="Search…" autocomplete="off">
                  <div class="rchk-list">
                    @foreach($programmes as $id => $name)
                      <label class="rchk-item">
                        <input type="checkbox" name="programme_id[]" value="{{ $id }}" {{ in_array((string) $id, $selectedProgrammeIds) ? 'checked' : '' }}>
                        {{ $name }}
                      </label>
                    @endforeach
                  </div>
                  <div class="rchk-footer">
                    <a href="#" class="rchk-select-all small">All</a>
                    <a href="#" class="rchk-clear small text-danger">Clear</a>
                  </div>
                </div>
              </div>

              <input type="number" name="year" class="form-control form-control-sm" style="max-width:120px;" value="{{ $filters['year'] ?? '' }}" placeholder="Year">
              <input type="text" name="search" class="form-control form-control-sm" style="max-width:200px;" value="{{ $filters['search'] ?? '' }}" placeholder="Search name…">

              <div class="form-check ml-1">
                <input type="checkbox" name="unsent_only" id="unsent_only" class="form-check-input" value="1" {{ !empty($filters['unsent_only']) ? 'checked' : '' }}>
                <label class="form-check-label" for="unsent_only" style="font-size:.85rem;">Not sent yet</label>
              </div>

              <button type="submit" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-filter mr-1"></i>Apply
              </button>
              <a href="{{ url('admin/letters/'.$template->id.'/recipients') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-times mr-1"></i>Clear All
              </a>
            </form>
          </div>
        </div>

        <form method="POST" action="{{ url('admin/letters/'.$template->id.'/dispatch') }}" id="dispatchForm">
          @csrf
          @foreach($selectedCountryIds as $cid)
            <input type="hidden" name="country_id[]" value="{{ $cid }}">
          @endforeach
          @foreach($selectedProgrammeIds as $pid)
            <input type="hidden" name="programme_id[]" value="{{ $pid }}">
          @endforeach
          <input type="hidden" name="year" value="{{ $filters['year'] ?? '' }}">
          <input type="hidden" name="search" value="{{ $filters['search'] ?? '' }}">
          <input type="hidden" name="unsent_only" value="{{ !empty($filters['unsent_only']) ? '1' : '' }}">

          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h3 class="card-title">Recipients ({{ $recipients->count() }})</h3>
              <div>
                <label class="mr-2 mb-0" style="font-size:.85rem;">Letter date</label>
                <input type="date" name="letter_date" class="form-control d-inline-block" style="width:170px;" value="{{ now()->format('Y-m-d') }}">
              </div>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive" style="max-height:480px; overflow-y:auto;">
                <table class="table table-striped table-sm mb-0">
                  <thead>
                    <tr>
                      <th style="width:3%;"><input type="checkbox" id="checkAll"></th>
                      <th>Name</th><th>Email</th><th>Country</th><th>Programme</th><th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($recipients as $r)
                      <tr>
                        <td><input type="checkbox" name="recipient_ids[]" value="{{ $r->id }}" class="recip-check"></td>
                        <td>{{ $r->name }}</td>
                        <td>{{ $r->email ?: '—' }}</td>
                        <td>{{ $r->country ?: '—' }}</td>
                        <td>{{ $r->programme ?: '—' }}</td>
                        <td>
                          @if($r->already_sent)
                            <span class="badge badge-success">Already Sent</span>
                          @else
                            <span class="badge badge-secondary">Not Sent</span>
                          @endif
                        </td>
                      </tr>
                    @endforeach
                    @if($recipients->isEmpty())
                      <tr><td colspan="6" class="text-center text-muted py-3">No recipients match these filters.</td></tr>
                    @endif
                  </tbody>
                </table>
              </div>
            </div>
            <div class="card-footer text-right">
              <button type="submit" class="btn btn-cosecsa" onclick="return confirm('Dispatch this letter to the selected recipients?')">
                <i class="fas fa-paper-plane mr-1"></i> Dispatch Selected
              </button>
            </div>
          </div>
        </form>
      </div>
    </section>
  </div>
@endsection

@push('scripts')
<script>
document.getElementById('checkAll').addEventListener('change', function () {
  document.querySelectorAll('.recip-check').forEach(cb => cb.checked = this.checked);
});

document.addEventListener('click', function (e) {
  const btn = e.target.closest('.rchk-filter-btn');
  if (btn) {
    e.stopPropagation();
    const filterId = btn.dataset.filter;
    const panel = document.getElementById(filterId + '-panel');
    document.querySelectorAll('.rchk-filter-panel').forEach(p => { if (p !== panel) p.style.display = 'none'; });
    panel.style.display = panel.style.display === 'block' ? 'none' : 'block';
    return;
  }
  if (e.target.closest('.rchk-filter-panel')) { e.stopPropagation(); return; }
  document.querySelectorAll('.rchk-filter-panel').forEach(p => p.style.display = 'none');
});

document.querySelectorAll('.rchk-search').forEach(function (input) {
  input.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    this.closest('.rchk-filter-panel').querySelectorAll('.rchk-item').forEach(function (item) {
      item.style.display = item.textContent.toLowerCase().indexOf(q) !== -1 ? '' : 'none';
    });
  });
});

document.querySelectorAll('.rchk-select-all').forEach(function (link) {
  link.addEventListener('click', function (e) {
    e.preventDefault();
    this.closest('.rchk-filter-panel').querySelectorAll('.rchk-item:not([style*="display: none"]) input[type="checkbox"]').forEach(cb => cb.checked = true);
  });
});
document.querySelectorAll('.rchk-clear').forEach(function (link) {
  link.addEventListener('click', function (e) {
    e.preventDefault();
    this.closest('.rchk-filter-panel').querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = false);
  });
});
</script>
@endpush
