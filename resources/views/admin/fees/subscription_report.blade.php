@extends('layout.app')

@section('title', 'Annual Subscription Report')

@php
    $multi    = count($selectedYears) > 1;
    // Tiles count fellow-year records, so with several years the % base is fellows × years.
    $total    = max(1, (int) ($summary['records'] ?? 0));
    $asc      = array_reverse($selectedYears);
    $isRange  = $multi && (int) end($asc) - (int) $asc[0] === count($asc) - 1;
    $yearLabel = !$multi ? $year : ($isRange ? $asc[0] . '–' . end($asc) : implode(', ', $asc));
    $keepYears = function () use ($selectedYears) {
        return collect($selectedYears)->map(fn ($y) => '<input type="hidden" name="years[]" value="' . e($y) . '">')->implode('');
    };
    $statusTiles = [
        'Paid'    => ['label' => 'Paid',      'key' => 'paid'],
        'Partial' => ['label' => 'Partial',   'key' => 'partial'],
        'Unpaid'  => ['label' => 'Unpaid',    'key' => 'unpaid'],
        'None'    => ['label' => 'No Record', 'key' => 'none'],
        'Waived'  => ['label' => 'Waived',    'key' => 'waived'],
    ];
    $activeStatus = $filters['status'] ?? '';
    $canManage = Auth::user()->hasPermission('fees.manage');
@endphp

