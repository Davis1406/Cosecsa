@extends('layout.app')

@section('content')
    <!DOCTYPE html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>AdminLTE 3 | General Form Elements</title>

        <!-- Google Font: Source Sans Pro -->
        <link rel="stylesheet"
            href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
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
                                <h1>Change Password</h1>
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
                                @include('_message')
                                <!-- general form elements -->
                                <div class="card card-primary">
                                    <div class="card-header" style="background-color: darkred">
                                        <h3 class="card-title">Fill in details</h3>
                                    </div>
                                    <!-- /.card-header -->
                                    <!-- form start -->

                                    <form method="POST" action="{{ url('profile/change_password') }}">
                                        @csrf
                                        <div class="card-body">

                                            <div class="form-group">
                                                <label for="password">Old Password:</label>
                                                <input type="password" name="old_password" class="form-control" required placeholder="Old Password">
                                            </div>

                                            <div class="form-group">
                                                <label for="programme_type">New Password:</label>
                                                <input type="password" name="new_password" class="form-control" required placeholder="New Password">
                                            </div>
                            
                                        </div>
                                        <!-- /.card-body -->

                                        <div class="card-footer" style="background-color: white">
                                            <button type="submit" class="btn btn-primary" style="background-color: #FEC503;border-color:#FEC503">Submit</button>
                                        </div>
                                    </form>
                                </div>
                                <!-- /.card -->

                                <!-- Email Signature -->
                                <div class="card card-primary mt-3">
                                    <div class="card-header" style="background-color: darkred">
                                        <h3 class="card-title">Email Signature</h3>
                                    </div>
                                    <form method="POST" action="{{ url('profile/signature') }}">
                                        @csrf
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label>Job Title / Position</label>
                                                        <input type="text" id="sig_title" name="signature_title" class="form-control"
                                                               value="{{ old('signature_title', $user->signature_title) }}"
                                                               placeholder="e.g. Admission Assistant">
                                                    </div>
                                                    <div class="form-group">
                                                        <label>Phone</label>
                                                        <input type="text" id="sig_phone" name="signature_phone" class="form-control"
                                                               value="{{ old('signature_phone', $user->signature_phone) }}"
                                                               placeholder="e.g. +255 27 2549362">
                                                    </div>
                                                    <small class="text-muted">
                                                        Name and email come from your account automatically. This signature is
                                                        appended to emails you send through the MIS (e.g. Examiner Confirmation).
                                                    </small>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Preview</label>
                                                    <div style="font-family:Arial,Helvetica,sans-serif; font-size:13px; color:#a02626; border:1px solid #eee; border-radius:6px; padding:16px;">
                                                        <p style="margin:0;">Best Regards,<br>{{ $user->name }}.</p>
                                                        <p id="sig_preview_title" style="font-weight:bold; margin:4px 0 10px;">{{ $user->signature_title ?: 'Job Title' }}</p>
                                                        <img src="{{ url('public/dist/img/Cosecsa_Logo.png') }}" alt="COSECSA" style="width:60px; height:60px; object-fit:contain;">
                                                        <p style="margin:10px 0 0; font-weight:bold;">
                                                            The College of Surgeons of East, Central and Southern Africa (COSECSA)
                                                        </p>
                                                        <p style="margin:2px 0;">
                                                            ECSA-HC, P.O. Box 1009<br>
                                                            Arusha, Tanzania.
                                                        </p>
                                                        <p style="margin:2px 0;">
                                                            Tel: <span id="sig_preview_phone">{{ $user->signature_phone ?: '—' }}</span>
                                                        </p>
                                                        <p style="margin:6px 0 0;">
                                                            <span style="color:#c99400;">Email:</span>
                                                            <a href="#" style="color:#2a6ebb;">{{ $user->email }}</a>
                                                            &nbsp;<span style="color:#c99400;">W:</span>
                                                            <a href="#" style="color:#2a6ebb;">www.cosecsa.org</a>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-footer" style="background-color: white">
                                            <button type="submit" class="btn btn-primary" style="background-color: #FEC503;border-color:#FEC503">Save Signature</button>
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
            $(function() {
                bsCustomFileInput.init();

                $('#sig_title').on('input', function () {
                    $('#sig_preview_title').text($(this).val() || 'Job Title');
                });
                $('#sig_phone').on('input', function () {
                    $('#sig_preview_phone').text($(this).val() || '—');
                });
            });
        </script>
    </body>

    </html>
@endsection
