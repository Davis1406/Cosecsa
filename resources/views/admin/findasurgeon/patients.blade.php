@extends('layout.app')

@section('title', 'Find A Surgeon · Patients')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid"><h1 style="font-size:1.4rem;">Find A Surgeon · Patients</h1></div>
    </section>

    <section class="content">
        <div class="container-fluid">
            @include('_message')
            @include('admin.findasurgeon._tabs')

            <div class="card">
                <div class="card-body">
                    <form method="get" class="form-inline mb-3">
                        <input type="text" name="q" value="{{ request('q') }}" class="form-control mr-2 mb-2" placeholder="Name, email or phone…" style="max-width:260px;">
                        <select name="status" class="form-control mr-2 mb-2">
                            <option value="">All accounts</option>
                            <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                            <option value="unverified" {{ request('status') === 'unverified' ? 'selected' : '' }}>Not verified</option>
                        </select>
                        <div class="custom-control custom-checkbox mr-3 mb-2">
                            <input type="checkbox" class="custom-control-input" id="wf" name="with_favourites" value="1" {{ request('with_favourites') ? 'checked' : '' }}>
                            <label class="custom-control-label" for="wf">Has saved surgeons</label>
                        </div>
                        <button class="btn btn-sm btn-dark mr-2 mb-2" type="submit">Filter</button>
                        <a href="{{ route('admin.findasurgeon.patients') }}" class="btn btn-sm btn-outline-secondary mb-2">Reset</a>
                        <span class="ml-auto fas-muted mb-2">{{ number_format($patients->total()) }} patient{{ $patients->total() == 1 ? '' : 's' }}</span>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead><tr>
                                <th>Patient</th><th>Email</th><th>Phone</th><th>Status</th>
                                <th class="text-center">Saved</th><th>Joined</th><th>Last login</th><th>Last active</th><th></th>
                            </tr></thead>
                            <tbody>
                            @forelse ($patients as $p)
                                <tr>
                                    <td><strong>{{ $p->full_name }}</strong></td>
                                    <td>{{ $p->email ?: '—' }}</td>
                                    <td>{{ $p->phone ?: '—' }}</td>
                                    <td>
                                        @if ($p->verified) <span class="badge badge-success">Verified</span>
                                        @else <span class="badge badge-secondary">Not verified</span> @endif
                                    </td>
                                    <td class="text-center">{{ $p->favourites_count }}</td>
                                    <td>{{ \Carbon\Carbon::parse($p->created_at)->format('d M Y') }}</td>
                                    <td>{{ $p->app_last_login_at ? \Carbon\Carbon::parse($p->app_last_login_at)->format('d M Y H:i') : '—' }}</td>
                                    <td>{{ $p->last_active ? \Carbon\Carbon::parse($p->last_active)->diffForHumans() : '—' }}</td>
                                    <td class="text-right"><button type="button" class="btn btn-xs btn-outline-dark js-patient" data-id="{{ $p->id }}">Details</button></td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center fas-muted py-4">No patients match.</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">{!! $patients->links() !!}</div>
                    <p class="fas-muted mt-3 mb-0" style="font-size:.8rem;"><i class="fas fa-lock mr-1"></i>Patient contact details are personal data: use them for support only.</p>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="patientModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="pmTitle">Patient</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button></div>
            <div class="modal-body" id="pmBody"><p class="fas-muted">Loading…</p></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var base = @json(url('admin/find-a-surgeon/patients'));
    function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) { return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
    function when(s) { return s ? new Date(s.replace(' ', 'T')).toLocaleString() : '—'; }

    $(document).on('click', '.js-patient', function () {
        var id = $(this).data('id');
        $('#pmTitle').text('Patient'); $('#pmBody').html('<p class="fas-muted">Loading…</p>');
        $('#patientModal').modal('show');
        $.getJSON(base + '/' + id).done(function (r) {
            var p = r.patient;
            $('#pmTitle').text(p.full_name);
            var html = '<div class="row mb-3">'
                + '<div class="col-md-6"><div class="fas-muted">Email</div><div>' + esc(p.email || '—') + '</div></div>'
                + '<div class="col-md-6"><div class="fas-muted">Phone</div><div>' + esc(p.phone || '—') + '</div></div>'
                + '<div class="col-md-4 mt-2"><div class="fas-muted">Status</div><div>' + (p.verified ? '<span class="badge badge-success">Verified</span>' : '<span class="badge badge-secondary">Not verified</span>') + '</div></div>'
                + '<div class="col-md-4 mt-2"><div class="fas-muted">Joined</div><div>' + esc(when(p.created_at)) + '</div></div>'
                + '<div class="col-md-4 mt-2"><div class="fas-muted">Last login</div><div>' + esc(when(p.last_login)) + '</div></div>'
                + '<div class="col-md-4 mt-2"><div class="fas-muted">Last active</div><div>' + esc(when(p.last_active)) + '</div></div>'
                + '<div class="col-md-4 mt-2"><div class="fas-muted">Signed-in devices</div><div>' + esc(p.signed_in_devices) + '</div></div>'
                + '</div><h6>Saved surgeons (' + p.favourites.length + ')</h6>';
            if (!p.favourites.length) { html += '<p class="fas-muted">None saved.</p>'; }
            else {
                html += '<table class="table table-sm"><thead><tr><th>Surgeon</th><th>Specialty</th><th>Hospital</th><th>Country</th><th>Saved</th></tr></thead><tbody>';
                p.favourites.forEach(function (f) {
                    html += '<tr><td>' + esc(f.name) + '</td><td>' + esc(f.specialty || '—') + '</td><td>' + esc(f.hospital || '—') + '</td><td>' + esc(f.country || '—') + '</td><td>' + esc(when(f.saved_at)) + '</td></tr>';
                });
                html += '</tbody></table>';
            }
            $('#pmBody').html(html);
        }).fail(function () { $('#pmBody').html('<p class="text-danger">Could not load this patient.</p>'); });
    });
})();
</script>
@endpush
