(function ($) {
    "use strict";

    // ── Only run stepper logic when the multi-step form is present ──────────
    if (!$('#msform').length) return;

    // ── Register easeInOutBack if jQuery UI didn't ship with it ─────────────
    if ($.easing && !$.easing.easeInOutBack) {
        $.easing.easeInOutBack = function (x, t, b, c, d, s) {
            if (s === undefined) s = 1.70158;
            if ((t /= d / 2) < 1) return c / 2 * (t * t * (((s *= 1.525) + 1) * t - s)) + b;
            return c / 2 * ((t -= 2) * t * (((s *= 1.525) + 1) * t + s) + 2) + b;
        };
    }

    var animating = false;

    // ── NEXT ─────────────────────────────────────────────────────────────────
    $(document).on('click', '#msform .next', function () {
        if (animating) return false;

        var current_fs = $(this).closest('fieldset');
        var next_fs    = current_fs.next('fieldset');
        if (!next_fs.length) return false;

        animating = true;
        $("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");

        next_fs.show();
        current_fs.animate({ opacity: 0 }, {
            step: function (now) {
                var scale   = 1 - (1 - now) * 0.2;
                var left    = (now * 50) + "%";
                var opacity = 1 - now;
                current_fs.css({ transform: 'scale(' + scale + ')', position: 'absolute' });
                next_fs.css({ left: left, opacity: opacity });
            },
            duration: 600,
            complete: function () {
                current_fs.hide();
                animating = false;
            },
            easing: $.easing.easeInOutBack ? 'easeInOutBack' : 'swing'
        });
    });

    // ── PREVIOUS ─────────────────────────────────────────────────────────────
    $(document).on('click', '#msform .previous', function () {
        if (animating) return false;

        var current_fs  = $(this).closest('fieldset');
        var previous_fs = current_fs.prev('fieldset');
        if (!previous_fs.length) return false;

        animating = true;
        $("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active");

        previous_fs.show();
        current_fs.animate({ opacity: 0 }, {
            step: function (now) {
                var scale   = 0.8 + (1 - now) * 0.2;
                var left    = ((1 - now) * 50) + "%";
                var opacity = 1 - now;
                current_fs.css({ left: left });
                previous_fs.css({ transform: 'scale(' + scale + ')', opacity: opacity });
            },
            duration: 600,
            complete: function () {
                current_fs.hide();
                animating = false;
            },
            easing: $.easing.easeInOutBack ? 'easeInOutBack' : 'swing'
        });
    });

    // ── Custom file input label update (guard against missing element) ───────
    var fileInput = document.querySelector('#msform .custom-file-input');
    if (fileInput) {
        fileInput.addEventListener('change', function (e) {
            var file = e.target.files && e.target.files[0];
            var label = e.target.nextElementSibling;
            if (label && file) label.innerText = file.name;
        });
    }

})(jQuery);
