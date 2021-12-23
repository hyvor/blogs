<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception) {

        if (!config('app.debug')) { // not in debug mode

            if ($request->is('api/*')) {
                $code = $exception->getCode();
                $httpCode = in_array($code, [400, 401, 402, 403, 404, 422, 500]) ? $code : 500;

                $error = $exception instanceof TrustedException ? $exception->getMessage() : 'Something went wrong on our side.';

                if ($exception instanceof NotFoundHttpException) {
                    $httpCode = 404;
                    $error = 'API Endpoint not found';
                }

                return response()->json([
                    'error' => $error,
                    'errorCode' => $code
                ], $httpCode);
            }

        }

        return parent::render($request, $exception);

    }
}
