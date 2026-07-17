@extends('layout.app')

@section('title', 'Application Detail')

@push('styles')
<style>
    .sf-hero { background:linear-gradient(135deg,#a02626 0%,#7a1f1f 100%); border-radius:10px;
               padding:20px 24px; color:#fff; margin-bottom:1.2rem; }
    .detail-card { background:#fff; border-radius:8px; padding:20px; box-shadow:0 1px 6px rgba(0,0,0,.08); margin-bottom:20px; }
    body.dark-mode .detail-card { background:#374151; }
    .detail-card h6 { color:#a02626; font-weight:700; text-transform:uppercase; font-size:.78rem; letter-spacing:.04em; margin-bottom:14px; border-bottom:1px solid #eee; padding-bottom:8px; }
    .dl-row { display:flex; padding:6px 0; font-size:.9rem; border-bottom:1px solid #f5f5f5; }
    body.dark-mode .dl-row { border-color:#4a5568; }
    .dl-row .dl-label { width:180px; flex-shrink:0; color:#888; font-weight:600; }
    .dl-row .dl-value { flex:1; }
    .stage-pill { display:inline-block; padding:3px 12px; border-radius:20px; font-weight:600; font-size:.78rem; }
    .stage-received { background:#fff3cd; color:#856404; }
    .stage-complete  { background:#d4edda; color:#155724; }
    .stage-review    { background:#cce5ff; color:#004085; }
    .stage-rejected  { background:#f8d7da; color:#721c24; }
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
                        <h4 class="mb-0"><i class="fas fa-file-alt mr-2"></i>{{ $application->applicant_name ?: $application->name }}</h4>
                        <div style="font-size:.85rem;opacity:.85;">{{ $application->name }}</div>
                    </div>
                    <a href="{{ url('admin/salesforce') }}" class="btn btn-light btn-sm font-weight-bold" style="color:#a02626;">
                        <i class="fas fa-arrow-left mr-1"></i>Back to list
                    </a>
                </div>

                @php
                    $stageLower = strtolower($application->application_stage ?? '');
                    $pillClass = 'stage-default';
                    if (str_contains($stageLower, 'complete')) $pillClass = 'stage-complete';
                    elseif (str_contains($stageLower, 'received')) $pillClass = 'stage-received';
                    elseif (str_contains($stageLower, 'review') || str_contains($stageLower, 'pending')) $pillClass = 'stage-review';
                    elseif (str_contains($stageLower, 'reject') || str_contains($stageLower, 'withdrawn')) $pillClass = 'stage-rejected';
                @endphp

                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-card">
                            <h6>Applicant</h6>
                            <div class="dl-row"><div class="dl-label">Name</div><div class="dl-value">{{ $application->applicant_name ?: '—' }}</div></div>
                            <div class="dl-row"><div class="dl-label">Email</div><div class="dl-value">{{ $application->applicant_email ?: '—' }}</div></div>
                            <div class="dl-row"><div class="dl-label">Phone</div><div class="dl-value">{{ $application->applicant_phone ?: '—' }}</div></div>
                            <div class="dl-row"><div class="dl-label">Gender</div><div class="dl-value">{{ $application->applicant_gender ?: '—' }}</div></div>
                            <div class="dl-row"><div class="dl-label">Country</div><div class="dl-value">{{ $application->country ?: '—' }}</div></div>
                            <div class="dl-row"><div class="dl-label">Hospital / Organisation</div><div class="dl-value">{{ $application->hospital_name ?: '—' }}</div></div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="detail-card">
                            <h6>Application</h6>
                            <div class="dl-row"><div class="dl-label">Stage</div><div class="dl-value"><span class="stage-pill {{ $pillClass }}">{{ $application->application_stage ?: '—' }}</span></div></div>
                            <div class="dl-row"><div class="dl-label">Level</div><div class="dl-value">{{ $application->application_level ?: '—' }}</div></div>
                            <div class="dl-row"><div class="dl-label">Programme</div><div class="dl-value">{{ $application->programme_name ?: '—' }}</div></div>
                            <div class="dl-row"><div class="dl-label">PEN</div><div class="dl-value">{{ $pillClass === 'stage-complete' ? ($application->pen ?: '—') : '— (assigned once Complete)' }}</div></div>
                            <div class="dl-row"><div class="dl-label">Date of Application</div><div class="dl-value">{{ $application->date_of_application ? \Carbon\Carbon::parse($application->date_of_application)->format('d M Y') : '—' }}</div></div>
                            <div class="dl-row"><div class="dl-label">Intake Year</div><div class="dl-value">{{ $intakeYear ?: '—' }} @if($intakeYear)<small class="text-muted">(Jul {{ $intakeYear - 1 }} – Jun {{ $intakeYear }})</small>@endif</div></div>
                            <div class="dl-row"><div class="dl-label">Exam Year (Salesforce)</div><div class="dl-value">{{ $application->exam_year ?: '—' }}</div></div>
                            <div class="dl-row"><div class="dl-label">Received</div><div class="dl-value">{{ $application->application_received ? 'Yes' : 'No' }}</div></div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="detail-card">
                            <h6>Sync Info</h6>
                            <div class="dl-row"><div class="dl-label">Salesforce ID</div><div class="dl-value"><code>{{ $application->sf_id }}</code></div></div>
                            <div class="dl-row"><div class="dl-label">SF Created</div><div class="dl-value">{{ $application->sf_created_at ? \Carbon\Carbon::parse($application->sf_created_at)->format('d M Y H:i') : '—' }}</div></div>
                            <div class="dl-row"><div class="dl-label">SF Last Modified</div><div class="dl-value">{{ $application->sf_modified_at ? \Carbon\Carbon::parse($application->sf_modified_at)->format('d M Y H:i') : '—' }}</div></div>
                            <div class="dl-row"><div class="dl-label">Last Synced</div><div class="dl-value">{{ $application->synced_at ? \Carbon\Carbon::parse($application->synced_at)->diffForHumans() : '—' }}</div></div>
                            <div class="dl-row">
                                <div class="dl-label">Open in Salesforce</div>
                                <div class="dl-value">
                                    <a href="https://cosecsa2.lightning.force.com/lightning/r/Application__c/{{ $application->sf_id }}/view" target="_blank" rel="noopener">
                                        <i class="fas fa-external-link-alt mr-1"></i>View in Salesforce
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="detail-card">
                            <h6>Trainee Record</h6>
                            @if($trainee)
                                <div class="dl-row"><div class="dl-label">Status</div><div class="dl-value"><span class="badge badge-success">Linked</span></div></div>
                                <div class="dl-row"><div class="dl-label">Entry Number</div><div class="dl-value">{{ $trainee->entry_number }}</div></div>
                                <div class="dl-row"><div class="dl-label">Admission Year</div><div class="dl-value">{{ $trainee->admission_year }}</div></div>
                                <div class="dl-row"><div class="dl-label">Exam Year</div><div class="dl-value">{{ $trainee->exam_year }}</div></div>
                                <a href="{{ url('admin/associates/trainees/view/' . $trainee->id) }}" class="btn btn-sm btn-outline-danger mt-2">
                                    <i class="fas fa-user-graduate mr-1"></i>View Trainee Profile
                                </a>
                            @else
                                <p class="text-muted mb-0">No trainee record linked yet.
                                    @if($stageLower === 'complete')
                                        Run <a href="{{ url('admin/salesforce/populate-trainees') }}">Populate Trainees</a> to create one from Complete applications.
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </div>
</div>
@endsection
