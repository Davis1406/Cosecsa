@extends('layout.app')

@section('content')
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
              <table class="table table-striped table-sm">
                <thead>
                  <tr><th>Name</th><th>Recipients</th><th>Times Sent</th><th>Status</th><th></th></tr>
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
                      <td>
                        <div class="dropdown">
                          <button type="button" class="btn btn-sm btn-cosecsa-outline" data-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-ellipsis-v"></i>
                          </button>
                          <div class="dropdown-menu dropdown-menu-right">
                            <a class="dropdown-item" href="{{ url('admin/letters/'.$t->id.'/recipients') }}">
                              <i class="fas fa-paper-plane mr-1"></i> Send
                            </a>
                            <a class="dropdown-item" href="{{ url('admin/letters/'.$t->id.'/preview') }}" target="_blank">
                              <i class="fas fa-eye mr-1"></i> Preview
                            </a>
                            <a class="dropdown-item" href="{{ url('admin/letters/'.$t->id.'/edit') }}">
                              <i class="fas fa-edit mr-1"></i> Edit
                            </a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ url('admin/letters/'.$t->id.'/delete') }}" onsubmit="return confirm('Delete this letter template?')">
                              @csrf
                              <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-trash mr-1"></i> Delete
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
