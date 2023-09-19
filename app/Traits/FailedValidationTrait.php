<?php

namespace App\Traits;

use App\Http\ApiResponse;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

trait FailedValidationTrait{
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(ApiResponse::error($validator->errors(),422));
    }
}
