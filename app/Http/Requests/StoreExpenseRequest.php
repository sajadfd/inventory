<?php

namespace App\Http\Requests;

use App\Enums\ExpenseSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreExpenseRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            "description" => ['required', 'string'],
            "price" => ['required', 'numeric'],
            "date" => ['required','date'],
            'source' => ['required', Rule::enum(ExpenseSource::class)],
        ];
    }
}
