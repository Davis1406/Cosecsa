{{--
  Body-only shell of a coded course page, for the admin's live preview (an iframe next to the wording editor).
  Blocks and topic cards carry data-bid / data-tid; the editor reloads this frame as the page changes and focuses/highlights the block being edited.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page preview</title>
    <style>
        html, body { margin: 0; background: #f6f5ef; }
        [data-bid], [data-tid] { transition: background .3s, outline-color .3s; }
        [data-bid].cw-flash, [data-tid].cw-flash { outline: 3px solid #f59e0b; outline-offset: 3px; border-radius: 4px; }
        body.cw-frame [data-bid] { cursor: pointer; }
        body.cw-frame [data-bid]:hover { outline: 2px dashed rgba(245,158,11,.6); outline-offset: 2px; }
    </style>
    @stack('styles')
</head>
<body class="cw-frame">
    @yield('course-shell')
    @stack('scripts')
    <script>
    (function () {
        var timer;
        function find(d) {
            if (d.bid) return document.querySelector('[data-bid="' + d.bid + '"]');
            if (d.tid) return document.querySelector('[data-tid="' + d.tid + '"]');
            return null;
        }
        window.addEventListener('message', function (e) {
            if (e.origin !== location.origin) return;
            var d = e.data || {};
            if (d.type !== 'cw-focus') return;
            var t = find(d); if (!t) return;
            var card = t.closest('.topic');
            if (card && !card.classList.contains('open')) { var h = card.querySelector('.th'); if (h) h.click(); }
            setTimeout(function () { t.scrollIntoView({ block: 'center', behavior: 'smooth' }); }, 80);
            document.querySelectorAll('.cw-flash').forEach(function (x) { x.classList.remove('cw-flash'); });
            t.classList.add('cw-flash'); clearTimeout(timer); timer = setTimeout(function () { t.classList.remove('cw-flash'); }, 2200);
        });
        // clicking a block (or a topic header) in the preview selects it in the editor
        document.addEventListener('click', function (e) {
            if (window.parent === window) return;
            var b = e.target.closest('[data-bid]'), t = e.target.closest('[data-tid]');
            if (b || t) window.parent.postMessage({ type: 'cw-click', bid: b ? b.getAttribute('data-bid') : null, tid: t ? t.getAttribute('data-tid') : null }, location.origin);
        });
    })();
    </script>
</body>
</html>
