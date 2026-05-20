<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * Exceptions that should not be reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [];

    /**
     * Inputs never flashed on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register exception callbacks.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render exception response.
     */
    public function render($request, Throwable $e)
    {
        /**
         * Validation Errors
         */
        if ($e instanceof ValidationException) {

            if ($request->expectsJson()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'Validation failed.',
                    'errors'  => $e->errors(),
                ], $e->status);
            }
        }

        /**
         * Production Error Handling
         */
        if (!config('app.debug')) {

            /**
             * API / AJAX Errors
             */
            if ($request->expectsJson() || $request->is('api/*')) {

                $statusCode = 500;

                if ($e instanceof HttpExceptionInterface) {
                    $statusCode = $e->getStatusCode();
                }

                return response()->json([
                    'status' => false,
                    'message' => $this->getApiMessage($statusCode),
                ], $statusCode);
            }

            /**
             * Web Errors
             */
            if ($e instanceof HttpExceptionInterface) {

                $statusCode = $e->getStatusCode();

                // Custom views for common errors
                if (view()->exists("errors.$statusCode")) {
                    return response()->view(
                        "errors.$statusCode",
                        [],
                        $statusCode
                    );
                }
            }

            // Fallback error page
            return response()->view('errors.500', [], 500);
        }

        return parent::render($request, $e);
    }

    /**
     * API error messages by status code.
     */
    protected function getApiMessage(int $statusCode): string
    {
        return match ($statusCode) {
            401 => 'Unauthenticated.',
            403 => 'Access denied.',
            404 => 'Resource not found.',
            422 => 'Validation failed.',
            429 => 'Too many requests.',
            default => 'Something went wrong. Please try again later.',
        };
    }

    /**
     * Determine if JSON should be returned.
     */
    protected function shouldReturnJson($request, Throwable $e): bool
    {
        if (config('app.debug') && !$request->is('api/*')) {
            return false;
        }

        return $request->expectsJson() || $request->is('api/*');
    }
}