<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\ValidatorAwareRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ImageOrValidSrcString implements ValidationRule, ValidatorAwareRule
{
    protected \Illuminate\Validation\Validator $validator;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value) && file_exists(public_path($modifiedValue = Str::remove(config('app.url'), $value)))) {
            $this->validator->setValue($attribute, $modifiedValue);
        } else if (!Validator::make([$attribute => $value], [$attribute => 'image'])->validate()) {
            $fail(__('The :attribute must be an image', ['attribute' => __($attribute)]));
        }

    }

    public function setValidator(\Illuminate\Validation\Validator $validator)
    {
        $this->validator = $validator;
        return $this;
    }
}
