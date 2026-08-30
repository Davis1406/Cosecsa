<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'cosecsa_api' => [
        'url'   => env('COSECSA_API_URL', 'http://api.cosecsamis.org'),
        'token' => env('COSECSA_API_TOKEN'),
    ],

    // The CEO's user_id — kept separate from config/progress_report_sections.php
    // (which deliberately excludes her: she's the report's recipient, not a
    // section that gets auto-seeded into every month). Used wherever code
    // needs to identify "is this the CEO" regardless of whether she happens
    // to have a participant row on a given period — see
    // ProgressReportParticipant::ceoUserId().
    'progress_reports' => [
        // Stella Itungu — hardcoded like every other id in
        // config/progress_report_sections.php, not env-driven.
        'ceo_user_id' => 17991,
    ],

    'capsule' => [
        'token' => env('CAPSULE_API_TOKEN'),
    ],

    'salesforce' => [
        'login_url'     => env('SALESFORCE_LOGIN_URL', 'https://cosecsa2.my.salesforce.com'),
        'client_id'     => env('SALESFORCE_CLIENT_ID'),
        'client_secret' => env('SALESFORCE_CLIENT_SECRET'),
        'api_version'   => env('SALESFORCE_API_VERSION', 'v60.0'),
    ],

];
