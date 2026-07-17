@extends('layout.app')

@section('content')
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-12">
            <h1 style="font-size:1.4rem;">College Reports</h1>
            <p class="text-muted mb-0" style="font-size:.85rem;">
              Pick a report type, choose the fields you need, optionally filter and group — then view the visual
              report or export it to Excel.
            </p>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        @include('_message')
        <div class="card">
          <div class="card-body">
            <form method="post" action="{{ url('admin/reports/generate') }}" id="reportForm">
              @csrf

              <div class="form-group">
                <label>1. Report Type</label>
                <select class="form-control" id="typeSelect" name="type" required style="max-width:400px;">
                  <option value="">— Select —</option>
                  @foreach($types as $key => $t)
                    <option value="{{ $key }}">{{ $t['label'] }}</option>
                  @endforeach
                </select>
              </div>

              <div id="fieldsSection" style="display:none;">
                <div class="form-group">
                  <label>2. Fields Needed</label>
                  <div id="fieldsCheckboxes" class="border rounded p-3" style="columns:3;"></div>
                </div>

                <div class="form-group">
                  <label>3. Filters (optional)</label>
                  <div id="filtersRow" class="form-row"></div>
                </div>

                <div class="form-group">
                  <label>4. Group / Chart By (optional)</label>
                  <select class="form-control" name="group_by" id="groupBySelect" style="max-width:400px;">
                    <option value="">No chart — table only</option>
                  </select>
                </div>

                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-chart-bar mr-1"></i> Generate Report
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </section>
  </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const typeSelect = document.getElementById('typeSelect');
  const fieldsSection = document.getElementById('fieldsSection');
  const fieldsBox = document.getElementById('fieldsCheckboxes');
  const filtersRow = document.getElementById('filtersRow');
  const groupBySelect = document.getElementById('groupBySelect');

  typeSelect.addEventListener('change', function () {
    const type = this.value;
    fieldsBox.innerHTML = '';
    filtersRow.innerHTML = '';
    groupBySelect.innerHTML = '<option value="">No chart — table only</option>';

    if (!type) { fieldsSection.style.display = 'none'; return; }

    fetch("{{ url('admin/reports/fields') }}/" + type)
      .then(r => r.json())
      .then(data => {
        Object.entries(data.fields).forEach(([key, label]) => {
          fieldsBox.insertAdjacentHTML('beforeend',
            `<div class="form-check"><input class="form-check-input" type="checkbox" name="fields[]" value="${key}" id="f_${key}" checked>
             <label class="form-check-label" for="f_${key}">${label}</label></div>`);
        });

        Object.entries(data.filters).forEach(([key, filter]) => {
          let opts = '<option value="">All</option>';
          Object.entries(filter.options).forEach(([val, label]) => {
            opts += `<option value="${val}">${label}</option>`;
          });
          filtersRow.insertAdjacentHTML('beforeend',
            `<div class="form-group col-md-3">
               <label style="font-size:.8rem;">${filter.label}</label>
               <select class="form-control form-control-sm" name="filters[${key}]">${opts}</select>
             </div>`);
        });

        data.group_by.forEach(key => {
          const label = data.fields[key] || key;
          groupBySelect.insertAdjacentHTML('beforeend', `<option value="${key}">${label}</option>`);
        });

        fieldsSection.style.display = 'block';
      });
  });
});
</script>
@endpush
