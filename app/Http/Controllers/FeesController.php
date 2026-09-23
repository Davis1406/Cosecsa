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
        // Accept ?years[]=… (multi-select) and the older ?year=… links.
        $selected = $this->requestedYears($request->input('years', $request->input('year')));
        if (! $selected) {
            $selected = [(int) date('Y')];
        }

        $perYear = $this->loadSubscriptionYears($selected, $request->only(['country_id', 'q']));
        if ($perYear === null) {
            abort(500, 'Failed to load the subscription report.');
        }

        $latest = $perYear[$selected[0]];
        $status = $request->input('status');

        // One row per fellow, with that fellow's row for each selected year.
        $fellows = [];
        foreach ($perYear as $year => $data) {
            foreach ($data->rows ?? [] as $r) {
                $f = $fellows[$r->fellow_id] ??= (object) [
                    'fellow_id'       => $r->fellow_id,
                    'name'            => trim((string) $r->name),
                    'email'           => $r->email,
                    'country_name'    => $r->country_name,
                    'fellowship_type' => $r->fellowship_type,
                    'years'           => [],
                ];
                $f->years[$year] = $r;
            }
        }
        $fellows = collect($fellows)->each(function ($f) {
            $f->total_paid  = collect($f->years)->sum(fn ($r) => $r->amount_paid ?? 0);
            $f->outstanding = collect($f->years)->sum(fn ($r) => $r->outstanding ?? 0);
            $f->owing       = collect($f->years)->contains(fn ($r) => in_array($r->effective_status, ['Unpaid', 'Partial', 'None']));
        });

        $sum = fn ($key) => collect($perYear)->sum(fn ($d) => $d->summary->{$key} ?? 0);
        $summary = [
            'total_fellows'    => $fellows->count(),
            'records'          => $fellows->count() * count($selected),
            'paid'             => $sum('paid'),
            'partial'          => $sum('partial'),
            'unpaid'           => $sum('unpaid'),
            'waived'           => $sum('waived'),
            'none'             => $sum('none'),
            'owing'            => $fellows->where('owing', true)->count(),
            'amount_due'       => round($sum('amount_due'), 2),
            'amount_collected' => round($sum('amount_collected'), 2),
            'outstanding'      => round($sum('outstanding'), 2),
        ];

        // Status filter is applied here (not by the API) so a fellow shows if
        // that status occurs in ANY selected year.
        $rows = $status
            ? $fellows->filter(fn ($f) => collect($f->years)->contains(fn ($r) => $r->effective_status === $status))
            : $fellows;

        return view('admin.fees.subscription_report', [
            'header_title'  => 'Annual Subscription Report',
            'year'          => (string) $selected[0],
            'selectedYears' => array_map('strval', $selected),
            'years'         => collect($latest->years ?? []),
            'countries'     => collect($latest->countries ?? []),
            'filters'       => ['status' => $status] + $request->only(['country_id', 'q']),
            'summary'       => $summary,
            'yearOwing'     => collect($perYear)->map(fn ($d) => (int) ($d->summary->owing ?? 0))->all(),
            'rows'          => $rows->values(),
        ]);
    }

    // Multi-year Excel download: a Summary sheet + one sheet per year.
    public function exportSubscriptions(Request $request)
    {
        $request->validate([
            'years'   => 'required|array|min:1|max:20',
            'years.*' => 'integer|min:1990|max:2099|distinct',
        ], ['years.required' => 'Select at least one year to download.']);

        $years   = $this->requestedYears($request->input('years'), 20);
        $filters = $request->boolean('apply_filters') ? $request->only(['status', 'country_id', 'q']) : [];

        $perYear = $this->loadSubscriptionYears($years, $filters);
        if ($perYear === null) {
            return back()->with('error', 'Failed to load the subscription report — nothing was downloaded.');
        }

        $summaryRows = [];
        $yearRows    = [];

        foreach ($perYear as $year => $data) {
            $s = (array) ($data->summary ?? []);

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

        $span = count($years) === 1 ? $years[0] : end($years) . '-' . $years[0];

        return Excel::download(
            new SubscriptionReportExport($summaryRows, $yearRows),
            "annual_subscriptions_{$span}.xlsx"
        );
    }

    // Sanitised, de-duplicated, newest-first list of years from a request value.
    private function requestedYears($input, int $max = 10): array
    {
        return collect((array) $input)
            ->map(fn ($y) => (int) $y)
            ->filter(fn ($y) => $y >= 1990 && $y <= 2099)
            ->unique()->sortDesc()->take($max)->values()->all();
    }

    // Fetches the report for each year concurrently. Returns [year => data]
    // (newest first), or null if any year failed to load.
    private function loadSubscriptionYears(array $years, array $filters): ?array
    {
        $queries = [];
        foreach ($years as $year) {
            $queries[$year] = array_filter(['year' => $year] + $filters);
        }

        $out = [];
        foreach ($this->api->getMany('fees/subscriptions/report', $queries) as $year => $response) {
            if (! $response || $response->failed()) {
                return null;
            }
            $out[$year] = $response->object();
        }

        return $out;
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
