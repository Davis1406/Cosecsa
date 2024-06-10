<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use HasFactory;

    protected $table = 'countries';

    static public function getCountry(){

        $return = Country:: select('countries.*')
        ->orderBy('countries.id', 'asc')
        ->get();

        return $return;
 } 

}