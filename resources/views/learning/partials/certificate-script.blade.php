{{-- Scales the 1440x810 certificate to its container, powers the PNG download and the confetti burst. --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
(function () {
    function fitOne(fit) {
        var stage = fit.querySelector('.lmscert-stage');
        var width = fit.parentElement.clientWidth;
        if (!stage || !width) { return; } // e.g. a hidden modal

        // Fit both the width of the container and the height of the screen.
        var top = fit.getAttribute('data-fit-top');
        top = top !== null ? parseFloat(top) : fit.getBoundingClientRect().top + window.scrollY;
        var byHeight = (window.innerHeight - top - 20) / 810;
        var scale = Math.max(0.2, Math.min(1, width / 1440, byHeight));
        stage.style.transform = 'scale(' + scale + ')';
        // centre it when the height limit makes it narrower than its container
        stage.style.marginLeft = Math.max(0, (width - 1440 * scale) / 2) + 'px';
        fit.style.height = (810 * scale) + 'px';
    }
    function fitCertificate() { document.querySelectorAll('.lmscert-fit').forEach(fitOne); }
    window.lmsFitCertificate = fitCertificate;
    window.addEventListener('resize', fitCertificate);
    fitCertificate();

    // Print a clean full-size copy of the certificate that is on screen (see lms-certificate.css).
    window.addEventListener('beforeprint', function () {
        var source = document.querySelector('.modal.show .lmscert') || document.querySelector('.lmscert');
        if (!source) { return; }
        var holder = document.createElement('div');
        holder.id = 'lmscertPrint';
        var copy = source.cloneNode(true);
        copy.removeAttribute('id');
        holder.appendChild(copy);
        document.body.appendChild(holder);
    });
    window.addEventListener('afterprint', function () {
        var holder = document.getElementById('lmscertPrint');
        if (holder) { holder.remove(); }
    });

    window.downloadCertificatePng = function (button, filename) {
        var scope = button.closest('.lmscert-modal') || document;
        var cert = scope.querySelector('.lmscert');
        var original = button.textContent;
        button.disabled = true;
        button.textContent = 'Rendering…';
        var ready = (document.fonts && document.fonts.ready) ? document.fonts.ready : Promise.resolve();
        ready.then(function () {
            return html2canvas(cert, {
                scale: 2, useCORS: true, backgroundColor: '#ffffff', logging: false,
                // render at full size, not at the on-screen scale
                onclone: function (doc) {
                    doc.querySelectorAll('.lmscert-stage').forEach(function (s) { s.style.transform = 'none'; s.style.marginLeft = '0'; });
                }
            });
        }).then(function (canvas) {
            var a = document.createElement('a');
            a.download = filename;
            a.href = canvas.toDataURL('image/png');
            document.body.appendChild(a); a.click(); a.remove();
        }).catch(function () {
            alert('Sorry, the PNG could not be generated.');
        }).then(function () {
            button.disabled = false;
            button.textContent = original;
        });
    };

    // Load the certificate (an HTML fragment) into #certModal, open it and celebrate.
    // Resolves once the modal is open; rejects if the certificate could not be loaded.
    window.lmsOpenCertificateModal = function (url) {
        return fetch(url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (res) {
                if (!res.ok) { throw new Error('certificate failed'); }
                return res.text();
            })
            .then(function (html) {
                document.getElementById('certModalBody').innerHTML = html;
                var $modal = window.jQuery('#certModal');
                $modal.one('shown.bs.modal', function () { fitCertificate(); window.lmsConfetti(); });
                $modal.modal('show');
            });
    };

    // A short confetti burst over the whole screen (skipped for reduced-motion users).
    window.lmsConfetti = function () {
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }

        var canvas = document.createElement('canvas');
        canvas.style.cssText = 'position:fixed;inset:0;width:100%;height:100%;z-index:2000;pointer-events:none;';
        var w = canvas.width = window.innerWidth, h = canvas.height = window.innerHeight;
        document.body.appendChild(canvas);
        var ctx = canvas.getContext('2d');

        var colors = ['#C99834', '#F3D27A', '#99060A', '#500E19', '#FFFFFF', '#2d3748'];
        var pieces = [];
        for (var i = 0; i < 160; i++) {
            pieces.push({
                x: w / 2 + (Math.random() - 0.5) * w * 0.3, y: h * 0.35,
                vx: (Math.random() - 0.5) * 16, vy: -Math.random() * 14 - 4,
                size: 6 + Math.random() * 7, rot: Math.random() * Math.PI, vr: (Math.random() - 0.5) * 0.4,
                color: colors[i % colors.length], round: Math.random() < 0.3
            });
        }

        var start = performance.now(), duration = 4200;
        (function frame(now) {
            var t = now - start;
            ctx.clearRect(0, 0, w, h);
            pieces.forEach(function (p) {
                p.vy += 0.32; p.vx *= 0.992;
                p.x += p.vx; p.y += p.vy; p.rot += p.vr;
                ctx.save();
                ctx.globalAlpha = Math.max(0, Math.min(1, (duration - t) / 900));
                ctx.translate(p.x, p.y); ctx.rotate(p.rot); ctx.fillStyle = p.color;
                if (p.round) { ctx.beginPath(); ctx.arc(0, 0, p.size / 2, 0, 6.3); ctx.fill(); }
                else { ctx.fillRect(-p.size / 2, -p.size / 4, p.size, p.size / 2); }
                ctx.restore();
            });
            if (t < duration) { requestAnimationFrame(frame); } else { canvas.remove(); }
        })(start);
    };
})();
</script>
