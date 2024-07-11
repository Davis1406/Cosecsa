<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Carbon\Carbon;


class HospitalProgrammesModel extends Model
{
    use HasFactory;

    protected $table = 'hospital_programmes';

    protected $fillable = [
        'hospital_id',
        'programme_id',
        'accredited_date',
        'expiry_date',
        'status',
    ];

    public function hospital() {
        return $this->belongsTo(HospitalModel::class, 'hospital_id');
    }

    public function programme() {
        return $this->belongsTo(Programme::class, 'programme_id');
    }

    static public function getHospitalProgrammes(Request $request){
        $query = self::select('hospital_programmes.*', 
                                'hospitals.name as hospital_name', 
                                'programmes.name as programme_name',
                                'countries.country_name as country_name'
                                )
                    ->join('programmes', 'programmes.id', '=', 'hospital_programmes.programme_id')
                    ->join('hospitals', 'hospitals.id', '=', 'hospital_programmes.hospital_id')
                    ->leftJoin('countries', 'hospitals.country_id', '=', 'countries.id');
 
        if($request->filled('programme_name')){
            $query->where('programmes.name','like', '%'.$request->programme_name.'%');
        }

        if($request->filled('hospital_name')){
            $query->where('hospitals.name','like', '%'.$request->hospital_name.'%');
        }

        $query->where('hospital_programmes.is_delete', '=', 0)
              ->orderBy('hospital_programmes.id', 'asc');
        
        return $query->get();
    }

    static public function exists($hospital_id) {
        return self::where('hospital_id', '=', $hospital_id)
                   ->pluck('programme_id')
                   ->toArray();
    }
    

    static public function getSingleId($id){
        return self::find($id);
    }

    // Accessors for formatted dates
    public function getAccreditedDateAttribute($value) {
        return Carbon::parse($value)->format('F Y');
    }

    public function getExpiryDateAttribute($value) {
        return Carbon::parse($value)->format('F Y');
    }
}
