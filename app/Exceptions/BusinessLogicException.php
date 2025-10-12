<?php

namespace App\Exceptions;

use Exception;

/**
 * Business Logic Exception
 * 
 * Use this exception for business rule violations
 */
class BusinessLogicException extends Exception
{
    protected $businessRule;
    protected $affectedData;

    public function __construct(
        string $businessRule,
        array $affectedData = [],
        string $message = null,
        int $code = 0,
        Exception $previous = null
    ) {
        $this->businessRule = $businessRule;
        $this->affectedData = $affectedData;

        $message = $message ?? "Violation de la règle métier : {$businessRule}";

        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the business rule that was violated
     */
    public function getBusinessRule(): string
    {
        return $this->businessRule;
    }

    /**
     * Get the data affected by the business rule violation
     */
    public function getAffectedData(): array
    {
        return $this->affectedData;
    }

    /**
     * Check if this is a business logic exception
     */
    public function isBusinessLogicException(): bool
    {
        return true;
    }
}
