<?php

namespace App\Imports;

use App\Models\Trainer;
use App\Models\User;
use App\Models\UserRole;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;

class TrainersImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $userType = 4; // '4' represents trainers

        // Create the User first
        $user = User::create([
            'name' => $row['name'],
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