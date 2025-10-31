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
            'category_sermon_id' => ['required', 'numeric', 'exists:category_sermons,id'],
            'preacher_name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'color' => ['nullable', 'integer', 'min:1'],
            // Audio: base64 ou fichier uploadé (optionnel)
            'audio' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    // Check if it's a valid base64 string with data URL prefix
                    if (is_string($value) && preg_match('/^data:audio\/(mp3|wav|m4a|aac|ogg|mpeg|webm);base64,/', $value)) {
                        // Validate that the base64 data is valid
                        $base64Data = substr($value, strpos($value, ',') + 1);
                        if (base64_decode($base64Data, true) === false) {
                            $fail('L\'audio contient des données base64 invalides.');
                        }
                        return;
                    }

                    // Check if it's a valid base64 string without prefix (for mobile apps)
                    if (is_string($value) && !empty($value)) {
                        // Validate base64 format
                        if (preg_match('/^[A-Za-z0-9+\/]*={0,2}$/', $value)) {
                            $decodedData = base64_decode($value, true);
                            if ($decodedData !== false) {
                                return; // Valid base64 audio
                            }
                        }
                    }

                    // Check if it's an uploaded file audio
                    if (request()->hasFile($attribute) && request()->file($attribute)->isValid()) {
                        if (!in_array(request()->file($attribute)->extension(), ['mp3', 'wav', 'm4a', 'aac', 'ogg'])) {
                            $fail('L\'audio doit être un fichier valide (mp3, wav, m4a, aac, ogg).');
                        }
                        return;
                    }

                    // If neither, fail
                    if (!empty($value)) {
                        $fail('L\'audio doit être un fichier valide (base64, data URL, ou fichier uploadé).');
                    }
                },
            ],

            // Cover: base64 ou fichier uploadé (optionnel)
            'cover' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    // Check if it's a valid base64 string with data URL prefix
                    if (is_string($value) && preg_match('/^data:image\/(png|jpg|jpeg|gif|webp);base64,/', $value)) {
                        // Validate that the base64 data is valid
                        $base64Data = substr($value, strpos($value, ',') + 1);
                        if (base64_decode($base64Data, true) === false) {
                            $fail('L\'image de couverture contient des données base64 invalides.');
                        }
                        return;
                    }

                    // Check if it's a valid base64 string without prefix (for mobile apps)
                    if (is_string($value) && !empty($value)) {
                        // Validate base64 format
                        if (preg_match('/^[A-Za-z0-9+\/]*={0,2}$/', $value)) {
                            $decodedData = base64_decode($value, true);
                            if ($decodedData !== false) {
                                // Additional check: verify it's actually an image by checking file signature
                                $imageInfo = @getimagesizefromstring($decodedData);
                                if ($imageInfo !== false) {
                                    return; // Valid base64 image
                                }
                            }
                        }
                    }

                    // Check if it's an uploaded file image
                    if (request()->hasFile($attribute) && request()->file($attribute)->isValid()) {
                        if (!in_array(request()->file($attribute)->extension(), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                            $fail('L\'image de couverture doit être une image valide (jpg, jpeg, png, gif, webp).');
                        }
                        return;
                    }

                    // If neither, fail
                    if (!empty($value)) {
                        $fail('L\'image de couverture doit être une image valide (base64, data URL, ou fichier uploadé).');
                    }
                },
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
