<?php

namespace App\Http\Requests\Booking;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status'           => 'required|in:approved,rejected,ongoing,completed,cancelled,disputed',
            'rejection_reason' => 'required_if:status,rejected|nullable|string|max:500',
        ];
    }
}
