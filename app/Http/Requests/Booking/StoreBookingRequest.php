<?php

namespace App\Http\Requests\Booking;

use App\Models\Listing;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'listing_id' => 'required|exists:listings,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'notes'      => 'nullable|string|max:500',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($validator->errors()->any()) return;

            $listing = Listing::find($this->listing_id);
            $start = Carbon::parse($this->start_date);
            $end = Carbon::parse($this->end_date);

            if ($listing && $listing->isBookedBetween($start, $end)) {
                $validator->errors()->add('listing_id', 'Barang sudah dipesan pada tanggal tersebut.');
            }
        });
    }
}
