# Change Log

> **Important:** Every new change, feature, bug fix, or configuration update must be documented in this file. Add entries under the `[Unreleased]` section as work is completed, then move them to a versioned section when a release is tagged.

## [Unreleased]

### Added (2026-10-21) — 83 more icons for the course page editor
- The icon picker now offers **103 icons** (it was 20): medical (stethoscope, syringe, pill, bone, microscope, thermometer, heart pulse, blood drop, scissors, medical cross), people and roles, documents and folders, calendar, clock and timer, communication (chat, megaphone, phone, microphone, video), security, charts, maps, money, arrows and status marks. Each has a name: hover for it, or type in the new search box. The icons are added to the course sprite (`course/partials/icons.blade.php`), so they show on the learner pages exactly as the originals.
- **Files:** `resources/views/course/partials/icons.blade.php`, `resources/views/learning/admin/coded-module.blade.php`, `app/Http/Controllers/Learning/AdminLearningController.php`.

### Added (2026-10-21) — Quick summary drawer on the Associates lists
- Clicking a person's name on the **Trainees, Candidates, Fellows, Alumni, Members, Programme Directors and Country Reps** lists now opens a side drawer (the same look as the fees subscription drawer) with a short summary: name and photo, three headline figures, contact, key facts and **Open full page** / **Edit** buttons. The action menu's "View" item, ctrl/cmd/shift-click and middle-click still open the full page. Nothing changes on other pages.
- Summary comes from cosecsa-api (`internal/associate-preview/{type}/{id}`) through `admin/associates/{segment}/preview/{id}` (permission by the same URL prefix as each list; the Edit button only shows to people who can manage that module).
- Name cells on the Members, Programme Directors, Country Reps and Alumni lists are now links (they were plain text).
- **Files:** `resources/views/partials/associate_drawer.blade.php`, `resources/views/layout/app.blade.php`, `app/Http/Controllers/AssociatePreviewController.php`, `routes/web.php`, `resources/views/admin/associates/{members,programme-directors,reps,alumni}/list.blade.php`.

### Changed (2026-10-21) — Trainees who deferred and resumed: exam year counted from the resume year
- Choosing a **Resume Year** on the trainee edit form (or editing it inline) now recounts the **Exam Year** from the year they continue (resume year + years left - 1; years left uses the Study Year they resume in). The form updates the exam year as you choose and the server applies the same rule on save. See cosecsa-api CHANGES.md.
- **Files:** `resources/views/admin/associates/trainees/edit_trainee.blade.php`.

### Changed (2026-10-21) — Examiner course: a full page editor (icons, colours, tables, blocks), not only wording
- **Admin > Examiner Training > Course content > a module** is now a page editor: blocks on the left, live preview of the page on the right. Per page: banner label, title, intro and **banner colours**; per topic card: **icon** (picker), **accent colour**, title, subtitle, move up/down, duplicate, delete, add a topic card; per block: change style (text, lead, heading, note, callout), **lists** (ticks, crosses, bullets, numbered, steps; add/remove/reorder items), **tables** (edit cells, add/remove rows and columns), **pictures** (replace, description, caption), move/duplicate/delete, add new blocks; designed blocks (card grids, tabs, video, animation...) can be reworded, moved, copied or removed (and their HTML edited by an expert). "Save page" publishes; "Discard changes" and "Back to original design" undo. The preview updates as you work and clicking anything in it selects its block.
- The first time a page is opened its own coded design is read into this editable form (`App\Support\CoursePage::parse`) and saved; examiners then see the page rendered from that data (`course/modules/_page.blade.php`), identical to before until something is changed. Image sources are stored host-independent (`{{img}}`). Uploaded pictures go to `public/images/course/uploads/`.
- The quiz keeps its own question editor. The earlier wording-only overrides still exist in the API but the editor no longer uses them.
- **Files:** `app/Support/CoursePage.php`, `app/Http/Controllers/Learning/{Admin,Examiner}LearningController.php`, `routes/web.php`, `resources/views/learning/admin/coded-module.blade.php`, `resources/views/course/modules/_page.blade.php`, `resources/views/course/layout.blade.php`, `resources/views/learning/examiner/coded-frame.blade.php`, `tests/Feature/CoursePageTest.php`. **Server:** `public/images/course/uploads` must exist and be writable by the web user.

### Changed (2026-10-21) — Examiner course: split wording editor, previous/next links, sidebar type
- **Admin wording editor** is now two columns: the blocks of wording on the left, a **live preview** of the page on the right. Typing updates the preview at once; clicking a block opens and highlights that spot in the preview; clicking wording in the preview jumps to its block. The preview never records progress. (`learning/admin/coded-module.blade.php`, `learning/examiner/coded-frame.blade.php`, `AdminLearningController::moduleFrame`, route `admin/exams/learning/modules/{id}/frame`; `CourseText::apply` can tag each piece of wording with `data-cwkey`.)
- **Previous / Next module** links under every coded course page (and the quiz page), as on the Completion page. They are plain links: a module still completes by reading its topics or passing the quiz. (`learning/examiner/coded-shell.blade.php`)
- **Module menu type:** the sidebar's module names are Merriweather (the course's text font) and its labels Raleway, matching the course. (`learning/examiner/_shell-styles.blade.php`)

### Added (2026-10-21) — Examiner course: admins edit the wording of the coded pages
- **Admin > Examiner Training > Course content > a module** now opens a wording editor for Modules 1–7 and the Acknowledgements (the pages of the final design pack): every heading, topic title, subtitle, paragraph, list item, highlighted note and table cell is a line you click, change and Save (bold, italic and links supported). Examiners see the new wording straight away; "Reset to original" restores the page's own wording; search finds wording on the page; edits show who made them. Topic cards, pictures, table layout and animations are part of the page design and are not editable here. The Quiz keeps its question editor.
- How it works: `App\Support\CourseText` finds every piece of wording in a page (stable key from the original wording), the admin saves overrides in cosecsa-api, and `course/layout.blade.php` applies them when the page is shown (examiners, and the admin preview). If a developer later changes the page's own wording, the matching edit goes "stale" and is listed at the bottom of the editor.
- **Files:** `app/Support/CourseText.php`, `app/Http/Controllers/Learning/AdminLearningController.php`, `routes/web.php`, `resources/views/learning/admin/coded-module.blade.php`, `resources/views/course/layout.blade.php`, `tests/Unit/CourseTextTest.php`.

### Changed (2026-10-21) — Examiner course fonts: Raleway headings, Merriweather text
- Headings, topic titles, labels and table headings are Raleway; body text, lead paragraphs, lists and callouts are Merriweather (swapped in the `--display` / `--body` tokens of `public/css/course/cosecsa-course.css`; the 12–13px caps labels stay Raleway). The course CSS/JS links now carry a file-time version so browsers pick up changes straight away.
- **Files:** `public/css/course/cosecsa-course.css`, `public/css/course/modules/*.css`, `resources/views/course/layout.blade.php`.

### Fixed (2026-10-21) — Adding a workplace on a fellow no longer adds a hospital to the accredited list
- The "New" button next to the workplace on Add/Edit Fellow created a real hospital record every time it was saved (nine on 6 Oct 2026, one name three times), so the accredited hospitals list grew past 162 with places that are not accredited. It now only sets the fellow's workplace (the free-text `organization` already stored on the fellow) and creates nothing.
- **Files:** `app/Http/Controllers/FellowsController.php`, `resources/views/admin/associates/fellows/_add_hospital_modal.blade.php`.

### Changed (2026-10-21) — Examiner Training: light mode only
- The Light/dark button is removed from every course page and the course always uses the light colours (the wrapper carries `data-theme="light"`, which switches off the device-based dark rules).
- **Files:** `resources/views/course/layout.blade.php`, `resources/views/course/modules/*.blade.php`, `public/js/course/cosecsa-course.js`.

### Changed (2026-10-20) — Examiner Training: the nine pages of the final design pack
- Modules 1–7, the Quiz and the Acknowledgements are now the coded pages of the final pack (Merriweather/Raleway, COSECSA green/crimson/gold, collapsed topic cards, Module 5 Zoom walk-through, Module 6 marking sheets, **new Module 7 Marking Fellowship Exams** with the descriptors). They render inside the existing player (module sidebar and drawer kept). Which page a module shows is `settings.view` in cosecsa-api; a module without one still shows its blocks (the Completion/certificate page does).
- Topic progress is saved per learner (the page posts to `examiner/learning/{slug}/topics`); a module completes when every topic card has been read. The quiz is driven by the server: each answer is checked on the server (instant feedback and explanation), the score is saved when all 20 are answered, pass mark 90% (18 of 20) unlocks the course; "Start again" retakes it.
- Admin preview shows the same pages (nothing is recorded there). The player sidebar and styles were moved into partials (`learning/examiner/_sidebar`, `_shell-styles`) so the coded pages share them.
- **Files:** `resources/views/course/**`, `public/{css,js,images}/course/**`, `config/course.php`, `resources/views/learning/examiner/{coded-shell,_sidebar,_shell-styles,player}.blade.php`, `app/Http/Controllers/Learning/{Examiner,Admin}LearningController.php`. Kit deviations: `cosecsa-course.js` (server-side progress), `quiz.js` and `quiz.blade.php` (server-checked quiz), `course/layout.blade.php` (save URL attributes).

