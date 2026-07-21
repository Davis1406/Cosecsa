<?php

// Every admin-facing module gets a "view" and a "manage" permission
// (manage covers create/edit/delete/import — anything beyond read-only).
// The view/manage strings describe exactly what that permission unlocks,
// shown on the Roles & Permissions screen so an admin configuring a role
// can see what they're actually granting, not just a bare checkbox.
// ROUTE_MAP is checked longest-prefix-first by PermissionMiddleware to decide
// which module a given request belongs to. Prefixes with no match are
// allowed through unchecked (fail-open) so a route nobody thought to map
// here doesn't silently lock everyone out.

return [

    'modules' => [
        'dashboard' => [
            'label'  => 'Dashboard',
            'view'   => 'See the admin dashboard overview and stats',
            'manage' => null,
        ],
        'admin_users' => [
            'label'  => 'Admin Accounts',
            'view'   => 'See the list of admin/staff accounts and their roles',
            'manage' => 'Create, edit, delete admin accounts; log in as another user ("Login as User")',
        ],
        'roles' => [
            'label'  => 'Roles & Permissions',
            'view'   => 'See the list of roles',
            'manage' => 'Create, edit, delete roles and change what each role can access',
        ],
        'lookups' => [
            'label'  => 'Countries / Hospitals / Programmes',
            'view'   => 'See accredited hospitals, programmes, and hospital-programme links',
            'manage' => 'Add, edit, delete hospitals, programmes, and hospital-programme links',
        ],
        'trainees' => [
            'label'  => 'Trainees',
            'view'   => 'See the trainee list, profiles, and reports',
            'manage' => 'Add, edit, delete, import trainees; bulk update and quick-update fields',
        ],
        'candidates' => [
            'label'  => 'Candidates',
            'view'   => 'See the candidate list, profiles, and reports',
            'manage' => 'Add, edit, delete, import candidates',
        ],
        'trainers' => [
            'label'  => 'Trainers (Programme Directors)',
            'view'   => 'See the programme directors list and profiles',
            'manage' => 'Add, edit, delete, import programme directors',
        ],
        'country_reps' => [
            'label'  => 'Country Reps',
            'view'   => 'See the country reps list and profiles',
            'manage' => 'Add, edit, delete, import country reps',
        ],
        'fellows' => [
            'label'  => 'Fellows',
            'view'   => 'See the fellow list, profiles, and reports',
            'manage' => 'Add, edit, delete, import fellows; manage subscriptions and labels',
        ],
        'members' => [
            'label'  => 'Members',
            'view'   => 'See the member list and profiles',
            'manage' => 'Add, edit, delete, import members',
        ],
        'examiners' => [
            'label'  => 'Examiners & Exam Results',
            'view'   => 'See examiners, attendance, and exam results across all programmes',
            'manage' => 'Add, edit, delete examiners; manage attendance, confirmations, bulk email, exam results, specialty updates',
        ],
        'promotions' => [
            'label'  => 'Promotions',
            'view'   => 'See trainee/candidate promotion screens',
            'manage' => 'Promote trainees between study years, and promote trainees to exam candidates',
        ],
        'capsule' => [
            'label'  => 'Capsule Sync',
            'view'   => 'See the Capsule CRM sync status and history',
            'manage' => 'Trigger a Capsule sync and import contacts',
        ],
        'salesforce' => [
            'label'  => 'Salesforce Applications',
            'view'   => 'See synced Salesforce applications',
            'manage' => 'Trigger a Salesforce sync; convert completed applications into trainees',
        ],
        'fees' => [
            'label'  => 'Fees',
            'view'   => 'See the fee catalogue and payment records',
            'manage' => 'Record, edit, delete payments; add/edit fee types',
        ],
        'settings' => [
            'label'  => 'Settings',
            'view'   => 'See fellow label and designation settings',
            'manage' => 'Add, edit, delete fellow labels and designations',
        ],
        'system_logs' => [
            'label'  => 'System Logs',
            'view'   => 'See login history, record changes, and dispatched emails',
            'manage' => null,
        ],
        'transcripts' => [
            'label'  => 'Transcripts',
            'view'   => 'See and generate candidate transcript PDFs',
            'manage' => 'Edit candidate transcript details and manage transcript templates (Settings)',
        ],
        'reports' => [
            'label'  => 'College Reports',
            'view'   => 'Build custom reports (choose type, fields, filters) and export to Excel',
            'manage' => null,
        ],
        'letters' => [
            'label'  => 'College Letters',
            'view'   => 'See letter templates, dispatch history, and the sent-letters report',
            'manage' => 'Create/edit letter templates, dispatch letters to recipients, manage the college letterhead',
        ],
    ],

    // Longest prefix wins — order doesn't matter, matching sorts by length.
    'route_map' => [
        'admin/dashboard'               => 'dashboard',
        'admin/global-search'           => 'dashboard',
        'admin/roles'                   => 'roles',
        'admin/list'                    => 'admin_users',
        'admin/add'                     => 'admin_users',
        'admin/edit'                    => 'admin_users',
        'admin/delete'                  => 'admin_users',
        'admin/impersonate'             => 'admin_users',
        'admin/countries'               => 'lookups',
        'admin/hospital'                => 'lookups',
        'admin/programmes'              => 'lookups',
        'admin/hospitalprogrammes'      => 'lookups',
        'admin/associates/trainees'     => 'trainees',
        'admin/associates/candidates'   => 'candidates',
        'admin/associates/trainers'     => 'trainers',
        'admin/associates/reps'         => 'country_reps',
        'admin/associates/fellows'      => 'fellows',
        'admin/associates/members'      => 'members',
        'admin/associates/promotion'    => 'promotions',
        'admin/exams'                   => 'examiners',
        'admin/capsule'                 => 'capsule',
        'admin/salesforce'              => 'salesforce',
        'admin/fees'                    => 'fees',
        'admin/settings'                => 'settings',
        'admin/logs'                    => 'system_logs',
        'admin/reports'                 => 'reports',
        'admin/transcripts'             => 'transcripts',
        'admin/settings/transcript-templates' => 'transcripts',
        'admin/letters'                 => 'letters',
    ],
];
