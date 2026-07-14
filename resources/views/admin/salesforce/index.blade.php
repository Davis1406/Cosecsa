@extends('layout.app')

@section('title', 'Salesforce Application')

@push('styles')
<style>
    .sf-hero { background:linear-gradient(135deg,#a02626 0%,#7a1f1f 100%); border-radius:10px;
               padding:20px 24px; color:#fff; margin-bottom:1.2rem; }
    .sf-hero h4 { font-weight:700; margin:0 0 4px; }

    .filter-bar { background:#fff; border:1px solid #e9ecef; border-radius:8px;
                  padding:14px 16px; margin-bottom:1.2rem; }
    body.dark-mode .filter-bar { background:#374151; border-color:#4a5568; }

    .stat-chip { display:inline-flex; flex-direction:column; align-items:center;
                 background:#fff; border:1px solid #e9ecef; border-radius:8px;
                 padding:10px 18px; min-width:110px; text-align:center; }
    .stat-chip .lbl { font-size:.66rem; color:#999; text-transform:uppercase; letter-spacing:.04em; }
    .stat-chip .val { font-size:1.2rem; font-weight:700; color:#222; }
    body.dark-mode .stat-chip { background:#374151; border-color:#4a5568; }
    body.dark-mode .stat-chip .val { color:#e0e0e0; }

    .sf-table thead th { background:#f8f0f0; color:#a02626; font-size:.75rem;
                          text-transform:uppercase; letter-spacing:.04em; }
    .stage-pill { display:inline-block; padding:3px 12px; border-radius:20px; font-weight:600; font-size:.78rem; }
    .stage-received { background:#fff3cd; color:#856404; }
    .stage-complete  { background:#d4edda; color:#155724; }
    .stage-review    { background:#cce5ff; color:#004085; }
    .stage-default   { background:#e9ecef; color:#495057; }
</style>
@endpush

@section('content')
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header"></section>
        <div class="col-md-12">@include('_message')</div>

        <section class="content">
            <div class="container-wrapper">

                <div class="sf-hero d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h4><i class="fas fa-cloud mr-2"></i>Salesforce Application</h4>
                        <div style="font-size:.85rem;opacity:.85;">
                            Applications received from the COSECSA Salesforce CRM (cosecsa2.lightning.force.com)
                        </div>
                    </div>
                    <form method="POST" action="{{ route('admin.salesforce.sync') }}">
                        @csrf
                        <button type="submit" class="btn btn-light btn-sm font-weight-bold" style="color:#a02626;">
                            <i class="fas fa-sync-alt mr-1"></i>Sync Now
                        </button>
                    </form>
                </div>

                <div class="d-flex flex-wrap mb-3" style="gap:.75rem;">
                    <div class="stat-chip">
                        <span class="lbl">Total</span>
                        <span class="val">{{ number_format($total) }}</span>
                    </div>
                    <div class="stat-chip">
                        <span class="lbl">Last Synced</span>
                        <span class="val" style="font-size:.85rem;">
                            {{ $lastSync?->synced_at ? \Carbon\Carbon::parse($lastSync->synced_at)->diffForHumans() : 'Never' }}
                        </span>
                    </div>
                    @if($lastSync)
                    <div class="stat-chip">
                        <span class="lbl">Last Sync Count</span>
                        <span class="val">{{ number_format($lastSync->records_synced ?? 0) }}</span>
                    </div>
                    @endif
                </div>

                {{-- Filters --}}
                <div class="filter-bar">
                    <form method="GET" action="{{ url('admin/salesforce') }}"
                          class="d-flex flex-wrap align-items-end" style="gap:.75rem;">
                        <div style="flex:1;min-width:220px;">
                            <label class="d-block mb-1 small font-weight-bold text-muted">Search</label>
                            <input type="text" name="q" value="{{ $search }}" placeholder="Name, email, PEN..."
                                   class="form-control form-control-sm" onchange="this.form.submit()">
                        </div>
                        <div>
                            <label class="d-block mb-1 small font-weight-bold text-muted">Stage</label>
                            <select name="stage" class="form-control form-control-sm" style="width:200px;" onchange="this.form.submit()">
                                <option value="">All stages</option>
                                @foreach($stages as $s)
                                    <option value="{{ $s }}" {{ $stage == $s ? 'selected' : '' }}>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="d-block mb-1 small font-weight-bold text-muted">Exam Year</label>
                            <select name="exam_year" class="form-control form-control-sm" style="width:130px;" onchange="this.form.submit()">
                                <option value="">All years</option>
                                @foreach($years as $y)
                                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <a href="{{ url('admin/salesforce') }}" class="btn btn-outline-secondary btn-sm">Clear</a>
                        </div>
                    </form>
                </div>

                <div class="card">
                    <div class="card-body p-0">
                        <table class="table table-sm table-bordered table-striped sf-table mb-0">
                            <thead>
                                <tr>
                                    <th>Applicant</th>
                                    <th>Email</th>
                                    <th>Programme</th>
                                    <th>Level</th>
                                    <th>Country</th>
                                    <th class="text-center">Exam Year</th>
                                    <th>PEN</th>
                                    <th>Date Applied</th>
                                    <th class="text-center">Stage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($applications as $app)
                                @php
                                    $stageLower = strtolower($app->application_stage ?? '');
                                    $pillClass = 'stage-default';
                                    if (str_contains($stageLower, 'complete')) $pillClass = 'stage-complete';
                                    elseif (str_contains($stageLower, 'received')) $pillClass = 'stage-received';
                                    elseif (str_contains($stageLower, 'review')) $pillClass = 'stage-review';
                                @endphp
                                <tr>
                                    <td>{{ $app->applicant_name ?: '—' }}</td>
                                    <td>{{ $app->applicant_email ?: '—' }}</td>
                                    <td>{{ $app->programme_name ?: '—' }}</td>
                                    <td>{{ $app->application_level ?: '—' }}</td>
                                    <td>{{ $app->country ?: '—' }}</td>
                                    <td class="text-center">{{ $app->exam_year ?: '—' }}</td>
                                    <td>{{ $app->entry_number ?: '—' }}</td>
                                    <td>{{ $app->date_of_application ? \Carbon\Carbon::parse($app->date_of_application)->format('d M Y') : '—' }}</td>
                                    <td class="text-center">
                                        <span class="stage-pill {{ $pillClass }}">{{ $app->application_stage ?: '—' }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">
                                        No applications synced yet. Click <strong>Sync Now</strong> above to pull data from Salesforce.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-3">
                    {{ $applications->links() }}
                </div>

            </div>
        </section>
    </div>
</div>
@endsection
