<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;use App\Models\{Configuration,Component};
class ReportController extends Controller {public function summary(){return response()->json(['data'=>['components'=>Component::count(),'configurations'=>Configuration::count(),'by_status'=>Configuration::selectRaw('status, COUNT(*) as total')->groupBy('status')->get()]]);}}
