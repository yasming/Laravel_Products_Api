<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $productId = null;
        if($this->getMethod() == 'PUT') $productId = request()->route('product')->id;
        return [
            'name'        => "required|string|unique:products,name,{$productId}",
            'price'       => 'required|numeric|min:1',
            'category_id' => 'required|integer|exists:categories,id',
        ];
    }
}
