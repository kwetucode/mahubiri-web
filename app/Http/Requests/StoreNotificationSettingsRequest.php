<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNotificationSettingsRequest extends FormRequest
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
            'new_sermon' => ['required', 'boolean'],
            'new_church' => ['required', 'boolean'],
            'new_announcement' => ['required', 'boolean'],
            'push_enabled' => ['required', 'boolean'],
            'email_enabled' => ['required', 'boolean'],
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
            'new_sermon.required' => 'Le paramètre new_sermon est requis.',
            'new_sermon.boolean' => 'Le paramètre new_sermon doit être un booléen.',
            'new_church.required' => 'Le paramètre new_church est requis.',
            'new_church.boolean' => 'Le paramètre new_church doit être un booléen.',
            'new_announcement.required' => 'Le paramètre new_announcement est requis.',
            'new_announcement.boolean' => 'Le paramètre new_announcement doit être un booléen.',
            'push_enabled.required' => 'Le paramètre push_enabled est requis.',
            'push_enabled.boolean' => 'Le paramètre push_enabled doit être un booléen.',
            'email_enabled.required' => 'Le paramètre email_enabled est requis.',
            'email_enabled.boolean' => 'Le paramètre email_enabled doit être un booléen.',
        ];
    }
}
