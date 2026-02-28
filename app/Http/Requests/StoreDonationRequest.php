<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDonationRequest extends FormRequest
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
        $countries = array_keys(config('shwary.countries', ['DRC' => [], 'KE' => [], 'UG' => []]));
        $countryCode = $this->input('country_code', config('shwary.default_country', 'DRC'));
        $minAmount = config("shwary.countries.{$countryCode}.min_amount", 100);

        return [
            'amount' => ['required', 'numeric', "min:{$minAmount}"],
            'phone_number' => ['required', 'string', 'regex:/^\+\d{10,15}$/'],
            'country_code' => ['sometimes', 'string', Rule::in($countries)],
            'message' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $countryCode = $this->input('country_code', config('shwary.default_country', 'DRC'));
        $minAmount = config("shwary.countries.{$countryCode}.min_amount", 100);
        $currency = config("shwary.countries.{$countryCode}.currency", 'CDF');

        return [
            'amount.required' => 'Le montant est obligatoire.',
            'amount.numeric' => 'Le montant doit être un nombre.',
            'amount.min' => "Le montant minimum est de {$minAmount} {$currency}.",
            'phone_number.required' => 'Le numéro de téléphone est obligatoire.',
            'phone_number.regex' => 'Le numéro de téléphone doit être au format international (ex: +243812345678).',
            'country_code.in' => 'Pays non supporté. Pays valides: DRC, KE, UG.',
            'message.max' => 'Le message ne peut pas dépasser 500 caractères.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (!$this->has('country_code')) {
            $this->merge([
                'country_code' => config('shwary.default_country', 'DRC'),
            ]);
        }
    }
}
