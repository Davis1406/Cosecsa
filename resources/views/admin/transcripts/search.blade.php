@extends('layout.app')

@section('content')
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-6">
            <h1 style="font-size:1.4rem;">Transcripts</h1>
            <p class="text-muted mb-0" style="font-size:.85rem;">
              Search for a fellow to issue or edit their transcript.
            </p>
          </div>
          @if(Auth::user()->hasPermission('transcripts.manage'))
          <div class="col-sm-6 text-right">
            <a href="{{ url('admin/settings/transcript-templates') }}" class="btn btn-outline-secondary">
              <i class="fas fa-file-alt mr-1"></i> Manage Templates
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
            <div class="form-row">
              <div class="form-group col-md-6">
                <input type="text" id="transcriptSearchInput" value="{{ $q }}" class="form-control"
                       placeholder="Search by name or candidate number…" autocomplete="off">
              </div>
            </div>

            <div id="transcriptSearchResults" class="table-responsive" style="{{ $results->isEmpty() ? 'display:none;' : '' }}">
              <table class="table table-striped table-sm">
                <thead>
                  <tr><th>Name</th><th>Candidate Number</th><th>Action</th></tr>
                </thead>
                <tbody id="transcriptResultsBody">
                  @foreach($results as $r)
                    <tr>
                      <td>{{ $r->name }}</td>
                      <td>{{ $r->ref }}</td>
                      <td>
                        <a href="{{ url('admin/transcripts/edit/'.$r->user_id) }}" class="btn btn-sm btn-primary">
                          <i class="fas fa-file-signature mr-1"></i> Issue / Edit Transcript
                        </a>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
            <div id="transcriptNoResults" class="text-center text-muted py-3" style="{{ $q && $results->isEmpty() ? '' : 'display:none;' }}">
              No matches found.
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const input = document.getElementById('transcriptSearchInput');
  const resultsBox = document.getElementById('transcriptSearchResults');
  const resultsBody = document.getElementById('transcriptResultsBody');
  const noResults = document.getElementById('transcriptNoResults');
  let debounceTimer = null;

  input.addEventListener('input', function () {
    clearTimeout(debounceTimer);
    const q = this.value.trim();

    if (!q) {
      resultsBox.style.display = 'none';
      noResults.style.display = 'none';
      return;
    }

    debounceTimer = setTimeout(function () {
      fetch("{{ url('admin/transcripts/search-live') }}?q=" + encodeURIComponent(q))
        .then(r => r.json())
        .then(rows => {
          resultsBody.innerHTML = '';
          if (rows.length === 0) {
            resultsBox.style.display = 'none';
            noResults.style.display = 'block';
            return;
          }
          noResults.style.display = 'none';
          resultsBox.style.display = 'block';
          rows.forEach(r => {
            resultsBody.insertAdjacentHTML('beforeend',
              `<tr><td>${r.name}</td><td>${r.ref ?? ''}</td><td>
                 <a href="{{ url('admin/transcripts/edit') }}/${r.user_id}" class="btn btn-sm btn-primary">
                   <i class="fas fa-file-signature mr-1"></i> Issue / Edit Transcript
                 </a>
               </td></tr>`);
          });
        });
    }, 300);
  });
});
</script>
@endpush
