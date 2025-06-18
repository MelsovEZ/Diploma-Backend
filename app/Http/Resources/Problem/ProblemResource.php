<?php

namespace App\Http\Resources\Problem;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProblemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = [
            'problem_id' => $this->problem_id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'description' => $this->description,
            'category' => [
                'id' => optional($this->category)->id,
                'name' => optional($this->category)->name,
            ],
            'location' => [
                'district' => [
                    'id' => optional($this->district)->id,
                    'name' => optional($this->district)->name,
                ],
                'city' => [
                    'id' => optional($this->city)->id,
                    'name' => optional($this->city)->name,
                ],
                'address' => $this->address,
            ],
            'status' => $this->status,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => optional($this->updated_at)?->format('Y-m-d H:i:s'),
            'photos' => $this->photos->pluck('photo_url'),
            'likes' => [
                'liked_by_user' => $this->likes()->where('user_id', auth()->id())->exists(),
                'count' => $this->likes()->count(),
            ],
            'comments_count' => $this->comments()->count(),
            'user' => [
                'name' => optional($this->user)->name,
                'surname' => optional($this->user)->surname,
                'email' => optional($this->user)->email,
                'avatar' => optional($this->user)->photo_url,
            ],
        ];

        return $data;
    }
}

