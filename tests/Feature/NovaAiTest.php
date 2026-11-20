<?php
namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use App\Models\{Category,Component};

class NovaAiTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_advisor_works_without_cloud_key(): void
    {
        config()->set('services.openai.key', null);
        $this->postJson('/api/ai/advise', ['question' => 'Как проверить совместимость?'])
            ->assertOk()->assertJsonPath('mode', 'local')->assertJsonStructure(['answer','notice']);
    }

    public function test_ai_rejects_invalid_payload(): void
    {
        $this->postJson('/api/ai/advise', ['question' => 'x', 'build' => ['valid', ['invalid']]])
            ->assertUnprocessable()->assertJsonValidationErrors(['question','build.1']);
    }

    public function test_ai_uses_matching_catalog_items(): void
    {
        config()->set('services.openai.key', null);
        $category = Category::factory()->create(['slug'=>'cpu']);
        Component::factory()->create(['category_id'=>$category->id, 'name'=>'Ryzen 5 7600', 'is_active'=>true]);
        $this->postJson('/api/ai/advise', ['question'=>'Расскажи про Ryzen 5 7600'])
            ->assertOk()->assertSee('Ryzen 5 7600');
    }

    public function test_cloud_response_is_parsed(): void
    {
        config()->set('services.openai.key', 'test-key');
        Http::fake(['api.openai.com/*' => Http::response(['output'=>[['content'=>[['type'=>'output_text','text'=>'Подбор по бюджету готов.']]]]],200)]);
        $this->postJson('/api/ai/advise', ['question'=>'Собери игровой компьютер'])
            ->assertOk()->assertJsonPath('mode','cloud')->assertJsonPath('answer','Подбор по бюджету готов.');
        Http::assertSent(fn($request)=>str_contains($request->url(),'/v1/responses'));
    }

    public function test_cloud_failure_returns_safe_error(): void
    {
        config()->set('services.openai.key', 'test-key');
        Http::fake(['api.openai.com/*' => Http::response(['error'=>'bad key'],401)]);
        $this->postJson('/api/ai/advise', ['question'=>'Подбери компьютер'])
            ->assertStatus(502)->assertJsonStructure(['message']);
    }
}
