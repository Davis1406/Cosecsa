@extends('layout.app')

@section('content')
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <!-- Page title can go here if needed -->
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        @include('_message')
        <!-- Trainee Promotion Box -->
        <div class="card card-default">
          <div class="card-header">
            <h3 class="card-title">Trainees Promotion</h3>
          </div>
          <!-- /.card-header -->
          <div class="card-body">
              <form method="POST" action="{{ url('admin/associates/promotion/promote_trainees') }}">
                  @csrf
                  <div class="row">
                      <div class="col-md-10">
                          <fieldset>
                              <div class="row">
                                  <div class="col-md-3">
                                      <div class="form-group">
                                          <label for="from-programme">From Programme:</label>
                                          <select id="from-programme" name="from_programme_id" class="form-control">
                                              <option value="">Select Programme</option>
                                              @foreach($getRecord->unique('programme_id') as $programme)
                                              <option value="{{ $programme->programme_id }}">{{ $programme->category }}</option>
                                              @endforeach
                                          </select>
                                      </div>
                                  </div>
                                  <div class="col-md-3">
                                      <div class="form-group">
                                          <label for="from-unit">From Programme Year:</label>
                                          <select id="from-unit" name="from_unit" class="form-control" disabled>
                                              <option value="">Select Programme First</option>
                                          </select>
                                      </div>
                                  </div>
                                  <div class="col-md-3">
                                      <div class="form-group">
                                          <label for="to-programme">To Programme:</label>
                                          <select id="to-programme" name="to_programme_id" class="form-control">
                                              <option value="">Select Programme</option>
                                              @foreach($getRecord->unique('programme_id') as $programme)
                                              <option value="{{ $programme->programme_id }}">{{ $programme->category }}</option>
                                              @endforeach
                                          </select>
                                      </div>
                                  </div>
                                  <div class="col-md-3">
                                      <div class="form-group">
                                          <label for="to-unit">To Programme Year:</label>
                                          <select id="to-unit" name="to_unit" class="form-control" disabled>
                                              <option value="">Select Programme First</option>
                                          </select>
                                      </div>
                                  </div>
                              </div>
                          </fieldset>
                      </div>
                      <div class="col-md-2 mt-4">
                          <div class="text-right">
                              <button type="submit" class="btn btn-warning">
                                  Manage Promotion <i class="fas fa-paper-plane"></i>
                              </button>
                          </div>
                      </div>
                  </div>
              </form>
          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <script>
      var units = @json($getRecord);

      document.getElementById('from-programme').addEventListener('change', function() {
          var programme_id = this.value;
          var fromUnit = document.getElementById('from-unit');
          fromUnit.innerHTML = '';

          if (programme_id) {
              var filteredUnits = units.filter(function(unit) {
                  return unit.programme_id == programme_id;
              });

              filteredUnits.forEach(function(unit) {
                  var option = document.createElement('option');
                  option.value = unit.study_year_id;
                  option.text = unit.programme_year;
                  fromUnit.appendChild(option);
              });

              fromUnit.disabled = false;
          } else {
              fromUnit.disabled = true;
          }
      });

      document.getElementById('to-programme').addEventListener('change', function() {
          var programme_id = this.value;
          var toUnit = document.getElementById('to-unit');
          toUnit.innerHTML = '';

          if (programme_id) {
              var filteredUnits = units.filter(function(unit) {
                  return unit.programme_id == programme_id;
              });

              filteredUnits.forEach(function(unit) {
                  var option = document.createElement('option');
                  option.value = unit.study_year_id;
                  option.text = unit.programme_year;
                  toUnit.appendChild(option);
              });

              toUnit.disabled = false;
          } else {
              toUnit.disabled = true;
          }
      });
  </script>
@endsection
