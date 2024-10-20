<?php

namespace App\Imports;

use App\Models\MembersModel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use App\Models\User;


class MembersImport implements ToModel,WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

     // Concatenate names
     $fullName = trim("{$row['firstname']} {$row['middlename']} {$row['lastname']}");

     // Create user
     $user = User::create([
         'name' => $fullName,
         'email' => $row['email'],
         'password' => $row['password'],
         'user_type' => 8 //'7' represents members
     ]);

     // Create Member
     return new MembersModel([
         'user_id' => $user->id,
         'category_id' => $row['category_id'],
         'firstname' => $row['firstname'],
         'middlename' => $row['middlename'],
         'lastname' => $row['lastname'],
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
