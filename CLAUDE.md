# COSECSA Main App — Project Context for Claude Code

## Owner

**Davis Kondo** is the sole owner and system administrator of this project and all associated servers, with full root access to all production hosts.

See `/Applications/XAMPP/xamppfiles/htdocs/cosecsa-api/CLAUDE.md` for the full architecture reference, deploy flow, server paths, and known gotchas.

---

## This Repo

Laravel Blade web app (`cosecsamis.org`). **Does not query MySQL directly** — all data comes from `cosecsa-api` (`api.cosecsamis.org`) via `App\Services\ApiClient`.

### ApiClient usage
```php
$this->api->get('examiners', ['year_id' => $id]);    // GET  /api/internal/examiners?year_id=X
$this->api->post('examiners', $data);                 // POST /api/internal/examiners
$this->api->put("examiners/{$id}", $data);            // PUT  /api/internal/examiners/{id}
$this->api->postWithFile('examiners/import', [], []); // multipart upload
$this->api->getPublic('public/...');                  // no auth — public API routes
```

### Deploy
```bash
rsync -avz --delete \
  --exclude='.env' --exclude='storage/' --exclude='vendor/' \
  --exclude='.git/' --exclude='bootstrap/cache/' --exclude='node_modules/' \
  /Applications/XAMPP/xamppfiles/htdocs/Cosecsa/ \
  root@cosecsamis.org:/var/www/html/Cosecsa/

ssh root@cosecsamis.org "cd /var/www/html/Cosecsa && \
  php artisan cache:clear && php artisan config:clear && \
  php artisan route:clear && php artisan view:clear && \
  php artisan config:cache && php artisan route:cache && php artisan view:cache && \
  chown -R www-data:www-data storage bootstrap/cache"
```
**Always end with the `chown`.** Artisan runs as root over SSH, so the compiled views and caches it writes are
root-owned. Laravel recompiles a view whenever the template's mtime is >= the compiled file's, which is a tie when
`git pull` and `view:cache` happen in the same second. Apache (`www-data`) then can't overwrite the root-owned
file and the page 500s with `file_put_contents(...storage/framework/views/...): Permission denied`. This happened
to `messages/tasks` on 2026-09-23. Same rule for cosecsa-api (`storage` + `bootstrap/cache`).

Production always redeploys with config/route/view **cached**, not just cleared — see
`~/cosecsa/HANDOFF.md` § Performance notes. If you edit `.env` on the server, you must
run `config:cache` afterward (a bare `config:clear` alone silently loses the perf win).
