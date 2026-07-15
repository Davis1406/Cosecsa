<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class FeesController extends Controller
{
    /**
     * Fee catalog + unified payments log (fee_payments plus fellow_subscriptions,
     * since Annual Subscription payments for fellows live in the existing table).
     */
    public function index(Request $request)
    {
        $header_title = 'Fees';

        $feeTypes = DB::table('fee_types')->orderBy('fee_group')->orderBy('name')->get()->groupBy('fee_group');

        $search    = trim((string) $request->input('q'));
        $group     = $request->input('group');
        $payerType = $request->input('payer_type');
        $status    = $request->input('status');
        // Years of subscription history go back to 2015 — default to the
        // current year so the page doesn't try to render 3,000+ rows at once.
        $year      = $request->input('year', (string) date('Y'));

        // ── Generic fee_payments log ──
        $feePayments = DB::table('fee_payments')
            ->select([
                DB::raw("CONCAT('fee-', id) as row_key"),
                'fee_group', 'fee_name', 'payer_type', 'payer_id', 'payer_name',
                'amount_due', 'amount_paid', 'status', 'date_paid', 'mode_of_payment',
                'reference_number', 'notes', 'created_at',
            ])
            ->when($search, fn ($q) => $q->where('payer_name', 'like', "%{$search}%"))
            ->when($group, fn ($q) => $q->where('fee_group', $group))
            ->when($payerType, fn ($q) => $q->where('payer_type', $payerType))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->when($year && $year !== 'all', fn ($q) => $q->whereYear('created_at', $year))
            ->get();

        // ── Annual Subscription log, sourced from fellow_subscriptions ──
        $subscriptionPayments = DB::table('fellow_subscriptions as fs')
            ->join('fellows as f', 'f.id', '=', 'fs.fellow_id')
            ->join('users as u', 'u.id', '=', 'f.user_id')
            ->select([
                DB::raw("CONCAT('sub-', fs.id) as row_key"),
                DB::raw("'Annual Subscription' as fee_group"),
                DB::raw("CONCAT('Annual Subscription - ', fs.year) as fee_name"),
                DB::raw("'fellow' as payer_type"),
                'fs.fellow_id as payer_id',
                'u.name as payer_name',
                'fs.amount_due', 'fs.amount_paid', 'fs.status',
                'fs.date_paid', 'fs.mode_of_payment',
                DB::raw('NULL as reference_number'),
                DB::raw('NULL as notes'),
                'fs.created_at',
            ])
            ->when($search, fn ($q) => $q->where('u.name', 'like', "%{$search}%"))
            ->when($group, fn ($q) => $q->where(DB::raw('1'), $group === 'Annual Subscription' ? 1 : 0))
            ->when($payerType, fn ($q) => $q->where(DB::raw('1'), $payerType === 'fellow' ? 1 : 0))
            ->when($status, fn ($q) => $q->where('fs.status', $status))
            ->when($year && $year !== 'all', fn ($q) => $q->where('fs.year', $year))
            ->get();

        // ── Programme Entry Fee log, sourced from trainees ──
        $traineeFeePayments = DB::table('trainees as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->leftJoin('programmes as p', 'p.id', '=', 't.programme_id')
            ->where(fn ($w) => $w->where('t.amount_paid', '>', 0)->orWhere('t.fee_paid', 'Yes'))
            ->select([
                DB::raw("CONCAT('tfee-', t.id) as row_key"),
                DB::raw("'Programme Fees' as fee_group"),
                DB::raw("CONCAT('Entry Fee - ', COALESCE(p.name, 'Unknown Programme')) as fee_name"),
                DB::raw("'trainee' as payer_type"),
                't.id as payer_id',
                'u.name as payer_name',
                DB::raw('COALESCE(NULLIF(t.invoice_amount, 0), p.entry_fee) as amount_due'),
                't.amount_paid',
                DB::raw("(CASE WHEN t.fee_paid = 'Yes' OR (t.amount_paid > 0 AND t.amount_paid >= COALESCE(NULLIF(t.invoice_amount, 0), p.entry_fee)) THEN 'Paid' WHEN t.amount_paid > 0 THEN 'Partial' ELSE 'Unpaid' END) as status"),
                't.payment_date as date_paid', 't.mode_of_payment',
                't.invoice_number as reference_number',
                DB::raw('NULL as notes'),
                't.updated_at as created_at',
            ])
            ->when($search, fn ($q) => $q->where('u.name', 'like', "%{$search}%"))
            ->when($group, fn ($q) => $q->where(DB::raw('1'), $group === 'Programme Fees' ? 1 : 0))
            ->when($payerType, fn ($q) => $q->where(DB::raw('1'), $payerType === 'trainee' ? 1 : 0))
            ->when($year && $year !== 'all', fn ($q) => $q->whereYear('t.updated_at', $year))
            ->get();

        // ── Exam Fee log, sourced from candidates ──
        $candidateFeePayments = DB::table('candidates as c')
            ->join('users as u', 'u.id', '=', 'c.user_id')
            ->leftJoin('programmes as p', 'p.id', '=', 'c.programme_id')
            ->where(fn ($w) => $w->where('c.amount_paid', '>', 0)->orWhere('c.fee_paid', 'Yes'))
            ->select([
                DB::raw("CONCAT('cfee-', c.id) as row_key"),
                DB::raw("'Programme Fees' as fee_group"),
                DB::raw("CONCAT('Exam Fee - ', COALESCE(p.name, 'Unknown Programme')) as fee_name"),
                DB::raw("'candidate' as payer_type"),
                'c.id as payer_id',
                'u.name as payer_name',
                DB::raw('COALESCE(NULLIF(c.invoice_amount, 0), p.exam_fee) as amount_due'),
                'c.amount_paid',
                DB::raw("(CASE WHEN c.fee_paid = 'Yes' OR (c.amount_paid > 0 AND c.amount_paid >= COALESCE(NULLIF(c.invoice_amount, 0), p.exam_fee)) THEN 'Paid' WHEN c.amount_paid > 0 THEN 'Partial' ELSE 'Unpaid' END) as status"),
                'c.payment_date as date_paid', 'c.mode_of_payment',
                'c.invoice_number as reference_number',
                DB::raw('NULL as notes'),
                'c.updated_at as created_at',
            ])
            ->when($search, fn ($q) => $q->where('u.name', 'like', "%{$search}%"))
            ->when($group, fn ($q) => $q->where(DB::raw('1'), $group === 'Programme Fees' ? 1 : 0))
            ->when($payerType, fn ($q) => $q->where(DB::raw('1'), $payerType === 'candidate' ? 1 : 0))
            ->when($year && $year !== 'all', fn ($q) => $q->whereYear('c.updated_at', $year))
            ->get();

        // ── Programme Entry Fee invoices straight from Salesforce, for
        //    applicants who haven't produced a trainee record yet (their
        //    application hasn't reached "Complete" stage / Populate Trainees
        //    hasn't run for them) — otherwise their invoice data is invisible
        //    even though it's already synced. Once a trainee_id is linked,
        //    the row above (sourced from trainees) takes over instead. ──
        $pendingApplicationFees = DB::table('salesforce_applications')
            ->whereNotNull('entry_invoice_number')
            ->whereNull('trainee_id')
            // Skip applications whose applicant already has a trainee record
            // for the SAME programme (matched by email + programme name) —
            // that specific entry fee is already represented via the
            // trainees-sourced row above. Matching on email alone would
            // wrongly hide a genuinely separate enrollment in a different
            // programme (e.g. General Surgery already a trainee, now also
            // applying for Plastic Surgery).
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('trainees')
                    ->join('programmes', 'programmes.id', '=', 'trainees.programme_id')
                    ->whereRaw('trainees.personal_email COLLATE utf8mb4_unicode_ci = salesforce_applications.applicant_email COLLATE utf8mb4_unicode_ci')
                    ->whereRaw('programmes.name COLLATE utf8mb4_unicode_ci = salesforce_applications.programme_name COLLATE utf8mb4_unicode_ci')
                    ->whereNotNull('salesforce_applications.applicant_email');
            })
            ->select([
                DB::raw("CONCAT('appfee-', id) as row_key"),
                DB::raw("'Programme Fees' as fee_group"),
                DB::raw("CONCAT('Entry Fee - ', COALESCE(programme_name, 'Unknown Programme'), ' (Salesforce)') as fee_name"),
                DB::raw("'applicant' as payer_type"),
                'id as payer_id',
                'applicant_name as payer_name',
                DB::raw('entry_invoice_amount as amount_due'),
                DB::raw('COALESCE(entry_payment_amount, 0) as amount_paid'),
                DB::raw("(CASE WHEN LOWER(entry_invoice_status) = 'paid' THEN 'Paid' WHEN COALESCE(entry_payment_amount,0) > 0 THEN 'Partial' ELSE 'Unpaid' END) as status"),
                'entry_payment_date as date_paid', 'entry_payment_method as mode_of_payment',
                'entry_invoice_number as reference_number',
                DB::raw('NULL as notes'),
                'synced_at as created_at',
            ])
            ->when($search, fn ($q) => $q->where('applicant_name', 'like', "%{$search}%"))
            ->when($group, fn ($q) => $q->where(DB::raw('1'), $group === 'Programme Fees' ? 1 : 0))
            ->when($payerType, fn ($q) => $q->where(DB::raw('1'), $payerType === 'applicant' ? 1 : 0))
            ->when($year && $year !== 'all', fn ($q) => $q->whereRaw('YEAR(COALESCE(entry_payment_date, date_of_application)) = ?', [$year]))
            ->get();

        $log = $feePayments->concat($subscriptionPayments)
            ->concat($traineeFeePayments)->concat($candidateFeePayments)->concat($pendingApplicationFees)
            ->sortByDesc('created_at')->values();

        $totalCollected = $log->sum(fn ($r) => $r->amount_paid ?? 0);
        $totalDue       = $log->sum(fn ($r) => max(0, ($r->amount_due ?? 0) - ($r->amount_paid ?? 0)));
        $paidCount      = $log->where('status', 'Paid')->count();

        $years = DB::table('fellow_subscriptions')->selectRaw('DISTINCT year')->orderByDesc('year')->pluck('year');

        $programmes = DB::table('programmes')->where('is_deleted', 0)->orderBy('name')->get();

        return view('admin.fees.index', compact(
            'header_title', 'feeTypes', 'programmes', 'log', 'search', 'group', 'payerType', 'status', 'year', 'years',
            'totalCollected', 'totalDue', 'paidCount'
        ));
    }

    // ── Fee type catalog CRUD ──────────────────────────────────────────────

    public function storeFeeType(Request $request)
    {
        $request->validate([
            'fee_group' => 'required|string|max:100',
            'name'      => 'required|string|max:150',
            'amount'    => 'required|numeric|min:0',
            'currency'  => 'nullable|string|max:10',
        ]);

        DB::table('fee_types')->insert([
            'fee_group'                => $request->fee_group,
            'name'                     => $request->name,
            'amount'                   => $request->amount,
            'currency'                 => $request->currency ?: 'USD',
            'applies_to_subscription'  => $request->boolean('applies_to_subscription'),
            'is_active'                => 1,
            'created_at'               => now(),
            'updated_at'               => now(),
        ]);

        return redirect('admin/fees')->with('success', 'Fee type "' . $request->name . '" added.');
    }

    public function updateFeeType(Request $request, $id)
    {
        $request->validate([
            'fee_group' => 'required|string|max:100',
            'name'      => 'required|string|max:150',
            'amount'    => 'required|numeric|min:0',
            'currency'  => 'nullable|string|max:10',
        ]);

        DB::table('fee_types')->where('id', $id)->update([
            'fee_group'                => $request->fee_group,
            'name'                     => $request->name,
            'amount'                   => $request->amount,
            'currency'                 => $request->currency ?: 'USD',
            'applies_to_subscription'  => $request->boolean('applies_to_subscription'),
            'is_active'                => $request->boolean('is_active'),
            'updated_at'               => now(),
        ]);

        return redirect('admin/fees')->with('success', 'Fee type updated.');
    }

    public function destroyFeeType($id)
    {
        DB::table('fee_types')->where('id', $id)->delete();
        return redirect('admin/fees')->with('success', 'Fee type deleted.');
    }

    // ── Payer search (AJAX) ─────────────────────────────────────────────────

    public function searchPayer(Request $request)
    {
        $q = trim((string) $request->input('q'));
        if (strlen($q) < 2) {
            return response()->json([]);
        }
        $like = "%{$q}%";

        $fellows = DB::table('fellows as f')
            ->join('users as u', 'u.id', '=', 'f.user_id')
            ->join('categories as cat', 'cat.id', '=', 'f.category_id')
            ->where('u.is_deleted', 0)
            ->where(function ($w) use ($like) {
                $w->where('u.name', 'like', $like)
                  ->orWhere('f.personal_email', 'like', $like)
                  ->orWhere('f.candidate_number', 'like', $like);
            })
            ->select('f.id', 'u.name', 'f.candidate_number', 'cat.category_name as category_name')
            ->limit(8)->get()
            ->map(fn ($r) => [
                'type' => 'fellow', 'id' => $r->id, 'name' => $r->name,
                'subtitle' => implode(' · ', array_filter(['Fellow', $r->category_name, $r->candidate_number])),
                'category_name' => $r->category_name,
            ]);

        $trainees = DB::table('trainees as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->leftJoin('programmes as p', 'p.id', '=', 't.programme_id')
            ->where(function ($w) use ($like) {
                $w->where('u.name', 'like', $like)
                  ->orWhere('t.personal_email', 'like', $like)
                  ->orWhere('t.entry_number', 'like', $like);
            })
            ->select('t.id', 'u.name', 't.entry_number', 'p.name as programme_name', 'p.entry_fee')
            ->limit(8)->get()
            ->map(fn ($r) => [
                'type' => 'trainee', 'id' => $r->id, 'name' => $r->name,
                'subtitle' => implode(' · ', array_filter(['Trainee', $r->entry_number, $r->programme_name])),
                'category_name' => null,
                'programme_fee' => $r->programme_name ? [
                    'label'  => "Entry Fee - {$r->programme_name}",
                    'amount' => $r->entry_fee,
                ] : null,
            ]);

        $candidates = DB::table('candidates as c')
            ->join('users as u', 'u.id', '=', 'c.user_id')
            ->leftJoin('programmes as p', 'p.id', '=', 'c.programme_id')
            ->where(function ($w) use ($like) {
                $w->where('u.name', 'like', $like)
                  ->orWhere('c.personal_email', 'like', $like)
                  ->orWhere('c.entry_number', 'like', $like);
            })
            ->select('c.id', 'u.name', 'c.entry_number', 'p.name as programme_name',
                     'p.exam_fee', 'p.repeat_fee', 'c.repeat_paper_one', 'c.repeat_paper_two')
            ->limit(8)->get()
            ->map(fn ($r) => [
                'type' => 'candidate', 'id' => $r->id, 'name' => $r->name,
                'subtitle' => implode(' · ', array_filter(['Candidate', $r->entry_number, $r->programme_name])),
                'category_name' => null,
                'programme_fee' => $r->programme_name ? [
                    'label'  => "Exam Fee - {$r->programme_name}",
                    'amount' => ($r->repeat_paper_one === 'Yes' || $r->repeat_paper_two === 'Yes') ? $r->repeat_fee : $r->exam_fee,
                ] : null,
            ]);

        return response()->json($fellows->concat($trainees)->concat($candidates)->values());
    }

    // ── Record / edit / delete a payment ────────────────────────────────────

    public function recordPayment(Request $request)
    {
        $request->validate([
            'fee_type_id' => 'required|string',
            'payer_type'  => 'required|in:fellow,trainee,candidate',
            'payer_id'    => 'required|integer',
            'payer_name'  => 'required|string|max:255',
            'amount_paid' => 'required|numeric|min:0',
            'status'      => 'required|in:Paid,Unpaid,Partial',
        ]);

        // ── Programme Entry Fee (trainee) / Exam Fee (candidate) — these live
        //    on the trainees/candidates records themselves, managed under
        //    Programmes for the amounts, not the fee_types catalog. ──
        if ($request->fee_type_id === 'programme') {
            if (! in_array($request->payer_type, ['trainee', 'candidate'])) {
                return redirect('admin/fees')->with('error', 'Programme fees only apply to trainees or candidates.');
            }

            $table = $request->payer_type === 'trainee' ? 'trainees' : 'candidates';

            DB::table($table)->where('id', $request->payer_id)->update([
                'invoice_amount'  => $request->input('programme_fee_amount'),
                'amount_paid'     => $request->amount_paid,
                'fee_paid'        => $request->status === 'Paid' ? 'Yes' : 'No',
                'invoice_status'  => $request->status === 'Paid' ? 'Complete' : 'Sent',
                'payment_date'    => $request->date_paid ?: null,
                'mode_of_payment' => $request->mode_of_payment,
                'invoice_number'  => $request->reference_number,
                'updated_at'      => now(),
            ]);

            $label = $request->payer_type === 'trainee' ? 'Entry fee' : 'Exam fee';
            return redirect('admin/fees')->with('success', "{$label} recorded for {$request->payer_name}.");
        }

        $request->validate(['fee_type_id' => 'exists:fee_types,id']);

        $feeType = DB::table('fee_types')->find($request->fee_type_id);
        if (! $feeType) {
            return redirect('admin/fees')->with('error', 'Fee type not found.');
        }

        if ($feeType->applies_to_subscription && $request->payer_type === 'fellow') {
            $year = $request->input('year', date('Y'));

            DB::table('fellow_subscriptions')->updateOrInsert(
                ['fellow_id' => $request->payer_id, 'year' => $year],
                [
                    'status'          => $request->status,
                    'amount_due'      => $feeType->amount,
                    'amount_paid'     => $request->amount_paid,
                    'date_paid'       => $request->date_paid ?: null,
                    'mode_of_payment' => $request->mode_of_payment,
                    'updated_at'      => now(),
                    'created_at'      => now(),
                ]
            );

            return redirect('admin/fees')->with('success', "Annual subscription ({$year}) recorded for {$request->payer_name}.");
        }

        DB::table('fee_payments')->insert([
            'fee_type_id'      => $feeType->id,
            'fee_group'        => $feeType->fee_group,
            'fee_name'         => $feeType->name,
            'payer_type'       => $request->payer_type,
            'payer_id'         => $request->payer_id,
            'payer_name'       => $request->payer_name,
            'amount_due'       => $feeType->amount,
            'amount_paid'      => $request->amount_paid,
            'status'           => $request->status,
            'date_paid'        => $request->date_paid ?: null,
            'mode_of_payment'  => $request->mode_of_payment,
            'reference_number' => $request->reference_number,
            'notes'            => $request->notes,
            'recorded_by'      => Auth::id(),
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return redirect('admin/fees')->with('success', "{$feeType->name} payment recorded for {$request->payer_name}.");
    }

    /**
     * $rowKey is "fee-{id}", "sub-{id}", "tfee-{id}", or "cfee-{id}" — routes
     * the edit to the right table.
     */
    public function updatePayment(Request $request, $rowKey)
    {
        [$source, $id] = explode('-', $rowKey, 2);

        $request->validate([
            'amount_paid' => 'required|numeric|min:0',
            'status'      => 'required|in:Paid,Unpaid,Partial',
        ]);

        if ($source === 'sub') {
            DB::table('fellow_subscriptions')->where('id', $id)->update([
                'amount_paid'     => $request->amount_paid,
                'status'          => $request->status,
                'date_paid'       => $request->date_paid ?: null,
                'mode_of_payment' => $request->mode_of_payment,
                'updated_at'      => now(),
            ]);
        } elseif ($source === 'tfee' || $source === 'cfee') {
            DB::table($source === 'tfee' ? 'trainees' : 'candidates')->where('id', $id)->update([
                'amount_paid'     => $request->amount_paid,
                'fee_paid'        => $request->status === 'Paid' ? 'Yes' : 'No',
                'invoice_status'  => $request->status === 'Paid' ? 'Complete' : 'Sent',
                'payment_date'    => $request->date_paid ?: null,
                'mode_of_payment' => $request->mode_of_payment,
                'invoice_number'  => $request->reference_number,
                'updated_at'      => now(),
            ]);
        } else {
            DB::table('fee_payments')->where('id', $id)->update([
                'amount_paid'      => $request->amount_paid,
                'status'           => $request->status,
                'date_paid'        => $request->date_paid ?: null,
                'mode_of_payment'  => $request->mode_of_payment,
                'reference_number' => $request->reference_number,
                'notes'            => $request->notes,
                'updated_at'       => now(),
            ]);
        }

        return redirect('admin/fees')->with('success', 'Payment record updated.');
    }

    public function destroyPayment($rowKey)
    {
        [$source, $id] = explode('-', $rowKey, 2);

        if ($source === 'sub') {
            DB::table('fellow_subscriptions')->where('id', $id)->delete();
        } elseif ($source === 'tfee' || $source === 'cfee') {
            // "Deleting" a programme-fee log entry just clears the fee fields
            // on the trainee/candidate record — there's no separate row to remove.
            DB::table($source === 'tfee' ? 'trainees' : 'candidates')->where('id', $id)->update([
                'amount_paid'     => 0,
                'fee_paid'        => 'No',
                'invoice_status'  => 'Pending',
                'payment_date'    => null,
                'mode_of_payment' => null,
                'invoice_number'  => null,
                'updated_at'      => now(),
            ]);
        } else {
            DB::table('fee_payments')->where('id', $id)->delete();
        }

        return redirect('admin/fees')->with('success', 'Payment record deleted.');
    }
}
