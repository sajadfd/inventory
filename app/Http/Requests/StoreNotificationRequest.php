<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreNotificationRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'user_id' => ['required', Rule::exists('users', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:255'],
            'push' => ['boolean']
        ];
    }
}
