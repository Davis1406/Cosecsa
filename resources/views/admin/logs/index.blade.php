@extends('layout.app')

@php
    $roleNames = [1=>'Admin',2=>'Trainee',3=>'Candidate',4=>'Programme Director',5=>'Country Representative',7=>'Fellow',8=>'Member',9=>'Examiner'];
@endphp

@section('content')
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-12">
            <h1 style="font-size:1.4rem;">System Logs</h1>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        @include('_message')

        <ul class="nav nav-tabs mb-3">
        <style>.nav-tabs .nav-link.active{background-color:#a02626!important;color:#fff!important;border-color:#a02626!important;}.nav-tabs .nav-link{color:#a02626!important;}.nav-tabs .nav-link:hover{background-color:#FEC503!important;color:#000!important;border-color:#FEC503!important;}</style>
          <li class="nav-item">
            <a class="nav-link {{ $tab === 'logins' ? 'active' : '' }}" href="{{ url('admin/logs?tab=logins') }}">
              <i class="fas fa-sign-in-alt mr-1"></i> Logins
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ $tab === 'changes' ? 'active' : '' }}" href="{{ url('admin/logs?tab=changes') }}">
              <i class="fas fa-history mr-1"></i> Record Changes
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link {{ $tab === 'emails' ? 'active' : '' }}" href="{{ url('admin/logs?tab=emails') }}">
              <i class="fas fa-envelope mr-1"></i> Emails Dispatched
            </a>
          </li>
        </ul>

        <div class="card">
          <div class="card-body">
            <form method="get" class="form-inline mb-3">
              <input type="hidden" name="tab" value="{{ $tab }}">
              <input type="text" name="q" value="{{ request('q') }}" class="form-control mr-2"
                     placeholder="Search…" style="max-width:240px;">
              @if($tab === 'changes')
                <select name="model_type" class="form-control mr-2">
                  <option value="">All record types</option>
                  @foreach($modelTypes ?? [] as $mt)
                    <option value="{{ $mt }}" {{ request('model_type')===$mt?'selected':'' }}>{{ $mt }}</option>
                  @endforeach
                </select>
                <select name="action" class="form-control mr-2">
                  <option value="">All actions</option>
                  <option value="created" {{ request('action')==='created'?'selected':'' }}>Created</option>
                  <option value="updated" {{ request('action')==='updated'?'selected':'' }}>Updated</option>
                  <option value="deleted" {{ request('action')==='deleted'?'selected':'' }}>Deleted</option>
                </select>
              @endif
              <button type="submit" class="btn mr-2" style="background-color:#FEC503;border-color:#FEC503;color:#000;">Filter</button>
              <a href="{{ url('admin/logs?tab='.$tab) }}" class="btn" style="background-color:#a02626;border-color:#a02626;color:#fff;">Clear</a>
            </form>

            @if($tab === 'logins')
              <div class="table-responsive">
                <table class="table table-striped table-sm">
                  <thead>
                    <tr>
                      <th>#</th><th>Name</th><th>Email</th><th>Role</th><th>IP Address</th><th>Logged In At</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($records as $r)
                      <tr>
                        <td>{{ $loop->iteration + ($records->currentPage()-1)*$records->perPage() }}</td>
                        <td>{{ $r->name }}</td>
                        <td>{{ $r->email }}</td>
                        <td>{{ $roleNames[$r->role_type] ?? '—' }}</td>
                        <td>{{ $r->ip_address }}</td>
                        <td>{{ date('d-m-Y H:i A', strtotime($r->logged_in_at)) }}</td>
                      </tr>
                    @endforeach
                    @if($records->isEmpty())
                      <tr><td colspan="6" class="text-center text-muted py-3">No logins recorded yet.</td></tr>
                    @endif
                  </tbody>
                </table>
              </div>
            @elseif($tab === 'changes')
              <div class="table-responsive">
                <table class="table table-striped table-sm">
                  <thead>
                    <tr>
                      <th>#</th><th>When</th><th>Who</th><th>Action</th><th>Record</th><th>What Changed</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($records as $r)
                      <tr>
                        <td>{{ $loop->iteration + ($records->currentPage()-1)*$records->perPage() }}</td>
                        <td>{{ date('d-m-Y H:i A', strtotime($r->created_at)) }}</td>
                        <td>{{ $r->user_name }}</td>
                        <td>
                          <span class="badge {{ $r->action==='deleted'?'badge-danger':($r->action==='created'?'badge-success':'badge-warning') }}">
                            {{ ucfirst($r->action) }}
                          </span>
                        </td>
                        <td>{{ $r->model_type }}: {{ $r->summary }}</td>
                        <td style="font-size:.8rem;">
                          @if($r->changes)
                            @php $changes = json_decode($r->changes, true); @endphp
                            @foreach($changes as $field => $vals)
                              <div><strong>{{ $field }}</strong>: {{ $vals['old'] ?? '—' }} → {{ $vals['new'] ?? '—' }}</div>
                            @endforeach
                          @else
                            —
                          @endif
                        </td>
                      </tr>
                    @endforeach
                    @if($records->isEmpty())
                      <tr><td colspan="6" class="text-center text-muted py-3">No changes recorded yet.</td></tr>
                    @endif
                  </tbody>
                </table>
              </div>
            @else
              <div class="table-responsive">
                <table class="table table-striped table-sm">
                  <thead>
                    <tr><th>#</th><th>Sent At</th><th>To</th><th>From</th><th>Reply-To</th><th>Subject</th><th>Type</th><th></th></tr>
                  </thead>
                  <tbody>
                    @foreach($records as $r)
                      <tr>
                        <td>{{ $loop->iteration + ($records->currentPage()-1)*$records->perPage() }}</td>
                        <td>{{ date('d-m-Y H:i A', strtotime($r->sent_at)) }}</td>
                        <td>{{ $r->to_address }}</td>
                        <td>
                          @if($r->from_name ?? null)
                            {{ $r->from_name }} @if($r->from_address ?? null)<span class="text-muted">&lt;{{ $r->from_address }}&gt;</span>@endif
                          @else
                            {{ $r->from_address ?? '—' }}
                          @endif
                        </td>
                        <td>
                          @if($r->reply_to_name ?? null)
                            {{ $r->reply_to_name }} @if($r->reply_to_address ?? null)<span class="text-muted">&lt;{{ $r->reply_to_address }}&gt;</span>@endif
                          @else
                            {{ $r->reply_to_address ?? '—' }}
                          @endif
                        </td>
                        <td>{{ $r->subject }}</td>
                        <td>{{ $r->mailable ? class_basename($r->mailable) : '—' }}</td>
                        <td>
                          <button type="button" class="btn btn-sm btn-cosecsa-outline pr-view-email-btn"
                                  data-id="{{ $r->id }}" data-subject="{{ $r->subject }}" data-to="{{ $r->to_address }}"
                                  data-sent-at="{{ date('d-m-Y H:i A', strtotime($r->sent_at)) }}">
                            <i class="fas fa-eye mr-1"></i> View
                          </button>
                        </td>
                      </tr>
                    @endforeach
                    @if($records->isEmpty())
                      <tr><td colspan="8" class="text-center text-muted py-3">No emails recorded yet.</td></tr>
                    @endif
                  </tbody>
                </table>
              </div>
            @endif

            <div class="d-flex justify-content-end mt-2">
              {!! $records->links() !!}
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Email content view modal (Emails Dispatched tab) -->
    <div class="modal fade" id="prViewEmailModal" tabindex="-1" role="dialog" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="fas fa-envelope-open-text mr-1"></i> <span id="prViewEmailSubject">Email</span>
            </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          </div>
          <div class="modal-body">
            <div class="mb-2" style="font-size:.85rem;color:#6b7280;">
              <div><strong>To:</strong> <span id="prViewEmailTo"></span></div>
              <div><strong>Sent:</strong> <span id="prViewEmailSentAt"></span></div>
            </div>
            <div id="prViewEmailLoading" class="text-center text-muted py-4">
              <i class="fas fa-spinner fa-spin mr-1"></i> Loading…
            </div>
            <div id="prViewEmailError" class="alert alert-danger d-none"></div>
            <iframe id="prViewEmailFrame" class="d-none" style="width:100%;min-height:400px;border:1px solid #dee2e6;border-radius:4px;background:#fff;" sandbox=""></iframe>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  @push('scripts')
    <script>
      document.addEventListener('DOMContentLoaded', function () {
        var modalEl = document.getElementById('prViewEmailModal');
        if (!modalEl) return;
        var $modal = window.jQuery ? jQuery(modalEl) : null;

        document.querySelectorAll('.pr-view-email-btn').forEach(function (btn) {
          btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-id');
            document.getElementById('prViewEmailSubject').textContent = btn.getAttribute('data-subject') || 'Email';
            document.getElementById('prViewEmailTo').textContent = btn.getAttribute('data-to') || '—';
            document.getElementById('prViewEmailSentAt').textContent = btn.getAttribute('data-sent-at') || '—';

            var loading = document.getElementById('prViewEmailLoading');
            var errorBox = document.getElementById('prViewEmailError');
            var frame = document.getElementById('prViewEmailFrame');
            loading.classList.remove('d-none');
            errorBox.classList.add('d-none');
            frame.classList.add('d-none');
            if (frame._blobUrl) { URL.revokeObjectURL(frame._blobUrl); frame._blobUrl = null; }
            frame.src = 'about:blank';

            if ($modal) { $modal.modal('show'); } else { modalEl.style.display = 'block'; }

            console.log('[EmailView] opening id:', id);
            fetch('{{ url('admin/logs/emails') }}/' + id, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
              .then(function (res) {
                console.log('[EmailView] fetch status:', res.status);
                if (!res.ok) { throw new Error('Could not load this email (HTTP ' + res.status + ').'); }
                return res.json();
              })
              .then(function (data) {
                console.log('[EmailView] data received — body length:', (data.body || '').length, 'is_html:', data.is_html);
                loading.classList.add('d-none');
                if (!data.body) {
                  errorBox.textContent = 'No content was saved for this email (sent before content logging was added, or a plain notification with no body).';
                  errorBox.classList.remove('d-none');
                  return;
                }
                // Use a blob URL instead of srcdoc — far more reliable across
                // browsers (srcdoc is silently ignored by some Chrome builds
                // when combined with Bootstrap 4 modal CSS).
                frame.classList.remove('d-none');
                var html = data.is_html
                  ? data.body
                  : '<pre style="white-space:pre-wrap;font-family:inherit;">' + data.body.replace(/&/g, '&amp;').replace(/</g, '&lt;') + '</pre>';
                // Replace email CID attachment references with the public logo
                // so images render instead of showing broken-image boxes.
                html = html.replace(/src=["']cid:cosecsa-logo\.png["']/gi, 'src="/dist/img/Cosecsa_Logo_email.png"');
                html = html.replace(/src=["']cid:[^"']+["']/gi, 'src="/dist/img/Cosecsa_Logo_email.png"');
                if (frame._blobUrl) { URL.revokeObjectURL(frame._blobUrl); }
                frame._blobUrl = URL.createObjectURL(new Blob([html], { type: 'text/html' }));
                frame.src = frame._blobUrl;
                console.log('[EmailView] blob URL set, iframe classes:', frame.className);
              })
              .catch(function (err) {
                console.error('[EmailView] fetch error:', err);
                loading.classList.add('d-none');
                errorBox.textContent = err.message || 'Failed to load this email.';
                errorBox.classList.remove('d-none');
              });
          });
        });
      });
    </script>
  @endpush
@endsection
