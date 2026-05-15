@extends('layout.app')

@section('content')
<div class="wrapper">
<div class="content-wrapper">

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-8">
                    <h4 class="mb-0">Upload Report — {{ $yearName }} Examiner Confirmation</h4>
                </div>
                <div class="col-sm-4 text-right">
                    <a href="{{ route('exams.upload.confirmation') }}" class="btn btn-sm btn-warning mr-1">
                        <i class="fas fa-upload mr-1"></i> Upload Another
                    </a>
                    <a href="{{ url('admin/exams/examiners') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Examiners
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            {{-- ── Summary cards ── --}}
            <div class="row mb-3">
                <div class="col-6 col-md-3">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ count($results['updated']) }}</h3>
                            <p>Updated</p>
                        </div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="small-box" style="background:#856404; color:#fff;">
                        <div class="inner">
                            <h3>{{ count($results['already_exists']) }}</h3>
                            <p>Already Exists</p>
                        </div>
                        <div class="icon"><i class="fas fa-clone"></i></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ count($results['duplicate_file']) }}</h3>
                            <p>Duplicate in File</p>
                        </div>
                        <div class="icon"><i class="fas fa-copy"></i></div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ count($results['not_found']) + count($results['error']) }}</h3>
                            <p>Not Found / Errors</p>
                        </div>
                        <div class="icon"><i class="fas fa-times-circle"></i></div>
                    </div>
                </div>
            </div>

            {{-- ── Detail tables ── --}}

            @php
                $sections = [
                    ['key' => 'updated',        'label' => 'Updated',           'icon' => 'check-circle',       'color' => '#28a745'],
                    ['key' => 'already_exists', 'label' => 'Already Exists',    'icon' => 'clone',              'color' => '#856404'],
                    ['key' => 'duplicate_file', 'label' => 'Duplicate in File', 'icon' => 'copy',               'color' => '#17a2b8'],
                    ['key' => 'not_found',      'label' => 'Not Found',         'icon' => 'user-slash',         'color' => '#dc3545'],
                    ['key' => 'error',          'label' => 'Errors',            'icon' => 'exclamation-triangle','color' => '#856404'],
                ];
            @endphp

            @foreach($sections as $sec)
                @if(count($results[$sec['key']]) > 0)
                <div class="card mb-3">
                    <div class="card-header d-flex align-items-center" style="background:{{ $sec['color'] }}; color:#fff;">
                        <i class="fas fa-{{ $sec['icon'] }} mr-2"></i>
                        <strong>{{ $sec['label'] }}</strong>
                        <span class="badge badge-light ml-2">{{ count($results[$sec['key']]) }}</span>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name in File</th>
                                    @if($sec['key'] === 'updated' || $sec['key'] === 'already_exists' || $sec['key'] === 'not_found')
                                        <th>Matched As</th>
                                    @endif
                                    <th>Email</th>
                                    <th>Specialty</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($results[$sec['key']] as $i => $row)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $row['name'] }}</td>
                                    @if($sec['key'] === 'updated' || $sec['key'] === 'already_exists' || $sec['key'] === 'not_found')
                                        <td>
                                            @if(isset($row['matched_name']))
                                                <span class="text-success"><i class="fas fa-check mr-1"></i>{{ $row['matched_name'] }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td><small>{{ $row['email'] }}</small></td>
                                    <td><small>{{ $row['specialty'] }}</small></td>
                                    <td><small class="text-muted">{{ $row['reason'] ?? '' }}</small></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            @endforeach

        </div>
    </section>
</div>
</div>
@endsection

@push('styles')
<style>
.small-box .icon { font-size: 70px; top: 10px; }
.small-box p { font-size: 14px; }
</style>
@endpush
