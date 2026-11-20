<?php
use Illuminate\Support\Facades\{Route,DB};
use App\Http\Controllers\Api\{AuthController,ComponentController,CategoryController,ConfigurationController,CompatibilityRuleController,ReportController};
Route::get('/health',function(){try{DB::select('SELECT 1');return response()->json(['app'=>config('app.name'),'time'=>now()->toIso8601String(),'database'=>'ok']);}catch(\Throwable $e){return response()->json(['message'=>'База данных недоступна'],503);}});
Route::post('/register',[AuthController::class,'register'])->middleware('throttle:10,1');
Route::post('/ai/advise',[\App\Http\Controllers\Api\AiAdvisorController::class,'advise'])->middleware('throttle:6,1');
Route::post('/login',[AuthController::class,'login'])->middleware('throttle:10,1');
Route::apiResource('components',ComponentController::class)->only(['index','show']);
Route::apiResource('categories',CategoryController::class)->only(['index','show']);
Route::middleware('auth:sanctum')->group(function(){
 Route::get('/user',[AuthController::class,'me']);Route::post('/logout',[AuthController::class,'logout']);
 Route::apiResource('configurations',ConfigurationController::class);
 Route::middleware('role:admin')->group(function(){
 Route::apiResource('components',ComponentController::class)->except(['index','show']);
 Route::apiResource('categories',CategoryController::class)->except(['index','show']);
 Route::apiResource('compatibility-rules',CompatibilityRuleController::class)->parameters(['compatibility-rules'=>'compatibilityRule']);
 Route::get('reports/summary',[ReportController::class,'summary']);
 });
});
