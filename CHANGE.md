# Change Log

> **Important:** Every new change, feature, bug fix, or configuration update must be documented in this file. Add entries under the `[Unreleased]` section as work is completed, then move them to a versioned section when a release is tagged.

## [Unreleased]

### Fixed (2026-09-02) — Fellow profile photo upload crashed with a 500
- Editing (or adding) a fellow with a profile photo threw an internal server error. Two bugs in
  sequence:
  1. **`FellowsController` called `ApiClient::postWithFile()` with the wrong arguments.** The
     signature is `postWithFile($path, array $data, array $files)`, but the add/edit/import
     methods passed the `UploadedFile` object as `$data` and a string as `$files`, so any fellow
     save with a photo died with `Argument #2 ($data) must be of type array,
     Illuminate\Http\UploadedFile given`. All three call sites now use the same pattern as
     `MembersController` / `TraineeController`:
     `postWithFile($path, $request->except('profile_image'), ['profile_image' => $file])` (and
     `postWithFile('fellows/import', [], ['file' => $file])`).
  2. **`ApiClient::postWithFile()` crashed on falsy multipart fields.** After fixing #1, saving
     still 500'd with `A 'contents' key is required` (Guzzle `MultipartStream`). Laravel's
     `attach()` stores each part via `array_filter()`, which drops the `contents` key for **any
     falsy value** (`''`, `'0'`, `0`, `false`, `null`) — the first empty or zero-valued field in
     the submitted form crashes the request. The fellow form sends `$request->except('profile_image')`
     (every field including blanks), so a blank optional field or a `0` value (e.g. `is_promoted`)
     was enough to 500. `postWithFile()` / `attachArray()` now skip all falsy scalar values (the
     API treats an absent field as unchanged/null, so omitting them is safe), and the file attach
     is guarded against an empty/0-byte/unreadable upload (`file_get_contents()` returning
     `false`/`''`). This fixes the same latent crash for every other controller that uses
     `postWithFile`.

Files changed:
```
app/Http/Controllers/FellowsController.php   (modified — correct postWithFile args)
app/Services/ApiClient.php                    (modified — skip falsy multipart fields)
```
Deployed via `cosecsa-deploy web` over SSH. No migrations. Verified by reproducing the exact
multipart build (falsy fields + a real image file) — the request builds without throwing
`A 'contents' key is required`, and a real file upload still attaches correctly.

### Fixed (2026-08-31) — Global search shows only the present exam year's candidates
- The admin global search bar's Candidates group (`DashboardController::globalSearch()`)
  returned every candidate row ever synced, so queries surfaced past candidates (e.g. Judith
  NASSAAZI, exam_year 2024; Mulualem WOLDEMICHAEL, exam_year 2025) alongside the current
  intake. Now scoped to `c.exam_year = date('Y')`, matching
  `cosecsa-api/GlobalSearchController::searchCandidates()` (same fix applied there) and the
  `date('Y')` convention `PromotionController`/`CandidateController::reportsData()` use. Rolls
  forward each January automatically.
- Past candidates remain fully visible on the Candidates page itself (its year filter is
  untouched) — only the search bar hides them.

### Added (2026-08-30) — "Secretariat Report" menu for the CEO + exempt her from the submission deadline
- New **Secretariat Report** sidebar entry under Progressive Reports, visible only to the CEO
  (Stella Itungu), alongside her existing "My Progress Report". Opens the full, consolidated
  current-period report (every officer's section, read-only outside her own row) with Download
  PDF / Download DOCX buttons — previously she only ever saw her own single row and had no menu
  path to the compiled report at all.
- `downloadPdf()` / `downloadDocx()` now give the CEO every section compiled, same as a manager
  (new `canViewFullReport()` = `isProgressReportManager() || isProgressReportCeo()`) — she was
  silently getting just her own row back before, same bug as the 2026-08-25 manager fix below.
- `ProgressReportParticipant::isLocked()`: the CEO's own section is exempt from the
  deadline-passed lock — she isn't submitting on a schedule, so there's no deadline to lock her
  out for. A past (no-longer-current) month still locks for her too, same as everyone's history.
- **Fixed a live bug found while doing this:** `shareWithCeo()` ("Share with CEO" button) has
  been silently broken since `bf86516` (2026-08-28) — it looked the CEO up via
  `config('progress_report_sections')`, but that same commit deliberately removed her from that
  list (she's the report's recipient, not an auto-seeded section), so every click just returned
  "No CEO account is configured to share with." Fixed by adding a dedicated
  `services.progress_reports.ceo_user_id` config value (`config/services.php`), decoupled from
  the sections list on purpose, and pointing `ProgressReportParticipant::ceoUserId()` /
  `shareWithCeo()` / the new CEO checks at that instead.
- Also hardened `shareWithCeo()`'s PDF write: the `public` disk has `throw => false`
  (`config/filesystems.php`), so a failed `Storage::put()` silently returns false instead of
  throwing — the code went on to message/email the CEO a link to a file that was never actually
  written. This happened for real on 2026-08-25 (via cosecsa-api's copy of the same method — see
  its `DEPLOYMENT.md` 2026-08-30 entry for how it was caught and repaired on that side). Now
  verified with `put()`'s return value + `exists()` before sending anything.
- Reminders (`SendProgressReportReminders`) and the sidebar's pending-count badge now exclude the
  CEO's `user_id`, so if she ever has a pending section in a given month (seeded by cosecsa-api's
  copy of the sections list, which does include her — e.g. Aug 2026) she's never emailed a
  deadline reminder and never inflates the manager's badge count.
- **Note for future reference:** this app and `cosecsa-api` each keep their own independent copy
  of `config/progress_report_sections.php` and their own Eloquent models over the same shared
  `progress_report_*` tables (per `CLAUDE.md`, this app is supposed to go through
  `cosecsa-api`/`ApiClient` for everything, but Progressive Reports predates that and still
  queries MySQL directly). The two configs have drifted — this app excludes the CEO from the
  section list, cosecsa-api includes her — so whichever app's "Add a new month" button gets
  clicked determines whether she gets an auto-seeded row that month. Not resolved here; flagging
  it since it's the reason a dedicated `ceo_user_id` setting was needed instead of just restoring
  her to this app's section list.

