<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetForgetPasswordRequest extends FormRequest
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
            'email' => 'required|min:3|max:100|email|exists:users,email',
            'password' => 'required|min:7|confirmed'
        ];
    }
}
