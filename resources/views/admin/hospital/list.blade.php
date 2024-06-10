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
            <a href="{{url('admin/hospital/add')}}" class="btn btn-primary">Add New Hospital</a>
          </div>

        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">

          <!-- general form elements -->
          <div class="card">
            <div class="card-header">
              <h3 class="card-title">Search Hospitals</h3>
            </div>
          
            <form method="get" action="">       
            <div class="card-body">
              <div class="row">
                  <div class="form-group col-md-3">
                      <label>Hospital Name</label>
                      <input type="text" class="form-control" value="{{ Request::get('hospital_name') }}"  name="hospital_name" placeholder="Hospital Name">
                  </div>
                 <div class="form-group col-md-3"">
                   <label>Country</label>
                   <input type="text" class="form-control" value="{{Request::get('country_name')}}"  name="country_name" placeholder="Enter Country">
                 </div>

               <div class="form-group col-md-3">
                <button class="btn btn-primary" type="submit" style="margin-top: 30px">Search</button>
                <a href="{{url('admin/hospital/list')}}" class="btn btn-success" style="margin-top: 30px">Clear</a>
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
                <h3 class="card-title">Accredited Hospitals</h3>
              </div>
              <!-- /.card-header -->
              <div class="card-body p-0"> 
                <table class="table table-striped">
                  <thead>
                    <tr>
                      <th style="width: 10px">#</th>
                      <th> Hospital Name</th>
                      <th>Country</th>
                      <th>Hospital Type</th>
                      <th>Status</th>
                      {{-- <th>Created Date</th> --}}
                      <th style="margin-left:30px">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach ($getRecord as $value)
                  <tr>
                    <td>{{$value->id}}</td>
                    <td>{{$value->name}}</td>
                    <td>{{$value->country_name}}</td>
                    <td>
                      @if($value->hospital_type == 1)
                      Government Hospital
                      @elseif($value->hospital_type == 2)
                      NGO / Faith based Hospital
                      @elseif($value->hospital_type == 3)
                      Private Hospital
                      @elseif($value->hospital_type == 4)
                      University Teaching Hospital	
                      @endif
                    </td>
                    <td>
                    @if($value->status == 0)
                    Active
                    @else
                    Inactive
                    @endif
                    </td>
                    {{-- <td>{{date('d-m-y H:i A',strtotime($value->created_at))}}</td> --}}
                    <td>
                      <a href="{{url('admin/hospital/view_hospital/'.$value->id)}}" class="btn btn-success btn-md">View</a> 
                      <a href="{{url('admin/hospital/edit_hospital/'.$value->id)}}" class="btn btn-primary btn-md">Edit</a> 
                      <a href="{{url('admin/hospital/delete/'.$value->id)}}" class="btn btn-danger btn-md">Delete</a>
                    </td>
                  </tr>
                    @endforeach
                  </tbody>
                </table>
                <div style="padding : 10px; float: right">
             {!!$getRecord->appends(Illuminate\Support\Facades\Request::except('page'))->links() !!}
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
