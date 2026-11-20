<?php
use Illuminate\Support\Facades\Route;
Route::get('/', fn()=>response()->json(['name'=>'PC Configurator','api'=>'/api/health']));
