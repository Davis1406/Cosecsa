<?php

namespace App\Imports;

use App\Models\ExamsModel;
use Maatwebsite\Excel\Concerns\ToModel;
use App\Models\User;
use App\Models\ExamsShift;
use App\Models\ExaminerHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\WithHeadingRow;


class ExaminersImport implements ToModel,WithHeadingRow
{
public function model(array $row)
{
    // 1. Create User
    $user = User::create([
        'name' => $row['name'],
        'email' => $row['email'],
        'password' => Hash::make($row['password']),
        'user_type' => 9, // Examiner
    ]);

    // 2. Create Examiner profile
    $examiner = ExamsModel::create([
        'user_id' => $user->id,
        'examiner_id' => $row['examiner_id'] ?? null,
        'country_id' => $row['country_id'],
        'mobile' => $row['mobile'] ?? null,
        'specialty' => $row['specialty'] ?? null,
        'subspecialty' => $row['subspecialty'] ?? null,
        'gender' => $row['gender'] ?? null,
        'role_id' => $row['role_id'],

    ]);

    // 3. Attach group (pivot table: exams_groups)
    if (!empty($row['group_id'])) {
        $yearId = User::getCurrentYearId(); // Assuming method exists
        $examiner->groups()->attach($row['group_id'], [
            'year_id' => $yearId,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    // 4. Assign shift (exams_shifts table)
    if (!empty($row['shift'])) {
        $yearId = User::getCurrentYearId();
        ExamsShift::create([
            'exm_id' => $examiner->id,
            'shift' => $row['shift'],
            'year_id' => $yearId
        ]);
    }

    // 5. Create Examiner History
    ExaminerHistory::create([
        'exm_id' => $examiner->id,
        'virtual_mcs_participated' => $row['virtual_mcs_participated'] ?? null,
        'fcs_participated' => $row['fcs_participated'] ?? null,
        'participation_type' => $row['participation_type'] ?? null,
        'hospital_type' => $row['hospital_type'] ?? null,
        'hospital_name' => $row['hospital_name'] ?? null,
        'exam_availability' => isset($row['exam_availability']) 
            ? json_encode(explode(',', $row['exam_availability'])) 
            : null,
        'examination_years' => isset($row['examination_years']) 
            ? json_encode(explode(',', $row['examination_years'])) 
            : null,
    ]);

    return $examiner;
}

}
