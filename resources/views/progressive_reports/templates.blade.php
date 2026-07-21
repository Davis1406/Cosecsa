@extends('layout.app')

@section('content')
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-6">
            <h1 style="font-size:1.4rem;">Recurring Tasks</h1>
            <p class="text-muted mb-0" style="font-size:.85rem;">These pre-populate every new report period so nobody starts from a blank sheet.</p>
          </div>
          <div class="col-sm-6 text-right">
            <a href="{{ url('progressive-reports') }}" class="btn btn-cosecsa-outline">
              <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        @include('_message')

        @foreach($sections as $section)
          @php $userId = $section['user_id']; $rows = $templatesByUser->get($userId, collect()); @endphp
          <div class="card">
            <div class="card-header"><h3 class="card-title" style="font-size:1rem;">{{ $section['label'] }}</h3></div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-sm table-striped mb-0 pr-template-table">
                  <thead><tr><th style="width:30%;">Activity</th><th>Default Planned Activities</th><th style="width:8%;">Active</th><th style="width:12%;"></th></tr></thead>
                  <tbody>
                    @foreach($rows as $t)
                      <tr data-template-id="{{ $t->id }}">
                        <td><input type="text" class="form-control form-control-sm pr-tpl-field" data-field="activity_description" value="{{ $t->activity_description }}"></td>
                        <td><input type="text" class="form-control form-control-sm pr-tpl-field" data-field="default_planned_activities" value="{{ $t->default_planned_activities }}"></td>
                        <td class="text-center"><input type="checkbox" class="pr-tpl-field" data-field="is_active" {{ $t->is_active ? 'checked' : '' }}></td>
                        <td>
                          <button type="button" class="btn btn-sm btn-cosecsa-outline pr-tpl-save">Save</button>
                          <button type="button" class="btn btn-sm btn-danger pr-tpl-delete">Delete</button>
                        </td>
                      </tr>
                    @endforeach
                    @if($rows->isEmpty())
                      <tr><td colspan="4" class="text-center text-muted py-2">No recurring tasks yet.</td></tr>
                    @endif
                  </tbody>
                </table>
              </div>
            </div>
            <div class="card-footer">
              <form method="POST" action="{{ url('progressive-reports/templates') }}" class="form-inline">
                @csrf
                <input type="hidden" name="user_id" value="{{ $userId }}">
                <input type="text" name="activity_description" class="form-control form-control-sm mr-2" placeholder="New recurring task…" required style="min-width:260px;">
                <input type="text" name="default_planned_activities" class="form-control form-control-sm mr-2" placeholder="Default planned activities (optional)" style="min-width:300px;">
                <button type="submit" class="btn btn-sm btn-cosecsa">Add</button>
              </form>
            </div>
          </div>
        @endforeach
      </div>
    </section>
  </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const csrf = '{{ csrf_token() }}';

  document.querySelectorAll('.pr-template-table').forEach(function (table) {
    table.addEventListener('click', function (e) {
      const saveBtn = e.target.closest('.pr-tpl-save');
      const delBtn = e.target.closest('.pr-tpl-delete');
      if (!saveBtn && !delBtn) return;

      const row = (saveBtn || delBtn).closest('tr');
      const id = row.dataset.templateId;

      if (saveBtn) {
        const body = new URLSearchParams();
        row.querySelectorAll('.pr-tpl-field').forEach(function (f) {
          if (f.type === 'checkbox') { if (f.checked) body.append(f.dataset.field, '1'); }
          else { body.append(f.dataset.field, f.value); }
        });
        fetch(`{{ url('progressive-reports/templates') }}/${id}/update`, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/x-www-form-urlencoded' },
          body: body.toString(),
        }).then(() => {
          saveBtn.innerHTML = '<i class="fas fa-check"></i>';
          setTimeout(() => saveBtn.textContent = 'Save', 1200);
        });
      }

      if (delBtn) {
        if (!confirm('Remove this recurring task?')) return;
        fetch(`{{ url('progressive-reports/templates') }}/${id}/delete`, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': csrf },
        }).then(() => row.remove());
      }
    });
  });
});
</script>
@endpush
