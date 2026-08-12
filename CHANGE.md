# Change Log

> **Important:** Every new change, feature, bug fix, or configuration update must be documented in this file. Add entries under the `[Unreleased]` section as work is completed, then move them to a versioned section when a release is tagged.

## [Unreleased]

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