@push('styles')
<style>
    /* ── Tokens ── */
    .sr-page { --sr-ink:#141d23; --sr-muted:#6b7280; --sr-faint:#9ca3af; --sr-line:#e5e9f0; --sr-card:#fff; --sr-soft:#f7f8fa;
               --sr-brand:#a02626;
               --st-Paid-bg:#e9f7ee;    --st-Paid-fg:#2e7d32;    --st-Paid-bd:#c3e9cf;
               --st-Partial-bg:#fff6dc; --st-Partial-fg:#8a6100; --st-Partial-bd:#f5e0a3;
               --st-Unpaid-bg:#fdeeee;  --st-Unpaid-fg:#c62828;  --st-Unpaid-bd:#f6caca;
               --st-None-bg:#f1f3f5;    --st-None-fg:#6b7280;    --st-None-bd:#e2e5e9;
               --st-Waived-bg:#e7f3f7;  --st-Waived-fg:#0c5f73;  --st-Waived-bd:#c3e2ea; }
    body.dark-mode .sr-page { --sr-ink:#f1f5f9; --sr-muted:#94a3b8; --sr-faint:#718096; --sr-line:#2d3748; --sr-card:#1e2330; --sr-soft:#252c3b;
               --sr-brand:#f48a8a;
               --st-Paid-bg:#17311f;    --st-Paid-fg:#7ddc97;    --st-Paid-bd:#25502f;
               --st-Partial-bg:#342a10; --st-Partial-fg:#f3cf6b; --st-Partial-bd:#54441a;
               --st-Unpaid-bg:#3a1c1f;  --st-Unpaid-fg:#f59a9a;  --st-Unpaid-bd:#5c2a2e;
               --st-None-bg:#2b3040;    --st-None-fg:#a0aec0;    --st-None-bd:#3b4254;
               --st-Waived-bg:#15303a;  --st-Waived-fg:#7cc9dd;  --st-Waived-bd:#20495a; }

    .sr-page { color:var(--sr-ink); }
    .sr-eyebrow { font-size:.68rem; font-weight:700; letter-spacing:.09em; text-transform:uppercase; color:var(--sr-faint); }

    /* ── Header ── */
    .sr-head { display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end; justify-content:space-between; margin:4px 0 18px; }
    .sr-head h4 { font-weight:700; margin:2px 0 2px; }
    .sr-head .sr-sub { color:var(--sr-muted); font-size:.86rem; }
    .sr-head .sr-actions { display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
    .sr-year { font-weight:700; border-radius:8px; min-width:96px; }
    .btn-sr { background:var(--sr-brand); border-color:var(--sr-brand); color:#fff; font-weight:600; border-radius:8px; }
    .btn-sr:hover { background:#870f0f; border-color:#870f0f; color:#FEC503; }
    body.dark-mode .btn-sr { background:#a02626; border-color:#a02626; }
    .btn-sr-ghost { border:1px solid var(--sr-line); background:var(--sr-card); color:var(--sr-ink); border-radius:8px; font-weight:600; }
    .btn-sr-ghost:hover { color:var(--sr-brand); }

    /* ── Cards ── */
    .sr-card { background:var(--sr-card); border:1px solid var(--sr-line); border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,.04); }

    /* Status tiles */
    .sr-tiles { display:grid; grid-template-columns:repeat(5, minmax(0,1fr)); gap:10px; }
    .sr-tile { display:flex; flex-direction:column; justify-content:center; text-align:center; padding:14px 8px; border-radius:12px;
               border:1px solid var(--sr-line); background:var(--sr-card); color:inherit; text-decoration:none !important; transition:transform .12s, box-shadow .12s; }
    .sr-tile:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(0,0,0,.07); }
    .sr-tile .n { font-size:1.55rem; font-weight:700; line-height:1.2; margin-top:4px; }
    .sr-tile .p { font-size:.74rem; color:var(--sr-faint); }
    @foreach(array_keys($statusTiles) as $st)
    .sr-tile.t-{{ $st }} .n { color:var(--st-{{ $st }}-fg); }
    .sr-tile.t-{{ $st }}.active { background:var(--st-{{ $st }}-bg); border-color:var(--st-{{ $st }}-bd); }
    @endforeach
    .sr-owing { display:flex; align-items:center; gap:10px; margin-top:10px; padding:10px 14px; border-radius:10px;
                background:var(--st-Unpaid-bg); border:1px solid var(--st-Unpaid-bd); color:var(--st-Unpaid-fg); font-size:.86rem; }
    .sr-owing strong { font-size:1rem; }
    .sr-dist { display:flex; height:6px; border-radius:99px; overflow:hidden; margin-top:10px; background:var(--sr-soft); }
    .sr-dist span { display:block; height:100%; }

    /* ── Filters + table ── */
    .sr-toolbar { display:flex; flex-wrap:wrap; gap:10px; align-items:center; padding:14px 16px; border-bottom:1px solid var(--sr-line); }
    .sr-toolbar .title { font-weight:700; margin-right:auto; }
    .sr-toolbar .count { background:var(--sr-soft); color:var(--sr-muted); border-radius:99px; padding:1px 9px; font-size:.78rem; margin-left:6px; font-weight:600; }
    .sr-toolbar .form-control { border-radius:8px; }
    .sr-search { position:relative; }
    .sr-search i { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--sr-faint); font-size:.8rem; }
    .sr-search input { padding-left:28px; width:220px; }

    #subscriptionReportTable { border-collapse:separate; border-spacing:0; }
    #subscriptionReportTable thead th { background:transparent; border-top:0; border-bottom:1px solid var(--sr-line); font-size:.68rem; font-weight:700;
                                        text-transform:uppercase; letter-spacing:.07em; color:var(--sr-faint); padding:10px 12px; white-space:nowrap; }
    #subscriptionReportTable tbody td { border-top:1px solid var(--sr-line); padding:10px 12px; vertical-align:middle; font-size:.88rem; }
    #subscriptionReportTable tbody tr { cursor:pointer; transition:background .1s; }
    #subscriptionReportTable tbody tr:hover { background:var(--sr-soft); }
    #subscriptionReportTable td.num, #subscriptionReportTable th.num { text-align:right; font-variant-numeric:tabular-nums; }
    #subscriptionReportTable th.num { padding-right:26px; }
    #subscriptionReportTable td.muted { color:var(--sr-faint); }
    .sr-wrap .dataTables_wrapper { padding:0 16px 14px; }
    .sr-wrap .dt-buttons { padding-top:12px; }
    .sr-wrap .dataTables_filter { display:none; }  /* server-side search box in toolbar */

    .sr-who { display:flex; align-items:center; gap:10px; min-width:200px; }
    .sr-avatar { flex:none; width:34px; height:34px; border-radius:50%; background:#f3e3e3; color:#a02626; font-weight:700; font-size:.78rem;
                 display:flex; align-items:center; justify-content:center; overflow:hidden; }
    .sr-avatar::before { content:attr(data-initials); }
    .sr-avatar.has-img::before { content:none; }
    .sr-avatar img { width:100%; height:100%; object-fit:cover; }
    body.dark-mode .sr-avatar { background:#3a2328; color:#f48a8a; }
    .sr-who .nm { font-weight:600; color:var(--sr-ink); line-height:1.2; }
    .sr-who .em { font-size:.78rem; color:var(--sr-faint); }
    .sr-go { color:var(--sr-faint); }
    tr:hover .sr-go { color:var(--sr-brand); }

    /* Status pill — shared by table + drawer */
    .sr-pill { display:inline-block; padding:2px 9px; border-radius:6px; font-size:.74rem; font-weight:700; white-space:nowrap;
               background:var(--st-None-bg); color:var(--st-None-fg); }
    @foreach(array_keys($statusTiles) as $st)
    .sr-pill.s-{{ $st }} { background:var(--st-{{ $st }}-bg); color:var(--st-{{ $st }}-fg); }
    @endforeach

    /* ── Drawer ── */
    .sr-backdrop { position:fixed; inset:0; background:rgba(15,20,30,.4); opacity:0; pointer-events:none; transition:opacity .2s; z-index:1060; }
    .sr-drawer { position:fixed; top:0; right:0; height:100vh; width:min(440px,100vw); background:var(--sr-card); z-index:1061;
                 box-shadow:-8px 0 30px rgba(0,0,0,.14); transform:translateX(100%); transition:transform .25s ease;
                 display:flex; flex-direction:column; color:var(--sr-ink); }
    body.dark-mode .sr-backdrop { background:rgba(0,0,0,.55); }
    .sr-open .sr-backdrop { opacity:1; pointer-events:auto; }
    .sr-open .sr-drawer { transform:none; }
    .sr-dh { display:flex; align-items:center; justify-content:space-between; padding:18px 22px; border-bottom:1px solid var(--sr-line); }
    .sr-dh h5 { margin:0; font-weight:700; font-size:1.1rem; }
    .sr-x { background:none; border:0; color:var(--sr-faint); font-size:1.3rem; line-height:1; padding:4px 6px; border-radius:6px; }
    .sr-x:hover { color:var(--sr-ink); background:var(--sr-soft); }
    .sr-db { flex:1; overflow-y:auto; padding:20px 22px 28px; }
    .sr-df { padding:14px 22px; border-top:1px solid var(--sr-line); display:flex; gap:8px; }
    .sr-df .btn { flex:1; }

    .sr-id { display:flex; align-items:center; gap:14px; margin-bottom:18px; }
    .sr-id .sr-avatar { width:54px; height:54px; font-size:1.05rem; }
    .sr-id .nm { font-weight:700; font-size:1.08rem; line-height:1.25; }
    .sr-id .meta { color:var(--sr-muted); font-size:.83rem; }

    .sr-stats { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:22px; }
    .sr-stat { border:1px solid var(--sr-line); background:var(--sr-soft); border-radius:12px; padding:12px 6px; text-align:center; }
    .sr-stat .v { font-size:1.12rem; font-weight:700; margin-top:3px; }
    @foreach(array_keys($statusTiles) as $st)
    .sr-stat.s-{{ $st }} { background:var(--st-{{ $st }}-bg); border-color:var(--st-{{ $st }}-bd); }
    .sr-stat.s-{{ $st }} .v { color:var(--st-{{ $st }}-fg); }
    @endforeach

    .sr-sec { margin:0 0 10px; }
    .sr-contact { margin-bottom:22px; }
    .sr-contact div { display:flex; align-items:center; gap:14px; padding:11px 0; border-bottom:1px solid var(--sr-line); font-size:.95rem; word-break:break-word; }
    .sr-contact i { width:16px; text-align:center; color:var(--sr-faint); }
    .sr-contact a, body.dark-mode .sr-contact a { color:var(--sr-ink) !important; }
    .sr-contact a:hover { color:var(--sr-brand) !important; }
    .sr-contact .none { color:var(--sr-faint); }

    .sr-hist { border:1px solid var(--sr-line); border-radius:12px; overflow:hidden; }
    .sr-hrow { display:flex; align-items:center; gap:10px; padding:13px 16px; border-bottom:1px solid var(--sr-line); }
    .sr-hrow .left { flex:1; min-width:0; }
    .sr-hrow .yr { font-weight:700; font-size:1.02rem; margin-right:8px; }
    .sr-hrow .left small { display:block; color:var(--sr-faint); font-size:.75rem; margin-top:2px; }
    .sr-hrow .amt { text-align:right; font-variant-numeric:tabular-nums; white-space:nowrap; }
    .sr-hrow .amt small { display:block; color:var(--sr-faint); font-size:.74rem; }
    .sr-hrow.current { background:var(--sr-soft); box-shadow:inset 3px 0 0 #a02626; }
    .sr-hfoot { display:flex; justify-content:space-between; align-items:center; padding:13px 16px; background:var(--sr-soft); }
    .sr-hfoot + .sr-hfoot { border-top:1px solid var(--sr-line); }
    .sr-hfoot strong { font-size:1.05rem; font-variant-numeric:tabular-nums; }
    .sr-empty { padding:22px 16px; text-align:center; color:var(--sr-faint); font-size:.88rem; }

    .sr-skel { background:linear-gradient(90deg,var(--sr-soft) 25%,var(--sr-line) 50%,var(--sr-soft) 75%); background-size:200% 100%;
               animation:srsk 1.2s infinite; border-radius:8px; }
    @keyframes srsk { to { background-position:-200% 0; } }

    .sr-yearpick .dropdown-toggle { font-weight:700; border-radius:8px; min-width:120px; text-align:left; }
    .sr-yearpick .dropdown-menu { padding:10px; min-width:230px; }
    .sr-yearpick .yp-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:6px; }
    .sr-yearpick .yp-foot { display:flex; justify-content:space-between; align-items:center; margin-top:10px; padding-top:8px; border-top:1px solid var(--sr-line); }
    .sr-yearpick .export-year span { min-width:0; width:100%; padding:5px 0; }
    #subscriptionReportTable td.yc { white-space:nowrap; }
    #subscriptionReportTable td.yc small { display:block; color:var(--sr-faint); font-size:.74rem; margin-top:2px; font-variant-numeric:tabular-nums; }
    .export-year input { display:none; }
    .export-year span { display:inline-block; min-width:64px; text-align:center; padding:6px 12px; border-radius:8px; cursor:pointer;
                        border:1px solid #d6dde6; font-weight:600; user-select:none; transition:all .1s; }
    .export-year input:checked + span { background:#a02626; border-color:#a02626; color:#fff; }
    body.dark-mode .export-year span { border-color:#4a5568; }

    .token-chip { cursor:pointer; border:1px solid #a02626; color:#a02626; background:#fff; border-radius:4px;
                  font-size:.72rem; padding:2px 8px; margin:0 3px 3px 0; }
    .token-chip:hover { background:#a02626; color:#fff; }

    @media (max-width: 991px) { .sr-tiles { grid-template-columns:repeat(3,minmax(0,1fr)); } }
    @media (max-width: 575px) { .sr-tiles { grid-template-columns:repeat(2,minmax(0,1fr)); } .sr-search input { width:100%; } }
</style>
@endpush

@section('content')
<div class="wrapper">
    <div class="content-wrapper">
        <section class="content-header"></section>
        <div class="col-md-12">@include('_message')</div>

        <section class="content sr-page">
            <div class="container-wrapper">

                {{-- ── Header ── --}}
                <div class="sr-head">
                    <div>
                        <div class="sr-eyebrow"><a href="{{ url('admin/fees') }}" style="color:inherit;">Fees</a> / Subscriptions</div>
                        <h4>Annual Subscription Report</h4>
                        <div class="sr-sub">
                            @if($multi)
                                Every fellow's subscriptions for {{ $yearLabel }}. A fellow with no record for a year counts as owing for that year.
                            @else
                                Every fellow's {{ $year }} subscription. Fellows with no record for the year count as owing.
                            @endif
                        </div>
                    </div>
                    <div class="sr-actions">
                        <form method="GET" action="{{ url('admin/fees/subscriptions/report') }}" class="m-0">
                            @foreach(['status','country_id','q'] as $keep)
                                @if(!empty($filters[$keep]))<input type="hidden" name="{{ $keep }}" value="{{ $filters[$keep] }}">@endif
                            @endforeach
                            <div class="dropdown sr-yearpick">
                                <button type="button" class="btn btn-sm btn-sr-ghost dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="far fa-calendar-alt mr-1"></i>{{ $yearLabel }}
                                </button>
                                <div class="dropdown-menu dropdown-menu-right" onclick="event.stopPropagation()">
                                    <div class="sr-eyebrow mb-2">Show years</div>
                                    <div class="yp-grid">
                                        @foreach($years as $y)
                                            <label class="export-year mb-0">
                                                <input type="checkbox" name="years[]" value="{{ $y }}" {{ in_array((string) $y, $selectedYears, true) ? 'checked' : '' }}>
                                                <span>{{ $y }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <div class="yp-foot">
                                        <a href="#" class="small" data-yp-all>Select all</a>
                                        <button type="submit" class="btn btn-sm btn-sr">Apply</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                        @if ($canManage && ($summary['owing'] ?? 0) > 0)
                        <button type="button" class="btn btn-sm btn-sr" onclick="$('#reminderModal').modal('show')">
                            <i class="fas fa-paper-plane mr-1"></i>{{ $multi ? 'Remind owing' : 'Remind ' . number_format($summary['owing'] ?? 0) . ' owing' }}
                        </button>
                        @endif
                    </div>
                </div>

                {{-- ── Summary ── --}}
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="sr-tiles">
                            @foreach($statusTiles as $st => $t)
                                @php $n = (int) ($summary[$t['key']] ?? 0); $isActive = $activeStatus === $st; @endphp
                                <a class="sr-tile t-{{ $st }} {{ $isActive ? 'active' : '' }}"
                                   href="{{ request()->fullUrlWithQuery(['status' => $isActive ? null : $st]) }}"
                                   title="{{ $isActive ? 'Show all statuses' : 'Show only ' . $t['label'] . ($multi ? ' (in any selected year)' : '') }}">
                                    <span class="sr-eyebrow">{{ $t['label'] }}</span>
                                    <span class="n">{{ number_format($n) }}</span>
                                    <span class="p">{{ round($n / $total * 100) }}% of {{ $multi ? 'records' : 'fellows' }}</span>
                                </a>
                            @endforeach
                        </div>
                        <div class="sr-dist" title="Status distribution across {{ number_format($summary['records'] ?? 0) }} {{ $multi ? 'fellow-year records' : 'fellows' }}">
                            @foreach($statusTiles as $st => $t)
                                <span style="width:{{ ($summary[$t['key']] ?? 0) / $total * 100 }}%;background:var(--st-{{ $st }}-fg);"></span>
                            @endforeach
                        </div>
                        @if(($summary['owing'] ?? 0) > 0)
                        <div class="sr-owing">
                            <i class="fas fa-exclamation-circle"></i>
                            <span><strong>{{ number_format($summary['owing']) }}</strong> of {{ number_format($summary['total_fellows'] ?? 0) }} fellows still owe for {{ $multi ? 'at least one of ' . $yearLabel : $year }} (Unpaid, Partial or No Record).</span>
                        </div>
                        @endif
                        @if($multi)
                        <div class="small mt-2" style="color:var(--sr-faint);">
                            Tile counts are summed across the {{ count($selectedYears) }} selected years (one record per fellow per year).
                        </div>
                        @endif
                    </div>
                </div>

                {{-- ── Table ── --}}
                <div class="sr-card sr-wrap mb-4">
                    <form method="GET" action="{{ url('admin/fees/subscriptions/report') }}" class="sr-toolbar m-0">
                        {!! $keepYears() !!}
                        <div class="title">Fellows <span class="count">{{ number_format($rows->count()) }}</span></div>
                        <div class="sr-search">
                            <i class="fas fa-search"></i>
                            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Name, email, reg no…"
                                   class="form-control form-control-sm" onchange="this.form.submit()">
                        </div>
                        <select name="status" class="form-control form-control-sm" style="width:140px;" onchange="this.form.submit()" aria-label="Status">
                            <option value="">All statuses</option>
                            @foreach($statusTiles as $st => $t)
                                <option value="{{ $st }}" {{ $activeStatus === $st ? 'selected' : '' }}>{{ $t['label'] }}</option>
                            @endforeach
                        </select>
                        <select name="country_id" class="form-control form-control-sm" style="width:170px;" onchange="this.form.submit()" aria-label="Country">
                            <option value="">All countries</option>
                            @foreach($countries as $c)
                                <option value="{{ $c->id }}" {{ (string)($filters['country_id'] ?? '') === (string)$c->id ? 'selected' : '' }}>{{ $c->country_name }}</option>
                            @endforeach
                        </select>
                        @if(!empty($filters['q']) || !empty($filters['status']) || !empty($filters['country_id']))
                            <a href="{{ url('admin/fees/subscriptions/report') . '?' . http_build_query(['years' => $selectedYears]) }}" class="btn btn-sm btn-sr-ghost">Clear</a>
                        @endif
                    </form>

                    <div class="table-responsive">
                        <table id="subscriptionReportTable" class="table mb-0" style="width:100%;">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Fellow</th>
                                    <th>Country</th>
                                    <th>Fellowship Type</th>
                                    @if($multi)
                                        @foreach($selectedYears as $y)<th>{{ $y }}</th>@endforeach
                                        <th class="num">Total Paid</th>
                                        <th class="num">Outstanding</th>
                                    @else
                                        <th>Status</th>
                                        <th class="num">Due</th>
                                        <th class="num">Paid</th>
                                        <th class="num">Outstanding</th>
                                        <th>Date Paid</th>
                                        <th>Mode</th>
                                    @endif
                                    <th class="no-export"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rows as $i => $row)
                                @php
                                    $parts = preg_split('/\s+/', trim($row->name));
                                    $initials = strtoupper(mb_substr($parts[0] ?? '', 0, 1) . mb_substr(count($parts) > 1 ? end($parts) : '', 0, 1));
                                    $r = $row->years[$year] ?? null;
                                    $mode = (!$r || !$r->mode_of_payment || preg_match('/^\d{4}-\d{2}-\d{2}/', $r->mode_of_payment)) ? null : $r->mode_of_payment;
                                @endphp
                                <tr data-fellow="{{ $row->fellow_id }}">
                                    <td class="muted">{{ $i + 1 }}</td>
                                    <td>
                                        <div class="sr-who">
                                            <span class="sr-avatar" data-initials="{{ $initials }}"></span>
                                            <div>
                                                <div class="nm">{{ $row->name }}</div>
                                                @if($row->email)<div class="em">{{ $row->email }}</div>@endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $row->country_name ?? '—' }}</td>
                                    <td>{{ $row->fellowship_type ?? '—' }}</td>
                                    @if($multi)
                                        @foreach($selectedYears as $y)
                                            @php $yr = $row->years[$y] ?? null; $st = $yr->effective_status ?? 'None'; @endphp
                                            <td class="yc" data-order="{{ $st }}">
                                                <span class="sr-pill s-{{ $st }}">{{ $st === 'None' ? 'No Record' : $st }}</span>
                                                @if($yr && $yr->amount_paid !== null)<small>{{ number_format($yr->amount_paid, 2) }}</small>@endif
                                            </td>
                                        @endforeach
                                        <td class="num" data-order="{{ $row->total_paid }}">{{ number_format($row->total_paid, 2) }}</td>
                                        <td class="num" data-order="{{ $row->outstanding }}">{{ number_format($row->outstanding, 2) }}</td>
                                    @else
                                        @php $st = $r->effective_status ?? 'None'; @endphp
                                        <td data-order="{{ $st }}">
                                            <span class="sr-pill s-{{ $st }}">{{ $st === 'None' ? 'No Record' : $st }}</span>
                                        </td>
                                        <td class="num" data-order="{{ $r->amount_due ?? -1 }}">{{ isset($r->amount_due) ? number_format($r->amount_due, 2) : '—' }}</td>
                                        <td class="num" data-order="{{ $r->amount_paid ?? -1 }}">{{ isset($r->amount_paid) ? number_format($r->amount_paid, 2) : '—' }}</td>
                                        <td class="num" data-order="{{ $r->outstanding ?? -1 }}">{{ isset($r->outstanding) ? number_format($r->outstanding, 2) : '—' }}</td>
                                        <td data-order="{{ $r->date_paid ?? '' }}">{{ !empty($r->date_paid) ? \Carbon\Carbon::parse($r->date_paid)->format('d M Y') : '—' }}</td>
                                        <td>{{ $mode ?? '—' }}</td>
                                    @endif
                                    <td class="no-export text-right"><i class="fas fa-chevron-right sr-go"></i></td>
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

{{-- ── Fellow subscription drawer ── --}}
<div class="sr-page" id="srDrawerRoot">
    <div class="sr-backdrop" data-close></div>
    <aside class="sr-drawer" role="dialog" aria-modal="true" aria-labelledby="srDrawerTitle" aria-hidden="true">
        <div class="sr-dh">
            <h5 id="srDrawerTitle">Fellow Subscription</h5>
            <button type="button" class="sr-x" data-close aria-label="Close">&times;</button>
        </div>
        <div class="sr-db" id="srDrawerBody"></div>
        <div class="sr-df">
            <a href="#" class="btn btn-sm btn-sr-ghost" id="srProfileLink"><i class="far fa-user mr-1"></i>Full profile</a>
            @if($canManage)
            <a href="#" class="btn btn-sm btn-sr" id="srManageLink"><i class="fas fa-receipt mr-1"></i>Manage subscriptions</a>
            @endif
        </div>
    </aside>
</div>

{{-- ── Multi-year Excel download ── --}}
@php $hasFilters = !empty($filters['q']) || !empty($filters['status']) || !empty($filters['country_id']); @endphp
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="GET" action="{{ route('admin.fees.subscriptions.export') }}" id="exportForm">
                <div class="modal-header" style="border-bottom:2px solid #a02626;">
                    <h5 class="modal-title" style="color:#a02626;"><i class="fas fa-file-excel mr-1"></i>Download Excel</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <label class="font-weight-bold small mb-0">Years to include</label>
                        <a href="#" class="small" id="exportToggleAll">Select all</a>
                    </div>
                    <div class="d-flex flex-wrap" style="gap:8px;">
                        @foreach($years as $y)
                            <label class="export-year mb-0">
                                <input type="checkbox" name="years[]" value="{{ $y }}" {{ in_array((string) $y, $selectedYears, true) ? 'checked' : '' }}>
                                <span>{{ $y }}</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="small text-muted mt-3">
                        One workbook: a <strong>Summary</strong> sheet (one row per year) plus a sheet per year listing every fellow.
                    </div>
                    @if($hasFilters)
                        <div class="custom-control custom-checkbox mt-3">
                            <input type="checkbox" class="custom-control-input" id="exportApplyFilters" name="apply_filters" value="1" checked>
                            <label class="custom-control-label small" for="exportApplyFilters">
                                Apply current filters
                                <span class="text-muted">({{ collect([
                                    !empty($filters['status']) ? ($filters['status'] === 'None' ? 'No Record' : $filters['status']) : null,
                                    !empty($filters['country_id']) ? optional($countries->firstWhere('id', $filters['country_id']))->country_name : null,
                                    !empty($filters['q']) ? '“' . $filters['q'] . '”' : null,
                                ])->filter()->implode(', ') }})</span>
                            </label>
                        </div>
                        @foreach(['status','country_id','q'] as $keep)
                            @if(!empty($filters[$keep]))<input type="hidden" name="{{ $keep }}" value="{{ $filters[$keep] }}">@endif
                        @endforeach
                    @endif
                    <div class="text-danger small mt-2 d-none" id="exportNoYear">Select at least one year.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn font-weight-bold" style="background:#a02626;border-color:#a02626;color:#fff;">
                        <i class="fas fa-download mr-1"></i>Download <span id="exportCount"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ── Send Reminder Modal ── --}}
@php $remindYears = array_filter($yearOwing, fn ($n) => $n > 0); $remindDefault = array_key_first($remindYears); @endphp
@if ($canManage && $remindYears)
<div class="modal fade" id="reminderModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.fees.subscriptions.remind') }}">
                @csrf
                <div class="modal-header" style="border-bottom:2px solid #a02626;">
                    <h5 class="modal-title" style="color:#a02626;"><i class="fas fa-envelope-open-text mr-1"></i>Send Subscription Reminders</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning py-2" style="font-size:.85rem;">
                        This will email <strong><span id="remindCount">{{ number_format($remindYears[$remindDefault]) }}</span> fellow(s)</strong>
                        with an outstanding <span id="remindYearText">{{ $remindDefault }}</span>
                        annual subscription (status Unpaid, Partial, or No Record). Waived fellows are never emailed.
                    </div>
                    @if(count($remindYears) > 1)
                        <div class="form-group">
                            <label class="font-weight-bold small">Year to remind for <span class="text-danger">*</span></label>
                            <select name="year" id="remindYear" class="form-control" style="max-width:220px;">
                                @foreach($remindYears as $y => $n)
                                    <option value="{{ $y }}" data-owing="{{ $n }}">{{ $y }} — {{ number_format($n) }} owing</option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <input type="hidden" name="year" value="{{ $remindDefault }}">
                    @endif
                    <div class="form-group">
                        <label class="font-weight-bold small">Subject <span class="text-danger">*</span></label>
                        <input type="text" name="subject" class="form-control" required
                               placeholder="Annual Subscription Reminder for {{ $remindDefault }}"
                               value="Annual Subscription Reminder for {{ $remindDefault }}">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold small">Body <span class="text-danger">*</span></label>
                        <textarea name="body" class="form-control" rows="4" required>Dear @{{first_name}},

This is a reminder that your COSECSA annual subscription for @{{year}} is still outstanding. Please arrange payment at your earliest convenience.

Kind Regards,
COSECSA Secretariat</textarea>
                        <div class="mt-2">
                            <small class="text-muted">Insert a token:</small>
                            @foreach(['name','first_name','year','country','fellowship_type','amount_due','amount_paid','outstanding'] as $tok)
                                <span class="token-chip" onclick="insertToken(this)">{{ '{' . '{' . $tok . '}' . '}' }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn font-weight-bold" style="background:#a02626;border-color:#a02626;color:#fff;"
                            onclick="return confirm('Send reminder emails to all ' + document.getElementById('remindCount').textContent + ' outstanding fellows for ' + document.getElementById('remindYearText').textContent + '?');">
                        <i class="fas fa-paper-plane mr-1"></i>Send Reminder Emails
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
$(document).ready(function () {
    var exportCols = { columns: ':not(.no-export)' };
    $('#subscriptionReportTable').DataTable({
        dom: 'Brtip',
        buttons: [
            { extend: 'copyHtml5',  className: 'btn-sm', exportOptions: exportCols },
            { extend: 'csvHtml5',   className: 'btn-sm', title: @json('annual_subscriptions_' . str_replace('–', '-', $yearLabel)), exportOptions: exportCols },
            { text: 'Excel', className: 'btn-sm', action: function () { $('#exportModal').modal('show'); } },
            { extend: 'pdfHtml5',   className: 'btn-sm', title: @json('Annual Subscription Report ' . $yearLabel), orientation: 'landscape', pageSize: 'A4', exportOptions: exportCols },
            { extend: 'print',      className: 'btn-sm', exportOptions: exportCols }
        ],
        columnDefs: [
            { orderable: false, targets: [0, -1] },
            { targets: 0, render: function (data, type, row, meta) { return meta.row + 1; } }
        ],
        pageLength: 25,
        order: []
    });

    // Row click opens the drawer (delegated so it survives paging/sorting).
    $('#subscriptionReportTable tbody').on('click', 'tr[data-fellow]', function () {
        SubDrawer.open($(this).data('fellow'));
    });
});

// ── Year picker (header) ─────────────────────────────────────────────────
(function () {
    var menu = document.querySelector('.sr-yearpick .dropdown-menu');
    if (!menu) return;
    var boxes = menu.querySelectorAll('input[name="years[]"]');
    var all = menu.querySelector('[data-yp-all]');
    function sync() {
        var n = Array.prototype.filter.call(boxes, function (b) { return b.checked; }).length;
        all.textContent = n === boxes.length ? 'Clear all' : 'Select all';
        menu.querySelector('button[type="submit"]').disabled = n === 0;
    }
    boxes.forEach(function (b) { b.addEventListener('change', sync); });
    all.addEventListener('click', function (e) {
        e.preventDefault();
        var on = Array.prototype.some.call(boxes, function (b) { return !b.checked; });
        boxes.forEach(function (b) { b.checked = on; });
        sync();
    });
    sync();
})();

// ── Reminder modal: switching the year updates the count + subject ──────
(function () {
    var sel = document.getElementById('remindYear');
    if (!sel) return;
    var subject = document.querySelector('#reminderModal input[name="subject"]');
    var shown = sel.value;
    sel.addEventListener('change', function () {
        var opt = sel.options[sel.selectedIndex];
        document.getElementById('remindCount').textContent = Number(opt.dataset.owing).toLocaleString('en-US');
        document.getElementById('remindYearText').textContent = sel.value;
        subject.value = subject.value.split(shown).join(sel.value);
        shown = sel.value;
    });
})();

// ── Multi-year Excel download modal ─────────────────────────────────────
(function () {
    var form = document.getElementById('exportForm');
    var boxes = form.querySelectorAll('input[name="years[]"]');
    var toggle = document.getElementById('exportToggleAll');
    function refresh() {
        var n = Array.prototype.filter.call(boxes, function (b) { return b.checked; }).length;
        document.getElementById('exportCount').textContent = n ? '(' + n + ' year' + (n === 1 ? '' : 's') + ')' : '';
        toggle.textContent = n === boxes.length ? 'Clear all' : 'Select all';
        if (n) document.getElementById('exportNoYear').classList.add('d-none');
        return n;
    }
    boxes.forEach(function (b) { b.addEventListener('change', refresh); });
    toggle.addEventListener('click', function (e) {
        e.preventDefault();
        var all = refresh() !== boxes.length;
        boxes.forEach(function (b) { b.checked = all; });
        refresh();
    });
    form.addEventListener('submit', function (e) {
        if (!refresh()) { e.preventDefault(); document.getElementById('exportNoYear').classList.remove('d-none'); return; }
        // File downloads don't navigate, so close the modal once the request is sent.
        setTimeout(function () { $('#exportModal').modal('hide'); }, 300);
    });
    refresh();
})();

// ── Fellow subscription drawer ──────────────────────────────────────────
var SubDrawer = (function () {
    var root   = document.getElementById('srDrawerRoot');
    var panel  = root.querySelector('.sr-drawer');
    var body   = document.getElementById('srDrawerBody');
    var year   = @json((string) $year);          // newest selected year
    var selectedYears = @json($selectedYears);
    var base   = @json(url('admin/fees/subscriptions/fellow'));
    var subsBase = @json(url('admin/associates/fellows/subscriptions'));
    var labels = { None: 'No Record' };
    var current = 0;

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function money(n) {
        return '$' + Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function initials(name) {
        var p = String(name || '').trim().split(/\s+/);
        return ((p[0] || '').charAt(0) + (p.length > 1 ? p[p.length - 1].charAt(0) : '')).toUpperCase();
    }
    function pill(status) {
        return '<span class="sr-pill s-' + esc(status) + '">' + esc(labels[status] || status) + '</span>';
    }

    function skeleton() {
        var row = '<div class="sr-skel" style="height:14px;margin:14px 0;"></div>';
        return '<div class="sr-id"><div class="sr-skel" style="width:54px;height:54px;border-radius:50%;"></div>'
             + '<div style="flex:1"><div class="sr-skel" style="height:16px;width:70%;"></div><div class="sr-skel" style="height:12px;width:45%;margin-top:8px;"></div></div></div>'
             + '<div class="sr-stats">' + '<div class="sr-skel" style="height:64px;border-radius:12px;"></div>'.repeat(3) + '</div>'
             + row.repeat(3) + '<div class="sr-skel" style="height:220px;border-radius:12px;margin-top:22px;"></div>';
    }

    function render(d) {
        var f = d.fellow, h = d.history || [];
        var thisYear = h.find(function (r) { return r.year === year; });
        var yrStatus = thisYear ? thisYear.status : 'None';

        var avatar = f.photo
            ? '<span class="sr-avatar has-img"><img src="' + esc(f.photo) + '" alt="" onerror="this.parentNode.classList.remove(\'has-img\');this.remove();"></span>'
            : '<span class="sr-avatar"></span>';
        var meta = [f.fellowship_type, f.programme].filter(Boolean).map(esc).join(' · ');

        var html = '<div class="sr-id">' + avatar.replace('class="sr-avatar', 'data-initials="' + esc(initials(f.name)) + '" class="sr-avatar')
                 + '<div><div class="nm">' + esc(f.name) + '</div>' + (meta ? '<div class="meta">' + meta + '</div>' : '') + '</div></div>';

        html += '<div class="sr-stats">'
              + '<div class="sr-stat s-' + esc(yrStatus) + '"><div class="sr-eyebrow">' + esc(year) + '</div><div class="v">' + esc(labels[yrStatus] || yrStatus) + '</div></div>'
              + '<div class="sr-stat"><div class="sr-eyebrow">Total Paid</div><div class="v">' + money(d.total_paid) + '</div></div>'
              + '<div class="sr-stat' + (d.total_owing > 0 ? ' s-Unpaid' : '') + '"><div class="sr-eyebrow">Owing</div><div class="v">' + money(d.total_owing) + '</div></div>'
              + '</div>';

        function line(icon, value, href) {
            if (!value) return '<div><i class="' + icon + '"></i><span class="none">Not on file</span></div>';
            var v = esc(value);
            return '<div><i class="' + icon + '"></i>' + (href ? '<a href="' + esc(href) + '">' + v + '</a>' : '<span>' + v + '</span>') + '</div>';
        }
        html += '<div class="sr-eyebrow sr-sec">Contact</div><div class="sr-contact">'
              + line('far fa-envelope', f.email, f.email ? 'mailto:' + f.email : null)
              + line('fas fa-phone-alt', f.phone, f.phone ? 'tel:' + String(f.phone).replace(/\s+/g, '') : null)
              + line('fas fa-map-marker-alt', f.country)
              + '</div>';

        html += '<div class="sr-eyebrow sr-sec">Subscription History</div><div class="sr-hist">';
        if (!h.length) {
            html += '<div class="sr-empty"><i class="far fa-folder-open d-block mb-2" style="font-size:1.4rem;"></i>No subscription records yet.</div>';
        } else {
            h.forEach(function (r) {
                var partial = r.amount_due != null && r.amount_paid != null && r.amount_paid < r.amount_due && r.status !== 'Waived';
                var sub = [];
                if (r.date_paid) sub.push(new Date(r.date_paid).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }));
                if (r.mode) sub.push(esc(r.mode));
                html += '<div class="sr-hrow' + (selectedYears.indexOf(r.year) !== -1 ? ' current' : '') + '">'
                      + '<div class="left"><span class="yr">' + esc(r.year) + '</span>' + pill(r.status)
                      + (sub.length ? '<small>' + sub.join(' · ') + '</small>' : '') + '</div>'
                      + '<span class="amt">' + (r.amount_paid != null ? money(r.amount_paid) : '—')
                      + (partial ? '<small>of ' + money(r.amount_due) + '</small>' : '') + '</span></div>';
            });
        }
        html += '<div class="sr-hfoot"><span class="sr-eyebrow">Total Paid</span><strong>' + money(d.total_paid) + '</strong></div>';
        if (d.total_owing > 0) {
            html += '<div class="sr-hfoot"><span class="sr-eyebrow">Outstanding</span><strong style="color:var(--st-Unpaid-fg);">' + money(d.total_owing) + '</strong></div>';
        }
        html += '</div>';

        if (!thisYear) {
            html += '<div class="sr-owing mt-3"><i class="fas fa-info-circle"></i><span>No ' + esc(year) + ' subscription on record, so this fellow is counted as owing.</span></div>';
        }

        body.innerHTML = html;
        document.getElementById('srProfileLink').href = f.profile_url;
        var manage = document.getElementById('srManageLink');
        if (manage) manage.href = subsBase + '/' + f.id;
    }

    function open(id) {
        current = id;
        body.innerHTML = skeleton();
        document.getElementById('srProfileLink').href = '#';
        root.classList.add('sr-open');
        panel.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';
        panel.querySelector('.sr-x').focus();

        fetch(base + '/' + encodeURIComponent(id), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
            .then(function (d) { if (current === id) render(d); })
            .catch(function () {
                if (current !== id) return;
                body.innerHTML = '<div class="sr-empty"><i class="fas fa-exclamation-triangle d-block mb-2" style="font-size:1.4rem;color:var(--st-Unpaid-fg);"></i>'
                               + 'Couldn\'t load this fellow\'s subscriptions. <a href="#" onclick="SubDrawer.open(' + Number(id) + ');return false;">Try again</a></div>';
            });
    }

    function close() {
        current = 0;
        root.classList.remove('sr-open');
        panel.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';
    }

    root.addEventListener('click', function (e) { if (e.target.closest('[data-close]')) close(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && root.classList.contains('sr-open')) close(); });

    return { open: open, close: close };
})();

// Insert a token marker into the reminder body at the cursor.
function insertToken(el) {
    var ta = document.querySelector('#reminderModal textarea[name="body"]');
    if (!ta) return;
    var token = el.textContent;
    var start = ta.selectionStart || 0;
    var end = ta.selectionEnd || 0;
    ta.value = ta.value.slice(0, start) + token + ta.value.slice(end);
    ta.selectionStart = ta.selectionEnd = start + token.length;
    ta.focus();
}
</script>
@endpush
