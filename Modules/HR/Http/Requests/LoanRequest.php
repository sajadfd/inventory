<?php

namespace Modules\HR\Http\Requests;

use App\Rules\DateParameterFormatterRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\HR\Entities\Contract;
use Modules\HR\Enums\PaymentMethodEnum;
use Modules\HR\Enums\SalaryCurrencyEnum;

class LoanRequest extends FormRequest
{


    public function rules()
    {
        switch ($this->getMethod()) {
            case 'POST': // Create
                return [
                    'contract_id' => ['required', Rule::exists('contracts', 'id')->where('is_active', true)->where(fn ($q) => $q->where('end_date', '>=', now()))],
                    'price' => 'required|numeric|min:1',
                    'profit_percent' => 'required|numeric|min:0',
                    'currency' => [Rule::in(SalaryCurrencyEnum::getAllValues())],
                    'duration' => 'required|int|min:1',
                    'payment_method' => [Rule::in(PaymentMethodEnum::getAllValues())],
                    'date' => ['required', new DateParameterFormatterRule(), function ($attribute, $value, $fail) {
                        $dateOutsideContract = Contract::where('id', request('contract_id'))
                            ->where(function ($query) use ($value) {
                                $query->where('start_date', '>', $value)
                                    ->orWhere('end_date', '<', $value);
                            })->exists();
                        if ($dateOutsideContract) {
                            $fail(__(key: 'Date outside contract date range'));
                        }
                    }]
                ];
            case 'PUT': // Update
                return [
                    'contract_id' => ['required', Rule::exists('contracts', 'id')->where('is_active', true)->where(fn ($q) => $q->where('end_date', '>=', now()))],
                    'price' => 'required|numeric|min:1',
                    'profit_percent' => 'required|numeric|min:0',
                    'currency' => [Rule::in(SalaryCurrencyEnum::getAllValues())],
                    'duration' => 'required|int|min:1',
                    'payment_method' => [Rule::in(PaymentMethodEnum::getAllValues())],
                    'date' => ['required', new DateParameterFormatterRule(), function ($attribute, $value, $fail) {
                        $dateOutsideContract = Contract::where('id', request('contract_id'))
                            ->where(function ($query) use ($value) {
                                $query->where('start_date', '>', $value)
                                    ->orWhere('end_date', '<', $value);
                            })->exists();
                        if ($dateOutsideContract) {
                            $fail(__(key: 'Date outside contract date range'));
                        }
                    }]
                ];
            case 'DELETE':
                return [
                    'loan_id' => 'required|exists:loans,id',
                ];
            default: // Other methods
                return [];
        }
    }


    public function authorize()
    {
        return true;
    }
}
