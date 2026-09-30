@extends('layout.app')

@section('content')
  <style>
    /* Unread vs read, same treatment as My Tasks: maroon left edge, light tint, bold. */
    #conversationList .conv-item { border-left:4px solid transparent; }
    #conversationList .conv-name { font-weight:500; color:#2d3748; }
    #conversationList .conv-item.is-unread { border-left-color:#a02626; background:rgba(160,38,38,.05); }
    #conversationList .conv-item.is-unread:hover { background:rgba(160,38,38,.09); }
    #conversationList .conv-item.is-unread .conv-name { font-weight:700; color:#141d23; }
    #conversationList .conv-item.is-unread .conv-preview { color:#141d23 !important; font-weight:600; }
    #conversationList .conv-item.is-unread .conv-time { color:#a02626 !important; font-weight:700; }
    #conversationList .conv-badge { display:none; }
    #conversationList .conv-item.is-unread .conv-badge { display:inline-block; }
    .badge-new { background:#a02626; color:#fff; }
    body.dark-mode #conversationList .conv-name { color:#cbd5e0; }
    body.dark-mode #conversationList .conv-item.is-unread { border-left-color:#f48a8a; background:rgba(244,138,138,.08); }
    body.dark-mode #conversationList .conv-item.is-unread .conv-name,
    body.dark-mode #conversationList .conv-item.is-unread .conv-preview { color:#f1f5f9 !important; }
    body.dark-mode #conversationList .conv-item.is-unread .conv-time { color:#f48a8a !important; }
  </style>

  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-6">
            <h1 style="font-size:1.4rem;">Messages</h1>
          </div>
          <div class="col-sm-6 text-right">
            <a href="{{ url('messages/tasks') }}" class="btn btn-cosecsa-outline">
              <i class="fas fa-tasks mr-1"></i> My Tasks
            </a>
            @if(Auth::user()->user_type == 1)
              <a href="{{ url('messages/groups') }}" class="btn btn-cosecsa-outline">
                <i class="fas fa-users mr-1"></i> Discussion Groups
              </a>
            @endif
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        @include('_message')

        <div class="card">
          <div class="card-header">
            <h3 class="card-title"><i class="fas fa-user-plus mr-1"></i> New Message</h3>
          </div>
          <div class="card-body">
            <input type="text" id="newMsgSearch" class="form-control" placeholder="Search by name or email…" autocomplete="off">
            <div id="newMsgResults" class="list-group mt-2" style="display:none;"></div>
          </div>
        </div>

        <div class="card">
          <div class="card-header"><h3 class="card-title">Conversations</h3></div>
          <div class="card-body p-0">
            <div class="list-group list-group-flush" id="conversationList">
              @foreach($conversations as $c)
                @php $last = $c->latestMessage; @endphp
                <a href="{{ url('messages/'.$c->id) }}" class="list-group-item list-group-item-action conv-item {{ $c->unread_count ? 'is-unread' : '' }}" data-conv-id="{{ $c->id }}">
                  <div class="d-flex justify-content-between">
                    <span class="conv-name">
                      @if($c->type === 'group')<i class="fas fa-users text-muted mr-1"></i>@endif
                      {{ $c->display_name }}
                    </span>
                    <small class="text-muted conv-time">{{ $last ? $last->created_at->diffForHumans() : '' }}</small>
                  </div>
                  <div class="d-flex justify-content-between align-items-center">
                    <div class="text-muted conv-preview" style="font-size:.85rem;">
                      {{ $last ? \Illuminate\Support\Str::limit(strip_tags($last->body), 90) : 'No messages yet.' }}
                    </div>
                    <span class="badge badge-pill badge-new conv-badge ml-2" title="Unread messages">{{ $c->unread_count }}</span>
                  </div>
                </a>
              @endforeach
              @if($conversations->isEmpty())
                <div class="text-center text-muted py-4" id="noConversationsPlaceholder">No conversations yet — search above to start one.</div>
              @endif
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
  const input = document.getElementById('newMsgSearch');
  const box = document.getElementById('newMsgResults');
  let timer = null;

  input.addEventListener('input', function () {
    clearTimeout(timer);
    const q = this.value.trim();
    if (!q) { box.style.display = 'none'; return; }

    timer = setTimeout(function () {
      fetch("{{ url('messages/search-users') }}?q=" + encodeURIComponent(q))
        .then(r => r.json())
        .then(rows => {
          box.innerHTML = '';
          if (rows.length === 0) {
            box.style.display = 'block';
            box.innerHTML = '<div class="list-group-item text-muted">No matches.</div>';
            return;
          }
          box.style.display = 'block';
          rows.forEach(u => {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = "{{ url('messages/start') }}";
            form.innerHTML = `@csrf <input type="hidden" name="user_id" value="${u.id}">
              <button type="submit" class="list-group-item list-group-item-action" style="border:none; width:100%; text-align:left;">
                ${u.name} <small class="text-muted">${u.email ?? ''}</small>
              </button>`;
            box.appendChild(form);
          });
        });
    }, 300);
  });

  // ── Poll for live-updated previews/timestamps/ordering ──────────────
  const list = document.getElementById('conversationList');
  function pollConversations() {
    fetch("{{ url('messages/poll-summary') }}")
      .then(r => r.ok ? r.json() : null)
      .then(data => {
        if (!data) return;
        (data.conversation_previews || []).forEach(c => {
          const row = list.querySelector(`[data-conv-id="${c.id}"]`);
          if (!row) return;
          row.querySelector('.conv-time').textContent = c.last_human || '';
          row.querySelector('.conv-preview').textContent = c.preview;
          row.classList.toggle('is-unread', c.unread > 0);
          row.querySelector('.conv-badge').textContent = c.unread;
          list.appendChild(row); // re-append in server order (newest last_message_at first)
        });
      })
      .catch(() => {});
  }
  if (list) setInterval(pollConversations, 10000);
});
</script>
@endpush
