<?php

// Every admin-facing module gets a "view" and a "manage" permission
// (manage covers create/edit/delete/import — anything beyond read-only).
// ROUTE_MAP is checked longest-prefix-first by PermissionMiddleware to decide
// which module a given request belongs to. Prefixes with no match are
// allowed through unchecked (fail-open) so a route nobody thought to map
// here doesn't silently lock everyone out.

return [

    'modules' => [
        'dashboard'    => 'Dashboard',
        'admin_users'  => 'Admin Accounts',
        'roles'        => 'Roles & Permissions',
        'lookups'      => 'Countries / Hospitals / Programmes',
        'trainees'     => 'Trainees',
        'candidates'   => 'Candidates',
        'trainers'     => 'Trainers',
        'country_reps' => 'Country Reps',
        'fellows'      => 'Fellows',
        'members'      => 'Members',
        'examiners'    => 'Examiners & Exam Results',
        'promotions'   => 'Promotions',
        'capsule'      => 'Capsule Sync',
        'salesforce'   => 'Salesforce Applications',
        'fees'         => 'Fees',
        'settings'     => 'Settings',
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
    ],
];
