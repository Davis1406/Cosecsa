<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CollegeLetterheadSetting extends Model
{
    protected $fillable = ['institution_name', 'address_text', 'footer_text', 'logo_path', 'watermark_path', 'updated_by'];

    public static function current(): self
    {
        return static::first() ?? static::create([]);
    }
}
