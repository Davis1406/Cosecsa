<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserRole;
use App\Models\HospitalModel;
use App\Models\YearModel;
use App\Models\ExaminerGroup;
use App\Models\FellowsModel;
use App\Services\ApiClient;

class DashboardController extends Controller
{
    public function __construct(private ApiClient $api) {}

public function dashboard()
    {
        $data['header_title'] = 'Dashboard';
        
        $activeRole = Auth::user()->getActiveRole();
        $data['userRoles'] = Auth::user()->getRoles();
        $data['activeRole'] = $activeRole;

        switch ($activeRole) {
            case 1:
                // Admin dashboard logic. Counts are sourced from the same API
                // endpoints the list pages use, so the tiles reconcile with the
                // respective tables (trainees, candidates, fellows, hospitals).
                // Trainees use the reports drill-down endpoint (filter=all) so
                // the tile matches the "Showing X of Y" total on the reports
                // table, which includes trainees promoted to Fellow.
                $trainees   = collect($this->api->get('trainees/reports/list', ['filter' => 'all'])->object() ?? []);
                $candidates = collect($this->api->get('candidates/list-data')->object()->candidates ?? []);
                $fellows    = collect($this->api->get('fellows/list-data')->object()->fellows ?? []);
                $hospitals  = $this->api->get('admin/hospitals')->object();

                $data['traineeCount']            = $trainees->count();
                $data['CandidateCount']          = $candidates->where('exam_year', (string) date('Y'))->count();
                $data['FellowsCount']            = $fellows->count();
                $data['accreditedHospitalCount'] = $hospitals->total_active ?? 0;

                // Unread messages / pending tasks — shown as a red count
                // above the Admission Data / Calendar row.
                $userId = Auth::id();
                $data['unreadConversationsCount'] = DB::table('conversation_participants as cp')
                    ->where('cp.user_id', $userId)
                    ->whereRaw('EXISTS (
                        SELECT 1 FROM messages m
                        WHERE m.conversation_id = cp.conversation_id
                        AND m.sender_id != ?
                        AND (cp.last_read_at IS NULL OR m.created_at > cp.last_read_at)
                    )', [$userId])
                    ->count();
                $data['pendingTasksCount'] = DB::table('tasks')
                    ->where('assigned_to', $userId)
                    ->where('status', '!=', 'done')
                    ->count();

                // Admission Data chart: Alumni (Fellow by Exam, category_id=5
                // + is_alumni=1) by graduation year, split out by female
                // graduates. "All Alumni" also counts each additional FCS
                // specialty as its own entry in its own year — same logic
                // used by the "All Alumni" filter on the Fellows list, so
                // the totals here reconcile with that page. Built from the
                // API fellows payload (same source as the Fellows list).
                $primaryAlumni = $fellows->where('category_id', 5)->where('is_alumni', 1);

                $alumniEntries = collect();
                foreach ($primaryAlumni as $p) {
                    $alumniEntries->push((object) ['year' => $p->fellowship_year, 'gender' => $p->gender]);
                }
                foreach ($primaryAlumni as $e) {
                    if (!empty($e->second_fcs_specialty)) {
                        $alumniEntries->push((object) ['year' => $e->second_fcs_year, 'gender' => $e->gender]);
                    }
                    if (!empty($e->third_fcs_specialty)) {
                        $alumniEntries->push((object) ['year' => $e->third_fcs_year, 'gender' => $e->gender]);
                    }
                }

                $byYear = $alumniEntries
                    ->groupBy(fn ($e) => $e->year ?: 'Unknown')
                    ->sortKeys()
                    ->map(fn ($group) => [
                        'total'  => $group->count(),
                        'female' => $group->where('gender', 'Female')->count(),
                    ]);

                $data['alumniYearLabels'] = $byYear->keys();
                $data['alumniYearTotals'] = $byYear->pluck('total');
                $data['alumniYearFemale'] = $byYear->pluck('female');
                $data['allAlumniCount']   = $alumniEntries->count();
                $data['femaleAlumniCount'] = $alumniEntries->where('gender', 'Female')->count();

                return view('admin.dashboard', $data);
                
            case 2:
                return view('trainee.dashboard', $data);
                
            case 7:
                $fellow = User::getFellows()->firstWhere('user_id', Auth::id());
                if ($fellow) {
                    $examinerPassport = DB::table('examiners')
                        ->where('user_id', Auth::id())
                        ->value('passport_image');
                    $imagePath = null;
                    foreach ([$fellow->profile_image, $examinerPassport] as $p) {
                        if (!empty($p) && !str_starts_with(basename($p), 'capsule_')) {
                            $imagePath = $p;
                            break;
                        }
                    }
                    $fellow->profile_image_url = $imagePath
                        ? 'https://cosecsamis.org/storage/' . ltrim($imagePath, '/')
                        : null;
                }
                $data['fellow'] = $fellow;

                // Load subscriptions if fellow record exists
                $data['subscriptions'] = collect();
                if ($fellow) {
                    $data['subscriptions'] = \DB::table('fellow_subscriptions')
                        ->where('fellow_id', $fellow->fellow_id)
                        ->orderBy('year', 'desc')
                        ->get();

                    // Load exam history from mcs_results and gs_results via candidates table
                    $candidate = \DB::table('candidates')
                        ->where('user_id', Auth::id())
                        ->first();

                    $data['examHistory'] = collect();
                    if ($candidate) {
                        $mcs = \DB::table('mcs_results')
                            ->where('candidate_id', $candidate->id)
                            ->select('exam_year','exam_type','overall','remarks','created_at')
                            ->get()->map(fn($r) => (object)array_merge((array)$r, ['source'=>'MCS']));

                        $gs = \DB::table('gs_results')
                            ->where('candidate_id', $candidate->id)
                            ->select('exam_year','exam_type','overall','remarks','created_at')
                            ->get()->map(fn($r) => (object)array_merge((array)$r, ['source'=>'FCS GS']));

                        $data['examHistory'] = $mcs->merge($gs)->sortByDesc('exam_year')->values();
                    }
                }
                return view('fellow.dashboard', $data);
                
            case 9:
                return view('examiner.dashboard', $data);
                
            default:
                Auth::logout();
                return redirect('login')->with('error', 'Invalid role');
        }
    }

