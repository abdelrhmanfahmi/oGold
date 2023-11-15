<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BuyGoldRequest extends FormRequest
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
            'payment_type' => 'required|in:cash,visa',
            'address' => 'required|min:5|max:100',
            'user_id' => 'required|integer|exists:users,id',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|integer|exists:products,id',
            'products.*.quantity' => 'required|integer',
        ];
    }
}
