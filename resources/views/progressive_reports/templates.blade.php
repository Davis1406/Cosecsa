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
                <table class="table table-sm table-striped mb-0 pr-template-table" data-user-id="{{ $userId }}">
                  <thead><tr><th style="width:30%;">Activity</th><th>Default Planned Activities</th><th style="width:8%;">Active</th><th style="width:12%;"></th></tr></thead>
                  <tbody class="pr-tpl-tbody">
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
                    <tr class="pr-tpl-empty-row" @if(!$rows->isEmpty()) style="display:none;" @endif>
                      <td colspan="4" class="text-center text-muted py-2">No recurring tasks yet.</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
            <div class="card-footer pr-tpl-new-wrap" data-user-id="{{ $userId }}">
              <div class="pr-tpl-new-rows">
                <div class="form-inline pr-tpl-new-row mb-2">
                  <input type="text" class="form-control form-control-sm mr-2 pr-tpl-new-activity" placeholder="New recurring task…" style="min-width:260px;">
                  <input type="text" class="form-control form-control-sm mr-2 pr-tpl-new-planned" placeholder="Default planned activities (optional)" style="min-width:300px;">
                  <button type="button" class="btn btn-sm btn-outline-danger pr-tpl-new-remove" title="Remove this row" style="display:none;"><i class="fas fa-times"></i></button>
                </div>
              </div>
              <button type="button" class="btn btn-sm btn-cosecsa-outline pr-tpl-new-add-row">
                <i class="fas fa-plus mr-1"></i> Add another row
              </button>
              <button type="button" class="btn btn-sm btn-cosecsa pr-tpl-new-save-all">
                Save all
              </button>
              <span class="pr-tpl-new-status ml-2 text-muted" style="font-size:.8rem;"></span>
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

  // ── Add new recurring tasks (one row or several) without reloading ──
  // Type several activities across multiple rows, then "Save all" sends
  // them together to templates/bulk in one request.
  function buildSavedRow(t) {
    const tr = document.createElement('tr');
    tr.dataset.templateId = t.id;
    tr.innerHTML = `
      <td><input type="text" class="form-control form-control-sm pr-tpl-field" data-field="activity_description" value="${t.activity_description ? t.activity_description.replace(/"/g, '&quot;') : ''}"></td>
      <td><input type="text" class="form-control form-control-sm pr-tpl-field" data-field="default_planned_activities" value="${t.default_planned_activities ? t.default_planned_activities.replace(/"/g, '&quot;') : ''}"></td>
      <td class="text-center"><input type="checkbox" class="pr-tpl-field" data-field="is_active" ${t.is_active ? 'checked' : ''}></td>
      <td>
        <button type="button" class="btn btn-sm btn-cosecsa-outline pr-tpl-save">Save</button>
        <button type="button" class="btn btn-sm btn-danger pr-tpl-delete">Delete</button>
      </td>`;
    return tr;
  }

  document.querySelectorAll('.pr-tpl-new-wrap').forEach(function (wrap) {
    const userId = wrap.dataset.userId;
    const rowsWrap = wrap.querySelector('.pr-tpl-new-rows');
    const status = wrap.querySelector('.pr-tpl-new-status');
    const table = wrap.closest('.card').querySelector('.pr-template-table');
    const tbody = table.querySelector('.pr-tpl-tbody');
    const emptyRow = tbody.querySelector('.pr-tpl-empty-row');

    function newRowEl() {
      const div = document.createElement('div');
      div.className = 'form-inline pr-tpl-new-row mb-2';
      div.innerHTML = `
        <input type="text" class="form-control form-control-sm mr-2 pr-tpl-new-activity" placeholder="New recurring task…" style="min-width:260px;">
        <input type="text" class="form-control form-control-sm mr-2 pr-tpl-new-planned" placeholder="Default planned activities (optional)" style="min-width:300px;">
        <button type="button" class="btn btn-sm btn-outline-danger pr-tpl-new-remove" title="Remove this row"><i class="fas fa-times"></i></button>`;
      return div;
    }

    function refreshRemoveButtons() {
      const rows = rowsWrap.querySelectorAll('.pr-tpl-new-row');
      rows.forEach(function (r) {
        r.querySelector('.pr-tpl-new-remove').style.display = rows.length > 1 ? '' : 'none';
      });
    }

    wrap.querySelector('.pr-tpl-new-add-row').addEventListener('click', function () {
      const row = newRowEl();
      rowsWrap.appendChild(row);
      refreshRemoveButtons();
      row.querySelector('.pr-tpl-new-activity').focus();
    });

    rowsWrap.addEventListener('click', function (e) {
      const removeBtn = e.target.closest('.pr-tpl-new-remove');
      if (!removeBtn) return;
      const rows = rowsWrap.querySelectorAll('.pr-tpl-new-row');
      if (rows.length > 1) removeBtn.closest('.pr-tpl-new-row').remove();
      refreshRemoveButtons();
    });

    // Enter in a "new task" field adds another row and moves focus there,
    // so someone can keep typing tasks one after another without touching
    // the mouse — the actual save only happens on "Save all".
    rowsWrap.addEventListener('keydown', function (e) {
      if (e.key !== 'Enter') return;
      if (!e.target.classList.contains('pr-tpl-new-activity') && !e.target.classList.contains('pr-tpl-new-planned')) return;
      e.preventDefault();
      wrap.querySelector('.pr-tpl-new-add-row').click();
    });

    wrap.querySelector('.pr-tpl-new-save-all').addEventListener('click', function () {
      const saveBtn = this;
      const tasks = [];
      rowsWrap.querySelectorAll('.pr-tpl-new-row').forEach(function (r) {
        const activity = r.querySelector('.pr-tpl-new-activity').value.trim();
        const planned = r.querySelector('.pr-tpl-new-planned').value.trim();
        if (activity) tasks.push({ activity_description: activity, default_planned_activities: planned || null });
      });

      if (tasks.length === 0) {
        status.textContent = 'Type at least one task first.';
        status.className = 'pr-tpl-new-status ml-2 text-danger';
        return;
      }

      saveBtn.disabled = true;
      status.textContent = 'Saving…';
      status.className = 'pr-tpl-new-status ml-2 text-muted';

      fetch(`{{ url('progressive-reports/templates/bulk') }}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ user_id: userId, tasks: tasks }),
      })
        .then(function (res) {
          if (!res.ok) throw new Error('Request failed (' + res.status + ')');
          return res.json();
        })
        .then(function (data) {
          if (emptyRow) emptyRow.style.display = 'none';
          data.templates.forEach(function (t) { tbody.appendChild(buildSavedRow(t)); });

          // Reset the footer back to a single blank row.
          rowsWrap.innerHTML = '';
          rowsWrap.appendChild(newRowEl());
          refreshRemoveButtons();

          status.textContent = tasks.length + (tasks.length === 1 ? ' task added.' : ' tasks added.');
          status.className = 'pr-tpl-new-status ml-2 text-success';
          rowsWrap.querySelector('.pr-tpl-new-activity').focus();
        })
        .catch(function (err) {
          status.textContent = 'Could not save — ' + err.message;
          status.className = 'pr-tpl-new-status ml-2 text-danger';
        })
        .finally(function () {
          saveBtn.disabled = false;
        });
    });
  });
});
</script>
@endpush
