<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendGiftRequest extends FormRequest
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
            'volume' => 'required|min:1|integer',
            'sender_user_id' => 'required|integer|exists:users,id',
            'recieved_user_id' => 'required|integer|exists:users,id'
        ];
    }
}
