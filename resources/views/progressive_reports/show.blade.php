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
  </style>
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-12 col-lg-6">
            <h1 style="font-size:1.4rem;">Secretariat Progress Report — {{ $period->period_month->format('F Y') }}</h1>
            <p class="text-muted mb-0" style="font-size:.85rem;">
              Due {{ $period->due_date->format('d M Y') }}
              <span class="badge {{ $period->status === 'consolidated' ? 'badge-success' : 'badge-secondary' }} ml-2">{{ ucfirst($period->status) }}</span>
            </p>
          </div>
          <div class="col-12 col-lg-6 mt-2 mt-lg-0 pr-header-actions">
            <a href="{{ url('progressive-reports/'.$period->id.'/download') }}" class="btn btn-cosecsa-outline" target="_blank">
              <i class="fas fa-file-pdf mr-1"></i> Download PDF
            </a>
            @if($canManage)
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
            @endif
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

        @foreach($period->participants as $participant)
          @php $isMine = $participant->user_id == $myUserId; $canEdit = $isMine || $canManage; @endphp
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
                @if($canEdit)
                  <form method="POST" action="{{ url('progressive-reports/'.$period->id.'/participants/'.$participant->id.'/copy-forward') }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-cosecsa-outline ml-1">Copy Last Month</button>
                  </form>
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
                      @if($canEdit)<th style="width:4%;"></th>@endif
                    </tr>
                  </thead>
                  <tbody data-participant-id="{{ $participant->id }}">
                    @forelse($participant->tasks as $task)
                      <tr data-task-id="{{ $task->id }}">
                        <td>{{ $task->row_no }}</td>
                        @if($canEdit)
                          <td><textarea class="form-control form-control-sm pr-field pr-activity" data-field="activity_description">{{ $task->activity_description }}</textarea></td>
                          <td><textarea class="form-control form-control-sm pr-field" data-field="planned_activities">{{ $task->planned_activities }}</textarea></td>
                          <td><textarea class="form-control form-control-sm pr-field" data-field="current_status">{{ $task->current_status }}</textarea></td>
                          <td><textarea class="form-control form-control-sm pr-field" data-field="next_steps">{{ $task->next_steps }}</textarea></td>
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
                      <tr><td colspan="{{ $canEdit ? 6 : 5 }}" class="text-center text-muted py-2">No tasks yet.</td></tr>
                    @endforelse
                  </tbody>
                </table>
              </div>
            </div>
            @if($canEdit)
              <div class="card-footer d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-sm btn-cosecsa-outline pr-add-row" data-participant-id="{{ $participant->id }}">
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
      </div>
    </section>
  </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
  const periodId = {{ $period->id }};
  const csrf = '{{ csrf_token() }}';

  function saveField(taskId, field, value, cardFooterFlash) {
    fetch(`{{ url('progressive-reports') }}/${periodId}/tasks/${taskId}/update`, {
      method: 'POST',
      headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
      body: JSON.stringify({ [field]: value }),
    }).then(r => r.json()).then(() => {
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
      saveField(taskId, field.dataset.field, field.value, flash);
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
      fetch(`{{ url('progressive-reports') }}/${periodId}/participants/${participantId}/tasks/add`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
      }).then(r => r.json()).then(data => {
        const tbody = document.querySelector(`tbody[data-participant-id="${participantId}"]`);
        const placeholder = tbody.querySelector('td[colspan]');
        if (placeholder) placeholder.closest('tr').remove();
        const tr = document.createElement('tr');
        tr.setAttribute('data-task-id', data.task.id);
        tr.innerHTML = `
          <td>${data.task.row_no}</td>
          <td><textarea class="form-control form-control-sm pr-field pr-activity" data-field="activity_description"></textarea></td>
          <td><textarea class="form-control form-control-sm pr-field" data-field="planned_activities"></textarea></td>
          <td><textarea class="form-control form-control-sm pr-field" data-field="current_status"></textarea></td>
          <td><textarea class="form-control form-control-sm pr-field" data-field="next_steps"></textarea></td>
          <td class="text-center"><a href="#" class="text-danger pr-delete-row" title="Delete row"><i class="fas fa-trash"></i></a></td>
        `;
        tbody.appendChild(tr);
      });
    });
  });
});
</script>
@endpush
