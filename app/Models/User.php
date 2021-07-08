<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

// use Spatie\Permission\Traits\HasRoles;
// use Spatie\Permission\Models\Role;
// use Spatie\Permission\Models\Permission;

class User extends Authenticatable implements MustVerifyEmail

{
    use HasApiTokens, HasFactory, Notifiable;

    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'skills'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function avatar()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function emailVerificationToken()
    {
        return $this->hasOne(EmailVerificationToken::class);
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    //Utility functions
    public function hasLiked(Post $post): bool
    {
        return !$post->likes->where('user_id', $this->id)->isEmpty();
    }
    public function isRole($roleTitle){ // checks if the user has the specified role
        $hasRole = !($this->roles->where('title',$roleTitle)->isEmpty());
        return $hasRole;
    }
}
