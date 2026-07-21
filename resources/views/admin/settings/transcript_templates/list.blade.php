@extends('layout.app')

@section('content')
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 style="font-size:1.4rem;">Transcript Templates</h1>
          </div>
          <div class="col-sm-6 text-right">
            <a href="{{ url('admin/settings/transcript-templates/add') }}" class="btn btn-primary">Add New Template</a>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        @include('_message')
        <div class="card">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-sm">
                <thead>
                  <tr><th>Name</th><th>Document Title</th><th>Signatory</th><th>Default</th><th>Action</th></tr>
                </thead>
                <tbody>
                  @foreach($templates as $t)
                    <tr>
                      <td>{{ $t->name }}</td>
                      <td>{{ $t->document_title }}</td>
                      <td>{{ $t->signatory_name }} — {{ $t->signatory_title }}</td>
                      <td>@if($t->is_default)<span class="badge badge-success">Default</span>@endif</td>
                      <td>
                        <a href="{{ url('admin/settings/transcript-templates/edit/'.$t->id) }}" class="btn btn-sm btn-primary">Edit</a>
                        @if(Auth::user()->isSuperAdmin())
                          <a href="{{ url('admin/settings/transcript-templates/delete/'.$t->id) }}" class="btn btn-sm btn-danger"
                             onclick="return confirm('Delete this template?')">Delete</a>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                  @if($templates->isEmpty())
                    <tr><td colspan="5" class="text-center text-muted py-3">No templates yet.</td></tr>
                  @endif
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
@endsection
