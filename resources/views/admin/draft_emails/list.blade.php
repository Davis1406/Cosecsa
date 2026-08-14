@extends('layout.app')

@push('styles')
<style>
    .de-hero { background:linear-gradient(135deg, #a02626, #7a1f1f); border-radius:10px; padding:24px 26px; color:#fff; margin-bottom:1.2rem; }
    .de-hero h4 { font-weight:700; margin:0 0 4px; }
    .de-hero .meta { font-size:.85rem; opacity:.85; }
    .de-hero .btn-new { background:rgba(255,255,255,.15); color:#fff; border:1px solid rgba(255,255,255,.35); }
    .de-hero .btn-new:hover { background:#fff; color:#a02626; }

    .de-search-wrap { position:relative; margin-bottom:1rem; }
    .de-search-wrap i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#aaa; }
    .de-search-wrap input { padding-left:38px; border-radius:8px; }

    .de-list { display:flex; flex-direction:column; gap:10px; }
    .de-card {
        background:#fff; border:1px solid #eee; border-radius:10px; padding:16px 18px;
        display:flex; align-items:center; gap:16px; transition:box-shadow .15s, border-color .15s;
    }
    .de-card:hover { box-shadow:0 3px 14px rgba(0,0,0,.07); border-color:#e8d5d5; }
    .de-icon {
        flex-shrink:0; width:44px; height:44px; border-radius:10px; background:#fdeeee; color:#a02626;
        display:flex; align-items:center; justify-content:center; font-size:1.1rem;
    }
    .de-body { flex:1; min-width:0; }
    .de-name { font-weight:700; color:#222; font-size:.95rem; margin-bottom:2px; }
    .de-subject { color:#777; font-size:.83rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .de-meta { flex-shrink:0; text-align:right; font-size:.72rem; color:#aaa; margin-right:6px; white-space:nowrap; }
    .de-actions { flex-shrink:0; display:flex; gap:6px; }
    .de-actions .btn { width:34px; height:34px; padding:0; display:inline-flex; align-items:center; justify-content:center; border-radius:8px; }

    .empty-state { text-align:center; padding:48px 20px; color:#aaa; font-size:.9rem; }
    .empty-state i { font-size:2.4rem; display:block; margin-bottom:10px; color:#e0c5c5; }

    body.dark-mode .de-card { background:#1f2937; border-color:#374151; }
    body.dark-mode .de-card:hover { border-color:#7a1f1f; }
    body.dark-mode .de-icon { background:#3a1f1f; color:#f87171; }
    body.dark-mode .de-name { color:#e5e7eb; }
    body.dark-mode .de-subject { color:#9ca3af; }
    body.dark-mode .de-meta { color:#6b7280; }
</style>
@endpush

@section('content')

<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <div class="col-md-12 p-0">@include('_message')</div>

            {{-- Hero header --}}
            <div class="de-hero">
                <div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:.75rem;">
                    <div>
                        <h4><i class="fas fa-paper-plane mr-2"></i>Draft Emails</h4>
                        <div class="meta">Reusable, previewable email drafts — create once, copy into any send flow.</div>
                    </div>
                    <a href="{{ url('admin/draft-emails/add') }}" class="btn btn-new">
                        <i class="fas fa-plus mr-1"></i> New Draft Email
                    </a>
                </div>
            </div>

            @if($draftEmails->count())
            <div class="de-search-wrap">
                <i class="fas fa-search"></i>
                <input type="text" id="deSearch" class="form-control" placeholder="Search drafts by name or subject...">
            </div>
            @endif

            <div class="de-list" id="deList">
                @forelse($draftEmails as $d)
                <div class="de-card" data-search="{{ strtolower($d->name.' '.$d->subject) }}">
                    <div class="de-icon"><i class="fas fa-envelope-open-text"></i></div>
                    <a href="{{ url('admin/draft-emails/edit/'.$d->id) }}" class="de-body text-decoration-none">
                        <div class="de-name">{{ $d->name }}</div>
                        <div class="de-subject">{{ $d->subject }}</div>
                    </a>
                    <div class="de-meta">
                        Updated<br>{{ $d->updated_at ? \Carbon\Carbon::parse($d->updated_at)->diffForHumans() : '-' }}
                    </div>
                    <div class="de-actions">
                        <a href="{{ url('admin/draft-emails/edit/'.$d->id) }}" class="btn btn-light border" title="Edit">
                            <i class="fas fa-edit text-primary"></i>
                        </a>
                        <a href="{{ url('admin/draft-emails/delete/'.$d->id) }}" class="btn btn-light border" title="Delete"
                           onclick="return confirm('Delete the draft \'{{ $d->name }}\'? This cannot be undone.');">
                            <i class="fas fa-trash text-danger"></i>
                        </a>
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <i class="fas fa-envelope-open-text"></i>
                    No draft emails yet.
                    <div class="mt-2">
                        <a href="{{ url('admin/draft-emails/add') }}" class="btn btn-sm btn-danger">
                            <i class="fas fa-plus mr-1"></i> Create your first draft
                        </a>
                    </div>
                </div>
                @endforelse
            </div>
            <div class="empty-state d-none" id="deNoResults">
                <i class="fas fa-search"></i>
                No drafts match your search.
            </div>

        </div>
    </section>
</div>

@endsection

@push('scripts')
<script>
$('#deSearch').on('input', function () {
    var q = $(this).val().trim().toLowerCase();
    var visible = 0;
    $('#deList .de-card').each(function () {
        var match = $(this).data('search').toString().indexOf(q) !== -1;
        $(this).toggle(match);
        if (match) visible++;
    });
    $('#deNoResults').toggleClass('d-none', !(q && visible === 0));
});
</script>
@endpush
