<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDeliveryRequest extends FormRequest
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
            'status' => 'required|in:pending,ready_to_picked,ready_to_shipped,delivered',
            'order_id' => 'required|exists:orders,id'
        ];
    }
}
