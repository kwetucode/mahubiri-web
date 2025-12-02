<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFcmTokenRequest extends FormRequest
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
            'fcm_token' => ['required', 'string'],
            'device_type' => ['nullable', 'string', 'in:android,ios,web'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'fcm_token.required' => 'Le token FCM est requis.',
            'fcm_token.string' => 'Le token FCM doit être une chaîne de caractères.',
            'device_type.string' => 'Le type de périphérique doit être une chaîne de caractères.',
            'device_type.in' => 'Le type de périphérique doit être android, ios ou web.',
        ];
    }
}
