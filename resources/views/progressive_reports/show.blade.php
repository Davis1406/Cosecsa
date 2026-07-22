@extends('layout.app')

@section('content')
  <style>
    .pr-table textarea { min-height: 60px; resize: vertical; font-size: .82rem; width: 100%; }
    .pr-table textarea.pr-activity { min-height: 44px; font-size: .85rem; font-weight: 600; }
    .pr-section-card.mine { border-left: 4px solid #a02626; }
    .pr-section-card { scroll-margin-top: 75px; }
    .pr-save-flash { display:none; }
    .pr-header-actions {
      display: flex;
      flex-wrap: wrap;
      justify-content: flex-end;
      gap: 10px;
    }
    .pr-header-actions form { margin: 0; }
    .pr-table td[style*="pre-line"] { line-height: 1.5; }
  </style>
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-12 col-lg-6">
            <h1 style="font-size:1.4rem;">
              @if($period)
                Secretariat Progress Report — {{ $period->period_month->format('F Y') }}
              @else
                My Progress Report
              @endif
            </h1>
            @if($period)
              <p class="text-muted mb-0" style="font-size:.85rem;">
                Due {{ $period->due_date->format('d M Y') }}
                <span class="badge {{ $period->status === 'consolidated' ? 'badge-success' : 'badge-secondary' }} ml-2">{{ ucfirst($period->status) }}</span>
              </p>
            @endif

            <div class="d-flex flex-wrap align-items-center mt-2" style="gap:20px;">
              @if(isset($myPeriods))
                <form method="GET" action="{{ url('progressive-reports/my') }}" class="form-inline mb-0">
                  <label class="mr-2" style="font-size:.85rem;">Past reports:</label>
                  <select name="period_id" class="form-control form-control-sm" onchange="this.form.submit()" style="min-width:160px;">
                    @foreach($myPeriods as $mp)
                      <option value="{{ $mp->id }}" {{ $selectedPeriodId == $mp->id ? 'selected' : '' }}>
                        {{ $mp->period_month->format('F Y') }} ({{ ucfirst($mp->status) }})
                      </option>
                    @endforeach
                  </select>
                </form>
              @endif

              @if(isset($isManager) && $isManager)
                <form method="POST" action="{{ url('progressive-reports/open') }}" class="form-inline mb-0">
                  @csrf
                  <label class="mr-2 font-weight-bold" style="font-size:.85rem;">Add a new month report:</label>
                  <label class="mr-2" style="font-size:.85rem;">Choose month</label>
                  <input type="month" name="period_month" class="form-control form-control-sm mr-2" value="{{ now()->format('Y-m') }}" required>
                  <button type="submit" class="btn btn-sm btn-cosecsa-outline"><i class="fas fa-plus mr-1"></i> Add Month</button>
                </form>
              @endif
            </div>
          </div>
          <div class="col-12 col-lg-6 mt-2 mt-lg-0 pr-header-actions">
            @if($period && $canManage)
              <a href="{{ url('progressive-reports/'.$period->id.'/download') }}" class="btn btn-cosecsa-outline" target="_blank">
                <i class="fas fa-file-pdf mr-1"></i> Download PDF
              </a>
              <form method="POST" action="{{ url('progressive-reports/'.$period->id.'/share-ceo') }}">
                @csrf
                <button type="submit" class="btn btn-cosecsa-outline" onclick="return confirm('Generate the current PDF and send it to the CEO via Messages?')">
                  <i class="fas fa-paper-plane mr-1"></i> Share with CEO
                </button>
              </form>
              @if($period->status !== 'consolidated')
                <form method="POST" action="{{ url('progressive-reports/'.$period->id.'/consolidate') }}">
                  @csrf
                  <button type="submit" class="btn btn-cosecsa" onclick="return confirm('Mark this period as consolidated?')">
                    <i class="fas fa-check-circle mr-1"></i> Consolidate
                  </button>
                </form>
              @else
                <form method="POST" action="{{ url('progressive-reports/'.$period->id.'/unconsolidate') }}">
                  @csrf
                  <button type="submit" class="btn btn-cosecsa-outline" onclick="return confirm('Reopen this period for editing?')">
                    <i class="fas fa-undo mr-1"></i> Unconsolidate
                  </button>
                </form>
              @endif
              @if(Auth::user()->isSuperAdmin())
                <form method="POST" action="{{ url('progressive-reports/'.$period->id.'/delete') }}" onsubmit="return confirm('Permanently delete this report? This removes every section\'s data and cannot be undone.')">
                  @csrf
                  <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash mr-1"></i> Delete Report
                  </button>
                </form>
              @endif
            @endif
            <a href="{{ $backUrl ?? url('progressive-reports') }}" class="btn btn-cosecsa-outline">
              <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        @include('_message')

        @if(isset($myPeriods))
          <div class="card">
            <div class="card-header" style="cursor:pointer;" data-toggle="collapse" data-target="#myTemplatesBody">
              <h3 class="card-title" style="font-size:1rem;"><i class="fas fa-redo mr-2"></i>My Recurring Tasks</h3>
              <div class="card-tools"><i class="fas fa-chevron-down"></i></div>
            </div>
            <div class="collapse" id="myTemplatesBody">
              <div class="card-body">
                <p class="text-muted" style="font-size:.82rem;">
                  These are activities you report on every month. Add them here once, then pick them from the
                  "Activity Description" dropdown on any row below instead of retyping. Remove any you no longer need.
                </p>
                <table class="table table-sm table-bordered mb-3">
                  <thead><tr><th style="width:35%;">Activity Description</th><th>Default Planned Activities</th><th style="width:8%;">Active</th><th style="width:10%;"></th></tr></thead>
                  <tbody>
                    @forelse($myTemplates as $tpl)
                      <tr>
                        <form method="POST" action="{{ url('progressive-reports/templates/'.$tpl->id.'/update') }}">
                          @csrf
                          <td><input type="text" name="activity_description" class="form-control form-control-sm" value="{{ $tpl->activity_description }}" required></td>
                          <td><input type="text" name="default_planned_activities" class="form-control form-control-sm" value="{{ $tpl->default_planned_activities }}" placeholder="One per line"></td>
                          <td class="text-center"><input type="checkbox" name="is_active" value="1" {{ $tpl->is_active ? 'checked' : '' }}></td>
                          <td class="text-center" style="white-space:nowrap;">
                            <button type="submit" class="btn btn-sm btn-cosecsa-outline" title="Save"><i class="fas fa-save"></i></button>
                        </form>
                        <form method="POST" action="{{ url('progressive-reports/templates/'.$tpl->id.'/delete') }}" style="display:inline;" onsubmit="return confirm('Remove this recurring task?')">
                              @csrf
                              <button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                          </td>
                      </tr>
                    @empty
                      <tr><td colspan="4" class="text-center text-muted py-2">No recurring tasks yet — add one below.</td></tr>
                    @endforelse
                  </tbody>
                </table>
                <form method="POST" action="{{ url('progressive-reports/templates') }}" class="form-row align-items-center">
                  @csrf
                  <div class="col-md-4 mb-1">
                    <input type="text" name="activity_description" class="form-control form-control-sm" placeholder="New activity description" required>
                  </div>
                  <div class="col-md-6 mb-1">
                    <input type="text" name="default_planned_activities" class="form-control form-control-sm" placeholder="Default planned activities (one per line)">
                  </div>
                  <div class="col-md-2 mb-1">
                    <button type="submit" class="btn btn-sm btn-cosecsa btn-block"><i class="fas fa-plus mr-1"></i> Add</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        @endif

        @if(isset($canApproveAccess) && $canApproveAccess && isset($pendingAccessRequests) && $pendingAccessRequests->isNotEmpty())
          <div class="card card-outline card-warning">
            <div class="card-header">
              <h3 class="card-title" style="font-size:1rem;"><i class="fas fa-unlock-alt mr-2"></i>Pending Edit-Access Requests</h3>
            </div>
            <div class="card-body p-0">
              <table class="table table-sm mb-0">
                <thead><tr><th>Requested By</th><th>Section</th><th>Period</th><th>Requested</th><th class="text-center">Action</th></tr></thead>
                <tbody>
                  @foreach($pendingAccessRequests as $ar)
                    <tr>
                      <td>{{ $ar->requester->name ?? '—' }}</td>
                      <td>{{ $ar->participant->section_label ?? '—' }}</td>
                      <td>{{ $ar->participant->period->period_month->format('F Y') ?? '—' }}</td>
                      <td>{{ $ar->created_at->format('d M, H:i') }}</td>
                      <td class="text-center" style="white-space:nowrap;">
                        <form method="POST" action="{{ url('progressive-reports/access-requests/'.$ar->id.'/approve') }}" style="display:inline;">
                          @csrf
                          <button type="submit" class="btn btn-sm btn-cosecsa">Approve</button>
                        </form>
                        <form method="POST" action="{{ url('progressive-reports/access-requests/'.$ar->id.'/deny') }}" style="display:inline;">
                          @csrf
                          <button type="submit" class="btn btn-sm btn-danger">Deny</button>
                        </form>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        @endif

        @if(! $period)
          <div class="card"><div class="card-body text-center text-muted py-4">
            You don't have a section on any report period yet.
          </div></div>
        @else

        @foreach($period->participants as $participant)
          @php
            $isMine = $participant->user_id == $myUserId;
            $canEdit = $isMine || $canManage;
            $isLocked = $participant->isLocked();
            $lockReason = ! $isLocked ? null
              : (! $participant->period->is_current ? 'past-month' : 'deadline');
            $canEditNow = $canEdit && ! $isLocked;
          @endphp
          <div class="card pr-section-card {{ $isMine ? 'mine' : '' }}" id="participant-{{ $participant->id }}">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h3 class="card-title" style="font-size:1rem;">
                {{ $participant->section_label }}
                <span class="text-muted" style="font-size:.8rem;">({{ $participant->user->name ?? '—' }})</span>
              </h3>
              <div>
                <span class="badge {{ $participant->status === 'submitted' ? 'badge-success' : 'badge-secondary' }}">
                  {{ $participant->status === 'submitted' ? 'Submitted '.$participant->submitted_at->format('d M, H:i') : 'Pending' }}
                </span>
                @if($isLocked)
                  <span class="badge badge-warning ml-1"><i class="fas fa-lock"></i>
                    @if($lockReason === 'past-month') Locked — past month
                    @elseif($lockReason === 'deadline') Locked — deadline passed
                    @else Locked
                    @endif
                  </span>
                @endif
                @if($canEditNow)
                  <form method="POST" action="{{ url('progressive-reports/'.$period->id.'/participants/'.$participant->id.'/copy-forward') }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-cosecsa-outline ml-1">Copy Last Month</button>
                  </form>
                @elseif($canEdit && $isLocked)
                  @if($participant->pendingAccessRequest)
                    <span class="badge badge-secondary ml-1">Access request pending Administrative Officer approval</span>
                  @else
                    <form method="POST" action="{{ url('progressive-reports/'.$period->id.'/participants/'.$participant->id.'/request-access') }}" style="display:inline;">
                      @csrf
                      <button type="submit" class="btn btn-sm btn-cosecsa-outline ml-1"><i class="fas fa-unlock mr-1"></i> Request Edit Access</button>
                    </form>
                  @endif
                @endif
              </div>
            </div>
            <div class="card-body p-0">
              <div class="table-responsive">
                <table class="table table-bordered table-sm pr-table mb-0">
                  <thead>
                    <tr>
                      <th style="width:3%;">No</th>
                      <th style="width:20%;">Activity Description</th>
                      <th style="width:23%;">Planned Activities</th>
                      <th style="width:26%;">Current Status</th>
                      <th style="width:23%;">Next Steps &amp; Time Frame</th>
                      @if($canEditNow)<th style="width:4%;"></th>@endif
                    </tr>
                  </thead>
                  <tbody data-participant-id="{{ $participant->id }}">
                    @forelse($participant->tasks as $task)
                      <tr data-task-id="{{ $task->id }}">
                        <td>{{ $task->row_no }}</td>
                        @if($canEditNow)
                          <td>
                            @if(! empty($templatesByUser[$participant->user_id]))
                              <select class="form-control form-control-sm pr-activity-picker mb-1">
                                <option value="">— select from my recurring tasks —</option>
                                @foreach($templatesByUser[$participant->user_id] as $tpl)
                                  <option value="{{ $tpl->activity_description }}" data-planned="{{ $tpl->default_planned_activities }}">{{ $tpl->activity_description }}</option>
                                @endforeach
                              </select>
                            @endif
                            <textarea class="form-control form-control-sm pr-field pr-activity" data-field="activity_description">{{ $task->activity_description }}</textarea>
                          </td>
                          <td><textarea class="form-control form-control-sm pr-field pr-bullet-field" data-field="planned_activities" placeholder="Press Enter for a new bullet point">{{ $task->planned_activities }}</textarea></td>
                          <td><textarea class="form-control form-control-sm pr-field pr-bullet-field" data-field="current_status" placeholder="Press Enter for a new bullet point">{{ $task->current_status }}</textarea></td>
                          <td><textarea class="form-control form-control-sm pr-field pr-bullet-field" data-field="next_steps" placeholder="Press Enter for a new bullet point">{{ $task->next_steps }}</textarea></td>
                          <td class="text-center">
                            <a href="#" class="text-danger pr-delete-row" title="Delete row"><i class="fas fa-trash"></i></a>
                          </td>
                        @else
                          <td>{{ $task->activity_description }}</td>
                          <td style="white-space:pre-line;">{{ $task->planned_activities }}</td>
                          <td style="white-space:pre-line;">{{ $task->current_status }}</td>
                          <td style="white-space:pre-line;">{{ $task->next_steps }}</td>
                        @endif
                      </tr>
                    @empty
                      <tr><td colspan="{{ $canEditNow ? 6 : 5 }}" class="text-center text-muted py-2">No tasks yet.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
            @if($canEditNow)
              <div class="card-footer d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-sm btn-cosecsa-outline pr-add-row" data-participant-id="{{ $participant->id }}"
                        data-templates="{{ json_encode(($templatesByUser[$participant->user_id] ?? collect())->map(fn($t) => ['activity_description' => $t->activity_description, 'default_planned_activities' => $t->default_planned_activities])->values()) }}">
                  <i class="fas fa-plus mr-1"></i> Add Row
                </button>
                <div>
                  <span class="pr-save-flash text-success mr-2" style="font-size:.8rem;"><i class="fas fa-check"></i> Saved</span>
                  <form method="POST" action="{{ url('progressive-reports/'.$period->id.'/participants/'.$participant->id.'/submit') }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-cosecsa">
                      {{ $participant->status === 'submitted' ? 'Re-submit' : 'Submit Section' }}
                    </button>
                  </form>
                </div>
              </div>
            @endif
          </div>
        @endforeach
        @endif
      </div>
    </section>
  </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const periodId = {{ $period->id ?? 'null' }};
  const csrf = '{{ csrf_token() }}';

  // Planned Activities / Current Status / Next Steps are bulleted lists —
  // Enter starts a new "❖ " point instead of a bare newline, and an empty
  // field gets its first bullet as soon as the user starts typing.
  function attachBulletBehavior(fields) {
    fields.forEach(function (field) {
      field.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        e.preventDefault();
        const start = field.selectionStart;
        const end = field.selectionEnd;
        const insert = '\n❖ ';
        field.value = field.value.slice(0, start) + insert + field.value.slice(end);
        const pos = start + insert.length;
        field.selectionStart = field.selectionEnd = pos;
      });
      field.addEventListener('focus', function () {
        if (! field.value) {
          field.value = '❖ ';
          field.selectionStart = field.selectionEnd = field.value.length;
        }
      });
    });
  }
  attachBulletBehavior(document.querySelectorAll('.pr-bullet-field'));

  function saveField(taskId, fieldName, value, cardFooterFlash, fieldEl) {
    fetch(`{{ url('progressive-reports') }}/${periodId}/tasks/${taskId}/update`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
      body: JSON.stringify({ [fieldName]: value }),
    }).then(r => r.json()).then(data => {
      // Bullet fields get a "❖ " prefix normalized server-side even if the
      // live typing behavior didn't add one (pasted text, select-all retype,
      // etc.) — sync the textarea so the bullet is visible immediately
      // instead of only appearing after a reload.
      if (fieldEl && data.task && data.task[fieldName] !== undefined && data.task[fieldName] !== value) {
        fieldEl.value = data.task[fieldName] || '';
      }
      if (cardFooterFlash) {
        cardFooterFlash.style.display = 'inline';
        setTimeout(() => cardFooterFlash.style.display = 'none', 1500);
      }
    }).catch(() => alert('Could not save — please try again.'));
  }

  document.querySelectorAll('.pr-table').forEach(function (table) {
    const card = table.closest('.card');
    const flash = card.querySelector('.pr-save-flash');

    table.addEventListener('change', function (e) {
      const field = e.target.closest('.pr-field');
      if (!field) return;
      const row = field.closest('tr');
      const taskId = row.dataset.taskId;
      saveField(taskId, field.dataset.field, field.value, flash, field);
    });

    table.addEventListener('change', function (e) {
      const picker = e.target.closest('.pr-activity-picker');
      if (!picker || !picker.value) return;
      const row = picker.closest('tr');
      const opt = picker.options[picker.selectedIndex];
      const activityField = row.querySelector('.pr-field[data-field="activity_description"]');
      const plannedField = row.querySelector('.pr-field[data-field="planned_activities"]');
      activityField.value = picker.value;
      activityField.dispatchEvent(new Event('change', { bubbles: true }));
      if (opt.dataset.planned && plannedField && !plannedField.value) {
        plannedField.value = opt.dataset.planned;
        plannedField.dispatchEvent(new Event('change', { bubbles: true }));
      }
      picker.value = '';
    });

    table.addEventListener('click', function (e) {
      const delBtn = e.target.closest('.pr-delete-row');
      if (!delBtn) return;
      e.preventDefault();
      if (!confirm('Delete this row?')) return;
      const row = delBtn.closest('tr');
      const taskId = row.dataset.taskId;
      fetch(`{{ url('progressive-reports') }}/${periodId}/tasks/${taskId}/delete`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
      }).then(r => r.json()).then(() => row.remove());
    });
  });

  document.querySelectorAll('.pr-add-row').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const participantId = btn.dataset.participantId;
      let templates = [];
      try { templates = JSON.parse(btn.dataset.templates || '[]'); } catch (e) {}
      fetch(`{{ url('progressive-reports') }}/${periodId}/participants/${participantId}/tasks/add`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
      }).then(r => r.json()).then(data => {
        const tbody = document.querySelector(`tbody[data-participant-id="${participantId}"]`);
        const placeholder = tbody.querySelector('td[colspan]');
        if (placeholder) placeholder.closest('tr').remove();
        const tr = document.createElement('tr');
        tr.setAttribute('data-task-id', data.task.id);
        const pickerHtml = templates.length ? `
          <select class="form-control form-control-sm pr-activity-picker mb-1">
            <option value="">— select from my recurring tasks —</option>
            ${templates.map(t => `<option value="${t.activity_description.replace(/"/g, '&quot;')}" data-planned="${(t.default_planned_activities || '').replace(/"/g, '&quot;')}">${t.activity_description}</option>`).join('')}
          </select>` : '';
        tr.innerHTML = `
          <td>${data.task.row_no}</td>
          <td>${pickerHtml}<textarea class="form-control form-control-sm pr-field pr-activity" data-field="activity_description"></textarea></td>
          <td><textarea class="form-control form-control-sm pr-field pr-bullet-field" data-field="planned_activities" placeholder="Press Enter for a new bullet point"></textarea></td>
          <td><textarea class="form-control form-control-sm pr-field pr-bullet-field" data-field="current_status" placeholder="Press Enter for a new bullet point"></textarea></td>
          <td><textarea class="form-control form-control-sm pr-field pr-bullet-field" data-field="next_steps" placeholder="Press Enter for a new bullet point"></textarea></td>
          <td class="text-center"><a href="#" class="text-danger pr-delete-row" title="Delete row"><i class="fas fa-trash"></i></a></td>
        `;
        attachBulletBehavior(tr.querySelectorAll('.pr-bullet-field'));
        tbody.appendChild(tr);
      });
    });
  });
});
</script>
@endpush
