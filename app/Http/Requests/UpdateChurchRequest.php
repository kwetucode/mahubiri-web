<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateChurchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Sera géré par le middleware auth dans les routes
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'required', 'string', 'max:255'],
            'abbreviation' => ['required', 'string', 'max:10'],
            'visionary_name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'logo' => [
                'nullable',
                'regex:/^data:image\/(png|jpg|jpeg|gif);base64,[A-Za-z0-9+\/=]+$/',
            ],
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
            'name.required' => 'Le nom de l\'église est requis.',
            'visionary_name.required' => 'Le nom du visionnaire est requis.',
            'name.max' => 'Le nom de l\'église ne peut pas dépasser 255 caractères.',
            'abbreviation.max' => 'L\'abréviation ne peut pas dépasser 10 caractères.',
            'logo.regex' => 'Le format de l\'image logo n\'est pas valide (doit être en base64).',
        ];
    }
}
