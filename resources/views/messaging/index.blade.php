@extends('layout.app')

@section('content')
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
            <div class="list-group list-group-flush">
              @foreach($conversations as $c)
                @php $last = $c->latestMessage; @endphp
                <a href="{{ url('messages/'.$c->id) }}" class="list-group-item list-group-item-action">
                  <div class="d-flex justify-content-between">
                    <strong>
                      @if($c->type === 'group')<i class="fas fa-users text-muted mr-1"></i>@endif
                      {{ $c->display_name }}
                    </strong>
                    <small class="text-muted">{{ $last ? $last->created_at->diffForHumans() : '' }}</small>
                  </div>
                  <div class="text-muted" style="font-size:.85rem;">
                    {{ $last ? \Illuminate\Support\Str::limit(strip_tags($last->body), 90) : 'No messages yet.' }}
                  </div>
                </a>
              @endforeach
              @if($conversations->isEmpty())
                <div class="text-center text-muted py-4">No conversations yet — search above to start one.</div>
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
});
</script>
@endpush
