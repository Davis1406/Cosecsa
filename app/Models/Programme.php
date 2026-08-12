<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

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

    // Active programme list — used on nearly every associate add/edit/view
    // page. Rarely changes, so cache it instead of re-querying every time.
    static public function getProgramme(){

        return Cache::remember('lookup:programmes', 3600, function () {
            return Programme::select('programmes.*')
                ->where('programmes.is_deleted', '=', 0)
                ->orderBy('programmes.id', 'asc')
                ->get();
        });
    }

  
}
