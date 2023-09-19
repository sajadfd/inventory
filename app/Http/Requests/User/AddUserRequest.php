<?php

namespace App\Http\Requests\User;

use App\Enums\PermissionEnum;
use App\Enums\UserType;
use App\Http\ApiResponse;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class AddUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasPermissionTo(PermissionEnum::UPDATE_USERS);
    }

    public function rules(): array
    {
        return [
                'phone' => ['required', 'numeric', Rule::unique('users', 'phone'),
                    function ($att, $val, $fail) {
                        if (!isValidPhoneNumber($val, request('code'))) {
                            $fail(t('Incorrect Phone Code'));
                        }// /if
                    }],
                'username' => ['required', 'string', Rule::unique('users', 'username')],
                'password' => ['required', 'min:4'],
                'type' => ['required', function ($att, $val, $fail) {
                    $state = UserType::tryFrom($val);
                    if (!$state) $fail(__('This user type is not allowed'));
                }],
                'code' => ['string', 'required', 'max:2'],
            ] + (request('type') === 'customer' ? [
                'customer_id' => ['required', Rule::exists('customers', 'id')->whereNull('user_id')]
            ] : [])
            + (request('type') === 'driver' ? [
                'driver_id' => [Rule::exists('drivers', 'id')->whereNull('user_id')],
                'driver_name' => ['string'],
                'driver_address' => ['address'],
            ] : []);
    }
}
