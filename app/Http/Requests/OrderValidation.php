<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class OrderValidation extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'delivery_address' => 'required|string',
            'note' => 'required|string',
        ];
    }

    // Phải có đuưa ra thông báo lỗi
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([$validator->errors()], 402));

    }
}