Files changed:
```
config/services.php                                   (modified)
app/Models/User.php                                    (modified)
app/Models/ProgressReportParticipant.php                (modified)
app/Http/Controllers/ProgressiveReportController.php   (modified)
app/Console/Commands/SendProgressReportReminders.php   (modified)
resources/views/layout/header.blade.php                (modified)
```
Deployed via `cosecsa-deploy web` over SSH. No migrations. Verified post-deploy via tinker:
`isProgressReportCeo()` / `isProgressReportManager()` / `isLocked()` / `ceoUserId()` all correct;
`shareWithCeo()`'s CEO lookup now resolves to Stella Itungu instead of failing.

### Fixed (2026-08-25) — Manager "Download PDF"/"Download DOCX" only returned the manager's own section
- Diana (Administrative Officer, a report manager) reported that downloading the report from
  her side gave her only her own section, not the compiled report. `downloadPdf()` and
  `downloadDocx()` both unconditionally scoped `participants` to `Auth::id()` regardless of who
  was asking — but the "Download PDF"/"Download DOCX" buttons a manager sees sit in the manager
  toolbar (`show.blade.php`) right alongside "Share with CEO" (which already compiles every
  section) and "Consolidate"/"Delete Report", so a manager downloading should get everyone's
  data, not just their own. The same-labelled buttons in the regular-participant toolbar should
  still stay scoped to just that person's own section — unaffected.
- Both methods now only apply the `where('user_id', Auth::id())` filter when `! $this->canManage()`
  (the same manager check `shareWithCeo()`/`authorizeManage()` already use) — managers get every
  participant, everyone else keeps seeing only their own.
- **Files:** `app/Http/Controllers/ProgressiveReportController.php`.

### Fixed (2026-08-25) — CEO DOCX report: section-name visibility, bullet formatting, repeated column headers
- The maroon section-header row (staff name/role) in the emailed/downloaded `.docx` report had
  the white `color` set on the **cell** style array (`bgColor` + `bold` + `color` together), but
  PhpWord's `Cell` style only honours `bgColor`/border/valign — font-level keys like `bold`/
  `color` are silently ignored there, so the name rendered in PhpWord's default (effectively
  invisible on the maroon fill). Split into a cell-level style (`bgColor` only) and a font-level
  style (`bold`, `color: FFFFFF`) passed to `addText()`'s own style argument, matching how the
  rest of the app renders it (already-visible white-on-maroon in the PDF/web views, which use
  CSS, not PhpWord).
- Planned Activities / Current Status / Next Steps store multiple bullet points joined by
  `\n❖ ` and render fine in the PDF/web views (`white-space:pre-line`), but PhpWord's `addText()`
  doesn't treat `\n` as a line break — every bullet in a multi-line field was mashed onto one
  run with no visible separation. New `buildProgressReportDocx()` helper
  `addBulletedCellText()` splits each field on newlines and adds every non-empty line as its own
  line via `addTextBreak()`, prefixing a `❖ ` bullet on any line that's missing one (covers
  older data entered before the bullet-textarea existed).
- The `No / Activity / Planned Activities / Current Status / Next Steps` column header row
  previously appeared once at the very top of the table; it's now repeated directly under every
  section's maroon name row so the columns stay identifiable while reading/scrolling through
  each staff member's section, not just the first one.
- **Files:** `app/Http/Controllers/ProgressiveReportController.php`.

