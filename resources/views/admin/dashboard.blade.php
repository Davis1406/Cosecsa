@extends('layout.app')

@section('content')

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0">Dashboard</h1>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <!-- Small boxes (Stat box) -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-info">
              <div class="inner">
                <h3>{{ $traineeCount }}</h3>
                <p>Trainees</p>
              </div>
              <div class="icon">
                <i class="ion ion-person-add"></i>
              </div>
              <a href="{{url('admin/associates/trainees/trainees')}}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-success">
              <div class="inner">
                <h3>{{$accreditedHospitalCount}}</h3>
                <p>Accredited Hospital</p>
              </div>
              <div class="icon">
                <i class="ion ion-medkit"></i>
              </div>
              <a href="{{url('admin/hospital/list')}}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
              <div class="inner">
                <h3>{{$CandidateCount}}</h3>
                <p>Candidates</p>
              </div>
              <div class="icon">
                <i class="ion ion-university"></i>
              </div>
              <a href="{{url('admin/associates/candidates/list')}}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
          <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-danger">
              <div class="inner">
                <h3>{{$FellowsCount}}</h3>
                <p>Fellows</p>
              </div>
              <div class="icon">
                <i class="ion ion-pie-graph"></i>
              </div>
              <a href="{{url('admin/associates/fellows/list')}}" class="small-box-footer">More info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <!-- ./col -->
        </div>
        <!-- /.row -->

        <!-- Messages / Tasks row -->
        <div class="row">
          <div class="col-lg-6 col-6">
            <div class="small-box" style="background:#fff;border:1px solid #eee;">
              <div class="inner">
                <h3 style="color:{{ $unreadConversationsCount > 0 ? '#dc3545' : '#333' }};">
                  {{ $unreadConversationsCount }}
                </h3>
                <p>Unread Messages</p>
              </div>
              <div class="icon">
                <i class="far fa-comments" style="color:#a02626;font-size:1.8rem;"></i>
              </div>
              <a href="{{ url('messages') }}" class="small-box-footer" style="color:#a02626 !important;">Open Messages <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
          <div class="col-lg-6 col-6">
            <div class="small-box" style="background:#fff;border:1px solid #eee;">
              <div class="inner">
                <h3 style="color:{{ $pendingTasksCount > 0 ? '#dc3545' : '#333' }};">
                  {{ $pendingTasksCount }}
                </h3>
                <p>Pending Tasks</p>
              </div>
              <div class="icon">
                <i class="fas fa-tasks" style="color:#a02626;font-size:1.8rem;"></i>
              </div>
              <a href="{{ url('messages/tasks') }}" class="small-box-footer" style="color:#a02626 !important;">Open Tasks <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
        </div>
        <!-- /.row -->

        <!-- Main row -->
        <div class="row">
          <!-- Left col -->
          <section class="col-lg-6 connectedSortable">
            <!-- Custom tabs (Charts with tabs)-->
            <div class="card">
              <div class="card-header">
                <h3 class="card-title">
                  <i class="fas fa-chart-pie mr-1"></i>
                  Admission Data <small class="text-muted">(All Alumni: {{ $allAlumniCount }}, Female: {{ $femaleAlumniCount }})</small>
                </h3>
                <div class="card-tools">
                  <ul class="nav nav-pills ml-auto">
                    <li class="nav-item">
                      <a class="nav-link active" href="#revenue-chart" data-toggle="tab">Alumni by Year</a>
                    </li>
                    <li class="nav-item">
                      <a class="nav-link" href="#sales-chart" data-toggle="tab">Gender Split</a>
                    </li>
                  </ul>
                </div>
              </div><!-- /.card-header -->
              <div class="card-body">
                <div class="tab-content p-0">
                  <div class="chart tab-pane active" id="revenue-chart"
                       style="position: relative; height: 300px;">
                      <canvas id="revenue-chart-canvas" height="300" style="height: 300px;"></canvas>
                   </div>
                  <div class="chart tab-pane" id="sales-chart" style="position: relative; height: 300px;">
                    <canvas id="sales-chart-canvas" height="300" style="height: 300px;"></canvas>
                  </div>
                </div>
              </div><!-- /.card-body -->
            </div>
            <!-- /.card -->
            <!-- /.card -->
          </section>
          <!-- /.Left col -->
          <!-- right col (We are only adding the ID to make the widgets sortable)-->
          <section class="col-lg-6 connectedSortable">

            <!-- Calendar -->
            <div class="card bg-gradient-success">
              <div class="card-header border-0">

                <h3 class="card-title">
                  <i class="far fa-calendar-alt"></i>
                  Calendar
                </h3>
                <!-- tools card -->
                <div class="card-tools">
                  <!-- button with a dropdown -->
                  <div class="btn-group">
                    <button type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" data-offset="-52">
                      <i class="fas fa-bars"></i>
                    </button>
                    <div class="dropdown-menu" role="menu">
                      <a href="#" class="dropdown-item">Add new event</a>
                      <a href="#" class="dropdown-item">Clear events</a>
                      <div class="dropdown-divider"></div>
                      <a href="#" class="dropdown-item">View calendar</a>
                    </div>
                  </div>
                  <button type="button" class="btn btn-success btn-sm" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-success btn-sm" data-card-widget="remove">
                    <i class="fas fa-times"></i>
                  </button>
                </div>
              </div>
              <!-- /.card-header -->
              <div class="card-body pt-0">
                <!--The calendar -->
                <div id="calendar" style="width: 100%; height: 320px;"></div>
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </section>
          <!-- right col -->
        </div>
        <!-- /.row (main row) -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  @push('scripts')
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    const yearLabels = {!! json_encode($alumniYearLabels) !!};
    const yearTotals = {!! json_encode($alumniYearTotals) !!};
    const yearFemale = {!! json_encode($alumniYearFemale) !!};

    new Chart(document.getElementById('revenue-chart-canvas').getContext('2d'), {
      type: 'bar',
      data: {
        labels: yearLabels,
        datasets: [
          { label: 'Total Alumni', data: yearTotals, backgroundColor: '#a02626', borderColor: '#a02626', type: 'bar' },
          { label: 'Female Graduates', data: yearFemale, backgroundColor: '#FEC503', borderColor: '#FEC503', type: 'line', fill: false, tension: 0.3 }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
      }
    });

    new Chart(document.getElementById('sales-chart-canvas').getContext('2d'), {
      type: 'doughnut',
      data: {
        labels: ['Male Alumni', 'Female Alumni'],
        datasets: [{
          data: [{{ $allAlumniCount - $femaleAlumniCount }}, {{ $femaleAlumniCount }}],
          backgroundColor: ['#a02626', '#FEC503']
        }]
      },
      options: { responsive: true, maintainAspectRatio: false }
    });
  });
  </script>
  @endpush

  @endsection
