<?php
namespace App\Imports;

use App\Models\CountryRepsModel;
use App\Models\User;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Hash;

class RepsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Check if a user with the same email and a different user_type exists
        $existingUser = User::where('email', $row['email'])->first();

        if ($existingUser) {
            if ($existingUser->user_type == 5) {
                // If the user already exists with user_type 5, use the existing user
                $user = $existingUser;
            } else {
                // Create a new user with the same email and different user_type
                $user = User::create([
                    'name' => $row['name'],
                    'email' => $row['email'],
                    'password' => Hash::make($row['password']), // Hash the password
                    'user_type' => 5 // '5' represents country representatives
                ]);
            }
        } else {
            // If no such user exists, create a new user
            $user = User::create([
                'name' => $row['name'],
                'email' => $row['email'],
                'password' => Hash::make($row['password']), // Hash the password
                'user_type' => 5 // '5' represents country representatives
            ]);
        }

        if ($user) {
            // Then create the CountryRepsModel
            return new CountryRepsModel([
                'user_id' => $user->id,
                'country_id' => $row['country_id'],
                'profile_image' => $row['profile_image'],
                'cosecsa_email' => $row['cosecsa_email'],
                'mobile_no' => $row['mobile_no'],
            ]);
        } else {
            // Handle the case where user creation fails
            throw new \Exception('User creation failed for email: ' . $row['email']);
        }
    }
}

