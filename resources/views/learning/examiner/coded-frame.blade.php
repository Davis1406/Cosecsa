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
        // a page with a player (Module 8) shows copies of the blocks and hides the originals: prefer the visible one
        function find(d) {
            var sel = d.bid ? '[data-bid="' + d.bid + '"]' : d.tid ? '[data-tid="' + d.tid + '"]' : null;
            if (!sel) return null;
            var all = document.querySelectorAll(sel);
            for (var i = 0; i < all.length; i++) if (all[i].getClientRects().length) return all[i];
            return all[0] || null;
        }
        function focusOn(t) {
            var card = t.closest('.topic');
            if (card && t.getClientRects().length && !card.classList.contains('open')) { var h = card.querySelector('.th'); if (h) h.click(); }
            setTimeout(function () { t.scrollIntoView({ block: 'center', behavior: 'smooth' }); }, 80);
            document.querySelectorAll('.cw-flash').forEach(function (x) { x.classList.remove('cw-flash'); });
            t.classList.add('cw-flash'); clearTimeout(timer); timer = setTimeout(function () { t.classList.remove('cw-flash'); }, 2200);
        }
        window.addEventListener('message', function (e) {
            if (e.origin !== location.origin) return;
            var d = e.data || {};
            if (d.type !== 'cw-focus') return;
            var t = find(d); if (!t) return;
            if (!t.getClientRects().length) {
                // hidden original: let the player show the step that holds it, then focus the visible copy
                var topic = t.closest('.topic');
                if (topic) document.dispatchEvent(new CustomEvent('cw-reveal', { detail: { k: [].indexOf.call(document.querySelectorAll('.topic'), topic) } }));
                setTimeout(function () { var v = find(d); if (v) focusOn(v); }, 450);
                return;
            }
            focusOn(t);
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
