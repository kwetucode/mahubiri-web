<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Client\RequestException as HttpClientRequestException;
use App\Exceptions\BusinessLogicException;

class ApiExceptionHandler
{
    /**
     * Auto-handle exceptions by detecting their type and routing to appropriate method
     *
     * @param Exception $exception
     * @param string $operation The operation being performed (e.g., 'connexion', 'création du compte')
     * @param array $additionalData
     * @return JsonResponse
     */
    public static function auto(
        Exception $exception,
        string $operation = 'opération',
        array $additionalData = []
    ): JsonResponse {
        // Detect exception type and route to appropriate handler

        // Validation exceptions
        if ($exception instanceof ValidationException) {
            return self::handleValidationError(
                $exception,
                "Les données fournies pour {$operation} ne sont pas valides.",
                $additionalData
            );
        }

        // Database exceptions
        if ($exception instanceof QueryException) {
            return self::handleDatabaseError($exception, $operation, $additionalData);
        }

        // Model not found exceptions
        if ($exception instanceof ModelNotFoundException || $exception instanceof NotFoundHttpException) {
            $resourceName = self::extractResourceNameFromOperation($operation);
            return self::handleNotFoundError($exception, $resourceName, $additionalData);
        }

        // Authentication exceptions
        if ($exception instanceof AuthenticationException) {
            return self::handleAuthenticationError(
                $exception,
                "Authentification requise pour {$operation}.",
                $additionalData
            );
        }

        // Authorization exceptions
        if ($exception instanceof AuthorizationException) {
            return self::handleAuthorizationError(
                $exception,
                "Vous n'êtes pas autorisé à effectuer cette {$operation}.",
                $additionalData
            );
        }

        // File exceptions
        if ($exception instanceof FileException) {
            return self::handleFileError($exception, $operation, $additionalData);
        }

        // HTTP Client exceptions (external services)
        if ($exception instanceof RequestException || $exception instanceof HttpClientRequestException) {
            return self::handleExternalServiceError(
                $exception,
                "service externe lors de {$operation}",
                $additionalData
            );
        }

        // Business logic exceptions
        if ($exception instanceof BusinessLogicException) {
            return self::handleBusinessLogicError(
                $exception,
                $exception->getBusinessRule(),
                array_merge($additionalData, ['affected_data' => $exception->getAffectedData()])
            );
        }

        // Generic business logic detection by message patterns
        if (
            stripos($exception->getMessage(), 'business') !== false ||
            stripos($exception->getMessage(), 'règle') !== false ||
            stripos($exception->getMessage(), 'policy') !== false
        ) {
            return self::handleBusinessLogicError($exception, $exception->getMessage(), $additionalData);
        }

        // Default to server error for unknown exceptions
        return self::handleServerError($exception, $operation, $additionalData);
    }

    /**
     * Extract resource name from operation string
     *
     * @param string $operation
     * @return string
     */
    private static function extractResourceNameFromOperation(string $operation): string
    {
        // Common patterns to extract resource names
        $patterns = [
            'utilisateur' => ['connexion', 'création du compte', 'modification utilisateur', 'suppression utilisateur'],
            'église' => ['création église', 'modification église', 'suppression église'],
            'sermon' => ['upload sermon', 'modification sermon', 'suppression sermon'],
            'rôle' => ['attribution rôle', 'modification rôle'],
        ];

        foreach ($patterns as $resource => $operations) {
            foreach ($operations as $op) {
                if (stripos($operation, $op) !== false) {
                    return $resource;
                }
            }
        }

        return 'ressource';
    }

