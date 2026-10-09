@extends('layout.app')

@section('title', 'Find A Surgeon')

@section('content')
<style>
    #fasHubTabs.nav-tabs .nav-link { color:#a02626; border-color:transparent; font-size:.82rem; padding:.35rem .85rem; }
    #fasHubTabs.nav-tabs .nav-link:hover { color:#841f1f; border-color:#eee #eee #dee2e6; }
    #fasHubTabs.nav-tabs .nav-link.active { color:#fff; background:#a02626; border-color:#a02626 #a02626 #a02626; font-weight:600; }
    body.dark-mode #fasHubTabs.nav-tabs .nav-link { color:#e0a5a5 !important; }
    body.dark-mode #fasHubTabs.nav-tabs .nav-link.active { color:#fff !important; background:#a02626 !important; }
    .fas-muted { color:#6b7280; } body.dark-mode .fas-muted { color:#94a3b8; }
    .fas-pane form.form-inline .form-control { width:auto; max-width:none; }
    .fas-pane form.form-inline input[type=text] { field-sizing:content; min-width:8ch; }
    .fas-pane.loading { opacity:.5; pointer-events:none; transition:opacity .15s; }
    @media print {
        .main-sidebar, .main-header, #fasHubTabs, .fas-noprint, .main-footer { display:none !important; }
        .content-wrapper { margin-left:0 !important; }
    }
</style>
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <h1 style="font-size:1.4rem;">Find A Surgeon</h1>
            <p class="text-muted mb-0" style="font-size:.85rem;">The patient app and the Fellow directory: who is using it, what Fellows are changing, and the hospitals waiting for review.</p>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            @include('_message')

            @php
                $tabs = [
                    'overview'  => ['Overview & Report', 'fa-chart-pie'],
                    'patients'  => ['Patients', 'fa-users'],
                    'fellows'   => ['Fellows', 'fa-user-md'],
                    'hospitals' => ['Hospital Review', 'fa-hospital'],
                    'changes'   => ['Profile Changes', 'fa-history'],
                ];
            @endphp
            <ul class="nav nav-tabs mb-3" id="fasHubTabs" role="tablist">
                @foreach ($tabs as $key => [$label, $icon])
                    <li class="nav-item">
                        <a class="nav-link {{ $active === $key ? 'active' : '' }}" id="fas-tab-{{ $key }}-trigger" data-toggle="tab" data-key="{{ $key }}"
                           href="#fas-tab-{{ $key }}" role="tab"><i class="fas {{ $icon }} mr-1"></i> {{ $label }}</a>
                    </li>
                @endforeach
            </ul>

            <div class="tab-content" id="fasHubContent">
                @foreach ($tabs as $key => $t)
                    <div class="tab-pane fade fas-pane {{ $active === $key ? 'show active' : '' }}" id="fas-tab-{{ $key }}" role="tabpanel" data-key="{{ $key }}">
                        @if ($active === $key)
                            @include($pane, $paneData)
                        @else
                            <p class="fas-muted py-4 text-center"><i class="fas fa-spinner fa-spin mr-1"></i> Loading…</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var base = @json(url('admin/find-a-surgeon'));
    var active = @json($active);
    var urls = {}, loaded = {};
    var paths = { overview: '', patients: '/patients', fellows: '/fellows', hospitals: '/hospitals', changes: '/changes' };
    Object.keys(paths).forEach(function (k) { urls[k] = base + paths[k]; });
    loaded[active] = true; urls[active] = window.location.href;

    function keyFor(href) {
        var a = document.createElement('a'); a.href = href;
        var p = a.pathname.replace(/\/+$/, ''), b = new URL(base, window.location.origin).pathname.replace(/\/+$/, '');
        if (p.indexOf(b) !== 0) { return null; }
        var rest = p.slice(b.length).split('/')[1] || '';
        return rest === '' ? 'overview' : ((paths.hasOwnProperty(rest) && rest !== 'overview') ? rest : null);
    }

    function load(key, url) {
        url = url.replace(/([?&])partial=1&?/, '$1').replace(/[?&]$/, '');
        var $p = $('#fas-tab-' + key).addClass('loading');
        $.ajax({ url: url, data: { partial: 1 }, dataType: 'html' }).done(function (html) {
            $p.html(html); loaded[key] = true; urls[key] = url;
            if ($('#fasHubTabs a.active').data('key') === key) { history.replaceState(null, '', url); }
        }).fail(function () {
            $p.html('<div class="alert alert-danger">Could not load this tab. <a href="#" class="fas-retry" data-key="' + key + '">Try again</a></div>');
        }).always(function () { $p.removeClass('loading'); });
    }

    $('#fasHubTabs a[data-toggle="tab"]').on('show.bs.tab', function () {
        var key = $(this).data('key');
        if (!loaded[key]) { load(key, urls[key]); } else { history.replaceState(null, '', urls[key]); }
    });

    $(document).on('click', '.fas-retry', function (e) { e.preventDefault(); load($(this).data('key'), urls[$(this).data('key')]); });

    // Filters and pagination stay inside the tab.
    $('#fasHubContent').on('submit', 'form[method="get"]', function (e) {
        var $f = $(this), key = $f.closest('.fas-pane').data('key');
        e.preventDefault();
        load(key, urls[key].split('?')[0] + '?' + $f.serialize());
    });
    // Plain links to another page of this section open in the matching tab.
    $('#fasHubContent').on('click', 'a[href]', function (e) {
        var href = this.href, key = keyFor(href), $a = $(this);
        if (!key || $a.attr('data-toggle') || $a.attr('target') || $a.hasClass('fas-retry') || e.ctrlKey || e.metaKey) { return; }
        e.preventDefault();
        var cur = $('#fasHubTabs a.active').data('key');
        if (key === cur) { load(key, href); }
        else { loaded[key] = false; urls[key] = href; $('#fas-tab-' + key + '-trigger').tab('show'); }
    });
})();
</script>
@endpush
