@extends('layout.app')

@section('content')
  <style>
    .admin-list-table .action-btn { padding:2px 8px; line-height:1.4; border-radius:4px; }
    .admin-list-table .action-btn:hover { background-color:#f0f0f0; }
    .admin-list-table .dropdown-menu { min-width:170px; font-size:.875rem; }
    .admin-list-table .dropdown-item { padding:6px 14px; }
    .admin-list-table .dropdown-item:hover { background-color:#f8f0f0; }
    body.dark-mode .admin-list-table .action-btn:hover { background-color:#4a5568 !important; }
    body.dark-mode .admin-list-table .dropdown-item:hover { background-color:#4a5568 !important; color:#fff !important; }
  </style>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
          </div>
          <div class="col-sm-6 text-right">
            <a href="{{url('admin/roles/list')}}" class="btn btn-outline-secondary">Roles & Permissions</a>
            <a href="{{url('admin/add')}}" class="btn btn-primary">Add New Admin</a>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <!-- General form elements -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Search Admin</h3>
        </div>
        <form method="get" action="">
          <div class="card-body">
            <div class="row">
              <div class="form-group col-md-3">
                <label>Name</label>
                <input type="text" class="form-control" value="{{ Request::get('name') }}" name="name" placeholder="Enter Name">
              </div>
              <div class="form-group col-md-3">
                <label>Email address</label>
                <input type="email" class="form-control" value="{{ Request::get('email') }}" name="email" placeholder="Enter email">
              </div>
              <div class="form-group col-md-3">
                <label>Date</label>
                <input type="date" class="form-control" value="{{ Request::get('date') }}" name="date">
              </div>
              <div class="form-group col-md-3 d-flex align-items-end">
                <button class="btn btn-primary mr-2" type="submit">Search</button>
                <a href="{{ url('admin/list') }}" class="btn btn-success">Clear</a>
              </div>
            </div>
          </div>
        </form>
      </div>
  
      <div class="container-fluid">
        <div class="row">     
          <div class="col-md-12">
            @include('_message')
            <!-- Admins List Card -->
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Admins List (Total : {{ $getRecord->total() }})</h3>
              </div>
              <!-- Card body with responsive table -->
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-striped admin-list-table">
                    <thead>
                      <tr>
                        <th style="width: 10px">#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Created Date</th>
                        <th class="text-center">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($getRecord as $value)
                        <tr>
                          <td>{{ $loop->iteration }}</td>
                          <td>{{ $value->name }}</td>
                          <td>{{ $value->email }}</td>
                          <td>{{ $value->adminRole->name ?? 'Super Admin' }}</td>
                          <td>{{ date('d-m-y H:i A', strtotime($value->created_at)) }}</td>
                          <td class="text-center" style="white-space:nowrap;">
                            <div class="dropdown">
                              <button class="btn btn-sm btn-light border dropdown-toggle action-btn"
                                      type="button" data-toggle="dropdown"
                                      aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                              </button>
                              <div class="dropdown-menu dropdown-menu-right shadow-sm">
                                <a class="dropdown-item" href="{{ url('admin/edit/'.$value->id) }}">
                                  <i class="fas fa-edit text-warning mr-2"></i> Edit
                                </a>
                                @if($value->id != Auth::id() && Auth::user()->hasPermission('admin_users.manage'))
                                  <a class="dropdown-item" href="{{ url('admin/impersonate/'.$value->id) }}"
                                     onclick="return confirm('Log in as this user? You can return to your admin account anytime from the banner at the top of the page.')">
                                    <i class="fas fa-user-secret text-info mr-2"></i> Login as User
                                  </a>
                                @endif
                                @if(Auth::user()->isSuperAdmin())
                                  <div class="dropdown-divider"></div>
                                  <a class="dropdown-item text-danger" href="{{ url('admin/delete/'.$value->id) }}"
                                     onclick="return confirm('Delete this admin account?')">
                                    <i class="fas fa-trash mr-2"></i> Delete
                                  </a>
                                @endif
                              </div>
                            </div>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
                <div class="d-flex justify-content-end p-2">
                  {!! $getRecord->appends(Illuminate\Support\Facades\Request::except('page'))->links() !!}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
@endsection