### Added (2026-08-25) — "Share with CEO" now also emails her the compiled DOCX report
- Previously "Share with CEO" (`progressive-reports/{id}/share-ceo`) only sent the consolidated
  PDF as an in-app Messages attachment — no email was ever sent, and the DOCX export existed
  only as a manual download nobody delivered to her. It now does both: the existing PDF-via-
  Messages share, plus an email to the CEO's account email with the compiled report attached
  as a formatted `.docx` (same masked-sender practice as the Progress Report reminder/Draft
  Email sends — From: the acting staff member's name via COSECSA, Reply-To: their own email).
- Extracted the PhpWord docx-rendering logic out of `downloadDocx()` into a shared
  `buildProgressReportDocx($period)` helper so the manual "Download DOCX" button and the CEO
  email attachment render from the same code path; `downloadDocx()`'s own behavior (scoped to
  the current user's section) is unchanged, `shareWithCeo()` passes the already-loaded
  all-participants `$period` for the compiled report.
- **Files:** `app/Http/Controllers/ProgressiveReportController.php`,
  `app/Mail/ProgressReportCeoShareMail.php` (new),
  `resources/views/emails/progress_report_ceo_share.blade.php` (new),
  `resources/views/progressive_reports/show.blade.php`.

### Added (2026-08-24) — Draft Emails: preview shows masked sender + {{number}}; emails send like the Progress Report reminders
- The Draft Email live preview now reflects how the email actually sends: **From** shows
  "Your Name via COSECSA `<communications@cosecsa.org>`" (the shared mailbox with the logged-in
  staff member's name as the display name) and **Reply-To** shows their own email — the same
  masked-sender practice as the Progress Report reminder emails — plus the `{{number}}`
  placeholder renders as a sample count ("25"). The form hint now documents both `[Name]` and
  `{{number}}`.
- Backed by the `cosecsa-api` change (see that repo's CHANGES.md): manual "Send Now" goes out
  through the shared COSECSA mailbox dressed up as personally from the acting staff member
  (From display name = their name, Reply-To = their email, their signature in the body), and
  the `{{number}}` placeholder is replaced with the real per-country pending-application count
  (automatic trigger and manual sends). Every admin/staff member who can open the Draft Emails
  section can use Send Now.
- **Files:** `resources/views/admin/draft_emails/form.blade.php`.
- **⚠️ Coordinate:** requires the matching `cosecsa-api` deploy (mailable + sendNow changes).

### Added (2026-08-24) — Country Rep Active/Retired status + dark-mode fix for position badges
- Country Reps can now be marked **Active** or **Retired**. Retired reps stay visible in the
  list/profile (with a badge) but are excluded from automated emails (the Draft Email
  `applications_threshold_per_country` trigger) and the Letters recipient resolver — a rep who
  stepped down no longer gets pinged for pending applications. Backing DB column is
  `country_reps.status` in `cosecsa-api` (see that repo's CHANGES.md; both apps deploy in
  lockstep, migration included).
- **List page** (`admin/associates/reps/list`): new **Status** column with Active/Retired badge
  and a matching filter in the filter bar. **View page** (`view.blade.php`): Status badge next to
  the Position badge in the header, plus an inline-pencil Status field (Active/Retired) in
  Representative Details. **Add/Edit forms** (`add`/`edit.blade.php`): Status dropdown, saved via
  `CountryRepsController::insert()/update()`.
- **Fixed:** the Position badge colours (`Country Representative` / `WiSA chair` /
  `Overseas Representative`) were hard-coded light-mode pastels that looked wrong on dark-mode
  card surfaces. Moved them into a `.cr-position-badge` class with proper dark-mode variants
  (`body.dark-mode`) on both the list and view pages.
- **Files:** `app/Http/Controllers/CountryRepsController.php`, `resources/views/admin/associates/reps/{list,view,add,edit}.blade.php`.
- **⚠️ Coordinate:** requires the matching `cosecsa-api` deploy (migration + `status` in
  list/detail/quick-update responses) to land first — the list/view read `status` from the API.

### Fixed (2026-08-24) — Country Reps table blank after adding the Status column
- The list page went blank (table never appeared) after the Status column was added. The
  `crstable` DataTable in `public/dist/js/custom.js` still declared **8** column definitions
  while the table now had **9** `<th>`s. The `#crstable` table is `opacity: 0` until DataTables
  initialises (see `custom.css`), and with `stateSave: true` a stale saved column state can make
  init throw before `initComplete` runs — so `hideLoader()` never fires and the table stays
  invisible, showing nothing.
- Fixed the column-definition count to 9 in `custom.js` so it matches the current table; the
  `#crstable_wrapper` column-visibility/order state re-saves itself on the next successful load.
  If a stale saved state still lingers in a browser, a hard refresh (or clearing that site's
  DataTables localStorage state) resolves it.
- **Files:** `public/dist/js/custom.js`.

### Fixed (2026-08-23) — Progressive Report "No." column didn't renumber after deleting a row
- Reported: add rows 5 and 6 to a section's task table, delete row 5, and the "No." column
  showed 4, 6 — row 5 missing entirely, off by one for every row after it — until a full page
  reload. Two stacked causes, one per repo:
  1. **`cosecsa-api`** — `progress_report_tasks.row_no` (the stored, displayed row number)
     wasn't compacted back to `1..N` after a delete, so it left a real gap in the database
     and the next added row reused the wrong number (`max(row_no)+1` skipped straight past
     the gap). Fixed there; see that repo's own CHANGES.md (2026-08-23 entry).
  2. **This repo** — even with (1) fixed, `progressive_reports/show.blade.php`'s delete
     handler only did `row.remove()` on success; it never updated the remaining rows'
     already-rendered "No." cells to match the backend's new numbering, so the page kept
     showing stale numbers until reloaded.
- Delete handler now walks the remaining rows in that participant's `<tbody>` after a
  successful delete and rewrites each row's "No." cell to its new `1..N` position — no
  reload needed.
- **Files:** `resources/views/progressive_reports/show.blade.php`.
- **⚠️ Coordinate:** requires the `cosecsa-api` deploy from the same date (row_no compaction
  fix) to actually be in place for the *database* numbers to be correct — this repo's fix
  only keeps the on-page display in sync with whatever the API returns.

### Data correction (2026-08-21) — Backfilled Secretariat Monthly Reports for Mar–Jul 2026 from the college's Word docs
- Imported `config/progress_report_sections.php` under `admin/progressive-reports` had no way to bulk-load a month from a Word doc — every period previously had to be filled in section-by-section through the UI. Ran a one-off backfill (`cosecsa-api`'s new `progress-reports:import-secretariat` command, see that repo's CHANGES.md) against the college's `Monthly Reports` folder for March, April, May, June, and July 2026 (April was initially going to be skipped as its supplied file was stale, then included once a corrected copy was supplied).
- Two existing periods (May id 5, June id 7) had already been auto-opened with template-seeded, unsubmitted content — backed up in-DB before being deleted and replaced with the real report content per Davis's instruction. No submitted staff data was lost (both were still 100% "pending").
- **New staff section:** `MANAGING EDITOR (VINCENT KIPKORIR)` — every source doc included this role but it didn't exist in `progress_report_sections.php` yet. Added `user_id => 17994` (existing account, `managing_editor@cosecsa.org`, not yet logged in).
- Confirmed `IT ASSISTANT (LAURENCE PAUL)` in the source docs and the existing config's `IT ASSISTANT (LAURENCE KISANGA)` (`user_id` 7827) are the same person — kept the existing `user_id`, no new account created.
- **Files:** `config/progress_report_sections.php`.
- **⚠️ Coordinate:** requires the matching `cosecsa-api` deploy (new `progress-reports:import-secretariat` command + identical config change) — see that repo's own CHANGES.md entry. Both apps' copies of `config/progress_report_sections.php` must stay in sync; nothing enforces that automatically.

### Changed (2026-08-18) — Delete now follows a module's "manage" permission, not Super-Admin-only
- `config/admin_permissions.php` has always documented "manage" as covering "create/edit/delete/import — anything beyond read-only" for every module (e.g. `lookups.manage`: *"Add, edit, delete hospitals, programmes, and hospital-programme links"*), but `PermissionMiddleware` hard-coded delete routes (`.../delete/*`, `.../destroy/*`, or the `DELETE` verb) to Super-Admin-only regardless of what any role's manage permission granted — contradicting the config's own documented intent. Surfaced by Davis granting Edna's role (Academic Records Assistant) `lookups.manage` expecting it to include hospital delete, per that permission's own description, and it didn't.
- `PermissionMiddleware::handle()`: delete now passes for `$user->isSuperAdmin() || $user->hasPermission("{$module}.manage")` instead of Super Admin only — matches the documented behavior for every module's manage permission app-wide, not just hospitals.
- Views only checked `Auth::user()->isSuperAdmin()` to decide whether to *show* a Delete action at all, which would've kept it hidden from a manage-permission holder even though the route now allows it. Fixed the three `lookups`-module list views (the ones actually reported: Hospitals, Hospital Programmes, Programmes) to also show Delete for `lookups.manage`: `resources/views/admin/hospital/{list,_list_content}.blade.php`, `resources/views/admin/hospitalprogrammes/{list,_list_content}.blade.php`, `resources/views/admin/programmes/list.blade.php`.
- **Not touched:** the same `isSuperAdmin()`-only pattern exists in several other list views (Fellows, Trainees, Members, Candidates, Programme Directors, Country Reps, Examiners, Transcript Templates, Admin Accounts, Progressive Reports). Their delete *routes* are now equally reachable by a manage-permission holder per the middleware change above (this was always the config's documented intent), but their Delete buttons still only render for Super Admin — same gap as hospitals had, just not yet reported. Worth a follow-up sweep if/when another role needs delete on one of those.
- **Files:** `app/Http/Middleware/PermissionMiddleware.php`, `app/Models/User.php` (comment only), `resources/views/admin/hospital/{list,_list_content}.blade.php`, `resources/views/admin/hospitalprogrammes/{list,_list_content}.blade.php`, `resources/views/admin/programmes/list.blade.php`.

### Changed (2026-08-18) — Countries list page redesigned: directory rows, interactive globe, quick-view panel
- Rebuilt `admin/countries/list` from a 4-column card grid into a scannable directory list (name + Hospitals/Trainees/Fellows counts per row, chevron to the full profile), per a design mockup. Existing search, "show empty" filter, stat tiles, Visual Report charts, CSV export, and Print all carried over onto the new markup (CSV export now reads straight off each row's `data-*` counts instead of parsing chip text).
- Added a draggable/auto-rotating D3 (v7, CDN) orthographic globe above the directory — clicking a highlighted country, or the new eye icon on a directory row, opens a slide-in "quick view" panel (Hospitals/Trainees/Fellows/Members counts + deep links into the matching tab on that country's full profile) without leaving the list page. Country-name matching between our `countries` table and the world-atlas/Natural-Earth topology names is best-effort (normalize + a small alias map for DRC/Congo/Côte d'Ivoire/Tanzania/Eswatini/CAR/South Sudan) — good enough for the visual/click-through feature, not authoritative.
- Added genuine client-side pagination ("Load More", 20 at a time) to the directory instead of always rendering all rows — filtering/searching still shows every match regardless of page.
- `admin/countries/view/{id}` gained a small hash-tab-activation script (`#ct-hospitals`, `#ct-trainees`, etc.) so the new quick-view panel's action links land directly on the right tab.
- **CSP change required for the globe:** added `connect-src 'self' cosecsamis.org https://cdn.jsdelivr.net` to `SecureHeaders.php` — the globe fetches its world-atlas topology JSON from jsdelivr's npm CDN via `fetch`, which the previous policy (no explicit `connect-src`, falling back to `default-src 'self'`) would have silently blocked. `script-src` already trusted `cdn.jsdelivr.net`, now also used for `d3@7` and `topojson-client@3`.
- **Files:** `resources/views/admin/countries/{list,view}.blade.php`, `app/Http/Middleware/SecureHeaders.php`. No controller/API changes — `CountryController::list()`/`view()` and the underlying `admin/countries` endpoints were already returning everything the new page needed.

### Fixed (2026-08-17) — Programme Directors table: custom.js never updated after the Trainers/PD split
- `public/dist/js/custom.js` still had its DataTable init block targeting `#trainerstable` — the table's id before Programme Directors and the ToT Trainers roster were split into separate tables/pages. The PD list table (`#pdtable`) was left with no matching block, so on page load it rendered as a bare, un-enhanced HTML table (no paging/search/sort/export buttons/stateSave, and none of the dropdown-menu-survives-redraw fix the other roster tables get) until a filter checkbox lazily auto-inited it with zero options. Renamed the block to `#pdtable`, kept the same columns/buttons/loader/dropdown-reinit config used by every other roster table.
- **Files:** `public/dist/js/custom.js`.

### Removed (2026-08-16) — Fees page: removed the Total Collected / Outstanding / Paid Records / Total Records stat row
- Fully removed the stat-chip row (previously hidden behind `d-none`) from `admin/fees` at Davis's request — deleted the markup, its now-unused `.stat-chip` CSS in that page, and the dead `totalCollected`/`totalDue`/`paidCount` view data in `FeesController::manage()`.
- **Files:** `resources/views/admin/fees/manage.blade.php`, `app/Http/Controllers/FeesController.php`.

### Fixed (2026-08-17) — Fees page: payer name link color, hid the top stat row
- `admin/fees` payer-name links were plain Bootstrap blue instead of the COSECSA red used everywhere else — added the `.entity-link` class/style (same pattern as every other associate list page).
- **Files:** `resources/views/admin/fees/manage.blade.php`.

### Added (2026-08-17) — Draft Emails: full associate-group targeting + custom recipient lists
- "Send To (recipient group)" dropdown only had one real option (Country Reps). Expanded it to every associate group — Fellows, Members, Trainees, Candidates, Programme Directors, Trainers (ToT), Country Reps, Examiners — backed by the same `LetterRecipientResolver` the Letters/mail-merge feature already uses, so "who counts as a fellow/trainee/etc" stays in sync between the two features. Added a `members` source to that resolver (it only covered 7 of the 8 associate types before).
- Added a **Custom list** mode: type recipients directly (`Name <email@x.com>` or a bare email, one per line) or **import a CSV** (name + email, either column order, with or without a header row — parsed client-side into the same textarea, no upload endpoint needed). Stored server-side as a new `draft_emails.custom_recipients` JSON column (see cosecsa-api CHANGES.md).
- CC logic generalized: any group with a linked User account (all except Trainers, which have no login) can optionally CC the recipient's personal login email alongside their primary one, not just Country Reps' cosecsa_email/personal-email pair.
- **Files:** `app/Http/Controllers/DraftEmailsController.php`, `resources/views/admin/draft_emails/{form,list}.blade.php`.
- **⚠️ Coordinate:** requires the matching cosecsa-api deploy (new `custom_recipients` migration + `Api\DraftEmailController`/`LetterRecipientResolver` changes) to land first.

### Data correction (2026-08-16) — Fellows specialty/programme/category corrected from college spreadsheet
- Ran a one-off correction from `Data Correction- Fellows.xlsx` (451 rows: Name, Email, Country, Specialty, Fellowship Type, Fellowship Year) via new command `fellows:correct-from-xlsx`.
- Matched 450/451 rows to an existing fellow (email first, then an order-independent name match; only 1 name genuinely not found in the DB, 3 total left for manual review — 2 had a specialty outside the 8 core COSECSA programmes, e.g. "Radiology"/"Oral and Maxillofacial", so only their text label was considered, not a programme link).
- **316 fellows corrected**, mostly backfilling `programme_id` (291 — the correct specialty text was already on file, just never linked to the real `programmes` row), plus 60 Fellowship Type (category_id) fixes and a couple of specialty-text/fellowship-year corrections. `fellows.programme_id` NULLs dropped from 894 → 603.
- Backup taken before running: `/var/backups/fellows_before_correction_xlsx_20260816_013043.sql`; full before/after audit trail: `/var/backups/fellows_correction_applied_20260816_013051.csv`.
- **Files:** `app/Console/Commands/CorrectFellowsFromXlsx.php` (new, re-runnable with `--dry-run`/`--log=`).

### Added (2026-08-17) — Trainers page: edit/delete, hospital linking, click-through, smaller stat tiles
- Stat tiles shrunk to a compact single-line "mini-stat" style and made clickable: Master Trainers/SS/Subspecialty tiles now toggle that slice as a live filter, Total resets everything — same underlying checkbox-filter/DataTable machinery as before, no page reload.
- Added a proper **Edit** page (`admin/associates/trainers/edit/{id}`) and **Delete** action, following the same GET-edit/POST-update/GET-delete-with-confirm convention as the Programme Directors page. The list and detail pages' single "view" icon are now a View/Edit/Delete dropdown (matching the Programme Directors list).
- Added an **Email** column to the list table.
- **Organisation** now links to the matched hospital's info page when cosecsa-api found one (`trainers.hospital_id`, new this round — see that repo's CHANGES.md); falls back to the raw organisation text otherwise. **Country** badges/cells and **Specialty** now link through to the Country/Programme info pages (same click-through pattern already used on the Programme Directors list), and those destination pages (Country, Programme, Hospital) each gained a "Trainers" tab showing who's linked to them — consistent with how they already show Fellows/Trainees/PDs/Country Reps.
- Sidebar: removed the nested "PDs, Trainers & Country Reps" submenu — Programme Directors, Trainers, and Country Reps are now flat top-level items under Associates, same as Fellows/Members/Trainees.
- Added `App\Models\TotYear` (thin read-only mirror of cosecsa-api's model, shared DB) to populate the Edit page's ToT-years checkboxes without an extra API round trip.
- **Files:** `app/Http/Controllers/{TrainerController,CountryController,ProgrammesController,HospitalController}.php`, `app/Models/TotYear.php`, `resources/views/admin/associates/trainers/{list,view,edit}.blade.php`, `resources/views/admin/{countries/view,programmes/view,hospital/view_hospital}.blade.php`, `resources/views/layout/header.blade.php`, `routes/web.php`.
- **⚠️ Coordinate:** requires the matching cosecsa-api deploy (new `trainers.hospital_id` migration + re-import) to land first.

### Added (2026-08-16) — Trainers page: year/country filters, stat cards, print, export
- Mirrors cosecsa-api's relational rework of the Trainers roster (see that repo's CHANGES.md): ToT year attendance and country-attended-in are now proper many-to-many relations instead of flat booleans/a single country column, so the page can filter by either — plus a fix to the specialty→programme matching that was silently failing for ENT/ORL/Urology rows.
- Trainers list page rebuilt: stat cards (total, Master Trainers, SS, unmatched-subspecialty counts), checkbox filters for ToT Year (compact "2019"/"2020"/"2022"… labels — the full cohort name like "Pre-2019 (Master Trainer ToT)" only shows on the trainer's own detail page), Country, and Specialty (a trainer can now show more than one country), a Master-Trainer-only toggle, a Print button (`@media print` hides chrome/filters), and an Export CSV button that respects whatever's currently filtered. Also linked to College Reports (`admin/reports?type=trainers`) for ad-hoc field/grouping exports — added a small `?type=` deep-link handler there so the link lands pre-selected on the Trainers report instead of the blank picker.
- `TrainerController`, `ReportBuilderController`'s `trainers` case, and `LetterRecipientResolver`'s `trainers()` updated for the new `trainer_tot_years`/`trainer_countries` pivot tables (country/years pulled via a correlated `GROUP_CONCAT` subquery so each stays one row per trainer). `config/reports.php`'s `trainers` type dropped its country filter (that now lives properly on the Trainers page itself) and gained a `years_attended` field.
- **Files:** `app/Http/Controllers/{TrainerController,ReportBuilderController}.php`, `app/Services/LetterRecipientResolver.php`, `resources/views/admin/associates/trainers/{list,view}.blade.php`, `resources/views/admin/reports/index.blade.php`, `routes/web.php`, `config/reports.php`.
- **No new migration here** — the schema change (new `tot_years`/pivot tables, dropped columns) lives in cosecsa-api, which owns the `trainers` table on the shared DB; this app only needed code changes.
- **⚠️ Coordinate:** requires the matching cosecsa-api deploy (new pivot tables + re-import) to land first, same as the previous Trainers release.

### Added (2026-08-16) — Split "Trainers" into Programme Directors + a real Trainers page
- The `trainers` table/pages/permission-module have always actually held Programme Directors (one per hospital/programme) — there was no genuine "Trainer" concept in the system. Split the two apart to add a real Trainers page: COSECSA's Master Trainer ToT (Training of Trainers) roster, imported from the master trainer spreadsheet into cosecsa-api's new `trainers` table (see that repo's CHANGES.md for the migration/import details — `php artisan trainers:import-tot-list`).
  - **Renamed** every existing PD-related file/route/permission from "Trainer(s)" to "Programme Director(s)": `TrainerController` → `ProgrammeDirectorController`, `Models\Trainer` → `Models\ProgrammeDirector`, `Imports\TrainersImport` → `Imports\ProgrammeDirectorsImport`, views `resources/views/admin/associates/trainers/*` → `.../programme-directors/*`, routes `admin/associates/trainers/*` → `admin/associates/programme-directors/*` (ApiClient calls moved from `trainers/*` to `programme-directors/*` accordingly, matching the API-side rename). Permission module `trainers` → `programme_directors` (label "Programme Directors"). Updated every other consumer: `DashboardController::globalSearch()`, `HospitalController` (dashboard PD-assign modal, hospital view's PD tab — `assigned_trainer_*` fields renamed `assigned_pd_*`, `trainer_id` renamed `programme_director_id`), `CountryController`'s country profile, `ReportBuilderController`, `LetterRecipientResolver`, `SystemLogger::TRACKED_MODELS`, `admin._role_switcher` partial, sidebar menu + global search dropdown in `layout/header.blade.php`, and every `user_type == 4` label across the app (login role-selection screen, System Logs, fellow dashboard, trainee/candidate "Add Role" dropdowns).
  - **New** read-only Trainers page at `admin/associates/trainers/list` + `.../view/{id}` (`TrainerController` — new class, `resources/views/admin/associates/trainers/{list,view}.blade.php`): name, organisation, email, country attended in, specialty (COSECSA main specialty or subspecialty), ToT years attended (pre-2019, SS2020, 2022–2026), Master Trainer / SS flags, an inline-editable comment. Added to the sidebar under "PDs, Trainers & Country Reps" and to the global search dropdown. New permission module `trainers` (label "Trainers (ToT)"), reusing the `trainers` route-map key freed up by the rename above. No add/edit/import UI yet — the roster is seeded/maintained via the cosecsa-api console command; this page is read-only (plus the one inline-editable Comment field, matching how the PD page's Phone Number field works).
  - **Files:** `app/Http/Controllers/{ProgrammeDirectorController,TrainerController}.php`, `app/Models/ProgrammeDirector.php`, `app/Imports/ProgrammeDirectorsImport.php`, `app/Models/User.php` (`getTrainers()` → `getProgrammeDirectors()`), `app/Http/Controllers/{DashboardController,HospitalController,CountryController,ReportBuilderController,AuthController,ImpersonationController}.php`, `app/Services/{LetterRecipientResolver,SystemLogger}.php`, `app/Models/UserRole.php`, `resources/views/admin/associates/{programme-directors,trainers}/*.blade.php`, `resources/views/admin/_role_switcher.blade.php`, `resources/views/admin/hospital/{dashboard,view_hospital}.blade.php`, `resources/views/admin/countries/view.blade.php`, `resources/views/layout/header.blade.php`, `resources/views/{admin/logs/index,auth/login,fellow/dashboard,admin/associates/fellows/view,admin/associates/members/view,admin/associates/trainees/add,admin/associates/trainees/edit_trainee}.blade.php`, `routes/web.php`, `config/{admin_permissions,reports}.php`.
  - **New migration** `2026_08_16_090000_rename_trainers_permission_to_programme_directors.php` renames the live `permissions` rows (`trainers.view`/`trainers.manage` → `programme_directors.view`/`programme_directors.manage`) in place — every role's existing grants carry over automatically, nobody loses PD access. It also creates the new `trainers.view`/`trainers.manage` permission rows, unassigned to any role by default — grant the new Trainers page per role explicitly via Roles & Permissions.
  - **⚠️ Coordinate:** requires the matching cosecsa-api deploy (table rename + new `trainers` table + route rename) to land first — this app's `ApiClient` calls to `programme-directors/*` and `trainers/*` will 404 against an un-migrated API. Run `php artisan migrate` here before/alongside that deploy.

### Added (2026-08-14) — Edit Programme Director modal on the hospital view page
- The Programme Directors table's inline pencils only covered Phone Number and Assistant PD Name — there was no way to fix a PD's actual name or email (the linked `users` row) without navigating to their full trainer profile. Added an "Edit" button per row opening an `editPdModal` pre-filled with Name, Email, Assistant PD Name, and Assistant PD Email; Phone Number stays as its existing inline pencil rather than being duplicated in the modal.
  - MIS: new `TrainerController::ajaxUpdate()` (`app/Http/Controllers/TrainerController.php`), route `POST admin/associates/trainers/{id}/ajax-update` (`routes/web.php`) — a JSON-returning counterpart to the existing form-based `update()`, reusing the same API call.
  - API: `Api\HospitalController::show()`'s trainers query now also selects `programme_id`/`mobile_no` (`app/Http/Controllers/Api/HospitalController.php`) — `Api\TrainerController::update()` overwrites every one of hospital_id/programme_id/mobile_no from the request rather than only the fields sent, so the modal has to round-trip the trainer's current values for those or they'd get nulled out on save.

### Changed (2026-08-14) — Programmes list back to the card-row layout, restyled
- Brought back the card-list layout (icon, name/type, inline fee stats, edit/delete) instead of the plain bordered table it had been reverted to, per feedback that the card layout read better — but toned down the generic "AI dashboard" styling from that earlier pass: dropped the gradient hero banner and glassy translucent button for the same flat `h5` + count-badge header and solid brand-red button used on `hospital/list.blade.php`, and tightened the card sizing/radius to the app's usual density. Dark-mode colors aligned to the palette already used by the sibling hospital/draft-emails pages (`#1f2937`/`#374151`/`#f87171`) instead of a slightly-off variant.

### Added (2026-08-14) — Inline-edit Programme Directors from the hospital view page
- The hospital view's "Programme Directors" tab table only linked out to each trainer's own profile to make any change. Added the same `ie-field`/`inline-edit.js` pencil-edit component already used on the trainer profile page for Phone Number and Assistant PD Name directly in this table, posting to the trainer's existing `POST admin/associates/trainers/{id}/quick-update` endpoint — no new backend/API work, same allow-listed fields (`Api\TrainerController::quickUpdate()`). Email isn't inline-editable here (or on the trainer profile) since it's the linked login `users` row, not a trainer field.

### Fixed (2026-08-14) — Programme view page ("Results by Year" tab) threw a 500
- `admin/programmes/view/{id}` errored with `Call to a member function firstWhere() on array` whenever a programme had exam results. `ProgrammesController::view()` cast the API's `examResultsByYear` object to an array with `collect((array) ...)`, which only wraps the *outer* keyed-by-year structure in a Collection — each year's own value stayed a plain PHP array, and the view calls `$rows->firstWhere(...)` on it. Fixed by mapping each year's rows through `collect()` too. Longstanding bug — first seen in production logs 2026-08-10, never previously traced.

### Fixed (2026-08-14) — Hospital view "Add Programme" modal threw a 500
- The modal's Accredited/Expiry Date fields used `<input type="date">`, submitting a full `YYYY-MM-DD` value. `Api\HospitalProgrammesController::store()` (ported from the standalone Hospital Programmes module, where the equivalent form uses `<input type="month">`) parses those fields with `Carbon::createFromFormat('Y-m', $date)`, which throws `Trailing data` on a day-of-month component — a hard 500 on every submission. Changed both inputs to `type="month"` to match the API's expected format (`resources/views/admin/hospital/view_hospital.blade.php`), instead of loosening the API parser, which the standalone module still relies on being strict.

### Added (2026-08-14) — Add another role to fellows
- New "Add Role" button on the fellow profile page opens a modal to grant a fellow an additional role — Trainer/Programme Director, Country Representative, or Examiner — **without creating a duplicate user**. The new role record is linked to the fellow's existing `user_id`/login via a new `UserRole` row, reusing the same pattern already used by the `admin._role_switcher` "Also in:" chips.
  - MIS: `FellowsController::addRole()` (`app/Http/Controllers/FellowsController.php`), route `POST admin/associates/fellows/{id}/add-role` (`routes/web.php`), modal + JS in `resources/views/admin/associates/fellows/view.blade.php` (role-specific fields: hospital/programme for Trainer, country for Country Rep, country/specialty/subspecialty for Examiner). `FellowsController::view()` now also passes `fellowHospitals` for the modal's hospital dropdown.
  - API: `Api\FellowController::addRole()` (`app/Http/Controllers/Api/FellowController.php`) validates the fellow exists and doesn't already hold that role (via the existing `relatedProfiles()` helper), creates the role-specific record (`Trainer`/`CountryRepsModel`/`ExamsModel`) pointed at the fellow's existing `user_id`, and upserts the matching `UserRole` row — all inside a DB transaction. Route: `POST admin/internal/fellows/{id}/add-role` (`routes/api.php`).

### Added (2026-08-14) — Hospital view: add programme, add PD, map fellows
- Hospital view page (`admin/hospital/view_hospital/{id}`) gains three actions, all via in-page modals (no page navigation needed):
  - **Add Programme** — multi-select of not-yet-accredited programmes, posts to the existing `admin/hospital-programmes` store endpoint (already used by the standalone Hospital Programmes module) via a new thin MIS wrapper `HospitalController::addProgramme()`.
  - **Add Programme Director** — search-existing-fellow-or-create-new widget (shared partial `resources/views/admin/hospital/_fellow_picker.blade.php`), then calls the fellow's `add-role` endpoint (role_type=4/Trainer) with this hospital+programme, reusing the "Add another role to fellows" feature above instead of creating a separate trainer login.
  - **Map Fellow** — same search-or-create widget, posts to a new `fellow_id`↔`hospital_id` mapping (new table `hospital_fellow_mappings`, migration `2026_08_14_090000_create_hospital_fellow_mappings_table`, model `HospitalFellowMapping`) so a fellow can be explicitly linked to a hospital independent of the existing implicit country+programme match. The Fellows tab now merges both sources (badge-labelled "mapped") and lets you remove an explicit mapping.
  - New reusable pieces: `Api\FellowController::search()` (`GET internal/fellows/search?q=`) typeahead used by both widgets; `Api\HospitalController::mapFellow()`/`unmapFellow()`; MIS `FellowsController::search()`/`quickCreate()` proxies. `FellowController::store()` now defaults to a random password when the quick-create flow doesn't collect one.

### Added (2026-08-14) — Draft Emails section
- New "Draft Emails" sidebar item (`admin/draft-emails`) — a full CRUD list of reusable email drafts (name, subject, Summernote rich-text body), modelled on the existing single-record examiner email-template editor (`admin/exams/email-template`) but supporting any number of named drafts instead of just one.
  - API: new table `draft_emails` (migration `2026_08_14_091500_create_draft_emails_table`), model `DraftEmail`, `Api\DraftEmailController` (index/show/store/update/destroy), routes under `admin/draft-emails` (`routes/api.php`). Kept as a separate table from the legacy `email_templates` table so this doesn't touch the working examiner bulk-email feature.
  - MIS: `DraftEmailsController` (`app/Http/Controllers/DraftEmailsController.php`), routes in `routes/web.php`, views `resources/views/admin/draft_emails/{list,form}.blade.php` (shared form for add/edit, same Summernote setup as the examiner email-template page).
  - The add/edit form now has a live "how it will look when sent" preview (same two-column layout, subject bar, COSECSA header/footer, and `[Name]` → "Dr. Example" substitution as the examiner email-template page), updating as you type the subject or edit the body.

### Performance
- Cache the `countries`, `hospitals`, and `programmes` lookup lists (`Country::getCountry()`, `HospitalModel::getHospital()`, `Programme::getProgramme()`) for 1h instead of re-querying MySQL directly on almost every add/edit/view page. `HospitalController` and `ProgrammesController` now call `Cache::forget()` on their write paths so edits appear immediately instead of waiting out the TTL.
- Production deploy now rebuilds `config:cache`/`route:cache`/`view:cache` after every pull instead of only clearing them (see `~/cosecsa/HANDOFF.md` § Performance notes).
- Fixed production `.env`: `APP_DEBUG` was set to `true` (now `false`) and `LOG_LEVEL` was `debug` (now `error`), both of which added per-request overhead and were unintentional in production.

### Added
- Inline pencil edit for trainee and fellow profile pages.
  - New reusable components: `public/dist/css/inline-edit.css` and `public/dist/js/inline-edit.js`.
  - Added pencil icons to editable fields on trainee view (`resources/views/admin/associates/trainees/view.blade.php`).
  - Added pencil icons to editable fields on fellow view (`resources/views/admin/associates/fellows/view.blade.php`).
  - Added fellow quick-update route and controller method (`routes/web.php`, `app/Http/Controllers/FellowsController.php`).
  - Included inline-edit assets in the main layout (`resources/views/layout/app.blade.php`).
- Extended the inline pencil edit to the remaining roster profile pages, reusing the existing `ie-field`/`inline-edit.js` component:
  - Trainer view — hospital, phone number, assistant PD name/email, mobile number (`resources/views/admin/associates/trainers/view.blade.php`).
  - Member view — personal email, country, gender, mobile, membership year, admission year, address, status (`resources/views/admin/associates/members/view.blade.php`).
  - Country Rep view — position, country, cosecsa email, mobile number (`resources/views/admin/associates/reps/view.blade.php`).
  - Candidate view — gender, personal email, country, hospital, programme, admission/exam year, invoice status, repeat papers, MMed, remarks (`resources/views/admin/associates/candidates/view_candidate.blade.php`).
  - Examiner view — email, secondary email, mobile, country, gender, status, specialty, sub-specialty (`resources/views/admin/exams/view_examiner.blade.php`).
  - Added `quickUpdate()` controller methods + `POST .../{id}/quick-update` routes for trainers, members, country reps, candidates, and examiners (`routes/web.php`, `TrainerController`, `MembersController`, `CountryRepsController`, `CandidatesController`, `ExamsController`).
  - Added matching `quickUpdate()` API endpoints and internal routes in `cosecsa-api` for trainers, members, country-reps, candidates, and examiners (`Api\TrainerController`, `Api\MemberController`, `Api\CountryRepController`, `Api\CandidateController`, `Api\ExaminerManagementController`, `routes/api.php`).
- Extended inline pencil edit into the **Fees & Payments** tabs on the trainee, fellow, and candidate profile pages:
  - Fellow: all fields editable — they're the fellow's own columns (`prog_entry_fee_year`, `prog_entry_mode_payment`, `sponsored_by`, `registered_by`, `secretariat_registration_date`, `exam_fee_year`, `exam_fee_date_paid`, `exam_fee_mode_payment`, `exam_fee_amount_paid`, `exam_fee_payment_verified`).
  - Trainee: only the entry-fee block matching the trainee's own current programme is editable (`invoice_number`, `invoice_amount`, `invoice_status`, `amount_paid`, `mode_of_payment`, `payment_date`, `sponsor`); other rows in that tab are synced in from Salesforce and stay read-only there (edit via Fees Log instead).
  - Candidate: the candidate's own Examination Fee block is fully editable (`invoice_number`, `invoice_date`, `invoice_amount`, `invoice_status`, `fee_paid`, `amount_paid`, `payment_date`, `mode_of_payment`, `sponsor`); the linked trainee's Programme Entry Fee block is also editable and posts to the trainee's own quick-update endpoint.
  - Added the corresponding fields to the `quickUpdate()` allow-lists in `Api\FellowController`, `Api\TraineeController`, and `Api\CandidateController`.
- Added inline pencil edit to the two genuinely-editable fields surfaced on the candidate **Admin** tab's linked-trainee summary (`status`, `admission_year`), posting to the trainee's quick-update endpoint. The rest of the Admin/Admin Notes tabs (login username, database record IDs) are intentionally left read-only — they're login credentials and primary keys, not profile data.

### Changed
- Admin dashboard tile counts are now sourced from API endpoints instead of local database queries (`app/Http/Controllers/DashboardController.php`).
- Trainee quick-update endpoint in the API now supports additional fields including `hospital_id`, `personal_email`, `gender`, and `invitation_letter_status`.
- New fellow quick-update endpoint in the API supports fields such as `personal_email`, `phone`, `address`, `country_id`, `gender`, `specialty`, `organization`, `status`, `fellowship_type`, `programme_id`, `admission_year`, `mcs_qualification_year`, and `fellowship_year`.

### Fixed (2026-08-12) — Global search: dual-role trainees, missing entity types, two silent-mismatch bugs
- **Trainees hidden by primary-role gate.** `globalSearch()`'s trainees section filtered on `users.user_type = 2`, which only stores one primary role per user. Anyone whose primary role is Fellow/Trainer/Examiner but who also holds an active trainee role (dual-role users — second-specialty retraining fellows, a trainer/examiner on a trainee-tracked programme) never appeared in search despite having a real trainee record. Confirmed 15 people hidden in production, incl. Sama Akanyun and the 12-person retraining batch. Fixed by gating on `user_roles.role_type = 2 AND is_active = 1`, matching `TraineeController::listData()`/`bulkUpdateData()` in the API.
- **Fellow-promotion exclusion missing `programme_id` check.** The same trainees section excluded any trainee row whose `entry_number` matched a `fellows.candidate_number`, without also requiring the same `programme_id` — so genuine second-specialty retraining trainees (same PEN, new programme) were wrongly treated as leftover rows from their already-completed programme and hidden. Added the `programme_id` match, mirroring the fix already applied to `TraineeController::listData()`.
- **Search only covered 4 of 7 roster types.** Added Trainers, Members, and Country Reps sections to `globalSearch()` (previously only Trainees/Candidates/Examiners/Fellows) and updated `header.blade.php`'s results renderer to match.
- **Members section matched nothing.** `members.is_deleted` is `ENUM('0','1')`, not a boolean/int column like `users.is_deleted` (tinyint). Comparing it against integer `0` matched the enum's internal storage index (`1`) instead of the `'0'` label, so the new members search silently returned zero rows for every query. Fixed by comparing against the string `'0'`, matching `MemberController::index()` in the API.
- **cosecsa-api companion fix:** `candidates.exam_year` was a hard-coded `ENUM('2024'..'2027','')` while the `trainees.exam_year` it syncs from is an unrestricted int (775 rows outside that range) — any sync write outside the enum threw "Data truncated for column". Migrated to `YEAR` type. ⚠️ The first attempt at this migration (`ALTER TABLE ... MODIFY exam_year YEAR`) silently converted the enum's internal storage *index* rather than its string label (`'2024'`→`2001`, `'2025'`→`2002`, `'2026'`→`2003`, `'2027'`→`2004`), corrupting 1,382 rows; caught immediately during post-fix verification and corrected with a follow-up migration using the confirmed deterministic reverse mapping — no data loss. All 3 trainee→candidate sync call sites now also wrap the save in try/catch so a future failure can't 500 the trainee update itself. See `cosecsa-api/CHANGES.md` (2026-08-12) for full detail.

## Template

When documenting a new change, use the following format:

```markdown
### Added
- Description of new feature.

### Changed
- Description of change to existing functionality.

### Fixed
- Description of bug fix.

### Removed
- Description of removed feature or file.
```
