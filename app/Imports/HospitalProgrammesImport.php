<?php

namespace App\Imports;

use App\Models\HospitalProgrammesModel;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class HospitalProgrammesImport implements ToModel, WithHeadingRow
{
    public function model(array $row) {
        return new HospitalProgrammesModel([
            'hospital_id' => $row['hospital_id'],
            'programme_id' => $row['programme_id'],
            'accredited_date' => $this->convertDateFormat($row['accredited_date']),
            'expiry_date' => $this->convertDateFormat($row['expiry_date']),
            'status' => $row['status'],
        ]);
    }

    private function convertDateFormat($date) {
        if (is_numeric($date)) {
            // Convert Excel serial date number to a Carbon date
            return Carbon::instance(ExcelDate::excelToDateTimeObject($date))->format('Y-m-d');
        } elseif ($date) {
            // Convert string date format to Y-m-d
            return Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d');
        }
        return null;
    }
}
