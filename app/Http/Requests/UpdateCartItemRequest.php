<?php

namespace App\Http\Requests;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCartItemRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'product_id' => ['required', Rule::exists('products', 'id')->where('is_active', true)->where('is_visible_in_store', true)],
            'count' => ['required', 'numeric', function ($attribute, $value, $fail) {
                try {
                    $product = Product::findOrFail(request()->product_id);
                }catch (\Exception $exception){
                    $fail(__('Product not found'));
                }
                if ($product->store < $value) {
                    $fail(__('This quantity is not available, The quantity in stock is :count', [$product->store]));
                }
            }],
            'notes' => ['string'],
        ];
    }

}
