@extends('layout.app')

@section('content')

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper" style="background:#f4f6f9;">

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid" style="padding-top:1rem;">

        <!-- Modern Record Count Tiles -->
        <div class="row" style="gap:0;">
          <!-- Trainees Tile -->
          <div class="col-lg-3 col-6">
            <div class="modern-tile" style="border-left:4px solid #06b6d4;">
              <div class="modern-tile-body">
                <div>
                  <p class="modern-tile-label">TRAINEES</p>
                  <h3 class="modern-tile-value">{{ $traineeCount }}</h3>
                </div>
                <div class="modern-tile-icon" style="background:#ecfeff;color:#06b6d4;">
                  <i class="fas fa-user-graduate"></i>
                </div>
              </div>
              <a href="{{url('admin/associates/trainees/trainees')}}" class="modern-tile-link" style="color:#06b6d4;">
                View Details <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>
          </div>
          <!-- Accredited Hospitals Tile -->
          <div class="col-lg-3 col-6">
            <div class="modern-tile" style="border-left:4px solid #22c55e;">
              <div class="modern-tile-body">
                <div>
                  <p class="modern-tile-label">ACCREDITED HOSPITALS</p>
                  <h3 class="modern-tile-value">{{ $accreditedHospitalCount }}</h3>
                </div>
                <div class="modern-tile-icon" style="background:#f0fdf4;color:#22c55e;">
                  <i class="fas fa-hospital"></i>
                </div>
              </div>
              <a href="{{url('admin/hospital/list')}}" class="modern-tile-link" style="color:#22c55e;">
                View Details <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>
          </div>
          <!-- Candidates Tile -->
          <div class="col-lg-3 col-6">
            <div class="modern-tile" style="border-left:4px solid #f59e0b;">
              <div class="modern-tile-body">
                <div>
                  <p class="modern-tile-label">CANDIDATES</p>
                  <h3 class="modern-tile-value">{{ $CandidateCount }}</h3>
                </div>
                <div class="modern-tile-icon" style="background:#fffbeb;color:#f59e0b;">
                  <i class="fas fa-user-check"></i>
                </div>
              </div>
              <a href="{{url('admin/associates/candidates/list')}}" class="modern-tile-link" style="color:#f59e0b;">
                View Details <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>
          </div>
          <!-- Fellows Tile -->
          <div class="col-lg-3 col-6">
            <div class="modern-tile" style="border-left:4px solid #a02626;">
              <div class="modern-tile-body">
                <div>
                  <p class="modern-tile-label">FELLOWS</p>
                  <h3 class="modern-tile-value">{{ $FellowsCount }}</h3>
                </div>
                <div class="modern-tile-icon" style="background:#fdf2f2;color:#a02626;">
                  <i class="fas fa-award"></i>
                </div>
              </div>
              <a href="{{url('admin/associates/fellows/list')}}" class="modern-tile-link" style="color:#a02626;">
                View Details <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>
          </div>
        </div>
        <!-- /.row -->

        <!-- Messages / Tasks row -->
        <div class="row">
          <div class="col-lg-6 col-6">
            <div class="modern-card" style="display:flex;flex-direction:column;">
              <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;">
                <div>
                  <h3 id="dashUnreadMessagesCount" style="font-size:2.25rem;font-weight:700;color:#0f172a;margin:0;">
                    {{ $unreadConversationsCount }}
                  </h3>
                  <p style="font-size:0.875rem;font-weight:500;color:#64748b;margin:0;">Unread Messages</p>
                </div>
                <div style="width:2.5rem;height:2.5rem;display:flex;align-items:center;justify-content:center;color:#fda4af;font-size:1.8rem;">
                  <i class="far fa-comments"></i>
                </div>
              </div>
              <a href="{{ url('messages') }}" class="modern-card-btn" style="background:#fff1f2;color:#e11d48;">
                Open Messages <i class="fas fa-external-link-alt" style="font-size:0.75rem;"></i>
              </a>
            </div>
          </div>
          <div class="col-lg-6 col-6">
            <div class="modern-card" style="display:flex;flex-direction:column;">
              <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;">
                <div>
                  <h3 id="dashPendingTasksCount" style="font-size:2.25rem;font-weight:700;color:#0f172a;margin:0;">
                    {{ $pendingTasksCount }}
                  </h3>
                  <p style="font-size:0.875rem;font-weight:500;color:#64748b;margin:0;">Pending Tasks</p>
                </div>
                <div style="width:2.5rem;height:2.5rem;display:flex;align-items:center;justify-content:center;color:#94a3b8;font-size:1.8rem;">
                  <i class="fas fa-tasks"></i>
                </div>
              </div>
              <a href="{{ url('messages/tasks') }}" class="modern-card-btn" style="background:#f1f5f9;color:#475569;">
                Open Tasks <i class="fas fa-external-link-alt" style="font-size:0.75rem;"></i>
              </a>
            </div>
          </div>
        </div>
        <!-- /.row -->

        <!-- Main row -->
        <div class="row">
          <!-- Left col -->
          <section class="col-lg-6 connectedSortable">
            <!-- Admission Data Chart -->
            <div class="modern-card">
              <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:2rem;">
                <div style="display:flex;align-items:center;gap:0.5rem;">
                  <i class="fas fa-chart-pie" style="color:#a02626;"></i>
                  <h3 style="font-weight:700;color:#1e293b;margin:0;font-size:1rem;">Admission Data</h3>
                  <span style="font-size:0.75rem;color:#94a3b8;font-weight:400;margin-left:0.5rem;">(All Alumni: {{ $allAlumniCount }}, Female: {{ $femaleAlumniCount }})</span>
                </div>
                <div class="chart-tab-pills">
                  <a class="chart-tab-pill active" href="#revenue-chart" data-toggle="tab" id="tab-alumni-year">Alumni by Year</a>
                  <a class="chart-tab-pill" href="#sales-chart" data-toggle="tab" id="tab-gender-split">Gender Split</a>
                </div>
              </div>
              <div class="tab-content p-0">
                <div class="chart tab-pane active" id="revenue-chart"
                     style="position: relative; height: 300px;">
                    <canvas id="revenue-chart-canvas" height="300" style="height: 300px;"></canvas>
                 </div>
                <div class="chart tab-pane" id="sales-chart" style="position: relative; height: 300px;">
                  <canvas id="sales-chart-canvas" height="300" style="height: 300px;"></canvas>
                </div>
              </div>
            </div>
            <!-- /.card -->
          </section>
          <!-- /.Left col -->
          <!-- right col -->
          <section class="col-lg-6 connectedSortable">

            <!-- Calendar -->
            <style>
              .cosecsa-calendar-card .bootstrap-datetimepicker-widget .table td,
              .cosecsa-calendar-card .bootstrap-datetimepicker-widget .table th {
                border: none;
              }
              .cosecsa-calendar-card .bootstrap-datetimepicker-widget table thead tr:first-child th:hover,
              .cosecsa-calendar-card .bootstrap-datetimepicker-widget table td.day:hover,
              .cosecsa-calendar-card .bootstrap-datetimepicker-widget table td.hour:hover,
              .cosecsa-calendar-card .bootstrap-datetimepicker-widget table td.minute:hover,
              .cosecsa-calendar-card .bootstrap-datetimepicker-widget table td.second:hover {
                background-color: rgba(255,255,255,.15);
                color: #fff;
              }
              .cosecsa-calendar-card .bootstrap-datetimepicker-widget table td.today {
                background-color: #FEC503;
                color: #3a2a00;
              }
              .cosecsa-calendar-card .bootstrap-datetimepicker-widget table td.today:before {
                display: none;
              }
              .cosecsa-calendar-card .bootstrap-datetimepicker-widget table td.active,
              .cosecsa-calendar-card .bootstrap-datetimepicker-widget table td.active:hover {
                background-color: #FEC503;
                color: #3a2a00;
                text-shadow: none;
              }
              .cosecsa-calendar-card .bootstrap-datetimepicker-widget table td.active.today:before {
                border-bottom-color: #FEC503;
              }
            </style>
            <div class="card cosecsa-calendar-card" style="background:linear-gradient(155deg,#a02626,#6e1a1a);color:#fff;border-radius:0.75rem;overflow:hidden;">
              <div class="card-header border-0">

                <h3 class="card-title">
                  <i class="far fa-calendar-alt"></i>
                  Calendar
                </h3>
                <!-- tools card -->
                <div class="card-tools">
                  <!-- button with a dropdown -->
                  <div class="btn-group">
                    <button type="button" class="btn btn-sm dropdown-toggle" style="background:rgba(255,255,255,.15);color:#fff;border:none;" data-toggle="dropdown" data-offset="-52">
                      <i class="fas fa-bars"></i>
                    </button>
                    <div class="dropdown-menu" role="menu">
                      <a href="#" class="dropdown-item">Add new event</a>
                      <a href="#" class="dropdown-item">Clear events</a>
                      <div class="dropdown-divider"></div>
                      <a href="#" class="dropdown-item">View calendar</a>
                    </div>
                  </div>
                  <button type="button" class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:none;" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                  </button>
                  <button type="button" class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:none;" data-card-widget="remove">
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

  <!-- Modern Tile & Card Styles -->
  <style>
    /* Modern Record Tiles */
    .modern-tile {
      background: #fff;
      border-radius: 0.75rem;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 1px 3px rgba(0,0,0,.06);
      border: 1px solid #e2e8f0;
      transition: all .3s ease;
      position: relative;
      overflow: hidden;
    }
    .modern-tile:hover {
      box-shadow: 0 4px 12px rgba(0,0,0,.08);
      transform: translateY(-2px);
    }
    .modern-tile-body {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 1rem;
    }
    .modern-tile-label {
      font-size: 0.75rem;
      font-weight: 700;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      margin: 0 0 0.25rem 0;
    }
    .modern-tile-value {
      font-size: 1.9rem;
      font-weight: 800;
      color: #0f172a;
      margin: 0;
      line-height: 1.2;
    }
    .modern-tile-icon {
      width: 2.75rem;
      height: 2.75rem;
      border-radius: 0.5rem;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.25rem;
      flex-shrink: 0;
      transition: transform .3s ease;
    }
    .modern-tile:hover .modern-tile-icon {
      transform: scale(1.1);
    }
    .modern-tile-link {
      display: flex;
      align-items: center;
      gap: 0.375rem;
      font-size: 0.81rem;
      font-weight: 600;
      text-decoration: none;
      transition: color .2s;
    }
    .modern-tile-link:hover {
      text-decoration: underline;
    }

    /* Modern Cards (Messages/Tasks & Chart) */
    .modern-card {
      background: #fff;
      border-radius: 0.75rem;
      padding: 1.5rem;
      margin-bottom: 1.5rem;
      box-shadow: 0 1px 3px rgba(0,0,0,.06);
      border: 1px solid #e2e8f0;
    }
    .modern-card-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      width: 100%;
      padding: 0.5rem 1rem;
      border-radius: 0.5rem;
      font-size: 0.875rem;
      font-weight: 600;
      text-decoration: none;
      border: none;
      cursor: pointer;
      transition: all .2s;
      margin-top: auto;
    }
    .modern-card-btn:hover {
      opacity: 0.85;
      text-decoration: none;
    }

    /* Chart Tab Pills */
    .chart-tab-pills {
      display: inline-flex;
      background: #f1f5f9;
      border-radius: 0.5rem;
      padding: 0.25rem;
    }
    .chart-tab-pill {
      padding: 0.375rem 1rem;
      border-radius: 0.375rem;
      font-size: 0.75rem;
      font-weight: 600;
      text-decoration: none;
      color: #64748b;
      transition: all .2s;
      cursor: pointer;
      border: none;
      background: transparent;
    }
    .chart-tab-pill:hover {
      color: #1e293b;
      text-decoration: none;
    }
    .chart-tab-pill.active {
      background: #fff;
      color: #1e293b;
      box-shadow: 0 1px 3px rgba(0,0,0,.08);
    }
  </style>

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

    // Chart tab pill switching with burgundy active state
    var tabPills = document.querySelectorAll('.chart-tab-pill');
    tabPills.forEach(function(pill) {
      pill.addEventListener('click', function(e) {
        e.preventDefault();
        tabPills.forEach(function(p) { p.classList.remove('active'); });
        this.classList.add('active');
        // Apply burgundy active color
        if (this.id === 'tab-gender-split') {
          this.style.background = '#a02626';
          this.style.color = '#fff';
          document.getElementById('tab-alumni-year').style.background = 'transparent';
          document.getElementById('tab-alumni-year').style.color = '#64748b';
        } else {
          this.style.background = '#fff';
          this.style.color = '#1e293b';
          document.getElementById('tab-gender-split').style.background = 'transparent';
          document.getElementById('tab-gender-split').style.color = '#64748b';
        }
        // Bootstrap tab activation
        var target = this.getAttribute('href');
        document.querySelectorAll('.tab-pane').forEach(function(pane) { pane.classList.remove('active'); });
        document.querySelector(target).classList.add('active');
      });
    });
  });
  </script>
  @endpush

  @endsection
