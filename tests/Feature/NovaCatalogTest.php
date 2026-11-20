<?php
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\{Category,Component};

class NovaCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_search_does_not_return_unrelated_items(): void
    {
        $cat=Category::factory()->create(['slug'=>'gpu']);
        Component::factory()->create(['category_id'=>$cat->id,'name'=>'GeForce RTX 5070']);
        Component::factory()->create(['category_id'=>$cat->id,'name'=>'Radeon RX 9070']);
        $this->getJson('/api/components?search=GeForce')->assertOk()
            ->assertJsonCount(1,'data')->assertJsonPath('data.0.name','GeForce RTX 5070');
    }

    public function test_catalog_pagination(): void
    {
        $cat=Category::factory()->create();
        for($i=0;$i<7;$i++) Component::factory()->create(['category_id'=>$cat->id]);
        $this->getJson('/api/components?per_page=5&page=1')->assertOk()
            ->assertJsonCount(5,'data')->assertJsonPath('meta.total',7);
    }

    public function test_catalog_seed_is_unique_by_category_and_name(): void
    {
        $rows=json_decode(file_get_contents(database_path('seeders/catalog_nova.json')),true);
        $keys=array_map(fn($row)=>$row['category'].'|'.mb_strtolower($row['name']),$rows);
        $this->assertCount(count(array_unique($keys)), $keys);
        $this->assertGreaterThan(2000, count($rows));
    }
}
