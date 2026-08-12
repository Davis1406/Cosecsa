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
