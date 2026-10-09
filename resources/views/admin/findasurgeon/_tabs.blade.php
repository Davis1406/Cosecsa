@php($seg = Request::segment(3))
<style>
    .fas-tabs.nav-tabs .nav-link { color:#a02626 !important; }
    .fas-tabs.nav-tabs .nav-link.active { background-color:#a02626 !important; color:#fff !important; border-color:#a02626 !important; }
    .fas-tabs.nav-tabs .nav-link:hover { background-color:#FEC503 !important; color:#000 !important; border-color:#FEC503 !important; }
    .fas-muted { color:#6b7280; } body.dark-mode .fas-muted { color:#94a3b8; }
</style>
<ul class="nav nav-tabs fas-tabs mb-3">
    <li class="nav-item"><a class="nav-link {{ $seg === null ? 'active' : '' }}" href="{{ route('admin.findasurgeon.overview') }}"><i class="fas fa-chart-pie mr-1"></i> Overview &amp; Report</a></li>
    <li class="nav-item"><a class="nav-link {{ $seg === 'patients' ? 'active' : '' }}" href="{{ route('admin.findasurgeon.patients') }}"><i class="fas fa-users mr-1"></i> Patients</a></li>
    <li class="nav-item"><a class="nav-link {{ $seg === 'hospitals' ? 'active' : '' }}" href="{{ route('admin.findasurgeon.hospitals') }}"><i class="fas fa-hospital mr-1"></i> Hospital Review</a></li>
    <li class="nav-item"><a class="nav-link {{ $seg === 'changes' ? 'active' : '' }}" href="{{ route('admin.findasurgeon.changes') }}"><i class="fas fa-history mr-1"></i> Profile Changes</a></li>
</ul>
