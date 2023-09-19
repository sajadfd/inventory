<?php

namespace App\Rules;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;

class DateParameterFormatterRule implements ValidationRule, ValidatorAwareRule
{

    protected \Illuminate\Validation\Validator $validator;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        try {
            $this->validator->setValue($attribute,Carbon::make($value)->format('Y-m-d H:i:s'));
        } catch (\Exception $exception) {
            $fail(__('validation.date', ['attribute' => __('validation.attributes.'.$attribute)]));
        }
    }


    public function setValidator(\Illuminate\Validation\Validator $validator)
    {
        $this->validator = $validator;
        return $this;
    }
}
