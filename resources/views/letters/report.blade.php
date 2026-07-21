@extends('layout.app')

@section('content')
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2 align-items-center">
          <div class="col-sm-6"><h1 style="font-size:1.4rem;">Sent Letters Report</h1></div>
          <div class="col-sm-6 text-right">
            <a href="{{ url('admin/letters') }}" class="btn btn-cosecsa-outline"><i class="fas fa-arrow-left mr-1"></i> Back</a>
          </div>
        </div>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        @include('_message')

        <div class="card">
          <div class="card-body">
            <form method="GET" action="{{ url('admin/letters/report') }}" class="form-row align-items-end">
              <div class="form-group col-md-3">
                <label>Template</label>
                <select name="template_id" class="form-control">
                  <option value="">All</option>
                  @foreach($templates as $t)
                    <option value="{{ $t->id }}" {{ ($filters['template_id'] ?? '') == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                  @endforeach
                </select>
              </div>
              <div class="form-group col-md-2">
                <label>Status</label>
                <select name="status" class="form-control">
                  <option value="">All</option>
                  <option value="sent" {{ ($filters['status'] ?? '') == 'sent' ? 'selected' : '' }}>Sent</option>
                  <option value="failed" {{ ($filters['status'] ?? '') == 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
              </div>
              <div class="form-group col-md-3">
                <label>Search</label>
                <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="Name or email">
              </div>
              <div class="form-group col-md-2">
                <button type="submit" class="btn btn-cosecsa-outline">Filter</button>
              </div>
            </form>
          </div>
        </div>

        <div class="card">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-striped table-sm">
                <thead>
                  <tr><th>Recipient</th><th>Email</th><th>Template</th><th>Sent By</th><th>When</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                  @foreach($rows as $r)
                    <tr>
                      <td>{{ $r->recipient_name }}</td>
                      <td>{{ $r->recipient_email ?: '—' }}</td>
                      <td>{{ $r->template->name ?? '—' }}</td>
                      <td>{{ $r->dispatch->sender->name ?? '—' }}</td>
                      <td>{{ $r->sent_at?->format('d M Y, H:i') }}</td>
                      <td>
                        <span class="badge {{ $r->status === 'sent' ? 'badge-success' : 'badge-danger' }}">{{ ucfirst($r->status) }}</span>
                      </td>
                      <td>
                        @if($r->pdf_path)
                          <a href="{{ url('admin/letters/sent/'.$r->id.'/download') }}" class="btn btn-sm btn-cosecsa-outline">
                            <i class="fas fa-file-pdf mr-1"></i> PDF
                          </a>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                  @if($rows->isEmpty())
                    <tr><td colspan="7" class="text-center text-muted py-3">No letters found.</td></tr>
                  @endif
                </tbody>
              </table>
            </div>
          </div>
          <div class="card-footer">{{ $rows->links() }}</div>
        </div>
      </div>
    </section>
  </div>
@endsection
