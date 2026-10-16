<?php

use App\Exceptions\DomainException;
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

$envelope = fn (string $message, int $status, mixed $errors = null) => response()->json([
    'success' => false,
    'message' => $message,
    'data' => null,
    'errors' => $errors,
], $status);

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['admin' => EnsureAdmin::class]);
    })
    ->withExceptions(function (Exceptions $exceptions) use ($envelope): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(fn (ValidationException $e) => $envelope('Validation failed.', 422, $e->errors()));
        $exceptions->render(fn (AuthenticationException $e) => $envelope('Unauthenticated.', 401));
        $exceptions->render(fn (DomainException $e) => $envelope($e->getMessage(), $e->status()));
        $exceptions->render(fn (ModelNotFoundException $e) => $envelope('Resource not found.', 404));
        $exceptions->render(fn (NotFoundHttpException $e) => $envelope('Resource not found.', 404));
        $exceptions->render(fn (HttpExceptionInterface $e) => $envelope($e->getMessage() ?: 'Request failed.', $e->getStatusCode()));
    })->create();
