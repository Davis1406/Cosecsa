<script>
    // Reusable per-block rich-text editor. Works standalone (the block edit page)
    // or inline, once per block, on the module page. `form` is the
    // <form data-block-editor> element.
    window.LmsBlockEditor = function (form, opts) {
        opts = opts || {};
        if (!form || form.dataset.editorInit) return;
        form.dataset.editorInit = '1';

        const preview = form.querySelector('.preview-inner');
        const status = form.querySelector('.sync-status');
        const csrf = form.querySelector('input[name="_token"]').value;
        const editables = [...form.querySelectorAll('.editable-field')];
        let timer = null;

        const setStatus = (text, state) => {
            if (!status) return;
            status.textContent = text;
            status.dataset.state = state;
        };

        // Copy every rich-text field into its hidden input, then post the whole form.
        const collect = () => {
            editables.forEach(editable => {
                editable.closest('.editor-field').querySelector('[data-text-input]').value = editable.innerHTML;
            });
            return new FormData(form);
        };

        const markPreviewVisible = () => {
            preview.querySelectorAll('[data-animate="1"]').forEach(el => el.classList.add('is-visible'));
        };

        const bindPreviewInteractions = () => {
            preview.querySelectorAll('.flip-card').forEach(card => {
                if (card.dataset.bound) return;
                card.dataset.bound = '1';
                card.addEventListener('click', () => card.classList.toggle('flipped'));
            });

            preview.querySelectorAll('[data-process]').forEach(proc => {
                if (proc.dataset.bound) return;
                proc.dataset.bound = '1';
                const steps = proc.querySelectorAll('.process-step');
                const dots = proc.querySelectorAll('.process-dots span');
                let cur = 0;
                const show = i => {
                    cur = Math.max(0, Math.min(steps.length - 1, i));
                    steps.forEach((s, idx) => s.classList.toggle('active', idx === cur));
                    dots.forEach((d, idx) => d.classList.toggle('active', idx === cur));
                };
                proc.querySelector('[data-process-prev]')?.addEventListener('click', () => show(cur - 1));
                proc.querySelector('[data-process-next]')?.addEventListener('click', () => show(cur + 1));
            });
        };

        const sync = async () => {
            setStatus('Syncing…', 'syncing');
            try {
                const res = await fetch(form.dataset.previewUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'text/html' },
                    body: collect(),
                });
                if (!res.ok) throw new Error('bad response');
                preview.innerHTML = await res.text();
                markPreviewVisible();
                bindPreviewInteractions();
                setStatus('Synced', 'synced');
            } catch (e) {
                setStatus('Sync failed', 'error');
            }
        };
        const scheduleSync = (delay = 220) => {
            clearTimeout(timer);
            timer = setTimeout(sync, delay);
        };

        form.addEventListener('submit', collect);
        document.addEventListener('keydown', (e) => {
            if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 's' &&
                (opts.globalSave || form.contains(document.activeElement))) {
                e.preventDefault();
                collect();
                form.submit();
            }
        });

        // ── Rich text editing ───────────────────────────────────────────────
        const toolbar = form.querySelector('.rt-toolbar');
        const ALLOWED = new Set(['P','BR','STRONG','B','EM','I','U','S','STRIKE','DEL','SUB','SUP','SPAN','DIV','H2','H3','H4','UL','OL','LI','A','BLOCKQUOTE','HR']);
        const DROP = 'script,style,meta,link,title,xml,iframe,object,embed,svg,template';
        let active = null;
        let savedRange = null;

        // Tidy pasted HTML (Word, Google Docs, web pages): keep structure and
        // basic emphasis, drop classes/inline styles/unknown tags.
        const cleanPaste = (html) => {
            const tpl = document.createElement('template');
            tpl.innerHTML = html;
            tpl.content.querySelectorAll(DROP).forEach(n => n.remove());
            const walk = (node) => {
                [...node.childNodes].forEach(child => {
                    if (child.nodeType === Node.COMMENT_NODE) { child.remove(); return; }
                    if (child.nodeType !== Node.ELEMENT_NODE) return;
                    walk(child);
                    if (!ALLOWED.has(child.tagName)) { child.replaceWith(...child.childNodes); return; }
                    [...child.attributes].forEach(a => { if (a.name !== 'href') child.removeAttribute(a.name); });
                });
            };
            walk(tpl.content);
            return tpl.innerHTML;
        };

        const saveSelection = () => {
            const sel = window.getSelection();
            if (sel.rangeCount && active && active.contains(sel.anchorNode)) savedRange = sel.getRangeAt(0).cloneRange();
        };
        const restoreSelection = () => {
            if (!active) return false;
            active.focus();
            if (savedRange) {
                const sel = window.getSelection();
                sel.removeAllRanges();
                sel.addRange(savedRange);
            }
            return true;
        };
        const exec = (cmd, value = null, css = false) => {
            if (!restoreSelection()) return;
            document.execCommand('styleWithCSS', false, css);
            document.execCommand(cmd, false, value);
            afterEdit();
        };
        const afterEdit = () => {
            if (!active) return;
            // Normalise links the toolbar created
            active.querySelectorAll('a[target="_blank"]').forEach(a => a.rel = 'noopener noreferrer');
            saveSelection();
            refreshState();
            scheduleSync();
        };

        const refreshState = () => {
            if (!toolbar) return;
            toolbar.dataset.disabled = active ? '0' : '1';
            toolbar.querySelectorAll('[data-state]').forEach(btn => {
                let on = false;
                try { on = active && document.queryCommandState(btn.dataset.cmd); } catch (e) {}
                btn.classList.toggle('is-active', !!on);
            });
            const block = toolbar.querySelector('[data-rt-block]');
            if (block && active) {
                let v = '';
                try { v = (document.queryCommandValue('formatBlock') || '').toLowerCase(); } catch (e) {}
                block.value = ['h2','h3','h4','blockquote'].includes(v) ? v : 'p';
            }
        };

        editables.forEach(editable => {
            editable.innerHTML = editable.closest('.editor-field').querySelector('[data-text-input]').value;

            editable.addEventListener('focus', () => { active = editable; refreshState(); });
            editable.addEventListener('input', () => { saveSelection(); scheduleSync(); });
            editable.addEventListener('keyup', () => { saveSelection(); refreshState(); });
            editable.addEventListener('mouseup', () => { saveSelection(); refreshState(); });
            editable.addEventListener('keydown', (e) => {
                if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') { e.preventDefault(); openLinkBar(); }
            });
            editable.addEventListener('paste', (e) => {
                const html = e.clipboardData.getData('text/html');
                const text = e.clipboardData.getData('text/plain');
                e.preventDefault();
                document.execCommand('styleWithCSS', false, false);
                html ? document.execCommand('insertHTML', false, cleanPaste(html))
                     : document.execCommand('insertText', false, text);
            });
        });

        if (toolbar) {
            // Keep the text selection when pressing toolbar buttons
            toolbar.addEventListener('mousedown', (e) => {
                if (e.target.closest('button') && !e.target.closest('.rt-linkbar')) e.preventDefault();
            });

            toolbar.querySelectorAll('[data-cmd]').forEach(btn => {
                btn.addEventListener('click', () => exec(btn.dataset.cmd));
            });

            toolbar.querySelector('[data-rt-block]').addEventListener('change', (e) => {
                exec('formatBlock', '<' + e.target.value + '>');
            });

            toolbar.querySelector('[data-rt-size]').addEventListener('change', (e) => {
                const size = e.target.value;
                e.target.value = '';
                if (!size || !restoreSelection()) return;
                // execCommand only knows size 1–7, so mark the selection with
                // size 7 and swap those <font> tags for real pixel sizes.
                document.execCommand('styleWithCSS', false, false);
                document.execCommand('fontSize', false, '7');
                active.querySelectorAll('font[size="7"]').forEach(font => {
                    const span = document.createElement('span');
                    if (size !== 'normal') span.style.fontSize = size;
                    font.querySelectorAll('[style]').forEach(n => n.style.fontSize = '');
                    span.append(...font.childNodes);
                    font.replaceWith(span);
                });
                afterEdit();
            });

            // Colour popovers
            const closePops = () => toolbar.querySelectorAll('[data-pop]').forEach(p => p.hidden = true);
            toolbar.querySelectorAll('[data-rt-pop]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const pop = toolbar.querySelector('[data-pop="' + btn.dataset.rtPop + '"]');
                    const wasHidden = pop.hidden;
                    closePops();
                    pop.hidden = !wasHidden;
                });
            });
            document.addEventListener('click', (e) => { if (!e.target.closest('.rt-pop-wrap')) closePops(); });

            const colorBar = toolbar.querySelector('[data-color-bar]');
            const applyFore = (hex) => {
                exec('foreColor', hex === 'inherit' ? '#1e293b' : hex, true);
                if (hex !== 'inherit' && colorBar) colorBar.style.background = hex;
                closePops();
            };
            toolbar.querySelectorAll('[data-fore]').forEach(b => b.addEventListener('click', () => applyFore(b.dataset.fore)));
            toolbar.querySelector('[data-fore-custom]').addEventListener('change', (e) => applyFore(e.target.value));
            toolbar.querySelectorAll('[data-hilite]').forEach(b => b.addEventListener('click', () => {
                exec('hiliteColor', b.dataset.hilite, true);
                closePops();
            }));

            toolbar.querySelector('[data-rt-clear]').addEventListener('click', () => {
                exec('removeFormat');
                exec('formatBlock', '<p>');
            });

            // Links
            const linkbar = toolbar.querySelector('[data-linkbar]');
            const linkUrl = toolbar.querySelector('[data-link-url]');
            const linkBlank = toolbar.querySelector('[data-link-blank]');
            function openLinkBar() {
                if (!active) return;
                saveSelection();
                const a = window.getSelection().anchorNode?.parentElement?.closest('a');
                linkUrl.value = a ? a.getAttribute('href') : '';
                linkBlank.checked = a ? a.target === '_blank' : true;
                linkbar.hidden = false;
                linkUrl.focus();
            }
            const applyLink = () => {
                const url = linkUrl.value.trim();
                if (!url) return;
                if (!/^(https?:\/\/|mailto:|tel:|\/|#)/i.test(url)) {
                    linkUrl.setCustomValidity('Start with https://, mailto: or tel:');
                    linkUrl.reportValidity();
                    return;
                }
                exec('createLink', url);
                const sel = window.getSelection();
                const a = sel.anchorNode?.parentElement?.closest('a') || active.querySelector('a[href="' + CSS.escape(url) + '"]');
                if (a) {
                    if (linkBlank.checked) { a.target = '_blank'; a.rel = 'noopener noreferrer'; }
                    else { a.removeAttribute('target'); a.removeAttribute('rel'); }
                }
                linkbar.hidden = true;
                afterEdit();
            };
            toolbar.querySelector('[data-rt-link]').addEventListener('click', openLinkBar);
            toolbar.querySelector('[data-link-apply]').addEventListener('click', applyLink);
            toolbar.querySelector('[data-link-remove]').addEventListener('click', () => { exec('unlink'); linkbar.hidden = true; });
            linkUrl.addEventListener('input', () => linkUrl.setCustomValidity(''));
            linkUrl.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') { e.preventDefault(); applyLink(); }
                if (e.key === 'Escape') { linkbar.hidden = true; restoreSelection(); }
            });

            document.addEventListener('selectionchange', () => {
                if (active && active.contains(window.getSelection().anchorNode)) refreshState();
            });
        }

        // ── Image upload ────────────────────────────────────────────────────
        form.querySelectorAll('.image-upload').forEach(input => {
            input.addEventListener('change', async () => {
                if (!input.files.length) return;
                const field = input.closest('.image-field');
                const fd = new FormData();
                fd.append('image', input.files[0]);
                fd.append('path', field.dataset.path);
                setStatus('Uploading…', 'syncing');
                try {
                    const res = await fetch(form.dataset.uploadUrl, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': csrf },
                        body: fd,
                    });
                    if (!res.ok) throw new Error('upload failed');
                    const data = await res.json();
                    const thumb = field.querySelector('[data-preview-img]');
                    if (thumb) {
                        thumb.src = data.url;
                    } else {
                        field.querySelector('.image-field-preview').innerHTML =
                            '<img src="' + data.url + '" alt="" data-preview-img>';
                    }
                    sync();
                } catch (e) {
                    setStatus('Upload failed', 'error');
                } finally {
                    input.value = '';
                }
            });
        });

        // ── Image display settings ──────────────────────────────────────────
        form.querySelectorAll('.image-field').forEach(field => {
            const controls = [...field.querySelectorAll('[data-setting]')];
            const showValue = (input) => {
                const out = input.parentElement.querySelector('output');
                if (out && input.type === 'range') out.textContent = input.value + (input.dataset.unit || '');
            };
            controls.forEach(input => {
                input.addEventListener('input', () => { showValue(input); scheduleSync(120); });
                input.addEventListener('change', () => { showValue(input); scheduleSync(60); });
            });

            field.querySelector('[data-image-reset]')?.addEventListener('click', () => {
                const defaults = { width: 100, align: 'center', radius: 12, border: 0, border_color: '#e2e8f0', shadow: 'none' };
                controls.forEach(input => {
                    const key = input.dataset.setting;
                    if (!(key in defaults)) return;
                    if (input.type === 'radio') input.checked = input.value === defaults[key];
                    else input.value = defaults[key];
                    showValue(input);
                });
                scheduleSync(0);
            });
        });

        // ── Inline (module page) behaviour: Cancel closes the editor ───────
        form.querySelector('[data-editor-cancel]')?.addEventListener('click', () => {
            const host = form.closest('.lms-block');
            if (!host) return; // standalone page: let the link navigate
            host.querySelector('.lms-block-editor').hidden = true;
            host.classList.remove('is-editing');
        });

        markPreviewVisible();
        bindPreviewInteractions();
    };
</script>