<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Spatie\QueryBuilder\Exceptions\InvalidFilterQuery;
use Spatie\QueryBuilder\Exceptions\InvalidFilterValue;
use Spatie\QueryBuilder\Exceptions\InvalidQuery;
use Throwable;

use Illuminate\Database\QueryException;
use Illuminate\Http\Response;
use App\Http\ApiResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class Handler extends ExceptionHandler
{
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {

        });
    }

    // this function for handel Exception
    public function render($request, Exception|Throwable $exception): Response|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response|\Illuminate\Http\RedirectResponse
    {
        if(!$request->has('api/')){
            return parent::render($request, $exception);
        }
        if (config('app.env') === 'testing') {
            return parent::render($request, $exception);
        }

        if ($exception instanceof UnauthorizedException) {
            return ApiResponse::error($exception->getMessage(), 403);
        } else if ($exception instanceof ValidationException) {
            return ApiResponse::error($exception->getMessage(), 422);
        } else if ($exception instanceof NotFoundHttpException) {
            Log::error($exception->getMessage());
            return ApiResponse::error(t('not found'), 404);
        } else if ($exception instanceof ModelNotFoundException) {
            Log::error($exception->getMessage());
            return ApiResponse::error(t('not found'), 404);
        } else if ($exception instanceof InvalidQuery) {
            return ApiResponse::error($exception->getMessage());
        }

        if (config('app.env') !== 'production' && env('DEBUG_TRACE') === true) {
            return parent::render($request, $exception);
        }

        Log::error($exception->getMessage());
        return ApiResponse::error(config('app.env') === 'production' ? t('wrong') : $exception->getMessage(), 500);
    }
}
