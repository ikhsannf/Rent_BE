<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'booking_id' => $this->booking_id,
            'reviewer'   => [
                'id'     => $this->reviewer->id,
                'name'   => $this->reviewer->name,
                'avatar' => $this->reviewer->avatar_url,
            ],
            'rating'     => (int) $this->rating,
            'comment'    => $this->comment,
            'type'       => $this->type,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
