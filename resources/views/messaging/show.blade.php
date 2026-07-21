@extends('layout.app')

@section('content')
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-8">
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
          <div class="col-sm-4 text-right">
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
                  <div class="p-2 rounded" style="background:{{ $mine ? '#a02626' : '#f1f1f1' }}; color:{{ $mine ? '#fff' : '#222' }};">
                    {{ $m->body }}
                  </div>
                  <div class="text-muted text-right" style="font-size:.7rem;">{{ $m->created_at->format('d M, H:i') }}</div>
                </div>
              </div>
            @empty
              <div class="text-center text-muted py-4">No messages yet — say hello.</div>
            @endforelse
          </div>
          <div class="card-footer">
            <form method="POST" action="{{ url('messages/'.$conversation->id.'/send') }}">
              @csrf
              <div class="input-group">
                <input type="text" name="body" class="form-control" placeholder="Type a message…" required autocomplete="off">
                <div class="input-group-append">
                  <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i></button>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>
  </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const box = document.getElementById('threadBody');
  if (box) box.scrollTop = box.scrollHeight;
});
</script>
@endpush
