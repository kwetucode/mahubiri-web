<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSermonRequest extends FormRequest
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
            'title' => 'sometimes|required|string|max:255',
            'preacher_name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'duration' => 'sometimes|nullable|integer|min:1',

            // Audio: base64 ou fichier uploadé
            'audio_base64' => 'sometimes|nullable|string|regex:/^data:audio\/[^;]+;base64,/',
            'audio_file' => 'sometimes|nullable|file|mimes:mp3,wav,m4a,aac,ogg|max:51200', // 50MB max

            // Cover: base64 ou fichier uploadé
            'cover_base64' => 'sometimes|nullable|string|regex:/^data:image\/[^;]+;base64,/',
            'cover_file' => 'sometimes|nullable|file|mimes:jpeg,jpg,png,gif,webp|max:10240', // 10MB max
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Le titre est requis.',
            'title.string' => 'Le titre doit être une chaîne de caractères.',
            'title.max' => 'Le titre ne peut pas dépasser 255 caractères.',
            'preacher_name.required' => 'Le nom du prédicateur est requis.',
            'preacher_name.string' => 'Le nom du prédicateur doit être une chaîne de caractères.',
            'preacher_name.max' => 'Le nom du prédicateur ne peut pas dépasser 255 caractères.',
            'description.string' => 'La description doit être une chaîne de caractères.',

            // Audio messages
            'audio_base64.string' => 'Le fichier audio doit être au format base64.',
            'audio_base64.regex' => 'Le format du fichier audio base64 n\'est pas valide.',
            'audio_file.file' => 'Le fichier audio doit être un fichier valide.',
            'audio_file.mimes' => 'Le fichier audio doit être au format: mp3, wav, m4a, aac ou ogg.',
            'audio_file.max' => 'Le fichier audio ne peut pas dépasser 50 MB.',

            // Cover messages
            'cover_base64.string' => 'L\'image de couverture doit être au format base64.',
            'cover_base64.regex' => 'Le format de l\'image de couverture base64 n\'est pas valide.',
            'cover_file.file' => 'Le fichier de couverture doit être un fichier valide.',
            'cover_file.mimes' => 'L\'image de couverture doit être au format: jpeg, jpg, png, gif ou webp.',
            'cover_file.max' => 'L\'image de couverture ne peut pas dépasser 10 MB.',
            'duration.integer' => 'La durée doit être un nombre entier.',
            'duration.min' => 'La durée doit être d\'au moins 1 seconde.',
        ];
    }
}
