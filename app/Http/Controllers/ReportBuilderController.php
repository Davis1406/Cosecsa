<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportBuilderExport;

class ReportBuilderController extends Controller
{
    public function index()
    {
        $data['types'] = config('reports.types');
        $data['header_title'] = 'College Reports';
        return view('admin.reports.index', $data);
    }

    // AJAX: fields + filters for the chosen report type.
    public function fields($type)
    {
        $config = config("reports.types.{$type}");
        if (! $config) {
            return response()->json(['error' => 'Unknown report type'], 404);
        }

        $filters = $config['filters'];
        foreach ($filters as $key => &$filter) {
            $filter['options'] = $this->filterOptions($filter['source']);
        }
        unset($filter);

        return response()->json([
            'fields'   => $config['fields'],
            'filters'  => $filters,
            'group_by' => $config['group_by'],
        ]);
    }

    public function generate(Request $request)
    {
        $type = $request->input('type');
        $config = config("reports.types.{$type}");
        if (! $config) {
            return back()->with('error', 'Unknown report type');
        }

        $selectedFields = array_values(array_intersect(
            $request->input('fields', []),
            array_keys($config['fields'])
        ));
        if (empty($selectedFields)) {
            return back()->with('error', 'Pick at least one field to build a report.');
        }

        $groupBy = $request->input('group_by');
        if ($groupBy && ! in_array($groupBy, $config['group_by'], true)) {
            $groupBy = null;
        }

        $rows = $this->runQuery($type, $selectedFields, $request->input('filters', []))->get();

        $chart = null;
        if ($groupBy && in_array($groupBy, $selectedFields, true)) {
            $chart = $rows->countBy(fn ($r) => $r->{$groupBy} ?: 'Unspecified')
                ->sortDesc()
                ->take(15);
        }

        return view('admin.reports.result', [
            'header_title'   => 'College Reports',
            'type'           => $type,
            'typeLabel'      => $config['label'],
            'fields'         => $config['fields'],
            'selectedFields' => $selectedFields,
            'rows'           => $rows,
            'groupBy'        => $groupBy,
            'chart'          => $chart,
            'filters'        => $request->input('filters', []),
        ]);
    }