### Changed (2026-10-20) — Intake Reports: this year's intake counts admitted trainees
- New default "Year of joining" option: for the current year, a trainee counts when admission year is this year, status is Active and Fee Paid is Yes (the admission list's "Complete" + "Fee Paid = Yes"), dated by the admission year even when the PEN carries an older year. Earlier years still use the PEN year. The other two options are unchanged. See cosecsa-api CHANGES.md.
- **Files:** `resources/views/admin/associates/intake-reports/index.blade.php`.

### Added (2026-10-20) — Tags filter on Trainees, Candidates and Fellows lists
- New "Tags" drop-down (searchable, tick several; a person shows if they have any ticked tag) in the filter bar of Associates > Trainees, Candidates and Fellows. The tags are the Capsule tags (e.g. "2019 Intake", "Passed MCS 2021") that cosecsa-api links to each person (`php artisan capsule:import-excel`). People without a linked Capsule contact have no tags.
- **Files:** `app/Http/Controllers/{Trainee,Candidates,Fellows}Controller.php`, `resources/views/admin/associates/{trainees/trainees,candidates/list,fellows/list}.blade.php`.

### Fixed (2026-10-20) — Salesforce (and other pages) failing after the loopback API change
- `ApiClient` called paths with a trailing slash (`salesforce/`, `fees/`, `letters/`, `capsule/`, and POSTs to `members/`, `trainees/`, `candidates/`, ...). Laravel answers those with a 301 to the public `http://api.cosecsamis.org/...`; with the API URL now `http://127.0.0.1:8081` the redirect left the loopback and lost the token, so the page got an error. `ApiClient::url()` now trims the trailing slash.
- **Files:** `app/Services/ApiClient.php`.

### Fixed (2026-10-20) — Sidebar: submenus indented in light mode, "Trainees → Candidates" on one line
- In light mode `custom.css` gave every sidebar link `padding: 9px 18px !important`, which overrode the submenu indent the header sets (`.nav-treeview .nav-link { padding-left: 2.4rem }`), so Associates (and every other submenu) sat flush with its parent while dark mode showed them indented. Light mode now uses the same 2.4rem indent. The third level (Associates > Associate Promotions) gets 2.9rem left, 10px right padding and a 6px icon gap in both modes, so the long "Trainees → Candidates" label fits on one line in light mode too (light mode uses Source Sans 3, wider than the dark mode's Source Sans Pro). Measured on a page built from the real AdminLTE, `custom.css` and header CSS: light mode wrapped to 2 lines before and 1 after; dark mode stays on 1 line.
- **Files:** `public/dist/css/custom.css`.

### Added (2026-10-20) — Associates > Intake Reports
- New page `admin/associates/intake-reports` (menu: Associates > Intake Reports; shown with `trainees.view`, `candidates.view` or `fellows.view`). KPIs (entries, different people, MCS, FCS, people in several intakes), a year by programme matrix (click a number or a year to list the people), an MCS/FCS-by-year chart, a detail table with Excel/CSV export (with the person's other intakes as a "Journey"), filters (years, level, programme, country, status, gender) and a "Year of joining" switch (PEN year with later programmes dated by admission year, or PEN year only), and a Data checks panel (what was merged, left out or needs a decision). Numbers come from cosecsa-api `internal/intake-report/*`; see its CHANGES.md for the counting rules.
- Tested by rendering the real page with data produced by the real API code (synthetic associates) in Chrome: KPIs, matrix, chart, checks, click-through to the detail table, export buttons; no console errors. Not yet seen on production data or a phone-sized window.
- **Files:** `app/Http/Controllers/IntakeReportController.php`, `resources/views/admin/associates/intake-reports/index.blade.php`, `resources/views/layout/header.blade.php`, `routes/web.php`, `config/admin_permissions.php`.

### Added (2026-10-20) — Examiner Training, part 2: hero banners, new typography, tick lists, sectioned quiz, topic progress saved to the account
- **Hero banner** (`learning/partials/hero.blade.php`) from `module.hero` (eyebrow, heading, text, `--hero1`/`--hero2` gradient); the module's accent colour (progress bar, accent bars) follows the hero. **Typography** per the brief (Literata / Public Sans / IBM Plex Mono, 16.5 px body, 18.5 px lead, 20 px sub-headings with a 30 px accent bar) in `learning/partials/course-ui.blade.php`, light and dark. A text block with `payload.lead` renders as the lead paragraph. New list variants `ticks` and `crosses` (plain lists with a mark, no tick boxes).
- **Topics read are saved to the learner progress** (`POST examiner/learning/{slug}/topics` → API `topics-read`), merged with the browser copy so they follow the examiner across devices; nothing is saved in the admin preview.
- **Quiz page rebuilt** (`learning/examiner/quiz.blade.php`): six section cards, tapping an answer shows right/wrong at once with the key learning point (`POST examiner/learning/{slug}/quiz/check`, preview: `admin/exams/learning/preview/{slug}/quiz/check`), "x of 20 answered", a results card with score and Start again; the full set of answers is sent once to `submit` (now also answers JSON) so scoring, attempts and completion are unchanged.
- Tested by rendering the real views from a SQLite copy of the generated blocks in Chrome (light and dark): hero, topics, tick lists, quiz feedback and results card. Not yet checked: a phone-sized window (the browser would not resize) and the live API.
- Content is applied by `php artisan lms:update-examiner-course` in `cosecsa-api`; deploy this repo first. **Files:** `app/Http/Controllers/Learning/{Examiner,Admin}LearningController.php`, `routes/web.php`, `resources/views/learning/{partials/{hero,course-ui,topics},blocks/{list,text},examiner/{player,quiz}}.blade.php`.

### Added (2026-10-19) — Examiner Training: topic-card layout for Modules 3 and 4
- A module whose blocks contain topic markers (a divider block with `payload.topic` {title, subtitle}; `payload.topic_end` closes the list) now shows collapsed numbered topic cards, one open at a time, a "N of M topics read" bar, a green tick on each opened topic, and a completion message plus closing card after the last topic. Topics open in any order. Progress is kept per examiner in the browser (not in the admin preview). Modules without markers keep the long-page layout; the existing Previous/Next module links are unchanged.
- New block renderers: `details` (collapsed list), `tiles` (tiles, pills or side-by-side cards) and `dodont` (green tick / red cross panels, plain lists). Images can carry a still frame shown instead of an animated GIF when the visitor prefers reduced motion; videos can carry a poster. Light and dark styles in `learning/partials/styles.blade.php`.
- Content is applied by `php artisan lms:update-examiner-modules-3-4` in `cosecsa-api` (see its CHANGES.md); deploy this repo first. Tested by rendering the real partials from a SQLite copy of the generated blocks in Chrome (topics open/close, tick, panels). Video captions are not included (no caption files supplied).
- **Files:** `app/Support/LearningView.php`, `resources/views/learning/partials/{topics,segment,styles}.blade.php`, `resources/views/learning/blocks/{details,tiles,dodont,_dispatch,_img,multimedia}.blade.php`, `resources/views/learning/examiner/player.blade.php`.

### Fixed (2026-10-17) — Examiner Training: flashcards overflowed and had stretched word gaps (Module 5 OSCE cards)
- Card text carried inline 18px / justified styles from the old course, so the back text overflowed the 190px card and justified words spread apart; the grid also left the fourth card alone on a second row. Card text is now centred at 14px, the card is 220px tall (long text scrolls inside it), and the grid uses columns of at least 280px (2 × 2 on medium widths, 4 across on wide ones). Checked in Chrome: all four backs fit inside their cards.
- **File:** `resources/views/learning/partials/styles.blade.php`.

### Added (2026-10-17) — Examiner Training: Table block, image alt text (for the Module 2, 5 and 6 rebuild)
- New `table` block (`resources/views/learning/blocks/table.blade.php`, dispatched from `_dispatch`): header row, optional row headers, scrolls sideways inside its own box on phones (keyboard-focusable), light and dark styles in `learning/partials/styles.blade.php`; "Table" icon in the add-block menu. Images now render `payload…image.alt` (empty as before when unset).
- Content is applied by `php artisan lms:update-examiner-modules` in `cosecsa-api` (see its CHANGES.md); deploy this repo first. Checked in Chrome from the real Blade partials: tables render and scroll; all three modules render with no errors.
- **Files:** `resources/views/learning/blocks/table.blade.php`, `_dispatch.blade.php`, `_img.blade.php`, `resources/views/learning/partials/styles.blade.php`, `resources/views/learning/admin/module.blade.php`.

### Fixed (2026-10-17) — Offline: Edit on a waiting FCS mark works for every specialty (e.g. Orthopaedic), with or without a saved Resubmit page
- Edit opened the FCS Resubmit page only when the device already had a saved Resubmit page of the *same specialty and format* (Orthopaedic Clinical with no earlier saved Orthopaedic Clinical result had none, so it fell back to the in-page editor). Now, when no matching page exists, the service worker serves the saved Results page and `examiner-offline.js` (`buildFcsFormPage`) builds the whole Resubmit form from the waiting mark, laid out like `resubmit_form_universal` (title, read-only group / candidate / station, one column per case with the right question labels and mark options (urology Viva adds 0), Add Question per case, total, remarks, Cancel and Update Evaluation); `hydrateFcs` then fills and saves it exactly as before. A matching saved page is still used when there is one. The "Edit" decision no longer needs a same-specialty page (any saved Results page is enough).
- **Tested on my own, before pushing:** in Chrome against a stub server with the real script and service worker, server down, for all 16 specialty × format combinations (cardiothoracic, neurosurgery, orthopaedic, ent, paediatric_orthopaedics, paediatric, plastic_surgery, urology × clinical / viva, with their real case shapes: 1, 2 or 2×3/4 marks): each page was built, the saved marks, hidden ids, read-only texts, total and remarks matched, changing the first mark recalculated the total, saving updated the single queued mark and returned to Results — 16 of 16 passed. The Edit button on the Results card was also clicked for Orthopaedic Clinical (form built, 4 marks) and Urology Clinical (saved matching page used, 6 marks). Found and fixed on the way: a string `onsubmit` attribute broke `el()`; the form title no longer repeats "Evaluation Form".
- **Files:** `public/dist/js/examiner-offline.js`, `public/examiner-sw.js`.

### Changed (2026-10-17) — Offline: View on a waiting mark opens the View marks page
- In the Results card "Saved on this device", **View** opened an in-page dialog. It now goes to the View marks page: `examiner/view_results/{candidate}/{station}?eo=<id>&type=mcs|gs` for MCS / General Surgery and `examiner/view_fcs_results/{candidate}?eo=<id>&type=fcs` for FCS. The server has no such mark yet, so the service worker serves any saved View page of that kind and `examiner-offline.js` (`hydrateQueuedView`) rebuilds its card from the waiting mark(s) with the same layout: MCS / GS show the candidate, group · station, total, grade (MCS), the marks per question and remarks; FCS shows every waiting Clinical and Viva mark of that candidate and specialty (overall, clinical and viva totals, station blocks with case headings, remarks). The card says it is saved on this device and has Edit links (to the waiting mark's Resubmit page) and All results. If no saved View page of that kind exists on the device, View falls back to the in-page viewer.
- Tested in Chrome against a stub server with the real script, service worker and the real View page markup, server down: an FCS candidate with a Clinical and a Viva mark showed overall 42 (22 + 20) with both blocks and Edit Clinical / Edit Viva; an MCS mark showed 10.0, Borderline, both question marks and its remarks.
- **Files:** `public/dist/js/examiner-offline.js`, `public/examiner-sw.js`.

### Changed (2026-10-17) — Offline: Edit on a waiting FCS mark opens its Resubmit page too
- In the Results page card "Saved on this device", Edit went to the Resubmit page only for MCS and General Surgery marks; FCS (Clinical / Viva) marks fell back to the in-page editor. Edit now opens `examiner/fcs-resubmit/{candidate}/{clinical|viva}?eo=<id>&type=fcs&exam=<specialty>&format=<format>`. The server has no such mark yet, so the service worker serves a saved Resubmit page of the same specialty and format (any candidate; the `exam_type` / `form_type` fields identify it), and `examiner-offline.js` (`hydrateFcs`) fills it with the waiting mark: group, candidate, station, the marks case by case (adding or removing question rows to match), total and remarks. Saving changes the waiting mark itself (its `question_marks_caseN[]`, `total_marks`, `remarks`) and returns to Results; it is still submitted once, when online. If no saved Resubmit page of that specialty and format exists on the device, Edit falls back to the in-page editor.
- Tested in Chrome against a stub server with the real script and service worker: with the server down, Edit opened the Urology Clinical resubmit page with the six marks in two cases, 22 total, "Better", read-only group/candidate/station filled; changing one mark and saving left one queued mark with the new mark, total 28 and new remarks.
- **Files:** `public/dist/js/examiner-offline.js`, `public/examiner-sw.js`.

### Fixed (2026-10-17) — 500 on the Urology Clinical Resubmit page (`examiner/fcs-resubmit/{candidate}/clinical`)
- **Cause:** the Urology Clinical marking form is a two-case form (3 marks per case, 6 marks saved), but `showFcsResubmitForm` treated every Clinical form as one case except neurosurgery and plastic surgery. The Resubmit page then tried to lay six marks out under the four single-case labels and failed with `Undefined array key 4` (laravel.log, 2026-10-17 12:07 and 12:09 for userId 8497). It would fail for any urology Clinical Resubmit (116 saved urology Clinical marks have six marks).
- **Fix:** Clinical is two cases for urology as well (neurosurgery, plastic surgery, urology); everything else is unchanged. The Cardiothoracic Viva special case (four cases) is removed so it matches the marking form (two cases); no Cardiothoracic marks exist in the database.
- **Test:** `test_urology_clinical_resubmit_page_lays_out_the_two_saved_cases` posts a urology Clinical mark and opens its Resubmit page: it returned 500 with the old rule and renders six marks in two cases now; all 32 tests pass (the test database gained `exams_groups`, `candidates.user_id` and a urology results table).
- **Files:** `app/Http/Controllers/CandidatesController.php`, `tests/Feature/ExaminerOfflineTest.php`.

### Changed (2026-10-17) — Results: FCS Resubmit goes straight to the Clinical or Viva form
- In the FCS Specialty Results table the single **Resubmit** button led to a "choose Clinical or Viva" page first. The Actions column now has **Resubmit Clinical** and **Resubmit Viva** (each shown only when that mark was saved), linking directly to `examiner/fcs-resubmit/{candidate}/clinical|viva`. The choose-a-format page still exists for old links. The offline result-page prefetch now also follows these links (`examiner-offline.js` `RESULT_PAGE`). New test renders the Results page: a clinical-only candidate gets no Viva button, a candidate with both gets both links and none to the generic page (`tests/Feature/ExaminerOfflineTest.php`, plus a minimal `tests/Fixtures/views/layout/app.blade.php` so a page can be rendered without the sidebar's queries); all 31 tests pass.
- **Files:** `resources/views/examiner/results.blade.php`, `public/dist/js/examiner-offline.js`, `tests/Feature/ExaminerOfflineTest.php`, `tests/Fixtures/views/layout/app.blade.php`.

### Changed (2026-10-17) — Examiner profile (`examiner/profile_settings`): real dark mode instead of forced white cards
- The page forced a light theme (`.card { background:#fff !important }`, dark text, `color-scheme: light`) even in dark mode, so it showed bright white cards on the dark page. The forced-light rules now apply only in light mode (unchanged there), and dark mode has its own: dark cards, light-maroon section headers, light table text and muted text, and fixes for the participation-by-year table (styled inline with light-theme greys: dark `#333` text and near-white row borders) and the history modal's light card headers. The maroon hero banner and the white ID-badge card are unchanged. Checked in Chrome with a hand-built copy of the page's cards/tables and its real stylesheet, in dark and light (not the live page, which needs a login and data).
- **File:** `resources/views/examiner/profile_settings.blade.php`.

### Fixed (2026-10-17) — FCS Resubmit "choose Clinical or Viva" page (`examiner/fcs-resubmit/{candidate}`): dark-mode colours
- Same problems as the specialty selection page: in dark mode the global heading / paragraph colours (light grey) recoloured the Viva button's title and caption, which vanished on the gold button; the maroon title and border were low-contrast on the dark page. Added the matching dark-mode rules (light-maroon title and border, light heading, readable sub-text, button text that follows the button colour: dark maroon on gold, white on maroon, and the hover swaps). Also in light mode: hovering Clinical turned the button gold with white text; the text is now maroon on the gold hover. Checked in Chrome with the app's stylesheets and global rules: rest and hover (real pointer) in dark mode, and the Clinical hover in light mode.
- **File:** `resources/views/examiner/fcs_resubmit_selection.blade.php`.

### Fixed (2026-10-17) — Results: FCS Specialty Results box header was dark on dark in dark mode
- The FCS boxes (the real "FCS Specialty Results" table and the "(waiting to sync)" box) have a header with an inline gold background. In dark mode `body.dark-mode .card-header` repaints it dark grey while the "gold gets dark text" rules still forced the title to `#3a2a00`, so the title was dark on dark. Added a dark-mode rule in `custom.css` so those titles use the same light colour as the other card headers (General Surgery / MCS). Light mode is unchanged. Checked the computed colours in Chrome: both headers light text (#e2e8f0) on dark grey (#252c3b).
- **File:** `public/dist/css/custom.css`.

### Changed (2026-10-17) — No status label on the marking forms either
- The floating status label is also removed from the pages where a result is recorded: `examiner/examiner_form` (MCS), `examiner/general_surgery`, and the FCS `examiner/<specialty>/clinical|viva` forms (urology, cardiothoracic, ent, orthopaedic, neurosurgery, paediatric, paediatric_orthopaedics, plastic_surgery), in addition to the View / Resubmit pages. It still shows for 3 seconds on the dashboard, Results, the specialty selection pages and other examiner pages. After an offline save the "saved on this device" toast still appears. Tested in Chrome: no label on `examiner_form` with a mark waiting; path rule checked for all the pages above.
- **File:** `public/dist/js/examiner-offline.js`.

### Changed (2026-10-17) — No status label on the View / Resubmit pages of a single result
- The floating status label is no longer shown on `examiner/resubmit/{candidate}/{station}`, `examiner/view_results/{candidate}/{station}`, `examiner/view_fcs_results/{candidate}` and `examiner/fcs-resubmit/{candidate}[/clinical|viva]`. It still shows (for 3 seconds) on the dashboard, Results, the marking forms and other examiner pages; toasts (e.g. "N marks synced") and the "Saved on this device" card on Results are unchanged. Tested in Chrome: a page with a mark waiting shows no label on `resubmit/1/1`.
- **File:** `public/dist/js/examiner-offline.js`.

### Changed (2026-10-17) — Offline status label: 3 seconds, on every examiner page
- The floating status label ("N marks waiting to sync", "Offline · marks are saved on this device", "Syncing…") now fades after 3 seconds (was 5) and does so on every examiner page including the dashboard (it previously stayed on the dashboard and other pages). It is shown when a page opens and when the connection goes offline or comes back, then fades; "needs attention" / "sign in again" labels still stay until dealt with, and the label stays while its panel is open. The dashboard's "Ready for offline marking" card is not a label and is unchanged. Also fixed: a label that turned into an error while its fade timer ran could be hidden by the timer. Tested in Chrome on the dashboard (visible 0.8 s after an offline event, gone by 3.8 s).
- **File:** `public/dist/js/examiner-offline.js`.

### Fixed (2026-10-17) — Examiner menu: every marking page keeps "Dashboard" highlighted
- Some examiner pages had no active item in the sidebar (the FCS specialty pages and their Clinical / Viva forms, and any other page without its own menu entry). Dashboard is now active on every `examiner/…` page except those that have their own entry (Results and its View / Resubmit pages, Examiner Training, Profile), so the MCS, General Surgery and FCS marking pages are all covered. Checked the rule for dashboard, examiner_form, general_surgery, cardiothoracic, urology, badge (active) and results, view_results, resubmit, fcs-resubmit, learning, profile_settings, edit_info, messages (not).
- **File:** `resources/views/layout/header.blade.php`.

### Fixed (2026-10-17) — FCS "Select Examination Format": Viva button text invisible in dark mode
- The Viva button is gold with dark-maroon text, but its title and caption are `<h4>` / `<p>`, which the global dark-mode rules (`body.dark-mode h4 { color:#e0e0e0 }`, `body.dark-mode p { color:#d1d5db }`) recoloured light grey, so they vanished on the gold. In `exam_type_selection.blade.php` the button's `h4`, `p` and icon now follow the button's own colour in dark mode. Checked in Chrome with the app's stylesheets and the global rules, at rest and on hover with a real pointer: Clinical white on maroon, hovered dark-maroon on gold; Viva dark-maroon on gold, hovered gold on maroon.
- **File:** `resources/views/examiner/exam_type_selection.blade.php`.

### Fixed (2026-10-16) — FCS "Select Examination Format" pages (`examiner/<specialty>`) in dark mode
- The page's box was a fixed white card (`.exam-fieldset { background:#fff }`), so the light heading/sub-text of dark mode vanished on it, and the maroon title and the Clinical/Viva hover states (maroon/gold swaps) were low-contrast on the dark page. Added dark-mode rules in `exam_type_selection.blade.php`: dark card with a light-maroon border, light title and heading, readable sub-text, and Clinical/Viva buttons with dark-maroon text on gold (and gold on maroon when hovered). Light mode is unchanged.
- **File:** `resources/views/examiner/exam_type_selection.blade.php`. Not viewed in a browser (the browser tool stopped responding); only the card's computed dark background was confirmed.

### Fixed (2026-10-16) — Sandbox candidates could be marked into the real General Surgery table
- **What happened:** on `examiner/general_surgery` the candidate list (`get-gs-candidates/{group}`) ignored the group and returned every GS candidate of the year, while the group dropdown only listed legacy groups (id <= 11, so not `TEST-PART1`). A sandbox candidate (MW/2026/22, in TEST-PART1) was marked under real group A (id 1); the sandbox check looked only at the posted group, so the mark was written to the real `gs_results` (row 1041, 2026-10-16 13:13 UTC, found in the Apache log as the offline sync `POST /examiner/general_surgery`).
- **Fix:** (1) the sandbox decision now also looks at the candidate's own group: a candidate in a `TEST-` group is always saved to the `test_*` table, filed under that sandbox group, whatever group the form posted (MCS, GS and FCS submits); (2) the GS candidate list shows a `TEST-` group's own candidates, and a real group no longer lists sandbox candidates; (3) the GS dropdown lists `TEST-` groups; MCS and GS forms no longer list the `TEST-FCS-*` sample groups (they belong on the FCS forms). Tests added (sandbox candidate under a real group lands in the test tables; a real candidate still goes to the real table); the whole `ExaminerOfflineTest` file passes (30 tests). Checked the new GS list query on production data: real group A lists 15 GS candidates, TEST-PART1 lists 78.
- **Still to do:** the mark already written to real `gs_results` (id 1041) has to be moved to `test_gs_results`.
- **Files:** `app/Http/Controllers/CandidatesController.php`, `resources/views/examiner/general_surgery.blade.php`, `tests/Feature/ExaminerOfflineTest.php`.

### Added (2026-10-16) — Results page: "FCS Specialty Results (waiting to sync)" box for FCS marks saved offline
- The Results page only renders its FCS table once the server holds FCS results, so an FCS mark saved offline showed only in the "Saved on this device" card. While FCS marks (Clinical / Viva) wait to sync, `examiner-offline.js` now also builds an "FCS Specialty Results (waiting to sync)" box in the same layout (Candidate ID, Group, Clinical Total, Viva Total, Overall Total) with a "Waiting to sync" tag (or "Needs attention" for a rejected one); one row per candidate, totals summed over the saved stations as the server does. It disappears once the marks sync and the real FCS table takes over. MCS / General Surgery marks are not affected.
- **File:** `public/dist/js/examiner-offline.js`. Checked by running the real script in a simulated page (jsdom) with two Clinical + one Viva FCS marks and an MCS mark in the queue: one FCS row with Clinical 20.5, Viva 17, Overall 37.5, MCS not included. The look in a real browser was not checked (the browser tool stopped responding).

### Fixed (2026-10-16) — FCS marking forms: sample groups now appear, each under its own specialty
- The group dropdown on the universal FCS forms (`examiner/<specialty>/clinical|viva`) only listed groups with `id <= 10` (legacy A-J), so the new `TEST-FCS-<specialty>` groups (ids 18-25) and `TEST-PART1` (17) never showed; with nothing to select there were no candidates / PENs. The form now also lists `TEST-` sandbox groups. Because the group list is already limited to groups holding that programme's candidates, each specialty page shows only its own sample group (e.g. Urology: `TEST-FCS-URO`); `TEST-PART1`, which holds every specialty's candidates, is excluded from the FCS pages. Candidates are listed by PEN (`entry_number`) as before; the production data for `TEST-FCS-URO` returns all five PENs.
- **Files:** `app/Http/Controllers/CandidatesController.php` (`loadExamForm`), `resources/views/examiner/universal_exam_form.blade.php`.

### Fixed (2026-10-16) — "Saved on this device" card: View / Edit / Discard (and Resubmit) are all real buttons
- Edit and Resubmit were links styled as buttons, so the app's dark-mode link colours (pink text) could still repaint them, leaving Edit with pink text on gold next to View and Discard. They are now `<button>`s that navigate on click (offline check and in-page-editor fallback unchanged), so View / Edit / Discard share one set of colours: grey with light text, gold with black text, red with white text (checked in Chrome with the app's stylesheets in dark mode).
- **File:** `public/dist/js/examiner-offline.js`.

### Fixed (2026-10-16) — Examiner Training: lesson player, quiz and result pages readable in dark mode
- These pages used fixed light-theme colours with no dark-mode rules (white sidebar, `#0f172a`/`#1e293b`/`#334155` text), so titles, the module sidebar, quiz questions/options and result review were unreadable or bright white on the dark page. Added dark rules to the shared `learning/partials/styles.blade.php`: player sidebar (course title, progress, module list incl. active/hover/done states), module title/description/badges, Previous/Next cards, quiz title/question/option rows/footer, result score ring, review options (correct/wrong) and feedback. Anchor colours use `!important` because the app's dark-mode link rule (pink, gold on hover) otherwise wins. The certificate itself keeps its light parchment design. Checked in Chrome (dark) with the real CSS and a hand-built copy of each page's markup (not the live pages).
- **File:** `resources/views/learning/partials/styles.blade.php` (also loaded by the admin Learning pages; the new rules only target the examiner classes).

### Fixed (2026-10-16) — Examiner Training course page: module titles invisible in dark mode
- On `examiner/learning` the module titles used a fixed light-theme colour (`#1e293b`) on the dark card, so they were unreadable; the number tiles had no dark variant either. Added dark-mode rules in `learning/examiner/cover.blade.php` (light titles, maroon-tinted number tile, green completed tile, darker hover shadow). Rendered the real page template with the real CSS in Chrome (dark): titles, numbers, badges readable. The lesson player, quiz and result pages have the same kind of fixed dark text colours and no dark-mode rules; not changed here.
- **File:** `resources/views/learning/examiner/cover.blade.php`.

### Fixed (2026-10-16) — "Edit" in the "Saved on this device" card: contrasting colours in dark mode
- Edit is a link (`<a class="eo-btn">`), so the app's dark-mode link rules (pink text, gold on hover) outranked the button's own black text: pink on the gold button, and gold-on-gold on hover. The text is now fixed black in every state (with a darker gold on hover). Checked at rest and on hover in Chrome with the app's link rules.
- **File:** `public/dist/js/examiner-offline.js`.

### Changed (2026-10-16) — Offline status label: shown for 5 seconds when the page opens or the connection changes
- The floating label ("N marks waiting to sync", "Offline · marks are saved on this device", "Syncing…") now appears for 5 seconds when a marking/Resubmit form or the Results page is opened, and again for 5 seconds when the connection goes offline or comes back online, then fades out. Other status changes (a mark queued, a sync running, the periodic retry) do not bring it back. "Needs attention" / "Sign in again" labels stay until dealt with; other pages (dashboard etc.) are unchanged; if its panel is open the label stays. The "Saved on this device, not yet submitted" card on Results is a list with Edit/Discard and stays. Tested in Chrome against a stub server: shown on page open, faded after the delay, not back after the 30 s retry, shown again on an offline event.
- **File:** `public/dist/js/examiner-offline.js`.

### Fixed (2026-10-16) — View marks pages: button and link colours in dark mode
- The Back / Resubmit / All results controls are plain `<a>` tags, so the app's dark-mode link rules (`body.dark-mode a:not(.btn)` → pink, hover → gold) outranked the page's own classes: pink text on the gold Resubmit button and gold-on-gold on hover. Colours are now fixed per state in `examiner/_view_style.blade.php` (used by `view_results` and `view_fcs_results`): Resubmit dark text on gold (darker gold on hover); All results light text with a grey border, gold border on hover; Back link muted, maroon (light) / gold (dark) on hover. Checked at rest and on hover in Chrome with the app's link rules.

### Fixed (2026-10-16) — Dark mode: "+ Add Question" button invisible on the marking forms
- `layout/header.blade.php` forces `body.dark-mode .btn-outline-secondary` to a transparent background and grey border with `!important`, which overrode the button's inline gold background; with the dark text colour it was invisible (`examiner/examiner_form`, Resubmit forms). `custom.css` now keeps any `.btn` with an inline gold background gold (hover slightly darker) with dark text in dark mode. Reproduced the transparent button with the header rule, and confirmed gold computed styles after the fix, in Chrome.
- **File:** `public/dist/css/custom.css`.

### Fixed (2026-10-16) — Dark mode: Resubmit / marking forms (legacy wizard forms)
- `wizard.css` paints `#msform` white (on a `#f6f9fb` strip) with dark grey headings, so in dark mode the titles and labels (light text) were unreadable on the white form (e.g. `examiner/resubmit/{candidate}/{station}`). Added dark-mode rules for `.multi_step_form #msform` in `custom.css`: dark card background, light title/labels, muted sub-text, readable maroon case headings, warning notice, input-group text and disabled Submit. Applies to every page using this wizard form; light mode is untouched. Rendered the real Resubmit form with the real CSS in Chrome (dark).
- **File:** `public/dist/css/custom.css`.

### Changed (2026-10-16) — FCS "View marks" page uses the same design
- `examiner/view_fcs_results/{candidate}` now matches the redesigned View marks page: candidate and group header, large overall total with Clinical and Viva sub-totals, a Clinical block and a Viva block (station, case headings, one row per question, station total, remarks, block total), Resubmit / All results actions; light and dark mode. Question labels and case headings are unchanged. The look is shared through `examiner/_view_style.blade.php` (also used by `view_results`); the per-block markup is `examiner/_fcs_records.blade.php`.
- **Files:** `resources/views/examiner/{view_fcs_results,view_results,_view_style,_fcs_records}.blade.php`. Rendered in Chrome with sample data (dark mode checked).

### Changed (2026-10-16) — Examiner Results: dark-mode contrast; redesigned View marks page; avatar fallback path
- **Dark mode.** The "Saved on this device, not yet submitted" card and its table were fixed light colours (`#fffdf3`, `#fff`) with text inherited from the page, so in dark mode the text was near-invisible. They (and the sync panel and edit dialog) now have explicit dark-mode colours; light mode text colour is explicit too. **File:** `public/dist/js/examiner-offline.js`.
- **View marks** (`examiner/view_results/{candidate}/{station}`, MCS and General Surgery) redesigned: candidate name, group/station chips, a large total with a pass/borderline/fail chip, one row per question, remarks, and Resubmit / All results actions. Minimal, supports light and dark mode, works on phones, pure CSS (still cacheable offline). The FCS view (`view_fcs_results`) is unchanged. **File:** `resources/views/examiner/view_results.blade.php`.
- **Profile photo.** Examiner profile fallback avatar pointed at `/public/dist/img/user.png`; now `dist/img/user.png`. Production check: all 336 examiner passport images resolve (HTTP 200); 384 of 720 examiners have no photo on record, so they show the default avatar.

### Fixed (2026-10-16) — Offline: Resubmit works for a mark that is still only on the device
- Edit on "Saved on this device, not yet submitted" opens `examiner/resubmit/{candidate}/{station}?eo=<id>&type=mcs|gs`. The server has no such mark yet, so the service worker (`examiner-sw.js`) answers with a saved Resubmit page of the same kind (MCS or General Surgery; any candidate), and `examiner-offline.js` (`hydrateQueuedNew`) fills it with the waiting mark (group, candidate, station, marks, total, grade, remarks). Saving there changes the waiting mark itself (no second queue entry) and returns to Results; it is submitted once, when online. If no saved page of that kind exists offline, Edit falls back to the in-page editor. FCS marks still use the in-page editor.
- **Files:** `public/examiner-sw.js`, `public/dist/js/examiner-offline.js`. Tested in Chrome against a stub server using the real script and service worker: Edit offline opens the Resubmit page filled with the waiting mark (marks, total, grade, remarks); changing marks/grade/remarks and saving updates the single queued mark and returns to Results; also works when the server redirects (mark not on server).

### Changed (2026-10-16) — Offline: dashboard re-saves every page; Edit on a waiting mark opens its Resubmit page
- **Pages stay in step with the server.** Opening the examiner dashboard online now always re-downloads every offline page (results, each View / Resubmit page, candidate lists); previously it was skipped if the last save was under 20 minutes old, so a result saved afterwards had no Resubmit page offline ("Not saved for offline use") and cached totals could differ from the results table. Other pages re-save when the last save is over 2 minutes old, and pages are re-saved right after queued marks sync. Cache key bumped (`examiner-offline-warm-v6`).
- **Edit button.** On "Saved on this device, not yet submitted", Edit for a new mark now links to its Resubmit page (`resubmit/{candidate}/{station}`, or `fcs-resubmit/{candidate}/{format}`) instead of opening the in-page editor (kept as a fallback when ids are missing). Offline, that page only exists once the mark has synced.
- **Files:** `public/dist/js/examiner-offline.js`.

### Changed (2026-10-15) — Results page offline: queued edits show in the table; card links back to Resubmit
- **Edited marks now show their new values.** The results table lists what the server holds, so a mark edited offline (Resubmit → queued) kept showing the old marks and looked as if nothing had changed. `examiner-offline.js` now overlays each queued edit on its row until it syncs (matched through the row's Resubmit link): MCS/GS rows show the edited Question 1-5, Total and Grade, FCS rows show the edited Clinical or Viva total and the recomputed Overall, and the candidate cell is tagged "Edited offline". The overlay is applied through the DataTables API (search/sort follow) and removed again if the edit is discarded; after sync the page reloads with the server's values.
- **Card buttons.** In "Saved on this device, not yet submitted", an entry that is an *edit of a mark the server already has* now shows a plain **Resubmit** link (to that mark's Resubmit page, or its Clinical/Viva form for FCS, which already shows the edited values) and **Discard**. Marks that are not on the server yet keep View / Edit / Discard.
- **Files:** `public/dist/js/examiner-offline.js` (client only). Checked in a browser against the real rendered results tables (MCS, GS, FCS) with queued edits: values overlaid, other rows untouched, discard restores the original values.

### Fixed (2026-10-15) — Resubmit (Clinical / Viva choice) page: title and buttons overlapped on small screens
- `examiner/resubmit/{candidate}/{station}` for an FCS specialty candidate shows `fcs_resubmit_selection` (choose Clinical or Viva). Its box was pinned to the middle of the screen with `position:absolute; top:50%; left:50%; transform:translate(-50%,-50%)` inside a `height:100vh` section. On a phone the content (two 250px-high cards, headings, Back button) is taller than the screen, so it spilled above the header (its title/heading were cut off and ran into the first card) and below onto the footer. The box now flows normally (`min-height` instead of `100vh`, no absolute centring), the title (`legend`) is centred and sized to fit, and on screens under 576px the cards are shorter. Cards stay side by side from 768px.
- Checked at 280, 320, 360, 390, 414, 576, 768, 992 and 1280px: nothing overlaps or is cut off, and there is no sideways scroll. The MCS and General Surgery edit forms were checked at the same widths and need no change.
- **Files:** `resources/views/examiner/fcs_resubmit_selection.blade.php`.

### Added (2026-10-15) — "Connection needed" page for examiner pages that are not available offline
- Examiner pages that are not kept for offline use (Examiner Training `examiner/learning/*`, ID badge, attendance, ...) showed the browser's own "no internet / dino" screen when opened offline. The service worker (`public/examiner-sw.js`) now answers any `/examiner/…` page navigation that it does not cache: online it is passed straight through (redirects, sign-in expiry etc. behave as before); offline it returns a branded **"Connection needed"** page naming the page ("Examiner Training is only available online…"), with **Try again**, **Go to dashboard** and **View my results** (both work offline), the "You are offline" status, and an automatic reload when the connection returns.
- A page that *can* be kept offline but was never saved on this device now gets the same design ("Not saved for offline use": open the dashboard once while online).
- **Files:** `public/examiner-sw.js` only. Checked in a browser with a stubbed server (online pass-through and redirect, offline training page, never-saved page, Go to dashboard offline, reload on reconnect).

### Changed (2026-10-15) — Offline status badge sits above the footer
- The "N marks waiting to sync / Offline" badge was pinned to the very bottom-left of the screen, on top of the page footer. It now sits just above the footer (the script measures `footer.main-footer` and keeps the badge 12px above it): bottom-left on small screens, centred over the content area on large screens (≥992px, following the sidebar open/closed), with its panel centred above it too. Checked at 390px and 1280px, including opening/collapsing the sidebar.
- **Files:** `public/dist/js/examiner-offline.js`.

### Fixed (2026-10-15) — Offline: saved pages lost their scripts after a deploy; result pages not kept
- **Cause.** The offline copy of a page was saved without the scripts/styles it refers to. Our script URLs carry `?v=<file time>`, which changes on every deploy, so a page saved after a deploy asked for a script URL the device never downloaded: opened offline (e.g. dashboard → Results) it ran without `examiner-offline.js`, so no "saved on this device" card, no View/Edit/Discard buttons and no sync badge.
- **Fix (`public/examiner-sw.js`).** Whenever a page is saved, the service worker now also downloads and keeps its `<script src>` and `<link rel=stylesheet>` files; and offline, a script/style that was never saved falls back to an older saved copy of the same file (ignoring `?v=`).
- **Result pages (`examiner-offline.js`).** Each time the results page is opened online it saves every View / Resubmit page it links to (also the rows on other pages of the table, read from the DataTable; and the Clinical/Viva forms linked from an FCS resubmit page), so a result submitted after the device was last prepared can still be opened offline (e.g. `examiner/view_results/{candidate}/{station}`).
- The offline readiness key was bumped (`examiner-offline-warm-v5`) so devices re-download the pages once and pick up their assets.
- **Files:** `public/examiner-sw.js`, `public/dist/js/examiner-offline.js`. Checked in a browser with a stubbed server (deploy simulated by changing the script version, then going offline).

### Added (2026-10-15) — Results: view, edit and discard marks that are still on the device
- Marks saved offline and not yet submitted had no View/Edit controls: they are not on the server, so there is no page for them. The "Saved on this device, not yet submitted" card on `examiner/results` now has **View**, **Edit** and **Discard** per mark (works offline). Edit opens a dialog with the mark's questions (grouped by case for FCS), grade, remarks and a live total; it keeps the original field order, refuses blank/negative marks and, for MCS/General Surgery, a total over 20. A rejected mark can be edited and is queued again. Saving re-reads the mark first: if it was submitted in the meantime the examiner is told to use Resubmit instead. Discard asks for confirmation.
- Client only: `public/dist/js/examiner-offline.js` (no server change). Checked in a browser with a stubbed server (single-list and multi-case marks, validation, view, discard, and that sync submits the edited values).

### Fixed (2026-10-15) — Results: View / Resubmit now work for test (TEST-… group) marks
- The Results summary showed a "Test mark" label instead of the View and Resubmit buttons for marks saved to `test_*_results`, because the View / Resubmit pages and the update endpoints only looked in the real tables. They now look in each results table and its `test_` twin (`withSandbox()`), so test marks can be viewed and corrected exactly like real ones; the results rows show a small "Test" badge next to the candidate and always have the buttons. The offline manifest includes the sandbox marks' pages too.
- `viewFcsResults` was missing `orthopaedic_results` (an Orthopaedic mark's View page said "No results"); added.
- `updateEvaluation` (MCS/GS edit) no longer rewrites `group_id`: the group is read-only on that form and the form posts the group's *name* (e.g. "PART1"), not its id, which would have broken the mark's group join. The group of an existing mark is kept.
- **Files:** `CandidatesController.php`, `ExaminerOfflineController.php`, `resources/views/examiner/results.blade.php`, `public/dist/js/examiner-offline.js` (warm key v4), `tests/Feature/ExaminerOfflineTest.php` (28 tests).

### Added (2026-10-15) — Offline: edit/resubmit saved marks and view the profile
- **Resubmit offline.** The Resubmit and View pages for each result the examiner already has (MCS/GS `examiner/resubmit/{c}/{station}` + `view_results`, FCS `fcs-resubmit/{c}[/{format}]` + `view_fcs_results`) are listed in the offline manifest and cached. The three edit forms (`resubmit`, `gsresubmit`, `resubmit_form_universal`) now use the offline submit (`data-offline-kind="update"`): offline, the edit is queued and the examiner is taken to the results page, where a "saved on this device" card shows it as "Edit · <candidate>". Only the latest queued edit of a mark is kept. Reopening the edit page shows the queued values with a notice. Edits sync like new marks; an edit of a mark that no longer exists is rejected and kept for review (Discard).
- **Server.** `updateEvaluation` (MCS/GS) and `updateEvaluationFcs` now reply with JSON (`{status: success|invalid|error, message}`) for JSON requests and still redirect to `examiner/results` for the classic form; they also validate that marks and a total were sent. Edits are idempotent, so a retried sync is harmless. Sandbox (`TEST-…`) marks have no edit pages.
- **Profile.** `examiner/profile_settings` and `examiner/edit_info/{id}` are cached and can be read offline. Saving the profile (uploads, password) needs a connection: the form is not queued and offline it shows "You are offline. Reconnect to save changes to your profile."
- The offline readiness key was bumped (`examiner-offline-warm-v3`) so devices download the extra pages once.
- **Files:** `CandidatesController.php`, `ExaminerOfflineController.php`, `public/examiner-sw.js`, `public/dist/js/examiner-offline.js`, `resources/views/examiner/{resubmit,gsresubmit,resubmit_form_universal,fcs_resubmit_selection,view_results,view_gs_results,view_fcs_results,profile_settings,edit_info}.blade.php`, `tests/Feature/ExaminerOfflineTest.php` (24 tests).

### Fixed (2026-10-15) — Results tables no longer overlap on small screens
- **Examiner results** (`examiner/results`): the DataTables setup was an inline script in the page body, which runs before jQuery loads, so it never executed and the 10-column tables were plain, unwrapped tables that spilled over the card on phones and tablets. The script is now in `@push('scripts')` and the three tables use DataTables Responsive (`dt-responsive`): Candidate, Actions and Total stay visible, the question marks collapse behind a + control on narrow screens. The "saved on this device" card scrolls sideways inside itself instead of widening the page.
- **Test Results** (`admin/exams/test_results`): DataTables' search/length/paging controls sat inside a fixed 900px scrolling wrapper, so on a phone they were centred off-screen over the table. The wrapper and `min-width` are gone and the table is responsive (Candidate, Total, Delete first). The empty-state row is now DataTables' own `emptyTable` message (a `colspan` row breaks Responsive), "Delete All" is right-aligned, and the outdated "expires 2026-08-20" banner text is replaced.
- **Files:** `resources/views/examiner/results.blade.php`, `resources/views/admin/exams/test_results.blade.php`, `public/dist/js/examiner-offline.js`. Checked at 390 / 768 / 1280px.

### Changed (2026-10-15) — Examiner Results summary works offline and includes sandbox marks
- `examiner/results` is now cached for offline use (read-only copy of what was last seen online) and listed in the offline manifest. While marks are still on the device a card at the top lists them ("Saved on this device, not yet submitted"); after they sync the page reloads to show them.
- The summary also lists the examiner's sandbox marks (`test_<table>`, from `TEST-…` groups) next to the real ones; those rows show a "Test mark" badge instead of View/Resubmit, which only work on real results. FCS marks are grouped per candidate separately for real and sandbox.
- The offline readiness key was bumped (`examiner-offline-warm-v2`) so devices re-download the pages once after this release.
- **Files:** `CandidatesController.php` (`results`, `examinerResultRows`), `resources/views/examiner/results.blade.php`, `ExaminerOfflineController.php`, `public/examiner-sw.js`, `public/dist/js/examiner-offline.js`, `tests/Feature/ExaminerOfflineTest.php` (17 tests).

### Changed (2026-10-15) — Marks for TEST-… groups are saved to the test results tables
- A group whose name starts with `TEST-` (e.g. `TEST-PART1`) is a sandbox. The MCS, General Surgery and FCS marking endpoints (`storeEvaluation`, `storegsEvaluation`, `submitExamEvaluation`) save marks for such a group to `test_<specialty>_results` instead of the real results table, and check duplicates there, so nothing reaches real results. They appear under Exams > Test Results (Delete / Delete All there remove them). If the test table is missing the mark is refused (422) rather than saved for real. Other groups are unchanged.
- The examiner Results/Resubmit pages read the real tables only, so sandbox marks are not listed there; review and delete them under Test Results.
- **Files:** `app/Http/Controllers/CandidatesController.php` (`resultsTable`), `tests/Feature/ExaminerOfflineTest.php` (+4 tests). Companion: cosecsa-api `exam-results/test` (2026-10-15).

### Added (2026-10-15) — Offline marking on the examiner pages
- The examiner dashboard and marking forms (MCS, General Surgery, the eight FCS specialties) now work without a connection. A service worker (`public/examiner-sw.js`, scope `/examiner/`) keeps the marking pages and candidate lists, and `public/dist/js/examiner-offline.js` queues marks entered offline in IndexedDB and submits them, oldest first, when the connection and session are back. Nothing changes online except that submit now goes through `fetch` and the page reloads with a toast, as the redirect did.
- The dashboard shows an "offline readiness" card (Prepare now / Update now). While anything is queued a bottom-left badge shows the count and opens a list (Sync now; Discard for marks the server rejected). Pages and the queue are bound to the signed-in examiner: switching account empties the cached pages, queued marks only ever go out from their own examiner's session, and logging out warns if marks are unsent.
- Server: `CandidatesController` `storeEvaluation` / `storegsEvaluation` / `submitExamEvaluation` now validate the submission and, for JSON requests, return `{status: success|duplicate|invalid|error, message}` (201/409/422/403); the browser form post still redirects with a flash message. New `ExaminerOfflineController` (`examiner/offline/manifest` lists the pages and the (group, programme) candidate lists to download; `examiner/offline/token` gives a fresh CSRF token for syncing).
- Syncing is idempotent: a repeated mark is a `duplicate` (the existing check on candidate + examiner + station [+ format]) and is treated as already saved. Results, resubmit, profile and learning pages are online-only.
- **Files:** `public/examiner-sw.js`, `public/dist/js/examiner-offline.js`, `app/Http/Controllers/{CandidatesController,ExaminerOfflineController}.php`, `routes/web.php`, `resources/views/examiner/{_offline,dashboard,examiner_form,general_surgery,universal_exam_form,exam_type_selection}.blade.php`, `tests/Feature/ExaminerOfflineTest.php` (11 tests).
- Also: examiner pages show the PEN (`entry_number`) when a candidate has no candidate number yet.

### Changed (2026-10-15) — Styled alert/confirm dialogs; examiner default page; button hover contrast
- Native `alert()`/`confirm()` are replaced by a styled COSECSA dialog (`CosecsaAlert` in `custom.js`, `.cs-alert-*` / `.cs-toast` in `custom.css`). `window.alert` is globally overridden; inline `onsubmit/onclick="return confirm('…')"` markup is intercepted in the capture phase (works for DataTables-built rows too) and the script-block confirmations were converted. New markup can use `data-confirm` (+ `data-confirm-title/-variant/-ok/-cancel`). Documented in DESIGN.md.
- Examiners now land on the Examiner Training course (`examiner/learning`) after login, role switch and impersonation, instead of `examiner/dashboard` (which still exists; the course falls back to it when attendance isn't confirmed).
- Button hover: `<a class="btn …">` inherited the maroon link hover (`#7f0a12`), giving dark-on-dark labels on maroon/coloured buttons; the link-hover rule now excludes `.btn`/`.dropdown-item`/`.page-link`/`.nav-link`. `.btn-outline-dark` (Login-as-User) and `.btn-outline-secondary` are legible at rest in dark mode.
- **Files:** `public/dist/js/custom.js`, `public/dist/css/custom.css`, `app/Http/Controllers/{AuthController,ImpersonationController}.php`, `resources/views/layout/header.blade.php`, `resources/views/admin/_impersonate_button.blade.php`, and the converted call sites under `resources/views/{admin,progressive_reports,messaging,learning}`.
- ⚠️ **Coordinate:** cosecsa-api now defaults `internal/examiners` to the current calendar year when no `year_id` is supplied (deployed alongside this change).

### Fixed (2026-10-13) — Dark mode: unreadable text (contrast sweep)
- **Second sweep (17 pages):** Promote Trainees final-year rows (`table-warning`) are gold-tinted instead of pale yellow under light text, and their ★ badge has dark text; gold "Viva Stations" headers (FCS results) stay gold rather than turning grey under dark text; green buttons (e.g. Examiners "Email Selected") use a darker green so the white text reads.
- **Charts:** Chart.js text defaulted to `#666` (dark grey on dark): legends ("Total Alumni"), axes and titles now follow the theme (`Chart.defaults.color`/`borderColor` set in `layout/app.blade.php` and re-applied, with a redraw, when the theme is toggled). Charts that hardcoded `#555` value labels (Salesforce) or `#f0f0f0` grids (Fellows/Candidates analytics, College Reports) use `cosecsaChartText()` / `cosecsaChartGrid()`.
- **Dashboard tiles:** "View Details" links use tone classes (`tile-link-teal/green/amber/maroon`) with lighter dark-mode shades — the Trainees one was dark teal on a dark tile, Fellows dark maroon.
- **Sweep of all 46 sidebar pages in dark mode** (every visible text element vs. its effective background, flagged below 3:1), fixed in `custom.css`: inline light backgrounds on table cells / rows / card headers (e.g. `#ffe6e6` result columns on the FCS results pages, `#f8f0f0` card headers) retinted for dark mode; `thead-light` headers (Promote Trainees) darkened; any inline gold background gets dark text in both modes (gold "Upload Fellows/Candidates", "Add New Admin", "Save Profile" — some were white on gold even in light mode); inline maroon text (`color:#a02626` headings on Countries, Programmes, College Reports, Promote Trainees, the Salesforce "Visual Report" header, signature links) and `.fee-amount` → `#f48a8a`; `badge-warning` text dark; Trainers stat labels and disabled pagination lighter.
- **Files:** `resources/views/layout/{app,header}.blade.php`, `resources/views/admin/dashboard.blade.php`, `public/dist/css/custom.css`, `resources/views/admin/salesforce/index.blade.php`, `resources/views/admin/associates/{fellows,candidates}/reports.blade.php`, `resources/views/admin/reports/index.blade.php`.

### Fixed (2026-10-13) — Dark mode: no blue page numbers, buttons or badges
- **Pagination in dark mode** used AdminLTE's blue (`.dark-mode .page-item .page-link` → `#3f6791`), which outranked our rule. Page numbers are now light red `#f48a8a` (gold on hover), current page maroon, disabled grey.
- **Solid `btn-primary` was never maroon**: DESIGN.md said it was overridden, but no rule existed, so it was Bootstrap blue (light) / `#3f6791` (dark) — e.g. Roles & Permissions. Now maroon in both modes (darker on hover). `btn-info` (cyan, e.g. Fellows "Analytics", Examiners "Upload Confirmation") → COSECSA teal; `badge-primary` / `bg-primary` → maroon; `badge-info` / `bg-info` → teal; `text-primary` → maroon (light red in dark mode); `text-info` → teal. Each rule is repeated with `body.dark-mode` to outrank AdminLTE's dark versions. Examiners' local blue `.badge-primary` override removed.
- Email signature links (Profile Settings preview + real emails in both apps) are maroon instead of blue `#2a6ebb`.
- Checked by loading all 46 sidebar pages in dark mode and flagging any link/tab/button/badge/page number with blue text or background; chart series colours and decorative chips (not links) are unchanged.
- **Files:** `public/dist/css/custom.css`, `resources/views/layout/header.blade.php`, `resources/views/admin/exams/examiners.blade.php`, `resources/views/emails/_signature.blade.php`, `resources/views/profile/change_password.blade.php`.

### Changed (2026-10-13) — Links are COSECSA maroon, not Bootstrap blue; Salesforce: no Withdrawn tile
- Links without their own colour used Bootstrap's blue (`#007bff`) — e.g. the inactive "Summary by Programme & Year" tab on Overall Results. `custom.css` now gives links maroon `#a02626` at rest (bare-`a` specificity, so component/page link styles still apply; buttons, dropdown items and pagination unchanged) and `.btn-link` too. Dark mode links were light blue (`#93c5fd`); now DESIGN.md's `#f48a8a` (gold on hover), including inactive tabs inside pages.
- Salesforce: the **Withdrawn by applicant** tile is removed (like Closed — still counted in Total and available in the Stage filter).
- **Files:** `public/dist/css/custom.css`, `resources/views/layout/header.blade.php`, `resources/views/admin/salesforce/index.blade.php`.

### Changed (2026-10-13) — Salesforce page: centred tile text; Visual Report collapsed by default
- Tile numbers and labels are centred vertically and horizontally in each tile.
- The **Visual Report** card starts collapsed (click **+** to open); its charts are resized when it opens. **Print / Export Report** opens it first, then prints, so the printout still has the charts.
- **Files:** `resources/views/admin/salesforce/index.blade.php`.

### Changed (2026-10-13) — Salesforce tiles: Pending = Received + In the Approval Process; no Closed tile
- **Pending** now combines Application Received and In the Approval Process (clicking it lists both; `?tile=Application Received|In the Approval Process` — cosecsa-api `tile` accepts several stages). The **Closed** tile is removed (closed applications still count in Total). Remaining tiles: Total, Pending, Question, Payment Verification Pending, Approved, Invoiced, Complete, Rejected, Withdrawn by applicant.
- **Files:** `resources/views/admin/salesforce/index.blade.php`; cosecsa-api `SalesforceSyncController` (deployed).

### Changed (2026-10-13) — Salesforce page: one tile per stage
- `admin/salesforce` shows **Total** plus a tile for every Salesforce stage, in process order — Application Received, Question, In the Approval Process, Payment Verification Pending, Approved, Invoiced, Complete, Rejected, Withdrawn by applicant, Closed (any new stage is added automatically). Previously Received / Question / In the Approval Process / Payment Verification Pending were folded into "Pending", Rejected and Withdrawn shared a tile, and Closed had none. Clicking a tile filters the table to that stage (`?tile=`) and keeps every tile's count; click again to clear.
- Tiles sit in a responsive grid (`.sf-tiles`) so all 11 fit on one row on wide screens and wrap evenly on smaller ones.
- **Files:** `app/Http/Controllers/SalesforceSyncController.php`, `resources/views/admin/salesforce/index.blade.php`. ⚠️ **Coordinate:** cosecsa-api `?tile=` (deployed).

### Changed (2026-10-13) — Candidates Part I column; global search opens the exact exam result
- Candidates list: **Fee Paid**, **Part I** and **Part II** are centred in their columns, and Part I shows Pass/Fail only (the marks are on Overall Results and the Part I upload preview).
- Overall Results accepts `?q=<name>`: opens the **Detail** tab filtered to that person (search also covers the name on the result itself, which can differ from the trainee name); `#tab-detail` opens that tab. Exam-result hits in the global search use it, instead of landing on the year's summary.
- **Files:** `public/dist/js/custom.js`, `resources/views/admin/exams/overall_results.blade.php`. ⚠️ **Coordinate:** cosecsa-api global search link change (deployed).

### Added (2026-10-12) — Upload Part I Results; Part II promotion; Overall Results "Part I Pass"
- **Candidates → Upload Part I Results** (`admin/associates/candidates/part-one`): upload the examination officer's "Part I Results" spreadsheet → **preview** (nothing saved) → **Import**. The upload page lists the sheet's columns by role — **Match key** (External Ref = programme entry number, Class = programme + exam year), **Imported** (P I / P II points and %, Total, Overall %, Pass Mark, Remark, Student ID), **Compared only** (Repeat PI, MMED, Sponsor — differences flagged, record unchanged), **Not used**. The preview shows tiles (rows, matched, pass, fail, not matched, with warnings; click to filter), the same legend checked against the file, and every row with match status, the matched candidate, the imported columns highlighted, whether it will be promoted, and notes (class ≠ programme, Repeat PI/MMED/Sponsor differences, remark ≠ pass mark, updates an existing result). Confirm saves the results and, if ticked, marks passers as **promoted to Part II**. The file is kept between preview and confirm (`storage/app/part-one-uploads`, deleted after) so it isn't re-selected.
- Candidates list: **Part I** (Pass/Fail + overall %) and **Part II** (Promoted) columns, **Part I** / **Part II** filters, and **Promote to Part II** / **Remove Part II promotion** in each row's menu (needs `candidates.manage`). Promotion is a mark only — examiner grouping/marking is unchanged.
- **Overall Results:** candidates with only a Part I result for the year show Part II as "—" and **Part I Pass** (gold) instead of a final Pass; the summary has a **Part I Pass** column and the Result filter a matching option.
- Checked against the real sheet (`Part I Results to Share.xlsx`): 407 rows, all matched to 2026 candidates by entry number, 381 Pass / 26 Fail, no warnings.
- **Files:** `app/Http/Controllers/PartOneResultsController.php`, `app/Http/Controllers/CandidatesController.php` (`listRows`), `routes/web.php`, `resources/views/admin/associates/candidates/{part_one_upload,part_one_preview,_part_one_columns,list}.blade.php`, `resources/views/admin/exams/overall_results.blade.php`, `public/dist/js/custom.js`. ⚠️ **Coordinate:** cosecsa-api `fc7ef07` (migration + `candidates/part-one/*`) — already deployed.

### Changed (2026-10-12) — Dropdowns: consistent inset arrow; role-selection page aligned
- Every single-choice `select.form-control` (≈160 across the app) now draws its own chevron inset from the right edge (`custom.css`), instead of the browser's native arrow sitting flush against the border; `form-control-sm` gets a smaller one, dark mode a light one. Multi-selects/listboxes, Select2/Tom Select and Bootstrap `.custom-select` are unaffected.
- `select-role`: the role dropdown and the Reset Role / Continue buttons share one 46px height, the dropdown uses the same inset chevron, and the label is bolder.
- **Files:** `public/dist/css/custom.css`, `resources/views/auth/role-selection.blade.php`.

### Fixed (2026-10-12) — Login page: change the role you're logging in as
- After picking a role on the role-selection page, it stayed in the session (`pending_role`), so `/` kept showing "Login as <role>" with no way to pick another. The login page now has a **Change role** link (`change-role`, new `AuthController::changeRole()`) that clears it and returns to role selection, with the previous role pre-selected.
- **Files:** `app/Http/Controllers/AuthController.php`, `routes/web.php`, `resources/views/auth/{login,role-selection}.blade.php`.

### Fixed (2026-10-12) — Fellow/examiner side: photos, examiner Profile, College Report dashboard, slow Examiner Training
- **Photos:** the fellow dashboard built photo URLs as `https://cosecsamis.org/storage/…`, but examiner passports (and older fellow photos) live on the API's storage, so they 404'd (e.g. Charles SUNGANI). New `ApiAsset::media()` uses the same rule as cosecsa-api's `MediaUrl` (this app's public storage if the file is there, otherwise the API's). New `User::photoUrl()` (account photo → fellow photo → examiner passport, resolved that way) now feeds the fellow dashboard and the **sidebar avatar** for everyone (it used `users.profile_image` only, so fellows/examiners showed the placeholder). Examiner profile/attendance pages use `ApiAsset::media()` for passports too.
- **Examiner Profile didn't open for fellow-examiners:** `examiner/profile_settings`, the ID badge and self-edit looked the examiner up in `User::getExaminers()`, which only includes `users.user_type = 9` — a fellow who is also an examiner (user_type 7) got "Examiner profile not found" and was sent back to the dashboard. They now use new `User::findExaminer()` (one examiner, same group/shift/history fields, no user_type restriction). It also loaded all ~760 examiners with per-row queries (≈4.9 s) to find one.
- **Fellow dashboard:** opens on a live **College Report** tab (Fellows, Trainees in Training, Candidates this year, Accredited Hospitals tiles; Alumni by Year and gender charts — the admin dashboard's cached `dashboard/stats`), replacing the static "Key Facts". Tabs follow the URL hash instead of being remembered in localStorage — the sidebar's Profile Settings link used to store `#tab-account`, so Dashboard kept reopening on Account. Sidebar: Dashboard, **My Profile** (`#tab-personal`), Profile Settings (`#tab-account`). The fellow record is fetched directly instead of loading all ~2,000 fellows.
- **Examiner Training slow:** course images were camera originals (cover 6.4 MB / 6000×4000, a module photo 14.5 MB). Four LMS JPEGs in cosecsa-api `public/lms/media/` resized in place to ≤1600px (185–250 KB each, same URLs); originals backed up to `/root/lms-media-originals-20261012/` on the server. The 1.3 MB / 784px logo is replaced on screen-only views (sidebar, favicon, login, role selection, change password) by `dist/img/Cosecsa_Logo_sm.png` (256px, 105 KB); certificates and print views keep the full-size file.
- **Files:** `app/Support/ApiAsset.php`, `app/Models/User.php` (`findExaminer`, `photoUrl`), `app/Http/Controllers/{DashboardController,ExamsController}.php`, `resources/views/fellow/dashboard.blade.php`, `resources/views/layout/{header,app}.blade.php`, `resources/views/auth/{login,role-selection}.blade.php`, `resources/views/profile/change_password.blade.php`, `resources/views/examiner/{profile_settings,confirm_attendance}.blade.php`, `public/dist/img/Cosecsa_Logo_sm.png`. No API change.

### Performance (2026-10-12) — Subscription Report, Overall Results, Examiner Confirmation, Hospital Accreditation build rows client-side
- Same approach as the other lists: rows are embedded as compact JSON and DataTables builds only the visible page (`deferRender`). Tables converted: `#subscriptionReportTable` (2,079 rows, 3.4 MB), Overall Results `#detailTable` (1,918 rows, 2.9 MB), `#examinerconfirmationtable` (321 rows, 2.2 MB — ~7 KB per row), Hospital Accreditation `#followUpTable` (1.5 MB) and the Accreditations tab `#hospitalProgrammesTable` (1.2 MB). Filters, tile filters, sorting (dates/amounts by raw value), exports (plain text), the subscription drawer, PD modal, status toggle, Reset Confirmation and Delete Examiner work as before (delegated handlers where rows are built later).
- Hospital Accreditation's three API calls (dashboard rows, hospitals, accreditations) now run concurrently via new `ApiClient::getAll()` — the comment said "in parallel" but they ran one after another.
- **Fix:** each Follow-up row's **Send Reminder** was a `<form>` nested inside the bulk-reminder form; browsers drop nested forms, so it actually submitted the bulk form (reminding whichever rows were ticked, not that row). It is now a `formaction` button posting to `admin/hospital/reminders/{id}/send`.
- **System Logs:** tabs are compact (scoped `.logs-tabs`, .82rem) instead of the oversized default tabs, the filter row uses small inputs and solid COSECSA buttons (gold Filter, maroon Clear, gold View), and times use a 12-hour clock with AM/PM (was 24-hour `H` with AM/PM, e.g. "14:00 PM").
- **Files:** `app/Services/ApiClient.php`, `app/Http/Controllers/HospitalController.php`, `resources/views/admin/{fees/subscription_report,exams/overall_results,exams/examiner_confirmation,hospital/dashboard,hospitalprogrammes/_list_content,logs/index}.blade.php`, `public/dist/js/custom.js`. No API change.

### Changed (2026-10-12) — Transcripts: # column, delete, solid buttons
- **Transcript History** has a **#** column (follows the current sort/page) and, for staff with `transcripts.manage` (or Super Admin), a **Delete** button: a shared row deletes that share and its kept PDF; a "saved, not shared" row deletes the saved transcript. Both confirm first. The fellow's **Transcript** tab has the same per-share delete, a **Delete Transcript** button, and a # column on its share history.
- Buttons on the Transcripts page, transcript edit page and fellow Transcript tab are solid: maroon `btn-cosecsa` (main action), gold `btn-cosecsa-yellow` (view/preview/download), new slate `btn-cosecsa-dark` (Back, Add Row; lighter slate in dark mode), red `btn-cosecsa-danger` (delete; same size as the other COSECSA buttons).
- Dark mode: link-buttons inside tables no longer get the pink table-link colour (`custom.css` rule now `table a[href]:not(.btn)`), so their text stays white on maroon / dark on gold.
- All solid COSECSA buttons (`btn-cosecsa`, `-yellow`, `-dark`, `-danger`) app-wide now lift slightly with a shadow on hover, press back on click, and show a gold focus ring for keyboard users.
- **Files:** `app/Http/Controllers/TranscriptController.php` (`destroy`, `destroyShare`), `routes/web.php`, `resources/views/admin/transcripts/{search,edit,_profile}.blade.php`, `resources/views/layout/header.blade.php`. ⚠️ **Coordinate:** cosecsa-api delete endpoints first.

### Added (2026-10-12) — Transcript History; Transcript tab on the fellow profile
- `admin/transcripts` gains a **Transcript History** card: every share (date, fellow, programme, sent to, by, **Sent copy** PDF of exactly what was emailed) and transcripts saved but not shared (**Preview**), each with **Open**. The fellow name links to their profile's Transcript tab.
- The fellow profile (`admin/associates/fellows/view/{id}`) has a **Transcript** tab (needs `transcripts.view`), loaded when first opened: the latest shared transcript embedded in the page, its share history with each copy, and **Issue Transcript** / **Edit / Share Again**. Replaces the small "Transcript" row added to the Fellowship tab earlier today.
- Search-result and Manage Templates buttons use the COSECSA button styles.
- Transcript PDFs (Download, Sent copy, embedded view) keep their full file name (e.g. `Vincent Lewis MKOCHI- COSECSA Transcript.pdf`); it was cut at the first space.
- **Files:** `app/Http/Controllers/TranscriptController.php` (`search`, `profile`, `sentCopy`), `app/Http/Controllers/FellowsController.php`, `routes/web.php`, `resources/views/admin/transcripts/{search,_profile}.blade.php`, `resources/views/admin/associates/fellows/view.blade.php`. ⚠️ **Coordinate:** cosecsa-api `transcript_dispatches` migration + history endpoints first.

### Performance (2026-10-12) — Trainers and Fees lists build rows client-side
- `admin/associates/trainers/list` (4.7 MB) and `admin/fees` (8 MB, 3,027 rows; each row carried its full record in an `onclick` plus a delete form with its own CSRF token) now embed their rows as JSON and DataTables builds only the visible page. Trainers' filters, stat-tile filters, delete confirmation and server-side export are unchanged; Fees' edit button passes the row's data to `editPayment()` as before.
- Examiners export no longer includes the empty "#" column.
- **Files:** `resources/views/admin/associates/trainers/list.blade.php`, `resources/views/admin/fees/manage.blade.php`, `public/dist/js/custom.js`. No API change.

### Performance (2026-10-12) — Examiners list builds rows client-side; JSON-row lists start their request early
- `admin/exams/examiners` (was 3.5 MB of rendered rows, each with its own View form + CSRF token) now embeds the examiner rows as JSON (`#examinersRows`) and DataTables builds only the visible page. Selection/Email Selected, the participant toggle, all filters, Shift column and exports work on row data. Exports now skip the checkbox and Action columns.
- Fellows, Trainees and Candidates start their `list-rows` request at the top of the page (`admin/_dt_prefetch.blade.php`) instead of after all scripts have loaded; `dtAjax()` uses it, falling back to a normal request.
- **Files:** `resources/views/admin/exams/examiners.blade.php`, `resources/views/admin/_dt_prefetch.blade.php`, the three list views, `public/dist/js/custom.js`. No API change.

### Performance (2026-10-12) — Candidates list opens immediately; rows load after
- `admin/associates/candidates/list` (was 8 MB / 4.2 s) uses the same JSON-row approach as Fellows/Trainees: filters render first, rows from the new `admin/associates/candidates/list-rows`. The default "current exam year" filter applies once rows load.
- **Files:** `app/Http/Controllers/CandidatesController.php` (`list`, `listRows`), `routes/web.php`, `resources/views/admin/associates/candidates/list.blade.php`, `public/dist/js/custom.js`. ⚠️ **Coordinate:** cosecsa-api candidates `list-data?rows=0` first.

### Performance (2026-10-12) — Trainees list opens immediately; rows load after
- Same approach as the Fellows list below: `admin/associates/trainees/trainees` renders with filters only (API `list-data?rows=0`), rows come from the new `admin/associates/trainees/list-rows` and DataTables renders only the visible page. Was 9.7 MB of HTML and 5.4 s.
- Shared helpers for JSON-row tables (`dtAjax`, `dtText`, `dtRefLink`, `dtActionMenu`, `dtMenuItem`, `dtNumberRows`) are now in `custom.js`; Fellows uses them too.
- The hidden **SFS Password** export column is unchanged (still `users.password`). ⚠️ 275 trainee accounts store that password in plain text rather than a bcrypt hash; to be handled separately.
- **Files:** `app/Http/Controllers/TraineeController.php` (`list`, `listRows`), `routes/web.php`, `resources/views/admin/associates/trainees/trainees.blade.php`, `public/dist/js/custom.js`. ⚠️ **Coordinate:** cosecsa-api `?rows=0` on trainees `list-data` first.

### Performance (2026-10-12) — Fellows list opens immediately; rows load after
- `admin/associates/fellows/list` used to wait for the API and render all ~2,080 fellows into the page (9.4 MB of HTML, 2.4 s before the first byte, ~4 s to load). The page now renders with just the filters (API `list-data?rows=0`, ~36 ms). The table rows come as JSON from the new `admin/associates/fellows/list-rows`, and DataTables builds HTML only for the 25 rows on screen (`deferRender`).
- Search, sorting (multi-specialty fellows first), the checkbox filters, the "All Alumni" additional-specialty rows, Columns, and Excel/PDF/Print export (plain text via `orthogonal: "export"`) work as before. Action menus respect the same permissions (Issue Transcript needs `transcripts.view`; Delete is Super Admin only).
- **Files:** `app/Http/Controllers/FellowsController.php` (`list`, new `listRows`), `routes/web.php`, `resources/views/admin/associates/fellows/list.blade.php`, `public/dist/js/custom.js` (`#fellowstable`). ⚠️ **Coordinate:** needs cosecsa-api `541a08c` (lean `list-data`, `?rows=0`); already deployed.

### Performance (2026-10-12) — Permission checks remembered per request; JS compressed and assets cached
- `User::hasPermission()` now keeps the role's permission keys on the user object for the rest of the request. List pages call it once per row (≈2,000 times on the Fellows list), and each call was a separate file-cache read.
- **Server (no code):** new Apache conf `/etc/apache2/conf-available/cosecsa-perf.conf` (enabled; backup of `/etc/apache2` at `/root/apache2-conf-backup-20261012131503.tgz`). JS is now gzipped (Apache sends `.js` as `text/javascript`, which the stock `deflate.conf` skipped): `pdfmake.min.js` 1.35 MB → 537 KB, jQuery 89 → 31 KB. MIS/API public assets get `Cache-Control`: CSS/JS 1 day, images/fonts 30 days, `?v=`-stamped CSS/JS 1 year (`immutable`). Undo: `a2disconf cosecsa-perf && systemctl reload apache2`.
- HTTP/2 not enabled: Apache 2.4.52 disables it under `mpm_prefork`, which `mod_php` needs. It needs a move to `mpm_event` + PHP-FPM for every site.
- **Files:** `app/Models/User.php`. No API change.

### Changed (2026-10-12) — Transcript page buttons use the COSECSA button styles
- `admin/transcripts/edit/{userId}` used Bootstrap grey/dark/green/blue buttons. The header now matches College Letters: **Back**, **Preview** and **Download PDF** as `btn-cosecsa-outline`, plus a maroon **Share Transcript** (`btn-cosecsa`) that submits the form from the top. The bottom Share button, **Add Row** and the **Sent copy** link use the same classes. Row remove stays the red outline.
- The cyan Bootstrap `alert-info` banner at the top is gone; its guidance is now a muted subtitle under the page title (as on College Letters). **Add Row** sits right in the card header (`card-tools`) instead of floating mid-header.
- **Files:** `resources/views/admin/transcripts/edit.blade.php`. No API change.

### Fixed (2026-10-12) — Transcript Preview showed nothing; "Save Transcript" becomes "Share Transcript"
- **Preview was blank** on `admin/transcripts/edit/{userId}` (e.g. `/8522`): the modal's PDF loader listened for `show.bs.modal` with native `addEventListener`, but Bootstrap 4 only fires modal events through jQuery, so the iframe never loaded. Now bound with `$(...).on(...)`.
- Preview now renders the form **as it is on screen** (unsaved edits included), via a new `POST admin/transcripts/preview/{userId}` → cosecsa-api `transcripts/{userId}/preview`. Nothing is saved. Staff with only `transcripts.view` (who can't POST) still preview the last saved version.
- The form button is now **Share Transcript**: it saves the details, emails the PDF to the fellow and keeps the sent copy on their profile, in one step. The separate header "Send Transcript" button and the MIS `admin/transcripts/send` route are removed. If saving works but the email fails, the page says so and the details stay saved.
- The fellow profile (`admin/associates/fellows/view/{id}`, Fellowship tab) shows a **Transcript** row once one has been shared: date, recipient and a **View copy** link to the retained PDF (needs `transcripts.view`).
- **Files:** `app/Http/Controllers/TranscriptController.php`, `app/Http/Controllers/FellowsController.php`, `routes/web.php`, `resources/views/admin/transcripts/edit.blade.php`, `resources/views/admin/associates/fellows/view.blade.php`. ⚠️ **Coordinate:** deploy cosecsa-api first (new `transcripts/{userId}/preview` route).

### Fixed (2026-10-12) — Countries page: revolving globe restored
- The D3 auto-rotating globe on `admin/countries/list` had been replaced (in the working tree) by a static, flat interactive map. Restored the committed globe design: directory rows, quick-view slide panel, auto-rotation, drag-to-rotate, and Reset View. The unused flat-map files (`resources/views/admin/countries/_map.blade.php`, `public/dist/js/pages/countries-map.js`, `public/dist/css/countries-map.css`, `public/dist/data/world-map.json`, `config/continents.php`) are left on disk but no longer referenced.
- **Files:** `resources/views/admin/countries/list.blade.php`. No API change.

### Added (2026-10-12) — Transcript page: Preview + Send Transcript
- `admin/transcripts/edit/{userId}` gains a **Preview** button (in-page modal rendering the transcript PDF, lazy-loaded) and a **Send Transcript** button that emails the PDF to the fellow via cosecsa-api and keeps a copy on their profile.
- When a transcript has been sent, the page shows "Sent to `<email>` on `<date>`" with a **Sent copy** download link.
- **Files:** `app/Http/Controllers/TranscriptController.php` (`send`, `sentCopy`), `routes/web.php`, `resources/views/admin/transcripts/edit.blade.php`. ⚠️ **Coordinate:** needs the matching cosecsa-api deploy (migration + `transcripts/{userId}/send` and `/copy`).

### Added (2026-10-09) — Find A Surgeon section (menu below Messages)
- New sidebar menu **Find A Surgeon** (below Messages; needs the new `findasurgeon.view` permission, and `findasurgeon.manage` to act on the hospital review). Four pages under `admin/find-a-surgeon`:
  - **Overview & Report**: patient accounts (total, verified, new/active in 30 days, saves), directory health (listed Fellows, photo/bio/city/hospital/subspecialty completeness), and charts (new patients per week, Fellow edits per week, what Fellows edit, most-saved surgeons, saves by specialty, Fellows by country). Print / save as PDF button.
  - **Patients**: searchable list (verified / not, has saved surgeons, joined, last active) with a detail view of contact details and saved surgeons. Staff-only personal data.
  - **Hospital Review**: hospital names Fellows typed because theirs was not on the official list (and older entries that never matched), grouped by name and country, with the closest listed hospitals. Actions: **Link** to a listed hospital, **Add to list** (creates an unaccredited hospital, which also shows in other hospital pickers), **Dismiss** / restore.
  - **Profile Changes**: before/after log of what Fellows changed from the app (and what the Secretariat changed via the review), filterable by field, source, date and Fellow.
- Files: `app/Http/Controllers/FindASurgeonController.php`, `resources/views/admin/findasurgeon/*`, `routes/web.php`, `layout/header.blade.php` (menu), `config/admin_permissions.php` (module + route map).
- ⚠️ **Coordinate:** deploy cosecsa-api first (migration + `internal/findasurgeon/*` endpoints). Only Super Admin and Master Admin get the new permissions automatically; grant it to other roles under Roles & Permissions.

### Changed (2026-10-05) — Sponsored by / Programme Entry Fee are exam-only too
- On the fellow add/edit form, **Sponsored by**, **Prog. Entry Fee Year** and **Entry Mode of Payment** now hide for every Fellowship Type except **Fellow by Examination** (category 5), matching the other exam fields. They sit alongside the registration/subscription fee fields, which stay for all types.
- **Files:** `resources/views/admin/associates/fellows/{add,edit}.blade.php`. No API change.

### Changed (2026-10-05) — Fellowship registration & subscription fees are saved; Country Office payment mode
- The Fees & Finance section now has editable **Registration Fee (USD)**, **Annual Subscription Fee (USD)** and **Fee Year** fields. They are prefilled from the fee catalogue for the selected Fellowship Type (e.g. **Fellow By Election** → Registration `500.00`, Annual Subscription `100.00`); **Fee Year** defaults to the current year. Changing the Fellowship Type refills the amounts; on Edit a saved amount is kept until the type changes.
- Saved on the fellow record via new cosecsa-api columns `registration_fee_amount`, `annual_subscription_fee_amount` and `fee_year` (migration `2026_10_05_120000_add_fees_to_fellows_table`). ⚠️ **Coordinate:** deploy cosecsa-api first (runs the migration), then this app.
- **Country Office** added to the **Entry Mode of Payment** and **Exam Fee Mode** dropdowns (matching the subscriptions page).
- Replaces the read-only fee hint added earlier today with the editable fields.
- **Files:** `resources/views/admin/associates/fellows/{add,edit}.blade.php`, new `_fellow_category_js.blade.php` (removes `_fellowship_fee_hint.blade.php`).

### Changed (2026-10-05) — Fellow form: non-exam fellowship types hide exam fields; defaults; fees in section 4
- On `admin/associates/fellows/add` and `/edit/{id}`, the exam-related fields now only apply to **Fellow by Examination** (category 5). Selecting any other fellowship type (Member, Associate Fellow, Fellow By Election, Foundation / Overseas / Honorary, …) hides:
  - **Intake / Admission Year**, **MCS Qualification Year**, **Country of MCS Training**, **Supervised by**, **Upcoming Exam Year**, **Previous Exam Year** (section 2 / step 2);
  - **Exam Fee Year**, **Exam Fee Date Paid**, **Exam Fee Amount**, **Exam Fee Mode**, **Exam Fee Verified** (Fees & Finance / step 4).
  They reappear when "Fellow by Examination" is chosen. Hiding is presentational only — nothing is deleted.
- **Registered by** now defaults to `Secretariat` and **Secretariat Reg. Date** to today's date. Both are kept for every type; an existing saved value is never overwritten on edit.
- The applicable catalogue fee block (Fellowship Registration / Annual Subscription for the selected type) now sits at the top of **4. Fees & Finance** and updates with the Fellowship Type.
- **Files:** `resources/views/admin/associates/fellows/{add,edit}.blade.php`, `resources/views/admin/associates/fellows/_fellowship_fee_hint.blade.php`. No API change.

### Added (2026-10-05) — "Add Fellow from Associate" covers every associate type
- ⚠️ **Coordinate:** requires the matching cosecsa-api deploy (`FellowController::searchAssociates` / `storeFromAssociate` now accept the extra source types). The modal's new options 422/400 without it.
- The **Add Fellow from Associate** modal on `admin/associates/fellows/list` previously searched only Examiners, Country Reps and Members. It now also lists **Programme Directors**, **Trainees**, **Candidates** and **Trainers (ToT)** — everyone on the Associates menu.
- The ToT `trainers` table has no login, so a trainer is matched to a `users` row by **email**; if none exists the modal says so (create the login first) instead of failing silently.
- The new fellow inherits the source record's name, email, gender, country, phone, specialty/programme and — for programme directors — their hospital/organisation.
- **Files:** `resources/views/admin/associates/fellows/list.blade.php`. Needs the matching cosecsa-api deploy.

### Added (2026-10-05) — Applicable fee shown on the fellow Add/Edit pages
- The **Fellowship Type** select on `admin/associates/fellows/add` and `/edit/{id}` now shows a read-only hint of the matching fee(s) from the fee catalogue: the **Fellowship Registration** fee and the **Annual Subscription** fee for that fellowship type (e.g. *Fellow By Election → Registration — Fellowship By Election/Reg: USD 500.00 · Annual Subscription — Fellows: USD 100.00*). Honorary fellows show none (normally exempt). It updates as the type changes.
- Matching is done in `FellowsController::fellowshipFeesByCategory()` by comparing the category name against the catalogue's fee names (the same word-scoring used to prefill subscription amounts). Nothing is saved — it's guidance for staff.
- **Files:** `app/Http/Controllers/FellowsController.php`, new `resources/views/admin/associates/fellows/_fellowship_fee_hint.blade.php`, `add.blade.php`, `edit.blade.php`. No API change (reads `fees/catalogue`).

### Added (2026-10-05) — Pick a hospital from the system (and add one) on the fellow form
- **Current Hospital / Organisation** on `admin/associates/fellows/add` was a plain text box; it is now the same searchable list of every active hospital as the Edit page, with a **+ New** button beside it.
- **+ New** opens an Add Hospital modal (name + country) that creates a real hospital through cosecsa-api (`admin/hospitals`), then adds it to the dropdown and selects it. The hospital lookup cache is cleared so it appears everywhere.
- The Edit page gets the same **+ New** button next to its existing hospital dropdown.
- The hospital quick-create lives under `admin/associates/fellows/...`, so `fellows.manage` is enough (the internal API call isn't re-checked against the lookups module).
- **Files:** `app/Http/Controllers/FellowsController.php` (`quickAddHospital()`), `routes/web.php`, new `resources/views/admin/associates/fellows/_add_hospital_modal.blade.php`, `add.blade.php`, `edit.blade.php`. No cosecsa-api change.

### Added (2026-10-02) — Examiner Training course inside the MIS
- ⚠️ **Coordinate:** deploy together with cosecsa-api `feat/learning` (migration + `internal/learning/*` endpoints + `lms:import`). These pages read everything from the API.
- **Examiners:** sidebar → **Examiner Training** (`examiner/learning`): course page, module player (module sidebar, rich content blocks, image preview, checklists, flashcards, process steps, labelled graphics, videos), the 20-question quiz with retakes, and results. **Next module** marks the module complete. Uses the normal MIS login and `examiner` middleware.
- **Staff:** Examinations → **Examiner Training** (`admin/exams/learning`), behind `admin` + `permission` (the existing `admin/exams` → `examiners` mapping: view to see, manage to edit): learner progress for all examiners (with per-person detail), course content with a block editor (formatting toolbar, live preview, image replace/size/alignment/corners/border/shadow/opacity), course logo, and video uploads.
- Correct quiz answers are only shown after a pass. Course styles are scoped under `.lms` so they don't affect Bootstrap/AdminLTE.
- `ApiClient::postStream()` streams large uploads (videos) instead of reading them into memory.
- **Files:** `app/Http/Controllers/Learning/{ExaminerLearningController,AdminLearningController}.php`, `app/Support/LearningView.php`, `app/Services/ApiClient.php`, `resources/views/learning/**`, `routes/web.php`, `resources/views/layout/header.blade.php`.
- **Note:** video uploads through the page are limited by PHP's `upload_max_filesize`/`post_max_size` (150M on the server); larger files go through `php artisan lms:import-videos` on the API.

### Changed (2026-10-01) — Role switch shows a placeholder and animates in
- Clicking an **Also in** chip now immediately covers the current profile with a skeleton of a profile page (header bar, photo card, stat tiles, tabs, content card) with a shimmer and a caption such as "Opening Examiner profile…". After 300 ms (0 with reduced motion) it navigates, so the placeholder registers even when the next profile is already prerendered; if it isn't, the skeleton stays up while it loads.
- The arriving profile fades in and slides up 10 px (`::view-transition-new(root)`); the old one fades out.
- Ctrl/Cmd/Shift/middle-click still open the profile in a new tab with no placeholder, and returning via Back clears it. `prefers-reduced-motion` turns off the shimmer and slide. Dark mode uses the DESIGN.md dark palette.
- Checked with headless-Chrome screenshots of the placeholder in light and dark mode.
- **Files:** `resources/views/admin/_role_switcher.blade.php`.

### Fixed (2026-10-01) — Role switch still loaded: prerender now starts on page load
- After the instant role switch above was deployed, switching from Fellow to Examiner on `fellows/view/5205` still showed a normal load. Cause: the rules used `eagerness: "eager"`, which on desktop Chrome (tested on 154) no longer starts on page load; it waits for the pointer to approach the link. A quick click found no prerendered page and loaded it fresh.
- Reproduced in headless Chrome with the two rendered profile pages: with `eager`, opening the fellow page fetched nothing else; with `immediate`, the examiner page was fetched straight away. Changed to `eagerness: "immediate"`.
- Still Chrome/Edge only. Chrome skips prerendering when **Settings → Performance → Preload pages** is off, under Energy or Memory Saver, and in a tab with a debugger attached.
- **Files:** `resources/views/admin/_role_switcher.blade.php`.

### Changed (2026-10-01) — Switching between a person's role profiles is instant
- The **Also in** chips (Fellow / Examiner / Programme Director / Country Rep / Member) on profile pages now prerender the person's other role profiles in the background (Chrome Speculation Rules, `eagerness: eager`). Clicking a chip shows that profile immediately, already fully loaded with its own scripts, instead of a fresh page load and preloader. A cross-document view transition (`@view-transition { navigation: auto; }`) fades between them.
- Chosen over swapping the page content in place: each profile page has a lot of page-specific script and CSS that only runs on load (the examiner page has 1,500+ lines of script), and an in-place swap would leave inline edits, modals and tabs unwired.
- All profile `view()` methods are read-only GETs, so prerendering has no side effects. The site CSP already allows the inline rules. Chrome and Edge only; Safari and Firefox navigate normally as before.
- **Files:** `resources/views/admin/_role_switcher.blade.php`.

### Data correction (2026-10-01) — Dr Ayman Elhosny: duplicate examiner login merged into his fellow account
- Fellow ID 2026 (user 13735) and examiner EXA720 (user 17720, created 2026-07-09) were separate accounts with the same email, so the fellow and examiner logins had separate passwords. Neither matched the `1234567` he was given, so examiner login failed both ways.
- Merged into user 13735: examiner 985 now belongs to it, it has the Examiner role, and 17720 is retired (role removed, `is_deleted = 1`, email renamed to `…merged-13735`). Fellow ID `2026` or the email with `1234567` now works for both Fellow and Examiner (password set at admin request; below the 8-character Add Role minimum).
- Unified details: name **Ayman A A ELHOSNY** (from the fellow record); fellow specialty "Paediatric Surgery" (double space fixed); examiner keeps "FCS Paediatric Surgery"; passport photo copied to the fellow profile photo; fellowship no. 2026 on his examiner participation. Mobile, country, gender and email already matched.
- Backup first: `/var/backups/cosecsa_2026-10-01_113902_pre_elhosny_merge.sql`; logged in `activity_logs`.
- **Note:** a fellow who already has a separate examiner account should be merged like this, not given **Add Role → Examiner**, which would create a second examiner profile.

### Fixed (2026-10-01) — Overall Exam Results page returned a 500
- `admin/exams/overall_results` failed with `Cannot use object of type stdClass as array`. The API's `summary` (year → programme → Pass/Fail/Absent counts) was decoded with `->object()` and only the top level cast to an array, so the view's `$counts['Pass']` hit a `stdClass`. The log shows the same error since at least 2026-07-09, so it is long-standing, not caused by today's data changes.
- `ExamsController::overallResults()` now passes `$response->json('summary')` (nested arrays). Rendered against the live API data (22 years) without error.
- **Files:** `app/Http/Controllers/ExamsController.php`.

### Data correction (2026-10-01) — Emmanuel Malekwa's MCS 2023 result added by hand
- Added `capsule_exam_results` 4180 (capsule_id 900001632): Emmanuel Malekwa, MCS 2023, score 70.85, **Pass**, linked to trainee 351 (`MW/2022/26`); there is no 2023 MCS candidate row. Stored like the existing MCS rows (specialty and exam type `MCS`, programme 10). Supplied by the admin; it was never imported. Backup first: `/var/backups/cosecsa_2026-10-01_110651_pre_malekwa_mcs2023.sql`; logged in `activity_logs`.
- The trainee detail API now returns only this result for him.
- **Wider gap, not filled:** Capsule MCS results stop at 2019 and exam-system `mcs_results` only cover 2024–2025, so no one's 2020–2023 MCS results are recorded. They need the examination officer's spreadsheet.

### Data correction (2026-10-01) — Tyler Pender's entry number; lost 2025/2024 GS marks restored
- **Fixed:** fellow 5425 (Tyler Pender, user 13955) had candidate number `MW/2022/26`, which is Emmanuel Malekwa's (trainee 351, candidate 1621). It is now **`MW/2025/26`**. Backup taken first: `/var/backups/cosecsa_2026-10-01_103324_pre_tyler_pen_fix.sql`; the change is in `activity_logs`.
- **Why Tyler was missing from `admin/exams/gs_results?year_id=6`:** his 2025 candidate row (GS-047) had been deleted, and `gs_results` used `ON DELETE CASCADE`, so his marks went with it. The same happened to 9 other 2025 GS candidates and 2 from 2024. Deleted after 2025-11-29 and before 2026-07-17; nothing logged who.
- **Restored** from the 2026-03-31 production export (`Downloads/ECSA/COSECSA/Db/cosecsa.sql`): 12 candidate rows and 168 GS marks with their original ids. Examiner 219 had been merged into 44, so his rows point at 44. 2025 GS now has 865 rows / 54 candidates, matching the export. Backup first: `/var/backups/cosecsa_2026-10-01_105635_pre_gs_restore.sql`.
- Tyler's restored candidate carries `MW/2025/26`. His Capsule results 2955/3598 now link to his candidate 785 instead of Malekwa's trainee 351 / candidate 1621; Malekwa has no results of his own. Tyler's stray Trainee role (`user_roles` 11702) is deactivated.
- **Prevention:** cosecsa-api migration `2026_10_01_120000_restrict_deletes_on_exam_results` (deployed): deleting a candidate or examiner with marks is now refused instead of deleting the marks.

### Fixed (2026-10-01) — Gold and cyan outline buttons didn't react to hover in dark mode
- The global dark-mode button styles (`layout/header.blade.php`) force `btn-outline-warning` and `btn-outline-info` to a transparent background with `!important`, but unlike the primary, danger and secondary outlines they had no `:hover` rule. In dark mode they didn't change on hover at all. Reported on the Fellow Profile's Subscriptions tab **Manage** button; the cyan Subscriptions icon in the profile's action bar was affected too, as was every other button using these two classes.
- Added dark-mode hovers in the same pattern as the others: warning fills gold (`#f59e0b`) with dark text, info fills cyan (`#0e7490`) with white text. Light mode is unchanged.
- **Files:** `resources/views/layout/header.blade.php` (CSS only).

### Fixed (2026-10-01) — Fellow Profile tables unreadable in dark mode
- On the Fellow Profile (`admin/associates/fellows/view/{id}`), the **Subscriptions** tab's table header (Year, Status, Amount Due, …) kept its pale pink background in dark mode, with light text on it, so the labels were almost invisible. The two **Results** tab tables (Exam History, FCS Exam Results) had the same inline pale header.
- Dark mode now gives all three headers the DESIGN.md dark card-header colour (`#252c3b`) with dark-mode maroon (`#f48a8a`) labels. The fellow's name in the top action bar (inline maroon on the slate bar) uses `#f48a8a` too.
- The subscriptions page's header colour is now `!important` as well, so it shows the intended `#f48a8a` instead of being overridden to white.
- Light mode unchanged. Checked with local headless-Chrome screenshots (Subscriptions tab and action bar; the local data had no exam results, so the Results tables weren't rendered).
- **Files:** `resources/views/admin/associates/fellows/view.blade.php`, `resources/views/admin/associates/fellows/subscriptions.blade.php` (CSS only).

### Fixed (2026-10-01) — Fellow subscriptions page unreadable in dark mode
- On `admin/associates/fellows/subscriptions/{id}` the table header (Year, Status, Amount Due, Amount Paid, Date Paid, Mode) kept its light pink background (`#fff5f5`) in dark mode and was hard to read. The card header (`#fafafa`), the totals row (`#f9f9f9`) and the four summary chips (`#fff`) had the same problem.
- Added dark-mode rules using the DESIGN.md dark palette: header and totals rows `#252c3b`, chips `#1e2330`, light text, and the dark-mode maroon `#f48a8a` / green for the outstanding and fully-paid figures. Light mode is unchanged.
- Checked with a local headless-Chrome screenshot of the page in dark mode.
- **Files:** `resources/views/admin/associates/fellows/subscriptions.blade.php` (CSS only).

### Changed (2026-10-01) — Fellow subscriptions: Amount Due is a fee-catalogue dropdown with "Other"
- On the **Add Subscription Year** and **Edit Subscription** modals, Amount Due is now a dropdown of the fee catalogue's active **Annual Subscription** fees (e.g. "Overseas Fellow — USD 120.00"), highest first, followed by **Other amount…**, which shows a box for typing any amount.
- **Add:** the fee matching the fellow's fellowship type is preselected and labelled with the type, e.g. "Fellows — USD 100.00 (Fellow by Examination)". Honorary fellows and fellows with no type start on Other.
- **Edit:** selects the fee whose amount matches the saved Amount Due. A saved amount not in the catalogue opens on Other with that amount, and a record with no amount falls back to the fellowship-type fee. This replaces the earlier "Catalogue: … · Use" link.
- The submitted `amount_due` is unchanged (a number), so the API is unchanged. If the catalogue can't be loaded, only Other is offered.
- **Files:** `app/Http/Controllers/FellowsController.php` (`subscriptionFees()`), `resources/views/admin/associates/fellows/_sub_due_field.blade.php` (new), `resources/views/admin/associates/fellows/subscriptions.blade.php`.

### Changed (2026-10-01) — Fellow subscriptions: Amount Paid follows the status, Partial shows the pending balance
- On the **Add Subscription Year** and **Edit Subscription** modals, the Amount Paid field now depends on Status:
  - **Unpaid / Waived:** hidden, saved as 0.
  - **Paid:** shown and filled with the Amount Due when Paid is chosen; editable, since some fellows overpay (e.g. 120 against 100).
  - **Partial:** shown and required, must be more than 0 and less than the Amount Due. A live **Pending balance: USD x** is calculated from Amount Due − Amount Paid. If the amount covers the full due, it says to use Paid instead.
- Editing an existing record keeps its saved Amount Paid; the field only resets when the status is changed. Changing Amount Due (or using the catalogue fee) recalculates the pending balance.
- Browser-side only; the API is unchanged. **Files:** `resources/views/admin/associates/fellows/subscriptions.blade.php`.

### Added (2026-10-01) — "Country Office" payment mode on fellow subscriptions
- **Mode of Payment** on the Add Subscription Year and Edit Subscription modals now includes **Country Office**, between Online and Waived. `fellow_subscriptions.mode_of_payment` is `varchar(50)` and the API doesn't restrict its values, so no API change is needed.
- The programme-entry and exam-fee payment modes on the fellow add/edit forms are unchanged.
- **Files:** `resources/views/admin/associates/fellows/subscriptions.blade.php`.

### Fixed (2026-10-01) — Finance couldn't save fellow subscriptions; Amount Due prefilled from the fee catalogue
- **Edit Subscription didn't save and went to the dashboard** (seen on `admin/associates/fellows/subscriptions/6146`, record 3804). `PermissionMiddleware` maps every `admin/associates/fellows/...` URL to the **Fellows** module. The Finance Officer role has Fees manage but not Fellows manage, so every save was redirected to the dashboard before reaching the API. The dashboard doesn't show flash messages, so there was no visible error.
  - `config/admin_permissions.php`: `admin/associates/fellows/subscriptions` now maps to `['fellows', 'fees']`. Either module's permission is enough, so the Finance Officer (fees) and the Administrative Officer (fellows) can both add, edit and delete subscription years. Editing the fellow profile itself still needs Fellows.
  - `PermissionMiddleware`: a `route_map` entry can now list several modules; holding the permission on any one of them passes.
- **Amount Due from the fee catalogue:** the fellow's fellowship type is matched to the best **Annual Subscription** fee in `fee_types`:
  - Fellow by Examination / Election / Foundation Fellow → Fellows (100)
  - Associate Fellow → Associate Fellows (70)
  - Associate Member → Associate Members (30)
  - Overseas Fellow → Overseas Fellow (120)
  - Honorary fellows and fellows with no type get no suggestion.
  - **Add Subscription Year** prefills it (it was a hard-coded 150.00, which isn't a catalogue fee). **Edit** keeps the saved amount, falls back to the catalogue fee only when none was recorded, and shows a "Catalogue: … · Use" link. Both stay editable.
- **Files:** `config/admin_permissions.php`, `app/Http/Middleware/PermissionMiddleware.php`, `app/Http/Controllers/FellowsController.php`, `resources/views/admin/associates/fellows/subscriptions.blade.php`. No cosecsa-api change.

### Changed (2026-09-30) — Messages list: unread conversations stand out
- On `messages` every conversation looked the same (all names bold), so read and unread couldn't be told apart. Now, same treatment as My Tasks:
  - **Unread:** maroon left edge, light maroon tint, bold name, dark bold preview, maroon time, and a maroon pill with the number of unread messages.
  - **Read:** normal-weight name, grey preview and time, no pill.
  - Dark mode covered.
- Unread = messages from others newer than your `last_read_at`, the same rule as the navbar bell. The 10 s poll now returns an `unread` count per conversation, so rows switch state live.
- **Files:** `app/Http/Controllers/MessagingController.php`, `resources/views/messaging/index.blade.php`.

### Fixed (2026-09-30) — CEO had no section to fill in on the September report
- The September 2026 report was consolidated without a **CEO** section, so the CEO had nothing to fill in. This app's `config/progress_report_sections.php` leaves her out when a month is opened. August had a CEO section only because it was opened from cosecsa-api, whose copy of the list includes her.
- New `ProgressReportParticipant::ensureCeoSection($period)` adds her section (label `CEO`, first in the report, other sections shifted down one). It uses her task templates if she has any. It is idempotent: it does nothing if she already has a section.
- When it runs:
  - **Consolidate**: she gets her section as soon as the report is consolidated.
  - **Share with CEO**: runs before the PDF/DOCX are built, so what she receives includes her (blank) CEO rows.
  - **When the CEO opens My Progress Report or Secretariat Report**, if the current period is consolidated. This covers September without a data script: her section appears the first time she opens it. Past months are not touched.
- Her section is still exempt from the deadline lock, reminders and the pending badge, as before, so she can fill it in after the due date and after consolidation.
- **Files:** `app/Models/ProgressReportParticipant.php`, `app/Http/Controllers/ProgressiveReportController.php`. No migration and no cosecsa-api change needed.

### Fixed (2026-09-24) — Fellows given the Examiner role; Add Role password + specialty
- Fellows who get the Examiner role via **Add Role** now appear in the public availability form dropdown, the Examiners list and bulk email. Those lists used to require the user's primary type to be Examiner. The fix is in cosecsa-api.
- **Add Role → Examiner** modal:
  - **Specialty** is now a dropdown of programmes, preselected with the fellow's current specialty.
  - New optional **Set Password / Confirm Password** fields. The login is shared, so this also changes the fellow password.
  - **Country** and **Mobile Number** are now filled in from the fellow's record in every role section (Programme Director, Country Rep, Examiner). If Mobile is cleared, the API falls back to the fellow's phone.
  - Hidden role sections are now disabled so their same-named fields (country, mobile) no longer overwrite the visible ones.
- `admin/exams/view_examiner/{id}`: inline **Specialty** edit is a programme dropdown showing the current value by name.
- **Files:** `app/Http/Controllers/FellowsController.php`, `app/Http/Controllers/ExamsController.php`, `resources/views/admin/associates/fellows/view.blade.php`, `resources/views/admin/exams/view_examiner.blade.php`. Needs the matching cosecsa-api deploy.

### Added (2026-09-24) — Salesforce page: Invoiced tile + clickable tiles filter the list
- New **Invoiced** tile (dark gold). Pending no longer includes Invoiced.
- All six tiles (Total, Pending, Approved, Invoiced, Complete, Rejected/Withdrawn) are links. Clicking one filters the table to that category (`?group=`) and outlines the tile in gold; clicking it again, or clicking Total, clears the filter. Tile counts stay fixed while a tile is selected. The filter bar keeps the selected tile when year, country etc. change, while choosing a specific Stage in the dropdown replaces it.
- Tiles are six across on wide screens (`col-xl-2`), three on tablets and two on phones.
- **Files:** `app/Http/Controllers/SalesforceSyncController.php`, `resources/views/admin/salesforce/index.blade.php`. Needs the matching cosecsa-api deploy (`group` + `invoicedCount`).

### Added (2026-09-24) — "Approved" count on the Salesforce applications page
- New **Approved** tile (teal, between Active and Complete) on `admin/salesforce`, counting applications whose Salesforce **Stage = "Approved"** under the current filters. The five tiles now share the row equally (`col-md`).
- The **Active (not rejected/withdrawn)** tile became **Pending** (blue): applications still being processed, i.e. every stage except Approved, Complete, Rejected, Withdrawn and Closed. The tiles now add up without overlap (Total = Pending + Approved + Complete + Rejected/Withdrawn + Closed); Closed has no tile.
- **Files:** `app/Http/Controllers/SalesforceSyncController.php`, `resources/views/admin/salesforce/index.blade.php`. Needs the cosecsa-api `approvedStageCount` / `pendingCount` change (falls back to 0 / the old Active count without it).

### Fixed (2026-09-24) — Salesforce application stages out of sync with Salesforce
- Applications approved in Salesforce weren't showing as approved (e.g. `admin/salesforce/view/1980`). The root cause was in cosecsa-api's sync: nothing was scheduled, there was a watermark gap, and failures were silent. See that repo's CHANGES.md (2026-09-24). Applications now auto-sync every 15 min, plus a full sync nightly.
- A one-off full sync on 2026-09-24 refreshed all 2,050 applications. App 1980 is now **Stage = Approved**, matching Salesforce.
- Salesforce's `Application_Approved__c` checkbox **does not track the stage**: 860 Complete and 14 Approved applications have it unticked, while some Rejected/Withdrawn ones have it ticked. The **Stage** is the source of truth, so the view page shows the Stage only. (A checkbox row added briefly earlier the same day was removed, because it would have said "No" for approved applications.)
- This repo: the Stage pill (view + list) now maps every stage Salesforce actually uses to a colour:
  - Complete / Approved: green
  - Application Received / Question: yellow
  - In the Approval Process / Invoiced / Payment Verification Pending: blue
  - Rejected / Withdrawn: red
  - Closed: grey
  - anything new falls back to the old keyword rules
- PEN is shown only for Complete, as before. The "Last synced" row notes the 15-minute auto-sync.
- **Not changed (needs a decision):** the list page's **Approved** filter still uses the unreliable checkbox, and its **Approved** count counts *Complete* only.
- **Files:** `resources/views/admin/salesforce/{show,index}.blade.php`.

### Changed (2026-09-23) — My Tasks: card layout rebuilt on the app's Stitch design system
- Feedback on the two earlier passes: the first looked "AI-designed" and the plain-table version was too basic. The page now sits between the two, built only from existing app components:
  - **Summary:** `.stitch-tile` summary tiles (All / Unread or Not Seen / Pending / In Progress / Done / Overdue), clickable as filters with the same `tile-clickable` / `tile-active` pattern as the Hospital dashboard.
  - **Task cards:** the stitch-tile look (white, 8px radius, the standard `0 1px 4px` shadow) with a 4px left border for state: maroon = unread, gold = in progress, red = overdue, green = done (faded and struck through). Grey means read and pending.
  - **Details on each card:** Bootstrap badges (status, a maroon **New** badge, Seen / Not seen yet), FontAwesome meta icons, and a `btn-cosecsa-outline` **Open** button. Maroon `nav-tabs` switch between To Me and By Me, and dark mode is covered.
- Behaviour (read tracking, inline status, live poll) is unchanged.
- **Files:** `resources/views/messaging/tasks.blade.php`.

### Fixed (2026-09-23) — `messages/tasks` 500: compiled views owned by root
- `messages/tasks` returned a 500: `file_put_contents(storage/framework/views/5269397e….php): Permission denied`, where that file is the compiled My Tasks view. Deploys ran `php artisan view:cache` as root over SSH, leaving root-owned (644) compiled views. Laravel's `CompilerEngine` recompiles when the template mtime is **>=** the compiled mtime, and `git pull` + `view:cache` in the same second produced exactly that tie. Apache (`www-data`) then couldn't overwrite the file.
- Fixed on the server with `chown -R www-data:www-data storage bootstrap/cache` for both the MIS (187 root-owned compiled views) and cosecsa-api (4 root-owned `bootstrap/cache` files). The page loads again.
- The deploy commands in `CLAUDE.md` and `DEPLOY.md` now end with that `chown`, with an explanation, so it can't recur. No code change.

### Changed (2026-09-23) — My Tasks restyled to match the app's own theme
- Replaced the custom card/chip/pill design from the earlier redesign with the app's standard building blocks, per feedback that it looked out of place:
  - the standard `content-header` title with a `btn-cosecsa-outline` Back button
  - maroon Bootstrap `nav-tabs`, styled the same as System Logs
  - an AdminLTE card holding a `table-hover` table
  - Bootstrap status badges (`badge-secondary` / `badge-warning` / `badge-success`)
  - `btn-cosecsa` / `btn-cosecsa-outline` filter buttons with count badges
  - the shared `.cosecsa-link` class for the maroon **Open** links
- Behaviour unchanged:
  - unread rows get a thin maroon left edge, a light tint, a bold title and a **New** badge
  - done rows are muted and struck through
  - overdue dates are red with an icon, and due-soon dates are amber
  - "Seen / Not seen" column on Assigned By Me
  - All / Unread / Pending / In Progress / Done / Overdue filters
  - inline status change and the 5 s live poll
- **Files:** `resources/views/messaging/tasks.blade.php`.

### Changed (2026-09-23) — My Tasks redesign with read/unread and status states
- Rebuilt `messages/tasks` in COSECSA colours:
  - "Assigned to me" and "Assigned by me" tabs; the first shows an unread dot
  - filter chips with counts: All, Unread (or "Not seen" on the by-me tab), Pending, In Progress, Done, Overdue
  - tasks shown as cards, each with an initials avatar, title, description, from/for, due date (amber when due within 2 days, red when overdue), when it was assigned, and a maroon **Open conversation** link
- **Read vs unread:** unread tasks get a maroon left edge, a light maroon tint, a bold title and a **NEW** badge, and are listed first. A task becomes read when the assignee opens it (new `GET messages/tasks/{id}/open`, which then goes to the chat), views its conversation (`MessagingController::show()`), or changes its status. `Task::markReadFor()` holds the rule.
- **Seen receipts:** on "Assigned by me", each task shows "✓ Not seen yet" or "✓✓ Seen" (seen time on hover), updated live by the 5 s poll. The poll now returns `read` / `read_at`.
- **Pending vs done:** status is a coloured pill or dropdown (Pending grey, In Progress gold, Done green). Done cards fade and are struck through with a green edge; overdue open tasks get a red edge. The assignee changes status inline, and the card restyles immediately.
- **Files:** `app/Models/Task.php`, `app/Http/Controllers/{TaskController,MessagingController}.php`, `routes/web.php`, `resources/views/messaging/tasks.blade.php`.
- **⚠️ Coordinate:** requires the cosecsa-api migration `2026_09_23_140000_add_read_at_to_tasks_table` to run first, because the page sorts by `tasks.read_at`.

### Added (2026-09-23) — More inline-edit fields on the examiner profile
- `admin/exams/view_examiner/{id}` gains pencil edits for **Full Name** (new row at the top of Personal Details), **Examiner ID**, **Role** (dropdown from `examiners_roles`), **Designation** (dropdown of the options managed under Settings, or none) and **Hospital** (Participation Summary). That makes 13 inline-editable fields, up from 8.
- Validation lives in the API: Examiner ID must be unique (the error names who has it), and Hospital needs a participation record first. Errors appear in the pencil popover.
- Left as they were: Groups, Shifts and Participation (per-year assignments with their own modals), Notes (existing memo editor), and documents/CV.
- **Files:** `app/Http/Controllers/ExamsController.php` (passes `roleOptions`), `resources/views/admin/exams/view_examiner.blade.php`.
- **⚠️ Coordinate:** requires the matching cosecsa-api deploy (quick-update allow-list + `role_options`) to land first.

### Changed (2026-09-23) — Chat messages show full date + time (local time) with day dividers
- Each message now shows its full date and time, e.g. `20 Sep 2026, 17:10`. Before, it showed a small `20 Sep, 14:10` with no year, and **in UTC**: the app timezone is UTC, so every chat time was 3 hours behind for East African staff. Hovering a time shows the full weekday/date.
- Times are converted in the browser to **each reader's own timezone** (the page carries the ISO timestamp in `data-ts`). This is correct for staff in any country without touching the app-wide `APP_TIMEZONE`, which the rest of the system's stored dates depend on. Server-rendered and poll fallbacks use Africa/Nairobi (EAT).
- WhatsApp-style **day dividers** between days: "Today", "Yesterday", the weekday for the past week, otherwise e.g. "Wed, 19 Aug 2026". They are recalculated as new messages arrive through the live poll.
- `formatMessage()` (poll/send JSON) gains `created_iso`, and `created_at` now includes the year.
- **Files:** `app/Http/Controllers/MessagingController.php`, `resources/views/messaging/show.blade.php`.

### Changed (2026-09-23) — Chat bubbles use WhatsApp-style contrasting colours
- Received messages were white bubbles on a near-white background, so a conversation of mostly incoming messages read as one undifferentiated block. The thread now uses WhatsApp's palette:
  - light mode: beige wallpaper (`#efeae2`), **white** received bubbles, **soft green** (`#d9fdd3`) sent bubbles, dark text in both
  - dark mode: `#0b141a` wallpaper, `#202c33` received, `#005c4b` sent
- Sent bubbles were previously maroon. Attachment links are blue in both bubbles, and the edit/delete icons on your own messages are recoloured to stay visible on green.
- **Files:** `resources/views/messaging/show.blade.php` (CSS only).

### Fixed (2026-09-23) — Tasks assigned in a chat weren't visible in that chat
- Reported: the "Deliverables" group (`messages/24`) seemed to have activity under Messages, but opening the group showed "No messages yet". Cause: the sidebar **Messages** badge is *unread messages + pending tasks* (`layout/header.blade.php`), and the group's activity was 2 tasks created with **Assign Task**. Tasks live in the separate `tasks` table and were only listed under My Tasks; the thread view never loaded them, and assigning one posted nothing to the chat. There were no hidden or lost messages (verified: `poll-summary` showed 0 unread, 2 pending tasks).
- The thread now shows a collapsible **Tasks** panel above the chat, listing that conversation's tasks: title, description, who it's for, who assigned it, and due date (flagged when overdue). Open tasks come first. The assignee or creator can change status inline (same `messages/tasks/{id}/status` endpoint as My Tasks); other members see a status badge. This also covers tasks created before this change.
- **Assign Task** now also posts a chat note ("Assigned a task to <name>: "<title>" (due <date>)") from the assigner, and bumps the conversation's `last_message_at`. The task therefore appears in the conversation, its list preview, and the assignee's unread count.
- **Files:** `app/Http/Controllers/MessagingController.php` (`show()` loads tasks), `app/Http/Controllers/TaskController.php` (`store()` posts the note), `resources/views/messaging/show.blade.php`.

### Changed (2026-09-23) — Subscription Report: view several years at once
- The header year dropdown is now a **multi-select year picker** (tick years → Apply; Select all / Clear all). URLs use `?years[]=2026&years[]=2025…`; old `?year=2026` links still work.
- **One year selected:** the page looks exactly as before.
- **Several years selected:**
  - The table shows one row per fellow with a **column per year** (status pill + amount paid), then **Total Paid** and **Outstanding** across the selected years.
  - Tiles sum each status across the years (shown as "% of records", i.e. fellow × year), and the owing banner counts fellows who owe for at least one selected year.
  - A status filter (tile or dropdown) shows fellows with that status in **any** selected year. It's applied MIS-side; country and search still go to the API.
- The **Excel** download modal now pre-ticks the years currently in view, so the extract matches what's on screen.
- **Reminders** are still sent one year at a time. With several years in view, the Remind modal gets a year dropdown showing each year's owing count, and switching years updates the count and subject line.
- The fellow drawer highlights every selected year in the fellow's history.
- New `ApiClient::getMany()` sends the per-year report requests concurrently (`Http::pool`), so a 3-year view loads in about the time of one year. `FeesController` has new private helpers `requestedYears()` (sanitise, de-dupe, newest first, max 10 for the view / 20 for export) and `loadSubscriptionYears()`, shared by the view and the Excel export. No cosecsa-api change.
- **Files:** `app/Services/ApiClient.php`, `app/Http/Controllers/FeesController.php`, `resources/views/admin/fees/subscription_report.blade.php`.

### Added (2026-09-23) — Multi-year Excel download on the Subscription Report
- The report's **Excel** button now opens a year picker (current year pre-ticked, Select all / Clear all) instead of exporting only the loaded table. It downloads one workbook, `annual_subscriptions_{from}-{to}.xlsx`, containing:
  - a **Summary** sheet with one row per year: total fellows, Paid/Partial/Unpaid/No Record/Waived/Owing counts, and due/collected/outstanding USD
  - **one sheet per year** listing every fellow (money columns are real numbers; bold, frozen header row)
- When the page has status/country/search filters active, an "Apply current filters" option (on by default) carries them into the export.
- New route `GET admin/fees/subscriptions/export` (`FeesController::exportSubscriptions()`, `fees.view`). It calls the existing `fees/subscriptions/report` API once per year (max 20 years), so **no cosecsa-api change is needed**. New export classes `App\Exports\SubscriptionReportExport` (multi-sheet) and `SubscriptionSheet` (uses `WithStrictNullComparison` so `0` values aren't written as blank cells).
- Copy/CSV/PDF/Print still export the table currently on screen.
- **Files:** `app/Http/Controllers/FeesController.php`, `app/Exports/{SubscriptionReportExport,SubscriptionSheet}.php`, `routes/web.php`, `resources/views/admin/fees/subscription_report.blade.php`.

### Added (2026-09-23) — Global search: recent searches (last 10)
- Focusing the navbar search box while it's empty now shows a **Recent searches** dropdown (up to 10, newest first). Clicking one re-runs it, × removes a single entry, and "Clear" empties the list.
- A search is saved when it's committed (Enter, the search icon, or opening one of its results), not on every debounced keystroke, so partial typing doesn't fill the list. Repeating a search moves it to the top instead of duplicating it (case-insensitive).
- Stored in the browser's `localStorage` under `gs_recent_{user id}`: per-browser, per-admin (a shared machine won't mix two admins' histories), nothing stored server-side. All reads and writes are wrapped in try/catch, so private windows just show no history.
- Also: a late-arriving search response no longer overwrites the dropdown if the box has been cleared or edited since.
- **Files:** `resources/views/layout/header.blade.php`.

### Removed (2026-09-23) — Subscription Report: "Collected in {year}" card
- Removed the collected-vs-billed summary card from `admin/fees/subscriptions/report` at Davis's request (its markup, CSS, and view variables). The status tiles now take the full width.
- **Files:** `resources/views/admin/fees/subscription_report.blade.php`.

### Changed (2026-09-23) — Subscription Report redesign + per-fellow subscription drawer
- Rebuilt `admin/fees/subscriptions/report`. The gradient hero is gone. The page now has a flat header with a year picker and a "Remind N owing" button, a **Collected** card (collected vs billed, with a progress bar), and five **status tiles** (Paid / Partial / Unpaid / No Record / Waived). Each tile shows its share of fellows and filters the table when clicked (click again to clear). Under the tiles are a status-distribution bar and an owing banner.
- The table now sits in one card, with the search / status / country filters in its toolbar (still server-side). Rows show an initials avatar, soft status pills and right-aligned money columns. Copy/CSV/Excel/PDF/Print export is unchanged; the avatar uses a CSS `data-initials` pseudo-element so exports stay clean.
- **Clicking any row opens a slide-in drawer** (modelled on the voting app's Member Profile panel) with:
  - three tiles: this year's status, Total Paid, Owing
  - contact details: email, phone, country
  - the fellow's full **Subscription History**: a row per year with status pill, amount, date/mode, and "of $X" for partial payments
  - Total Paid / Outstanding footers
  - links to the full profile and (for `fees.manage`) the fellow's subscriptions page

  Supports dark mode; closes with Esc or a backdrop click.
- New JSON route `GET admin/fees/subscriptions/fellow/{id}` (`FeesController::subscriptionFellow()`) feeds the drawer. It reuses the existing `fellows/{id}/detail` API endpoint, so **no cosecsa-api change or coordinated deploy is needed**. It lives under `admin/fees`, so `fees.view` is enough to use it.
- **Files:** `app/Http/Controllers/FeesController.php`, `routes/web.php`, `resources/views/admin/fees/subscription_report.blade.php`.

### Added (2026-09-23) — Annual Subscription Report per year + reminder emails
- New Fees subpage at `admin/fees/subscriptions/report` (named route `admin.fees.subscriptions.report`): every fellow's payment status for a chosen year, including fellows with **no subscription record at all** (shown as "No Record", treated as owing — many fellows simply never had a record created).
- Summary tiles (total fellows, Paid/Partial/Unpaid/Waived/No Record counts, Owing, due/collected/outstanding USD), filters (year, status, country, search), DataTable with copy/CSV/Excel/PDF/print export, and click-through to each fellow's subscription tab.
- **Send Reminders** (only visible with `fees.manage`, using the same permission gate as recording payments): a modal composes subject + body (with `{{name}}`, `{{first_name}}`, `{{year}}`, `{{country}}`, `{{fellowship_type}}`, `{{amount_due}}`, `{{amount_paid}}`, `{{outstanding}}` tokens inserted via clickable chips) and emails every outstanding fellow for the year — Unpaid, Partial, or No Record; Waived/Paid are never touched.
- Record Payment form's Mode of Payment dropdown gained **Country Office** (alongside Bank Transfer/Online/Cash/Cheque/Mobile Money), plus a matching datalist on the Edit Payment modal's free-text Mode field.
- Sidebar: "Subscriptions Report" link under the Fees treeview.
- **Files:** `app/Http/Controllers/FeesController.php`, `routes/web.php`, `resources/views/admin/fees/{manage,subscription_report}.blade.php`, `resources/views/layout/header.blade.php`.
- **⚠️ Coordinate:** requires the matching cosecsa-api deploy (new `fees/subscriptions/report` + `fees/subscriptions/remind` endpoints, `fee_reminders` migration, `AnnualSubscriptionReminderMail`) to land first — run `php artisan migrate` there before/alongside this.

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
