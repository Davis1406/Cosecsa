@extends('layout.app')  

@section('content')
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>AdminLTE 3 | General Form Elements</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="../../plugins/fontawesome-free/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="../../dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">


  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Edit Information</h1>
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
            
              <form method="POST" action="{{ url('admin/edit/' . $getRecord->id) }}"
                    enctype="multipart/form-data">
                {{ csrf_field() }}
                <div class="card-body">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" class="form-control" name="name" value="{{ old('name', $getRecord->name) }}" required placeholder="Enter full name">
                    </div>
                    <div class="form-group">
                        <label>Email address</label>
                        <input type="email" class="form-control" name="email" value="{{ old('email', $getRecord->email) }}" required placeholder="Enter email">
                        <div style="color: red">{{ $errors->first('email') }}</div>
                    </div>
                    <div class="form-group">
                        <label>Password <small class="text-muted">(leave blank to keep current)</small></label>
                        <input type="password" class="form-control" name="password" placeholder="New password">
                    </div>
                    <div class="form-group">
                        <label>Profile Photo</label>
                        @if(!empty($getRecord->profile_image))
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $getRecord->profile_image) }}"
                                     alt="Current Photo"
                                     style="width:80px;height:80px;object-fit:cover;border-radius:50%;border:2px solid #ddd;">
                                <small class="d-block text-muted mt-1">Current photo</small>
                            </div>
                        @endif
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="profileImageInput"
                                   name="profile_image" accept="image/*">
                            <label class="custom-file-label" for="profileImageInput">Choose new photo</label>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->

                <div class="card-footer" style="background-color: white">
                    <button type="submit" class="btn btn-primary" style="background-color: #FEC503;border-color:#FEC503">Save Changes</button>
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
    if (typeof bsCustomFileInput !== 'undefined') { bsCustomFileInput.init(); }
    $('#profileImageInput').on('change', function () {
        var name = $(this).val().split('\\').pop();
        $(this).next('.custom-file-label').text(name || 'Choose new photo');
    });
});
</script>
</body>
</html>

  @endsection
