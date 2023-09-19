<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
        //TODO: Policy add App Key.
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', Rule::unique('users', 'phone'),
                function ($att, $val, $fail) {
                    if (!isValidPhoneNumber($val, request('code'))) {
                        $fail(__("Incorrect Phone Code"));
                    }
                }],
            'username' => ['required', 'string', Rule::unique('users', 'username')],
            'password' => ['string', 'required', 'min:4'],
            'code' => ['string', 'max:2', 'required'],
            'first_name' => 'nullable|string',
            'last_name' => 'nullable|string',
            'national_identification_number' => 'nullable|string',
            'address' => 'nullable|string',
            'device_token' => 'string',
        ];
    }
}
