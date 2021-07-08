<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'body' => $this->body,
            'created_at' => $this->created_at,
            'is_approved' => $this->is_approved,
            'author' => [
                'profile' => new UserResource($this->user),
                'avatar' => new ImageResource($this->user->avatar),
            ],
            'likes_count' => $this->likes()->count(),
            'likes' => new LikeCollection($this->likes),
            'comments_count' => $this->comments()->count(),
            'comments' => new CommentCollection($this->comments)
        ];
    }
}
