<?php

namespace App\Http\Controllers;

use App\Models\TranscriptCourse;
use App\Models\TranscriptRecord;
use App\Models\TranscriptTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class TranscriptController extends Controller
{
    // Prefilled onto every new transcript (matches the college's standard
    // MCS + FCS course structure) — admin edits/adds/removes rows per
    // candidate from there; this is just a starting point, not a rule.
    public const DEFAULT_COURSES = [
        ['section' => 'MCS (Membership of college of surgeons)', 'subsection' => 'MCS', 'course_name' => 'MCS Case Studies', 'academic_year' => '', 'result' => 'Complete'],
        ['section' => 'MCS (Membership of college of surgeons)', 'subsection' => 'MCS', 'course_name' => 'Surgery In Africa Journal Club module', 'academic_year' => '', 'result' => 'Complete'],
        ['section' => 'MCS (Membership of college of surgeons)', 'subsection' => 'MCS', 'course_name' => 'Basic Surgical Science Course', 'academic_year' => '', 'result' => 'Complete'],
        ['section' => 'MCS (Membership of college of surgeons)', 'subsection' => 'MCS', 'course_name' => 'Basic Surgical Skill Course', 'academic_year' => '', 'result' => 'Complete'],
        ['section' => 'MCS (Membership of college of surgeons)', 'subsection' => 'MCS', 'course_name' => 'Operations Logbook', 'academic_year' => '', 'result' => 'Complete'],
        ['section' => 'MCS (Membership of college of surgeons)', 'subsection' => 'MCS', 'course_name' => 'MCS Exam Part I', 'academic_year' => '', 'result' => 'Pass'],
        ['section' => 'MCS (Membership of college of surgeons)', 'subsection' => 'MCS', 'course_name' => 'MCS Exam Part II', 'academic_year' => '', 'result' => 'Pass'],
        ['section' => 'FCS (Fellowship of College of Surgeons)', 'subsection' => 'FCS', 'course_name' => 'FCS Case Studies', 'academic_year' => '', 'result' => 'Complete'],
        ['section' => 'FCS (Fellowship of College of Surgeons)', 'subsection' => 'FCS', 'course_name' => 'Operations Logbook', 'academic_year' => '', 'result' => 'Complete'],
        ['section' => 'FCS (Fellowship of College of Surgeons)', 'subsection' => 'FCS', 'course_name' => 'FCS Exam Part I', 'academic_year' => '', 'result' => 'Pass'],
        ['section' => 'FCS (Fellowship of College of Surgeons)', 'subsection' => 'FCS', 'course_name' => 'FCS Exam Part II', 'academic_year' => '', 'result' => 'Pass'],
    ];

    public function search(Request $request)
    {
        $q = trim((string) $request->input('q'));
        $results = [];

        if ($q) {
            $like = "%{$q}%";

            $fellows = DB::table('fellows')
                ->where(function ($w) use ($like) {
                    $w->where('firstname', 'like', $like)
                      ->orWhere('lastname', 'like', $like)
                      ->orWhere('candidate_number', 'like', $like);
                })
                ->select('user_id', DB::raw("TRIM(CONCAT(firstname,' ',lastname)) as name"), 'candidate_number as ref', DB::raw("'Fellow' as source"))
                ->limit(20)->get();

            $trainees = DB::table('trainees')
                ->where(function ($w) use ($like) {
                    $w->where('firstname', 'like', $like)
                      ->orWhere('lastname', 'like', $like)
                      ->orWhere('entry_number', 'like', $like);
                })
                ->select('user_id', DB::raw("TRIM(CONCAT(firstname,' ',lastname)) as name"), 'entry_number as ref', DB::raw("'Trainee' as source"))
                ->limit(20)->get();

            $results = $fellows->merge($trainees)->unique('user_id')->values();
        }

        return view('admin.transcripts.search', [
            'header_title' => 'Transcripts',
            'q'            => $q,
            'results'      => $results,
        ]);
    }

    public function edit($userId)
    {
        $record = TranscriptRecord::where('user_id', $userId)->first();

        if (! $record) {
            $record = $this->prefillFromExistingRecord($userId);
        }

        $courses = $record->exists ? $record->courses : collect();
        if ($courses->isEmpty()) {
            $courses = collect(self::DEFAULT_COURSES)->map(fn ($c) => (object) $c);
        }

        $data['header_title'] = 'Issue Transcript';
        $data['record'] = $record;
        $data['courses'] = $courses;
        $data['templates'] = TranscriptTemplate::orderBy('name')->get();
        $data['userId'] = $userId;

        return view('admin.transcripts.edit', $data);
    }

    public function save(Request $request, $userId)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $record = TranscriptRecord::updateOrCreate(
                ['user_id' => $userId],
                [
                    'template_id'            => $request->template_id ?: null,
                    'full_name'              => $request->full_name,
                    'gender'                 => $request->gender,
                    'programme_entry_number' => $request->programme_entry_number,
                    'medium_of_instruction'  => $request->medium_of_instruction ?: 'English',
                    'programme'              => $request->programme,
                    'entry_period'           => $request->entry_period,
                    'completion_period'      => $request->completion_period,
                    'final_score'            => $request->final_score,
                    'created_by'             => $record?->created_by ?? Auth::id(),
                ]
            );

            TranscriptCourse::where('transcript_record_id', $record->id)->delete();

            $rows = $request->input('courses', []);
            foreach ($rows as $i => $row) {
                if (empty($row['course_name'])) continue;
                TranscriptCourse::create([
                    'transcript_record_id' => $record->id,
                    'section'              => $row['section']    ?? null,
                    'subsection'           => $row['subsection'] ?? null,
                    'course_name'          => $row['course_name'],
                    'academic_year'        => $row['academic_year'] ?? null,
                    'result'               => $row['result']        ?? null,
                    'sort_order'           => $i,
                ]);
            }

            DB::commit();
            return redirect("admin/transcripts/edit/{$userId}")->with('success', 'Transcript details saved.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error saving transcript: ' . $e->getMessage());
        }
    }

    public function pdf($userId)
    {
        $record = TranscriptRecord::with(['courses', 'template'])->where('user_id', $userId)->firstOrFail();
        $template = $record->template
            ?? TranscriptTemplate::where('is_default', true)->first()
            ?? new TranscriptTemplate(['document_title' => 'TRANSCRIPT OF TRAINING', 'closing_salutation' => 'Yours Sincerely,']);

        $grouped = $record->courses->groupBy(fn ($c) => $c->section . '|' . $c->subsection);

        $pdf = Pdf::loadView('admin.transcripts.pdf', [
            'record'   => $record,
            'template' => $template,
            'grouped'  => $grouped,
        ])->setPaper('a4');

        $filename = $record->full_name . '- COSECSA Transcript.pdf';
        $response = $pdf->stream($filename);

        // PHP's session cache-limiter defaults to "no-store", which some
        // browsers refuse to render inline (the PDF just fails to open even
        // though the bytes are a valid file) — override with a permissive
        // but still private/no-cache-shared value.
        $response->headers->set('Cache-Control', 'private, max-age=0, must-revalidate');
        $response->headers->remove('Pragma');

        return $response;
    }

    protected function prefillFromExistingRecord($userId): TranscriptRecord
    {
        $fellow = DB::table('fellows')
            ->leftJoin('programmes', 'programmes.id', '=', 'fellows.programme_id')
            ->where('fellows.user_id', $userId)
            ->select('fellows.*', 'programmes.name as programme_name')
            ->first();

        if ($fellow) {
            return new TranscriptRecord([
                'user_id'                => $userId,
                'full_name'              => trim($fellow->firstname . ' ' . $fellow->lastname),
                'gender'                 => $fellow->gender,
                'programme_entry_number' => $fellow->candidate_number,
                'programme'              => $fellow->programme_name,
                'entry_period'           => $fellow->admission_year,
                'completion_period'      => $fellow->fellowship_year,
            ]);
        }

        $trainee = DB::table('trainees')
            ->leftJoin('programmes', 'programmes.id', '=', 'trainees.programme_id')
            ->where('trainees.user_id', $userId)
            ->select('trainees.*', 'programmes.name as programme_name')
            ->first();

        if ($trainee) {
            return new TranscriptRecord([
                'user_id'                => $userId,
                'full_name'              => trim($trainee->firstname . ' ' . $trainee->lastname),
                'gender'                 => $trainee->gender,
                'programme_entry_number' => $trainee->entry_number,
                'programme'              => $trainee->programme_name,
                'entry_period'           => $trainee->admission_year,
            ]);
        }

        $user = DB::table('users')->find($userId);
        return new TranscriptRecord([
            'user_id'   => $userId,
            'full_name' => $user->name ?? '',
        ]);
    }
}
