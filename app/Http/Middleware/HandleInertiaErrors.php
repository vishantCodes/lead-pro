<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\HandleExceptions as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Throwable;

class HandleInertiaErrors extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        $response = parent::render($request, $e);

        if (!method_exists($response, 'getStatusCode') || $response->getStatusCode() !== 404) {
            return $response;
        }

        return Inertia::render('Errors/NotFound', [
            'status' => $response->getStatusCode(),
        ])->toResponse($request);
    }

    public function report(Throwable $e)
    {
        if (app()->bound('sentry') && app('sentry') instanceof \Sentry\State\HubInterface) {
            app('sentry')->captureException($e);
        }

        Log::error($e->getMessage(), [
            'exception' => $e,
            'url' => request()->fullUrl(),
            'user_id' => auth()->id(),
            'tenant_id' => auth()->user()?->tenant_id,
        ]);

        parent::report($e);
    }
}
