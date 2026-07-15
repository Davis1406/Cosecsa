@extends('layout.app')

@section('title', 'Populate Trainees from Applications')

@push('styles')
<style>
    .sf-hero { background:linear-gradient(135deg,#a02626 0%,#7a1f1f 100%); border-radius:10px;
               padding:20px 24px; color:#fff; margin-bottom:1.2rem; }
    .stat-chip { display:inline-flex; flex-direction:column; align-items:center;
                 background:#fff; border:1px solid #e9ecef; border-radius:8px;
                 padding:10px 18px; min-width:120px; text-align:center; }
    .stat-chip .lbl { font-size:.66rem; color:#999; text-transform:uppercase; letter-spacing:.04em; }
    .stat-chip .val { font-size:1.2rem; font-weight:700; color:#222; }
    .pt-table thead th { background:#f8f0f0; color:#a02626; font-size:.75rem; text-transform:uppercase; letter-spacing:.04em; }
    .scroll-table { max-height: 480px; overflow-y: auto; border: 1px solid #eee; border-radius: 6px; }
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
                        <h4><i class="fas fa-user-graduate mr-2"></i>Populate Trainees from Complete Applications</h4>
                        <div style="font-size:.85rem;opacity:.85;">
                            Preview only — nothing has been written yet. Review below, then confirm.
                        </div>
                    </div>
                    <a href="{{ url('admin/salesforce') }}" class="btn btn-light btn-sm font-weight-bold" style="color:#a02626;">
                        <i class="fas fa-arrow-left mr-1"></i>Back
                    </a>
                </div>

                <div class="d-flex flex-wrap mb-3" style="gap:.75rem;">
                    <div class="stat-chip"><span class="lbl">Ready to create</span><span class="val" style="color:#155724;">{{ count($ready) }}</span></div>
                    <div class="stat-chip"><span class="lbl">Already exist</span><span class="val" style="color:#856404;">{{ count($skipped) }}</span></div>
                    <div class="stat-chip"><span class="lbl">Unresolved</span><span class="val" style="color:#721c24;">{{ count($unresolved) }}</span></div>
                </div>

                @if(count($ready))
                <form method="POST" action="{{ route('admin.salesforce.populate-trainees.apply') }}"
                      onsubmit="return confirm('Create {{ count($ready) }} new trainee record(s)? This writes to the database.');">
                    @csrf
                    <button type="submit" class="btn btn-danger mb-3" style="background:#a02626;border-color:#a02626;">
                        <i class="fas fa-check mr-1"></i>Create {{ count($ready) }} Trainee(s)
                    </button>
                </form>
                @endif

                <h6 class="font-weight-bold" style="color:#155724;">Ready to create ({{ count($ready) }})</h6>
                <div class="scroll-table mb-4">
                    <table class="table table-sm table-bordered table-striped pt-table mb-0">
                        <thead><tr><th>Name</th><th>PEN</th><th>Programme</th><th>Country</th><th>Hospital</th><th>Exam Year</th><th>Admission Year</th></tr></thead>
                        <tbody>
                            @forelse($ready as $row)
                            <tr>
                                <td>{{ $row['app']->applicant_name }}</td>
                                <td>{{ $row['app']->pen }}</td>
                                <td>{{ $row['app']->programme_name }}</td>
                                <td>{{ $row['app']->country }}</td>
                                <td>{{ $row['app']->hospital_name }}</td>
                                <td>{{ $row['exam_year'] }} <small class="text-muted">({{ $row['exam_year_source'] }})</small></td>
                                <td>{{ $row['admission_year'] }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="7" class="text-center text-muted py-3">None</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <h6 class="font-weight-bold" style="color:#856404;">Already exist, skipped ({{ count($skipped) }})</h6>
                <div class="scroll-table mb-4">
                    <table class="table table-sm table-bordered table-striped pt-table mb-0">
                        <thead><tr><th>Name</th><th>PEN</th><th>Reason</th><th>Trainee</th></tr></thead>
                        <tbody>
                            @forelse($skipped as $row)
                            <tr>
                                <td>{{ $row['app']->applicant_name }}</td>
                                <td>{{ $row['app']->pen ?: $row['app']->entry_number }}</td>
                                <td>{{ $row['reason'] }}</td>
                                <td><a href="{{ url('admin/associates/trainees/view/' . $row['trainee_id']) }}">View #{{ $row['trainee_id'] }}</a></td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">None</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <h6 class="font-weight-bold" style="color:#721c24;">Unresolved — need manual review ({{ count($unresolved) }})</h6>
                <div class="scroll-table">
                    <table class="table table-sm table-bordered table-striped pt-table mb-0">
                        <thead><tr><th>Name</th><th>PEN</th><th>Problem</th><th></th></tr></thead>
                        <tbody>
                            @forelse($unresolved as $row)
                            <tr>
                                <td>{{ $row['app']->applicant_name }}</td>
                                <td>{{ $row['app']->pen ?: $row['app']->entry_number ?: '—' }}</td>
                                <td>{{ $row['reason'] }}</td>
                                <td><a href="{{ url('admin/salesforce/view/' . $row['app']->id) }}">View application</a></td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">None</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </section>
    </div>
</div>
@endsection
