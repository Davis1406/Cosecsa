@extends('layout.app')

@section('content')
<div class="wrapper">
<div class="content-wrapper">

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h4 class="mb-0"><i class="fas fa-gavel mr-2"></i> Additional Designation Options</h4>
                    <small class="text-muted">These values appear in the Additional Designation dropdown when editing an examiner.</small>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ url('admin/exams/examiners') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Examiners
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-md-7">

                    @include('_message')

                    {{-- Add new option --}}
                    <div class="card mb-4">
                        <div class="card-header" style="background:#f8f0f0;color:#a02626;font-weight:600;">
                            <i class="fas fa-plus mr-2"></i> Add New Designation
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.designations.store') }}" class="d-flex" style="gap:.5rem;">
                                @csrf
                                <input type="text" name="name" class="form-control"
                                       placeholder="e.g. Chief Examiner" maxlength="80" required
                                       style="flex:1;">
                                <button type="submit" class="btn btn-danger" style="white-space:nowrap;">
                                    <i class="fas fa-plus mr-1"></i> Add
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Existing options --}}
                    <div class="card">
                        <div class="card-header" style="background:#f8f0f0;color:#a02626;font-weight:600;">
                            <i class="fas fa-list mr-2"></i> Current Options
                            <span class="badge badge-secondary ml-2">{{ $options->count() }}</span>
                        </div>
                        <div class="card-body p-0">
                            @if($options->isEmpty())
                                <p class="text-muted text-center p-4 mb-0">No options yet. Add one above.</p>
                            @else
                                <table class="table table-sm table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th class="pl-3">#</th>
                                            <th>Designation Name</th>
                                            <th style="width:100px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($options as $opt)
                                        <tr>
                                            <td class="pl-3 text-muted">{{ $loop->iteration }}</td>
                                            <td>
                                                <span class="badge badge-pill"
                                                      style="background:#a02626;color:#fff;font-size:.8rem;padding:.3em .7em;">
                                                    {{ $opt->name }}
                                                </span>
                                            </td>
                                            <td class="text-right pr-3">
                                                <a href="{{ route('admin.designations.delete', $opt->id) }}"
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Delete \'{{ addslashes($opt->name) }}\'?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>

</div>
</div>
@endsection
