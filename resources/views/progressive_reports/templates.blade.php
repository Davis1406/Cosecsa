@extends('layout.app')

@section('content')
  <style>
    .pr-tpl-table textarea.pr-tpl-autogrow { resize: none; overflow: hidden; min-height: 32px; width: 100%; font-size: .82rem; }
  </style>
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
                <table class="table table-sm table-striped mb-0 pr-tpl-table" data-user-id="{{ $userId }}">
                  <thead><tr><th style="width:30%;">Activity</th><th>Default Planned Activities</th><th style="width:8%;">Active</th><th style="width:8%;"></th></tr></thead>
                  <tbody class="pr-tpl-tbody">
                    @foreach($rows as $t)
                      @include('progressive_reports._template_row', ['tpl' => $t])
                    @endforeach
                    <tr class="pr-tpl-empty-row" @if(!$rows->isEmpty()) style="display:none;" @endif>
                      <td colspan="4" class="text-center text-muted py-2">No recurring tasks yet.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="card-footer">
              <button type="button" class="btn btn-sm btn-cosecsa-outline pr-tpl-add-row" data-user-id="{{ $userId }}">
                <i class="fas fa-plus mr-1"></i> Add Row
              </button>
              <span class="pr-tpl-save-flash text-success ml-2" style="font-size:.8rem; display:none;"><i class="fas fa-check"></i> Saved</span>
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

  // Grows a textarea's height to fit its content (no scrollbar, no manual
  // drag-resize needed) — same idea as the report task fields, but those
  // only resize by hand; these actually track what you type.
  function autoGrow(el) {
    el.style.height = 'auto';
    el.style.height = el.scrollHeight + 'px';
  }

  function buildTplRow(t) {
    const tr = document.createElement('tr');
    tr.dataset.templateId = t.id;
    tr.innerHTML = `
      <td><textarea class="form-control form-control-sm pr-tpl-field pr-tpl-autogrow" data-field="activity_description" rows="1" placeholder="Activity description">${(t.activity_description || '')}</textarea></td>
      <td><textarea class="form-control form-control-sm pr-tpl-field pr-tpl-autogrow" data-field="default_planned_activities" rows="1" placeholder="One per line">${(t.default_planned_activities || '')}</textarea></td>
      <td class="text-center"><input type="checkbox" class="pr-tpl-field" data-field="is_active" ${t.is_active ? 'checked' : ''}></td>
      <td class="text-center"><button type="button" class="btn btn-sm btn-danger pr-tpl-delete" title="Delete"><i class="fas fa-trash"></i></button></td>`;
    tr.querySelectorAll('.pr-tpl-autogrow').forEach(autoGrow);
    return tr;
  }

  // Size every existing textarea to its (possibly multi-line) saved content
  // on page load, then keep growing as the user types.
  document.querySelectorAll('.pr-tpl-autogrow').forEach(autoGrow);
  document.addEventListener('input', function (e) {
    if (e.target.classList.contains('pr-tpl-autogrow')) autoGrow(e.target);
  });

  // Autosave, same pattern as the report-task fields below: one request per
  // field on change (blur), no manual Save button. Delete stays explicit.
  document.querySelectorAll('.pr-tpl-table').forEach(function (table) {
    const card = table.closest('.card');
    const flash = card.querySelector('.pr-tpl-save-flash');

    table.addEventListener('change', function (e) {
      const field = e.target.closest('.pr-tpl-field');
      if (!field) return;
      const row = field.closest('tr');
      const id = row.dataset.templateId;
      const value = field.type === 'checkbox' ? field.checked : field.value;

      fetch(`{{ url('progressive-reports/templates') }}/${id}/update`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ [field.dataset.field]: value }),
      }).then(function (r) {
        if (!r.ok) throw new Error();
        if (flash) {
          flash.style.display = 'inline';
          setTimeout(() => flash.style.display = 'none', 1500);
        }
      }).catch(() => alert('Could not save that recurring task — please try again.'));
    });

    table.addEventListener('click', function (e) {
      const delBtn = e.target.closest('.pr-tpl-delete');
      if (!delBtn) return;
      const row = delBtn.closest('tr');
      if (!confirm('Remove this recurring task?')) return;
      fetch(`{{ url('progressive-reports/templates') }}/${row.dataset.templateId}/delete`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf },
      }).then(() => {
        row.remove();
        const tbody = table.querySelector('.pr-tpl-tbody');
        if (!tbody.querySelector('tr[data-template-id]')) {
          const emptyRow = tbody.querySelector('.pr-tpl-empty-row');
          if (emptyRow) emptyRow.style.display = '';
        }
      });
    });
  });

  // "Add Row" creates a blank recurring task instantly (like "Add Row" on
  // the report tasks table) — it appears empty and autosaves as you type,
  // no separate submit step.
  document.querySelectorAll('.pr-tpl-add-row').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const userId = btn.dataset.userId;
      const table = btn.closest('.card').querySelector('.pr-tpl-table');
      const tbody = table.querySelector('.pr-tpl-tbody');
      const emptyRow = tbody.querySelector('.pr-tpl-empty-row');

      fetch(`{{ url('progressive-reports/templates/add-blank') }}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ user_id: userId }),
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (emptyRow) emptyRow.style.display = 'none';
          const row = buildTplRow(data.template);
          tbody.appendChild(row);
          row.querySelector('.pr-tpl-field[data-field="activity_description"]').focus();
        })
        .catch(() => alert('Could not add a new row — please try again.'));
    });
  });
});
</script>
@endpush
