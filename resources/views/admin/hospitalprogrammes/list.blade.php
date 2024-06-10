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

          <div class="col-sm-6" style="text-align: right">
            <a href="{{ url('admin/hospitalprogrammes/add') }}" class="btn btn-primary">Assign Programme </a>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">

      <!-- general form elements -->
      <div class="card">
        <div class="card-header">
          <h3 class="card-title">Search Hospitals Programmes</h3>
        </div>
                
        <form method="get" action="">       
          <div class="card-body">
            <div class="row">
              <div class="form-group col-md-3">
                <label>Hospital Name</label>
                <input type="text" class="form-control" value="{{ Request::get('hospital_name') }}" name="hospital_name" placeholder="Hospital Name">
              </div>
              <div class="form-group col-md-3">
                <label>Programme Name</label>
                <input type="text" class="form-control" value="{{ Request::get('programme_name') }}" name="programme_name" placeholder="Programme Name">
              </div>
              <div class="form-group col-md-3">
                <button class="btn btn-primary" type="submit" style="margin-top: 30px">Search</button>
                <a href="{{ url('admin/hospitalprogrammes/list') }}" class="btn btn-success" style="margin-top: 30px">Clear</a>
              </div> 
            </div>
          </div>
          <!-- /.card-body -->
        </form>
      </div>

      <div class="container-fluid">
        <div class="row">     
          <div class="col-md-12">
            @include('_message')
            <!-- /.card -->
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Hospital Programme List</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body p-0"> 
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th>#</th>
                      <th>Hospital Name</th>
                      <th>Programme Name</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($getHospitalProgrammes as $data)
                    <tr>
                      <td>{{ $data->id }}</td>
                      <td>{{ $data->hospital_name }}</td>
                      <td>{{ $data->programme_name }}</td>
                      <td>
                        @if($data->status == 0)
                          Active
                        @else
                          Inactive
                        @endif
                      </td>
                      <td>
                        <a href="{{ url('admin/hospitalprogrammes/edit/'.$data->id) }}" class="btn btn-primary">Edit</a>
                        <a href="{{ url('admin/hospitalprogrammes/delete/'.$data->id) }}" class="btn btn-danger">Delete</a>
                      </td>
                    </tr>
                    @endforeach
                  </tbody>
                </table>
                <div style="padding: 10px; float: right">
                  {!! $getHospitalProgrammes->appends(Request::except('page'))->links() !!}
                </div>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

@endsection
