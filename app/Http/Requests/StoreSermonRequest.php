<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSermonRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'preacher_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'duration' => ['nullable', 'integer', 'min:1'],
            'church_id' => ['required', 'exists:churches,id'],

            // Audio: base64 ou fichier uploadé (optionnel)
            'audio_base64' => ['nullable', 'string', 'regex:/^data:audio\/[a-zA-Z0-9]+;base64,/'],
            'audio_file' => ['nullable', 'file', 'mimes:mp3,wav,m4a,aac,ogg', 'max:51200'], // 50MB max

            // Cover: base64 ou fichier uploadé (optionnel)
            'cover_base64' => ['nullable', 'string', 'regex:/^data:image\/[a-zA-Z]+;base64,/'],
            'cover_file' => ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp', 'max:10240'], // 10MB max
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
            'title.required' => 'Le titre du sermon est requis.',
            'title.max' => 'Le titre ne peut pas dépasser 255 caractères.',
            'preacher_name.required' => 'Le nom du prédicateur est requis.',
            'preacher_name.max' => 'Le nom du prédicateur ne peut pas dépasser 255 caractères.',
            'duration.integer' => 'La durée doit être un nombre entier.',
            'duration.min' => 'La durée doit être au moins 1 seconde.',
            'church_id.required' => 'L\'église est requise.',
            'church_id.exists' => 'L\'église sélectionnée n\'existe pas.',

            // Audio messages
            'audio_base64.regex' => 'Le format de l\'audio n\'est pas valide (doit être en base64).',
            'audio_file.file' => 'Le fichier audio doit être un fichier valide.',
            'audio_file.mimes' => 'Le fichier audio doit être au format: mp3, wav, m4a, aac ou ogg.',
            'audio_file.max' => 'Le fichier audio ne peut pas dépasser 50 MB.',

            // Cover messages
            'cover_base64.regex' => 'Le format de l\'image de couverture n\'est pas valide (doit être en base64).',
            'cover_file.file' => 'Le fichier de couverture doit être un fichier valide.',
            'cover_file.mimes' => 'L\'image de couverture doit être au format: jpeg, jpg, png, gif ou webp.',
            'cover_file.max' => 'L\'image de couverture ne peut pas dépasser 10 MB.',
        ];
    }
}
