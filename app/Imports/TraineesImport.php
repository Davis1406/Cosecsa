<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Trainee;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class TraineesImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Convert date formats
        $invoiceDate = $this->convertDateFormat($row['invoice_date']);
        $paymentDate = $this->convertDateFormat($row['payment_date']);

        // Concatenate names
        $fullName = trim("{$row['firstname']} {$row['middlename']} {$row['lastname']}");

        // Create user
        $user = User::create([
            'name' => $fullName,
            'email' => $row['email'],
            'password' => $row['password'],
            'user_type' => 2 // Assuming '2' represents trainees
        ]);

        // Create trainee
        return new Trainee([
            'user_id' => $user->id,
            'firstname' => $row['firstname'],
            'middlename' => $row['middlename'],
            'lastname' => $row['lastname'],
            'personal_email' => $row['personal_email'],
            'gender' => $row['gender'],
            'status' => $row['status'],
            'profile_image' => $row['profile_image'],
            'programme_id' => $row['programme_id'],
            'hospital_id' => $row['hospital_id'],
            'country_id' => $row['country_id'],
            'entry_number' => $row['entry_number'],
            'admission_letter_status' => $row['admission_letter_status'],
            'invitation_letter_status' => $row['invitation_letter_status'],
            'admission_year' => $row['admission_year'],
            'exam_year' => $row['exam_year'],
            'programme_period' => $row['programme_period'],
            'invoice_number' => $row['invoice_number'],
            'invoice_date' => $invoiceDate,
            'invoice_status' => $row['invoice_status'],
            'sponsor' => $row['sponsor'],
            'mode_of_payment' => $row['mode_of_payment'],
            'amount_paid' => $row['amount_paid'],
            'payment_date' => $paymentDate,
        ]);
    }

    private function convertDateFormat($date)
    {
        if ($date) {
            return Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
        }
        return null;
    }
}
