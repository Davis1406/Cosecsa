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
            <a href="{{ url('admin/settings/transcript-templates') }}" class="btn btn-cosecsa-yellow">
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
                        <a href="{{ url('admin/transcripts/edit/'.$r->user_id) }}" class="btn btn-sm btn-cosecsa">
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

        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Transcript History</h3>
            <div class="card-tools text-muted" style="font-size:.85rem;">
              {{ $history->where('kind', 'shared')->count() }} shared · {{ $history->where('kind', 'saved')->count() }} saved, not shared
            </div>
          </div>
          <div class="card-body">
            @php $canDelete = Auth::user()->isSuperAdmin() || Auth::user()->hasPermission('transcripts.manage'); @endphp
            @if($history->isEmpty())
              <p class="text-muted mb-0" style="font-size:.85rem;">No transcripts have been issued yet.</p>
            @else
              <table id="transcriptHistoryTable" class="table table-sm table-hover mb-0" style="font-size:.85rem;">
                <thead>
                  <tr><th style="width:36px;">#</th><th>Date</th><th>Fellow</th><th>Programme</th><th>Status</th><th>Sent to</th><th>By</th><th class="text-right">Actions</th></tr>
                </thead>
                <tbody>
                  @foreach($history as $h)
                    @php $at = \Illuminate\Support\Carbon::parse($h['at']); @endphp
                    <tr>
                      <td class="row-num"></td>
                      <td data-order="{{ $at->timestamp }}">{{ $at->format('d M Y, H:i') }}</td>
                      <td>
                        @if($h['fellow_id'])
                          <a href="{{ url('admin/associates/fellows/view/'.$h['fellow_id']) }}#tab-transcript" class="cosecsa-link">{{ $h['full_name'] }}</a>
                        @else
                          {{ $h['full_name'] }}
                        @endif
                      </td>
                      <td>{{ $h['programme'] ?: '—' }}</td>
                      <td>
                        @if($h['kind'] === 'shared')
                          <span class="badge badge-success">Shared</span>
                        @else
                          <span class="badge badge-secondary">Saved, not shared</span>
                        @endif
                      </td>
                      <td>{{ $h['sent_to'] ?: '—' }}</td>
                      <td>{{ $h['by_name'] ?: '—' }}</td>
                      <td class="text-right" style="white-space:nowrap;">
                        @if($h['kind'] === 'shared')
                          <a href="{{ url('admin/transcripts/copy/'.$h['user_id'].'?dispatch='.$h['dispatch_id']) }}" target="_blank" class="btn btn-sm btn-cosecsa-yellow">
                            <i class="fas fa-file-pdf mr-1"></i> Sent copy
                          </a>
                        @else
                          <a href="{{ url('admin/transcripts/pdf/'.$h['user_id']) }}" target="_blank" class="btn btn-sm btn-cosecsa-yellow">
                            <i class="fas fa-eye mr-1"></i> Preview
                          </a>
                        @endif
                        <a href="{{ url('admin/transcripts/edit/'.$h['user_id']) }}" class="btn btn-sm btn-cosecsa">
                          <i class="fas fa-file-signature mr-1"></i> Open
                        </a>
                        @if($canDelete)
                          @if($h['kind'] === 'shared')
                            <form method="POST" action="{{ url('admin/transcripts/delete/'.$h['user_id'].'/share/'.$h['dispatch_id']) }}" class="d-inline"
                                  onsubmit="return confirm('Delete this shared transcript and its saved copy? The fellow keeps the email they received.');">
                              @csrf
                              <button type="submit" class="btn btn-sm btn-cosecsa-danger" title="Delete this share"><i class="fas fa-trash"></i></button>
                            </form>
                          @else
                            <form method="POST" action="{{ url('admin/transcripts/delete/'.$h['user_id']) }}" class="d-inline"
                                  onsubmit="return confirm('Delete the saved transcript for {{ addslashes($h['full_name']) }}?');">
                              @csrf
                              <button type="submit" class="btn btn-sm btn-cosecsa-danger" title="Delete this transcript"><i class="fas fa-trash"></i></button>
                            </form>
                          @endif
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            @endif
          </div>
        </div>
      </div>
    </section>
  </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  if (document.getElementById('transcriptHistoryTable')) {
    $('#transcriptHistoryTable').DataTable({
      pageLength: 10,
      order: [[1, 'desc']],
      columnDefs: [{ orderable: false, searchable: false, targets: [0, -1] }],
      drawCallback: function () {
        var api = this.api(), start = api.page.info().start;
        api.column(0, { page: 'current' }).nodes().each(function (cell, i) { cell.innerHTML = start + i + 1; });
      }
    });
  }

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
                 <a href="{{ url('admin/transcripts/edit') }}/${r.user_id}" class="btn btn-sm btn-cosecsa">
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
