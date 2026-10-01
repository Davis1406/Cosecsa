@extends('layout.app')

@section('content')
<div class="content-wrapper">
    @include('learning.admin._head', ['title' => 'Examiner Training', 'subtitle' => $course['title']])
    <section class="content">
        <div class="container-fluid lms">
            @include('learning.partials.flash')
            @include('learning.admin._tabs')

            <div class="card card-pad mb-3">
                <h5 style="font-size:16px; font-weight:700;">{{ $user['name'] }} — {{ $user['email'] }}</h5>
                <p class="muted" style="font-size:13px; margin-bottom:0;">Progress through "{{ $course['title'] }}"</p>
                <div class="progress-track mt-2"><div class="progress-fill" style="width: {{ $percent }}%;"></div></div>
            </div>

            <div class="card card-pad">
                <table class="tbl">
                    <thead>
                        <tr><th>Module</th><th>Type</th><th>Status</th><th>Score</th><th>Completed</th></tr>
                    </thead>
                    <tbody>
                        @foreach($modules as $module)
                            <tr>
                                <td>{{ $module['title'] }}</td>
                                <td><span class="badge-gray">{{ $module['type'] }}</span></td>
                                <td>
                                    @if($module['completed'])
                                        <span class="badge-green">Completed</span>
                                    @elseif($module['type'] === 'quiz' && $module['score'] !== null)
                                        <span class="badge-red">Failed — can retake</span>
                                    @else
                                        <span class="badge-gray">Pending</span>
                                    @endif
                                </td>
                                <td>
                                    @if($module['score'] !== null)
                                        {{ round($module['score']) }}% <span class="muted">({{ $module['attempts'] }} {{ \Illuminate\Support\Str::plural('attempt', $module['attempts']) }})</span>
                                    @else
                                        <span class="muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($module['completed_at'])
                                        {{ \Illuminate\Support\Carbon::parse($module['completed_at'])->timezone(config('app.timezone'))->format('M j, Y H:i') }}
                                    @else
                                        <span class="muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
@include('learning.partials.styles')
@include('learning.admin._styles')
@endpush
