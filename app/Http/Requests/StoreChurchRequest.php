<?php

namespace App\Http\Requests;

use App\Models\Church;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreChurchRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'abbreviation' => ['nullable', 'string', 'max:10'],
            'description' => ['nullable', 'string'],
            'country_name' => ['nullable', 'string'],
            'country_code' => ['nullable', 'string'],
            'city' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'logo' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    // Check if it's a valid base64 string with data URL prefix
                    if (is_string($value) && preg_match('/^data:image\/(png|jpg|jpeg|gif);base64,/', $value)) {
                        // Validate that the base64 data is valid
                        $base64Data = substr($value, strpos($value, ',') + 1);
                        if (base64_decode($base64Data, true) === false) {
                            $fail('Le logo contient des données base64 invalides.');
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
                        if (!in_array(request()->file($attribute)->extension(), ['jpg', 'jpeg', 'png', 'gif'])) {
                            $fail('Le logo doit être une image valide (jpg, jpeg, png, gif).');
                        }
                        return;
                    }

                    // If neither, fail
                    if (!empty($value)) {
                        $fail('Le logo doit être une image valide (base64, data URL, ou fichier uploadé).');
                    }
                },
            ],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Vérifier si l'utilisateur a déjà créé une église
            $existingChurch = Church::where('created_by', Auth::id())->first();
            if ($existingChurch) {
                $validator->errors()->add('church', 'Vous avez déjà créé une église. Un utilisateur ne peut créer qu\'une seule église.');
            }
        });
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
            'name.max' => 'Le nom de l\'église ne peut pas dépasser 255 caractères.',
            'abbreviation.max' => 'L\'abréviation ne peut pas dépasser 10 caractères.',
            'logo' => 'Le logo doit être une image valide (base64, data URL, ou fichier uploadé).',
        ];
    }
}
