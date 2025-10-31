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
     * Get the error messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Le titre du sermon est requis.',
            'title.string' => 'Le titre doit être une chaîne de caractères.',
            'title.max' => 'Le titre ne peut pas dépasser 255 caractères.',

            'category_sermon_id.required' => 'La catégorie du sermon est requise.',
            'category_sermon_id.numeric' => 'La catégorie doit être un nombre.',
            'category_sermon_id.exists' => 'La catégorie sélectionnée n\'existe pas.',

            'preacher_name.required' => 'Le nom du prédicateur est requis.',
            'preacher_name.string' => 'Le nom du prédicateur doit être une chaîne de caractères.',
            'preacher_name.max' => 'Le nom du prédicateur ne peut pas dépasser 255 caractères.',

            'description.string' => 'La description doit être une chaîne de caractères.',

            'color.integer' => 'La couleur doit être un nombre entier.',
            'color.min' => 'La couleur doit être au moins 1.',
        ];
    }
}
