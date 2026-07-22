{{-- Usage: @include('progressive_reports._bulleted', ['text' => $task->planned_activities]) --}}
@php
  $__bulletLines = array_values(array_filter(preg_split('/\r\n|\r|\n/', (string) ($text ?? '')), fn ($l) => trim($l) !== ''));
@endphp
@if(count($__bulletLines))
  <ul class="pr-bullets">
    @foreach($__bulletLines as $__line)
      <li>{{ $__line }}</li>
    @endforeach
  </ul>
@endif
