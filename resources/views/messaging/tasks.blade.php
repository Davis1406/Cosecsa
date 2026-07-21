@extends('layout.app')

@section('content')
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-6">
            <h1 style="font-size:1.4rem;">My Tasks</h1>
          </div>
          <div class="col-sm-6 text-right">
            <a href="{{ url('messages') }}" class="btn btn-secondary">
              <i class="fas fa-arrow-left mr-1"></i> Back to Messages
            </a>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        @include('_message')

        <div class="card">
          <div class="card-header"><h3 class="card-title">Assigned To Me</h3></div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-sm">
                <thead><tr><th>Title</th><th>From</th><th>Conversation</th><th>Due</th><th>Status</th></tr></thead>
                <tbody>
                  @foreach($assignedToMe as $t)
                    <tr>
                      <td>
                        <strong>{{ $t->title }}</strong>
                        @if($t->description)<br><small class="text-muted">{{ $t->description }}</small>@endif
                      </td>
                      <td>{{ $t->creator->name ?? '—' }}</td>
                      <td>
                        @if($t->conversation)
                          <a href="{{ url('messages/'.$t->conversation_id) }}">Open</a>
                        @else — @endif
                      </td>
                      <td>{{ $t->due_date ? \Carbon\Carbon::parse($t->due_date)->format('d M Y') : '—' }}</td>
                      <td>
                        <form method="POST" action="{{ url('messages/tasks/'.$t->id.'/status') }}">
                          @csrf
                          <select name="status" class="form-control form-control-sm" onchange="this.form.submit()" style="width:130px;">
                            <option value="pending" {{ $t->status==='pending'?'selected':'' }}>Pending</option>
                            <option value="in_progress" {{ $t->status==='in_progress'?'selected':'' }}>In Progress</option>
                            <option value="done" {{ $t->status==='done'?'selected':'' }}>Done</option>
                          </select>
                        </form>
                      </td>
                    </tr>
                  @endforeach
                  @if($assignedToMe->isEmpty())
                    <tr><td colspan="5" class="text-center text-muted py-3">Nothing assigned to you.</td></tr>
                  @endif
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="card-header"><h3 class="card-title">Assigned By Me</h3></div>
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-sm">
                <thead><tr><th>Title</th><th>Assigned To</th><th>Conversation</th><th>Due</th><th>Status</th></tr></thead>
                <tbody>
                  @foreach($assignedByMe as $t)
                    <tr>
                      <td>{{ $t->title }}</td>
                      <td>{{ $t->assignee->name ?? '—' }}</td>
                      <td>
                        @if($t->conversation)
                          <a href="{{ url('messages/'.$t->conversation_id) }}">Open</a>
                        @else — @endif
                      </td>
                      <td>{{ $t->due_date ? \Carbon\Carbon::parse($t->due_date)->format('d M Y') : '—' }}</td>
                      <td>
                        <span class="badge {{ $t->status==='done'?'badge-success':($t->status==='in_progress'?'badge-warning':'badge-secondary') }}">
                          {{ ucfirst(str_replace('_',' ',$t->status)) }}
                        </span>
                      </td>
                    </tr>
                  @endforeach
                  @if($assignedByMe->isEmpty())
                    <tr><td colspan="5" class="text-center text-muted py-3">You haven't assigned any tasks.</td></tr>
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
