<?php

use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up'
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias(['role' => EnsureUserHasRole::class]);
        $middleware->redirectGuestsTo(fn (Request $request) => null);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request, \Throwable $e) => $request->is('api/*') || $request->expectsJson()
        );

        $exceptions->render(function (ValidationException $e, Request $r) {
            if ($r->is('api/*')) {
                return response()->json(['message' => 'Ошибка валидации', 'errors' => $e->errors()], 422);
            }
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $r) {
            if ($r->is('api/*')) {
                return response()->json(['message' => 'Запись не найдена'], 404);
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $r) {
            if ($r->is('api/*')) {
                return response()->json(['message' => 'Необходима авторизация'], 401);
            }
        });

        $exceptions->render(function (AuthorizationException $e, Request $r) {
            if ($r->is('api/*')) {
                return response()->json(['message' => 'Доступ запрещён'], 403);
            }
        });

        $exceptions->render(function (\Throwable $e, Request $r) {
            if (!$r->is('api/*')) {
                return null;
            }
            $code = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;
            return response()->json(['message' => match ($code) {
                401 => 'Необходима авторизация',
                403 => 'Доступ запрещён',
                404 => 'Ресурс не найден',
                429 => 'Слишком много запросов',
                default => 'Внутренняя ошибка сервера',
            }], $code);
        });
    })
    ->create();
