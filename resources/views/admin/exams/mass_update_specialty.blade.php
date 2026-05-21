@extends('layout.app')

@section('content')
<div class="wrapper">
<div class="content-wrapper">

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <a href="{{ url('admin/exams/examiners') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Examiners
                    </a>
                </div>
                <div class="col-sm-6 text-right">
                    <h4 class="mb-0">Normalise Examiner Specialties</h4>
                </div>
            </div>
        </div>
    </section>

    <div class="col-md-12">@include('_message')</div>

    <section class="content">
        <div class="container-fluid">

            {{-- Info card --}}
            <div class="alert alert-info mb-4" style="font-size:.9rem;">
                <i class="fas fa-info-circle mr-2"></i>
                This tool normalises free-text specialty values to official COSECSA programme names.
                Rows highlighted in <span style="background:#fff3cd;padding:0 4px;border-radius:3px;">yellow</span>
                will be updated. Rows in grey are already correct or have no mapping defined.
                Any specialty not in the automatic mapping can be set manually in the <em>Override</em> column.
            </div>

            <form method="POST" action="{{ route('exams.mass.specialty.process') }}">
                @csrf

                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center"
                         style="background:#f8f0f0;color:#a02626;border-bottom:1px solid #f0dada;">
                        <span><i class="fas fa-magic mr-2"></i> Specialty Mapping Preview</span>
                        <button type="submit" class="btn btn-sm btn-danger"
                                onclick="return confirm('Apply all automatic mappings now?')">
                            <i class="fas fa-check mr-1"></i> Apply Mappings
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th style="width:40px;">#</th>
                                    <th>Current Value</th>
                                    <th style="width:60px;" class="text-center">Count</th>
                                    <th>Maps To (automatic)</th>
                                    <th>Override (manual)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($current as $i => $row)
                                @php
                                    $rowStyle = $row->needs_fix ? 'background:#fff9e6;' : '';
                                    $statusIcon = $row->needs_fix
                                        ? '<i class="fas fa-arrow-right text-warning"></i>'
                                        : '<i class="fas fa-check text-success"></i>';
                                @endphp
                                <tr style="{{ $rowStyle }}">
                                    <td class="text-muted text-center">{{ $i + 1 }}</td>
                                    <td>
                                        <code>{{ $row->specialty ?: '(empty)' }}</code>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-secondary">{{ $row->cnt }}</span>
                                    </td>
                                    <td>
                                        @if($row->needs_fix)
                                            {!! $statusIcon !!}
                                            <strong class="text-success ml-1">{{ $row->mapped_to }}</strong>
                                        @elseif($row->mapped_to && $row->mapped_to === $row->specialty)
                                            <i class="fas fa-check text-success"></i>
                                            <span class="text-muted ml-1">Already correct</span>
                                        @else
                                            <span class="text-muted">— no mapping —</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(!$row->needs_fix)
                                        <select name="overrides[{{ urlencode($row->specialty) }}]"
                                                class="form-control form-control-sm">
                                            <option value="">— No change —</option>
                                            @foreach($programmes as $prog)
                                                <option value="{{ $prog }}">{{ $prog }}</option>
                                            @endforeach
                                        </select>
                                        @else
                                            <span class="text-muted" style="font-size:.8rem;">
                                                Handled automatically
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-danger"
                                onclick="return confirm('Apply all mappings and any overrides you selected?')">
                            <i class="fas fa-magic mr-1"></i> Apply All Mappings
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </section>
</div>
</div>
@endsection
