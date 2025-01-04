<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use \Illuminate\Database\Eloquent\ModelNotFoundException;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->renderable(function (AuthenticationException $e) {
            return response()->json([
                'error' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage()
                ]
            ], $e->getCode());
        });

        $this->renderable(function (ModelNotFoundException $e) {
            return response()->json([
                'error' => [
                    'code' => $e->getCode(),
                    'message' => 'Model Not Found'
                ]
            ], 404);
        });

        $this->renderable(function (HttpException $e) {
            return response()->json([
                'code' => $e->getStatusCode(),
                'message' => $e->getMessage(),
            ], $e->getStatusCode());
        });

        $this->renderable(function (ValidationException $e) {
            return response()->json([
                'code' => $e->status,
                'message' => $e->getMessage(),
                'errors' => $e->errors(),
            ], $e->status);
        });

        $this->renderable(function (Exception $e) {
            return response()->json([
                'code' => $e->getCode(),
                'message' => $e->getMessage(),
            ], 500);
        });
//        $this->reportable(function (Throwable $e) {
//
//        });
    }
}
