@extends('layout.app')

@push('styles')
<style>
    .pl-hero { background:linear-gradient(135deg,#a02626 0%,#7a1f1f 100%); border-radius:14px; padding:26px 28px; color:#fff; margin-bottom:1.25rem; }
    .pl-hero h4 { font-weight:700; margin:0 0 4px; font-size:1.3rem; }
    .pl-hero .meta { font-size:.85rem; opacity:.85; }
    .pl-hero .btn-new { background:rgba(255,255,255,.15); color:#fff; border:1px solid rgba(255,255,255,.35); border-radius:8px; font-size:.83rem; font-weight:600; padding:.45rem 1.1rem; transition:background .15s,color .15s; }
    .pl-hero .btn-new:hover { background:#fff; color:#a02626; }

    .pl-search-wrap { position:relative; margin-bottom:1rem; max-width:340px; }
    .pl-search-wrap i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#aaa; font-size:.85rem; }
    .pl-search-wrap input { padding-left:36px; border-radius:8px; font-size:.85rem; border:1px solid #e5e0e0; }
    .pl-search-wrap input:focus { border-color:#c98d8d; box-shadow:0 0 0 3px rgba(160,38,38,.08); }

    .pl-list { display:flex; flex-direction:column; gap:10px; }
    .pl-card { background:#fff; border:1px solid #f0f0f0; border-radius:12px; padding:16px 18px; display:flex; align-items:center; gap:16px; transition:box-shadow .15s,border-color .15s; }
    .pl-card:hover { box-shadow:0 3px 14px rgba(0,0,0,.06); border-color:#e8d5d5; }
    .pl-icon { flex-shrink:0; width:44px; height:44px; border-radius:10px; background:#fdeeee; color:#a02626; display:flex; align-items:center; justify-content:center; font-size:1.1rem; }
    .pl-body { flex:1; min-width:0; text-decoration:none; }
    .pl-name { font-weight:700; color:#222; font-size:.95rem; margin-bottom:2px; }
    .pl-sub { color:#888; font-size:.8rem; }
    .pl-sub .sep { margin:0 6px; color:#ddd; }
    .pl-fees { flex-shrink:0; display:flex; gap:18px; margin-right:6px; }
    .pl-fee { text-align:center; }
    .pl-fee .lbl { font-size:.62rem; color:#aaa; text-transform:uppercase; letter-spacing:.04em; }
    .pl-fee .val { font-size:.85rem; font-weight:700; color:#333; }
    .pl-actions { flex-shrink:0; display:flex; gap:6px; }
    .pl-actions .btn { width:34px; height:34px; padding:0; display:inline-flex; align-items:center; justify-content:center; border-radius:8px; font-size:.85rem; }
    .pl-actions .btn-edit { background:#fbf7f7; color:#a02626; border:none; }
    .pl-actions .btn-edit:hover { background:#f1e0e0; color:#a02626; }
    .pl-actions .btn-del { background:#fff; color:#c9a0a0; border:1px solid #f0e0e0; }
    .pl-actions .btn-del:hover { background:#fdeeee; color:#a02626; border-color:#e8d5d5; }

    .empty-state { text-align:center; padding:48px 20px; color:#aaa; font-size:.9rem; }
    .empty-state i { font-size:2.4rem; display:block; margin-bottom:10px; color:#e0c5c5; }

    body.dark-mode .pl-card { background:#1f2937; border-color:#374151; }
    body.dark-mode .pl-card:hover { border-color:#7a1f1f; }
    body.dark-mode .pl-icon { background:#3a1f1f; color:#f87171; }
    body.dark-mode .pl-name { color:#e5e7eb; }
    body.dark-mode .pl-sub { color:#9ca3af; }
    body.dark-mode .pl-fee .lbl { color:#6b7280; }
    body.dark-mode .pl-fee .val { color:#e5e7eb; }
    body.dark-mode .pl-actions .btn-edit { background:#374151; color:#f87171; }
    body.dark-mode .pl-actions .btn-edit:hover { background:#4b2626; }
    body.dark-mode .pl-actions .btn-del { background:#1f2937; color:#6b7280; border-color:#374151; }
    body.dark-mode .pl-actions .btn-del:hover { background:#3a1f1f; color:#f87171; border-color:#4b2626; }
    body.dark-mode .pl-search-wrap input { background:#1f2937; border-color:#374151; color:#e5e7eb; }
</style>
@endpush

@section('content')
<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <div class="col-md-12 p-0">@include('_message')</div>

            {{-- Hero header --}}
            <div class="pl-hero">
                <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:.75rem;">
                    <div>
                        <h4><i class="fas fa-stethoscope mr-2"></i>Programmes</h4>
                        <div class="meta">{{ $getRecord->count() }} training programme{{ $getRecord->count() === 1 ? '' : 's' }} on file</div>
                    </div>
                    <a href="{{ url('admin/programmes/add_programmes') }}" class="btn btn-new">
                        <i class="fas fa-plus mr-1"></i> Add New Programme
                    </a>
                </div>
            </div>

            @if($getRecord->count())
            <div class="pl-search-wrap">
                <i class="fas fa-search"></i>
                <input type="text" id="plSearch" class="form-control" placeholder="Search programmes...">
            </div>
            @endif

            <div class="pl-list" id="plList">
                @forelse($getRecord as $programme)
                <div class="pl-card" data-search="{{ strtolower($programme->name.' '.$programme->programme_type) }}">
                    <div class="pl-icon"><i class="fas fa-stethoscope"></i></div>
                    <a href="{{ url('admin/programmes/view/'.$programme->id) }}" class="pl-body">
                        <div class="pl-name">{{ $programme->name }}</div>
                        <div class="pl-sub">
                            {{ $programme->programme_type ?: '—' }}
                            @if($programme->duration)<span class="sep">·</span>{{ $programme->duration }} Years @endif
                        </div>
                    </a>
                    <div class="pl-fees d-none d-md-flex">
                        <div class="pl-fee">
                            <div class="lbl">Entry</div>
                            <div class="val">{{ $programme->entry_fee ? number_format($programme->entry_fee) : '-' }}</div>
                        </div>
                        <div class="pl-fee">
                            <div class="lbl">Exam</div>
                            <div class="val">{{ $programme->exam_fee ? number_format($programme->exam_fee) : '-' }}</div>
                        </div>
                        <div class="pl-fee">
                            <div class="lbl">Repeat</div>
                            <div class="val">{{ $programme->repeat_fee ? number_format($programme->repeat_fee) : '-' }}</div>
                        </div>
                    </div>
                    <div class="pl-actions">
                        <a href="{{ url('admin/programmes/edit_programmes/'.$programme->id) }}" class="btn btn-edit" title="Edit">
                            <i class="fas fa-pen"></i>
                        </a>
                        @if(Auth::user()->isSuperAdmin())
                        <a href="{{ url('admin/programmes/delete/'.$programme->id) }}" class="btn btn-del" title="Delete"
                           onclick="return confirm('Delete {{ $programme->name }}? This cannot be undone.');">
                            <i class="fas fa-trash"></i>
                        </a>
                        @endif
                    </div>
                </div>
                @empty
                <div class="empty-state"><i class="fas fa-stethoscope"></i>No programmes yet.</div>
                @endforelse
            </div>

        </div>
    </section>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('plSearch');
    if (!input) return;
    input.addEventListener('input', function () {
        var q = this.value.trim().toLowerCase();
        document.querySelectorAll('#plList .pl-card').forEach(function (card) {
            card.style.display = card.dataset.search.includes(q) ? '' : 'none';
        });
    });
});
</script>
@endpush
@endsection
