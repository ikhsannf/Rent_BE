<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ListingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'lender_id'      => $this->user_id,
            'category_id'    => $this->category_id,
            'title'          => $this->title,
            'description'    => $this->description,
            'price_per_day'  => (float) $this->price_per_day,
            'deposit'        => (float) $this->deposit,
            'condition'      => $this->condition,
            'status'         => $this->status,
            'lender_name'    => $this->lender->name,
            'lender_avatar'  => $this->lender->avatar_url,
            'lender_rating'  => (float) $this->lender->rating,
            'category_name'  => $this->category->name,
            'photos'         => $this->photos->pluck('url')->toArray(),
            'average_rating' => (float) $this->average_rating,
            'review_count'   => (int) $this->review_count,
            'location'       => $this->location,
        ];
    }
}
