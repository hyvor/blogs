<?php

namespace App\Exceptions;

use Hyvor\FilterQ\Exceptions\FilterQException;
use Hyvor\Internal\Http\Exceptions\HttpException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Sentry\Laravel\Integration;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{

    protected $dontReport = [
        //
    ];

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
            if (
                $e instanceof TrustedException ||
                $e instanceof HttpException
            ) {
                return;
            }
            Integration::captureUnhandledException($e);
        });
    }

    public function render($request, Throwable $exception)
    {
        if (!config('app.debug')) { // not in debug mode
            if ($request->getHost() === config('blogs.domain_app')) {
                // app domain

                if (
                    $request->is('api/*') ||
                    $request->is('integrations/*') ||
                    $request->is('embed/*')
                ) {
                    $code = $exception->status ?? $exception->getCode();

                    if ($code === 400) {
                        $code = 422;
                    }

                    $httpCode = method_exists($exception, 'getStatusCode') ?
                        $exception->getStatusCode() :
                        (in_array($code, [401, 403, 404, 422, 500]) ? $code : 500);

                    $error =
                        $exception instanceof TrustedException ||
                        $exception instanceof FilterQException ||
                        $exception instanceof HttpException
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
                        'code' => $code,
                    ], $httpCode);
                }
            } else {
                // subdomains
                if ($exception instanceof SubdomainNotFoundException) {
                    return redirect('https://' . config('blogs.domain_app'));
                }
            }
        }

        return parent::render($request, $exception);
    }
}
