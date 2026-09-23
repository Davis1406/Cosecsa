@extends('layout.app')

@php
    $today = \Carbon\Carbon::today();
    $facts = function ($t) use ($today) {
        $due = $t->due_date ? \Carbon\Carbon::parse($t->due_date) : null;
        return [
            'due'     => $due,
            'overdue' => $due && $t->status !== 'done' && $due->lt($today),
            'soon'    => $due && $t->status !== 'done' && $due->gte($today) && $due->lte($today->copy()->addDays(2)),
        ];
    };
    $count = fn ($list) => [
        'all'         => $list->count(),
        'unread'      => $list->whereNull('read_at')->count(),
        'pending'     => $list->where('status', 'pending')->count(),
        'in_progress' => $list->where('status', 'in_progress')->count(),
        'done'        => $list->where('status', 'done')->count(),
        'overdue'     => $list->filter(fn ($t) => $facts($t)['overdue'])->count(),
    ];
    $toMeCounts = $count($assignedToMe);
    $byMeCounts = $count($assignedByMe);
    $statusLabel = ['pending' => 'Pending', 'in_progress' => 'In Progress', 'done' => 'Done'];
    $initials = function ($name) {
        $p = preg_split('/\s+/', trim((string) $name));
        return strtoupper(mb_substr($p[0] ?? '?', 0, 1) . (count($p) > 1 ? mb_substr(end($p), 0, 1) : ''));
    };
@endphp

