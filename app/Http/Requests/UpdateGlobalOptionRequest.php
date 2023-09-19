<?php

namespace App\Http\Requests;

use App\Enums\GlobalOptionEnum;
use Illuminate\Foundation\Http\FormRequest;

class UpdateGlobalOptionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'value' => match ($this->global_option?->name) {
                GlobalOptionEnum::CurrencyValue => 'numeric:gt:0',
                GlobalOptionEnum::PaginatorPerPage => 'numeric:gte:-1',
                GlobalOptionEnum::HeaderImage, GlobalOptionEnum::FooterImage => 'nullable|image',
                default => 'nullable',
            }
        ];
    }
}
