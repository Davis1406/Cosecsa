# Change Log

> **Important:** Every new change, feature, bug fix, or configuration update must be documented in this file. Add entries under the `[Unreleased]` section as work is completed, then move them to a versioned section when a release is tagged.

## [Unreleased]

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
