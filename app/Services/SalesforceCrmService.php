<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SalesforceCrmService
{
    protected string $loginUrl;
    protected string $clientId;
    protected string $clientSecret;
    protected string $apiVersion;

    public function __construct()
    {
        $this->loginUrl    = config('services.salesforce.login_url') ?? 'https://cosecsa2.my.salesforce.com';
        $this->clientId    = config('services.salesforce.client_id') ?? '';
        $this->clientSecret = config('services.salesforce.client_secret') ?? '';
        $this->apiVersion  = config('services.salesforce.api_version') ?? 'v60.0';
    }

    /**
     * Get a valid access token + instance URL, cached for 90 minutes
     * (Client Credentials Flow tokens are short-lived; well within that window).
     */
    protected function auth(): ?array
    {
        return Cache::remember('salesforce_access_token', 5400, function () {
            $response = Http::asForm()->post("{$this->loginUrl}/services/oauth2/token", [
                'grant_type'    => 'client_credentials',
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

            if (! $response->successful()) {
                Log::error('Salesforce auth failed', ['status' => $response->status(), 'body' => $response->body()]);
                return null;
            }

            return [
                'access_token' => $response->json('access_token'),
                'instance_url' => $response->json('instance_url'),
            ];
        });
    }

    protected function http(): ?Http\PendingRequest
    {
        $auth = $this->auth();
        if (! $auth) {
            return null;
        }

        return Http::withToken($auth['access_token'])->baseUrl($auth['instance_url']);
    }

    /**
     * Run a SOQL query, following pagination (nextRecordsUrl) until done.
     */
    public function query(string $soql): array
    {
        $auth = $this->auth();
        if (! $auth) {
            return [];
        }

        $records = [];
        $url = "/services/data/{$this->apiVersion}/query?" . http_build_query(['q' => $soql]);

        while ($url) {
            $response = Http::withToken($auth['access_token'])->get($auth['instance_url'] . $url);

            if (! $response->successful()) {
                Log::error('Salesforce query failed', ['status' => $response->status(), 'body' => $response->body()]);
                break;
            }

            $records = array_merge($records, $response->json('records', []));
            $url = $response->json('nextRecordsUrl');
        }

        return $records;
    }

    /**
     * Fetch Application__c records with related Applicant/Programme fields,
     * modified since the given timestamp (for incremental syncs). Pass null
     * for a full sync.
     */
    public function getApplications(?string $modifiedSince = null): array
    {
        $soql = "SELECT Id, Name, Applicant__r.Name, Applicant__r.Email__c, Applicant__r.Phone_Number__c, "
              . "Applicant__r.Gender__c, Application_Level__c, Application_Stage__c, "
              . "COSECSA_Programme_applied_for__r.Name, Base_Hospital__r.Name, "
              . "Country__c, Exam_Year__c, Date_of_Application__c, Entry_Number__c, Program_Entry_Number__c, "
              . "Application_Received__c, Application_Approved__c, CreatedDate, LastModifiedDate, "
              . "(SELECT Name, Payment_Date__c, Payment_Amount__c, Invoiced_Amount__c, Payment_Method__c, Status__c "
              . " FROM Invoices__r WHERE Invoice_Type__c = 'Application' ORDER BY CreatedDate DESC LIMIT 1) "
              . "FROM Application__c ";

        if ($modifiedSince) {
            $soql .= "WHERE LastModifiedDate > {$modifiedSince} ";
        }

        $soql .= "ORDER BY LastModifiedDate DESC";

        return $this->query($soql);
    }
}
