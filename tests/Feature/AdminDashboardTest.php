<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Component;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_admin_report(): void
    {
        $this->getJson('/api/reports/summary')
            ->assertUnauthorized();
    }

    public function test_regular_user_cannot_access_admin_report(): void
    {
        Sanctum::actingAs(
            User::factory()->create()
        );

        $this->getJson('/api/reports/summary')
            ->assertForbidden();
    }

    public function test_admin_can_view_dashboard_statistics(): void
    {
        Sanctum::actingAs(
            User::factory()->create([
                'role' => 'admin',
            ])
        );

        $category = Category::factory()->create([
            'name' => 'Processors',
            'slug' => 'cpu',
        ]);

        Component::factory()->create([
            'category_id' => $category->id,
            'price' => 100000,
            'stock' => 3,
            'is_active' => true,
        ]);

        Component::factory()->create([
            'category_id' => $category->id,
            'price' => 50000,
            'stock' => 0,
            'is_active' => true,
        ]);

        Component::factory()->create([
            'category_id' => $category->id,
            'price' => 20000,
            'stock' => 5,
            'is_active' => false,
        ]);

        $this->getJson('/api/reports/summary')
            ->assertOk()
            ->assertJsonPath('data.components', 3)
            ->assertJsonPath('data.available_components', 1)
            ->assertJsonPath('data.out_of_stock_components', 1)
            ->assertJsonPath('data.inactive_components', 1)
            ->assertJsonPath('data.inventory_value', 300000)
            ->assertJsonPath(
                'data.components_by_category.0.components_count',
                3
            )
            ->assertJsonStructure([
                'data' => [
                    'components',
                    'configurations',
                    'by_status',
                    'users',
                    'categories_count',
                    'available_components',
                    'out_of_stock_components',
                    'inactive_components',
                    'inventory_value',
                    'currency',
                    'components_by_category',
                    'generated_at',
                ],
            ]);
    }
}
