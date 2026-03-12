<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

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
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'super_admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'verified' => \App\Http\Middleware\EnsureEmailVerified::class,
            'onboarding' => \App\Http\Middleware\EnsureOnboardingCompleted::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Ensure all API exceptions render as JSON, never HTML.
        // Exclude Inertia requests so that session expiration redirects
        // to the login page instead of showing raw JSON.
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            $isApiRequest = $request->is('api/*')
                || ($request->wantsJson() && !$request->header('X-Inertia'));

            if ($isApiRequest) {
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

            // Non-API requests (Inertia/web): render Error page for HTTP exceptions
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
                $status = $e->getStatusCode();
                if (in_array($status, [403, 404, 419, 500, 503])) {
                    return \Inertia\Inertia::render('Error', ['status' => $status])
                        ->toResponse($request)
                        ->setStatusCode($status);
                }
            }

            return null;
        });
    })

    ->create();
