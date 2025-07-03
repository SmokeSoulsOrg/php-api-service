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
                if (
                    $data instanceof \Illuminate\Http\Resources\Json\ResourceCollection &&
                    $data->resource instanceof \Illuminate\Pagination\AbstractPaginator
                ) {
                    // Merge the resource response directly and add custom keys
                    $response = $data->toResponse(request())->getData(true);
                    $response['success'] = $success;
                    $response['message'] = $message;
                    $response['errors'] = $errors;

                    return response()->json($response, $status);
                }

                return response()->json([
                    'success' => $success,
                    'message' => $message,
                    'data'    => $data,
                    'errors'  => $errors,
                ], $status);
            });
        });
    }
}
