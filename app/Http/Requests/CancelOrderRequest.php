<?php

namespace App\Http\Requests;

use App\Models\PurchaseList;
use App\Traits\FailedValidationTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;

class CancelOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'cancellation_reason' => ['required', 'string'],
        ];
    }
}
