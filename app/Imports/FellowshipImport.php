<?php

namespace App\Imports;

use App\Models\FellowsModel;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class FellowshipImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $fullName = trim(($row['firstname'] ?? '') . ' ' . ($row['middlename'] ?? '') . ' ' . ($row['lastname'] ?? ''));
        $userType = 7;

        $user = User::create([
            'name'      => $fullName,
            'email'     => $row['email'],
            'password'  => Hash::make($row['password'] ?? 'changeme123'),
            'user_type' => $userType
        ]);

        UserRole::create([
            'user_id'   => $user->id,
            'role_type' => $userType,
            'is_active' => 1
        ]);

        return new FellowsModel([
            'user_id'                       => $user->id,
            'category_id'                   => $row['category_id'] ?? null,
            'firstname'                     => $row['firstname'] ?? null,
            'middlename'                    => $row['middlename'] ?? null,
            'lastname'                      => $row['lastname'] ?? null,
            'personal_email'                => $row['personal_email'] ?? null,
            'second_email'                  => $row['second_email'] ?? null,
            'gender'                        => $row['gender'] ?? null,
            'status'                        => $row['status'] ?? 'Active',
            'profile_image'                 => $row['profile_image'] ?? null,
            'programme_id'                  => $row['programme_id'] ?? null,
            'country_id'                    => $row['country_id'] ?? null,
            'phone_number'                  => $row['phone_number'] ?? null,
            'is_promoted'                   => (string)($row['is_promoted'] ?? '0'),
            'address'                       => $row['address'] ?? null,
            'current_specialty'             => $row['current_specialty'] ?? null,
            'organization'                  => $row['organization'] ?? null,
            'admission_year'                => $row['admission_year'] ?? null,
            'fellowship_year'               => $row['fellowship_year'] ?? null,
            // Extended fields
            'candidate_number'              => $row['candidate_number'] ?? null,
            'supervised_by'                 => $row['supervised_by'] ?? null,
            'registered_by'                 => $row['registered_by'] ?? null,
            'secretariat_registration_date' => !empty($row['secretariat_registration_date']) ? $row['secretariat_registration_date'] : null,
            'prog_entry_fee_year'           => $row['prog_entry_fee_year'] ?? null,
            'prog_entry_mode_payment'       => $row['prog_entry_mode_payment'] ?? null,
            'exam_fee_year'                 => $row['exam_fee_year'] ?? null,
            'exam_fee_date_paid'            => !empty($row['exam_fee_date_paid']) ? $row['exam_fee_date_paid'] : null,
            'exam_fee_mode_payment'         => $row['exam_fee_mode_payment'] ?? null,
            'exam_fee_amount_paid'          => $row['exam_fee_amount_paid'] ?? null,
            'exam_fee_payment_verified'     => $row['exam_fee_payment_verified'] ?? 0,
            'sponsored_by'                  => $row['sponsored_by'] ?? null,
            'mcs_qualification_year'        => $row['mcs_qualification_year'] ?? null,
            'country_mcs_training'          => $row['country_mcs_training'] ?? null,
            'exam_year_upcoming'            => $row['exam_year_upcoming'] ?? null,
            'exam_year_previous'            => $row['exam_year_previous'] ?? null,
            'cosecsa_region'                => $row['cosecsa_region'] ?? null,
        ]);
    }
}
