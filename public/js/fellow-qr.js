/*
 * QR codes for the Fellows' certificates, drawn in the browser (nothing is sent anywhere). Needs qrcode-generator.
 * One place decides the quality of what is downloaded:
 *   - SVG: vector, so it prints sharp at any size;
 *   - PNG: at least 4000 px a side, a whole number of pixels per module, so every edge is razor sharp;
 *   - error correction 'Q', still scans with a quarter of the code damaged or printed small;
 *   - a quiet zone of 4 modules, as the QR standard requires, on white.
 */
window.FellowQR = (function () {
    var QUIET = 4, PNG_MIN = 4000;

    function make(text) {
        var qr = qrcode(0, 'Q');
        qr.addData(text);
        qr.make();
        var n = qr.getModuleCount(), size = n + QUIET * 2;

        // px given: a fixed-size file; none: scales to its container (for the page)
        function svg(px) {
            var d = '';
            for (var r = 0; r < n; r++) for (var c = 0; c < n; c++) if (qr.isDark(r, c)) d += 'M' + (c + QUIET) + ' ' + (r + QUIET) + 'h1v1h-1z';
            return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' + size + ' ' + size + '"' +
                (px ? ' width="' + px + '" height="' + px + '"' : ' style="width:100%;height:auto;display:block"') +
                ' shape-rendering="crispEdges"><rect width="' + size + '" height="' + size + '" fill="#fff"/><path d="' + d + '" fill="#000"/></svg>';
        }

        var cell = Math.ceil(PNG_MIN / size);
        function png() {
            return new Promise(function (resolve, reject) {
                var cv = document.createElement('canvas'), ctx;
                cv.width = cv.height = cell * size;
                ctx = cv.getContext('2d');
                ctx.fillStyle = '#fff'; ctx.fillRect(0, 0, cv.width, cv.height);
                ctx.fillStyle = '#000';
                for (var r = 0; r < n; r++) for (var c = 0; c < n; c++) if (qr.isDark(r, c)) ctx.fillRect((c + QUIET) * cell, (r + QUIET) * cell, cell, cell);
                cv.toBlob(function (b) { cv.width = cv.height = 0; b ? resolve(b) : reject(new Error('The PNG could not be made.')); }, 'image/png');
            });
        }

        return { svg: svg, png: png, pixels: cell * size };
    }

    function slug(s) {
        return String(s || '').normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '') || 'fellow';
    }

    // The file name for a person's QR: just their name as written (First Last, or First Middle Last), without the characters
    // a file name cannot hold. `used` (an object kept by the caller) keeps two people with the same name apart: "Name (2)".
    function fileBase(name, used) {
        var base = String(name || '').replace(/[\\/:*?"<>|\u0000-\u001f]/g, '').replace(/\s+/g, ' ').trim().replace(/^\.+/, '') || 'Fellow', n = base, i = 2;
        if (used) { while (used[n.toLowerCase()]) { n = base + ' (' + (i++) + ')'; } used[n.toLowerCase()] = 1; }
        return n;
    }

    function save(blob, filename) {
        var a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = filename;
        document.body.appendChild(a); a.click(); a.remove();
        setTimeout(function () { URL.revokeObjectURL(a.href); }, 4000);
    }

    return { make: make, slug: slug, fileBase: fileBase, save: save, PNG_MIN: PNG_MIN };
})();
