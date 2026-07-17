<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['key', 'module', 'label'];
    public $timestamps = true;

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }
}