    /**
     * GET admin/global-search?q=...
     * Returns JSON: { trainees, candidates, examiners, fellows, trainers, members, country_reps }
     */
    public function globalSearch(Request $request)
    {
        $empty = [
            'trainees' => [], 'candidates' => [], 'examiners' => [], 'fellows' => [],
            'trainers' => [], 'members' => [], 'country_reps' => [],
        ];

        $q = trim($request->input('q', ''));
        if (strlen($q) < 2) {
            return response()->json($empty);
        }

        $like = '%' . $q . '%';

        // Trainees
        //
        // Gate on the ACTIVE TRAINEE ROLE (user_roles.role_type = 2), not on
        // users.user_type — that column only stores one "primary" role per
        // user, so anyone whose primary role is Fellow/Trainer/Examiner but
        // who also holds an active trainee role (e.g. a fellow re-training in
        // a second specialty, or a trainer/examiner doing a trainee-tracked
        // programme) would otherwise never appear here even though they have
        // a real trainee record. Same pattern already used by
        // TraineeController::listData()/bulkUpdateData() in cosecsa-api.
        $trainees = DB::table('trainees as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->join('user_roles as ur', function ($j) {
                $j->on('ur.user_id', '=', 'u.id')->where('ur.role_type', 2)->where('ur.is_active', 1);
            })
            ->leftJoin('programmes as p', 'p.id', '=', 't.programme_id')
            ->where('u.is_deleted', 0)
            // Only exclude a trainee row when the matching fellow record is
            // for the SAME programme — that's a leftover row from a
            // completed programme. A different programme means genuine
            // re-training in a second specialty (e.g. Emmanuel Koma, Dr
            // Emmanuel Bua), which should still be searchable. Without the
            // programme_id check this wrongly hid every such person — same
            // fix already applied to TraineeController::listData().
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('fellows')
                    ->whereColumn('fellows.candidate_number', 't.entry_number')
                    ->whereColumn('fellows.programme_id', 't.programme_id')
                    ->whereNotNull('t.entry_number')
                    ->where('t.entry_number', '!=', '');
            })
            ->where(function ($w) use ($like) {
                $w->where('u.name', 'like', $like)
                  ->orWhere('t.entry_number', 'like', $like)
                  ->orWhere('t.personal_email', 'like', $like);
            })
            ->select('t.id as trainee_id', 'u.name', 't.entry_number', 'p.name as programme')
            ->orderBy('u.name')->limit(8)->get()
            ->map(fn($r) => [
                'name' => $r->name,
                'sub'  => implode(' · ', array_filter([$r->entry_number, $r->programme])),
                'url'  => url('admin/associates/trainees/view/' . $r->trainee_id),
            ]);

        // Candidates
        $candidates = DB::table('candidates as c')
            ->join('users as u', 'u.id', '=', 'c.user_id')
            ->leftJoin('programmes as p', 'p.id', '=', 'c.programme_id')
            ->where('u.is_deleted', 0)
            ->where(function ($w) use ($like) {
                $w->where('u.name', 'like', $like)
                  ->orWhere('c.entry_number', 'like', $like)
                  ->orWhere('c.candidate_id', 'like', $like)
                  ->orWhere('c.personal_email', 'like', $like);
            })
            ->select('c.id as cid', 'u.name', 'c.entry_number', 'c.exam_year', 'p.name as programme')
            ->orderByDesc('c.exam_year')->orderBy('u.name')->limit(8)->get()
            ->map(fn($r) => [
                'name' => $r->name,
                'sub'  => implode(' · ', array_filter([$r->entry_number, $r->programme, $r->exam_year])),
                'url'  => url('admin/associates/candidates/view/' . $r->cid),
            ]);

        // Examiners
        $examiners = DB::table('examiners as e')
            ->join('users as u', 'u.id', '=', 'e.user_id')
            ->leftJoin('countries as co', 'co.id', '=', 'e.country_id')
            ->where('u.is_deleted', 0)
            ->where(function ($w) use ($like) {
                $w->where('u.name', 'like', $like)
                  ->orWhere('e.examiner_id', 'like', $like)
                  ->orWhere('u.email', 'like', $like)
                  ->orWhere('e.specialty', 'like', $like);
            })
            ->select('e.id as examin_id', 'u.name', 'e.examiner_id', 'e.specialty', 'co.country_name')
            ->orderBy('u.name')->limit(8)->get()
            ->map(fn($r) => [
                'name' => $r->name,
                'sub'  => implode(' · ', array_filter([$r->examiner_id, $r->specialty, $r->country_name])),
                'url'  => url('admin/exams/view_examiner/' . $r->examin_id),
            ]);

        // Fellows
        $fellows = DB::table('fellows as f')
            ->join('users as u', 'u.id', '=', 'f.user_id')
            ->leftJoin('countries as co', 'co.id', '=', 'f.country_id')
            ->where('u.is_deleted', 0)
            ->where(function ($w) use ($like) {
                $w->where('u.name', 'like', $like)
                  ->orWhere('f.candidate_number', 'like', $like)
                  ->orWhere('f.personal_email', 'like', $like);
            })
            ->select('f.id as fellow_id', 'u.name', 'f.candidate_number', 'co.country_name')
            ->orderBy('u.name')->limit(8)->get()
            ->map(fn($r) => [
                'name' => $r->name,
                'sub'  => implode(' · ', array_filter([$r->candidate_number, $r->country_name])),
                'url'  => url('admin/associates/fellows/view/' . $r->fellow_id),
            ]);

        // Trainers
        $trainers = DB::table('trainers as tr')
            ->join('users as u', 'u.id', '=', 'tr.user_id')
            ->leftJoin('hospitals as h', 'h.id', '=', 'tr.hospital_id')
            ->leftJoin('programmes as p', 'p.id', '=', 'tr.programme_id')
            ->where('u.is_deleted', 0)
            ->where(function ($w) use ($like) {
                $w->where('u.name', 'like', $like)
                  ->orWhere('u.email', 'like', $like)
                  ->orWhere('tr.phone_number', 'like', $like)
                  ->orWhere('tr.mobile_no', 'like', $like);
            })
            ->select('tr.id as trainer_id', 'u.name', 'h.name as hospital_name', 'p.name as programme')
            ->orderBy('u.name')->limit(8)->get()
            ->map(fn($r) => [
                'name' => $r->name,
                'sub'  => implode(' · ', array_filter([$r->hospital_name, $r->programme])),
                'url'  => url('admin/associates/trainers/view/' . $r->trainer_id),
            ]);

        // Members
        $members = DB::table('members as m')
            ->join('users as u', 'u.id', '=', 'm.user_id')
            ->leftJoin('countries as co', 'co.id', '=', 'm.country_id')
            // members.is_deleted is ENUM('0','1'), not a boolean/int column —
            // comparing against integer 0 matches on the enum's internal
            // storage index (1) instead of the '0' label and silently drops
            // every row. Must compare against the string '0'.
            ->where('m.is_deleted', '0')
            ->where('u.is_deleted', 0)
            ->where(function ($w) use ($like) {
                $w->where('u.name', 'like', $like)
                  ->orWhere('m.member_id_number', 'like', $like)
                  ->orWhere('m.personal_email', 'like', $like);
            })
            ->select('m.id as member_id', 'u.name', 'm.member_id_number', 'co.country_name')
            ->orderBy('u.name')->limit(8)->get()
            ->map(fn($r) => [
                'name' => $r->name,
                'sub'  => implode(' · ', array_filter([$r->member_id_number, $r->country_name])),
                'url'  => url('admin/associates/members/view/' . $r->member_id),
            ]);

        // Country Reps
        $country_reps = DB::table('country_reps as cr')
            ->join('users as u', 'u.id', '=', 'cr.user_id')
            ->leftJoin('countries as co', 'co.id', '=', 'cr.country_id')
            ->where('u.is_deleted', 0)
            ->where(function ($w) use ($like) {
                $w->where('u.name', 'like', $like)
                  ->orWhere('cr.cosecsa_email', 'like', $like)
                  ->orWhere('cr.position', 'like', $like);
            })
            ->select('cr.id as rep_id', 'u.name', 'cr.position', 'co.country_name')
            ->orderBy('u.name')->limit(8)->get()
            ->map(fn($r) => [
                'name' => $r->name,
                'sub'  => implode(' · ', array_filter([$r->position, $r->country_name])),
                'url'  => url('admin/associates/reps/view/' . $r->rep_id),
            ]);

        return response()->json(compact(
            'trainees', 'candidates', 'examiners', 'fellows', 'trainers', 'members', 'country_reps'
        ));
    }

    // Updated examiner form method
    public function examinerform()
    {
        $currentYear = YearModel::orderBy('id', 'desc')->first();
        $yearId = $currentYear ? $currentYear->id : null;

        // Get examiner's groups for current year
        $examinerGroups = User::getExaminerGroups(Auth::id(), $yearId);
        $examinerGroupIds = $examinerGroups->pluck('id')->toArray();

        // Get all available groups
        $allGroups = ExaminerGroup::all();

        // Get candidates for examiner's groups in current year
        $candidates = User::getExaminerCandidates(Auth::id(), $yearId);

        $data['header_title'] = 'Examiner Form';
        $data['getRecord'] = $candidates;
        $data['groups'] = $allGroups;
        $data['examinerGroups'] = $examinerGroups;
        $data['examinerGroupIds'] = $examinerGroupIds;
        $data['currentYear'] = $currentYear;

        return view('examiner.dashboard', $data);
    }
}
