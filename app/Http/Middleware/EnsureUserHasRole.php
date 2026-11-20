<?php
namespace App\Http\Middleware;
use Closure;use Illuminate\Http\Request;
class EnsureUserHasRole {public function handle(Request $request,Closure $next,string $role){if(!$request->user()||$request->user()->role->value!==$role)return response()->json(['message'=>'Недостаточно прав'],403);return $next($request);}}
