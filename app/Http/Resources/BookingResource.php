<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'booking_code'      => $this->booking_code,
            'borrower_id'       => $this->borrower_id,
            'listing_id'        => $this->listing_id,
            'start_date'        => $this->start_date->format('Y-m-d'),
            'end_date'          => $this->end_date->format('Y-m-d'),
            'total_days'        => (int) $this->total_days,
            'total_price'       => (float) $this->total_price,
            'status'            => $this->status,
            'notes'             => $this->notes,
            'listing_title'     => $this->listing->title,
            'listing_photo'     => $this->listing->primaryPhoto ? $this->listing->primaryPhoto->url : null,
            'lender_name'       => $this->lender->name,
            'borrower_name'     => $this->borrower->name,
            'payment_status'    => $this->payment_status,
            'borrower_reviewed' => (bool) $this->borrower_reviewed,
            'lender_reviewed'   => (bool) $this->lender_reviewed,
            'created_at'        => $this->created_at->toIso8601String(),
        ];
    }
}
