<?php

namespace App\Http\Resources\Problem;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProblemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'problem_id' => $this->problem_id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'category_name' => optional($this->category)->name,
            'status' => $this->status,
            'location_lat' => $this->location_lat,
            'location_lng' => $this->location_lng,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'photo_urls' => $this->photos->pluck('photo_url'),
            'likes_count' => $this->likes()->count(),
            'comments_count' => $this->comments()->count(),
            'user' => [
                'name' => $this->user->name,
                'surname' => $this->user->surname,
                'email' => $this->user->email,
                'photo_url' => $this->user->photo_url,
            ]
        ];
    }
}

