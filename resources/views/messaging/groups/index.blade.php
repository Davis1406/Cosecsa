@extends('layout.app')

@section('content')
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 style="font-size:1.4rem;">Discussion Groups</h1>
          </div>
          <div class="col-sm-6 text-right">
            <a href="{{ url('messages') }}" class="btn btn-secondary">
              <i class="fas fa-arrow-left mr-1"></i> Back to Messages
            </a>
            <a href="{{ url('messages/groups/create') }}" class="btn btn-primary">
              <i class="fas fa-plus mr-1"></i> New Group
            </a>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        @include('_message')
        <div class="card">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-sm">
                <thead><tr><th>Name</th><th>Members</th><th>Action</th></tr></thead>
                <tbody>
                  @foreach($groups as $g)
                    <tr>
                      <td>{{ $g->name }}</td>
                      <td>{{ $g->participants_count }}</td>
                      <td>
                        <a href="{{ url('messages/'.$g->id) }}" class="btn btn-sm btn-outline-primary">Open</a>
                        <a href="{{ url('messages/groups/'.$g->id.'/edit') }}" class="btn btn-sm btn-primary">Edit</a>
                        <form method="POST" action="{{ url('messages/groups/'.$g->id.'/delete') }}" style="display:inline;"
                              onsubmit="return confirm('Delete this group and all its messages?')">
                          @csrf
                          <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                        </form>
                      </td>
                    </tr>
                  @endforeach
                  @if($groups->isEmpty())
                    <tr><td colspan="3" class="text-center text-muted py-3">No groups yet.</td></tr>
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
