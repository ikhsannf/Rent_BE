<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => 'nullable|string|max:100',
            'phone'     => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]+$/'],
            'address'   => 'nullable|string|max:255',
            'avatar'    => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'fcm_token' => 'nullable|string',
        ];
    }
}
