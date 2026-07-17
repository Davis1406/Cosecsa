@extends('layout.app')

@section('content')
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 style="font-size:1.4rem;">Roles &amp; Permissions</h1>
          </div>
          <div class="col-sm-6 text-right">
            <a href="{{ url('admin/roles/add') }}" class="btn btn-primary">Add New Role</a>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-12">
            @include('_message')
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Roles (Total: {{ $getRecord->count() }})</h3>
              </div>
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th style="width: 10px">#</th>
                        <th>Role Name</th>
                        <th>Can Manage</th>
                        <th>Can View Only</th>
                        <th>Admins Assigned</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($getRecord as $value)
                        <tr>
                          <td>{{ $loop->iteration }}</td>
                          <td>
                            {{ $value->name }}
                            @if($value->is_system)
                              <span class="badge badge-secondary">System</span>
                            @endif
                            @if($value->description)
                              <br><small class="text-muted">{{ $value->description }}</small>
                            @endif
                          </td>
                          <td style="font-size:.82rem;">{{ $value->manage_summary }}</td>
                          <td style="font-size:.82rem;" class="text-muted">{{ $value->view_summary }}</td>
                          <td>{{ $value->users_count }}</td>
                          <td>
                            <a href="{{ url('admin/roles/edit/'.$value->id) }}" class="btn btn-primary btn-sm">
                              {{ $value->is_system ? 'View' : 'Edit' }}
                            </a>
                            @if(!$value->is_system)
                              <a href="{{ url('admin/roles/delete/'.$value->id) }}" class="btn btn-danger btn-sm"
                                 onclick="return confirm('Delete this role?')">Delete</a>
                            @endif
                          </td>
                        </tr>
                      @endforeach
                      @if($getRecord->isEmpty())
                        <tr><td colspan="6" class="text-center text-muted py-3">No roles yet.</td></tr>
                      @endif
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
@endsection
