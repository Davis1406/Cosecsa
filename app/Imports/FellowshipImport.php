<?php

namespace App\Imports;

use App\Models\FellowsModel;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class FellowshipImport implements ToModel,WithHeadingRow
{

    public function model(array $row)
    {
     // Convert date formats
    //  $invoiceDate = $this->convertDateFormat($row['invoice_date']);
    //  $paymentDate = $this->convertDateFormat($row['payment_date']);

     // Concatenate names
     $fullName = trim("{$row['firstname']} {$row['middlename']} {$row['lastname']}");

     // Create user
     $user = User::create([
         'name' => $fullName,
         'email' => $row['email'],
         'password' => $row['password'],
         'user_type' => 7 //'7' represents fellows
     ]);

     // Create Fellow
     return new FellowsModel([
         'user_id' => $user->id,
         'category_id' => $row['category_id'],
         'firstname' => $row['firstname'],
         'middlename' => $row['middlename'],
         'lastname' => $row['lastname'],
         'personal_email' => $row['personal_email'],
         'gender' => $row['gender'],
         'status' => $row['status'],
         'profile_image' => $row['profile_image'],
         'programme_id' => $row['programme_id'],
         'country_id' => $row['country_id'],
         'phone_number' => $row['phone_number'],
         'is_promoted' => (string) $row['is_promoted'],
         'address' => $row['address'],
         'current_specialty' => $row['current_specialty'],
         'organization' => $row['organization'],
         'admission_year' => $row['admission_year'],
         'fellowship_year' => $row['fellowship_year']
     ]);
    }

    // private function convertDateFormat($date) {
    //     if (is_numeric($date)) {
    //         // Convert Excel serial date number to a Carbon date
    //         return Carbon::instance(ExcelDate::excelToDateTimeObject($date))->format('Y-m-d');
    //     } elseif ($date) {
    //         // Convert string date format to Y-m-d
    //         return Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
    //     }
    //     return null;
    // }
}
