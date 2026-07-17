@extends('layout.app')

@section('content')
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-12">
            <h1 style="font-size:1.4rem;">College Reports</h1>
            <p class="text-muted mb-0" style="font-size:.85rem;">
              Pick a report type below — it generates immediately with every field and a chart. You can then
              change which fields show, filter, or re-chart from the results page.
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
                <label>Report Type</label>
                <select class="form-control" id="typeSelect" name="type" required style="max-width:400px;">
                  <option value="">— Select a report type to generate it —</option>
                  @foreach($types as $key => $t)
                    <option value="{{ $key }}">{{ $t['label'] }}</option>
                  @endforeach
                </select>
              </div>
              <div id="loadingNote" class="text-muted" style="display:none; font-size:.85rem;">
                <i class="fas fa-spinner fa-spin mr-1"></i> Generating…
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
  const form = document.getElementById('reportForm');
  const loadingNote = document.getElementById('loadingNote');

  typeSelect.addEventListener('change', function () {
    const type = this.value;
    if (!type) return;

    loadingNote.style.display = 'block';

    fetch("{{ url('admin/reports/fields') }}/" + type)
      .then(r => r.json())
      .then(data => {
        // Every field checked by default — the results page lets you
        // narrow this down without re-picking from scratch.
        Object.keys(data.fields).forEach(key => {
          const input = document.createElement('input');
          input.type = 'hidden';
          input.name = 'fields[]';
          input.value = key;
          form.appendChild(input);
        });
        // Chart on the first available group-by field, if any.
        if (data.group_by.length) {
          const gb = document.createElement('input');
          gb.type = 'hidden';
          gb.name = 'group_by';
          gb.value = data.group_by[0];
          form.appendChild(gb);
        }
        form.submit();
      });
  });
});
</script>
@endpush
