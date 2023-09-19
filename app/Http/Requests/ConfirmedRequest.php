<?php

namespace App\Http\Requests;

use App\Models\PurchaseList;
use App\Traits\FailedValidationTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;

class ConfirmedRequest extends FormRequest
{
    use FailedValidationTrait ;
    public function authorize(): bool
    {
        return true;
    }// /authorize

    public function rules(): array
    {
        $id =\Illuminate\Support\Facades\Route::current()->parameter('id');

        return [
            'confirmed'=>['required','boolean',function($att,$val,$fail) use($id){
                if(PurchaseList::where('id', $id)->first()->confirmed){
                    $fail(t('confirmed'));
                }// /if
            }]
        ];// /return
    }// /rules
}
