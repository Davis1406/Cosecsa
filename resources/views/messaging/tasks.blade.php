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

    /* Summary tiles — the app's .stitch-tile, clickable like the hospital dashboard */
    .tile-clickable { cursor:pointer; }
    .tile-clickable.tile-active { outline:2px solid #a02626; outline-offset:2px; }
    .tile-clickable.tile-disabled { opacity:.5; cursor:default; }
    .stitch-tile-red { border-left-color:#dc3545; color:#dc3545; }
    .stitch-tile .stitch-tile-value { font-size:1.6rem; }

    /* Task cards share the stitch-tile look: white, 8px radius, 4px state border */
    .task-item { background:#fff; border-radius:8px; border-left:4px solid #dee2e6; box-shadow:0 1px 4px rgba(0,0,0,.07);
                 padding:14px 18px; margin-bottom:10px; display:flex; align-items:center; gap:16px; }
    .task-item:hover { box-shadow:0 4px 12px rgba(0,0,0,.09); }
    .task-item.is-unread  { border-left-color:#a02626; }
    .task-item.is-progress { border-left-color:#FEC503; }
    .task-item.is-overdue { border-left-color:#dc3545; }
    .task-item.is-done    { border-left-color:#28a745; opacity:.75; }
    .task-item.is-unread.is-progress, .task-item.is-unread.is-overdue { border-left-color:#a02626; }
    .task-item .task-title { font-size:1rem; font-weight:600; color:#141d23; margin:0; }
    .task-item.is-unread .task-title { font-weight:700; }
    .task-item.is-done .task-title { text-decoration:line-through; color:#6c757d; }
    .task-item .task-desc { color:#6c757d; font-size:.875rem; margin:2px 0 0; }
    .task-item .task-meta { font-size:.8rem; color:#6c757d; margin-top:6px; }
    .task-item .task-meta span { margin-right:14px; white-space:nowrap; }
    .task-item .task-meta i { margin-right:4px; }
    .task-item .task-side { flex:none; display:flex; align-items:center; gap:10px; }
    .badge-new { background:#a02626; color:#fff; }
    body.dark-mode .task-item { background:#1e2330; }
    body.dark-mode .task-item .task-title { color:#f1f5f9; }
    body.dark-mode .task-item.is-done .task-title, body.dark-mode .task-item .task-desc, body.dark-mode .task-item .task-meta { color:#94a3b8; }
    @media (max-width: 767px) { .task-item { flex-wrap:wrap; } .task-item .task-side { width:100%; justify-content:space-between; } }
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
                {{ $label }} <span class="badge badge-light ml-1">{{ $c['all'] }}</span>
                @if($key === 'toMe' && $c['unread'])<span class="badge badge-new ml-1">{{ $c['unread'] }} new</span>@endif
              </a>
            </li>
          @endforeach
        </ul>

        <div class="tab-content">
          @foreach($panes as $key => [$label, $list, $c, $emptyText])
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="pane-{{ $key }}" role="tabpanel" data-pane="{{ $key }}">

              <div class="row">
                @foreach([
                  'all'         => ['All Tasks', 'teal'],
                  'unread'      => [$key === 'toMe' ? 'Unread' : 'Not Seen', 'maroon'],
                  'pending'     => ['Pending', 'blue'],
                  'in_progress' => ['In Progress', 'gold'],
                  'done'        => ['Done', 'green'],
                  'overdue'     => ['Overdue', 'red'],
                ] as $f => [$fLabel, $colour])
                  <div class="col-lg-2 col-md-4 col-6 mb-3">
                    <div class="stitch-tile stitch-tile-{{ $colour }} tile-clickable {{ $f === 'all' ? 'tile-active' : '' }} {{ $f !== 'all' && !$c[$f] ? 'tile-disabled' : '' }}"
                         data-filter="{{ $f }}" title="Show {{ strtolower($fLabel) }}">
                      <div class="stitch-tile-label">{{ $fLabel }}</div>
                      <div class="stitch-tile-value">{{ $c[$f] }}</div>
                      <div class="stitch-tile-bar"><div class="stitch-tile-fill" style="width:{{ $c['all'] ? round($c[$f] / $c['all'] * 100) : 0 }}%"></div></div>
                    </div>
                  </div>
                @endforeach
              </div>

              <div class="task-list">
                @forelse($list as $t)
                  @php
                    $overdue = $isOverdue($t);
                    $unread  = $key === 'toMe' && !$t->read_at;
                    $person  = $key === 'toMe' ? ($t->creator->name ?? '—') : ($t->assignee->name ?? '—');
                  @endphp
                  <div class="task-item {{ $unread ? 'is-unread' : '' }} {{ $t->status === 'in_progress' ? 'is-progress' : '' }} {{ $t->status === 'done' ? 'is-done' : '' }} {{ $overdue ? 'is-overdue' : '' }}"
                       data-task-id="{{ $t->id }}" data-status="{{ $t->status }}" data-read="{{ $t->read_at ? 1 : 0 }}" data-overdue="{{ $overdue ? 1 : 0 }}">
                    <div class="flex-grow-1" style="min-width:0;">
                      <p class="task-title">
                        {{ $t->title }}
                        @if($unread)<span class="badge badge-new ml-1 task-new">New</span>@endif
                      </p>
                      @if($t->description)<p class="task-desc">{{ \Illuminate\Support\Str::limit($t->description, 180) }}</p>@endif
                      <div class="task-meta">
                        <span><i class="fas fa-user"></i>{{ $key === 'toMe' ? 'From' : 'To' }} <strong>{{ $person }}</strong></span>
                        @if($t->due_date)
                          @if($overdue)
                            <span class="text-danger font-weight-bold"><i class="fas fa-exclamation-circle"></i>Overdue — {{ \Carbon\Carbon::parse($t->due_date)->format('d M Y') }}</span>
                          @elseif($isSoon($t))
                            <span class="text-warning font-weight-bold"><i class="fas fa-calendar-day"></i>Due {{ \Carbon\Carbon::parse($t->due_date)->format('d M Y') }}</span>
                          @else
                            <span><i class="fas fa-calendar-alt"></i>Due {{ \Carbon\Carbon::parse($t->due_date)->format('d M Y') }}</span>
                          @endif
                        @endif
                        <span><i class="fas fa-clock"></i>{{ $t->created_at?->diffForHumans() }}</span>
                        @if($key === 'byMe')
                          <span class="task-seen">
                            @if($t->read_at)
                              <span class="text-success" title="Seen {{ $t->read_at->format('d M Y, H:i') }}"><i class="fas fa-check-double"></i>Seen</span>
                            @else
                              <i class="fas fa-check"></i>Not seen yet
                            @endif
                          </span>
                        @endif
                      </div>
                    </div>
                    <div class="task-side">
                      @if($key === 'toMe')
                        <select class="form-control form-control-sm task-status-select" data-task-id="{{ $t->id }}" style="width:130px;">
                          @foreach($statusLabel as $v => $l)
                            <option value="{{ $v }}" {{ $t->status === $v ? 'selected' : '' }}>{{ $l }}</option>
                          @endforeach
                        </select>
                      @else
                        <span class="badge task-status-badge {{ $statusBadge[$t->status] ?? 'badge-light' }}">{{ $statusLabel[$t->status] ?? $t->status }}</span>
                      @endif
                      @if($t->conversation_id)
                        <a href="{{ route('messages.tasks.open', $t->id) }}" class="btn btn-sm btn-cosecsa-outline"><i class="fas fa-comments mr-1"></i> Open</a>
                      @endif
                    </div>
                  </div>
                @empty
                  <div class="card"><div class="card-body text-center text-muted">{{ $emptyText }}</div></div>
                @endforelse
                <div class="card task-nomatch" style="display:none;"><div class="card-body text-center text-muted">No tasks match this filter.</div></div>
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
  const pane = name => document.querySelector(`.tab-pane[data-pane="${name}"]`);

  // ── Tile filters ────────────────────────────────────────────────────
  function applyFilter(name) {
    const p = pane(name);
    const f = p.querySelector('.tile-active').dataset.filter;
    let shown = 0;
    p.querySelectorAll('.task-item').forEach(item => {
      const ok = f === 'all'
        || (f === 'unread' && item.dataset.read === '0')
        || (f === 'overdue' && item.dataset.overdue === '1')
        || item.dataset.status === f;
      item.style.display = ok ? '' : 'none';
      if (ok) shown++;
    });
    p.querySelector('.task-nomatch').style.display = (p.querySelector('.task-item') && !shown) ? '' : 'none';
  }
  document.querySelectorAll('.tab-pane[data-pane]').forEach(p => {
    p.querySelectorAll('.tile-clickable').forEach(tile => tile.addEventListener('click', function () {
      if (tile.classList.contains('tile-disabled')) return;
      p.querySelectorAll('.tile-clickable').forEach(t => t.classList.toggle('tile-active', t === tile));
      applyFilter(p.dataset.pane);
    }));
  });

  // ── Card state ──────────────────────────────────────────────────────
  function paint(item, status, read) {
    item.dataset.status = status;
    item.classList.toggle('is-progress', status === 'in_progress');
    item.classList.toggle('is-done', status === 'done');
    if (status === 'done') { item.dataset.overdue = '0'; item.classList.remove('is-overdue'); }
    if (read !== undefined && item.closest('[data-pane="toMe"]')) {
      item.dataset.read = read ? '1' : '0';
      item.classList.toggle('is-unread', !read);
      if (read) { const n = item.querySelector('.task-new'); if (n) n.remove(); }
    }
  }

  // Assignee changes status (also marks it read server-side)
  pane('toMe').addEventListener('change', function (e) {
    const sel = e.target.closest('.task-status-select');
    if (!sel) return;
    const item = sel.closest('.task-item');
    const previous = item.dataset.status;
    fetch(`{{ url('messages/tasks') }}/${sel.dataset.taskId}/status`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
      body: JSON.stringify({ status: sel.value }),
    })
      .then(r => { if (!r.ok) throw new Error(r.status); })
      .then(() => { paint(item, sel.value, true); applyFilter('toMe'); })
      .catch(() => { sel.value = previous; alert('Could not update task status.'); });
  });

  // ── Live updates: status + "Seen" ───────────────────────────────────
  function sync(name, rows) {
    rows.forEach(t => {
      const item = pane(name).querySelector(`.task-item[data-task-id="${t.id}"]`);
      if (!item) return;
      if (name === 'toMe') {
        const sel = item.querySelector('.task-status-select');
        if (sel && document.activeElement !== sel) sel.value = t.status;
        paint(item, t.status, t.read);
      } else {
        const badge = item.querySelector('.task-status-badge');
        badge.className = 'badge task-status-badge ' + badgeClass[t.status];
        badge.textContent = statusLabel[t.status];
        paint(item, t.status);
        if (t.read) {
          item.dataset.read = '1';
          item.querySelector('.task-seen').innerHTML = `<span class="text-success" title="Seen ${t.read_at}"><i class="fas fa-check-double"></i>Seen</span>`;
        }
      }
    });
    applyFilter(name);
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
