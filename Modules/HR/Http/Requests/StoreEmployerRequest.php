<?php

namespace Modules\HR\Http\Requests;

use App\Rules\ImageOrValidSrcString;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployerRequest extends FormRequest
{
    public function rules()
    {
        return [
            "name" => ["required", "string", Rule::unique('employers', 'name')->ignore($this->employer?->id)],
            "phone" => ["required", "string", Rule::unique('employers', 'phone')->ignore($this->employer?->id)],
            "address" => ["nullable", "string"],
            'image' => ['nullable', new ImageOrValidSrcString],
            'is_active' => "boolean",
        ];
    }
}
