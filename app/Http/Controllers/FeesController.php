<?php

namespace App\Http\Controllers;

use App\Exports\SubscriptionReportExport;
use App\Services\ApiClient;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class FeesController extends Controller
{
    public function __construct(private ApiClient $api) {}

    public function catalogue(Request $request)
    {
        $response = $this->api->get('fees/catalogue');
        $data = $response->object();

        // fee_types is grouped → stdClass { "Group": [rows...] } → rebuild as
        // Collection of Collections so Blade can iterate and call count() etc.
        $feeTypes = collect((array) ($data->fee_types ?? []))
            ->map(fn ($fees) => collect($fees));

        return view('admin.fees.catalogue', [
            'header_title' => 'Fee Catalogues',
            'feeTypes'     => $feeTypes,
            'programmes'   => collect($data->programmes ?? []),
        ]);
    }

    public function manage(Request $request)
    {
        $response = $this->api->get('fees/', $request->only(['q', 'group', 'payer_type', 'status', 'year']));
        $data = $response->object();

        $feeTypes = collect((array) ($data->fee_types ?? []))
            ->map(fn ($fees) => collect($fees));

        $filters = (array) ($data->filters ?? []);

        return view('admin.fees.manage', [
            'header_title'   => 'Manage Fees',
            'feeTypes'       => $feeTypes,
            'programmes'     => collect($data->programmes ?? []),
            'log'            => collect($data->log ?? []),
            'search'         => $filters['search'] ?? '',
            'group'          => $filters['group'] ?? null,
            'payerType'      => $filters['payerType'] ?? null,
            'status'         => $filters['status'] ?? null,
            'year'           => $filters['year'] ?? date('Y'),
            'years'          => collect($data->years ?? []),
        ]);
    }

    // ── Fee type catalogue CRUD ────────────────────────────────────────────

    public function storeFeeType(Request $request)
    {
        $response = $this->api->post('fees/types', $request->only([
            'fee_group', 'name', 'amount', 'currency', 'applies_to_subscription', 'is_active',
        ]));

        if ($response->failed()) {
            return redirect('admin/fees')->with('error', $response->json('message'));
        }

        return redirect('admin/fees')->with('success', $response->json('message'));
    }

    public function updateFeeType(Request $request, $id)
    {
        $response = $this->api->put("fees/types/{$id}", $request->only([
            'fee_group', 'name', 'amount', 'currency', 'applies_to_subscription', 'is_active',
        ]));

        if ($response->failed()) {
            return redirect('admin/fees')->with('error', $response->json('message'));
        }

        return redirect('admin/fees')->with('success', $response->json('message'));
    }

    public function destroyFeeType($id)
    {
        $response = $this->api->delete("fees/types/{$id}");

        if ($response->failed()) {
            return redirect('admin/fees')->with('error', $response->json('message'));
        }

        return redirect('admin/fees')->with('success', $response->json('message'));
    }

    // ── Payer search (AJAX) ────────────────────────────────────────────────

    public function searchPayer(Request $request)
    {
        $response = $this->api->get('fees/search-payer', $request->only(['q']));

        return response()->json($response->json(), $response->status());
    }

    // ── Record / edit / delete a payment ──────────────────────────────────

    public function recordPayment(Request $request)
    {
        $response = $this->api->post('fees/record-payment', $request->all());

        if ($response->failed()) {
            return redirect('admin/fees')->with('error', $response->json('message'));
        }

        return redirect('admin/fees')->with('success', $response->json('message'));
    }

    public function updatePayment(Request $request, $rowKey)
    {
        $response = $this->api->put("fees/payment/{$rowKey}", $request->only([
            'amount_paid', 'status', 'date_paid', 'mode_of_payment', 'reference_number', 'notes',
        ]));

        if ($response->failed()) {
            return redirect('admin/fees')->with('error', $response->json('message'));
        }

        return redirect('admin/fees')->with('success', $response->json('message'));
    }

    public function destroyPayment($rowKey)
    {
        $response = $this->api->delete("fees/payment/{$rowKey}");

        if ($response->failed()) {
            return redirect('admin/fees')->with('error', $response->json('message'));
        }

        return redirect('admin/fees')->with('success', $response->json('message'));
    }

    // ── Annual Subscription report + reminders ────────────────────────────

    public function subscriptionReport(Request $request)
    {
        $response = $this->api->get('fees/subscriptions/report', $request->only(['year', 'status', 'country_id', 'q']));

        if ($response->failed()) {
            abort(500, 'Failed to load the subscription report.');
        }

        $data = $response->object();

        return view('admin.fees.subscription_report', [
            'header_title' => 'Annual Subscription Report',
            'year'         => $data->year,
            'years'        => collect($data->years ?? []),
            'countries'    => collect($data->countries ?? []),
            'filters'      => (array) ($data->filters ?? []),
            'summary'      => (array) ($data->summary ?? []),
            'rows'         => collect($data->rows ?? []),
        ]);
    }

    // Multi-year Excel download: one report API call per selected year,
    // bundled into a Summary sheet + one sheet per year.
    public function exportSubscriptions(Request $request)
    {
        $request->validate([
            'years'   => 'required|array|min:1|max:20',
            'years.*' => 'integer|min:1990|max:2099|distinct',
        ], ['years.required' => 'Select at least one year to download.']);

        $years   = collect($request->input('years'))->map(fn ($y) => (int) $y)->sortDesc()->values();
        $filters = $request->boolean('apply_filters') ? $request->only(['status', 'country_id', 'q']) : [];

        $summaryRows = [];
        $yearRows    = [];

        foreach ($years as $year) {
            $response = $this->api->get('fees/subscriptions/report', array_filter(['year' => $year] + $filters));

            if ($response->failed()) {
                return back()->with('error', "Failed to load the {$year} subscription report — nothing was downloaded.");
            }

            $data = $response->object();
            $s    = (array) ($data->summary ?? []);

            $summaryRows[] = [
                $year, $s['total_fellows'] ?? 0, $s['paid'] ?? 0, $s['partial'] ?? 0, $s['unpaid'] ?? 0,
                $s['none'] ?? 0, $s['waived'] ?? 0, $s['owing'] ?? 0,
                $s['amount_due'] ?? 0, $s['amount_collected'] ?? 0, $s['outstanding'] ?? 0,
            ];

            $yearRows[$year] = collect($data->rows ?? [])->map(fn ($r) => [
                trim((string) $r->name),
                $r->email,
                $r->country_name,
                $r->fellowship_type,
                $r->effective_status === 'None' ? 'No Record' : $r->effective_status,
                $r->amount_due,
                $r->amount_paid,
                $r->outstanding,
                $r->date_paid ? \Carbon\Carbon::parse($r->date_paid)->format('Y-m-d') : null,
                (! $r->mode_of_payment || preg_match('/^\d{4}-\d{2}-\d{2}/', $r->mode_of_payment)) ? null : $r->mode_of_payment,
            ])->all();
        }

        $span = $years->count() === 1 ? $years->first() : $years->last() . '-' . $years->first();

        return Excel::download(
            new SubscriptionReportExport($summaryRows, $yearRows),
            "annual_subscriptions_{$span}.xlsx"
        );
    }

    // JSON for the report page's fellow drawer: contact details + every
    // subscription year on record. Reuses the fellow detail endpoint so no
    // API change is needed; lives under admin/fees so fees.view is enough.
    public function subscriptionFellow($id)
    {
        $response = $this->api->get("fellows/{$id}/detail");

        if ($response->failed()) {
            return response()->json(['message' => 'Fellow not found.'], $response->status() === 404 ? 404 : 500);
        }

        $fellow = $response->object()->fellow;
        $subs   = collect($response->object()->subscriptions ?? []);

        $history = $subs->map(fn ($s) => [
            'year'        => (string) $s->year,
            'status'      => $s->status ?: 'Unpaid',
            'amount_due'  => $s->amount_due !== null ? (float) $s->amount_due : null,
            'amount_paid' => $s->amount_paid !== null ? (float) $s->amount_paid : null,
            'date_paid'   => $s->date_paid,
            'mode'        => (! $s->mode_of_payment || preg_match('/^\d{4}-\d{2}-\d{2}/', $s->mode_of_payment)) ? null : $s->mode_of_payment,
        ])->sortByDesc('year')->values();

        return response()->json([
            'fellow' => [
                'id'              => $fellow->fellow_id,
                'name'            => trim((string) $fellow->fellow_name) ?: trim(($fellow->firstname ?? '') . ' ' . ($fellow->lastname ?? '')),
                'email'           => trim((string) ($fellow->personal_email ?? '')) ?: ($fellow->email ?? null),
                'phone'           => $fellow->phone_number ?? null,
                'country'         => $fellow->country_name ?? null,
                'fellowship_type' => $fellow->fellowship_type ?? null,
                'programme'       => $fellow->programme_name ?? null,
                'photo'           => $fellow->profile_image_url ?? null,
                'profile_url'     => url('admin/associates/fellows/view/' . $fellow->fellow_id) . '#tab-subs',
            ],
            'history'      => $history,
            'total_paid'   => round($history->sum(fn ($h) => $h['amount_paid'] ?? 0), 2),
            'total_owing'  => round($history->sum(fn ($h) => $h['status'] === 'Waived' ? 0 : max(0, ($h['amount_due'] ?? 0) - ($h['amount_paid'] ?? 0))), 2),
            'years_paid'   => $history->where('status', 'Paid')->count(),
        ]);
    }

    public function sendSubscriptionReminders(Request $request)
    {
        $response = $this->api->post('fees/subscriptions/remind', $request->only([
            'year', 'subject', 'body', 'recipient_ids',
        ]));

        if ($response->failed()) {
            return back()->withInput()->with('error', $response->json('message', 'Failed to send reminders.'));
        }

        return back()->with('success', $response->json('message'));
    }
}
