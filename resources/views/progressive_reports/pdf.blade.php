<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  @page { margin: 40px 30px; }
  body { font-family: Arial, sans-serif; font-size: 9pt; color: #222; }
  h1 { font-size: 13pt; text-align: center; margin-bottom: 2px; }
  .subtitle { text-align: center; font-size: 9pt; color: #555; margin-bottom: 16px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
  th, td { border: 1px solid #999; padding: 4px 6px; text-align: left; vertical-align: top; }
  th { background: #f1f1f1; font-size: 8.5pt; }
  .section-header td { background: #a02626; color: #fff; font-weight: bold; font-size: 9.5pt; }
  .col-no { width: 3%; }
  .col-activity { width: 18%; }
  .col-planned { width: 26%; }
  .col-status { width: 27%; }
  .col-next { width: 26%; }
</style>
</head>
<body>
  <h1>COSECSA SECRETARIAT MONTHLY REPORT</h1>
  <div class="subtitle">{{ strtoupper($period->period_month->format('F Y')) }} &nbsp;•&nbsp; Due {{ $period->due_date->format('d M Y') }}</div>

  <table>
    <thead>
      <tr>
        <th class="col-no">No</th>
        <th class="col-activity">Activity Description</th>
        <th class="col-planned">Planned Activities</th>
        <th class="col-status">Current Status</th>
        <th class="col-next">Next Steps &amp; Time Frame</th>
      </tr>
    </thead>
    <tbody>
      @foreach($period->participants as $participant)
        <tr class="section-header">
          <td colspan="5">{{ $participant->section_label }}</td>
        </tr>
        @forelse($participant->tasks as $task)
          <tr>
            <td>{{ $task->row_no }}</td>
            <td>{{ $task->activity_description }}</td>
            <td style="white-space:pre-line;">{{ $task->planned_activities }}</td>
            <td style="white-space:pre-line;">{{ $task->current_status }}</td>
            <td style="white-space:pre-line;">{{ $task->next_steps }}</td>
          </tr>
        @empty
          <tr><td colspan="5" style="color:#888;">No tasks recorded.</td></tr>
        @endforelse
      @endforeach
    </tbody>
  </table>
</body>
</html>
