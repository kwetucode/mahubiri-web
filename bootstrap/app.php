<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        //api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        then: function ($router) {
            Route::prefix('api/v1')
                ->middleware('api')
                ->name('api.v1.')
                ->group(base_path('routes/api.php'));

            Route::prefix('admin')
                ->middleware(['web'])
                ->name('admin.')
                ->group(base_path('routes/admin.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
        $middleware->api(prepend: [
            // Must run FIRST: forces Accept: application/json so Laravel
            // always renders errors as JSON instead of HTML.
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);
        $middleware->api(append: [
            \App\Http\Middleware\OptimizeApiResponse::class,
            // Must run LAST: catches any HTML error responses (WAF/proxy)
            // and converts them to JSON for the Flutter client.
            \App\Http\Middleware\HandleWafErrors::class,
        ]);
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Ensure all API exceptions render as JSON, never HTML
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($request->is('api/*') || $request->wantsJson()) {
                $status = method_exists($e, 'getStatusCode')
                    ? $e->getStatusCode()
                    : 500;

                $message = match (true) {
                    $e instanceof \Illuminate\Auth\AuthenticationException
                        => 'Authentification requise. Veuillez vous reconnecter.',
                    $e instanceof \Illuminate\Auth\Access\AuthorizationException
                        => 'Vous n\'êtes pas autorisé à effectuer cette action.',
                    $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
                        => 'La ressource demandée est introuvable.',
                    $e instanceof \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException
                        => 'Trop de requêtes. Veuillez patienter.',
                    $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException
                        => 'Élément introuvable.',
                    $e instanceof \Symfony\Component\HttpKernel\Exception\HttpException
                        => $e->getMessage() ?: 'Erreur serveur.',
                    default => app()->isProduction()
                        ? 'Une erreur interne est survenue.'
                        : $e->getMessage(),
                };

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'error'   => [
                        'code'   => $status,
                        'type'   => class_basename($e),
                        ...(app()->isProduction() ? [] : [
                            'trace' => $e->getTraceAsString(),
                        ]),
                    ],
                ], $status);
            }

            // Non-API requests: let Laravel handle normally
            return null;
        });
    })

    ->create();
