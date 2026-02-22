<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'username' => 'required|string|max:60|unique:users',
            'email' => 'required|email|max:120|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'first_name' => 'required|string|max:120',
            'last_name' => 'required|string|max:120',
            'middle_name' => 'nullable|string|max:120',
            'display_name' => 'nullable|string|max:80',
            'role_id' => 'required|exists:roles,id',
            'phone' => 'nullable|string|max:30',
            'timezone' => 'nullable|string|max:40',
            'is_active' => 'boolean',
        ];
    }
}