@section('content')
<style>
    .tk-page { --tk-brand:#a02626; --tk-brand-soft:rgba(160,38,38,.06); --tk-gold:#FEC503; --tk-ink:#141d23; --tk-muted:#6b7280;
               --tk-faint:#9ca3af; --tk-line:#e5e9f0; --tk-card:#fff; --tk-soft:#f7f8fa;
               --st-pending-bg:#f1f3f5; --st-pending-fg:#4b5563; --st-in_progress-bg:#fff6d6; --st-in_progress-fg:#8a6100;
               --st-done-bg:#e9f7ee; --st-done-fg:#2e7d32; --tk-red:#c62828; --tk-red-bg:#fdeeee; }
    body.dark-mode .tk-page { --tk-brand:#f48a8a; --tk-brand-soft:rgba(244,138,138,.08); --tk-ink:#f1f5f9; --tk-muted:#94a3b8; --tk-faint:#718096;
               --tk-line:#2d3748; --tk-card:#1e2330; --tk-soft:#252c3b;
               --st-pending-bg:#2b3040; --st-pending-fg:#cbd5e0; --st-in_progress-bg:#342a10; --st-in_progress-fg:#f3cf6b;
               --st-done-bg:#17311f; --st-done-fg:#7ddc97; --tk-red:#f59a9a; --tk-red-bg:#3a1c1f; }
    .tk-page { color:var(--tk-ink); }

    /* Header */
    .tk-head { display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end; justify-content:space-between; margin:6px 0 16px; }
    .tk-head h1 { font-size:1.5rem; font-weight:700; margin:0; }
    .tk-head .sub { color:var(--tk-muted); font-size:.87rem; }
    .tk-btn-ghost { border:1px solid var(--tk-line); background:var(--tk-card); color:var(--tk-ink); border-radius:8px; font-weight:600; }
    .tk-btn-ghost:hover { color:var(--tk-brand); border-color:var(--tk-brand); }

    /* Tabs */
    .tk-tabs { display:flex; gap:6px; border-bottom:1px solid var(--tk-line); margin-bottom:14px; }
    .tk-tab { background:none; border:0; padding:9px 14px; font-weight:600; color:var(--tk-muted); border-bottom:3px solid transparent; margin-bottom:-1px; }
    .tk-tab.active { color:var(--tk-brand); border-bottom-color:#a02626; }
    .tk-tab .n { background:var(--tk-soft); color:var(--tk-muted); border-radius:99px; padding:0 8px; font-size:.75rem; margin-left:6px; }
    .tk-tab.active .n { background:#a02626; color:#fff; }
    .tk-tab .dot { display:inline-block; width:8px; height:8px; border-radius:50%; background:#a02626; margin-left:6px; vertical-align:middle; }

    /* Filter chips */
    .tk-chips { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:14px; }
    .tk-chip { border:1px solid var(--tk-line); background:var(--tk-card); color:var(--tk-ink); border-radius:99px; padding:5px 12px;
               font-size:.82rem; font-weight:600; display:inline-flex; align-items:center; gap:6px; cursor:pointer; }
    .tk-chip .c { font-weight:700; }
    .tk-chip .sw { width:8px; height:8px; border-radius:50%; display:inline-block; }
    .tk-chip.active { background:#a02626; border-color:#a02626; color:#fff; }
    .tk-chip.active .sw { box-shadow:0 0 0 2px #fff; }
    .tk-chip:disabled { opacity:.45; cursor:default; }

    /* Task cards */
    .tk-list { display:flex; flex-direction:column; gap:10px; }
    .tk-card { position:relative; display:flex; gap:14px; align-items:flex-start; background:var(--tk-card); border:1px solid var(--tk-line);
               border-left:4px solid var(--tk-line); border-radius:12px; padding:14px 16px; box-shadow:0 1px 3px rgba(0,0,0,.04); transition:box-shadow .12s; }
    .tk-card:hover { box-shadow:0 4px 14px rgba(0,0,0,.07); }
    .tk-card.unread { border-left-color:#a02626; background:linear-gradient(90deg, var(--tk-brand-soft), var(--tk-card) 40%); }
    .tk-card.st-in_progress { border-left-color:var(--tk-gold); }
    .tk-card.unread.st-in_progress { border-left-color:#a02626; }
    .tk-card.is-overdue:not(.unread) { border-left-color:var(--tk-red); }
    .tk-card.st-done { border-left-color:var(--st-done-fg); opacity:.72; }
    .tk-card.st-done .tk-title { text-decoration:line-through; color:var(--tk-muted); }

    .tk-avatar { flex:none; width:38px; height:38px; border-radius:50%; background:#f3e3e3; color:#a02626; font-weight:700; font-size:.8rem;
                 display:flex; align-items:center; justify-content:center; }
    body.dark-mode .tk-avatar { background:#3a2328; color:#f48a8a; }
    .tk-main { flex:1; min-width:0; }
    .tk-title { font-size:1rem; font-weight:600; line-height:1.3; }
    .tk-card.unread .tk-title { font-weight:800; }
    .tk-new { background:#a02626; color:#fff; font-size:.64rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase;
              border-radius:4px; padding:2px 6px; margin-left:6px; vertical-align:middle; }
    .tk-desc { color:var(--tk-muted); font-size:.86rem; margin-top:3px; white-space:pre-line;
               display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; }
    .tk-meta { display:flex; flex-wrap:wrap; gap:6px 14px; align-items:center; margin-top:8px; font-size:.8rem; color:var(--tk-muted); }
    .tk-meta i { width:14px; text-align:center; color:var(--tk-faint); }
    .tk-due.soon { color:#8a6100; font-weight:600; }
    .tk-due.overdue { color:var(--tk-red); font-weight:700; }
    .tk-due.overdue i { color:var(--tk-red); }
    .tk-open, .tk-open:visited { color:#a02626 !important; font-weight:600; text-decoration:none; }
    .tk-open:hover { color:#7f0a12 !important; text-decoration:underline; }
    body.dark-mode .tk-open, body.dark-mode .tk-open:visited { color:#f48a8a !important; }

    .tk-side { flex:none; display:flex; flex-direction:column; align-items:flex-end; gap:8px; min-width:140px; }
    .tk-pill { display:inline-flex; align-items:center; gap:6px; border-radius:99px; padding:3px 11px; font-size:.78rem; font-weight:700; }
    .tk-pill::before { content:''; width:7px; height:7px; border-radius:50%; background:currentColor; }
    .tk-pill.s-pending, .tk-select.s-pending { background:var(--st-pending-bg); color:var(--st-pending-fg); }
    .tk-pill.s-in_progress, .tk-select.s-in_progress { background:var(--st-in_progress-bg); color:var(--st-in_progress-fg); }
    .tk-pill.s-done, .tk-select.s-done { background:var(--st-done-bg); color:var(--st-done-fg); }
    .tk-select { border:0; border-radius:99px; font-weight:700; font-size:.8rem; padding:4px 26px 4px 12px; height:auto; cursor:pointer; width:auto; }
    .tk-select:focus { box-shadow:0 0 0 .15rem rgba(160,38,38,.25); }
    .tk-seen { font-size:.76rem; color:var(--tk-faint); white-space:nowrap; }
    .tk-seen.yes { color:#2e7d32; font-weight:600; }
    body.dark-mode .tk-seen.yes { color:#7ddc97; }

    .tk-empty { text-align:center; color:var(--tk-muted); padding:40px 16px; border:1px dashed var(--tk-line); border-radius:12px; background:var(--tk-card); }
    .tk-empty i { font-size:1.6rem; color:var(--tk-faint); display:block; margin-bottom:8px; }
    .tk-nomatch { display:none; }

    @media (max-width: 575px) {
        .tk-card { flex-wrap:wrap; }
        .tk-side { flex-direction:row; align-items:center; justify-content:space-between; width:100%; min-width:0; }
    }
</style>

<div class="content-wrapper">
    <section class="content tk-page pt-3">
        <div class="container-fluid">
            @include('_message')

            <div class="tk-head">
                <div>
                    <h1><i class="fas fa-tasks mr-2" style="color:#a02626;"></i>My Tasks</h1>
                    <div class="sub">Tasks assigned in Messages. Opening a task marks it as read.</div>
                </div>
                <a href="{{ url('messages') }}" class="btn btn-sm tk-btn-ghost"><i class="fas fa-arrow-left mr-1"></i> Back to Messages</a>
            </div>

            <div class="tk-tabs" role="tablist">
                <button type="button" class="tk-tab active" data-tab="toMe">
                    Assigned to me <span class="n">{{ $toMeCounts['all'] }}</span>
                    @if($toMeCounts['unread'])<span class="dot" title="{{ $toMeCounts['unread'] }} unread"></span>@endif
                </button>
                <button type="button" class="tk-tab" data-tab="byMe">
                    Assigned by me <span class="n">{{ $byMeCounts['all'] }}</span>
                </button>
            </div>

            @foreach(['toMe' => [$assignedToMe, $toMeCounts], 'byMe' => [$assignedByMe, $byMeCounts]] as $pane => [$list, $cnt])
            <div class="tk-pane" data-pane="{{ $pane }}" @if($pane === 'byMe') hidden @endif>
                <div class="tk-chips">
                    @foreach([
                        'all'         => ['All', null],
                        'unread'      => [$pane === 'toMe' ? 'Unread' : 'Not seen', '#a02626'],
                        'pending'     => ['Pending', 'var(--st-pending-fg)'],
                        'in_progress' => ['In Progress', '#FEC503'],
                        'done'        => ['Done', 'var(--st-done-fg)'],
                        'overdue'     => ['Overdue', 'var(--tk-red)'],
                    ] as $key => [$label, $sw])
                        <button type="button" class="tk-chip {{ $key === 'all' ? 'active' : '' }}" data-filter="{{ $key }}" {{ $key !== 'all' && !$cnt[$key] ? 'disabled' : '' }}>
                            @if($sw)<span class="sw" style="background:{{ $sw }};"></span>@endif
                            {{ $label }} <span class="c">{{ $cnt[$key] }}</span>
                        </button>
                    @endforeach
                </div>

                <div class="tk-list">
                    @forelse($list as $t)
                        @php
                            $f = $facts($t);
                            $person = $pane === 'toMe' ? ($t->creator->name ?? '—') : ($t->assignee->name ?? '—');
                        @endphp
                        <div class="tk-card st-{{ $t->status }} {{ ($pane === 'toMe' && !$t->read_at) ? 'unread' : '' }} {{ $f['overdue'] ? 'is-overdue' : '' }}" data-task-id="{{ $t->id }}"
                             data-status="{{ $t->status }}" data-read="{{ $t->read_at ? 1 : 0 }}" data-overdue="{{ $f['overdue'] ? 1 : 0 }}">
                            <span class="tk-avatar" title="{{ $person }}">{{ $initials($person) }}</span>
                            <div class="tk-main">
                                <div class="tk-title">
                                    {{ $t->title }}
                                    @if($pane === 'toMe' && !$t->read_at)<span class="tk-new">New</span>@endif
                                </div>
                                @if($t->description)<div class="tk-desc">{{ $t->description }}</div>@endif
                                <div class="tk-meta">
                                    <span><i class="far fa-user"></i>{{ $pane === 'toMe' ? 'From' : 'For' }} <strong>{{ $person }}</strong></span>
                                    @if($f['due'])
                                        <span class="tk-due {{ $f['overdue'] ? 'overdue' : ($f['soon'] ? 'soon' : '') }}">
                                            <i class="far fa-calendar-alt"></i>{{ $f['overdue'] ? 'Overdue · ' : 'Due ' }}{{ $f['due']->format('d M Y') }}
                                        </span>
                                    @else
                                        <span><i class="far fa-calendar"></i>No due date</span>
                                    @endif
                                    <span><i class="far fa-clock"></i>Assigned {{ $t->created_at?->diffForHumans() }}</span>
                                    @if($t->conversation_id)
                                        <a href="{{ route('messages.tasks.open', $t->id) }}" class="tk-open">
                                            Open conversation <i class="fas fa-arrow-right" style="color:inherit;"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                            <div class="tk-side">
                                @if($pane === 'toMe')
                                    <select class="form-control tk-select s-{{ $t->status }}" data-task-id="{{ $t->id }}" aria-label="Status">
                                        @foreach($statusLabel as $v => $l)
                                            <option value="{{ $v }}" {{ $t->status === $v ? 'selected' : '' }}>{{ $l }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <span class="tk-pill s-{{ $t->status }}">{{ $statusLabel[$t->status] ?? $t->status }}</span>
                                    <span class="tk-seen {{ $t->read_at ? 'yes' : '' }}" title="{{ $t->read_at ? 'Seen ' . $t->read_at->format('d M Y, H:i') : 'Not opened yet' }}">
                                        @if($t->read_at)<i class="fas fa-check-double"></i> Seen @else<i class="fas fa-check"></i> Not seen yet @endif
                                    </span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="tk-empty">
                            <i class="far fa-check-circle"></i>
                            {{ $pane === 'toMe' ? 'Nothing assigned to you.' : "You haven't assigned any tasks." }}
                        </div>
                    @endforelse
                    <div class="tk-empty tk-nomatch"><i class="fas fa-filter"></i>No tasks match this filter.</div>
                </div>
            </div>
            @endforeach
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const statusLabel = { pending: 'Pending', in_progress: 'In Progress', done: 'Done' };

  // ── Tabs ────────────────────────────────────────────────────────────
  document.querySelectorAll('.tk-tab').forEach(tab => tab.addEventListener('click', function () {
    document.querySelectorAll('.tk-tab').forEach(t => t.classList.toggle('active', t === tab));
    document.querySelectorAll('.tk-pane').forEach(p => p.hidden = p.dataset.pane !== tab.dataset.tab);
  }));

  // ── Filter chips (per pane) ─────────────────────────────────────────
  function applyFilter(pane) {
    const f = (pane.querySelector('.tk-chip.active') || {}).dataset?.filter || 'all';
    let shown = 0;
    pane.querySelectorAll('.tk-card').forEach(card => {
      const ok = f === 'all'
        || (f === 'unread' && card.dataset.read === '0')
        || (f === 'overdue' && card.dataset.overdue === '1')
        || card.dataset.status === f;
      card.hidden = !ok;
      if (ok) shown++;
    });
    const nomatch = pane.querySelector('.tk-nomatch');
    if (nomatch) nomatch.style.display = (!shown && pane.querySelector('.tk-card')) ? 'block' : 'none';
  }
  document.querySelectorAll('.tk-pane').forEach(pane => {
    pane.querySelectorAll('.tk-chip').forEach(chip => chip.addEventListener('click', function () {
      pane.querySelectorAll('.tk-chip').forEach(c => c.classList.toggle('active', c === chip));
      applyFilter(pane);
    }));
  });

  // ── Status change (assignee) ────────────────────────────────────────
  function paintCard(card, status, read) {
    card.classList.remove('st-pending', 'st-in_progress', 'st-done');
    card.classList.add('st-' + status);
    card.dataset.status = status;
    if (status === 'done') { card.dataset.overdue = '0'; card.classList.remove('is-overdue'); }
    if (read !== undefined) {
      card.dataset.read = read ? '1' : '0';
      card.classList.toggle('unread', !read);
      if (read) { const n = card.querySelector('.tk-new'); if (n) n.remove(); }
    }
  }
  document.querySelectorAll('.tk-select').forEach(sel => {
    let previous = sel.value;
    sel.addEventListener('change', function () {
      const card = sel.closest('.tk-card');
      fetch(`{{ url('messages/tasks') }}/${sel.dataset.taskId}/status`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
        body: JSON.stringify({ status: sel.value }),
      })
        .then(r => { if (!r.ok) throw new Error(r.status); return r.json(); })
        .then(() => {
          previous = sel.value;
          sel.className = 'form-control tk-select s-' + sel.value;
          paintCard(card, sel.value, true);   // acting on a task marks it read
          applyFilter(card.closest('.tk-pane'));
        })
        .catch(() => { sel.value = previous; alert('Could not update task status.'); });
    });
  });

  // ── Live updates: status changes + "Seen" receipts ──────────────────
  function sync(paneName, rows) {
    const pane = document.querySelector(`.tk-pane[data-pane="${paneName}"]`);
    rows.forEach(t => {
      const card = pane.querySelector(`.tk-card[data-task-id="${t.id}"]`);
      if (!card) return;                       // new since load — shows on next visit
      if (paneName === 'toMe') {
        const sel = card.querySelector('.tk-select');
        if (sel && document.activeElement !== sel && sel.value !== t.status) {
          sel.value = t.status; sel.className = 'form-control tk-select s-' + t.status;
        }
        paintCard(card, t.status, t.read);
      } else {
        const pill = card.querySelector('.tk-pill');
        pill.className = 'tk-pill s-' + t.status; pill.textContent = statusLabel[t.status] || t.status;
        paintCard(card, t.status);
        const seen = card.querySelector('.tk-seen');
        if (t.read && !seen.classList.contains('yes')) {
          seen.classList.add('yes'); seen.title = 'Seen ' + t.read_at;
          seen.innerHTML = '<i class="fas fa-check-double"></i> Seen';
        }
      }
    });
    applyFilter(pane);
  }
  function poll() {
    fetch("{{ url('messages/tasks/poll') }}", { headers: { Accept: 'application/json' } })
      .then(r => r.ok ? r.json() : null)
      .then(d => { if (!d) return; sync('toMe', d.assigned_to_me || []); sync('byMe', d.assigned_by_me || []); })
      .catch(() => {});
  }
  setInterval(poll, 5000);
});
</script>
@endpush
