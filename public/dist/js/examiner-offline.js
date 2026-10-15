/* COSECSA examiner offline marking.
 *
 * Loaded on the examiner dashboard and marking forms (see
 * resources/views/examiner/_offline.blade.php). It:
 *
 *   1. registers /examiner-sw.js so the marking pages and candidate lists
 *      stay available without a connection;
 *   2. "prepares" the device by fetching those pages/lists while online;
 *   3. takes over submit on forms marked data-offline-submit: online it posts
 *      the mark as JSON, offline (or if the request cannot reach the server)
 *      it queues the mark in IndexedDB;
 *   4. syncs queued marks, in order, once the connection and session are back.
 *
 * Syncing is safe to repeat: the marking endpoints reject a second mark for
 * the same candidate/station/format with a "duplicate" status, which the queue
 * treats as already saved. Queued marks belong to the examiner who entered
 * them and are only ever sent from that examiner's own session.
 */
(function () {
  'use strict';

  var cfg = window.EXAMINER_OFFLINE;
  if (!cfg || !cfg.userId) return;

  var DB_NAME = 'cosecsa-examiner';
  var DB_VERSION = 1;
  var STORE = 'queue';
  var SYNC_INTERVAL_MS = 30000;
  var WARM_MAX_AGE_MS = 20 * 60 * 1000;
  var WARM_CONCURRENCY = 3;
  var WARM_KEY = 'examiner-offline-warm-v2'; // bump to make every device re-download the offline pages
  var FLASH_KEY = 'examiner-offline-flash';

  var state = { auth: false, syncing: false, warming: false, warmError: null };
  var ui = {};

  // ── IndexedDB queue ─────────────────────────────────────────────────────

  var dbPromise = null;

  function openDb() {
    if (!dbPromise) {
      dbPromise = new Promise(function (resolve, reject) {
        if (!window.indexedDB) {
          reject(new Error('IndexedDB is not available'));
          return;
        }
        var request = indexedDB.open(DB_NAME, DB_VERSION);
        request.onupgradeneeded = function () {
          var db = request.result;
          if (!db.objectStoreNames.contains(STORE)) db.createObjectStore(STORE, { keyPath: 'id' });
        };
        request.onsuccess = function () { resolve(request.result); };
        request.onerror = function () { reject(request.error); };
      });
      dbPromise.catch(function () { dbPromise = null; });
    }
    return dbPromise;
  }

  function withStore(mode, action) {
    return openDb().then(function (db) {
      return new Promise(function (resolve, reject) {
        var tx = db.transaction(STORE, mode);
        var result;
        var request = action(tx.objectStore(STORE));
        if (request) request.onsuccess = function () { result = request.result; };
        tx.oncomplete = function () { resolve(result); };
        tx.onabort = tx.onerror = function () { reject(tx.error); };
      });
    });
  }

  function queueAll() {
    return withStore('readonly', function (store) { return store.getAll(); }).then(function (rows) {
      return (rows || [])
        .filter(function (row) { return row.userId === cfg.userId; })
        .sort(function (a, b) { return a.createdAt - b.createdAt; });
    });
  }
  function queuePut(item) { return withStore('readwrite', function (store) { return store.put(item); }); }
  function queueDelete(id) { return withStore('readwrite', function (store) { return store.delete(id); }); }

  // ── Small helpers ───────────────────────────────────────────────────────

  function uuid() {
    if (window.crypto && crypto.randomUUID) return crypto.randomUUID();
    return 'm-' + Date.now().toString(36) + '-' + Math.random().toString(36).slice(2, 10);
  }

  function el(tag, attrs, children) {
    var node = document.createElement(tag);
    Object.keys(attrs || {}).forEach(function (key) {
      if (key === 'text') node.textContent = attrs[key];
      else if (key === 'class') node.className = attrs[key];
      else if (key.indexOf('on') === 0) node.addEventListener(key.slice(2), attrs[key]);
      else node.setAttribute(key, attrs[key]);
    });
    (children || []).forEach(function (child) { if (child) node.appendChild(child); });
    return node;
  }

  function plural(n, one, many) { return n + ' ' + (n === 1 ? one : many); }

  function formatTime(ts) {
    try {
      return new Date(ts).toLocaleString([], { day: 'numeric', month: 'short', hour: '2-digit', minute: '2-digit' });
    } catch (e) { return ''; }
  }

  function setFlash(type, text) {
    try { sessionStorage.setItem(FLASH_KEY, JSON.stringify({ type: type, text: text })); } catch (e) { /* ignore */ }
  }

  // ── Network: token + submission ─────────────────────────────────────────

  var AUTH = { kind: 'auth' };

  /** Fresh CSRF token. Throws AUTH when the session has ended. */
  function fetchToken() {
    return fetch(cfg.tokenUrl, {
      credentials: 'same-origin',
      redirect: 'manual',
      cache: 'no-store',
      headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    }).then(function (response) {
      if (response.type === 'opaqueredirect' || response.status === 401) throw AUTH;
      if (!response.ok) throw new Error('token ' + response.status);
      return response.json().then(function (body) {
        if (!body || !body.token) throw AUTH;
        if (body.user_id !== cfg.userId) throw AUTH; // someone else is signed in now
        return body.token;
      });
    });
  }

  /**
   * POST one queued/new submission. Resolves to
   * { outcome: success|duplicate|invalid|auth|token|retry, message }.
   * Rejects only when the server cannot be reached at all.
   */
  function send(item, token) {
    var body = new FormData();
    item.fields.forEach(function (pair) { body.append(pair[0], pair[1]); });
    body.set('_token', token);

    return fetch(item.url, {
      method: 'POST',
      body: body,
      credentials: 'same-origin',
      redirect: 'manual',
      headers: { Accept: 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
    }).then(function (response) {
      if (response.type === 'opaqueredirect' || response.status === 401) return { outcome: 'auth' };
      if (response.status === 419) return { outcome: 'token' };

      return response.json().then(
        function (json) { return json; },
        function () { return null; }
      ).then(function (json) {
        var message = json && json.message ? json.message : '';
        if (response.status >= 500) return { outcome: 'retry', message: message };
        if (!json || !json.status) return { outcome: response.ok ? 'auth' : 'retry', message: message };
        if (json.status === 'success') return { outcome: 'success', message: message };
        if (json.status === 'duplicate') return { outcome: 'duplicate', message: message };
        return { outcome: 'invalid', message: message || 'The server rejected this mark.' };
      });
    });
  }

  // ── Building a queue item from a form ───────────────────────────────────

  function fieldValue(form, name) {
    var node = form.elements[name];
    return node && node.value != null ? String(node.value) : '';
  }

  function selectedText(form, name) {
    var node = form.elements[name];
    if (!node || !node.options || node.selectedIndex < 0) return '';
    return (node.options[node.selectedIndex].text || '').trim();
  }

  function buildItem(form) {
    var fields = [];
    new FormData(form).forEach(function (value, key) {
      if (typeof value === 'string' && key !== '_token') fields.push([key, value]);
    });

    var heading = document.querySelector('.tittle h2');
    return {
      id: uuid(),
      userId: cfg.userId,
      url: form.action,
      fields: fields,
      createdAt: Date.now(),
      status: 'pending',
      error: '',
      key: [form.action, fieldValue(form, 'candidate_id'), fieldValue(form, 'station_id'),
            fieldValue(form, 'form_type'), fieldValue(form, 'exam_type')].join('|'),
      label: {
        exam: heading ? heading.textContent.replace(/\s+/g, ' ').trim() : 'Evaluation',
        group: selectedText(form, 'group_id'),
        candidate: selectedText(form, 'candidate_id'),
        station: selectedText(form, 'station_id'),
      },
    };
  }

  // ── Submitting ──────────────────────────────────────────────────────────

  function enqueue(item) {
    return queueAll().then(function (items) {
      var clash = items.some(function (other) { return other.key === item.key; });
      if (clash) return 'exists';
      return queuePut(item).then(function () { return 'queued'; });
    });
  }

  function onSubmit(event, form) {
    if (event.defaultPrevented) return; // the form's own validation refused it
    event.preventDefault();
    if (form.getAttribute('data-offline-busy')) return;

    var buttons = form.querySelectorAll('button[type="submit"], input[type="submit"]');
    var setBusy = function (busy) {
      if (busy) form.setAttribute('data-offline-busy', '1'); else form.removeAttribute('data-offline-busy');
      buttons.forEach(function (button) { button.disabled = busy; });
    };

    setBusy(true);
    var item = buildItem(form);

    var queueIt = function (reason) {
      return enqueue(item).then(function (result) {
        if (result === 'exists') {
          toast('error', 'This candidate and station are already saved on this device, waiting to sync.');
          setBusy(false);
          return;
        }
        var text = reason === 'auth'
          ? 'Saved on this device. Sign in again to submit it.'
          : 'Saved on this device. It will be submitted automatically when you are back online.';
        finishAndReset(form, 'warning', text, reason !== 'auth');
      }, function () {
        toast('error', 'Could not save this mark on this device. Reconnect before submitting.');
        setBusy(false);
      });
    };

    if (!navigator.onLine) {
      queueIt('offline');
      return;
    }

    submitNow(item, fieldValue(form, '_token')).then(function (result) {
      if (result.outcome === 'success') {
        finishAndReset(form, 'success', result.message || 'Evaluation submitted successfully.');
      } else if (result.outcome === 'duplicate' || result.outcome === 'invalid') {
        toast('error', result.message);
        setBusy(false);
      } else {
        return queueIt(result.outcome);
      }
    }, function () {
      // Could not reach the server even though the browser says it is online.
      return queueIt('offline');
    });
  }

  /** Post now, retrying once with a fresh token if the page's token is stale. */
  function submitNow(item, pageToken) {
    return send(item, pageToken).then(function (result) {
      if (result.outcome !== 'token') return result;
      return fetchToken().then(
        function (token) { return send(item, token); },
        function (error) { if (error === AUTH) return { outcome: 'auth' }; throw error; }
      );
    });
  }

  /** Show a message and give the examiner a clean form, like the old redirect did. */
  function finishAndReset(form, type, text, reload) {
    refreshStatus();
    if (reload !== false && navigator.serviceWorker && navigator.serviceWorker.controller) {
      setFlash(type, text);
      window.location.reload();
      return;
    }
    // Reloading is not possible (no cached copy offline) or not wanted (session ended): reset in place.
    form.removeAttribute('data-offline-busy');
    form.reset();
    form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach(function (b) { b.disabled = false; });
    toast(type, text);
  }

  // ── Syncing ─────────────────────────────────────────────────────────────

  /** Send everything waiting, oldest first. Resolves to { synced, duplicates, failed }. */
  function syncQueue() {
    var summary = { synced: 0, duplicates: 0, failed: 0 };
    if (state.syncing || !navigator.onLine) return Promise.resolve(summary);

    var run = function () {
      state.syncing = true;
      refreshStatus();
      return queueAll().then(function (items) {
        var pending = items.filter(function (item) { return item.status === 'pending'; });
        if (!pending.length) return summary;

        return fetchToken().then(function (token) {
          state.auth = false;
          return pending.reduce(function (chain, item) {
            return chain.then(function () {
              return send(item, token).then(function (result) {
                if (result.outcome !== 'token') return result;
                return fetchToken().then(function (fresh) { token = fresh; return send(item, token); });
              }).then(function (result) {
                switch (result.outcome) {
                  case 'success': summary.synced++; return queueDelete(item.id);
                  case 'duplicate': summary.duplicates++; return queueDelete(item.id);
                  case 'invalid':
                    summary.failed++;
                    item.status = 'failed';
                    item.error = result.message;
                    return queuePut(item);
                  case 'auth':
                    state.auth = true;
                    throw AUTH;
                  default:
                    throw new Error('retry later'); // server error: keep it queued, stop here
                }
              });
            });
          }, Promise.resolve());
        }).then(function () { return summary; });
      }).catch(function (error) {
        if (error === AUTH) state.auth = true;
        return summary; // network trouble: leave the queue as is, try again later
      }).then(function (result) {
        state.syncing = false;
        refreshStatus();
        if (result.synced || result.duplicates) {
          toast('success', plural(result.synced + result.duplicates, 'mark', 'marks') + ' synced.');
          // The results page lists what the server holds: reload it to include what just synced.
          if (document.getElementById('examiner-offline-pending')) setTimeout(function () { window.location.reload(); }, 1200);
        }
        if (result.failed) {
          toast('error', plural(result.failed, 'mark was', 'marks were') + ' rejected by the server. Open the sync badge for details.');
        }
        return result;
      });
    };

    // One tab at a time; the server also rejects repeats, this just avoids the noise.
    if (navigator.locks && navigator.locks.request) {
      return navigator.locks.request('examiner-sync', { ifAvailable: true }, function (lock) {
        return lock ? run() : summary;
      });
    }
    return run();
  }

  // ── Preparing the device (service worker + cached pages) ────────────────

  function readWarm() {
    try { return JSON.parse(localStorage.getItem(WARM_KEY) || 'null'); } catch (e) { return null; }
  }
  function writeWarm(value) {
    try { localStorage.setItem(WARM_KEY, JSON.stringify(value)); } catch (e) { /* ignore */ }
  }

  function registerWorker() {
    if (!('serviceWorker' in navigator)) return Promise.resolve(null);
    return navigator.serviceWorker.register(cfg.swUrl, { scope: cfg.scope }).then(function () {
      return navigator.serviceWorker.ready;
    }).then(function (registration) {
      if (registration.active) registration.active.postMessage({ type: 'session', userId: cfg.userId });
      return registration;
    }).catch(function () { return null; });
  }

  function waitForController(timeoutMs) {
    if (!('serviceWorker' in navigator)) return Promise.resolve(false);
    if (navigator.serviceWorker.controller) return Promise.resolve(true);
    return new Promise(function (resolve) {
      var done = function (value) { resolve(value); };
      var timer = setTimeout(function () { done(!!navigator.serviceWorker.controller); }, timeoutMs);
      navigator.serviceWorker.addEventListener('controllerchange', function () { clearTimeout(timer); done(true); }, { once: true });
    });
  }

  /** Download the marking pages and candidate lists so they work offline. */
  function prepareOffline(force) {
    if (!('serviceWorker' in navigator) || !navigator.onLine || state.warming) return Promise.resolve();

    var last = readWarm();
    if (!force && last && last.userId === cfg.userId && Date.now() - last.at < WARM_MAX_AGE_MS) return Promise.resolve();

    state.warming = true;
    state.warmError = null;
    refreshStatus();

    return waitForController(5000).then(function (controlled) {
      if (!controlled) throw new Error('The offline helper is not active yet. Reload the page and try again.');
      return fetch(cfg.manifestUrl, { credentials: 'same-origin', cache: 'no-store', redirect: 'manual', headers: { Accept: 'application/json' } });
    }).then(function (response) {
      if (response.type === 'opaqueredirect' || !response.ok) throw new Error('Could not read the list of pages to download.');
      return response.json();
    }).then(function (manifest) {
      var urls = (manifest.pages || []).concat(manifest.data || []);
      var okCount = 0;
      var next = 0;

      var worker = function () {
        if (next >= urls.length) return Promise.resolve();
        var url = urls[next++];
        return fetch(url, { credentials: 'same-origin' }).then(function (response) {
          if (response.ok && !response.redirected) okCount++;
        }, function () { /* counted as a miss */ }).then(worker);
      };

      var workers = [];
      for (var i = 0; i < WARM_CONCURRENCY; i++) workers.push(worker());
      return Promise.all(workers).then(function () {
        writeWarm({ userId: cfg.userId, at: Date.now(), total: urls.length, ok: okCount });
        if (okCount < urls.length) state.warmError = 'Some pages could not be downloaded (' + okCount + ' of ' + urls.length + ').';
      });
    }).catch(function (error) {
      state.warmError = error && error.message ? error.message : 'Could not prepare for offline use.';
    }).then(function () {
      state.warming = false;
      refreshStatus();
    });
  }

  // ── UI ──────────────────────────────────────────────────────────────────

  function injectStyles() {
    var css = [
      '.eo-toasts{position:fixed;top:64px;right:16px;z-index:2100;display:flex;flex-direction:column;gap:8px;max-width:360px}',
      '.eo-toast{padding:10px 14px;border-radius:6px;color:#fff;font-size:.9rem;box-shadow:0 2px 8px rgba(0,0,0,.25)}',
      '.eo-toast.success{background:#2e7d32}.eo-toast.error{background:#c62828}.eo-toast.warning{background:#8a5a00}',
      '.eo-pill{position:fixed;left:16px;bottom:16px;z-index:2000;font-family:inherit}',
      '.eo-pill button.eo-badge{border:0;border-radius:20px;padding:8px 16px;font-size:.85rem;font-weight:600;color:#fff;background:#37474f;box-shadow:0 2px 8px rgba(0,0,0,.3);cursor:pointer}',
      '.eo-pill.pending button.eo-badge{background:#8a5a00}.eo-pill.error button.eo-badge{background:#c62828}',
      '.eo-panel{position:absolute;left:0;bottom:46px;width:340px;max-width:calc(100vw - 32px);max-height:60vh;overflow:auto;background:#fff;color:#251917;border:1px solid #ddd;border-radius:8px;box-shadow:0 4px 16px rgba(0,0,0,.25);padding:12px;font-size:.85rem}',
      '.eo-panel h4{margin:0 0 8px;font-size:.95rem;color:#a02626}',
      '.eo-item{border-top:1px solid #eee;padding:8px 0}.eo-item small{color:#666;display:block}',
      '.eo-item .eo-err{color:#c62828}.eo-actions{display:flex;gap:8px;margin-top:8px}',
      '.eo-btn{border:0;border-radius:4px;padding:5px 12px;background:#FEC503;color:#000;font-weight:600;cursor:pointer}',
      '.eo-btn.link{background:none;color:#a02626;padding:0;text-decoration:underline}',
      '.eo-ready{border-left:4px solid #FEC503;background:#fffdf3;border-radius:6px;padding:10px 14px;margin:0 0 16px;text-align:left;font-size:.88rem}',
      '.eo-ready.ok{border-left-color:#2e7d32}.eo-ready.bad{border-left-color:#c62828}',
    ].join('');
    document.head.appendChild(el('style', { text: css }));
  }

  function toast(type, text) {
    if (!text) return;
    if (!ui.toasts) {
      ui.toasts = el('div', { class: 'eo-toasts', role: 'status', 'aria-live': 'polite' });
      document.body.appendChild(ui.toasts);
    }
    var node = el('div', { class: 'eo-toast ' + type, text: text });
    ui.toasts.appendChild(node);
    setTimeout(function () { if (node.parentNode) node.parentNode.removeChild(node); }, type === 'error' ? 9000 : 5000);
  }

  function buildPill() {
    ui.pill = el('div', { class: 'eo-pill', hidden: 'hidden' });
    ui.badge = el('button', { class: 'eo-badge', type: 'button', onclick: function () { togglePanel(); } });
    ui.panel = el('div', { class: 'eo-panel', hidden: 'hidden' });
    ui.pill.appendChild(ui.panel);
    ui.pill.appendChild(ui.badge);
    document.body.appendChild(ui.pill);
  }

  function togglePanel(force) {
    var open = force != null ? force : ui.panel.hasAttribute('hidden');
    if (open) ui.panel.removeAttribute('hidden'); else ui.panel.setAttribute('hidden', 'hidden');
    if (open) renderPanel();
  }

  var lastItems = [];

  function renderPanel() {
    var panel = ui.panel;
    while (panel.firstChild) panel.removeChild(panel.firstChild);
    panel.appendChild(el('h4', { text: 'Marks saved on this device' }));

    if (!lastItems.length) {
      panel.appendChild(el('div', { text: 'Nothing is waiting to sync.' }));
      return;
    }

    if (state.auth) {
      panel.appendChild(el('div', { class: 'eo-err', text: 'Your session has ended. Sign in again while online and these marks will be submitted.' }));
    } else if (!navigator.onLine) {
      panel.appendChild(el('div', { text: 'You are offline. These marks will be submitted when the connection returns.' }));
    }

    lastItems.forEach(function (item) {
      var label = [item.label.candidate, item.label.station, item.label.group].filter(Boolean).join(' · ');
      var node = el('div', { class: 'eo-item' }, [
        el('strong', { text: label || 'Evaluation' }),
        el('small', { text: (item.label.exam || '') + ' — ' + formatTime(item.createdAt) }),
      ]);
      if (item.status === 'failed') {
        node.appendChild(el('small', { class: 'eo-err', text: 'Rejected: ' + (item.error || 'unknown reason') + ' Re-enter it from the form if still needed.' }));
        node.appendChild(el('button', { class: 'eo-btn link', type: 'button', text: 'Discard', onclick: function () {
          queueDelete(item.id).then(refreshStatus);
        } }));
      }
      panel.appendChild(node);
    });

    var actions = el('div', { class: 'eo-actions' });
    if (navigator.onLine && !state.syncing) {
      actions.appendChild(el('button', { class: 'eo-btn', type: 'button', text: 'Sync now', onclick: function () {
        state.auth = false;
        syncQueue().then(refreshStatus);
      } }));
    }
    panel.appendChild(actions);
  }

  /** Recount the queue and redraw the badge and the dashboard card. */
  function refreshStatus() {
    return queueAll().catch(function () { return []; }).then(function (items) {
      lastItems = items;
      var pending = items.filter(function (i) { return i.status === 'pending'; }).length;
      var failed = items.filter(function (i) { return i.status === 'failed'; }).length;
      var online = navigator.onLine;

      var text = '';
      var mode = '';
      if (failed) { text = plural(failed, 'mark needs', 'marks need') + ' attention'; mode = 'error'; }
      else if (pending && state.auth) { text = 'Sign in again to sync ' + plural(pending, 'mark', 'marks'); mode = 'error'; }
      else if (pending && !online) { text = 'Offline · ' + plural(pending, 'mark', 'marks') + ' waiting'; mode = 'pending'; }
      else if (pending && state.syncing) { text = 'Syncing ' + plural(pending, 'mark', 'marks') + '…'; mode = 'pending'; }
      else if (pending) { text = plural(pending, 'mark', 'marks') + ' waiting to sync'; mode = 'pending'; }
      else if (!online) { text = 'Offline · marks are saved on this device'; mode = ''; }

      if (ui.pill) {
        if (text) {
          ui.badge.textContent = text;
          ui.pill.className = 'eo-pill ' + mode;
          ui.pill.removeAttribute('hidden');
        } else {
          ui.pill.setAttribute('hidden', 'hidden');
          ui.panel.setAttribute('hidden', 'hidden');
        }
        if (!ui.panel.hasAttribute('hidden')) renderPanel();
      }
      renderReadiness();
      renderPendingCard();
    });
  }

  function renderReadiness() {
    var host = document.getElementById('examiner-offline-status');
    if (!host) return;
    while (host.firstChild) host.removeChild(host.firstChild);

    var warm = readWarm();
    var ready = warm && warm.userId === cfg.userId && warm.ok > 0;
    var message;
    var cls = 'eo-ready';

    if (!('serviceWorker' in navigator)) {
      message = 'Offline mode is not available in this browser. Marks can still be saved on this device if the connection drops while a form is open.';
      cls += ' bad';
    } else if (state.warming) {
      message = 'Preparing this device for offline marking…';
    } else if (state.warmError) {
      message = state.warmError;
      cls += ' bad';
    } else if (ready) {
      message = 'Ready for offline marking. Last updated ' + formatTime(warm.at) + '.';
      cls += ' ok';
    } else {
      message = 'Not prepared for offline marking yet.';
    }

    var box = el('div', { class: cls }, [el('span', { text: message })]);
    if ('serviceWorker' in navigator && navigator.onLine && !state.warming) {
      box.appendChild(document.createTextNode(' '));
      box.appendChild(el('button', { class: 'eo-btn link', type: 'button', text: ready ? 'Update now' : 'Prepare now', onclick: function () { prepareOffline(true); } }));
    }
    host.appendChild(box);
  }

  /** On the results page: list the marks still on this device, ahead of the server's results. */
  function renderPendingCard() {
    var host = document.getElementById('examiner-offline-pending');
    if (!host) return;
    while (host.firstChild) host.removeChild(host.firstChild);
    if (!lastItems.length) return;

    var card = el('div', { class: 'eo-ready', style: 'margin-top:20px' }, [
      el('strong', { text: 'Saved on this device, not yet submitted (' + lastItems.length + ')' }),
    ]);
    if (state.auth) card.appendChild(el('div', { class: 'eo-err', style: 'color:#c62828', text: 'Sign in again while online to submit them.' }));
    else if (!navigator.onLine) card.appendChild(el('div', { text: 'They will be submitted automatically when you are back online.' }));

    var table = el('table', { class: 'table table-sm', style: 'margin:8px 0 0;background:#fff' }, [
      el('thead', {}, [el('tr', {}, ['Candidate', 'Station', 'Group', 'Exam', 'Saved', 'Status'].map(function (h) { return el('th', { text: h }); }))]),
    ]);
    var body = el('tbody');
    lastItems.forEach(function (item) {
      body.appendChild(el('tr', {}, [
        el('td', { text: item.label.candidate || '-' }),
        el('td', { text: item.label.station || '-' }),
        el('td', { text: item.label.group || '-' }),
        el('td', { text: item.label.exam || '-' }),
        el('td', { text: formatTime(item.createdAt) }),
        el('td', { text: item.status === 'failed' ? 'Rejected: ' + (item.error || 'unknown reason') : 'Waiting to sync' }),
      ]));
    });
    table.appendChild(body);
    card.appendChild(table);
    host.appendChild(card);
  }

  function showStoredFlash() {
    try {
      var raw = sessionStorage.getItem(FLASH_KEY);
      if (!raw) return;
      sessionStorage.removeItem(FLASH_KEY);
      var flash = JSON.parse(raw);
      toast(flash.type, flash.text);
    } catch (e) { /* ignore */ }
  }

  // ── Logout ──────────────────────────────────────────────────────────────

  function bindLogout() {
    document.addEventListener('click', function (event) {
      var link = event.target.closest ? event.target.closest('a[href]') : null;
      if (!link || !/\/logout\/?$/.test(link.getAttribute('href'))) return;

      var waiting = lastItems.length;
      if (waiting && !window.confirm(plural(waiting, 'mark has', 'marks have') + ' not been submitted yet. They stay on this device and are submitted the next time you sign in while online. Log out anyway?')) {
        event.preventDefault();
        return;
      }
      if (navigator.serviceWorker && navigator.serviceWorker.controller) {
        navigator.serviceWorker.controller.postMessage({ type: 'clear' });
      }
      try { localStorage.removeItem(WARM_KEY); } catch (e) { /* ignore */ }
    }, true);
  }

  // ── Start ───────────────────────────────────────────────────────────────

  function init() {
    injectStyles();
    buildPill();
    showStoredFlash();

    document.querySelectorAll('form[data-offline-submit]').forEach(function (form) {
      form.addEventListener('submit', function (event) { onSubmit(event, form); });
    });
    bindLogout();

    window.addEventListener('online', function () {
      state.auth = false;
      refreshStatus();
      syncQueue().then(function () { return prepareOffline(false); });
    });
    window.addEventListener('offline', refreshStatus);

    setInterval(function () {
      if (navigator.onLine && lastItems.some(function (i) { return i.status === 'pending'; }) && !state.auth) syncQueue();
    }, SYNC_INTERVAL_MS);

    refreshStatus().then(function () {
      return registerWorker();
    }).then(function () {
      return syncQueue();
    }).then(function () {
      return prepareOffline(false);
    });
  }

  // For support and automated tests.
  window.ExaminerOffline = {
    sync: syncQueue,
    prepare: function () { return prepareOffline(true); },
    pending: queueAll,
  };

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();
})();
