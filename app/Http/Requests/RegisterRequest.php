<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => 'required|min:2|max:100|string',
            'surname' => 'nullable|min:2|max:100|string',
            'email' => 'required|min:3|max:50|email|unique:users,email',
            'password' => 'required|min:7|confirmed',
            'phone' => 'nullable|numeric|unique:users,phone',
            'dateOfBirth' => 'nullable|date',
            'type' => 'required|in:admin,client,refinery'
        ];
    }
}
