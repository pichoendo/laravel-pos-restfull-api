<?php

use App\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $apiResponse = new ApiResponse();
        $exceptions->render(function (NotFoundHttpException $exception, Request $request) use ($apiResponse) {
            return $apiResponse->error("Data was not found", 404, []);
        });

        $exceptions->render(function (UnauthorizedException $exception, Request $request) use ($apiResponse) {
            return $apiResponse->error("You are not allowed to access", 403, []);
        });

        $exceptions->render(function (ValidationException $exception, Request $request) use ($apiResponse) {
            return $apiResponse->error("Form validation unsuccessful", 422, $exception->errors());
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) use ($apiResponse) {
            return $apiResponse->error("Your session has expired or you are not authenticated.", 401, []);
        });
    })->create();
