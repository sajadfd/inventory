<?php

namespace App\Http\Requests;

use App\Rules\ImageOrValidSrcString;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => ['string', 'max:20'],
            'last_name' => ['string', 'max:20'],
            'address' => ['nullable', 'string',],
            'national_identification_number' => ['nullable', 'string', 'max:50'],
            'image' => ['nullable', new ImageOrValidSrcString],
        ];
    }
}
