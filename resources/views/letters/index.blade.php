@extends('layout.app')

@section('content')
  <style>
    .letters-table .action-btn { padding:2px 8px; line-height:1.4; border-radius:4px; }
    .letters-table .action-btn:hover { background-color:#f0f0f0; }
    .letters-table .dropdown-menu { min-width:150px; font-size:.875rem; }
    .letters-table .dropdown-item { padding:6px 14px; }
    .letters-table .dropdown-item:hover { background-color:#f8f0f0; }
    body.dark-mode .letters-table .action-btn:hover { background-color:#4a5568 !important; }
    body.dark-mode .letters-table .dropdown-item:hover { background-color:#4a5568 !important; color:#fff !important; }
  </style>
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-6">
            <h1 style="font-size:1.4rem;">College Letters</h1>
            <p class="text-muted mb-0" style="font-size:.85rem;">Letterhead-based letters — save a template, pick recipients, and dispatch.</p>
          </div>
          <div class="col-sm-6 text-right">
            <a href="{{ url('admin/letters/letterhead') }}" class="btn btn-cosecsa-outline">
              <i class="fas fa-file-signature mr-1"></i> College Letterhead
            </a>
            <a href="{{ url('admin/letters/report') }}" class="btn btn-cosecsa-outline">
              <i class="fas fa-list mr-1"></i> Sent Letters Report
            </a>
            <a href="{{ url('admin/letters/create') }}" class="btn btn-cosecsa">
              <i class="fas fa-plus mr-1"></i> New Template
            </a>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        @include('_message')

        <div class="card">
          <div class="card-header"><h3 class="card-title">Letter Templates</h3></div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-sm letters-table">
                <thead>
                  <tr><th>Name</th><th>Recipients</th><th>Times Sent</th><th>Status</th><th class="text-center">Action</th></tr>
                </thead>
                <tbody>
                  @foreach($templates as $t)
                    <tr>
                      <td>{{ $t->name }}</td>
                      <td>{{ \App\Services\LetterRecipientResolver::SOURCES[$t->recipient_source] ?? $t->recipient_source }}</td>
                      <td>{{ $t->dispatches_count }}</td>
                      <td>
                        <span class="badge {{ $t->is_active ? 'badge-success' : 'badge-secondary' }}">{{ $t->is_active ? 'Active' : 'Inactive' }}</span>
                      </td>
                      <td class="text-center" style="white-space:nowrap;">
                        <div class="dropdown">
                          <button class="btn btn-sm btn-light border dropdown-toggle action-btn"
                                  type="button" data-toggle="dropdown"
                                  aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-ellipsis-v"></i>
                          </button>
                          <div class="dropdown-menu dropdown-menu-right shadow-sm">
                            <a class="dropdown-item" href="{{ url('admin/letters/'.$t->id.'/recipients') }}">
                              <i class="fas fa-paper-plane text-success mr-2"></i> Send
                            </a>
                            <a class="dropdown-item" href="{{ url('admin/letters/'.$t->id.'/preview') }}" target="_blank">
                              <i class="fas fa-eye text-info mr-2"></i> Preview
                            </a>
                            <a class="dropdown-item" href="{{ url('admin/letters/'.$t->id.'/edit') }}">
                              <i class="fas fa-edit text-warning mr-2"></i> Edit
                            </a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ url('admin/letters/'.$t->id.'/delete') }}" onsubmit="return confirm('Delete this letter template?')">
                              @csrf
                              <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-trash mr-2"></i> Delete
                              </button>
                            </form>
                          </div>
                        </div>
                      </td>
                    </tr>
                  @endforeach
                  @if($templates->isEmpty())
                    <tr><td colspan="5" class="text-center text-muted py-3">No letter templates yet.</td></tr>
                  @endif
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header"><h3 class="card-title">Recent Dispatches</h3></div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-sm">
                <thead><tr><th>Template</th><th>Sent By</th><th>Recipients</th><th>When</th></tr></thead>
                <tbody>
                  @foreach($recentDispatches as $d)
                    <tr>
                      <td>{{ $d->template->name ?? '—' }}</td>
                      <td>{{ $d->sender->name ?? '—' }}</td>
                      <td>{{ $d->recipient_count }}</td>
                      <td>{{ $d->sent_at?->format('d M Y, H:i') }}</td>
                    </tr>
                  @endforeach
                  @if($recentDispatches->isEmpty())
                    <tr><td colspan="4" class="text-center text-muted py-3">No letters dispatched yet.</td></tr>
                  @endif
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
@endsection
