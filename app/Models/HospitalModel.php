<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Request;

class HospitalModel extends Model
{
    use HasFactory;

    protected $table ='hospitals';

    static public function getRecord(){

        $return = HospitalModel:: select('hospitals.*', 'countries.country_name as country_name')
                ->join('countries', 'countries.id', 'hospitals.country_id')
                ->where('hospitals.is_deleted', '=', 0);

                if(!empty(Request::get('country_name'))){

                    $return =$return->where('countries.country_name','like', '%'.Request::get('country_name').'%');
                }

                if(!empty(Request::get('hospital_name'))){

                    $return =$return->where('hospitals.name','like', '%'.Request::get('hospital_name').'%');
                }
              $return= $return ->orderBy('hospitals.id', 'asc') ->paginate(20);

        return $return;
    }

    static public function getSingleId($id){

        return self::find($id);
      
    }

    static public function getHospital(){

        $return = HospitalModel:: select('hospitals.*', 'countries.country_name as country_name')
        ->join('countries', 'countries.id', 'hospitals.country_id')
        ->where('hospitals.is_deleted', '=', 0)
        ->orderBy('hospitals.name', 'asc')
        ->get();

        return $return;

    }

}
