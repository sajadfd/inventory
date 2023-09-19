<?php

namespace App\Http\Requests;

use App\Enums\UserType;
use App\Models\Cart;
use App\Models\Product;
use App\Traits\FailedValidationTrait;
use Auth;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

use function PHPUnit\Framework\isNull;

class StoreCartItemRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'product_id' => ['required', Rule::exists('products', 'id')->where('is_active',true)->where('is_visible_in_store', true)],
            'count' => ['required', 'numeric', function ($attribute, $value, $fail) {
                $product = Product::findOrFail(request()->product_id);
                if($product->store < $value){
                    //TODO Translate
                   $fail(__('This quantity is not available, the quantity in stock is'). ' ' . $product->store);
                }
            }],
            'notes' => ['nullable', 'string'],
            'user_id' => ['nullable', Rule::exists('users', 'id')->where('type', UserType::Customer->value)],
        ];
    }

    public function validated($key = null, $default = null)
    {

        if (request()->user_id) {
            $user_id = request()->user_id;
        } else {
            $user_id = Auth()->id();
        }

        $cart = Cart::firstOrCreate(['user_id' => $user_id]);

        return [
            'product_id'  => $this->product_id,
            'count'       => $this->count,
            'notes'       => $this->notes,
            'cart_id'     => $cart->id,
        ];
    }
}
