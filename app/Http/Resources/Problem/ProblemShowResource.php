<?php

namespace App\Http\Resources\Problem;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProblemShowResource extends JsonResource
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
                'city' => [
                    'id' => optional($this->city)->id,
                    'name' => optional($this->city)->name,
                ],
                'district' => [
                    'id' => optional($this->district)->id,
                    'name' => optional($this->district)->name,
                ],
                'address' => $this->address,
                'coordinates' => [
                    'lat' => $this->location_lat,
                    'lng' => $this->location_lng,
                ],
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
            'admin_comment' => $this->admin_comment,
        ];



        if (in_array($this->status, ['in_progress', 'in_review', 'done', 'declined']) && $this->report) {
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

