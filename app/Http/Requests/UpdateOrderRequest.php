<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'payment_type' => 'in:cash,visa',
            'address' => 'min:5|max:100',
            'user_id' => 'integer|exists:users,id',
            'products' => 'array|min:1',
            'products.*.product_id' => 'integer|exists:products,id',
            'products.*.quantity' => 'integer',
        ];
    }
}
