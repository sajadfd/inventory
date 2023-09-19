<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDriverRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'name' => ['required' , 'string'] ,
            'address' => ['required' , 'string' , 'max:255'] ,
            'phone' => ['required' , 'string'] ,
            'image' => ['required' ,'image' ,'mimes:jpeg,png,jpg,gif,svg' , 'max:2048'] ,
        ];
    }
}
