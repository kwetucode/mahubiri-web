<?php

namespace App\Services;

use App\Models\Donation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;

class ShwaryService
{
    protected string $baseUrl;
    protected string $merchantId;
    protected string $merchantKey;
    protected bool $sandbox;
    protected int $timeout;
    protected int $connectTimeout;

    public function __construct()
    {
        $this->baseUrl = config('shwary.base_url');
        $this->merchantId = config('shwary.merchant_id');
        $this->merchantKey = config('shwary.merchant_key');
        $this->sandbox = config('shwary.sandbox', true);
        $this->timeout = config('shwary.timeout', 30);
        $this->connectTimeout = config('shwary.connect_timeout', 10);
    }

    /**
     * Get HTTP client with merchant headers.
     */
    protected function client()
    {
        return Http::baseUrl($this->baseUrl)
            ->timeout($this->timeout)
            ->connectTimeout($this->connectTimeout)
            ->withHeaders([
                'x-merchant-id' => $this->merchantId,
                'x-merchant-key' => $this->merchantKey,
                'Authorization' => 'Bearer ' . $this->merchantKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]);
    }

    /**
     * Initiate a Mobile Money payment.
     *
     * @param float $amount
     * @param string $phoneNumber
     * @param string $countryCode
     * @param string|null $callbackUrl
     * @return array
     * @throws \Exception
     */
    public function initiatePayment(
        float $amount,
        string $phoneNumber,
        string $countryCode = 'DRC',
        ?string $callbackUrl = null
    ): array {
        $this->validateCountry($countryCode);
        $this->validateAmount($amount, $countryCode);
        $this->validatePhoneNumber($phoneNumber, $countryCode);

        // Convertir le code pays pour l'API Shwary (CD -> DRC)
        $apiCountryCode = config("shwary.countries.{$countryCode}.api_code", $countryCode);

        $endpoint = $this->sandbox
            ? "/merchants/payment/sandbox/{$apiCountryCode}"
            : "/merchants/payment/{$apiCountryCode}";

        $payload = [
            'amount' => $amount,
            'clientPhoneNumber' => $phoneNumber,
        ];

        if ($callbackUrl) {
            $payload['callbackUrl'] = $callbackUrl;
        } elseif (config('shwary.callback_url')) {
            $payload['callbackUrl'] = config('shwary.callback_url');
        }

        try {
            Log::info('Shwary: Initiating payment', [
                'endpoint' => $endpoint,
                'amount' => $amount,
                'phone' => substr($phoneNumber, 0, -4) . '****',
                'country' => $countryCode,
                'sandbox' => $this->sandbox,
            ]);

            $response = $this->client()->post($endpoint, $payload);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Shwary: Payment initiated successfully', [
                    'transaction_id' => $data['id'] ?? null,
                    'status' => $data['status'] ?? null,
                ]);
                return [
                    'success' => true,
                    'data' => $data,
                ];
            }

            $errorMessage = $response->json('message') ?? 'Payment initiation failed';
            Log::error('Shwary: Payment failed', [
                'status' => $response->status(),
                'error' => $errorMessage,
            ]);

            return [
                'success' => false,
                'error' => $errorMessage,
                'status_code' => $response->status(),
            ];

        } catch (RequestException $e) {
            Log::error('Shwary: Request exception', [
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Service de paiement temporairement indisponible',
                'exception' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get transaction details by ID.
     *
     * @param string $transactionId
     * @return array
     */
    public function getTransaction(string $transactionId): array
    {
        try {
            Log::info('Shwary: Fetching transaction', ['transaction_id' => $transactionId]);

            $response = $this->client()->get("/merchants/transactions/{$transactionId}");

            if ($response->successful()) {
                $data = $response->json();

                Log::info('Shwary: Transaction fetched', [
                    'transaction_id' => $transactionId,
                    'status' => $data['status'] ?? 'unknown',
                    'failureReason' => $data['failureReason'] ?? null,
                ]);

                return [
                    'success' => true,
                    'data' => $data,
                ];
            }

            Log::warning('Shwary: Failed to fetch transaction', [
                'transaction_id' => $transactionId,
                'status_code' => $response->status(),
                'response' => $response->json(),
            ]);

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'Transaction not found',
                'status_code' => $response->status(),
            ];

        } catch (RequestException $e) {
            Log::error('Shwary: Failed to get transaction', [
                'transaction_id' => $transactionId,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Impossible de récupérer la transaction',
            ];
        }
    }

    /**
     * Process a donation payment.
     *
     * @param Donation $donation
     * @return array
     */
    public function processDonation(Donation $donation): array
    {
        $result = $this->initiatePayment(
            (float) $donation->amount,
            $donation->phone_number,
            $donation->country_code
        );

        if ($result['success']) {
            $data = $result['data'];

            $donation->update([
                'shwary_transaction_id' => $data['id'] ?? null,
                'shwary_reference_id' => $data['referenceId'] ?? null,
                'status' => $data['status'] ?? 'pending',
                'is_sandbox' => $data['isSandbox'] ?? $this->sandbox,
            ]);

            // If sandbox, transaction completes immediately
            if (($data['status'] ?? 'pending') === 'completed') {
                $donation->markAsCompleted($data['id']);
            }
        } else {
            $donation->markAsFailed($result['error'] ?? 'Échec du paiement');
        }

        return $result;
    }

    /**
     * Validate country code.
     *
     * @param string $countryCode
     * @throws \InvalidArgumentException
     */
    protected function validateCountry(string $countryCode): void
    {
        $countries = array_keys(config('shwary.countries', []));

        if (!in_array($countryCode, $countries)) {
            throw new \InvalidArgumentException(
                "Pays non supporté: {$countryCode}. Pays valides: " . implode(', ', $countries)
            );
        }
    }

    /**
     * Validate amount for country.
     *
     * @param float $amount
     * @param string $countryCode
     * @throws \InvalidArgumentException
     */
    protected function validateAmount(float $amount, string $countryCode): void
    {
        $minAmount = config("shwary.countries.{$countryCode}.min_amount", 100);

        if ($amount < $minAmount) {
            $currency = config("shwary.countries.{$countryCode}.currency", 'CDF');
            throw new \InvalidArgumentException(
                "Le montant minimum est de {$minAmount} {$currency}"
            );
        }
    }

    /**
     * Validate phone number format.
     *
     * @param string $phoneNumber
     * @param string $countryCode
     * @throws \InvalidArgumentException
     */
    protected function validatePhoneNumber(string $phoneNumber, string $countryCode): void
    {
        $prefix = config("shwary.countries.{$countryCode}.phone_prefix");

        if (!str_starts_with($phoneNumber, $prefix)) {
            throw new \InvalidArgumentException(
                "Le numéro de téléphone doit commencer par {$prefix}"
            );
        }

        // Basic E.164 format validation
        if (!preg_match('/^\+\d{10,15}$/', $phoneNumber)) {
            throw new \InvalidArgumentException(
                "Format de numéro de téléphone invalide. Utilisez le format E.164 (ex: {$prefix}XXXXXXXXX)"
            );
        }
    }

    /**
     * Get supported countries with their configurations.
     *
     * @return array
     */
    public function getSupportedCountries(): array
    {
        return config('shwary.countries', []);
    }

    /**
     * Check if sandbox mode is enabled.
     *
     * @return bool
     */
    public function isSandbox(): bool
    {
        return $this->sandbox;
    }
}
