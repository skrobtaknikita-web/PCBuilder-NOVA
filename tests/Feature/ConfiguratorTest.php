<?php
namespace Tests\Feature;
use Tests\TestCase;use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;use App\Models\{User,Category,Component,Configuration};
class ConfiguratorTest extends TestCase {use RefreshDatabase;
 public function test_health():void{$this->getJson('/api/health')->assertOk()->assertJsonPath('database','ok');}
 public function test_registration():void{$this->postJson('/api/register',['name'=>'Tester','email'=>'tester@example.test','password'=>'Password123!','password_confirmation'=>'Password123!'])->assertCreated()->assertJsonStructure(['token','user']);}
 public function test_registration_validation():void{$this->postJson('/api/register',['name'=>'','email'=>'bad','password'=>'x'])->assertStatus(422)->assertJsonValidationErrors(['name','email','password']);}
 public function test_login_wrong_password():void{$u=User::factory()->create();$this->postJson('/api/login',['email'=>$u->email,'password'=>'wrong'])->assertStatus(401);}
 public function test_guest_cannot_create_configuration():void{$this->postJson('/api/configurations',['name'=>'PC'])->assertUnauthorized();}
 public function test_user_cannot_create_component():void{Sanctum::actingAs(User::factory()->create());$this->postJson('/api/components',[])->assertForbidden();}
 public function test_user_can_create_configuration():void{$u=User::factory()->create();Sanctum::actingAs($u);$this->postJson('/api/configurations',['name'=>'PC'])->assertCreated()->assertJsonPath('data.name','PC');$this->assertDatabaseHas('configurations',['name'=>'PC','user_id'=>$u->id]);}
 public function test_user_cannot_read_others_configuration():void{$u=User::factory()->create();$other=User::factory()->create();$c=Configuration::factory()->create(['user_id'=>$other->id]);Sanctum::actingAs($u);$this->getJson('/api/configurations/'.$c->id)->assertForbidden();}
 public function test_component_filter():void{$cat=Category::factory()->create();Component::factory()->create(['category_id'=>$cat->id,'name'=>'UniqueTestGPU']);$this->getJson('/api/components?search=UniqueTestGPU&per_page=5')->assertOk()->assertJsonCount(1,'data')->assertJsonPath('meta.total',1);}
 public function test_not_found():void{$this->getJson('/api/components/999999')->assertNotFound();}
 public function test_admin_can_create_component():void{$u=User::factory()->create(['role'=>'admin']);$cat=Category::factory()->create();Sanctum::actingAs($u);$this->postJson('/api/components',['category_id'=>$cat->id,'name'=>'Test CPU','brand'=>'AMD','price'=>12000,'stock'=>5])->assertCreated();}
 public function test_negative_price_rejected():void{$u=User::factory()->create(['role'=>'admin']);$cat=Category::factory()->create();Sanctum::actingAs($u);$this->postJson('/api/components',['category_id'=>$cat->id,'name'=>'Test','brand'=>'AMD','price'=>-1,'stock'=>5])->assertStatus(422)->assertJsonValidationErrors('price');}
}
