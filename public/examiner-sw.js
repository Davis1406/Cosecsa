/* COSECSA examiner offline service worker.
 *
 * Registered from the examiner pages with scope /examiner/ (see
 * public/dist/js/examiner-offline.js). It makes the marking screens usable
 * without a connection:
 *
 *   - marking pages + candidate lists  -> network first, cached copy when the
 *                                         network is down or slow
 *   - scripts, styles, fonts, images   -> stale-while-revalidate, falling back to an
 *                                         older cached version if the network is down
 *   - every cached page also has its own scripts and styles downloaded with it, so a
 *     page saved while online never needs a file that was not saved too
 *   - everything else (POSTs, admin pages, profile, results, ...) is left to
 *     the browser untouched.
 *
 * Marks entered offline are queued by the page script (IndexedDB), not here.
 * Cached pages are personal to the logged-in examiner, so the caches are
 * emptied when the account changes or on logout.
 */

const VERSION = 'v1';  // cache names; assets are re-checked on every visit, so this only changes if the cache layout does
const PAGE_CACHE = `examiner-pages-${VERSION}`;
const STATIC_CACHE = `examiner-static-${VERSION}`;
const META_CACHE = `examiner-meta-${VERSION}`;
const SESSION_KEY = '/__examiner-session';
const NETWORK_TIMEOUT_MS = 5000;

// Pages and data worth keeping for offline marking. Anything else under
// /examiner/ (profile, password, badge, learning, ...) is deliberately not cached.
// The results summary, each result's View / Resubmit pages and the profile pages are cached
// as copies of what was last seen online.
const PAGE_PATTERNS = [
  /^\/examiner\/dashboard\/?$/,
  /^\/examiner\/examiner_form\/?$/,
  /^\/examiner\/general_surgery\/?$/,
  /^\/examiner\/results\/?$/,
  /^\/examiner\/profile_settings\/?$/,
  /^\/examiner\/edit_info\/\d+\/?$/,
  /^\/examiner\/(resubmit|view_results)\/\d+\/\d+\/?$/,
  /^\/examiner\/fcs-resubmit\/\d+(\/(clinical|viva))?\/?$/,
  /^\/examiner\/view_fcs_results\/\d+\/?$/,
  /^\/examiner\/(cardiothoracic|urology|paediatric|orthopaedic|ent|plastic_surgery|neurosurgery|paediatric_orthopaedics)(\/(clinical|viva))?\/?$/,
  /^\/get-(mcs|gs)-candidates\/\d+\/?$/,
  /^\/get-exam-candidates\/[a-z_]+\/\d+\/?$/,
];

const STATIC_DESTINATIONS = new Set(['style', 'script', 'font', 'image']);

self.addEventListener('install', () => {
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  const keep = new Set([PAGE_CACHE, STATIC_CACHE, META_CACHE]);
  event.waitUntil(
    (async () => {
      const names = await caches.keys();
      await Promise.all(names.filter((n) => n.startsWith('examiner-') && !keep.has(n)).map((n) => caches.delete(n)));
      await self.clients.claim();
    })()
  );
});

self.addEventListener('message', (event) => {
  const data = event.data || {};
  if (data.type === 'session') {
    event.waitUntil(bindToUser(data.userId));
  } else if (data.type === 'clear') {
    event.waitUntil(clearPersonalData());
  }
});

self.addEventListener('fetch', (event) => {
  const request = event.request;
  if (request.method !== 'GET') return;

  const url = new URL(request.url);

  if (url.origin === self.location.origin && isCacheablePage(url)) {
    event.respondWith(networkFirst(request, url, event));
    return;
  }

  if (STATIC_DESTINATIONS.has(request.destination)) {
    event.respondWith(staleWhileRevalidate(request));
  }
});

function isCacheablePage(url) {
  return PAGE_PATTERNS.some((pattern) => pattern.test(url.pathname));
}

/** Cache key ignores the query string, so ?x=1 variants share one entry. */
function cacheKey(url) {
  return url.origin + url.pathname.replace(/\/$/, '');
}

async function networkFirst(request, url, event) {
  const cache = await caches.open(PAGE_CACHE);
  const key = cacheKey(url);

  try {
    const response = await fetchWithTimeout(request, NETWORK_TIMEOUT_MS);
    // A redirect here means the session ended (we got bounced to the login
    // page): never store that as the marking page.
    if (response.ok && !response.redirected) {
      await cache.put(key, response.clone());
      if ((response.headers.get('content-type') || '').includes('text/html')) {
        event.waitUntil(cachePageAssets(response.clone(), url.href));
      }
    }
    return response;
  } catch (error) {
    const cached = await cache.match(key);
    if (cached) return cached;
    if (request.mode === 'navigate') return offlinePage();
    return new Response(JSON.stringify({ error: 'offline' }), {
      status: 503,
      headers: { 'Content-Type': 'application/json' },
    });
  }
}

