<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Preview — COSECSA Secretariat Report — {{ $period->period_month->format('F Y') }}</title>
<style>
  body { font-family: Arial, Helvetica, sans-serif; font-size: 13px; margin: 40px; background: #f5f5f5; }
  .page { max-width: 1100px; margin: 0 auto; background: #fff; padding: 40px; box-shadow: 0 2px 8px rgba(0,0,0,.1); }
  h1 { text-align: center; font-size: 18px; margin-bottom: 4px; }
  .subtitle { text-align: center; font-size: 13px; color: #555; margin-bottom: 24px; }
  table { width: 100%; border-collapse: collapse; margin-top: 8px; }
  th, td { border: 1px solid #999; padding: 6px 8px; vertical-align: top; }
  th { background: #f1f1f1; font-weight: bold; font-size: 12px; }
  .section-header { background: #a02626; color: #fff; font-weight: bold; font-size: 12px; }
  .section-header td { border-color: #a02626; }
  .no-tasks { color: #888; font-style: italic; }
  .col-no { width: 3%; text-align: center; }
  .col-activity { width: 20%; }
  .col-planned { width: 23%; }
  .col-status { width: 26%; }
  .col-next { width: 23%; }
  ul.bullets { margin: 0; padding-left: 16px; }
  ul.bullets li { margin-bottom: 2px; }
  .blank-row td { height: 28px; }
  .actions { text-align: center; margin-bottom: 20px; }
  .actions a, .actions button { display: inline-block; padding: 8px 18px; background: #a02626; color: #fff; text-decoration: none; border-radius: 4px; border: none; font-size: 13px; cursor: pointer; margin: 0 4px; }
  .actions a.secondary, .actions button.secondary { background: #6c757d; }
</style>
</head>
<body>
  <div class="actions">
    <a href="{{ url('progressive-reports/'.$period->id.'/download-docx') }}" target="_blank">Download DOCX</a>
    <a href="{{ url('progressive-reports/'.$period->id) }}" class="secondary">Back to Report</a>
  </div>

  <div class="page">
    <h1>COSECSA SECRETARIAT MONTHLY REPORT</h1>
    <div class="subtitle">{{ strtoupper($period->period_month->format('F Y')) }}  -  Due {{ $period->due_date->format('d M Y') }}</div>

    <table>
      <thead>
        <tr>
          <th class="col-no">No</th>
          <th class="col-activity">Activity</th>
          <th class="col-planned">Planned Activities</th>
          <th class="col-status">Current Status</th>
          <th class="col-next">Next Steps</th>
        </tr>
      </thead>
      <tbody>
        @foreach($period->participants as $participant)
          <tr class="section-header">
            <td colspan="5">{{ $participant->section_label }}</td>
          </tr>
          <tr>
            <th class="col-no">No</th>
            <th class="col-activity">Activity</th>
            <th class="col-planned">Planned Activities</th>
            <th class="col-status">Current Status</th>
            <th class="col-next">Next Steps</th>
          </tr>
          @if($participant->tasks->isEmpty())
            @if($participant->section_label === 'CEO')
              @for($i = 1; $i <= 4; $i++)
                <tr class="blank-row">
                  <td class="col-no">{{ $i }}</td>
                  <td class="col-activity"></td>
                  <td class="col-planned"></td>
                  <td class="col-status"></td>
                  <td class="col-next"></td>
                </tr>
              @endfor
            @else
              <tr>
                <td colspan="5" class="no-tasks">No tasks recorded.</td>
              </tr>
            @endif
          @else
            @foreach($participant->tasks as $task)
              <tr>
                <td class="col-no">{{ $task->row_no }}</td>
                <td class="col-activity">{{ $task->activity_description }}</td>
                <td class="col-planned">
                  @if($task->planned_activities)
                    <ul class="bullets">
                      @foreach(explode("\n", $task->planned_activities) as $line)
                        @if(trim($line))
                          <li>{{ ltrim(trim($line), '❖ ') }}</li>
                        @endif
                      @endforeach
                    </ul>
                  @endif
                </td>
                <td class="col-status">
                  @if($task->current_status)
                    <ul class="bullets">
                      @foreach(explode("\n", $task->current_status) as $line)
                        @if(trim($line))
                          <li>{{ ltrim(trim($line), '❖ ') }}</li>
                        @endif
                      @endforeach
                    </ul>
                  @endif
                </td>
                <td class="col-next">
                  @if($task->next_steps)
                    <ul class="bullets">
                      @foreach(explode("\n", $task->next_steps) as $line)
                        @if(trim($line))
                          <li>{{ ltrim(trim($line), '❖ ') }}</li>
                        @endif
                      @endforeach
                    </ul>
                  @endif
                </td>
              </tr>
            @endforeach
          @endif
        @endforeach
      </tbody>
    </table>
  </div>
</body>
</html>
