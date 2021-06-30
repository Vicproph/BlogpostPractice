<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use phpDocumentor\Reflection\Types\This;

class Like extends Model
{
    use HasFactory;
    protected $guarded=[];
    public function post()
    {
        return  $this->belongsTo(Post::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Utils
    public static function cleanupRedundancies(){// بعد از اجرای سیدر دیتابیس در فکتوری لایک، ممکن است توسط یک کاربر چند لایک بر روی یک پست بخورد
        $alreadyTaken =[];
        foreach (Like::all() as $like){
            if (in_array(['user_id'=>$like->user->id,'post_id'=>$like->post->id],$alreadyTaken))
                $like->delete();
            $alreadyTaken[] = ['user_id'=>$like->user->id,'post_id'=>$like->post->id];
        }
    }
}
