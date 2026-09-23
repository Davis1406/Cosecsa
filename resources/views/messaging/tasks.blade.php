@extends('layout.app')

@section('content')
  @php
    $today = \Carbon\Carbon::today();
    $isOverdue = fn ($t) => $t->due_date && $t->status !== 'done' && \Carbon\Carbon::parse($t->due_date)->lt($today);
    $isSoon    = fn ($t) => $t->due_date && $t->status !== 'done' && \Carbon\Carbon::parse($t->due_date)->between($today, $today->copy()->addDays(2));
    $counts = fn ($list) => [
      'all'         => $list->count(),
      'unread'      => $list->whereNull('read_at')->count(),
      'pending'     => $list->where('status', 'pending')->count(),
      'in_progress' => $list->where('status', 'in_progress')->count(),
      'done'        => $list->where('status', 'done')->count(),
      'overdue'     => $list->filter($isOverdue)->count(),
    ];
    $statusBadge = ['pending' => 'badge-secondary', 'in_progress' => 'badge-warning', 'done' => 'badge-success'];
    $statusLabel = ['pending' => 'Pending', 'in_progress' => 'In Progress', 'done' => 'Done'];
    $panes = [
      'toMe' => ['Assigned To Me', $assignedToMe, $counts($assignedToMe), 'Nothing assigned to you.'],
      'byMe' => ['Assigned By Me', $assignedByMe, $counts($assignedByMe), "You haven't assigned any tasks."],
    ];
  @endphp

  <style>
    .nav-tabs .nav-link.active { background-color:#a02626 !important; color:#fff !important; border-color:#a02626 !important; }
    .nav-tabs .nav-link { color:#a02626 !important; }
    .nav-tabs .nav-link:hover { background-color:#FEC503 !important; color:#000 !important; border-color:#FEC503 !important; }
    .tasks-table td { vertical-align: middle; }
    .tasks-table tr.task-unread { box-shadow: inset 3px 0 0 #a02626; background: rgba(160,38,38,.04); }
    .tasks-table tr.task-unread .task-title { font-weight: 700; }
    .tasks-table tr.task-done .task-title { text-decoration: line-through; }
    .tasks-table tr.task-done td { color: #6c757d; }
    body.dark-mode .tasks-table tr.task-unread { background: rgba(244,138,138,.06); }
    .badge-new { background:#a02626; color:#fff; }
    .task-filters .btn { margin: 0 4px 4px 0; }
  </style>

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

        <ul class="nav nav-tabs mb-3" role="tablist">
          @foreach($panes as $key => [$label, $list, $c])
            <li class="nav-item">
              <a class="nav-link {{ $loop->first ? 'active' : '' }}" data-toggle="tab" href="#pane-{{ $key }}" role="tab">
                {{ $label }}
                <span class="badge badge-light ml-1">{{ $c['all'] }}</span>
                @if($key === 'toMe' && $c['unread'])
                  <span class="badge badge-new ml-1">{{ $c['unread'] }} new</span>
                @endif
              </a>
            </li>
          @endforeach
        </ul>

        <div class="tab-content">
          @foreach($panes as $key => [$label, $list, $c, $emptyText])
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="pane-{{ $key }}" role="tabpanel">
              <div class="card">
                <div class="card-header">
                  <div class="task-filters" data-pane="{{ $key }}">
                    @foreach([
                      'all'         => 'All',
                      'unread'      => $key === 'toMe' ? 'Unread' : 'Not Seen',
                      'pending'     => 'Pending',
                      'in_progress' => 'In Progress',
                      'done'        => 'Done',
                      'overdue'     => 'Overdue',
                    ] as $f => $fLabel)
                      <button type="button" class="btn btn-sm {{ $f === 'all' ? 'btn-cosecsa' : 'btn-cosecsa-outline' }}" data-filter="{{ $f }}"
                              {{ $f !== 'all' && !$c[$f] ? 'disabled' : '' }}>
                        {{ $fLabel }} <span class="badge badge-light ml-1">{{ $c[$f] }}</span>
                      </button>
                    @endforeach
                  </div>
                </div>
                <div class="card-body p-0">
                  <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0 tasks-table" id="table-{{ $key }}">
                      <thead>
                        <tr>
                          <th>Task</th>
                          <th>{{ $key === 'toMe' ? 'From' : 'Assigned To' }}</th>
                          <th>Due</th>
                          <th>Status</th>
                          @if($key === 'byMe')<th>Seen</th>@endif
                          <th></th>
                        </tr>
                      </thead>
                      <tbody>
                        @forelse($list as $t)
                          @php $overdue = $isOverdue($t); @endphp
                          <tr data-task-id="{{ $t->id }}" data-status="{{ $t->status }}" data-read="{{ $t->read_at ? 1 : 0 }}" data-overdue="{{ $overdue ? 1 : 0 }}"
                              class="{{ ($key === 'toMe' && !$t->read_at) ? 'task-unread' : '' }} {{ $t->status === 'done' ? 'task-done' : '' }}">
                            <td>
                              <span class="task-title">{{ $t->title }}</span>
                              @if($key === 'toMe' && !$t->read_at)<span class="badge badge-new ml-1 task-new">New</span>@endif
                              @if($t->description)<br><small class="text-muted">{{ \Illuminate\Support\Str::limit($t->description, 140) }}</small>@endif
                            </td>
                            <td class="text-nowrap">{{ $key === 'toMe' ? ($t->creator->name ?? '—') : ($t->assignee->name ?? '—') }}</td>
                            <td class="text-nowrap">
                              @if($t->due_date)
                                @if($overdue)
                                  <span class="text-danger font-weight-bold"><i class="fas fa-exclamation-circle mr-1"></i>{{ \Carbon\Carbon::parse($t->due_date)->format('d M Y') }}</span>
                                @elseif($isSoon($t))
                                  <span class="text-warning font-weight-bold">{{ \Carbon\Carbon::parse($t->due_date)->format('d M Y') }}</span>
                                @else
                                  {{ \Carbon\Carbon::parse($t->due_date)->format('d M Y') }}
                                @endif
                              @else
                                <span class="text-muted">—</span>
                              @endif
                            </td>
                            <td>
                              @if($key === 'toMe')
                                <select class="form-control form-control-sm task-status-select" data-task-id="{{ $t->id }}" style="width:130px;">
                                  @foreach($statusLabel as $v => $l)
                                    <option value="{{ $v }}" {{ $t->status === $v ? 'selected' : '' }}>{{ $l }}</option>
                                  @endforeach
                                </select>
                              @else
                                <span class="badge task-status-badge {{ $statusBadge[$t->status] ?? 'badge-light' }}">{{ $statusLabel[$t->status] ?? $t->status }}</span>
                              @endif
                            </td>
                            @if($key === 'byMe')
                              <td class="text-nowrap task-seen">
                                @if($t->read_at)
                                  <span class="text-success" title="Seen {{ $t->read_at->format('d M Y, H:i') }}"><i class="fas fa-check-double mr-1"></i>Seen</span>
                                @else
                                  <span class="text-muted"><i class="fas fa-check mr-1"></i>Not seen</span>
                                @endif
                              </td>
                            @endif
                            <td class="text-right text-nowrap">
                              @if($t->conversation_id)
                                <a href="{{ route('messages.tasks.open', $t->id) }}" class="cosecsa-link">Open <i class="fas fa-external-link-alt ml-1" style="font-size:.75em;"></i></a>
                              @endif
                            </td>
                          </tr>
                        @empty
                          <tr><td colspan="{{ $key === 'byMe' ? 6 : 5 }}" class="text-center text-muted py-3">{{ $emptyText }}</td></tr>
                        @endforelse
                        <tr class="task-nomatch" style="display:none;"><td colspan="{{ $key === 'byMe' ? 6 : 5 }}" class="text-center text-muted py-3">No tasks match this filter.</td></tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </section>
  </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const badgeClass = { done: 'badge-success', in_progress: 'badge-warning', pending: 'badge-secondary' };
  const statusLabel = { pending: 'Pending', in_progress: 'In Progress', done: 'Done' };

  // ── Filters ─────────────────────────────────────────────────────────
  function applyFilter(pane) {
    const group = document.querySelector(`.task-filters[data-pane="${pane}"]`);
    const f = group.querySelector('.btn-cosecsa').dataset.filter;
    let shown = 0;
    document.querySelectorAll(`#table-${pane} tbody tr[data-task-id]`).forEach(tr => {
      const ok = f === 'all'
        || (f === 'unread' && tr.dataset.read === '0')
        || (f === 'overdue' && tr.dataset.overdue === '1')
        || tr.dataset.status === f;
      tr.style.display = ok ? '' : 'none';
      if (ok) shown++;
    });
    const nomatch = document.querySelector(`#table-${pane} .task-nomatch`);
    const hasRows = document.querySelector(`#table-${pane} tr[data-task-id]`);
    nomatch.style.display = (hasRows && !shown) ? '' : 'none';
  }
  document.querySelectorAll('.task-filters').forEach(group => {
    group.addEventListener('click', function (e) {
      const btn = e.target.closest('[data-filter]');
      if (!btn || btn.disabled) return;
      group.querySelectorAll('[data-filter]').forEach(b => {
        b.classList.toggle('btn-cosecsa', b === btn);
        b.classList.toggle('btn-cosecsa-outline', b !== btn);
      });
      applyFilter(group.dataset.pane);
    });
  });

  // ── Row state ───────────────────────────────────────────────────────
  function paintRow(tr, status, read) {
    tr.dataset.status = status;
    tr.classList.toggle('task-done', status === 'done');
    if (status === 'done') tr.dataset.overdue = '0';
    if (read !== undefined && tr.closest('#table-toMe')) {
      tr.dataset.read = read ? '1' : '0';
      tr.classList.toggle('task-unread', !read);
      if (read) { const n = tr.querySelector('.task-new'); if (n) n.remove(); }
    }
  }

  // Assignee changes status (also marks the task read server-side)
  document.getElementById('table-toMe').addEventListener('change', function (e) {
    const sel = e.target.closest('.task-status-select');
    if (!sel) return;
    const tr = sel.closest('tr');
    const previous = tr.dataset.status;
    fetch(`{{ url('messages/tasks') }}/${sel.dataset.taskId}/status`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
      body: JSON.stringify({ status: sel.value }),
    })
      .then(r => { if (!r.ok) throw new Error(r.status); })
      .then(() => { paintRow(tr, sel.value, true); applyFilter('toMe'); })
      .catch(() => { sel.value = previous; alert('Could not update task status.'); });
  });

  // ── Live updates: status + "Seen" ───────────────────────────────────
  function sync(pane, rows) {
    rows.forEach(t => {
      const tr = document.querySelector(`#table-${pane} tr[data-task-id="${t.id}"]`);
      if (!tr) return;
      if (pane === 'toMe') {
        const sel = tr.querySelector('.task-status-select');
        if (sel && document.activeElement !== sel) sel.value = t.status;
        paintRow(tr, t.status, t.read);
      } else {
        const badge = tr.querySelector('.task-status-badge');
        badge.className = 'badge task-status-badge ' + badgeClass[t.status];
        badge.textContent = statusLabel[t.status];
        paintRow(tr, t.status);
        if (t.read) {
          tr.dataset.read = '1';
          tr.querySelector('.task-seen').innerHTML = `<span class="text-success" title="Seen ${t.read_at}"><i class="fas fa-check-double mr-1"></i>Seen</span>`;
        }
      }
    });
    applyFilter(pane);
  }
  setInterval(function () {
    fetch("{{ url('messages/tasks/poll') }}", { headers: { Accept: 'application/json' } })
      .then(r => r.ok ? r.json() : null)
      .then(d => { if (!d) return; sync('toMe', d.assigned_to_me || []); sync('byMe', d.assigned_by_me || []); })
      .catch(() => {});
  }, 5000);
});
</script>
@endpush
