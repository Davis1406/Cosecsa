<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Request;

class HospitalModel extends Model
{
    use HasFactory;

    protected $table ='hospitals';

    static public function getRecord(){
        $return = HospitalModel::select('hospitals.*', 'countries.country_name as country_name')
                    ->join('countries', 'countries.id', 'hospitals.country_id')
                    ->where('hospitals.is_deleted', '=', 0);
    
        if (!empty(Request::get('country_name'))) {
            $return = $return->where('countries.country_name', 'like', '%' . Request::get('country_name') . '%');
        }
    
        if (!empty(Request::get('hospital_name'))) {
            $return = $return->where('hospitals.name', 'like', '%' . Request::get('hospital_name') . '%');
        }
    
        $return = $return->orderBy('hospitals.id', 'asc')->get();
    
        return $return;
    }
    

    static public function getSingleId($id){

        return self::find($id);
      
    }

    // Full active-hospital list — used to populate the hospital dropdown on
    // nearly every trainee/candidate/trainer add/edit/view page. Rarely
    // changes, so cache it instead of re-joining on every request.
    static public function getHospital(){

        return Cache::remember('lookup:hospitals', 3600, function () {
            return HospitalModel::select('hospitals.*', 'countries.country_name as country_name')
                ->join('countries', 'countries.id', 'hospitals.country_id')
                ->where('hospitals.is_deleted', '=', 0)
                ->orderBy('hospitals.name', 'asc')
                ->get();
        });
    }

}
