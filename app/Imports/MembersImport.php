<?php

namespace App\Imports;

use App\Models\MembersModel;
use App\Models\User;
use App\Models\UserRole;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class MembersImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $userType = 8; // '8' represents members

        // Concatenate names
        $fullName = trim("{$row['firstname']} {$row['middlename']} {$row['lastname']}");

        // Create user
        $user = User::create([
            'name' => $fullName,
            'email' => $row['email'],
            'password' => Hash::make($row['password']), // Hash the password
            'user_type' => $userType
        ]);

        // Assign role in user_roles table
        UserRole::create([
            'user_id' => $user->id,
            'role_type' => $userType,
            'is_active' => 1
        ]);

        // Create Member
        return new MembersModel([
            'user_id' => $user->id,
            'category_id' => $row['category_id'],
            'firstname' => $row['firstname'],
            'middlename' => $row['middlename'],
            'lastname' => $row['lastname'],
            'password' => Hash::make($row['password']), // Hash the password
            'personal_email' => $row['personal_email'],
            'gender' => $row['gender'],
            'status' => $row['status'],
            'profile_image' => $row['profile_image'],
            'country_id' => $row['country_id'],
            'phone_number' => $row['phone_number'],
            'is_promoted' => (string) $row['is_promoted'],
            'address' => $row['address'],
            'admission_year' => $row['admission_year'],
            'membership_year' => $row['membership_year']
        ]);
    }
}