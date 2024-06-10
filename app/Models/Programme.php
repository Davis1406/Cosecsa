<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Programme extends Model
{
    use HasFactory;

    protected $table = 'programmes';

    static public function getRecord(){
         
        $return = Programme:: select('programmes.*')
        ->where('programmes.is_deleted', '=', 0)
        -> orderBy('programmes.id', 'asc')
        ->paginate(20);

        return $return;

    }

    static public function getSingleId($id){

        return self::find($id);
      
    }

    static public function getProgramme(){
        
        $return = Programme:: select('programmes.*')
        ->where('programmes.is_deleted', '=', 0)
        -> orderBy('programmes.id', 'asc')
        ->get();

        return $return;

    }

  
}
