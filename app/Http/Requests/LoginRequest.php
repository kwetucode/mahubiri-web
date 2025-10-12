<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
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
            'login' => 'required|string',
            'password' => 'required|string'
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $login = $this->input('login');

            // Vérifier si c'est un email ou un téléphone
            if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
                // C'est un email, pas de validation supplémentaire nécessaire
                return;
            } else {
                // C'est supposé être un téléphone, vérifier le format
                if (!preg_match('/^[0-9\+\-\s\(\)]+$/', $login)) {
                    $validator->errors()->add('login', 'Le champ doit être une adresse email valide ou un numéro de téléphone valide.');
                }
            }
        });
    }

    /**
     * Get custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'login.required' => 'L\'adresse email ou le numéro de téléphone est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
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
            'login' => 'identifiant (email ou téléphone)',
            'password' => 'mot de passe'
        ];
    }

    /**
     * Determine if the login field is an email.
     *
     * @return bool
     */
    public function isEmail(): bool
    {
        return filter_var($this->input('login'), FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Get the login field name based on the input type.
     *
     * @return string
     */
    public function getLoginField(): string
    {
        return $this->isEmail() ? 'email' : 'phone';
    }
}
