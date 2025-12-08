<?php

namespace App\Http\Requests;

use App\Enums\MinistryType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePreacherProfileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled in the controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ministry_name' => 'required|string|max:255',
            'ministry_type' => ['required', Rule::in(MinistryType::getMinistryValues())],
            'avatar_url' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    // Check if it's a valid base64 string with data URL prefix
                    if (is_string($value) && preg_match('/^data:image\/(png|jpg|jpeg|gif|webp);base64,/', $value)) {
                        // Validate that the base64 data is valid
                        $base64Data = substr($value, strpos($value, ',') + 1);
                        if (base64_decode($base64Data, true) === false) {
                            $fail('L\'avatar contient des données base64 invalides.');
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
                            $fail('L\'avatar doit être une image valide (jpg, jpeg, png, gif, webp).');
                        }
                        return;
                    }

                    // If neither, fail
                    if (!empty($value)) {
                        $fail('L\'avatar doit être une image valide (base64, data URL, ou fichier uploadé).');
                    }
                },
            ],
            'country_name' => 'nullable|string|max:255',
            'country_code' => 'nullable|string|max:10',
            'city' => 'nullable|string|max:255',
            'social_links' => 'nullable|array',
            'social_links.facebook' => 'nullable|url',
            'social_links.youtube' => 'nullable|url',
            'social_links.instagram' => 'nullable|url',
            'social_links.twitter' => 'nullable|url',
            'social_links.website' => 'nullable|url',
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
            'ministry_name.required' => 'Le nom du ministère est requis',
            'ministry_name.max' => 'Le nom du ministère ne peut pas dépasser 255 caractères',
            'ministry_type.required' => 'Le type de ministère est requis',
            'ministry_type.in' => 'Le type de ministère sélectionné n\'est pas valide',
            'avatar_url' => 'L\'avatar doit être une image valide (base64, data URL, ou fichier uploadé)',
            'country_name.max' => 'Le nom du pays ne peut pas dépasser 255 caractères',
            'country_code.max' => 'Le code pays ne peut pas dépasser 10 caractères',
            'city.max' => 'Le nom de la ville ne peut pas dépasser 255 caractères',
            'social_links.array' => 'Les liens sociaux doivent être un tableau',
            'social_links.facebook.url' => 'Le lien Facebook doit être une URL valide',
            'social_links.youtube.url' => 'Le lien YouTube doit être une URL valide',
            'social_links.instagram.url' => 'Le lien Instagram doit être une URL valide',
            'social_links.twitter.url' => 'Le lien Twitter doit être une URL valide',
            'social_links.website.url' => 'Le lien du site web doit être une URL valide',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'ministry_name' => 'nom du ministère',
            'ministry_type' => 'type de ministère',
            'avatar_url' => 'photo de profil',
            'country_name' => 'nom du pays',
            'country_code' => 'code pays',
            'city' => 'ville',
            'social_links' => 'liens sociaux',
        ];
    }
}
