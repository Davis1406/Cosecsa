# Deploy — cosecsamis.org (this repo)

Quick reference for shipping a change in this repo (`Cosecsa`, the Blade frontend) to
production. See `CLAUDE.md` for the architecture overview and `cosecsa-api`'s own
`DEPLOYMENT.md`/`CHANGES.md` for that sibling repo's deploy — the two are **not** always
in lockstep; check the entry in `CHANGE.md` for a "⚠️ Coordinate" note before deploying
either one alone.

## Server

| | |
|---|---|
| Host | `89.117.55.62` (`vmi2259734.contaboserver.net`) — also reachable as `cosecsamis.org` |
| SSH user | `root` |
| App path | `/var/www/html/Cosecsa` |
| Sibling API app path | `/var/www/cosecsa-api` |
| Web server | **Apache** (`mod_php`, not php-fpm) + nginx (check `apache2ctl -S` / vhost configs if routing is ever in question) |
| PHP | 8.2.32, OPcache **on**, `validate_timestamps=On`, `revalidate_freq=60` — code changes are picked up automatically within ~60s; `apache2ctl graceful` forces it immediately (safe, no dropped connections) |
| DB | MySQL, database `mysystemdb`, user `cosecsaAdmin` (owned by `cosecsa-api`; this repo never connects to it directly — see `CLAUDE.md`) |
| Scheduler | cron → `php artisan schedule:run` runs against `cosecsa-api`, not this repo |

Credentials (SSH root password, DB password) are held by Davis Kondo directly — not
duplicated here. Prefer an SSH key over the password where practical for future sessions.

## Standard deploy

```bash
# 1. Commit + push from the working repo
git add -A
git commit -m "..."
git push origin master

# 2. rsync the working tree to the server (excludes are load-bearing — do not drop any)
rsync -avz --delete \
  --exclude='.env' --exclude='storage/' --exclude='vendor/' \
  --exclude='.git/' --exclude='bootstrap/cache/' --exclude='node_modules/' \
  /path/to/local/Cosecsa/ \
  root@89.117.55.62:/var/www/html/Cosecsa/

# 3. Clear + rebuild Laravel's cached config/routes/views
ssh root@89.117.55.62 "cd /var/www/html/Cosecsa && \
  php artisan cache:clear && php artisan config:clear && \
  php artisan route:clear && php artisan view:clear && \
  php artisan config:cache && php artisan route:cache && php artisan view:cache"

# 4. (Optional but recommended for controller/model/service changes) force OPcache
#    to pick up the new bytecode immediately instead of waiting out the 60s TTL
ssh root@89.117.55.62 "apache2ctl graceful"
```

**Always redeploy with config/route/view cached, not just cleared** (step 3's last three
commands) — a bare `clear` without the matching `cache` loses the production perf win. If
you edit `.env` directly on the server, you must run `php artisan config:cache` afterward
too, for the same reason.

### If `patch`/manual edit was applied directly on the server (no rsync)

Sometimes it's faster to hand-apply a small, reviewed diff straight into a Termius/SSH
session (e.g. `patch -p1 < file.patch` from `/var/www/html/Cosecsa`) instead of syncing a
whole local tree. That's fine for one-off fixes, but:
- **Back up the file first**: `cp file.php file.php.bak-$(date +%Y%m%d)`.
- **The local working copy and git history must be updated to match** afterward (commit +
  push the same change locally) — otherwise the next full `rsync` deploy will silently
  revert the server-only edit, or worse, a `--dry-run` on a later patch will fail because
  the file on disk no longer matches what git thinks is there.
- Always run `patch --dry-run` first and read the output before applying for real.

## Verifying a backend-only change (no UI to click through)

For something like a PDF/DOCX-rendering method that isn't directly reachable without
walking through the app UI, `php artisan tinker --execute="..."` on the server is the
fastest way to exercise the exact code path and eyeball the output — e.g. invoking a
`private` controller method via `ReflectionClass` and emailing yourself the generated file
instead of (or before) sending it to a real recipient:

```bash
ssh root@89.117.55.62 'cd /var/www/html/Cosecsa && php artisan tinker --execute="
\$period = \App\Models\ProgressReportPeriod::with([\"participants.user\",\"participants.tasks\"])->orderByDesc(\"period_month\")->first();
\$sender = \App\Models\User::where(\"user_type\", 1)->first();
\$ref = new \ReflectionClass(\App\Http\Controllers\ProgressiveReportController::class);
\$m = \$ref->getMethod(\"buildProgressReportDocx\");
\$m->setAccessible(true);
[\$binary, \$filename] = \$m->invoke(new \App\Http\Controllers\ProgressiveReportController(), \$period);
echo strlen(\$binary).PHP_EOL;
\Illuminate\Support\Facades\Mail::to(\"YOUR_EMAIL_HERE\")->send(new \App\Mail\ProgressReportCeoShareMail(
    \$period->period_month->format(\"F Y\"), \$filename, \$binary, \$sender
));
"'
```

A `<warning> Notice: ob_end_flush(): Failed to delete and flush buffer...` in tinker's
output for anything using `buildProgressReportDocx()`/`downloadDocx()` is expected and
harmless — it's tinker's own CLI output buffering colliding with the
`while (ob_get_level()) { ob_end_clean(); }` cleanup loop that method uses to make the real
HTTP `streamDownload()` response clean. It does not occur on the real web request path.

## Coordinating with `cosecsa-api`

This repo has **no direct database access** — every schema/data change lives in
`cosecsa-api` and is deployed separately (see that repo's own `DEPLOYMENT.md`). Check
`CHANGE.md`'s "⚠️ Coordinate" notes before assuming a single-repo deploy is sufficient;
several past changes have required both apps to land together (new migration + matching
`ApiClient` call, a renamed API route, etc.) — deploying only one half will 404 or drift
silently rather than error loudly.
