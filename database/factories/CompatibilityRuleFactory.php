<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
class CompatibilityRuleFactory extends Factory {protected $model=\App\Models\CompatibilityRule::class;public function definition():array{return ['name'=>fake()->unique()->word(),'description'=>fake()->sentence()];}}
