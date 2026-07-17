@extends('layout.app')

@php
    $roleNames = [1=>'Admin',2=>'Trainee',3=>'Candidate',4=>'Trainer',5=>'Country Representative',7=>'Fellow',8=>'Member',9=>'Examiner'];
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
              <button type="submit" class="btn btn-primary mr-2">Filter</button>
              <a href="{{ url('admin/logs?tab='.$tab) }}" class="btn btn-secondary">Clear</a>
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
                    <tr><th>#</th><th>Sent At</th><th>To</th><th>Subject</th><th>Type</th></tr>
                  </thead>
                  <tbody>
                    @foreach($records as $r)
                      <tr>
                        <td>{{ $loop->iteration + ($records->currentPage()-1)*$records->perPage() }}</td>
                        <td>{{ date('d-m-Y H:i A', strtotime($r->sent_at)) }}</td>
                        <td>{{ $r->to_address }}</td>
                        <td>{{ $r->subject }}</td>
                        <td>{{ $r->mailable ? class_basename($r->mailable) : '—' }}</td>
                      </tr>
                    @endforeach
                    @if($records->isEmpty())
                      <tr><td colspan="5" class="text-center text-muted py-3">No emails recorded yet.</td></tr>
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
  </div>
@endsection
