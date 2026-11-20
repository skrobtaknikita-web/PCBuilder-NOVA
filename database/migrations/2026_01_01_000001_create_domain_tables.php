<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {
 Schema::create('users',function(Blueprint $t){$t->id();$t->string('name');$t->string('email')->unique();$t->timestamp('email_verified_at')->nullable();$t->string('password');$t->string('role')->default('user')->index();$t->rememberToken();$t->timestamps();});
 Schema::create('password_reset_tokens',function(Blueprint $t){$t->string('email')->primary();$t->string('token');$t->timestamp('created_at')->nullable();});
 Schema::create('sessions',function(Blueprint $t){$t->string('id')->primary();$t->foreignId('user_id')->nullable()->index();$t->string('ip_address',45)->nullable();$t->text('user_agent')->nullable();$t->longText('payload');$t->integer('last_activity')->index();});
 Schema::create('categories',function(Blueprint $t){$t->id();$t->string('name');$t->string('slug')->unique();$t->text('description')->nullable();$t->timestamps();});
 Schema::create('components',function(Blueprint $t){$t->id();$t->foreignId('category_id')->constrained()->restrictOnDelete();$t->string('name');$t->string('brand');$t->decimal('price',10,2);$t->unsignedInteger('stock')->default(0);$t->unsignedInteger('power_watts')->nullable();$t->string('socket')->nullable();$t->string('ram_type')->nullable();$t->string('form_factor')->nullable();$t->unsignedInteger('wattage')->nullable();$t->unsignedInteger('length_mm')->nullable();$t->unsignedInteger('max_gpu_length_mm')->nullable();$t->boolean('is_active')->default(true);$t->timestamps();$t->index(['category_id','price']);$t->index('brand');});
 Schema::create('configurations',function(Blueprint $t){$t->id();$t->foreignId('user_id')->constrained()->cascadeOnDelete();$t->string('name');$t->string('status')->default('draft');$t->decimal('total_price',12,2)->default(0);$t->timestamps();$t->index(['user_id','status']);});
 Schema::create('configuration_items',function(Blueprint $t){$t->id();$t->foreignId('configuration_id')->constrained()->cascadeOnDelete();$t->foreignId('component_id')->constrained()->restrictOnDelete();$t->unsignedInteger('quantity')->default(1);$t->timestamps();$t->unique(['configuration_id','component_id']);});
 Schema::create('compatibility_rules',function(Blueprint $t){$t->id();$t->string('name')->unique();$t->text('description');$t->boolean('is_active')->default(true);$t->timestamps();});
 }
 public function down():void {foreach(['compatibility_rules','configuration_items','configurations','components','categories','sessions','password_reset_tokens','users'] as $table) Schema::dropIfExists($table);}
};
