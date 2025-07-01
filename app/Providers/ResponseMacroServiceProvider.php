<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

class ResponseMacroServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->booted(function () {
            Response::macro('api', function (
                mixed $data = null,
                bool $success = true,
                string $message = '',
                int $status = 200,
                array $errors = []
            ) {
                return Response::json([
                    'success' => $success,
                    'message' => $message,
                    'data'    => $data,
                    'errors'  => $errors,
                ], $status);
            });
        });
    }
}
