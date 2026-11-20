<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
class ComponentFactory extends Factory {protected $model=\App\Models\Component::class;public function definition():array{return ['category_id'=>\App\Models\Category::factory(),'name'=>fake()->words(3,true),'brand'=>fake()->company(),'price'=>fake()->numberBetween(10000,200000),'stock'=>10];}}
