<?php

namespace App\Http\Controllers\api;

use App\Events\MadeActivity;
use App\Events\PostUnapproved;
use App\Http\Controllers\Controller;
use App\Http\Requests\CommentRequest;
use App\Http\Requests\PostCreateRequest;
use App\Http\Requests\PostDisapprovalRequest;
use App\Http\Resources\CommentCollection;
use App\Http\Resources\CommentResource;
use App\Http\Resources\ImageResource;
use App\Http\Resources\LikeCollection;
use App\Http\Resources\LikeResource;
use App\Http\Resources\PostCollection;
use App\Http\Resources\PostResource;
use App\Http\Resources\UserResource;
use App\Models\Comment;
use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

use function PHPUnit\Framework\matches;
use function PHPUnit\Framework\returnSelf;

class PostController extends Controller
{
    //
    public function index()
    {
        event(new MadeActivity(Auth::user()));
        $posts = Post::approved()->get();
        return response(new PostCollection($posts));
    }


    public function indexUnapproved()
    {
        event(new MadeActivity(Auth::user()));
        $user = Auth::user();
        $posts = Post::unapproved()->get();
        return response(new PostCollection($posts));
    }
    public function show($id)
    {
        event(new MadeActivity(Auth::user()));
        $post = Post::approved()->findOrFail($id);
        return new PostResource($post);
    }
    public function showFromUser($userId) // shows posts of a specific user
    {
        event(new MadeActivity(Auth::user()));
        $user = User::findOrFail($userId);
        return new PostCollection($user->posts->where('is_approved', true));
    }

    public function search($query)
    {
        event(new MadeActivity(Auth::user()));

        $matches = Post::approved()->search($query)->get();
        return new PostCollection($matches);
    }

    public function searchAndOrderBy($query, $orderBy)
    {
        event(new MadeActivity(Auth::user()));
        //پارامتر دوم می تواند مقدار های زیر را داشته باشد
        // orderBy == likes-asc || likes-desc || created_at-desc || created_at-asc ||comments-desc || comments-asc
        $orderingFactor = null;
        $direction = null;
        switch ($orderBy) {
            case 'likes-asc':
                $orderingFactor = 'likes';
                $direction = 'asc';
                break;
            case 'likes-desc':
                $orderingFactor = 'likes';
                $direction = 'desc';
                break;
            case 'comments-asc':
                $orderingFactor = 'comments';
                $direction = 'asc';
                break;
            case 'comments-desc':
                $orderingFactor = 'comments';
                $direction = 'desc';
                break;
            case 'created_at-asc':
                $orderingFactor = 'created_at';
                $direction = 'asc';
                break;
            default:
                $orderingFactor = 'created_at';
                $direction = 'desc';
        }
        /**
         * @var $matches Collection
         */
        // قطعه کد زیر تصمیم می گیرد که چطور پست ها را بر اساس چه فاکتوری مرتب کند
        $matches = Post::approved()->search($query);
        $sortedMatches = $this->decideHowToSort($matches, $orderingFactor, $direction);

        return new PostCollection($sortedMatches);
    }

    public function create(PostCreateRequest $request)
    {
        event(new MadeActivity(Auth::user()));
        /**
         * @var $user User
         */
        $user = Auth::user();
        $validated = $request->validated();
        if ($user->can('create', Post::class)) {
            $post = $user->posts()->create(
                [
                    'title' => $validated['title'],
                    'body' => $validated['body']
                ]
            );
            return $post;
        } else {
            return response([
                'message' => 'شما جهت ارسال پست باید شماره موبایل خود را تنظیم کنید'
            ]);
        }
    }

    public function like($id)
    {
        event(new MadeActivity(Auth::user()));
        /**
         * @var $user User
         */

        $user = Auth::user();
        if ($user->can('like', Post::class)) {
            $post = Post::approved()->find($id);
            if ($post == null) {
                return response([
                    'message' => 'چنین پستی وجود ندارد'
                ]);
            }
            if ($user->hasLiked($post))
                return response([
                    'message' => 'این پست قبلا لایک شده'
                ]);
            $like = $user->likes()->create(['post_id' => $id]);
            return $like;
        } else {
            return response([
                'message' => 'شما برای لایک کردن باید شماره تلفن را تنظیم کرده باشید'
            ]);
        }
    }

    public function comment(CommentRequest $request, $id)
    {
        event(new MadeActivity(Auth::user()));
        $post = Post::approved()->find($id);
        if ($post == null) {
            return response([
                'message' => 'چنین پستی وجود ندارد'
            ]);
        }
        $validated = $request->validated();
        $comment = $post->comments()->create(['body' => $validated['body'], 'user_id' => Auth::user()->id]);
        return $comment;
    }

    public function commentAndLike(CommentRequest $request, $id)
    {
        $comment = $this->comment($request, $id);
        $like = $this->like($id);
        return response([
            'comment' => ($comment instanceof Comment ? new CommentResource($comment) : $comment),
            'like' => ($like instanceof Like ? new LikeResource($like) : $like)
        ]);
    }

    public function approvePost($id)
    {
        $user = Auth::user();
        $post = Post::unapproved()->where('id', $id)->first();
        if ($post != null) {
            $post->is_approved = true;
            $post->save();
            return response(new PostResource($post));
        } else {
            return response([
                'error' => [
                    'message' => __('messages.post_not_found')
                ]
            ]);
        }
    }
    public function unapprovePost(PostDisapprovalRequest $request, $id)
    {
        $user = Auth::user();
        $validated = $request->validated();
        $post = $post = Post::unapproved()->where('id', $id)->first();
        if ($post != null) {
            $post->disapproval_body = $validated['body'];
            $post->save();
            event(new PostUnapproved($post));
            return $post;
        } else {
            return response([
                'error' => [
                    'message' => __('messages.post_not_found')
                ]
            ]);
        }
    }

    private function decideHowToSort($posts, $orderingFactor, $direction)
    {

        return (($orderingFactor == 'likes') ?
            $posts->sortByLikes($direction)->get() : (($orderingFactor == 'created_at') ?
                $posts->sortByDate($direction)->get() :
                $posts->sortByComments($direction)->get()));
    }
}
