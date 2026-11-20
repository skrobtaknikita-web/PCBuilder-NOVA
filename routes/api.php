<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ComponentController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ConfigurationController;
use App\Http\Controllers\Api\CompatibilityRuleController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\AiAdvisorController;
use App\Http\Controllers\Api\ReadyBuildController;
use App\Models\Component;

/*
|--------------------------------------------------------------------------
| PCBuilder NOVA - API Routes
|--------------------------------------------------------------------------
*/

// Проверка работоспособности сервера и базы данных
Route::get('/health', function () {
    try {
        DB::select('SELECT 1');

        return response()->json([
            'app' => config('app.name'),
            'time' => now()->toIso8601String(),
            'database' => 'ok',
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'message' => 'База данных недоступна',
        ], 503);
    }
});

// Регистрация пользователя
Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:10,1');

// Вход пользователя
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:10,1');

// AI-помощник по подбору комплектующих
Route::post('/ai/advise', [AiAdvisorController::class, 'advise'])
    ->middleware('throttle:6,1');

// Публичный каталог комплектующих
Route::apiResource('components', ComponentController::class)
    ->only(['index', 'show']);

// Публичные категории комплектующих
Route::apiResource('categories', CategoryController::class)
    ->only(['index', 'show']);

// Информация о системе
Route::get('/system/info', function () {
    return response()->json([
        'application' => config('app.name'),
        'api_version' => '1.0',
        'available_components' => Component::available()->count(),
        'timestamp' => now()->toIso8601String(),
    ]);
})->name('system.info');

// Готовые сборки компьютеров
Route::get('/ready-builds', [
    ReadyBuildController::class,
    'index',
]);

Route::get('/ready-builds/{slug}', [
    ReadyBuildController::class,
    'show',
]);

// Маршруты авторизованных пользователей
Route::middleware('auth:sanctum')->group(function () {

    // Информация о текущем пользователе
    Route::get('/user', [AuthController::class, 'me']);

    // Выход из аккаунта
    Route::post('/logout', [AuthController::class, 'logout']);

    // Выход со всех устройств
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);

    // Создание и управление собственными сборками ПК
    Route::apiResource(
        'configurations',
        ConfigurationController::class
    );

    // Маршруты администратора
    Route::middleware('role:admin')->group(function () {

        // Управление комплектующими
        Route::apiResource(
            'components',
            ComponentController::class
        )->except(['index', 'show']);

        // Управление категориями
        Route::apiResource(
            'categories',
            CategoryController::class
        )->except(['index', 'show']);

        // Управление правилами совместимости
        Route::apiResource(
            'compatibility-rules',
            CompatibilityRuleController::class
        )->parameters([
            'compatibility-rules' => 'compatibilityRule',
        ]);

        // Отчёты администратора
        Route::get(
            '/reports/summary',
            [ReportController::class, 'summary']
        );

    });

});
