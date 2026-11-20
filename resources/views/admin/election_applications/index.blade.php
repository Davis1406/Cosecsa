@extends('layout.app')

@section('title', 'Fellowship by Election')

@push('styles')
<style>
    .fe-hero { background:linear-gradient(135deg,#a02626 0%,#7a1f1f 100%); border-radius:10px; padding:20px 24px; color:#fff; margin-bottom:1.2rem; }
    .fe-hero h4 { font-weight:700; margin:0 0 4px; }
    .fe-tile { display:block; background:#fff; border:1px solid #e9ecef; border-radius:8px; padding:12px 16px; margin-bottom:16px; text-align:center; color:inherit !important; text-decoration:none !important; }
    body.dark-mode .fe-tile { background:#374151; border-color:#4a5568; }
    .fe-tile.active { outline:3px solid #FEC503; outline-offset:2px; }
    .fe-tile .n { font-size:26px; font-weight:700; line-height:1.1; }
    .fe-tile .l { font-size:12px; text-transform:uppercase; letter-spacing:.03em; opacity:.75; }
    .fe-filter { background:#fff; border:1px solid #e9ecef; border-radius:8px; padding:12px 16px; margin-bottom:1.2rem; }
    body.dark-mode .fe-filter { background:#374151; border-color:#4a5568; }
    .fe-pill { display:inline-block; padding:2px 10px; border-radius:20px; font-weight:600; font-size:.75rem; white-space:nowrap; }
    .fe-pending  { background:#fff3cd; color:#856404; }
    .fe-approved { background:#d4edda; color:#155724; }
    .fe-rejected { background:#f8d7da; color:#721c24; }
    .fe-known    { background:#e2e3f3; color:#383d7a; }
    .fe-fellow   { background:#cce5ff; color:#004085; }
    .fe-note { font-size:.8rem; opacity:.8; }
</style>
@endpush

@section('content')
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header"></section>
        <div class="col-md-12">@include('_message')</div>

        <section class="content">
            <div class="container-wrapper">

                <div class="fe-hero d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <h4><i class="fas fa-vote-yea mr-2"></i>Fellowship by Election</h4>
                        <div style="font-size:.85rem;opacity:.85;">
                            Applications to the COSECSA Ordinary Fellow by Election form on Wufoo.
                            @if ($lastSync) Last synced {{ \Carbon\Carbon::parse($lastSync)->diffForHumans() }}. @endif
                        </div>
                    </div>
                    @if (Auth::user()->hasPermission('fellows.manage'))
                        <form method="POST" action="{{ route('admin.election.sync') }}" class="mt-2 mt-md-0">
                            @csrf
                            <button type="submit" class="btn btn-light btn-sm font-weight-bold" style="color:#a02626;" @if(! $configured) disabled title="The Wufoo API key has not been set on the server yet" @endif>
                                <i class="fas fa-sync-alt mr-1"></i>Sync with Wufoo
                            </button>
                        </form>
                    @endif
                </div>

                @unless ($configured)
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-1"></i>
                        Wufoo is not connected live yet (the API key has not been set on the server), so this list shows the entries that were loaded from an export.
                        Uploaded documents open once it is connected.
                    </div>
                @endunless

                <div class="row">
                    <div class="col-6 col-md-3"><a href="#" class="fe-tile" data-status=""><div class="n">{{ $counts['all'] }}</div><div class="l">All applications</div></a></div>
                    <div class="col-6 col-md-3"><a href="#" class="fe-tile" data-status="pending"><div class="n">{{ $counts['pending'] }}</div><div class="l">Pending</div></a></div>
                    <div class="col-6 col-md-3"><a href="#" class="fe-tile" data-status="approved"><div class="n">{{ $counts['approved'] }}</div><div class="l">Approved</div></a></div>
                    <div class="col-6 col-md-3"><a href="#" class="fe-tile" data-status="rejected"><div class="n">{{ $counts['rejected'] }}</div><div class="l">Rejected</div></a></div>
                </div>

                @php
                    $years = collect($applications)->pluck('year')->filter()->unique()->sortDesc()->values();
                @endphp
                <div class="fe-filter">
                    <div class="form-row">
                        <div class="col-md-3 mb-2 mb-md-0">
                            <label class="mb-0 small font-weight-bold">Year applied</label>
                            <select id="feYear" class="form-control form-control-sm">
                                <option value="">All years</option>
                                @foreach ($years as $y)
                                    <option value="{{ $y }}" @if ($loop->first) selected @endif>{{ $y }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <label class="mb-0 small font-weight-bold">Already in the system</label>
                            <select id="feKnown" class="form-control form-control-sm">
                                <option value="">Everyone</option>
                                <option value="new">Not in the system yet</option>
                                <option value="tentative">Already tentative</option>
                                <option value="fellow">Already a Fellow</option>
                            </select>
                        </div>
                        <div class="col-md-5 fe-note align-self-end">
                            An applicant who is already tentative is linked when approved, not added twice. Someone who is already a Fellow cannot be approved again.
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <table id="feTable" class="table table-bordered table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>Applied</th>
                                    <th>Applicant</th>
                                    <th>Country</th>
                                    <th>Organisation</th>
                                    <th>Specialty qualification</th>
                                    <th>In the system</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($applications as $a)
                                    @php
                                        $k = $a['known'] ?? null;
                                        $kind = ! $k ? 'new' : ($k['kind'] === 'fellow' ? 'fellow' : 'tentative');
                                    @endphp
                                    <tr data-year="{{ $a['year'] }}" data-status="{{ $a['status'] }}" data-known="{{ $kind }}">
                                        <td data-order="{{ $a['submitted_at'] }}">{{ $a['submitted_at'] ? \Carbon\Carbon::parse($a['submitted_at'])->format('d M Y') : '—' }}</td>
                                        <td>
                                            <a href="{{ route('admin.election.show', $a['id']) }}" class="font-weight-bold">{{ $a['name'] }}</a>
                                            @if ($a['times_applied'] > 1)<span class="fe-pill fe-known ml-1" title="Applied more than once (same email)">{{ $a['times_applied'] }} entries</span>@endif
                                            <div class="fe-note">{{ $a['email'] }}</div>
                                        </td>
                                        <td>{{ $a['country'] ?: '—' }}</td>
                                        <td>{{ $a['organisation'] ?: '—' }}</td>
                                        <td><span class="fe-note">{{ $a['specialty'] ?: '—' }}</span></td>
                                        <td>
                                            @if ($kind === 'fellow')
                                                <span class="fe-pill fe-fellow" title="Matched by {{ $k['by'] }}">Fellow: {{ $k['label'] }}</span>
                                            @elseif ($kind === 'tentative')
                                                <span class="fe-pill fe-known" title="Matched by {{ $k['by'] }}">Tentative: {{ $k['label'] }}</span>
                                            @else
                                                <span class="fe-note">—</span>
                                            @endif
                                        </td>
                                        <td><span class="fe-pill fe-{{ $a['status'] }}">{{ ucfirst($a['status']) }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    var status = '', table = $('#feTable').DataTable({ order: [[0, 'desc']], pageLength: 25, autoWidth: false });

    function pass(tr, withStatus) {
        var y = $('#feYear').val(), k = $('#feKnown').val();
        return (!withStatus || !status || tr.getAttribute('data-status') === status)
            && (!y || tr.getAttribute('data-year') === y)
            && (!k || tr.getAttribute('data-known') === k);
    }

    $.fn.dataTable.ext.search.push(function (settings, data, index) {
        return settings.nTable.id !== 'feTable' || pass(table.row(index).node(), true);
    });

    // The tiles count what the Year / "In the system" filters leave, so they always add up to the table.
    function refreshTiles() {
        var n = { '': 0, pending: 0, approved: 0, rejected: 0 };
        table.rows().every(function () {
            var tr = this.node();
            if (!pass(tr, false)) return;
            n['']++; n[tr.getAttribute('data-status')]++;
        });
        $('.fe-tile').each(function () { $(this).find('.n').text(n[$(this).data('status') || ''] || 0); });
    }

    function redraw() { refreshTiles(); table.draw(); }

    $('.fe-tile').on('click', function (e) {
        e.preventDefault();
        status = $(this).data('status') || '';
        $('.fe-tile').removeClass('active');
        $(this).addClass('active');
        table.draw();
    });
    $('.fe-tile[data-status=""]').addClass('active');
    $('#feYear, #feKnown').on('change', redraw);
    redraw();
});
</script>
@endpush
