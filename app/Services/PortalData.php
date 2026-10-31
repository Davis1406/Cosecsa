<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * What the Candidate, Member, Programme Director and Country Representative dashboards show. Everything is
 * scoped to the signed-in user's own records (their candidate / member row, the hospital they direct, the
 * country they represent), and the lists carry names and training details only: no emails or phone numbers
 * of other people.
 */
class PortalData
{
    /** Trainees in these statuses are "current" for a Programme Director / Country Rep. */
    private const CURRENT = ['Active'];

    private static function fullName(object $row): string
    {
        return trim(preg_replace('/\s+/', ' ', ($row->firstname ?? '').' '.($row->middlename ?? '').' '.($row->lastname ?? '')));
    }

    /** One entry per exam sitting the candidate registered for. */
    public function candidate(int $userId): array
    {
        $entries = DB::table('candidates')
            ->leftJoin('programmes', 'programmes.id', '=', 'candidates.programme_id')
            ->leftJoin('hospitals', 'hospitals.id', '=', 'candidates.hospital_id')
            ->leftJoin('countries', 'countries.id', '=', 'candidates.country_id')
            ->where('candidates.user_id', $userId)
            ->orderByDesc('candidates.exam_year')->orderByDesc('candidates.id')
            ->select('candidates.*', 'programmes.name as programme_name', 'hospitals.name as hospital_name', 'countries.country_name')
            ->get()
            ->map(function ($c) {
                $c->full_name = self::fullName($c);
                $c->fee_status = $c->fee_paid === 'Yes' ? 'Paid' : ($c->invoice_status === 'Complete' ? 'Paid' : ($c->invoice_status ?: 'Pending'));

                return $c;
            });

        return ['entries' => $entries, 'current' => $entries->first()];
    }

    /** The member's own record(s). */
    public function member(int $userId): array
    {
        $records = DB::table('members')
            ->leftJoin('categories', 'categories.id', '=', 'members.category_id')
            ->leftJoin('countries', 'countries.id', '=', 'members.country_id')
            ->where('members.user_id', $userId)
            ->where('members.is_deleted', '0')
            ->select('members.*', 'categories.category_name', 'countries.country_name')
            ->get()
            ->map(function ($m) {
                $m->full_name = self::fullName($m);

                return $m;
            });

        return ['records' => $records, 'current' => $records->first()];
    }

    /**
     * The hospital(s) this user directs: its accredited programmes and the trainees currently there.
     *
     * @return array{hospitals: Collection}
     */
    public function programmeDirector(int $userId): array
    {
        $hospitalIds = DB::table('programme_directors')->where('user_id', $userId)->whereNotNull('hospital_id')->pluck('hospital_id')->unique()->values();

        $hospitals = DB::table('hospitals')
            ->leftJoin('countries', 'countries.id', '=', 'hospitals.country_id')
            ->whereIn('hospitals.id', $hospitalIds)
            ->select('hospitals.*', 'countries.country_name')
            ->get()
            ->map(function ($h) {
                $h->programmes = DB::table('hospital_programmes')
                    ->join('programmes', 'programmes.id', '=', 'hospital_programmes.programme_id')
                    ->where('hospital_programmes.hospital_id', $h->id)->where('hospital_programmes.is_delete', 0)
                    ->orderBy('programmes.name')
                    ->select('programmes.name', 'hospital_programmes.status', 'hospital_programmes.accredited_date', 'hospital_programmes.expiry_date')
                    ->get();

                $trainees = DB::table('trainees')
                    ->leftJoin('programmes', 'programmes.id', '=', 'trainees.programme_id')
                    ->where('trainees.hospital_id', $h->id)
                    ->whereIn('trainees.status', self::CURRENT)
                    ->orderBy('programmes.name')->orderBy('trainees.lastname')->orderBy('trainees.firstname')
                    ->select('trainees.firstname', 'trainees.middlename', 'trainees.lastname', 'trainees.entry_number', 'trainees.admission_year',
                        'trainees.training_year', 'trainees.status', 'programmes.name as programme_name')
                    ->get()
                    ->map(function ($t) {
                        $t->full_name = self::fullName($t);

                        return $t;
                    });
                $h->trainees = $trainees;
                $h->by_programme = $trainees->groupBy(fn ($t) => $t->programme_name ?: 'No programme')->map->count()->sortKeys();
                $h->completed_count = DB::table('trainees')->where('hospital_id', $h->id)->where('status', 'Completed')->count();

                return $h;
            });

        return ['hospitals' => $hospitals];
    }