    public function export(Request $request)
    {
        $type = $request->input('type');
        $config = config("reports.types.{$type}");
        if (! $config) {
            return back()->with('error', 'Unknown report type');
        }

        $selectedFields = array_values(array_intersect(
            explode(',', (string) $request->input('fields')),
            array_keys($config['fields'])
        ));
        if (empty($selectedFields)) {
            return back()->with('error', 'Pick at least one field to export.');
        }

        $filters = $request->input('filters', []);
        $rows = $this->runQuery($type, $selectedFields, $filters)->get();

        $headings = array_map(fn ($f) => $config['fields'][$f], $selectedFields);
        $data = $rows->map(fn ($row) => collect($selectedFields)->map(fn ($f) => $row->{$f})->all())->all();

        $filename = 'college-report-' . $type . '-' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new ReportBuilderExport($headings, $data), $filename);
    }

    // ── Query building ───────────────────────────────────────────────────

    protected function runQuery(string $type, array $selectedFields, array $filters)
    {
        [$query, $sqlMap] = $this->baseQuery($type);

        $selects = [];
        foreach ($selectedFields as $field) {
            if (isset($sqlMap[$field])) {
                $selects[] = DB::raw("{$sqlMap[$field]} as `{$field}`");
            }
        }
        $query->select($selects);

        $config = config("reports.types.{$type}");
        foreach ($filters as $key => $value) {
            if ($value === null || $value === '') continue;
            if (! isset($config['filters'][$key])) continue;
            $query->where($this->filterColumn($type, $key), $value);
        }

        return $query;
    }

    protected function baseQuery(string $type): array
    {
        switch ($type) {
            case 'trainees':
                return [
                    DB::table('trainees as t')
                        ->leftJoin('countries as c', 'c.id', '=', 't.country_id')
                        ->leftJoin('programmes as p', 'p.id', '=', 't.programme_id')
                        ->leftJoin('hospitals as h', 'h.id', '=', 't.hospital_id')
                        ->leftJoin('study_year as sy', 'sy.id', '=', 't.training_year'),
                    [
                        'name'               => "TRIM(CONCAT(t.firstname,' ',IFNULL(t.middlename,''),' ',t.lastname))",
                        'entry_number'       => 't.entry_number',
                        'gender'             => 't.gender',
                        'country_name'       => 'c.country_name',
                        'programme_name'     => 'p.name',
                        'hospital_name'      => 'h.name',
                        'training_year_name' => 'sy.name',
                        'admission_year'     => 't.admission_year',
                        'exam_year'          => 't.exam_year',
                        'status'             => 't.status',
                        'fee_paid'           => 't.fee_paid',
                        'invoice_status'     => 't.invoice_status',
                    ],
                ];
            case 'candidates':
                return [
                    DB::table('candidates as c')
                        ->leftJoin('countries as co', 'co.id', '=', 'c.country_id')
                        ->leftJoin('programmes as p', 'p.id', '=', 'c.programme_id')
                        ->leftJoin('hospitals as h', 'h.id', '=', 'c.hospital_id'),
                    [
                        'name'           => "TRIM(CONCAT(c.firstname,' ',IFNULL(c.middlename,''),' ',c.lastname))",
                        'entry_number'   => 'c.entry_number',
                        'gender'         => 'c.gender',
                        'country_name'   => 'co.country_name',
                        'programme_name' => 'p.name',
                        'hospital_name'  => 'h.name',
                        'exam_year'      => 'c.exam_year',
                        'admission_year' => 'c.admission_year',
                        'invoice_status' => 'c.invoice_status',
                        'fee_paid'       => 'c.fee_paid',
                        'amount_paid'    => 'c.amount_paid',
                    ],
                ];
            case 'fellows':
                return [
                    DB::table('fellows as f')
                        ->leftJoin('countries as co', 'co.id', '=', 'f.country_id')
                        ->leftJoin('programmes as p', 'p.id', '=', 'f.programme_id')
                        ->leftJoin('categories as cat', 'cat.id', '=', 'f.category_id'),
                    [
                        'name'              => "TRIM(CONCAT(f.firstname,' ',IFNULL(f.middlename,''),' ',f.lastname))",
                        'candidate_number'  => 'f.candidate_number',
                        'gender'            => 'f.gender',
                        'country_name'      => 'co.country_name',
                        'programme_name'    => 'p.name',
                        'category_name'     => 'cat.category_name',
                        'current_specialty' => 'f.current_specialty',
                        'admission_year'    => 'f.admission_year',
                        'fellowship_year'   => 'f.fellowship_year',
                        'status'            => 'f.status',
                        'cosecsa_region'    => 'f.cosecsa_region',
                        'is_alumni'         => "IF(f.is_alumni=1,'Yes','No')",
                    ],
                ];
            case 'members':
                return [
                    DB::table('members as m')
                        ->leftJoin('countries as co', 'co.id', '=', 'm.country_id')
                        ->leftJoin('categories as cat', 'cat.id', '=', 'm.category_id'),
                    [
                        'name'            => "TRIM(CONCAT(m.firstname,' ',IFNULL(m.middlename,''),' ',m.lastname))",
                        'gender'          => 'm.gender',
                        'country_name'    => 'co.country_name',
                        'category_name'   => 'cat.category_name',
                        'membership_year' => 'm.membership_year',
                        'admission_year'  => 'm.admission_year',
                        'status'          => 'm.status',
                    ],
                ];
            case 'trainers':
                return [
                    DB::table('trainers as t')
                        ->leftJoin('users as u', 'u.id', '=', 't.user_id')
                        ->leftJoin('hospitals as h', 'h.id', '=', 't.hospital_id'),
                    [
                        'name'            => 'u.name',
                        'hospital_name'   => 'h.name',
                        'phone_number'    => 't.phone_number',
                        'assistant_pd'    => 't.assistant_pd',
                        'assistant_email' => 't.assistant_email',
                    ],
                ];
            case 'country_reps':
                return [
                    DB::table('country_reps as cr')
                        ->leftJoin('users as u', 'u.id', '=', 'cr.user_id')
                        ->leftJoin('countries as co', 'co.id', '=', 'cr.country_id'),
                    [
                        'name'          => 'u.name',
                        'country_name'  => 'co.country_name',
                        'position'      => 'cr.position',
                        'mobile_no'     => 'cr.mobile_no',
                        'cosecsa_email' => 'cr.cosecsa_email',
                    ],
                ];
            case 'examiners':
                return [
                    DB::table('examiners as e')
                        ->leftJoin('users as u', 'u.id', '=', 'e.user_id')
                        ->leftJoin('countries as co', 'co.id', '=', 'e.country_id'),
                    [
                        'name'                 => 'u.name',
                        'examiner_id'          => 'e.examiner_id',
                        'gender'               => 'e.gender',
                        'country_name'         => 'co.country_name',
                        'specialty'            => 'e.specialty',
                        'subspecialty'         => 'e.subspecialty',
                        'status'               => 'e.status',
                        'examiner_designation' => 'e.examiner_designation',
                    ],
                ];
            default:
                abort(404);
        }
    }

    // Raw filterable column per type+key, used for ->where(). Kept separate
    // from the SELECT sql map since filters always compare the raw id/enum
    // column (e.g. t.country_id), not necessarily a field the user selected.
    protected function filterColumn(string $type, string $key): string
    {
        $map = [
            'trainees'     => ['country_id' => 't.country_id', 'programme_id' => 't.programme_id', 'gender' => 't.gender'],
            'candidates'   => ['country_id' => 'c.country_id', 'programme_id' => 'c.programme_id', 'gender' => 'c.gender'],
            'fellows'      => ['country_id' => 'f.country_id', 'category_id' => 'f.category_id', 'gender' => 'f.gender', 'status' => 'f.status'],
            'members'      => ['country_id' => 'm.country_id', 'category_id' => 'm.category_id', 'gender' => 'm.gender', 'status' => 'm.status'],
            'country_reps' => ['country_id' => 'cr.country_id'],
            'examiners'    => ['country_id' => 'e.country_id', 'gender' => 'e.gender', 'status' => 'e.status'],
        ];

        return $map[$type][$key] ?? $key;
    }

    protected function filterOptions(string $source): array
    {
        switch ($source) {
            case 'countries':
                return DB::table('countries')->orderBy('country_name')->pluck('country_name', 'id')->all();
            case 'programmes':
                return DB::table('programmes')->orderBy('name')->pluck('name', 'id')->all();
            case 'categories':
                return DB::table('categories')->orderBy('category_name')->pluck('category_name', 'id')->all();
            case 'gender':
                return ['Male' => 'Male', 'Female' => 'Female'];
            case 'status':
                return ['Active' => 'Active', 'Inactive' => 'Inactive'];
            default:
                return [];
        }
    }
}
