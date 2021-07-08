<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    const ROLE_ADMIN_TITLE = 'Admin';
    const ROLE_USER_TITLE = 'User';

    use HasFactory;
    protected $guarded = [];

    // Scopes
    public function scopeAdmins($query)
    {
        return $query->where('title', self::ROLE_ADMIN_TITLE);
    }
    public function scopeUsersOnly($query)
    {
        return $query->where('title', self::ROLE_USER_TITLE);
    }

    // Relationships
    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    // Utility functions
    public static function getRoleId($roleTitle){ // a function that returns the ID of a specific role
        return self::where('title',$roleTitle)->first()->id;
    }
}
