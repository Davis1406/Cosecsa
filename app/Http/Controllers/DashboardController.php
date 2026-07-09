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

class DashboardController extends Controller
{
   

public function dashboard()
    {
        $data['header_title'] = 'Dashboard';
        
        $activeRole = Auth::user()->getActiveRole();
        $data['userRoles'] = Auth::user()->getRoles();
        $data['activeRole'] = $activeRole;

        switch ($activeRole) {
            case 1:
                // Admin dashboard logic
                $traineeCount = User::getTrainee()->count();
                $CandidateCount = User::getCandidates(date('Y'))->count();
                $FellowsCount = User::getFellows()->count();
                $accreditedHospitalCount = HospitalModel::where('status', 'active')->count();
                
                $data['traineeCount'] = $traineeCount;
                $data['accreditedHospitalCount'] = $accreditedHospitalCount;
                $data['CandidateCount'] = $CandidateCount;
                $data['FellowsCount'] = $FellowsCount;
                
                return view('admin.dashboard', $data);
                
            case 2:
                return view('trainee.dashboard', $data);
                
            case 7:
                $fellow = User::getFellows()->firstWhere('user_id', Auth::id());
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
     * Returns JSON: { trainees: [...], candidates: [...], examiners: [...], fellows: [...] }
     */
    public function globalSearch(Request $request)
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 2) {
            return response()->json(['trainees' => [], 'candidates' => [], 'examiners' => [], 'fellows' => []]);
        }

        $like = '%' . $q . '%';

        // Trainees
        $trainees = DB::table('trainees as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->join('user_roles as ur', function ($j) {
                $j->on('ur.user_id', '=', 'u.id')->where('ur.is_active', 1);
            })
            ->leftJoin('programmes as p', 'p.id', '=', 't.programme_id')
            ->where('u.user_type', 2)
            ->where('u.is_deleted', 0)
            // Exclude anyone already promoted to Fellow — their profile lives
            // under Fellows now, and the trainee record may be orphaned.
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('fellows')
                    ->whereColumn('fellows.candidate_number', 't.entry_number')
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

        return response()->json(compact('trainees', 'candidates', 'examiners', 'fellows'));
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
