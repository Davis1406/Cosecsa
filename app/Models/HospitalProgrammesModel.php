<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class HospitalProgrammesModel extends Model
{
    use HasFactory;

    protected $table = 'hospital_programmes';

    protected $fillable = [
        'hospital_id',
        'programme_id',
        'status',
    ];

    public function hospital() {
        return $this->belongsTo(HospitalModel::class, 'hospital_id');
    }

    public function programme() {
        return $this->belongsTo(Programme::class, 'programme_id');
    }

    static public function getHospitalProgrammes(Request $request){
        $query = self::select('hospital_programmes.*', 'hospitals.name as hospital_name', 'programmes.name as programme_name')
                    ->join('programmes', 'programmes.id', '=', 'hospital_programmes.programme_id')
                    ->join('hospitals', 'hospitals.id', '=', 'hospital_programmes.hospital_id');
 
                    if($request->filled('programme_name')){

                        $query->where('programmes.name','like', '%'.$request->programme_name.'%');
                    }
    
                    if($request->filled('hospital_name')){
    
                        $query->where('hospitals.name','like', '%'.$request->hospital_name.'%');
                    }

                    $query -> where('hospital_programmes.is_delete', '=', 0)
                           ->orderBy('hospital_programmes.id', 'asc');
                    
                  return $query->paginate(10)->appends($request-> except('page'));
    }

    static public function exists($hospital_id, $programme_id) {
        return self::where('hospital_id', '=', $hospital_id)
                    ->where('programme_id', '=', $programme_id)
                    ->first();
    }


    static public function getSingleId($id){

        return self::find($id);
      
    }
}
