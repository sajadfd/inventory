<?php
declare(strict_types=1);
namespace App\Http\Requests\User;

use App\Enums\UserType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LoginRequest extends FormRequest
{

    public function authorize(): bool
    {
        return !auth()->check();
    }

    public function rules(): array
    {
        $field = request()->has('username') ? 'username' : 'phone';
        $value = request()->input($field);
        request()->merge([$field => $value]);


        return [
            $field => ['required', 'string'],
            'password' => ['required', 'min:4',],
            'type' => ['string',Rule::in(UserType::getAllValues())],
            'device_token' => ['string'],
        ];
    }

    // this function to check if username or email is valid or no
    public function isValid($filed, $data): array
    {
        if ($filed == 'phone') {
            return ['required'];
        } else return ['required', 'string'];
    }
}
