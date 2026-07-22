<?php

namespace App\Services;

use App\Models\LetterDispatchRecipient;
use Illuminate\Support\Facades\DB;

class LetterRecipientResolver
{
    public const SOURCES = [
        'trainees'     => 'Trainees',
        'candidates'   => 'Candidates',
        'fellows'      => 'Fellows',
        'examiners'    => 'Examiners',
        'country_reps' => 'Country Reps',
        'trainers'     => 'Trainers (Programme Directors)',
    ];

    public function countries()
    {
        return DB::table('countries')->orderBy('country_name')->pluck('country_name', 'id');
    }

    public function programmes()
    {
        return DB::table('programmes')->orderBy('name')->pluck('name', 'id');
    }

    // Accepts either a single id or an array of ids (multi-select filter
    // panels submit arrays) and applies a whereIn when non-empty.
    protected function applyIn($q, string $col, $value): void
    {
        $ids = array_filter((array) $value);
        if (! empty($ids)) $q->whereIn($col, $ids);
    }

    /**
     * Returns a Collection of normalized stdClass rows:
     * source, id, user_id, name, email, country, programme, hospital,
     * entry_number, exam_year, admission_year, sfs_username, sfs_password,
     * legacy_status (nullable — value of the trainee's letter status field).
     */
    public function query(string $source, array $filters = [], ?string $legacyStatusField = null)
    {
        return match ($source) {
            'trainees'     => $this->trainees($filters, $legacyStatusField),
            'candidates'   => $this->candidates($filters),
            'fellows'      => $this->fellows($filters),
            'examiners'    => $this->examiners($filters),
            'country_reps' => $this->countryReps($filters),
            'trainers'     => $this->trainers($filters),
            default        => collect(),
        };
    }

    protected function trainees(array $filters, ?string $legacyStatusField)
    {
        $q = DB::table('trainees as t')
            ->leftJoin('countries as co', 'co.id', '=', 't.country_id')
            ->leftJoin('programmes as p', 'p.id', '=', 't.programme_id')
            ->leftJoin('hospitals as h', 'h.id', '=', 't.hospital_id')
            ->where('t.status', '!=', 'Inactive')
            ->select('t.*', 'co.country_name', 'p.name as programme_name', 'h.name as hospital_name');

        $this->applyIn($q, 't.country_id', $filters['country_id'] ?? null);
        $this->applyIn($q, 't.programme_id', $filters['programme_id'] ?? null);
        if (! empty($filters['year'])) $q->where('t.admission_year', $filters['year']);
        if (! empty($filters['search'])) {
            $like = '%' . $filters['search'] . '%';
            $q->where(function ($w) use ($like) {
                $w->where('t.firstname', 'like', $like)->orWhere('t.lastname', 'like', $like)->orWhere('t.entry_number', 'like', $like);
            });
        }
        if (! empty($filters['unsent_only']) && $legacyStatusField && in_array($legacyStatusField, ['admission_letter_status', 'invitation_letter_status'])) {
            $q->where("t.{$legacyStatusField}", '!=', 'Sent');
        }

        return $q->orderBy('t.firstname')->get()->map(function ($r) {
            return (object) [
                'source' => 'trainees', 'id' => $r->id, 'user_id' => $r->user_id,
                'name' => trim("{$r->firstname} {$r->middlename} {$r->lastname}"),
                'email' => $r->personal_email,
                'country' => $r->country_name, 'programme' => $r->programme_name, 'hospital' => $r->hospital_name,
                'entry_number' => $r->entry_number, 'exam_year' => $r->exam_year, 'admission_year' => $r->admission_year,
                'sfs_username' => $r->sfs_username, 'sfs_password' => $r->sfs_password,
                'admission_letter_status' => $r->admission_letter_status,
                'invitation_letter_status' => $r->invitation_letter_status,
            ];
        });
    }

    protected function candidates(array $filters)
    {
        $q = DB::table('candidates as c')
            ->leftJoin('countries as co', 'co.id', '=', 'c.country_id')
            ->leftJoin('programmes as p', 'p.id', '=', 'c.programme_id')
            ->leftJoin('hospitals as h', 'h.id', '=', 'c.hospital_id')
            ->select('c.*', 'co.country_name', 'p.name as programme_name', 'h.name as hospital_name');

        $this->applyIn($q, 'c.country_id', $filters['country_id'] ?? null);
        $this->applyIn($q, 'c.programme_id', $filters['programme_id'] ?? null);
        if (! empty($filters['year'])) $q->where('c.exam_year', $filters['year']);
        if (! empty($filters['search'])) {
            $like = '%' . $filters['search'] . '%';
            $q->where(function ($w) use ($like) {
                $w->where('c.firstname', 'like', $like)->orWhere('c.lastname', 'like', $like)->orWhere('c.entry_number', 'like', $like);
            });
        }

        return $q->orderBy('c.firstname')->get()->map(function ($r) {
            return (object) [
                'source' => 'candidates', 'id' => $r->id, 'user_id' => $r->user_id,
                'name' => trim("{$r->firstname} {$r->middlename} {$r->lastname}"),
                'email' => $r->personal_email,
                'country' => $r->country_name, 'programme' => $r->programme_name, 'hospital' => $r->hospital_name,
                'entry_number' => $r->entry_number, 'exam_year' => $r->exam_year, 'admission_year' => $r->admission_year,
                'sfs_username' => null, 'sfs_password' => null,
            ];
        });
    }

