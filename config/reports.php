<?php

// Powers the "College Reports" builder (admin/reports): pick a type, pick
// fields, optionally filter, get a data table + chart + Excel export.
// Each type's 'sql' map is the field key => raw SQL expression selected
// (already scoped to the aliases used in ReportBuilderController::baseQuery()).

return [
    'types' => [
        'trainees' => [
            'label' => 'Trainees',
            'fields' => [
                'name'               => 'Name',
                'entry_number'       => 'Entry Number',
                'gender'             => 'Gender',
                'country_name'       => 'Country',
                'programme_name'     => 'Programme',
                'hospital_name'      => 'Hospital',
                'training_year_name' => 'Study Year',
                'admission_year'     => 'Admission Year',
                'exam_year'          => 'Exam Year',
                'status'             => 'Status',
                'fee_paid'           => 'Fee Paid',
                'invoice_status'     => 'Invoice Status',
            ],
            'filters' => [
                'country_id'   => ['label' => 'Country', 'source' => 'countries'],
                'programme_id' => ['label' => 'Programme', 'source' => 'programmes'],
                'gender'       => ['label' => 'Gender', 'source' => 'gender'],
            ],
            'group_by' => ['country_name', 'programme_name', 'gender', 'training_year_name', 'status'],
        ],
        'candidates' => [
            'label' => 'Candidates',
            'fields' => [
                'name'           => 'Name',
                'entry_number'   => 'Entry Number',
                'gender'         => 'Gender',
                'country_name'   => 'Country',
                'programme_name' => 'Programme',
                'hospital_name'  => 'Hospital',
                'exam_year'      => 'Exam Year',
                'admission_year' => 'Admission Year',
                'invoice_status' => 'Invoice Status',
                'fee_paid'       => 'Fee Paid',
                'amount_paid'    => 'Amount Paid',
            ],
            'filters' => [
                'country_id'   => ['label' => 'Country', 'source' => 'countries'],
                'programme_id' => ['label' => 'Programme', 'source' => 'programmes'],
                'gender'       => ['label' => 'Gender', 'source' => 'gender'],
            ],
            'group_by' => ['country_name', 'programme_name', 'gender', 'exam_year', 'invoice_status'],
        ],
        'fellows' => [
            'label' => 'Fellows',
            'fields' => [
                'name'              => 'Name',
                'candidate_number'  => 'Candidate Number',
                'gender'            => 'Gender',
                'country_name'      => 'Country',
                'programme_name'    => 'Programme',
                'category_name'     => 'Category',
                'current_specialty' => 'Specialty',
                'admission_year'    => 'Admission Year',
                'fellowship_year'   => 'Fellowship Year',
                'status'            => 'Status',
                'cosecsa_region'    => 'COSECSA Region',
                'is_alumni'         => 'Alumni',
            ],
            'filters' => [
                'country_id'  => ['label' => 'Country', 'source' => 'countries'],
                'category_id' => ['label' => 'Category', 'source' => 'categories'],
                'gender'      => ['label' => 'Gender', 'source' => 'gender'],
                'status'      => ['label' => 'Status', 'source' => 'status'],
            ],
            'group_by' => ['country_name', 'category_name', 'current_specialty', 'gender', 'status'],
        ],
        'members' => [
            'label' => 'Members',
            'fields' => [
                'name'             => 'Name',
                'gender'           => 'Gender',
                'country_name'     => 'Country',
                'category_name'    => 'Category',
                'membership_year'  => 'Membership Year',
                'admission_year'   => 'Admission Year',
                'status'           => 'Status',
            ],
            'filters' => [
                'country_id'  => ['label' => 'Country', 'source' => 'countries'],
                'category_id' => ['label' => 'Category', 'source' => 'categories'],
                'gender'      => ['label' => 'Gender', 'source' => 'gender'],
                'status'      => ['label' => 'Status', 'source' => 'status'],
            ],
            'group_by' => ['country_name', 'category_name', 'gender', 'status'],
        ],
        'trainers' => [
            'label' => 'Trainers (Programme Directors)',
            'fields' => [
                'name'            => 'Name',
                'hospital_name'   => 'Hospital',
                'phone_number'    => 'Phone Number',
                'assistant_pd'    => 'Assistant PD',
                'assistant_email' => 'Assistant Email',
            ],
            'filters' => [],
            'group_by' => ['hospital_name'],
        ],
        'country_reps' => [
            'label' => 'Country Reps',
            'fields' => [
                'name'           => 'Name',
                'country_name'   => 'Country',
                'position'       => 'Position',
                'mobile_no'      => 'Mobile',
                'cosecsa_email'  => 'Cosecsa Email',
            ],
            'filters' => [
                'country_id' => ['label' => 'Country', 'source' => 'countries'],
            ],
            'group_by' => ['country_name', 'position'],
        ],
        'examiners' => [
            'label' => 'Examiners',
            'fields' => [
                'name'                  => 'Name',
                'examiner_id'           => 'Examiner ID',
                'gender'                => 'Gender',
                'country_name'          => 'Country',
                'specialty'             => 'Specialty',
                'subspecialty'          => 'Subspecialty',
                'status'                => 'Status',
                'examiner_designation'  => 'Designation',
            ],
            'filters' => [
                'country_id' => ['label' => 'Country', 'source' => 'countries'],
                'gender'     => ['label' => 'Gender', 'source' => 'gender'],
                'status'     => ['label' => 'Status', 'source' => 'status'],
            ],
            'group_by' => ['country_name', 'specialty', 'gender', 'status'],
        ],
    ],
];
