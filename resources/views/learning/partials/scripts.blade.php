<script>
(function () {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const root = document.querySelector('.pm-content');
    if (!root) return;

    // ── Scroll entrance animations ─────────────────────────────────────────
    // Blocks that enter together are staggered so they cascade in rather than
    // appearing all at once.
    (function () {
        const blocks = [...root.querySelectorAll('.block-reveal[data-animate="1"]')];
        const reveal = (el, delay) => {
            el.style.transitionDelay = delay + 'ms';
            el.classList.add('is-visible');
            setTimeout(() => { el.style.transitionDelay = ''; }, delay + 900);
        };

        if (reduceMotion || !('IntersectionObserver' in window)) {
            blocks.forEach(el => el.classList.add('is-visible'));
            return;
        }

        const io = new IntersectionObserver((entries) => {
            entries
                .filter(e => e.isIntersecting)
                .sort((a, b) => a.boundingClientRect.top - b.boundingClientRect.top)
                .forEach((entry, i) => {
                    reveal(entry.target, Math.min(i, 4) * 90);
                    io.unobserve(entry.target);
                });
        }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });

        blocks.forEach(el => io.observe(el));
    })();

    // ── Flashcards ─────────────────────────────────────────────────────────
    root.querySelectorAll('.flip-card').forEach(card => {
        const flip = () => {
            card.classList.toggle('flipped');
            card.setAttribute('aria-pressed', card.classList.contains('flipped'));
        };
        card.addEventListener('click', flip);
        card.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); flip(); }
        });
    });

    // ── Process steppers ───────────────────────────────────────────────────
    root.querySelectorAll('[data-process]').forEach(widget => {
        const steps = widget.querySelectorAll('.process-step');
        const dots = widget.querySelectorAll('.process-dots span');
        const prev = widget.querySelector('[data-process-prev]');
        const next = widget.querySelector('[data-process-next]');
        let current = 0;

        const show = (i) => {
            current = Math.max(0, Math.min(steps.length - 1, i));
            steps.forEach((s, idx) => s.classList.toggle('active', idx === current));
            dots.forEach((d, idx) => {
                d.classList.toggle('active', idx === current);
                d.classList.toggle('seen', idx < current);
            });
            if (prev) prev.disabled = current === 0;
            if (next) next.disabled = current === steps.length - 1;
        };

        if (prev) prev.addEventListener('click', () => show(current - 1));
        if (next) next.addEventListener('click', () => show(current + 1));
        show(0);
    });

    // ── Labeled graphics ───────────────────────────────────────────────────
    root.querySelectorAll('[data-labeledgraphic]').forEach(widget => {
        const markers = [...widget.querySelectorAll('[data-lg-marker]')];
        const pop = widget.querySelector('[data-lg-pop]');
        const dataEl = widget.querySelector('[data-lg-data]');
        if (!pop || !dataEl) return;

        const data = JSON.parse(dataEl.textContent || '[]');
        const title = widget.querySelector('[data-lg-title]');
        const desc = widget.querySelector('[data-lg-desc]');
        const wrapEl = widget.querySelector('.lg-wrap');

        const hide = () => {
            pop.hidden = true;
            markers.forEach(m => m.classList.remove('active'));
        };
        const show = (i) => {
            const m = markers[i];
            const d = data[i] || {};
            title.innerHTML = d.title || '';
            desc.innerHTML = d.description || '';
            markers.forEach(x => x.classList.toggle('active', x === m));
            pop.hidden = false;

            const rect = m.getBoundingClientRect();
            const wrap = wrapEl.getBoundingClientRect();
            const popW = Math.min(260, wrap.width - 16);
            pop.style.width = popW + 'px';
            pop.style.left = Math.max(8, Math.min(rect.left - wrap.left + 20, wrap.width - popW - 8)) + 'px';
            pop.style.top = (rect.top - wrap.top + 20) + 'px';
        };

        markers.forEach((m, i) => m.addEventListener('click', (e) => {
            e.stopPropagation();
            m.classList.contains('active') && !pop.hidden ? hide() : show(i);
        }));
        widget.querySelector('[data-lg-close]')?.addEventListener('click', hide);
        widget.addEventListener('click', (e) => {
            if (!e.target.closest('[data-lg-marker]') && !e.target.closest('[data-lg-pop]')) hide();
        });
    });

    // ── Checklists (remembered per learner in this browser) ────────────────
    root.querySelectorAll('[data-checklist]').forEach(list => {
        const key = 'cosecsa:checklist:{{ auth()->id() }}:' + list.dataset.checklist;
        const items = [...list.querySelectorAll('li[role="checkbox"]')];
        const fill = list.querySelector('[data-checklist-fill]');
        const count = list.querySelector('[data-checklist-count]');

        let saved = [];
        try { saved = JSON.parse(localStorage.getItem(key) || '[]'); } catch (e) {}

        const render = () => {
            const done = items.filter(li => li.classList.contains('checked')).length;
            fill.style.transform = 'scaleX(' + (items.length ? done / items.length : 0) + ')';
            count.textContent = done === items.length && done > 0
                ? 'All ' + done + ' checked'
                : done + ' of ' + items.length + ' checked';
            list.classList.toggle('is-complete', done === items.length && done > 0);
        };
        const set = (li, on) => {
            li.classList.toggle('checked', on);
            li.setAttribute('aria-checked', on);
        };
        const persist = () => {
            try {
                localStorage.setItem(key, JSON.stringify(items.map((li, i) => li.classList.contains('checked') ? i : -1).filter(i => i >= 0)));
            } catch (e) {}
        };

        items.forEach((li, i) => {
            set(li, saved.includes(i));
            const toggle = (e) => {
                if (e.target.closest('a')) return;
                set(li, !li.classList.contains('checked'));
                render();
                persist();
            };
            li.addEventListener('click', toggle);
            li.addEventListener('keydown', (e) => {
                if (e.key === ' ' || e.key === 'Enter') { e.preventDefault(); toggle(e); }
            });
        });
        render();
    });

    // ── Image lightbox ─────────────────────────────────────────────────────
    (function () {
        const box = document.getElementById('lightbox');
        if (!box) return;

        const img = box.querySelector('[data-lb-img]');
        const stage = box.querySelector('[data-lb-stage]');
        const caption = box.querySelector('[data-lb-caption]');
        const counter = box.querySelector('[data-lb-count]');
        const prevBtn = box.querySelector('[data-lb-prev]');
        const nextBtn = box.querySelector('[data-lb-next]');
        const closeBtn = box.querySelector('[data-lb-close]');
        const zoomBtn = box.querySelector('[data-lb-zoom]');

        // Flashcard faces flip on click, so they are not previewable.
        const images = [...root.querySelectorAll('.block img')].filter(el => !el.closest('.flip-card'));
        if (!images.length) return;

        const captionFor = (el) => {
            const block = el.closest('.block');
            const node = el.closest('figure')?.querySelector('figcaption')
                || el.closest('.process-step')?.querySelector('.step-title')
                || block?.querySelector('.img-caption, .aside-caption, .overlay-caption, .process-title');
            return node ? node.textContent.trim() : '';
        };

        let index = 0;
        let lastFocus = null;

        const render = () => {
            const el = images[index];
            stage.classList.remove('zoomed');
            img.classList.remove('is-in');
            img.src = el.currentSrc || el.src;
            const text = captionFor(el);
            caption.textContent = text;
            caption.hidden = !text;
            counter.textContent = images.length > 1 ? (index + 1) + ' / ' + images.length : '';
            prevBtn.hidden = nextBtn.hidden = images.length < 2;
            requestAnimationFrame(() => img.classList.add('is-in'));
        };
        const open = (i) => {
            index = i;
            lastFocus = document.activeElement;
            render();
            box.hidden = false;
            document.documentElement.classList.add('lb-lock');
            requestAnimationFrame(() => box.classList.add('is-open'));
            closeBtn.focus();
        };
        const close = () => {
            box.classList.remove('is-open');
            document.documentElement.classList.remove('lb-lock');
            setTimeout(() => { box.hidden = true; img.removeAttribute('src'); }, reduceMotion ? 0 : 220);
            lastFocus?.focus?.();
        };
        const go = (step) => { index = (index + step + images.length) % images.length; render(); };
        const toggleZoom = () => stage.classList.toggle('zoomed');

        images.forEach((el, i) => {
            const target = el.closest('.block-image-overlay') || el;
            target.classList.add('is-zoomable');
            target.setAttribute('tabindex', '0');
            target.setAttribute('role', 'button');
            target.setAttribute('aria-label', 'Open image preview' + (captionFor(el) ? ': ' + captionFor(el) : ''));

            const activate = (e) => {
                if (e.target.closest('a, button')) return;
                // Clicking a labeled graphic with an open hotspot closes the hotspot first.
                const lg = el.closest('[data-labeledgraphic]');
                if (lg && !lg.querySelector('[data-lg-pop]').hidden) return;
                open(i);
            };
            target.addEventListener('click', activate);
            target.addEventListener('keydown', (e) => {
                if (e.target === target && (e.key === 'Enter' || e.key === ' ')) { e.preventDefault(); open(i); }
            });
        });

        closeBtn.addEventListener('click', close);
        prevBtn.addEventListener('click', () => go(-1));
        nextBtn.addEventListener('click', () => go(1));
        zoomBtn.addEventListener('click', toggleZoom);
        img.addEventListener('click', toggleZoom);
        box.addEventListener('click', (e) => { if (e.target === box || e.target === stage) close(); });

        document.addEventListener('keydown', (e) => {
            if (box.hidden) return;
            if (e.key === 'Escape') close();
            else if (e.key === 'ArrowLeft' && images.length > 1) go(-1);
            else if (e.key === 'ArrowRight' && images.length > 1) go(1);
            else if (e.key === 'Tab') {
                const focusable = [...box.querySelectorAll('button')].filter(b => !b.hidden);
                const first = focusable[0], last = focusable[focusable.length - 1];
                if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
                else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
            }
        });

        // Swipe between images on touch screens
        let startX = null;
        stage.addEventListener('touchstart', (e) => { startX = e.touches[0].clientX; }, { passive: true });
        stage.addEventListener('touchend', (e) => {
            if (startX === null || stage.classList.contains('zoomed')) return;
            const dx = e.changedTouches[0].clientX - startX;
            if (Math.abs(dx) > 50 && images.length > 1) go(dx < 0 ? 1 : -1);
            startX = null;
        });
    })();
})();
</script>
