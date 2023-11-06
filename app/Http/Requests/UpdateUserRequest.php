<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
            'name' => 'min:2|max:100|string',
            'surname' => 'min:2|max:100|string',
            'email' => 'min:3|max:50|email|unique:users,email,'. $this->id,
            'password' => 'min:7|confirmed',
            'phone' => 'numeric',
            'dateOfBirth' => 'date',
            'country' => 'string',
            'state' => 'string',
            'city' => 'string',
            'address' => 'nullable|string',
            'bankName' => 'nullable|string',
            'bankAddress' => 'nullable|string',
            'bankSwiftCode' => 'nullable|string',
            'bankAccount' => 'nullable|string',
            'accountName' => 'nullable|string',
            'type' => 'min:5|max:10|in:admin,client,provider'
        ];
    }
}
