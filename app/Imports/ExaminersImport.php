<?php

namespace App\Imports;

use App\Models\ExamsModel;
use Maatwebsite\Excel\Concerns\ToModel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\WithHeadingRow;


class ExaminersImport implements ToModel,WithHeadingRow
{
    public function model(array $row)
    {

     // Create user
     $user = User::create([
         'name' => $row['name'],
         'email' => $row['email'],
         'password' => Hash::make($row['password']), // Encrypting password
         'user_type' => 9 //'9' represents Examiners
     ]);

     // Create Examiner
     return new ExamsModel([
         'user_id' => $user->id,
         'examiner_id' => $row['examiner_id'],
         'country_id' => $row['country_id'],
         'mobile' => $row['mobile'],
         'group_id' => (string) $row['group_id'],
         'specialty' => $row['specialty'],
         'shift' => $row['shift'],
     ]);
    }
}
