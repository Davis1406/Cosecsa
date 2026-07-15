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

        // ── Generic fee_payments log ──
        $feePayments = DB::table('fee_payments')
            ->select([
                DB::raw("CONCAT('fee-', id) as row_key"),
                'fee_group', 'fee_name', 'payer_type', 'payer_id', 'payer_name',
                'amount_due', 'amount_paid', 'status', 'date_paid', 'mode_of_payment',
                'reference_number', 'notes', 'created_at',
            ])
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
            ->get();

        $log = $feePayments->concat($subscriptionPayments);

        if ($search) {
            $log = $log->filter(fn ($r) => str_contains(strtolower($r->payer_name), strtolower($search)));
        }
        if ($group) {
            $log = $log->filter(fn ($r) => $r->fee_group === $group);
        }
        if ($payerType) {
            $log = $log->filter(fn ($r) => $r->payer_type === $payerType);
        }
        if ($status) {
            $log = $log->filter(fn ($r) => $r->status === $status);
        }

        $log = $log->sortByDesc('created_at')->values();

        $totalCollected = $log->sum('amount_paid');
        $totalDue       = $log->sum(fn ($r) => max(0, $r->amount_due - $r->amount_paid));
        $paidCount      = $log->where('status', 'Paid')->count();

        return view('admin.fees.index', compact(
            'header_title', 'feeTypes', 'log', 'search', 'group', 'payerType', 'status',
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
            ->select('f.id', 'u.name', 'f.candidate_number', 'cat.name as category_name')
            ->limit(8)->get()
            ->map(fn ($r) => [
                'type' => 'fellow', 'id' => $r->id, 'name' => $r->name,
                'subtitle' => implode(' · ', array_filter(['Fellow', $r->category_name, $r->candidate_number])),
                'category_name' => $r->category_name,
            ]);

        $trainees = DB::table('trainees as t')
            ->join('users as u', 'u.id', '=', 't.user_id')
            ->where(function ($w) use ($like) {
                $w->where('u.name', 'like', $like)
                  ->orWhere('t.personal_email', 'like', $like)
                  ->orWhere('t.entry_number', 'like', $like);
            })
            ->select('t.id', 'u.name', 't.entry_number')
            ->limit(8)->get()
            ->map(fn ($r) => [
                'type' => 'trainee', 'id' => $r->id, 'name' => $r->name,
                'subtitle' => implode(' · ', array_filter(['Trainee', $r->entry_number])),
                'category_name' => null,
            ]);

        $candidates = DB::table('candidates as c')
            ->join('users as u', 'u.id', '=', 'c.user_id')
            ->where(function ($w) use ($like) {
                $w->where('u.name', 'like', $like)
                  ->orWhere('c.personal_email', 'like', $like)
                  ->orWhere('c.entry_number', 'like', $like);
            })
            ->select('c.id', 'u.name', 'c.entry_number')
            ->limit(8)->get()
            ->map(fn ($r) => [
                'type' => 'candidate', 'id' => $r->id, 'name' => $r->name,
                'subtitle' => implode(' · ', array_filter(['Candidate', $r->entry_number])),
                'category_name' => null,
            ]);

        return response()->json($fellows->concat($trainees)->concat($candidates)->values());
    }

    // ── Record / edit / delete a payment ────────────────────────────────────

    public function recordPayment(Request $request)
    {
        $request->validate([
            'fee_type_id' => 'required|exists:fee_types,id',
            'payer_type'  => 'required|in:fellow,trainee,candidate',
            'payer_id'    => 'required|integer',
            'payer_name'  => 'required|string|max:255',
            'amount_paid' => 'required|numeric|min:0',
            'status'      => 'required|in:Paid,Unpaid,Partial',
        ]);

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
     * $rowKey is "fee-{id}" or "sub-{id}" — routes the edit to the right table.
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
        } else {
            DB::table('fee_payments')->where('id', $id)->delete();
        }

        return redirect('admin/fees')->with('success', 'Payment record deleted.');
    }
}
