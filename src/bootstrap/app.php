<?php

use App\Exceptions\InvalidTicketAssignmentException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->appendToGroup('api', [
            EnsureFrontendRequestsAreStateful::class
        ]);
    })
    ->withCommands([
        \App\Console\Commands\FetchClientMails::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(
            function (Exception $e, Request $request) {
                $message = $e->getMessage();
                $code = $e->getCode() != 0 ? Response::HTTP_INTERNAL_SERVER_ERROR : $e->getCode();
                $errors = null;
                // dd($e);
                if ($e instanceof AuthenticationException) {
                    return response()->error(
                        message: $message,
                        errors: $errors,
                        code: Response::HTTP_UNAUTHORIZED
                    );
                }

                if ($e instanceof InvalidSignatureException) {
                    return response()->error(
                        message: $message,
                        errors: $errors,
                        code: Response::HTTP_FORBIDDEN
                    );
                }

                if ($e instanceof InvalidTicketAssignmentException) {
                    return response()->error(
                        message: $message,
                        errors: $errors,
                        code: Response::HTTP_BAD_REQUEST
                    );
                }

                if ($e instanceof ValidationException) {
                    return response()->error(
                        message: $message,
                        errors: $e->validator->errors(),
                        code: Response::HTTP_UNPROCESSABLE_ENTITY
                    );
                }

                if ($e instanceof ModelNotFoundException) {
                    return response()->error(
                        message: $message,
                        errors: null,
                        code: Response::HTTP_NOT_FOUND
                    );
                }

                if ($e instanceof NotFoundHttpException) {
                    return response()->error(
                        message: $message,
                        errors: null,
                        code: Response::HTTP_NOT_FOUND
                    );
                }

                return response()->error(
                    message: $message,
                    errors: $errors,
                    code: $code
                );
            }
        );
    })->create();
