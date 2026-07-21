@extends('layout.app')

@section('content')
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-7">
            <h1 style="font-size:1.4rem;">
              @if($conversation->type === 'group')<i class="fas fa-users mr-1"></i>@endif
              {{ $title }}
            </h1>
            @if($conversation->type === 'group')
              <p class="text-muted mb-0" style="font-size:.8rem;">
                {{ $conversation->participants->pluck('user.name')->filter()->implode(', ') }}
              </p>
            @endif
          </div>
          <div class="col-sm-5 text-right">
            <button type="button" class="btn btn-outline-success" data-toggle="modal" data-target="#assignTaskModal">
              <i class="fas fa-tasks mr-1"></i> Assign Task
            </button>
            @if($conversation->type === 'group' && Auth::user()->user_type == 1)
              <a href="{{ url('messages/groups/'.$conversation->id.'/edit') }}" class="btn btn-outline-primary">
                <i class="fas fa-user-cog mr-1"></i> Manage Members
              </a>
            @endif
            <a href="{{ url('messages') }}" class="btn btn-secondary">
              <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        @include('_message')

        <div class="card">
          <div class="card-body" style="max-height:520px; overflow-y:auto;" id="threadBody">
            @forelse($conversation->messages as $m)
              @php $mine = $m->sender_id == Auth::id(); @endphp
              <div class="mb-3 d-flex {{ $mine ? 'justify-content-end' : 'justify-content-start' }}">
                <div style="max-width:70%;">
                  @if(!$mine)
                    <div class="text-muted" style="font-size:.75rem;">{{ $m->sender->name ?? 'Unknown' }}</div>
                  @endif

                  @if($m->deleted_at)
                    <div class="p-2 rounded font-italic text-muted" style="background:#f1f1f1;">
                      <i class="fas fa-ban mr-1"></i> This message was deleted.
                    </div>
                  @else
                    <div class="p-2 rounded message-bubble" data-msg-id="{{ $m->id }}"
                         style="background:{{ $mine ? '#a02626' : '#f1f1f1' }}; color:{{ $mine ? '#fff' : '#222' }}; position:relative;">
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
                            <a href="{{ asset('storage/'.$a->path) }}" target="_blank" style="color:{{ $mine ? '#fff' : '#a02626' }};text-decoration:underline;">
                              <i class="fas fa-paperclip mr-1"></i>{{ $a->original_name }}
                            </a>
                          @endif
                        </div>
                      @endforeach

                      @if($mine)
                        <div class="msg-actions" style="position:absolute; top:2px; right:6px; display:none;">
                          <a href="#" class="text-white msg-edit-btn" title="Edit" style="margin-right:6px;"><i class="fas fa-edit"></i></a>
                          <form method="POST" action="{{ url('messages/'.$conversation->id.'/messages/'.$m->id.'/delete') }}" style="display:inline;" onsubmit="return confirm('Delete this message?')">
                            @csrf
                            <button type="submit" class="btn btn-link p-0 text-white" title="Delete"><i class="fas fa-trash"></i></button>
                          </form>
                        </div>
                      @endif
                    </div>

                    @if($mine)
                      <form method="POST" action="{{ url('messages/'.$conversation->id.'/messages/'.$m->id.'/edit') }}" class="msg-edit-form mt-1" style="display:none;">
                        @csrf
                        <div class="input-group input-group-sm">
                          <input type="text" name="body" class="form-control" value="{{ $m->body }}">
                          <div class="input-group-append">
                            <button type="submit" class="btn btn-primary">Save</button>
                            <button type="button" class="btn btn-secondary msg-edit-cancel">Cancel</button>
                          </div>
                        </div>
                      </form>
                    @endif
                  @endif

                  <div class="text-muted text-right" style="font-size:.7rem;">
                    {{ $m->created_at->format('d M, H:i') }}
                    @if($m->edited_at && !$m->deleted_at) <span class="font-italic">(edited)</span> @endif
                  </div>
                </div>
              </div>
            @empty
              <div class="text-center text-muted py-4">No messages yet — say hello.</div>
            @endforelse
          </div>
          <div class="card-footer">
            <form method="POST" action="{{ url('messages/'.$conversation->id.'/send') }}" enctype="multipart/form-data" id="sendForm">
              @csrf
              <div id="attachPreview" class="mb-2" style="display:none;"></div>
              <div class="input-group">
                <div class="input-group-prepend">
                  <label class="btn btn-outline-secondary mb-0" title="Attach file" style="cursor:pointer;">
                    <i class="fas fa-paperclip"></i>
                    <input type="file" name="attachments[]" id="attachInput" multiple style="display:none;">
                  </label>
                  <button type="button" class="btn btn-outline-secondary" id="voiceBtn" title="Record voice note">
                    <i class="fas fa-microphone"></i>
                  </button>
                </div>
                <input type="text" name="body" class="form-control" placeholder="Type a message…" autocomplete="off">
                <div class="input-group-append">
                  <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i></button>
                </div>
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
            <button type="submit" class="btn btn-primary">Assign Task</button>
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
  if (box) box.scrollTop = box.scrollHeight;

  // Show edit/delete actions on hover
  document.querySelectorAll('.message-bubble').forEach(function (bubble) {
    bubble.addEventListener('mouseenter', function () {
      const a = this.querySelector('.msg-actions');
      if (a) a.style.display = 'block';
    });
    bubble.addEventListener('mouseleave', function () {
      const a = this.querySelector('.msg-actions');
      if (a) a.style.display = 'none';
    });
  });

  // Inline edit toggle
  document.querySelectorAll('.msg-edit-btn').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const bubble = this.closest('.message-bubble');
      bubble.style.display = 'none';
      bubble.nextElementSibling.style.display = 'block'; // the edit form
    });
  });
  document.querySelectorAll('.msg-edit-cancel').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const form = this.closest('.msg-edit-form');
      form.style.display = 'none';
      form.previousElementSibling.style.display = 'block';
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
  const sendForm = document.getElementById('sendForm');

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
