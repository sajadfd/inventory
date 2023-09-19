<?php

namespace App\Http\Requests;

use App\Models\PurchaseList;
use App\Traits\FailedValidationTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;

class ConfirmCurrentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'end_latitude'   => ['required_with:end_longitude,end_address' , 'string'],
            'end_longitude'  => ['required_with:end_latitude,end_address' , 'string'],
            'end_address'    => ['required_with:end_longitude,end_latitude' , 'string'],
        ];
    }
}
