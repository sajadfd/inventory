<?php

namespace App\Http\Requests;

use App\Enums\PermissionEnum;
use App\Rules\ImageOrValidSrcString;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCategoryRequest extends FormRequest
{

    public function authorize()
    {
        return $this->user()->hasPermissionTo(PermissionEnum::CREATE_CATEGORIES);
    }

    public function rules()
    {
        return [
            'name' => [Rule::requiredIf(!$this->category),'string', Rule::unique('categories', 'name')->ignore($this->category?->id)],
            'image' => [new ImageOrValidSrcString],
            'is_active' => 'boolean',
        ];
    }

}
