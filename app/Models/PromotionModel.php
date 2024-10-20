<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromotionModel extends Model
{
    use HasFactory;

    protected $table = 'programmes';

    static public function getStudyYear()
    {
        $return = self::select(
                'programmes.id as programme_id',
                'programmes.name as category',
                'study_year.id as study_year_id',
                'study_year.name as programme_year'
            )
            ->join('study_year', 'programmes.id', '=', 'study_year.programme_id')
            ->orderBy('programmes.id', 'asc')
            ->get();

        return $return;
    }
}


