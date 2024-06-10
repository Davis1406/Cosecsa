@extends('layout.app')  

@section('content')
<div class="wrapper">


  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Add New Admin</h1>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <!-- left column -->
          <div class="col-md-12">
            <!-- general form elements -->
            <div class="card card-primary">
              <div class="card-header" style="background-color: darkred">
                <h3 class="card-title">Fill in details</h3>
              </div>
              <!-- /.card-header -->
              <!-- form start -->
            
              <form method="POST" action="">
                {{ csrf_field() }}
                <div class="card-body">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" class="form-control" value="{{old('name')}}"  name="name" required placeholder="Enter full name">
                      </div>
                  <div class="form-group">
                    <label>Email address</label>
                    <input type="email" class="form-control" value="{{old('email')}}"  name="email"required placeholder="Enter email">
                   <div style="color: red">{{$errors->first('email')}}</div> 
                </div>
                  <div class="form-group">
                    <label for="exampleInputPassword1">Password</label>
                    <input type="password" class="form-control" name="password" required placeholder="Password">
                  </div>
                  <div class="form-group">
                    <label for="exampleInputFile">Profile Image</label>
                    <div class="input-group">
                      <div class="custom-file">
                        <input type="file" class="custom-file-input">
                        <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                      </div>
                      <div class="input-group-append">
                        <span class="input-group-text">Upload</span>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer" style="background-color: white">
                  <button type="submit" class="btn btn-primary" style="background-color: #FEC503;border-color:#FEC503">Submit</button>
                </div>
              </form>
            </div>
            <!-- /.card -->
          </div>
          <!--/.col (left) -->
        </div>
        <!-- /.row -->
      </div>
      <!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>


</div>

<script>
$(function () {
  bsCustomFileInput.init();
});
</script>

@endsection
