{{--
  Body-only shell of a coded course page, for the admin's live preview (an iframe next to the wording editor).
  The page's pieces of wording carry data-cwkey; the editor sends the text being typed and this frame shows it at once.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page preview</title>
    <style>
        html, body { margin: 0; background: #f6f5ef; }
        [data-cwkey] { transition: background .3s; }
        [data-cwkey].cw-flash { background: #fff3b0 !important; outline: 2px solid #f59e0b; outline-offset: 2px; border-radius: 3px; }
        body.cw-frame [data-cwkey] { cursor: pointer; }
    </style>
    @stack('styles')
</head>
<body class="cw-frame">
    @yield('course-shell')
    @stack('scripts')
    <script>
    (function () {
        var timer;
        function el(key) { return document.querySelector('[data-cwkey="' + key + '"]'); }
        window.addEventListener('message', function (e) {
            if (e.origin !== location.origin) return;
            var d = e.data || {}, t = d.key ? el(d.key) : null;
            if (!t) return;
            if (d.type === 'cw-set') { t.innerHTML = d.html; }
            if (d.type === 'cw-focus') {
                var card = t.closest('.topic');
                if (card && !card.classList.contains('open')) { var h = card.querySelector('.th'); if (h) h.click(); }
                setTimeout(function () { t.scrollIntoView({ block: 'center', behavior: 'smooth' }); }, 60);
                document.querySelectorAll('.cw-flash').forEach(function (x) { x.classList.remove('cw-flash'); });
                t.classList.add('cw-flash'); clearTimeout(timer); timer = setTimeout(function () { t.classList.remove('cw-flash'); }, 2500);
            }
        });
        // clicking wording in the preview jumps to its field in the editor
        document.addEventListener('click', function (e) {
            var t = e.target.closest('[data-cwkey]');
            if (t && window.parent !== window) window.parent.postMessage({ type: 'cw-click', key: t.getAttribute('data-cwkey') }, location.origin);
        });
    })();
    </script>
</body>
</html>
