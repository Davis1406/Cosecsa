<?php

namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Request;

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
}