    protected function fellows(array $filters)
    {
        $q = DB::table('fellows as f')
            ->leftJoin('countries as co', 'co.id', '=', 'f.country_id')
            ->leftJoin('programmes as p', 'p.id', '=', 'f.programme_id')
            ->select('f.*', 'co.country_name', 'p.name as programme_name');

        $this->applyIn($q, 'f.country_id', $filters['country_id'] ?? null);
        $this->applyIn($q, 'f.programme_id', $filters['programme_id'] ?? null);
        if (! empty($filters['year'])) $q->where('f.fellowship_year', $filters['year']);
        if (! empty($filters['search'])) {
            $like = '%' . $filters['search'] . '%';
            $q->where(function ($w) use ($like) {
                $w->where('f.firstname', 'like', $like)->orWhere('f.lastname', 'like', $like)->orWhere('f.candidate_number', 'like', $like);
            });
        }

        return $q->orderBy('f.firstname')->get()->map(function ($r) {
            return (object) [
                'source' => 'fellows', 'id' => $r->id, 'user_id' => $r->user_id,
                'name' => trim("{$r->firstname} {$r->middlename} {$r->lastname}"),
                'email' => $r->personal_email,
                'country' => $r->country_name, 'programme' => $r->programme_name, 'hospital' => $r->organization,
                'entry_number' => $r->candidate_number, 'exam_year' => $r->exam_year_upcoming, 'admission_year' => $r->admission_year,
                'sfs_username' => null, 'sfs_password' => null,
            ];
        });
    }

    protected function examiners(array $filters)
    {
        $q = DB::table('examiners as e')
            ->join('users as u', 'u.id', '=', 'e.user_id')
            ->leftJoin('countries as co', 'co.id', '=', 'e.country_id')
            ->select('e.*', 'u.name as user_name', 'u.email as user_email', 'co.country_name');

        $this->applyIn($q, 'e.country_id', $filters['country_id'] ?? null);
        if (! empty($filters['search'])) {
            $like = '%' . $filters['search'] . '%';
            $q->where('u.name', 'like', $like);
        }

        return $q->orderBy('u.name')->get()->map(function ($r) {
            return (object) [
                'source' => 'examiners', 'id' => $r->id, 'user_id' => $r->user_id,
                'name' => $r->user_name, 'email' => $r->user_email,
                'country' => $r->country_name, 'programme' => $r->specialty, 'hospital' => null,
                'entry_number' => $r->examiner_id, 'exam_year' => null, 'admission_year' => null,
                'sfs_username' => null, 'sfs_password' => null,
            ];
        });
    }

    protected function countryReps(array $filters)
    {
        $q = DB::table('country_reps as cr')
            ->join('users as u', 'u.id', '=', 'cr.user_id')
            ->leftJoin('countries as co', 'co.id', '=', 'cr.country_id')
            ->select('cr.*', 'u.name as user_name', 'u.email as user_email', 'co.country_name');

        $this->applyIn($q, 'cr.country_id', $filters['country_id'] ?? null);
        if (! empty($filters['search'])) {
            $like = '%' . $filters['search'] . '%';
            $q->where('u.name', 'like', $like);
        }

        return $q->orderBy('u.name')->get()->map(function ($r) {
            return (object) [
                'source' => 'country_reps', 'id' => $r->id, 'user_id' => $r->user_id,
                'name' => $r->user_name, 'email' => $r->cosecsa_email ?: $r->user_email,
                'country' => $r->country_name, 'programme' => $r->position, 'hospital' => null,
                'entry_number' => null, 'exam_year' => null, 'admission_year' => null,
                'sfs_username' => null, 'sfs_password' => null,
            ];
        });
    }

    protected function trainers(array $filters)
    {
        $q = DB::table('trainers as tr')
            ->join('users as u', 'u.id', '=', 'tr.user_id')
            ->leftJoin('hospitals as h', 'h.id', '=', 'tr.hospital_id')
            ->leftJoin('countries as co', 'co.id', '=', 'h.country_id')
            ->select('tr.*', 'u.name as user_name', 'u.email as user_email', 'h.name as hospital_name', 'co.country_name');

        $this->applyIn($q, 'co.id', $filters['country_id'] ?? null);
        if (! empty($filters['search'])) {
            $like = '%' . $filters['search'] . '%';
            $q->where('u.name', 'like', $like);
        }

        return $q->orderBy('u.name')->get()->map(function ($r) {
            return (object) [
                'source' => 'trainers', 'id' => $r->id, 'user_id' => $r->user_id,
                'name' => $r->user_name, 'email' => $r->user_email,
                'country' => $r->country_name, 'programme' => null, 'hospital' => $r->hospital_name,
                'entry_number' => null, 'exam_year' => null, 'admission_year' => null,
                'sfs_username' => null, 'sfs_password' => null,
            ];
        });
    }

    public function mergeFields($r, ?\Carbon\Carbon $letterDate = null): array
    {
        $nameParts = explode(' ', trim($r->name ?? ''));

        return [
            'name'           => $r->name,
            'first_name'     => $nameParts[0] ?? '',
            'email'          => $r->email,
            'country'        => $r->country,
            'programme'      => $r->programme,
            'hospital'       => $r->hospital,
            'entry_number'   => $r->entry_number,
            'exam_year'      => $r->exam_year,
            'admission_year' => $r->admission_year,
            'sfs_username'   => $r->sfs_username,
            'sfs_password'   => $r->sfs_password,
            'date'           => ($letterDate ?? now())->format('jS F, Y'),
        ];
    }

    public function render(string $template, array $fields): string
    {
        $out = $template;
        foreach ($fields as $k => $v) {
            $out = str_ireplace('{{' . $k . '}}', (string) ($v ?? ''), $out);
        }
        return $out;
    }

    public function alreadySent(int $templateId, string $source, $id): bool
    {
        return LetterDispatchRecipient::where('letter_template_id', $templateId)
            ->where('recipient_source', $source)->where('recipient_id', $id)
            ->where('status', 'sent')->exists();
    }
}
