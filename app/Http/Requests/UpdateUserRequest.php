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
     */
    public function rules(): array
    {
        $id = $this->route('id') ?? $this->route('user'); // Depending on your route definition
        return [
            'username' => "required|string|max:60|unique:users,username,{$id}",
            'email' => "required|email|max:120|unique:users,email,{$id}",
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
