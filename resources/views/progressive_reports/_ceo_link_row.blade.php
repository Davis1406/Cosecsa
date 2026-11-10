<div class="row-card" data-id="{{ $task->id }}">
  <div class="row-top"><span class="row-no">Row {{ $no }}</span><button type="button" class="btn-del">Delete row</button></div>
  <div class="grid">
    <div class="full"><label>Activity description</label><textarea class="act" data-field="activity_description" id="a{{ $task->id }}">{{ $task->activity_description }}</textarea></div>
    <div><label>Planned activities</label><textarea class="bul" data-field="planned_activities" id="p{{ $task->id }}">{{ $task->planned_activities }}</textarea></div>
    <div><label>Current status</label><textarea class="bul" data-field="current_status" id="c{{ $task->id }}">{{ $task->current_status }}</textarea></div>
    <div><label>Next steps &amp; time frame</label><textarea class="bul" data-field="next_steps" id="n{{ $task->id }}">{{ $task->next_steps }}</textarea></div>
  </div>
</div>
