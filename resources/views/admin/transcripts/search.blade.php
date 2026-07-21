@extends('layout.app')

@section('content')
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-6">
            <h1 style="font-size:1.4rem;">Transcripts</h1>
            <p class="text-muted mb-0" style="font-size:.85rem;">
              Search for a fellow or trainee to issue or edit their transcript.
            </p>
          </div>
          @if(Auth::user()->hasPermission('transcripts.manage'))
          <div class="col-sm-6 text-right">
            <a href="{{ url('admin/settings/transcript-templates') }}" class="btn btn-outline-secondary">
              <i class="fas fa-file-alt mr-1"></i> Manage Templates
            </a>
          </div>
          @endif
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        @include('_message')
        <div class="card">
          <div class="card-body">
            <form method="get">
              <div class="form-row">
                <div class="form-group col-md-6">
                  <input type="text" name="q" value="{{ $q }}" class="form-control"
                         placeholder="Search by name, PEN, or candidate number…">
                </div>
                <div class="form-group col-md-2">
                  <button type="submit" class="btn btn-primary">Search</button>
                </div>
              </div>
            </form>

            @if($q)
              <div class="table-responsive">
                <table class="table table-striped table-sm">
                  <thead>
                    <tr><th>Name</th><th>Reference</th><th>Type</th><th>Action</th></tr>
                  </thead>
                  <tbody>
                    @foreach($results as $r)
                      <tr>
                        <td>{{ $r->name }}</td>
                        <td>{{ $r->ref }}</td>
                        <td>{{ $r->source }}</td>
                        <td>
                          <a href="{{ url('admin/transcripts/edit/'.$r->user_id) }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-file-signature mr-1"></i> Issue / Edit Transcript
                          </a>
                        </td>
                      </tr>
                    @endforeach
                    @if($results->isEmpty())
                      <tr><td colspan="4" class="text-center text-muted py-3">No matches found.</td></tr>
                    @endif
                  </tbody>
                </table>
              </div>
            @endif
          </div>
        </div>
      </div>
    </section>
  </div>
@endsection
