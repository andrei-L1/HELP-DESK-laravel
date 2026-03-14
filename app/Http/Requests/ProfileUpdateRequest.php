<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'middle_name' => ['nullable', 'string', 'max:120'],
            'display_name' => ['nullable', 'string', 'max:80'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:120',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'timezone' => ['nullable', 'string', 'max:40'],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'], // 2MB max
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // If display_name is not provided, generate from first and last name
        if (!$this->display_name && $this->first_name && $this->last_name) {
            $this->merge([
                'display_name' => $this->first_name . ' ' . $this->last_name,
            ]);
        }
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'avatar.image' => 'The file must be an image.',
            'avatar.mimes' => 'The image must be a file of type: jpeg, png, jpg, gif.',
            'avatar.max' => 'The image size must not exceed 2MB.',
        ];
    }
}