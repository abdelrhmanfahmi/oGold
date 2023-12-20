<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBankRequest extends FormRequest
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
            'bank_name' => 'nullable|min:3|max:100|string',
            'bank_address' => 'nullable|min:3',
            'bank_swift_code' => 'nullable',
            'bank_account_num' => 'nullable|numeric|min:1',
            'bank_account_name' => 'nullable'
        ];
    }
}