    /**
     * The country (or countries) this user represents, with headline numbers, a per-programme table, a
     * per-hospital table and the list of current trainees. Trainees and candidates are counted by their own country; hospitals by the hospital's.
     *
     * @return array{countries: Collection}
     */
    public function countryRep(int $userId): array
    {
        $countryIds = DB::table('country_reps')->where('user_id', $userId)->whereNotNull('country_id')->pluck('country_id')->unique()->values();

        $countries = DB::table('countries')->whereIn('id', $countryIds)->orderBy('country_name')->get()->map(function ($c) {
            $id = $c->id;
            $c->counts = [
                'trainees_active' => DB::table('trainees')->where('country_id', $id)->whereIn('status', self::CURRENT)->count(),
                'trainees_completed' => DB::table('trainees')->where('country_id', $id)->where('status', 'Completed')->count(),
                'candidates' => DB::table('candidates')->where('country_id', $id)->where('exam_year', date('Y'))->count(),
                'fellows' => DB::table('fellows')->where('country_id', $id)->where('status', 'Active')->count(),
                'members' => DB::table('members')->where('country_id', $id)->where('is_deleted', '0')->count(),
                'hospitals' => DB::table('hospitals')->where('country_id', $id)->where('is_deleted', 0)->where('status', 1)->count(),
            ];

            $c->by_programme = DB::table('trainees')
                ->leftJoin('programmes', 'programmes.id', '=', 'trainees.programme_id')
                ->where('trainees.country_id', $id)->whereIn('trainees.status', self::CURRENT)
                ->groupBy('programmes.name')->orderBy('programmes.name')
                ->selectRaw("programmes.name as programme_name, count(*) as total, sum(case when trainees.gender = 'Female' then 1 else 0 end) as female")
                ->get();

            $c->trainees = DB::table('trainees')
                ->leftJoin('programmes', 'programmes.id', '=', 'trainees.programme_id')
                ->leftJoin('hospitals', 'hospitals.id', '=', 'trainees.hospital_id')
                ->where('trainees.country_id', $id)->whereIn('trainees.status', self::CURRENT)
                ->orderBy('trainees.lastname')->orderBy('trainees.firstname')
                ->select('trainees.firstname', 'trainees.middlename', 'trainees.lastname', 'trainees.entry_number', 'trainees.admission_year',
                    'programmes.name as programme_name', 'hospitals.name as hospital_name')
                ->get()
                ->map(function ($t) {
                    $t->full_name = self::fullName($t);

                    return $t;
                });

            $directors = DB::table('programme_directors')
                ->join('users', 'users.id', '=', 'programme_directors.user_id')
                ->whereNotNull('programme_directors.hospital_id')
                ->orderBy('users.name')
                ->get(['programme_directors.hospital_id', 'users.name'])
                ->groupBy('hospital_id')->map(fn ($rows) => $rows->pluck('name')->unique()->implode(', '));

            $c->hospitals = DB::table('hospitals')
                ->where('hospitals.country_id', $id)->where('hospitals.is_deleted', 0)
                ->orderBy('hospitals.name')
                ->select('hospitals.id', 'hospitals.name', 'hospitals.status')
                ->get()
                ->map(function ($h) use ($directors) {
                    $h->active_trainees = DB::table('trainees')->where('hospital_id', $h->id)->whereIn('status', self::CURRENT)->count();
                    $h->programme_count = DB::table('hospital_programmes')->where('hospital_id', $h->id)->where('is_delete', 0)->where('status', 'Active')->count();
                    $h->directors = $directors[$h->id] ?? null;

                    return $h;
                });

            return $c;
        });

        return ['countries' => $countries];
    }
}
