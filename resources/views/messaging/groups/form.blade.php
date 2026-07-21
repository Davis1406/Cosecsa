@extends('layout.app')

@section('content')
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-12">
            <h1 style="font-size:1.4rem;">{{ $header_title }}</h1>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        @include('_message')
        <div class="card">
          <div class="card-body">
            <form method="post" action="{{ $group ? url('messages/groups/'.$group->id.'/edit') : url('messages/groups/create') }}">
              @csrf
              <div class="form-group">
                <label>Group Name</label>
                <input type="text" name="name" class="form-control" required value="{{ old('name', $group->name ?? '') }}">
              </div>

              <div class="form-group">
                <label>Members</label>
                <input type="text" id="memberSearch" class="form-control" placeholder="Search by name or email to add…" autocomplete="off">
                <div id="memberSearchResults" class="list-group mt-1" style="display:none; max-height:200px; overflow-y:auto;"></div>
              </div>

              <div class="form-group">
                <label style="font-size:.8rem;" class="text-muted">Selected Members</label>
                <div id="selectedMembers" class="border rounded p-2" style="min-height:50px;">
                  @foreach($members as $m)
                    @php $u = $m->user ?? $m; @endphp
                    <span class="badge badge-secondary p-2 mr-1 mb-1" data-id="{{ $u->id }}">
                      {{ $u->name }} <a href="#" class="text-white remove-member ml-1">&times;</a>
                      <input type="hidden" name="member_ids[]" value="{{ $u->id }}">
                    </span>
                  @endforeach
                </div>
              </div>

              <button type="submit" class="btn btn-cosecsa">{{ $group ? 'Save Changes' : 'Create Group' }}</button>
              <a href="{{ url('messages/groups') }}" class="btn btn-cosecsa-outline">Cancel</a>
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
  const input = document.getElementById('memberSearch');
  const resultsBox = document.getElementById('memberSearchResults');
  const selectedBox = document.getElementById('selectedMembers');
  let timer = null;

  function alreadySelected(id) {
    return !!selectedBox.querySelector(`[data-id="${id}"]`);
  }

  function addMember(id, name) {
    if (alreadySelected(id)) return;
    const span = document.createElement('span');
    span.className = 'badge badge-secondary p-2 mr-1 mb-1';
    span.setAttribute('data-id', id);
    span.innerHTML = `${name} <a href="#" class="text-white remove-member ml-1">&times;</a>
      <input type="hidden" name="member_ids[]" value="${id}">`;
    selectedBox.appendChild(span);
  }

  selectedBox.addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-member')) {
      e.preventDefault();
      e.target.closest('span').remove();
    }
  });

  input.addEventListener('input', function () {
    clearTimeout(timer);
    const q = this.value.trim();
    if (!q) { resultsBox.style.display = 'none'; return; }

    timer = setTimeout(function () {
      fetch("{{ url('messages/search-users') }}?q=" + encodeURIComponent(q))
        .then(r => r.json())
        .then(rows => {
          resultsBox.innerHTML = '';
          resultsBox.style.display = 'block';
          if (rows.length === 0) {
            resultsBox.innerHTML = '<div class="list-group-item text-muted">No matches.</div>';
            return;
          }
          rows.forEach(u => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'list-group-item list-group-item-action';
            btn.textContent = u.name + ' (' + (u.email ?? '') + ')';
            btn.addEventListener('click', function () {
              addMember(u.id, u.name);
              input.value = '';
              resultsBox.style.display = 'none';
            });
            resultsBox.appendChild(btn);
          });
        });
    }, 300);
  });
});
</script>
@endpush
