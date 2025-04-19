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
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
            'photo_urls' => $this->photos->pluck('photo_url'),
            'liked_by_user' => $this->likes()->where('user_id', auth()->id())->exists(),
            'likes_count' => $this->likes()->count(),
            'comments_count' => $this->comments()->count(),
            'user' => [
                'name' => $this->user->name,
                'surname' => $this->user->surname,
                'email' => $this->user->email,
                'avatar' => $this->user->photo_url,
            ]
        ];

        if (in_array($this->status, ['in_review', 'done', 'declined']) && $this->report) {
            $data['report'] = [
                'description' => $this->report->description,
                'assigned_at' => Carbon::make($this->report->assigned_at)?->format('Y-m-d H:i:s'),
                'submitted_at' => Carbon::make($this->report->submitted_at)?->format('Y-m-d H:i:s'),
                'confirmed_at' => Carbon::make($this->report->confirmed_at)?->format('Y-m-d H:i:s'),
                'moderator' => $this->report->moderator ? [
                    'id' => $this->report->moderator->id,
                    'name' => $this->report->moderator->name,
                    'surname' => $this->report->moderator->surname,
                    'email' => $this->report->moderator->email,
                    'avatar' => $this->report->moderator->photo_url,
                ] : null,
                'photos' => $this->report->photos->pluck('photo_url'),
            ];
        }


        return $data;
    }
}

