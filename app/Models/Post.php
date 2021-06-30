<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Post extends Model
{
    use HasFactory;
    protected $guarded=[];

    public function scopeSearch($query,$searchQuery)
    {
        return $query->where('title','LIKE','%'.$searchQuery.'%')->
        orWhere('body','LIKE','%'.$searchQuery.'%');
    }
    public function scopeSortByLikes($query,$direction){
        return $query->withCount('likes')
            ->orderBy('likes_count',$direction);
    }

    public function scopeSortByComments($query, $direction)
    {
        return $query->withCount('comments')
            ->orderBy('comments_count',$direction);
    }

    public function scopeSortByDate($query,$direction)
    {
        return $query->orderBy('created_at',$direction);
    }
    public function user()
    {
        return  $this->belongsTo(User::class);
    }

    public function comments()
    {
        return  $this->hasMany(Comment::class);
    }
    public function likes()
    {
        return  $this->hasMany(Like::class);
    }
}
