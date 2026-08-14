@extends('layout.app')

@push('styles')
<style>
    .entity-table td, .entity-table th { vertical-align:middle; font-size:.875rem; }
    .entity-table thead th { background:#f8f0f0; color:#a02626; font-size:.75rem; text-transform:uppercase;
                             letter-spacing:.05em; border-bottom:2px solid #e8d5d5; }
    .entity-link { color:#a02626; font-weight:500; text-decoration:none; }
    .entity-link:hover { text-decoration:underline; }
    .fee-col { color:#555; }

    body.dark-mode .entity-table thead th { background:#374151 !important; color:#f87171 !important; border-bottom-color:#4a5568 !important; }
    body.dark-mode .entity-table td, body.dark-mode .entity-table th { border-color:#4a5568 !important; color:#e0e0e0 !important; }
    body.dark-mode .fee-col { color:#9ca3af !important; }
</style>
@endpush

@section('content')
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header"></section>
        <div class="col-md-12">@include('_message')</div>

        <section class="content">
            <div class="container-wrapper">

                {{-- ── Header bar ── --}}
                <div class="d-flex align-items-center justify-content-between flex-wrap mb-3" style="gap:.5rem;">
                    <h5 class="mb-0 font-weight-bold" style="color:#a02626;">
                        <i class="fas fa-stethoscope mr-2"></i>Programmes
                        <span class="badge badge-secondary ml-1" style="font-size:.75rem;">{{ $getRecord->count() }}</span>
                    </h5>
                    <a href="{{ url('admin/programmes/add_programmes') }}" class="btn btn-sm" style="background:#a02626;border-color:#a02626;color:#fff;">
                        <i class="fas fa-plus mr-1"></i> Add New Programme
                    </a>
                </div>

                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped entity-table mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Programme Name</th>
                                        <th>Type</th>
                                        <th>Duration</th>
                                        <th>Entry Fee</th>
                                        <th>Exam Fee</th>
                                        <th>Repeat Fee</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($getRecord as $programme)
                                    <tr>
                                        <td>{{ $programme->id }}</td>
                                        <td>
                                            <a href="{{ url('admin/programmes/view/'.$programme->id) }}" class="entity-link">
                                                {{ $programme->name }}
                                            </a>
                                        </td>
                                        <td>{{ $programme->programme_type ?: '-' }}</td>
                                        <td>{{ $programme->duration ? $programme->duration.' Years' : '-' }}</td>
                                        <td class="fee-col">{{ $programme->entry_fee ? number_format($programme->entry_fee) : '-' }}</td>
                                        <td class="fee-col">{{ $programme->exam_fee ? number_format($programme->exam_fee) : '-' }}</td>
                                        <td class="fee-col">{{ $programme->repeat_fee ? number_format($programme->repeat_fee) : '-' }}</td>
                                        <td>
                                            <a href="{{ url('admin/programmes/edit_programmes/'.$programme->id) }}" class="btn btn-xs" style="background:#a02626;border-color:#a02626;color:#fff;">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            @if(Auth::user()->isSuperAdmin())
                                            <a href="{{ url('admin/programmes/delete/'.$programme->id) }}" class="btn btn-xs btn-outline-danger"
                                               onclick="return confirm('Delete {{ $programme->name }}? This cannot be undone.');">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>
</div>
@endsection
