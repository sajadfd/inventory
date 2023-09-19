<?php

namespace Modules\HR\Http\Requests;

use App\Rules\DateParameterFormatterRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\HR\Entities\Installment;
use Modules\HR\Entities\InstallmentPayment;
use Modules\HR\Enums\SalaryCurrencyEnum;

class InstallmentPaymentRequest extends FormRequest
{
    protected function getInstallmentIdRules()
    {
        return [
            'required',
            'exists:installments,id',
            function ($attribute, $value, $fail) {
                $penaltyExist = Installment::where('penalty_id', '!=', null)->where('id', $value)->exists();
                if ($penaltyExist)
                    $fail(__('The installment contains a penalty that cannot be added to it'));
            }
        ];
    }

    protected function getPayedAtRules()
    {
        $rules = [
            'required',
            new DateParameterFormatterRule()
        ];
        return $rules;
    }

    protected function getTotalPaymentForInstallment()
    {
        $rules = [
            'required', 'numeric', 'min:1',
            function ($attribute, $value, $fail) {
                $totalPrice = InstallmentPayment::where('installment_id', $this->installment_id)->sum('price');
                $PriceOutside = Installment::where('id', request('installment_id'))
                    ->where(function ($query) use ($value, $totalPrice) {
                        $query->where('price', '<', $totalPrice + $value);
                    })->exists();

                if ($PriceOutside) {
                    $fail(__(key: 'The amount entered is greater than the amount of the installment'));
                }
            }
        ];
        return $rules;
    }
    public function rules()
    {
        switch ($this->getMethod()) {
            case 'POST':
                return [
                    'installment_id' => $this->getInstallmentIdRules(),
                    'price' => $this->getTotalPaymentForInstallment(),
                    'currency' => [Rule::in(SalaryCurrencyEnum::getAllValues())],
                    'payed_at' => $this->getPayedAtRules(),
                ];
            case 'PUT':
                return [
                    'installment_id' => $this->getInstallmentIdRules(),
                    'price' => [
                        'required', 'numeric', 'min:1',
                        function ($attribute, $value, $fail) {
                            $id = request()->segment(count(request()->segments()));
                            $totalPrice = InstallmentPayment::where('id', '!=', $id)
                                ->where('installment_id', $this->installment_id)
                                ->sum('price');
                            $PriceOutside = Installment::where('id', request('installment_id'))
                                ->where(function ($query) use ($value, $totalPrice) {
                                    $query->where('price', '<', $totalPrice + $value);
                                })->exists();
                            if ($PriceOutside) {
                                $fail(__(key: 'The amount entered is greater than the amount of the installment'));
                            }
                        }
                    ],
                    'currency' => [Rule::in(SalaryCurrencyEnum::getAllValues())],
                    'payed_at' => $this->getPayedAtRules(),
                ];

            default:
                return [];
        }
    }

    public function authorize()
    {
        return true;
    }
}
