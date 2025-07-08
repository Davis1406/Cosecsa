<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'role_type', 'is_active'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get role name
     */
    public function getRoleName()
    {
        $roleNames = [
            1 => 'Admin',
            2 => 'Trainee',
            3 => 'Candidate',
            4 => 'Trainer',
            5 => 'Country Representative',
            7 => 'Fellow',
            8 => 'Member',
            9 => 'Examiner'
        ];

        return $roleNames[$this->role_type] ?? 'Unknown Role';
    }
}
