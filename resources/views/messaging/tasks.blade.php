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
            <a href="{{ url('messages') }}" class="btn btn-cosecsa-outline">
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
                <tbody id="assignedToMeBody">
                  @foreach($assignedToMe as $t)
                    <tr data-task-id="{{ $t->id }}">
                      <td>
                        <strong class="task-title">{{ $t->title }}</strong>
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
                        <select class="form-control form-control-sm task-status-select" data-task-id="{{ $t->id }}" style="width:130px;">
                          <option value="pending" {{ $t->status==='pending'?'selected':'' }}>Pending</option>
                          <option value="in_progress" {{ $t->status==='in_progress'?'selected':'' }}>In Progress</option>
                          <option value="done" {{ $t->status==='done'?'selected':'' }}>Done</option>
                        </select>
                      </td>
                    </tr>
                  @endforeach
                  @if($assignedToMe->isEmpty())
                    <tr id="assignedToMeEmpty"><td colspan="5" class="text-center text-muted py-3">Nothing assigned to you.</td></tr>
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
                <tbody id="assignedByMeBody">
                  @foreach($assignedByMe as $t)
                    <tr data-task-id="{{ $t->id }}">
                      <td>{{ $t->title }}</td>
                      <td>{{ $t->assignee->name ?? '—' }}</td>
                      <td>
                        @if($t->conversation)
                          <a href="{{ url('messages/'.$t->conversation_id) }}">Open</a>
                        @else — @endif
                      </td>
                      <td>{{ $t->due_date ? \Carbon\Carbon::parse($t->due_date)->format('d M Y') : '—' }}</td>
                      <td>
                        <span class="badge task-status-badge {{ $t->status==='done'?'badge-success':($t->status==='in_progress'?'badge-warning':'badge-secondary') }}">
                          {{ ucfirst(str_replace('_',' ',$t->status)) }}
                        </span>
                      </td>
                    </tr>
                  @endforeach
                  @if($assignedByMe->isEmpty())
                    <tr id="assignedByMeEmpty"><td colspan="5" class="text-center text-muted py-3">You haven't assigned any tasks.</td></tr>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const toMeBody = document.getElementById('assignedToMeBody');
  const byMeBody = document.getElementById('assignedByMeBody');
  const badgeClass = { done: 'badge-success', in_progress: 'badge-warning', pending: 'badge-secondary' };
  const statusLabel = s => s.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase());

  // Update a task's status via AJAX (assignee changing their own status)
  toMeBody.addEventListener('change', function (e) {
    const sel = e.target.closest('.task-status-select');
    if (!sel) return;
    fetch(`{{ url('messages/tasks') }}/${sel.dataset.taskId}/status`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
      body: JSON.stringify({ status: sel.value }),
    }).catch(() => alert('Could not update task status.'));
  });

  function upsertRows(tbody, rows, emptyId, withStatusSelect) {
    if (rows.length > 0) {
      const empty = document.getElementById(emptyId);
      if (empty) empty.remove();
    }
    rows.forEach(t => {
      let tr = tbody.querySelector(`[data-task-id="${t.id}"]`);
      if (!tr) {
        // A brand-new task assigned since the page loaded — skip; it'll
        // appear on next full navigation. Keeps polling lightweight.
        return;
      }
      const statusCell = tr.querySelector(withStatusSelect ? '.task-status-select' : '.task-status-badge');
      if (!statusCell) return;
      if (withStatusSelect) {
        if (document.activeElement !== statusCell) statusCell.value = t.status;
      } else {
        statusCell.className = 'badge task-status-badge ' + badgeClass[t.status];
        statusCell.textContent = statusLabel(t.status);
      }
    });
  }

  function pollTasks() {
    fetch("{{ url('messages/tasks/poll') }}")
      .then(r => r.ok ? r.json() : null)
      .then(data => {
        if (!data) return;
        upsertRows(toMeBody, data.assigned_to_me || [], 'assignedToMeEmpty', true);
        upsertRows(byMeBody, data.assigned_by_me || [], 'assignedByMeEmpty', false);
      })
      .catch(() => {});
  }
  setInterval(pollTasks, 5000);
});
</script>
@endpush
