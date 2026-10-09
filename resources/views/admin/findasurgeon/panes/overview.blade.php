

@php
    $p = $data->patients;  $d = $data->directory;  $c = $data->changes;  $h = $data->hospital_review;  $ch = $data->charts;
    $pct = fn ($n, $of) => $of > 0 ? round($n * 100 / $of) : 0;
    $completeness = [
        ['Photo',                 $d->with_photo],
        ['Bio',                   $d->with_bio],
        ['City',                  $d->with_city],
        ['Current hospital',      $d->with_hospital],
        ['Hospital on the list',  $d->hospital_linked],
        ['Subspecialty',          $d->with_subspecialty],
    ];
@endphp

<style>
    .fas-kpi { border-radius:12px; border:1px solid rgba(0,0,0,.08); padding:14px 16px; height:100%; background:#fff; }
    body.dark-mode .fas-kpi { background:#1e2330; border-color:#2d3748; }
    .fas-kpi .n { font-size:1.7rem; font-weight:700; line-height:1.15; color:#a02626; }
    body.dark-mode .fas-kpi .n { color:#f48a8a; }
    .fas-kpi .l { font-size:.74rem; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:#6b7280; }
    .fas-kpi .s { font-size:.8rem; color:#6b7280; }
    .fas-chart { position:relative; height:260px; }
    .fas-chart.tall { height:320px; }
    .fas-alert { border-left:4px solid #FEC503; }
    @media print {
        .main-sidebar, .main-header, #fasHubTabs, .fas-noprint, .main-footer { display:none !important; }
        .content-wrapper { margin-left:0 !important; }
        .card { break-inside:avoid; }
    }
</style>

<div class="fas-pane-head mb-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <div class="fas-muted" style="font-size:.85rem;">Patient app activity, directory health and what Fellows are changing. Generated {{ \Carbon\Carbon::parse($data->generated_at)->format('d M Y, H:i') }}.</div>
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm fas-noprint" onclick="window.print()"><i class="fas fa-print mr-1"></i> Print / save as PDF</button>
            </div>
        </div>
<div>

            @if ($h->groups_pending > 0)
                <div class="alert alert-light fas-alert shadow-sm d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <strong>{{ $h->groups_pending }} hospital name{{ $h->groups_pending == 1 ? '' : 's' }}</strong> used by
                        <strong>{{ $h->fellows_pending }} Fellow{{ $h->fellows_pending == 1 ? '' : 's' }}</strong> {{ $h->groups_pending == 1 ? 'is' : 'are' }} not on the official hospital list
                        @if ($h->typed_by_fellows > 0) ({{ $h->typed_by_fellows }} typed in the app) @endif.
                    </div>
                    <a href="{{ route('admin.findasurgeon.hospitals') }}" class="btn btn-sm btn-dark fas-noprint">Review</a>
                </div>
            @endif

            {{-- Patients --}}
            <h6 class="text-uppercase fas-muted mb-2" style="letter-spacing:.08em;">Patients</h6>
            <div class="row mb-3">
                <div class="col-6 col-md-3 mb-3"><div class="fas-kpi"><div class="l">Accounts</div><div class="n">{{ number_format($p->total) }}</div><div class="s">{{ $p->new_30d }} new in the last 30 days</div></div></div>
                <div class="col-6 col-md-3 mb-3"><div class="fas-kpi"><div class="l">Verified</div><div class="n">{{ $pct($p->verified, $p->total) }}%</div><div class="s">{{ number_format($p->verified) }} verified · {{ number_format($p->unverified) }} not yet</div></div></div>
                <div class="col-6 col-md-3 mb-3"><div class="fas-kpi"><div class="l">Active (30 days)</div><div class="n">{{ number_format($p->active_30d) }}</div><div class="s">signed in and used the app</div></div></div>
                <div class="col-6 col-md-3 mb-3"><div class="fas-kpi"><div class="l">Saved surgeons</div><div class="n">{{ number_format($p->saves_total) }}</div><div class="s">by {{ number_format($p->with_favourites) }} patient{{ $p->with_favourites == 1 ? '' : 's' }}</div></div></div>
            </div>

            <div class="row">
                <div class="col-lg-7 mb-3"><div class="card h-100"><div class="card-header"><strong>New patient accounts per week</strong> <span class="fas-muted">· last 12 weeks</span></div><div class="card-body"><div class="fas-chart"><canvas id="chSignups"></canvas></div></div></div></div>
                <div class="col-lg-5 mb-3"><div class="card h-100"><div class="card-header"><strong>Most saved surgeons</strong></div><div class="card-body">
                    @if (count($ch->top_favourited)) <div class="fas-chart"><canvas id="chTop"></canvas></div>
                    @else <p class="fas-muted mb-0">No surgeon has been saved yet.</p> @endif
                </div></div></div>
            </div>

            {{-- Directory --}}
            <h6 class="text-uppercase fas-muted mb-2 mt-2" style="letter-spacing:.08em;">Directory health</h6>
            <div class="row mb-3">
                <div class="col-6 col-md-3 mb-3"><div class="fas-kpi"><div class="l">Fellows listed</div><div class="n">{{ number_format($d->listed) }}</div><div class="s">active Fellows patients can find</div></div></div>
                <div class="col-6 col-md-3 mb-3"><div class="fas-kpi"><div class="l">With a photo</div><div class="n">{{ $pct($d->with_photo, $d->listed) }}%</div><div class="s">{{ number_format($d->with_photo) }} of {{ number_format($d->listed) }}</div></div></div>
                <div class="col-6 col-md-3 mb-3"><div class="fas-kpi"><div class="l">With a bio</div><div class="n">{{ $pct($d->with_bio, $d->listed) }}%</div><div class="s">{{ number_format($d->with_bio) }} of {{ number_format($d->listed) }}</div></div></div>
                <div class="col-6 col-md-3 mb-3"><div class="fas-kpi"><div class="l">Fellow edits (30 days)</div><div class="n">{{ number_format($c->last_30d) }}</div><div class="s">by {{ number_format($c->fellows_30d) }} Fellow{{ $c->fellows_30d == 1 ? '' : 's' }}</div></div></div>
            </div>
            <div class="row">
                <div class="col-lg-6 mb-3"><div class="card h-100"><div class="card-header"><strong>How complete are the listed profiles?</strong></div><div class="card-body"><div class="fas-chart"><canvas id="chComplete"></canvas></div></div></div></div>
                <div class="col-lg-6 mb-3"><div class="card h-100"><div class="card-header"><strong>Fellow profile edits per week</strong> <span class="fas-muted">· last 12 weeks</span></div><div class="card-body"><div class="fas-chart"><canvas id="chChanges"></canvas></div></div></div></div>
            </div>
            <div class="row">
                <div class="col-lg-4 mb-3"><div class="card h-100"><div class="card-header"><strong>What Fellows edit</strong> <span class="fas-muted">· last 90 days</span></div><div class="card-body">
                    @if (count($ch->changes_by_field)) <div class="fas-chart"><canvas id="chFields"></canvas></div>
                    @else <p class="fas-muted mb-0">No edits yet.</p> @endif
                </div></div></div>
                <div class="col-lg-4 mb-3"><div class="card h-100"><div class="card-header"><strong>Saves by specialty</strong></div><div class="card-body">
                    @if (count($ch->saves_by_specialty)) <div class="fas-chart"><canvas id="chSpec"></canvas></div>
                    @else <p class="fas-muted mb-0">Nothing saved yet.</p> @endif
                </div></div></div>
                <div class="col-lg-4 mb-3"><div class="card h-100"><div class="card-header"><strong>Listed Fellows by country</strong> <span class="fas-muted">· top 10</span></div><div class="card-body"><div class="fas-chart"><canvas id="chCountry"></canvas></div></div></div></div>
            </div>
</div>

<script>
(function () {
    var C = @json($ch);
    var completeness = @json(collect($completeness)->map(fn ($r) => ['label' => $r[0], 'pct' => $pct($r[1], $d->listed)])->values());
    var maroon = '#a02626', gold = '#FEC503', slate = '#475569';
    var dark = document.body.classList.contains('dark-mode');
    var grid = dark ? 'rgba(255,255,255,.08)' : 'rgba(0,0,0,.06)', ink = dark ? '#cbd5e1' : '#475569';
    Chart.defaults.color = ink; Chart.defaults.font.family = 'inherit';

    function make(id, cfg) { var el = document.getElementById(id); if (el) return new Chart(el, cfg); }
    var ints = { beginAtZero: true, ticks: { precision: 0 }, grid: { color: grid } };
    var noGrid = { grid: { display: false } };

    make('chSignups', { type: 'bar', data: { labels: C.weeks, datasets: [{ label: 'New accounts', data: C.patient_signups, backgroundColor: maroon, borderRadius: 4 }] },
        options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: ints, x: noGrid } } });

    make('chChanges', { type: 'line', data: { labels: C.weeks, datasets: [{ label: 'Edits', data: C.profile_changes, borderColor: maroon, backgroundColor: 'rgba(160,38,38,.12)', fill: true, tension: .3, pointRadius: 3 }] },
        options: { maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: ints, x: noGrid } } });

    make('chComplete', { type: 'bar', data: { labels: completeness.map(function (r) { return r.label; }), datasets: [{ data: completeness.map(function (r) { return r.pct; }), backgroundColor: gold, borderRadius: 4 }] },
        options: { indexAxis: 'y', maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { callbacks: { label: function (c) { return c.parsed.x + '% of listed Fellows'; } } } },
                   scales: { x: { min: 0, max: 100, ticks: { callback: function (v) { return v + '%'; } }, grid: { color: grid } }, y: noGrid } } });

    if (C.top_favourited.length) make('chTop', { type: 'bar', data: { labels: C.top_favourited.map(function (r) { return r.name; }), datasets: [{ label: 'Saves', data: C.top_favourited.map(function (r) { return r.saves; }), backgroundColor: maroon, borderRadius: 4 }] },
        options: { indexAxis: 'y', maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: ints, y: noGrid } } });

    if (C.changes_by_field.length) make('chFields', { type: 'doughnut', data: { labels: C.changes_by_field.map(function (r) { return r.field; }), datasets: [{ data: C.changes_by_field.map(function (r) { return r.count; }),
        backgroundColor: ['#a02626', '#FEC503', '#0c5f73', '#6b7280', '#c2410c', '#15803d', '#7c3aed', '#be185d', '#0369a1', '#a16207'] }] },
        options: { maintainAspectRatio: false, plugins: { legend: { position: 'right', labels: { boxWidth: 12 } } } } });

    if (C.saves_by_specialty.length) make('chSpec', { type: 'bar', data: { labels: C.saves_by_specialty.map(function (r) { return r.specialty; }), datasets: [{ data: C.saves_by_specialty.map(function (r) { return r.saves; }), backgroundColor: slate, borderRadius: 4 }] },
        options: { indexAxis: 'y', maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: ints, y: noGrid } } });

    make('chCountry', { type: 'bar', data: { labels: C.fellows_by_country.map(function (r) { return r.country; }), datasets: [{ data: C.fellows_by_country.map(function (r) { return r.fellows; }), backgroundColor: gold, borderRadius: 4 }] },
        options: { indexAxis: 'y', maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: ints, y: noGrid } } });
})();
</script>
