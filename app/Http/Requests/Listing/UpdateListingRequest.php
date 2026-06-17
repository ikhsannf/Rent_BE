<?php

namespace App\Http\Requests\Listing;

use Illuminate\Foundation\Http\FormRequest;

class UpdateListingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id'   => 'nullable|exists:categories,id',
            'title'         => 'nullable|string|min:5|max:200',
            'description'   => 'nullable|string|min:20',
            'price_per_day' => 'nullable|numeric|min:1000|max:10000000',
            'deposit'       => 'nullable|numeric|min:0',
            'condition'     => 'nullable|in:new,good,fair',
            'status'        => 'nullable|in:available,unavailable',
            'location'      => 'nullable|string|max:100',
            'brand'         => 'nullable|string|max:100',
            'model'         => 'nullable|string|max:100',
            'min_rent_days' => 'nullable|integer|min:1|max:30',
            'max_rent_days' => 'nullable|integer|min:1|gte:min_rent_days',
            'photos'        => 'nullable|array|max:5',
            'photos.*'      => 'image|mimes:jpg,jpeg,png,webp|max:2048',
        ];
    }
}
