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
            'category' => [
                'id' => $this->category->id ?? null,
                'name' => $this->category->name ?? null,
            ],
            'city' => [
                'id' => $this->city->id ?? null,
                'name' => $this->city->name ?? null,
            ],
            'district' => [
                'id' => $this->district->id ?? null,
                'name' => $this->district->name ?? null,
            ],
            'status' => $this->status,
            'location_lat' => $this->location_lat,
            'location_lng' => $this->location_lng,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
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

