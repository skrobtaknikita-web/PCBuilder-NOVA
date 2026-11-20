<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Component;
use App\Models\Configuration;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function summary(): JsonResponse
    {
        $totalComponents = Component::count();

        $availableComponents = Component::available()->count();

        $outOfStock = Component::query()
            ->where('is_active', true)
            ->where('stock', '<=', 0)
            ->count();

        $inactiveComponents = Component::query()
            ->where('is_active', false)
            ->count();

        $inventoryValue = Component::query()
            ->where('is_active', true)
            ->selectRaw('COALESCE(SUM(price * stock), 0) as total')
            ->value('total');

        $categories = Category::query()
            ->withCount('components')
            ->orderBy('name')
            ->get()
            ->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'components_count' => $category->components_count,
            ]);

        $byStatus = Configuration::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->get();

        return response()->json([
            'data' => [
                // Existing report fields
                'components' => $totalComponents,
                'configurations' => Configuration::count(),
                'by_status' => $byStatus,

                // Extended dashboard statistics
                'users' => User::count(),
                'categories_count' => Category::count(),

                'available_components' => $availableComponents,
                'out_of_stock_components' => $outOfStock,
                'inactive_components' => $inactiveComponents,

                'inventory_value' => round(
                    (float) $inventoryValue,
                    2
                ),

                'currency' => 'KZT',

                'components_by_category' => $categories,

                'generated_at' => now()->toIso8601String(),
            ],
        ]);
    }
}