async function staleWhileRevalidate(request) {
  const cache = await caches.open(STATIC_CACHE);
  const cached = await cache.match(request);

  const refresh = fetch(request)
    .then((response) => {
      // status 0 = opaque cross-origin response (CDN assets); fine to reuse.
      if (response && (response.ok || response.type === 'opaque')) {
        cache.put(request, response.clone());
      }
      return response;
    })
    .catch(() => null);

  if (cached) return cached;

  const response = await refresh;
  if (response) return response;

  // Offline and this exact file (e.g. app.js?v=<new deploy>) was never saved:
  // an older copy of the same file is far better than nothing.
  const older = await cache.match(request, { ignoreSearch: true });
  return older || Response.error();
}

/**
 * Download the scripts and stylesheets a page refers to and keep them, so the
 * saved copy of the page can be opened offline with every file it asks for.
 * Versioned URLs (?v=...) change on each deploy, which is why this matters.
 */
async function cachePageAssets(response, pageUrl) {
  try {
    const html = await response.text();
    const cache = await caches.open(STATIC_CACHE);

    for (const url of assetUrls(html, pageUrl)) {
      if (await cache.match(url)) continue;
      try {
        const sameOrigin = new URL(url).origin === self.location.origin;
        const asset = await fetch(url, { mode: sameOrigin ? 'same-origin' : 'no-cors', credentials: 'omit' });
        if (asset.ok || asset.type === 'opaque') await cache.put(url, asset);
      } catch (error) {
        // one missing file must not stop the rest
      }
    }
  } catch (error) {
    // best effort only
  }
}

/** Absolute URLs of the <script src> and <link rel="stylesheet"> files in a page. */
function assetUrls(html, pageUrl) {
  const found = new Set();
  const add = (value) => {
    if (!value || value.startsWith('data:') || value.startsWith('javascript:')) return;
    try { found.add(new URL(value, pageUrl).href); } catch (error) { /* ignore */ }
  };

  for (const match of html.matchAll(/<script\b[^>]*\bsrc=["']([^"']+)["']/gi)) add(match[1]);
  for (const tag of html.matchAll(/<link\b[^>]*>/gi)) {
    if (/\brel=["']?stylesheet/i.test(tag[0])) {
      const href = /\bhref=["']([^"']+)["']/i.exec(tag[0]);
      if (href) add(href[1]);
    }
  }
  return found;
}

function fetchWithTimeout(request, ms) {
  return new Promise((resolve, reject) => {
    const timer = setTimeout(() => reject(new Error('timeout')), ms);
    fetch(request).then(
      (response) => {
        clearTimeout(timer);
        resolve(response);
      },
      (error) => {
        clearTimeout(timer);
        reject(error);
      }
    );
  });
}

/** Empty the personal caches if a different examiner is now signed in. */
async function bindToUser(userId) {
  if (userId == null) return;
  const meta = await caches.open(META_CACHE);
  const stored = await meta.match(SESSION_KEY);
  const previous = stored ? await stored.text() : null;

  if (previous !== null && previous !== String(userId)) {
    await caches.delete(PAGE_CACHE);
  }
  await meta.put(SESSION_KEY, new Response(String(userId)));
}

async function clearPersonalData() {
  await caches.delete(PAGE_CACHE);
  await caches.delete(META_CACHE);
}

function offlinePage() {
  const html = `<!doctype html><html lang="en"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1"><title>Offline - COSECSA</title>
<style>body{font-family:system-ui,sans-serif;background:#fff8f7;color:#251917;margin:0;display:flex;min-height:100vh;align-items:center;justify-content:center;padding:24px}
.box{max-width:460px;text-align:center}h1{color:#a02626;font-size:1.4rem}p{line-height:1.5}
a{display:inline-block;margin-top:12px;background:#FEC503;color:#000;padding:8px 18px;border-radius:6px;text-decoration:none;font-weight:600}</style></head>
<body><div class="box"><h1>You are offline</h1>
<p>This page was not saved for offline use. Reconnect and open the examiner dashboard once so the marking pages can be downloaded, then try again.</p>
<a href="/examiner/dashboard">Go to dashboard</a></div></body></html>`;
  return new Response(html, { status: 503, headers: { 'Content-Type': 'text/html; charset=utf-8' } });
}
