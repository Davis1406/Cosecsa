@extends('layout.app')

@section('content')

    <div class="hold-transition sidebar-mini">
        <div class="wrapper">
            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    <div class="container-fluid">
                        <div class="row mb-2">
                            <div class="col-sm-6">
                                <h1>Import Candidates</h1>
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
                                        <h3 class="card-title">Upload Candidates Data (CSV/XLS/XLSX):
                                        </h3>
                                    </div>
                                    <!-- /.card-header -->
                                    <!-- form start -->
                                    <div class="col-md-6 offset-md-3">

                                        <form method="POST" action="{{ route('trainees.import.data') }}" enctype="multipart/form-data">
                                            {{ csrf_field() }}
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="customFile" name="file" required>
                                                        <label class="custom-file-label" for="customFile">No file selected</label>
                                                    </div>
                                                    <span class="text-muted">Accepted Files: .csv, .xls, .xlsx. Max file size 2Mb</span>
                                                </div>
                                            </div>
                                            <!-- /.card-body -->
                                            <div class="card-footer">
                                                <button type="submit" class="btn btn-primary"
                                                    style="background-color: #FEC503;border-color:#FEC503">Upload <span class="fas fa-upload"></span></button>
                                            </div>
                                        </form>
                                    </div>

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

    </div>

        <!-- jQuery -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <!-- bs-custom-file-input -->
        <script src="https://cdn.jsdelivr.net/npm/bs-custom-file-input/dist/bs-custom-file-input.min.js"></script>
        <!-- Initialize the custom file input -->
        <script>
            $(document).ready(function () {
                bsCustomFileInput.init();
            });
        </script>

            <!-- Custom CSS for the file input -->
            <style>
                .custom-file-input ~ .custom-file-label::after {
                    content: "Choose file" !important;
                    background-color: #03a9f4;
                    border: none;
                    padding: 0.375rem 0.75rem;
                    color: white;
                }   
                .text-muted{
                    display: block;
                    margin-top: 0.5rem;
                    color: #999 !important; 
                }
    
                .card-footer{
                    background-color: white;
                    text-align: right;
                    padding-top: 0px;
                    padding-bottom: 10px;
                    padding-right: 15px;
    
                }
            </style>
@endsection
