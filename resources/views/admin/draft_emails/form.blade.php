@extends('layout.app')

@section('content')
<div class="content-wrapper">

    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-8">
                    <h4 class="mb-0">{{ $draftEmail ? 'Edit Draft Email' : 'New Draft Email' }}</h4>
                    <small class="text-muted">
                        Use <code>[Name]</code> anywhere in the body to insert a recipient's name later.
                    </small>
                </div>
                <div class="col-sm-4 text-right">
                    <a href="{{ url('admin/draft-emails') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Draft Emails
                    </a>
                </div>
            </div>
        </div>
    </section>

    <div class="col-md-12">@include('_message')</div>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header" style="background:#a02626; color:#fff;">
                    <h3 class="card-title">
                        <i class="fas fa-edit mr-2"></i> {{ $draftEmail ? 'Edit Draft' : 'New Draft' }}
                    </h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ $draftEmail ? url('admin/draft-emails/edit/'.$draftEmail->id) : url('admin/draft-emails/add') }}">
                        @csrf

                        <div class="form-group">
                            <label class="font-weight-bold">Draft Name</label>
                            <input type="text" name="name" class="form-control"
                                   value="{{ old('name', $draftEmail->name ?? '') }}" required>
                            <small class="text-muted">An internal label to find this draft again (e.g. "Trainee Welcome Email").</small>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Subject Line</label>
                            <input type="text" name="subject" class="form-control"
                                   value="{{ old('subject', $draftEmail->subject ?? '') }}" required>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Email Body</label>
                            <textarea name="body" id="bodyEditor" class="form-control" rows="14">{{ old('body', $draftEmail->body ?? '') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-save mr-1"></i> Save Draft
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="{{ asset('plugins/summernote/summernote-bs4.min.css') }}">
<style>
    .note-editor.note-frame { border: 1px solid #ced4da; border-radius: .25rem; }
    .note-toolbar { background: #f8f9fa; border-bottom: 1px solid #dee2e6; }
    code { background:#f0f0f0; padding:1px 5px; border-radius:3px; font-size:.85em; color:#a02626; }
</style>
@endpush

@push('scripts')
<script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
<script>
$(function () {
    $('#bodyEditor').summernote({
        height: 340,
        toolbar: [
            ['style',  ['bold', 'italic', 'underline', 'clear']],
            ['font',   ['strikethrough']],
            ['para',   ['ul', 'ol', 'paragraph']],
            ['insert', ['link', 'hr']],
            ['view',   ['codeview']],
        ],
    });
});
</script>
@endpush
