<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationSettingsRequest extends FormRequest
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
            'new_sermon' => ['sometimes', 'boolean'],
            'new_church' => ['sometimes', 'boolean'],
            'new_announcement' => ['sometimes', 'boolean'],
            'storage_alert' => ['sometimes', 'boolean'],
            'push_enabled' => ['sometimes', 'boolean'],
            'email_enabled' => ['sometimes', 'boolean'],
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
            'new_sermon.boolean' => 'Le paramètre new_sermon doit être un booléen.',
            'new_church.boolean' => 'Le paramètre new_church doit être un booléen.',
            'new_announcement.boolean' => 'Le paramètre new_announcement doit être un booléen.',
            'storage_alert.boolean' => 'Le paramètre storage_alert doit être un booléen.',
            'push_enabled.boolean' => 'Le paramètre push_enabled doit être un booléen.',
            'email_enabled.boolean' => 'Le paramètre email_enabled doit être un booléen.',
        ];
    }
}