    /**
     * Handle API exceptions and return standardized JSON response
     *
     * @param Exception $exception
     * @param string $context Context where the exception occurred (e.g., 'Login', 'Registration')
     * @param string|null $customMessage Custom error message to return to user
     * @param int $statusCode HTTP status code (default: 500)
     * @param array $additionalData Additional data to log
     * @return JsonResponse
     */
    public static function handle(
        Exception $exception,
        string $context,
        ?string $customMessage = null,
        int $statusCode = 500,
        array $additionalData = []
    ): JsonResponse {
        // Prepare comprehensive log data
        $logData = array_merge([
            'exception_class' => get_class($exception),
            'exception_message' => $exception->getMessage(),
            'exception_code' => $exception->getCode(),
            'context' => $context,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'stack_trace' => $exception->getTraceAsString(),
            'http_status_code' => $statusCode,
            'user_message' => $customMessage,
            'timestamp' => Carbon::now()->toISOString(),
            'request_id' => request()->header('X-Request-ID', uniqid('req_')),
            'user_agent' => request()->header('User-Agent'),
            'ip_address' => request()->ip(),
            'url' => request()->fullUrl(),
            'method' => request()->method(),
        ], $additionalData);

        // Log the error with appropriate level based on status code
        if ($statusCode >= 500) {
            Log::error("{$context} - Critical Error", $logData);
        } elseif ($statusCode >= 400) {
            Log::warning("{$context} - Client Error", $logData);
        } else {
            Log::info("{$context} - Info", $logData);
        }

        // Default error message if none provided
        $userMessage = $customMessage ?? 'Une erreur est survenue lors de l\'opération.';

        // Prepare response data
        $responseData = [
            'success' => false,
            'message' => $userMessage,
            'error' => $exception->getMessage()
        ];

        // Return standardized JSON response
        return response()->json($responseData, $statusCode);
    }

    /**
     * Handle server errors (5xx status codes)
     *
     * @param Exception $exception
     * @param string $operation The operation being performed (e.g., 'création', 'mise à jour', 'suppression')
     * @param array $additionalData
     * @return JsonResponse
     */
    public static function handleServerError(
        Exception $exception,
        string $operation = 'opération',
        array $additionalData = []
    ): JsonResponse {
        // Log specific server error details
        Log::critical("Server Error during {$operation}", [
            'operation' => $operation,
            'exception_type' => get_class($exception),
            'additional_data' => $additionalData
        ]);

        return self::handle(
            $exception,
            'Server Error',
            "Une erreur est survenue lors {$operation}.",
            500,
            $additionalData
        );
    }

    /**
     * Handle validation errors (422 status code)
     *
     * @param Exception $exception
     * @param string|null $customMessage
     * @param array $additionalData
     * @return JsonResponse
     */
    public static function handleValidationError(
        Exception $exception,
        ?string $customMessage = null,
        array $additionalData = []
    ): JsonResponse {
        // Log validation error details
        Log::warning("Validation Error", [
            'custom_message' => $customMessage,
            'validation_data' => $additionalData,
            'request_data' => request()->all()
        ]);

        $message = $customMessage ?? 'Les données fournies ne sont pas valides.';
        return self::handle(
            $exception,
            'Validation Error',
            $message,
            422,
            $additionalData
        );
    }

    /**
     * Handle authorization errors (403 status code)
     *
     * @param Exception $exception
     * @param string|null $customMessage
     * @param array $additionalData
     * @return JsonResponse
     */
    public static function handleAuthorizationError(
        Exception $exception,
        ?string $customMessage = null,
        array $additionalData = []
    ): JsonResponse {
        // Log authorization error with security context
        $user = Auth::user();
        Log::warning("Authorization Error - Access Denied", [
            'user_id' => $user?->id,
            'user_email' => $user?->email,
            'attempted_action' => $customMessage,
            'security_data' => $additionalData,
            'user_role' => $user?->role?->name ?? 'No role'
        ]);

        $message = $customMessage ?? 'Vous n\'êtes pas autorisé à effectuer cette action.';
        return self::handle(
            $exception,
            'Authorization Error',
            $message,
            403,
            $additionalData
        );
    }

    /**
     * Handle authentication errors (401 status code)
     *
     * @param Exception $exception
     * @param string|null $customMessage
     * @param array $additionalData
     * @return JsonResponse
     */
    public static function handleAuthenticationError(
        Exception $exception,
        ?string $customMessage = null,
        array $additionalData = []
    ): JsonResponse {
        // Log authentication failure for security monitoring
        Log::warning("Authentication Error - Login Failed", [
            'attempted_login' => request()->input('login') ?? request()->input('email'),
            'auth_message' => $customMessage,
            'auth_data' => $additionalData,
            'login_attempt_count' => session('login_attempts', 0) + 1
        ]);

        $message = $customMessage ?? 'Authentification requise.';
        return self::handle(
            $exception,
            'Authentication Error',
            $message,
            401,
            $additionalData
        );
    }

