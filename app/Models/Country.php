<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Country extends Model
{
    use HasFactory;

    protected $table = 'countries';

    // Countries barely ever change — cache the full list instead of hitting
    // the DB on every page (this lookup is loaded on almost every
    // add/edit/view form across the app).
    static public function getCountry(){

        return Cache::remember('lookup:countries', 3600, function () {
            return Country::select('countries.*')
                ->orderBy('countries.id', 'asc')
                ->get();
        });
    }

}