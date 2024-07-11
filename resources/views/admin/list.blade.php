@extends('layout.app')  

@section('content')
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
          </div>
          <div class="col-sm-6 text-right">
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
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th style="width: 10px">#</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Created Date</th>
                        <th>Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($getRecord as $value)
                        <tr>
                          <td>{{ $value->id }}</td>
                          <td>{{ $value->name }}</td>
                          <td>{{ $value->email }}</td>
                          <td>{{ date('d-m-y H:i A', strtotime($value->created_at)) }}</td>
                          <td>
                            <a href="{{ url('admin/edit/'.$value->id) }}" class="btn btn-primary btn-md">Edit</a> 
                            <a href="{{ url('admin/delete/'.$value->id) }}" class="btn btn-danger btn-md">Delete</a>
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