    /**
     * Handle resource not found errors (404 status code)
     *
     * @param Exception $exception
     * @param string $resourceName Name of the resource (e.g., 'utilisateur', 'église', 'sermon')
     * @param array $additionalData
     * @return JsonResponse
     */
    public static function handleNotFoundError(
        Exception $exception,
        string $resourceName = 'ressource',
        array $additionalData = []
    ): JsonResponse {
        // Log resource not found for tracking
        Log::info("Resource Not Found", [
            'resource_name' => $resourceName,
            'requested_resource_id' => $additionalData['id'] ?? 'unknown',
            'search_criteria' => $additionalData,
            'user_id' => Auth::id()
        ]);

        return self::handle(
            $exception,
            'Not Found Error',
            "La {$resourceName} demandée n'a pas été trouvée.",
            404,
            $additionalData
        );
    }

    /**
     * Handle database errors
     *
     * @param Exception $exception
     * @param string $operation The database operation (e.g., 'insertion', 'mise à jour', 'suppression')
     * @param array $additionalData
     * @return JsonResponse
     */
    public static function handleDatabaseError(
        Exception $exception,
        string $operation = 'opération de base de données',
        array $additionalData = []
    ): JsonResponse {
        // Log database error with detailed information
        Log::critical("Database Error", [
            'database_operation' => $operation,
            'sql_state' => $exception->getCode(),
            'exception_type' => get_class($exception),
            'query_data' => $additionalData,
            'database_connection' => config('database.default'),
            'is_sql_exception' => $exception instanceof \PDOException
        ]);

        return self::handle(
            $exception,
            'Database Error',
            "Une erreur de base de données est survenue lors de l'{$operation}.",
            500,
            $additionalData
        );
    }

    /**
     * Handle file operation errors
     *
     * @param Exception $exception
     * @param string $operation The file operation (e.g., 'téléchargement', 'suppression', 'lecture')
     * @param array $additionalData
     * @return JsonResponse
     */
    public static function handleFileError(
        Exception $exception,
        string $operation = 'opération sur le fichier',
        array $additionalData = []
    ): JsonResponse {
        // Log file operation error with file system details
        Log::error("File Operation Error", [
            'file_operation' => $operation,
            'file_path' => $additionalData['file_path'] ?? 'unknown',
            'file_size' => $additionalData['file_size'] ?? null,
            'file_type' => $additionalData['file_type'] ?? null,
            'storage_disk' => $additionalData['storage_disk'] ?? config('filesystems.default'),
            'available_space' => disk_free_space(storage_path())
        ]);

        return self::handle(
            $exception,
            'File Error',
            "Une erreur est survenue lors du {$operation}.",
            500,
            $additionalData
        );
    }

    /**
     * Handle external service errors (API calls, etc.)
     *
     * @param Exception $exception
     * @param string $serviceName Name of the external service
     * @param array $additionalData
     * @return JsonResponse
     */
    public static function handleExternalServiceError(
        Exception $exception,
        string $serviceName = 'service externe',
        array $additionalData = []
    ): JsonResponse {
        // Log external service error for monitoring
        Log::error("External Service Error", [
            'service_name' => $serviceName,
            'service_url' => $additionalData['url'] ?? 'unknown',
            'response_code' => $additionalData['response_code'] ?? null,
            'response_time' => $additionalData['response_time'] ?? null,
            'service_data' => $additionalData,
            'retry_count' => $additionalData['retry_count'] ?? 0
        ]);

        return self::handle(
            $exception,
            'External Service Error',
            "Une erreur est survenue avec le {$serviceName}.",
            502,
            $additionalData
        );
    }

    /**
     * Handle business logic errors
     *
     * @param Exception $exception
     * @param string $businessRule Description of the business rule that failed
     * @param array $additionalData
     * @return JsonResponse
     */
    public static function handleBusinessLogicError(
        Exception $exception,
        string $businessRule = 'règle métier',
        array $additionalData = []
    ): JsonResponse {
        // Log business logic error for analysis
        Log::warning("Business Logic Error", [
            'business_rule' => $businessRule,
            'business_context' => $additionalData,
            'user_id' => Auth::id(),
            'affected_data' => $additionalData['affected_data'] ?? null,
            'validation_rules' => $additionalData['validation_rules'] ?? null
        ]);

        return self::handle(
            $exception,
            'Business Logic Error',
            "Erreur de logique métier : {$businessRule}.",
            400,
            $additionalData
        );
    }
}
