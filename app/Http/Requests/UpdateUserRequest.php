<?php

namespace App\Http\Requests;

use App\Enums\PermissionEnum;
use App\Enums\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function rules()
    {
        return [
            'username' => [Rule::unique('users', 'username')->ignore($this->user?->id)],
            'phone' => [Rule::unique('users', 'phone')->ignore($this->user?->id), 'digits_between:10,11'],
            'code' => ['string', 'max:2', function ($att, $val, $fail) {
                if (!isValidPhoneNumber(request('phone'), $val)) {
                    $fail(__("Incorrect Phone Code"));
                }
            }],
            'password' => 'min:4',
            'type' => [Rule::excludeIf(in_array(request('type'), ['driver', 'customer'])), Rule::in(['inventory_admin', 'super_admin'])],
            'my_password' => ['required', 'current_password', 'exclude'],
            'is_active' => ['boolean']
        ];
    }
}
