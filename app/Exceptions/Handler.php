<?php

namespace App\Exceptions;

use Hyvor\FilterQ\Exceptions\FilterQException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
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

    public function render($request, Throwable $exception)
    {
        if (! config('app.debug')) { // not in debug mode
            if ($request->getHost() === config('blogs.domain_app')) {
                // app domain

                if (
                    $request->is('api/*') ||
                    $request->is('integrations/*') ||
                    $request->is('embed/*')
                ) {
                    $code = $exception->status ?? $exception->getCode();

                    /**
                     * Laravel input validation sends 422
                     * But, in our APIs we only return 400
                     */
                    if ($code === 400) {
                        $code = 422;
                    }

                    $httpCode = in_array($code, [401, 403, 404, 422, 500]) ? $code : 500;

                    $error =
                        $exception instanceof TrustedException ||
                        $exception instanceof FilterQException
                        ?
                        $exception->getMessage() :
                        'Something went wrong on our side.';

                    if ($exception instanceof NotFoundHttpException) {
                        $httpCode = 404;
                        $error = 'API Endpoint not found';
                    }

                    if ($exception instanceof ValidationException) {
                        $error = $exception->validator->errors()->first();
                    }

                    return response()->json([
                        'error' => $error,
                        'error_code' => $code,
                    ], $httpCode);
                }
            } else {
                // subdomains

                if ($exception instanceof SubdomainNotFoundException) {
                    return redirect('https://'.config('blogs.domain_app'));
                }
            }
        }

        dd($exception);
        // return parent::render($request, $exception);
    }
}
