<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Always render JSON for API routes
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });

        // Route/Model not found
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($e->getPrevious() instanceof ModelNotFoundException) {
                return response()->api(null, false, 'Resource not found.', 404);
            }
            return response()->api(null, false, 'Route not found.', 404);
        });

        // Validation failed
        $exceptions->render(function (ValidationException $e, Request $request) {
            return response()->api(null, false, 'Validation failed.', 422, $e->errors());
        });

        // Catch-all fallback for any other exception
        $exceptions->render(function (Throwable $e, Request $request) {
            return response()->api(null, false, 'Server error.', 500);
        });
    })->create();
