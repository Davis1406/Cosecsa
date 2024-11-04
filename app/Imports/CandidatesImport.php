<?php

namespace App\Imports;

use App\Models\Candidates;
use Maatwebsite\Excel\Concerns\ToModel;
use App\Models\User;
use App\Models\Trainee;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class CandidatesImport implements ToModel, WithHeadingRow 
{

    public function model(array $row)
    {
               // Convert date formats
               $invoiceDate = $this->convertDateFormat($row['invoice_date']);
            //    $paymentDate = $this->convertDateFormat($row['payment_date']);
       
               // Concatenate names
               $fullName = trim("{$row['firstname']} {$row['middlename']} {$row['lastname']}");

               $user = User::where('name', $fullName)->first();

               // Create user
               if(!$user){

                $user = User::create([
                    'name' => $fullName,
                    'email' => $row['email'],
                    'password' => $row['password'],
                    'user_type' => 3 //'3' represents trainees
                ]);

               }


        return new Candidates([
            'user_id'=> $user->id,
            'firstname' => $row['firstname'],
            'middlename' => $row['middlename'],
            'lastname' => $row['lastname'],
            'personal_email' => $row['personal_email'],
            'entry_number' => $row['entry_number'],
            'hospital_id' => $row['hospital_id'],
            'programme_id' => $row['programme_id'],
            'group_id' => $row['group_id'],
            'candidate_id' => $row['candidate_id'],
            'gender' => $row['gender'],
            'country_id' => $row['country_id'],
            'repeat_paper_one'=> $row['repeat_paper_one'],
            'repeat_paper_two'=> $row['repeat_paper_two'],
            'mmed'=> $row['mmed'],
            'invoice_number' => $row['invoice_number'],
            'invoice_date' => $invoiceDate,
            'amount_paid' => $row['amount_paid'],
            'invoice_status' => $row['invoice_status'],
            'admission_year' => $row['admission_year'],
            'exam_year' => $row['exam_year'],
            'sponsor' => $row['sponsor'],
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
