<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReadyBuildService;
use Illuminate\Http\JsonResponse;

class ReadyBuildController extends Controller
{
    public function __construct(
        private ReadyBuildService $service
    ) {
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->service->all(),
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $build = $this->service->find($slug);

        if ($build === null) {
            return response()->json([
                'message' => 'Готовая сборка не найдена',
            ], 404);
        }

        return response()->json([
            'data' => $build,
        ]);
    }
}
