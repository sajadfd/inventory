<?php

namespace App\Http\Requests\User;

use App\Enums\PermissionEnum;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePermissionRequest extends FormRequest
{

    public function authorize(): bool
    {
        return auth()->user()->hasPermissionTo(PermissionEnum::UPDATE_PERMISSIONS);
    }

    public function rules()
    {
        return [
            'permission' => ['required',Rule::exists('permissions', 'name')],
            'allow' => ['boolean']
        ];
    }

}
