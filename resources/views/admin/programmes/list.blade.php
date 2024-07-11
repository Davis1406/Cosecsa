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
            <a href="{{url('admin/programmes/add_programmes')}}" class="btn btn-primary">Add New Programme</a>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">     
          <div class="col-md-12">
            @include('_message')
            <!-- Programmes List Card -->
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">Programmes List</h3>
              </div>
              <!-- Card body with responsive table -->
              <div class="card-body p-0">
                <div class="table-responsive">
                  <table class="table table-striped">
                    <thead>
                      <tr>
                        <th>#</th>
                        <th>Programme Name</th>
                        <th>Programme Type</th>
                        <th>Duration</th>
                        <th>Entry Fee</th>
                        <th>Exam Fee</th>
                        <th>Repeat Fee</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($getRecord as $programme)
                        <tr>
                          <td>{{ $programme->id }}</td>
                          <td>{{ $programme->name }}</td>
                          <td>{{ $programme->programme_type }}</td>
                          <td>{{ $programme->duration }} Years</td>
                          <td>{{ $programme->entry_fee }}</td>
                          <td>{{ $programme->exam_fee }}</td>
                          <td>{{ $programme->repeat_fee }}</td>
                          <td>
                            <a href="{{ url('admin/programmes/edit_programmes/'.$programme->id) }}" class="btn btn-primary">Edit</a>
                            <a href="{{ url('admin/programmes/delete/'.$programme->id) }}" class="btn btn-danger">Delete</a>
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
