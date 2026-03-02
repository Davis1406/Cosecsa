<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FellowLabel extends Model
{
    protected $table = 'fellow_labels';

    protected $fillable = ['name', 'color', 'description', 'is_active'];

    public function fellows()
    {
        return $this->belongsToMany(FellowsModel::class, 'fellow_label_assignments', 'label_id', 'fellow_id');
    }
}
