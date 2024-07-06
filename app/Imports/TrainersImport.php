<?php

namespace App\Imports;

use App\Models\Trainer;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TrainersImport implements ToModel, WithHeadingRow
{

    public function model(array $row)
    {
        // Create the User first
        $user = User::create([
            'name' => $row['name'],
            'email' => $row['email'],
            'password' => $row['password'],
            'user_type' => 4 // '4' represents trainers
        ]);

        // Then create the Trainer
        return new Trainer([
            'user_id' => $user->id,
            'phone_number' => $row['phone_number'],
            'hospital_id' => $row['hospital_id'],
            'profile_image' => $row['profile_image'],
            'assistant_pd' => $row['assistant_pd'],
            'assistant_email' => $row['assistant_email'],
            'mobile_no' => $row['mobile_no'],
        ]);
    }
}
