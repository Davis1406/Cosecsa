@extends('layout.app')

@section('content')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h4 class="mb-0">Draft Emails</h4>
                    <small class="text-muted">Reusable email drafts you can create and edit here, then copy into a send flow.</small>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ url('admin/draft-emails/add') }}" class="btn btn-danger">
                        <i class="fas fa-plus mr-1"></i> New Draft Email
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    @include('_message')

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">All Drafts</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Name</th>
                                            <th>Subject</th>
                                            <th>Last Updated</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($draftEmails as $i => $d)
                                        <tr>
                                            <td>{{ $i+1 }}</td>
                                            <td>{{ $d->name }}</td>
                                            <td>{{ $d->subject }}</td>
                                            <td>{{ $d->updated_at ? \Carbon\Carbon::parse($d->updated_at)->format('d M Y, H:i') : '-' }}</td>
                                            <td>
                                                <a href="{{ url('admin/draft-emails/edit/'.$d->id) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <a href="{{ url('admin/draft-emails/delete/'.$d->id) }}" class="btn btn-sm btn-danger"
                                                   onclick="return confirm('Delete the draft \'{{ $d->name }}\'? This cannot be undone.');">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">
                                                No draft emails yet. <a href="{{ url('admin/draft-emails/add') }}">Create the first one.</a>
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@endsection
