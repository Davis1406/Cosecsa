{{-- Scales the 1440x810 certificate to its container and powers the PNG download. --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
(function () {
    var fit = document.getElementById('lmscertFit');
    var stage = document.getElementById('lmscertStage');

    function fitCertificate() {
        var scale = Math.min(1, fit.parentElement.clientWidth / 1440);
        stage.style.transform = 'scale(' + scale + ')';
        fit.style.height = (810 * scale) + 'px';
    }
    window.addEventListener('resize', fitCertificate);
    window.addEventListener('beforeprint', function () { stage.style.transform = 'none'; });
    window.addEventListener('afterprint', fitCertificate);
    fitCertificate();

    window.downloadCertificatePng = function (button, filename) {
        var original = button.textContent;
        button.disabled = true;
        button.textContent = 'Rendering…';
        var ready = (document.fonts && document.fonts.ready) ? document.fonts.ready : Promise.resolve();
        ready.then(function () {
            return html2canvas(document.getElementById('lmscert'), {
                scale: 2, useCORS: true, backgroundColor: '#ffffff', logging: false,
                // render at full size, not at the on-screen scale
                onclone: function (doc) { doc.getElementById('lmscertStage').style.transform = 'none'; }
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
})();
</script>
