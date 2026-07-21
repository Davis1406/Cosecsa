@extends('layout.app')

@section('content')
  <style>
    /* ── Header ── */
    .thread-header-actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 8px; }
    .thread-header-actions form { margin: 0; }
    .btn-cosecsa-yellow { background:#FEC503; border-color:#FEC503; color:#3a2a00; font-weight:600; }
    .btn-cosecsa-yellow:hover, .btn-cosecsa-yellow:focus { background:#e6b200; border-color:#e6b200; color:#3a2a00; }
    .chat-title-icon {
      width:38px; height:38px; border-radius:50%; background:#a02626; color:#fff;
      display:inline-flex; align-items:center; justify-content:center; font-size:.95rem; font-weight:700;
      margin-right:10px; vertical-align:middle; flex-shrink:0;
    }

    /* ── Card / thread surface ── */
    .chat-card { border: none; border-radius: 14px; overflow: hidden; box-shadow: 0 2px 14px rgba(0,0,0,.07); }
    #threadBody {
      background: linear-gradient(180deg,#fafafa,#f2f3f5);
      padding: 20px;
    }
    body.dark-mode #threadBody { background: linear-gradient(180deg,#1f2937,#161d29) !important; }

    /* ── Message rows / avatars / bubbles ── */
    .chat-row { display:flex; align-items:flex-end; margin-bottom:16px; }
    .chat-row.theirs { justify-content:flex-start; }
    .chat-row.mine { justify-content:flex-end; }
    .chat-avatar {
      width:30px; height:30px; border-radius:50%; background:#a02626; color:#fff;
      display:flex; align-items:center; justify-content:center; font-size:.72rem; font-weight:700;
      margin-right:8px; flex-shrink:0;
    }
    .chat-bubble-col { max-width:68%; }
    .message-bubble:hover .msg-actions { display: block !important; }
    .msg-bubble-mine, .msg-bubble-theirs { position: relative; box-shadow: 0 1px 3px rgba(0,0,0,.09); }
    .msg-bubble-mine   { background: #a02626; color: #fff; border-radius: 16px 16px 4px 16px; }
    .msg-bubble-theirs { background: #ffffff; color: #222; border-radius: 16px 16px 16px 4px; }
    .msg-bubble-mine   .msg-attach-link { color: #fff; }
    .msg-bubble-theirs .msg-attach-link { color: #a02626; }
    body.dark-mode .msg-bubble-mine   { background: #a02626 !important; color: #fff !important; }
    body.dark-mode .msg-bubble-theirs { background: #374151 !important; color: #e0e0e0 !important; }
    body.dark-mode .msg-bubble-mine   .msg-attach-link { color: #fff !important; }
    body.dark-mode .msg-bubble-theirs .msg-attach-link { color: #fca5a5 !important; }
    .msg-deleted-bubble { border-radius: 16px; }
    body.dark-mode .msg-deleted-bubble { background: #374151 !important; color: #9ca3af !important; }

    /* ── Input footer ── */
    .chat-footer { background:#fff; border-top:1px solid #ececec; }
    body.dark-mode .chat-footer { border-top-color:#374151 !important; }
    .chat-input-group {
      border:1px solid #e2e2e2; border-radius:24px; overflow:hidden;
      display:flex; align-items:center; background:#fff;
    }
    body.dark-mode .chat-input-group { border-color:#374151 !important; background:#1f2937 !important; }
    .chat-input-group .form-control {
      border:none !important; box-shadow:none !important; background:transparent !important; flex:1;
    }
    .chat-input-group .btn {
      border:none !important; border-radius:0 !important; background:transparent !important; color:#a02626;
    }
    .chat-input-group .btn:hover { background:rgba(160,38,38,.08) !important; }
    .chat-input-group .btn-send { color:#fff; background:#a02626 !important; border-radius:50% !important; width:38px; height:38px; padding:0; margin:4px; display:flex; align-items:center; justify-content:center; }
    .chat-input-group .btn-send:hover { background:#8a1f1f !important; }
  </style>
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-7 d-flex align-items-center">
            <span class="chat-title-icon">
              @if($conversation->type === 'group')
                <i class="fas fa-users"></i>
              @else
                {{ strtoupper(substr($title, 0, 1)) }}
              @endif
            </span>
            <div>
              <h1 style="font-size:1.4rem;margin:0;">{{ $title }}</h1>
              @if($conversation->type === 'group')
                <p class="text-muted mb-0" style="font-size:.8rem;">
                  {{ $conversation->participants->pluck('user.name')->filter()->implode(', ') }}
                </p>
              @endif
            </div>
          </div>
          <div class="col-sm-5 text-right thread-header-actions">
            <button type="button" class="btn btn-cosecsa" data-toggle="modal" data-target="#assignTaskModal">
              <i class="fas fa-tasks mr-1"></i> Assign Task
            </button>
            @if($conversation->type === 'group' && Auth::user()->user_type == 1)
              <a href="{{ url('messages/groups/'.$conversation->id.'/edit') }}" class="btn btn-cosecsa-outline">
                <i class="fas fa-user-cog mr-1"></i> Manage Members
              </a>
            @endif
            @if($conversation->type === 'direct' || Auth::user()->user_type == 1)
              <form method="POST" action="{{ url('messages/'.$conversation->id.'/delete-conversation') }}" style="display:inline;" onsubmit="return confirm('Delete this entire chat? All messages and attachments will be permanently removed for everyone.')">
                @csrf
                <button type="submit" class="btn btn-cosecsa-yellow">
                  <i class="fas fa-trash mr-1"></i> Delete Chat
                </button>
              </form>
            @endif
            <a href="{{ url('messages') }}" class="btn btn-cosecsa-outline">
              <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        @include('_message')

        <div class="card chat-card">
          <div class="card-body" style="max-height:520px; overflow-y:auto;" id="threadBody"
               data-conversation-id="{{ $conversation->id }}" data-since="{{ now()->toDateTimeString() }}">
            @forelse($conversation->messages as $m)
              @php $mine = $m->sender_id == Auth::id(); @endphp
              <div class="chat-row {{ $mine ? 'mine' : 'theirs' }}" data-row-id="{{ $m->id }}">
                @if(!$mine)
                  <span class="chat-avatar">{{ strtoupper(substr($m->sender->name ?? '?', 0, 1)) }}</span>
                @endif
                <div class="chat-bubble-col">
                  @if(!$mine)
                    <div class="text-muted" style="font-size:.75rem;">{{ $m->sender->name ?? 'Unknown' }}</div>
                  @endif

                  @if($m->deleted_at)
                    <div class="p-2 rounded font-italic text-muted msg-deleted-bubble" style="background:#f1f1f1;">
                      <i class="fas fa-ban mr-1"></i> This message was deleted.
                    </div>
                  @else
                    <div class="p-2 rounded message-bubble {{ $mine ? 'msg-bubble-mine' : 'msg-bubble-theirs' }}" data-msg-id="{{ $m->id }}">
                      <span class="msg-body-text">{{ $m->body }}</span>

                      @foreach($m->attachments as $a)
                        <div class="mt-2">
                          @if($a->kind === 'image')
                            <a href="{{ asset('storage/'.$a->path) }}" target="_blank">
                              <img src="{{ asset('storage/'.$a->path) }}" style="max-width:220px;max-height:220px;border-radius:6px;display:block;">
                            </a>
                          @elseif($a->kind === 'audio')
                            <audio controls src="{{ asset('storage/'.$a->path) }}" style="max-width:220px;"></audio>
                          @else
                            <a href="{{ asset('storage/'.$a->path) }}" target="_blank" class="msg-attach-link" style="text-decoration:underline;">
                              <i class="fas fa-paperclip mr-1"></i>{{ $a->original_name }}
                            </a>
                          @endif
                        </div>
                      @endforeach

                      @if($mine)
                        <div class="msg-actions" style="position:absolute; top:2px; right:6px; display:none;">
                          <a href="#" class="text-white msg-edit-btn" title="Edit" style="margin-right:6px;"><i class="fas fa-edit"></i></a>
                          <a href="#" class="text-white msg-delete-btn" title="Delete"><i class="fas fa-trash"></i></a>
                        </div>
                      @endif
                    </div>

                    @if($mine)
                      <form class="msg-edit-form mt-1" style="display:none;" data-msg-id="{{ $m->id }}">
                        <div class="input-group input-group-sm">
                          <input type="text" name="body" class="form-control" value="{{ $m->body }}">
                          <div class="input-group-append">
                            <button type="submit" class="btn btn-cosecsa">Save</button>
                            <button type="button" class="btn btn-cosecsa-outline msg-edit-cancel">Cancel</button>
                          </div>
                        </div>
                      </form>
                    @endif
                  @endif

                  <div class="text-muted text-right" style="font-size:.7rem;">
                    <span class="msg-time">{{ $m->created_at->format('d M, H:i') }}</span>
                    <span class="msg-edited-tag font-italic" style="{{ ($m->edited_at && !$m->deleted_at) ? '' : 'display:none;' }}"> (edited)</span>
                  </div>
                </div>
              </div>
            @empty
              <div class="text-center text-muted py-4" id="noMessagesPlaceholder">No messages yet — say hello.</div>
            @endforelse
          </div>
          <div class="card-footer chat-footer">
            <form method="POST" action="{{ url('messages/'.$conversation->id.'/send') }}" enctype="multipart/form-data" id="sendForm">
              @csrf
              <div id="attachPreview" class="mb-2" style="display:none;"></div>
              <div class="chat-input-group">
                <label class="btn mb-0" title="Attach file" style="cursor:pointer;">
                  <i class="fas fa-paperclip"></i>
                  <input type="file" name="attachments[]" id="attachInput" multiple style="display:none;">
                </label>
                <button type="button" class="btn" id="voiceBtn" title="Record voice note">
                  <i class="fas fa-microphone"></i>
                </button>
                <input type="text" name="body" class="form-control" placeholder="Type a message…" autocomplete="off">
                <button type="submit" class="btn btn-send" title="Send"><i class="fas fa-paper-plane"></i></button>
              </div>
              <div id="recordingIndicator" class="text-danger mt-1" style="display:none; font-size:.85rem;">
                <i class="fas fa-circle fa-xs mr-1"></i> Recording… <a href="#" id="stopRecordingBtn">Stop</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- Assign Task Modal -->
  <div class="modal fade" id="assignTaskModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <form method="POST" action="{{ url('messages/'.$conversation->id.'/tasks') }}">
          @csrf
          <div class="modal-header" style="background:#a02626;color:#fff;">
            <h5 class="modal-title">Assign Task</h5>
            <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Title</label>
              <input type="text" name="title" class="form-control" required>
            </div>
            <div class="form-group">
              <label>Description</label>
              <textarea name="description" class="form-control" rows="3"></textarea>
            </div>
            <div class="form-group">
              <label>Assign To</label>
              <select name="assigned_to" class="form-control" required>
                @foreach($conversation->participants as $p)
                  @if($p->user)
                    <option value="{{ $p->user_id }}" {{ $p->user_id == Auth::id() ? 'selected' : '' }}>{{ $p->user->name }}</option>
                  @endif
                @endforeach
              </select>
            </div>
            <div class="form-group">
              <label>Due Date</label>
              <input type="date" name="due_date" class="form-control">
            </div>
          </div>
          <div class="modal-footer">
            <button type="submit" class="btn btn-cosecsa">Assign Task</button>
          </div>
        </form>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const box = document.getElementById('threadBody');
  const conversationId = box.dataset.conversationId;
  if (box) box.scrollTop = box.scrollHeight;

  function escapeHtml(s) {
    return (s || '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  }

  function renderRow(m) {
    const wrap = document.createElement('div');
    wrap.className = 'chat-row ' + (m.mine ? 'mine' : 'theirs');
    wrap.setAttribute('data-row-id', m.id);

    let html = '';
    if (!m.mine) {
      html += `<span class="chat-avatar">${escapeHtml((m.sender_name || '?').charAt(0).toUpperCase())}</span>`;
    }
    html += '<div class="chat-bubble-col">';
    if (!m.mine) {
      html += `<div class="text-muted" style="font-size:.75rem;">${escapeHtml(m.sender_name)}</div>`;
    }
    if (m.deleted) {
      html += `<div class="p-2 rounded font-italic text-muted msg-deleted-bubble" style="background:#f1f1f1;"><i class="fas fa-ban mr-1"></i> This message was deleted.</div>`;
    } else {
      html += `<div class="p-2 rounded message-bubble ${m.mine ? 'msg-bubble-mine' : 'msg-bubble-theirs'}" data-msg-id="${m.id}">`;
      html += `<span class="msg-body-text">${escapeHtml(m.body)}</span>`;
      (m.attachments || []).forEach(a => {
        if (a.kind === 'image') {
          html += `<div class="mt-2"><a href="${a.url}" target="_blank"><img src="${a.url}" style="max-width:220px;max-height:220px;border-radius:6px;display:block;"></a></div>`;
        } else if (a.kind === 'audio') {
          html += `<div class="mt-2"><audio controls src="${a.url}" style="max-width:220px;"></audio></div>`;
        } else {
          html += `<div class="mt-2"><a href="${a.url}" target="_blank" class="msg-attach-link" style="text-decoration:underline;"><i class="fas fa-paperclip mr-1"></i>${escapeHtml(a.name)}</a></div>`;
        }
      });
      if (m.mine) {
        html += `<div class="msg-actions" style="position:absolute;top:2px;right:6px;display:none;">
          <a href="#" class="text-white msg-edit-btn" title="Edit" style="margin-right:6px;"><i class="fas fa-edit"></i></a>
          <a href="#" class="text-white msg-delete-btn" title="Delete"><i class="fas fa-trash"></i></a>
        </div>`;
      }
      html += `</div>`;
      if (m.mine) {
        html += `<form class="msg-edit-form mt-1" style="display:none;" data-msg-id="${m.id}">
          <div class="input-group input-group-sm">
            <input type="text" name="body" class="form-control" value="${escapeHtml(m.body)}">
            <div class="input-group-append">
              <button type="submit" class="btn btn-cosecsa">Save</button>
              <button type="button" class="btn btn-cosecsa-outline msg-edit-cancel">Cancel</button>
            </div>
          </div>
        </form>`;
      }
    }
    html += `<div class="text-muted text-right" style="font-size:.7rem;">
      <span class="msg-time">${m.created_at}</span>
      <span class="msg-edited-tag font-italic" style="${(m.edited && !m.deleted) ? '' : 'display:none;'}"> (edited)</span>
    </div></div>`;
    wrap.innerHTML = html;
    return wrap;
  }

  function upsertMessage(m, scroll) {
    const placeholder = document.getElementById('noMessagesPlaceholder');
    if (placeholder) placeholder.remove();

    const existing = box.querySelector(`[data-row-id="${m.id}"]`);
    const row = renderRow(m);
    if (existing) {
      existing.replaceWith(row);
    } else {
      box.appendChild(row);
    }
    if (scroll) box.scrollTop = box.scrollHeight;
  }

  // ── Polling for new/edited/deleted messages ─────────────────────────
  function pollThread() {
    fetch(`{{ url('messages') }}/${conversationId}/poll?since=${encodeURIComponent(box.dataset.since)}`)
      .then(r => r.ok ? r.json() : null)
      .then(data => {
        if (!data) return;
        const wasAtBottom = box.scrollTop + box.clientHeight >= box.scrollHeight - 40;
        (data.messages || []).forEach(m => upsertMessage(m, wasAtBottom));
        box.dataset.since = data.server_time;
      })
      .catch(() => {});
  }
  setInterval(pollThread, 1500);

  // ── Event delegation for edit/delete (works for server-rendered and
  //    dynamically appended rows alike) ───────────────────────────────
  box.addEventListener('click', function (e) {
    const editBtn = e.target.closest('.msg-edit-btn');
    if (editBtn) {
      e.preventDefault();
      const bubble = editBtn.closest('.message-bubble');
      bubble.style.display = 'none';
      bubble.nextElementSibling.style.display = 'block';
      return;
    }
    const cancelBtn = e.target.closest('.msg-edit-cancel');
    if (cancelBtn) {
      const form = cancelBtn.closest('.msg-edit-form');
      form.style.display = 'none';
      form.previousElementSibling.style.display = 'block';
      return;
    }
    const delBtn = e.target.closest('.msg-delete-btn');
    if (delBtn) {
      e.preventDefault();
      if (!confirm('Delete this message?')) return;
      const msgId = delBtn.closest('.message-bubble').dataset.msgId;
      fetch(`{{ url('messages') }}/${conversationId}/messages/${msgId}/delete`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
      }).then(r => r.json()).then(data => upsertMessage(data.message, false));
    }
  });

  box.addEventListener('submit', function (e) {
    const form = e.target.closest('.msg-edit-form');
    if (!form) return;
    e.preventDefault();
    const msgId = form.dataset.msgId;
    const body = form.querySelector('input[name="body"]').value;
    fetch(`{{ url('messages') }}/${conversationId}/messages/${msgId}/edit`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' },
      body: JSON.stringify({ body }),
    }).then(r => r.json()).then(data => upsertMessage(data.message, false));
  });

  // ── Send message via AJAX (no page reload) ──────────────────────────
  const sendForm = document.getElementById('sendForm');
  sendForm.addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(sendForm);
    const bodyInput = sendForm.querySelector('input[name="body"]');
    const bodyText = formData.get('body');
    const hasFiles = attachInput.files.length > 0 || !!sendForm.querySelector('input[name="voice_note"]');
    if (!bodyText && !hasFiles) return;

    // Optimistic render for plain-text sends so the message appears
    // instantly instead of waiting on the round trip / next poll.
    let tempId = null;
    if (bodyText && !hasFiles) {
      tempId = 'tmp-' + Date.now();
      upsertMessage({ id: tempId, mine: true, sender_name: 'You', body: bodyText, deleted: false, edited: false, created_at: 'Sending…', attachments: [] }, true);
      bodyInput.value = '';
    }

    fetch(sendForm.action, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
      body: formData,
    })
      .then(r => r.json())
      .then(data => {
        if (tempId) {
          const tempRow = box.querySelector(`[data-row-id="${tempId}"]`);
          if (tempRow) tempRow.remove();
        }
        upsertMessage(data.message, true);
        bodyInput.value = '';
        attachInput.value = '';
        attachPreview.style.display = 'none';
        attachPreview.innerHTML = '';
        const voiceInput = sendForm.querySelector('input[name="voice_note"]');
        if (voiceInput) voiceInput.remove();
      })
      .catch(() => {
        if (tempId) {
          const tempRow = box.querySelector(`[data-row-id="${tempId}"]`);
          if (tempRow) tempRow.remove();
        }
        alert('Could not send message. Please try again.');
      });
  });

  // Attachment preview (filenames chosen)
  const attachInput = document.getElementById('attachInput');
  const attachPreview = document.getElementById('attachPreview');
  attachInput.addEventListener('change', function () {
    if (this.files.length === 0) { attachPreview.style.display = 'none'; return; }
    attachPreview.style.display = 'block';
    attachPreview.innerHTML = Array.from(this.files).map(f => `<span class="badge badge-light border mr-1">${f.name}</span>`).join('');
  });

  // Voice recording via MediaRecorder
  let mediaRecorder = null;
  let audioChunks = [];
  const voiceBtn = document.getElementById('voiceBtn');
  const recordingIndicator = document.getElementById('recordingIndicator');
  const stopBtn = document.getElementById('stopRecordingBtn');

  voiceBtn.addEventListener('click', function () {
    if (!navigator.mediaDevices || !window.MediaRecorder) {
      alert('Voice recording is not supported in this browser.');
      return;
    }
    navigator.mediaDevices.getUserMedia({ audio: true }).then(function (stream) {
      audioChunks = [];
      mediaRecorder = new MediaRecorder(stream);
      mediaRecorder.addEventListener('dataavailable', e => audioChunks.push(e.data));
      mediaRecorder.addEventListener('stop', function () {
        stream.getTracks().forEach(t => t.stop());
        const blob = new Blob(audioChunks, { type: 'audio/webm' });
        const file = new File([blob], 'voice-note.webm', { type: 'audio/webm' });
        const dt = new DataTransfer();
        dt.items.add(file);

        let voiceInput = sendForm.querySelector('input[name="voice_note"]');
        if (!voiceInput) {
          voiceInput = document.createElement('input');
          voiceInput.type = 'file';
          voiceInput.name = 'voice_note';
          voiceInput.style.display = 'none';
          sendForm.appendChild(voiceInput);
        }
        voiceInput.files = dt.files;

        recordingIndicator.style.display = 'none';
        attachPreview.style.display = 'block';
        attachPreview.innerHTML += '<span class="badge badge-danger mr-1"><i class="fas fa-microphone mr-1"></i>Voice note ready</span>';
      });
      mediaRecorder.start();
      recordingIndicator.style.display = 'block';
    }).catch(function () {
      alert('Microphone permission is required to record a voice note.');
    });
  });

  stopBtn.addEventListener('click', function (e) {
    e.preventDefault();
    if (mediaRecorder && mediaRecorder.state !== 'inactive') mediaRecorder.stop();
  });
});
</script>
@endpush
