<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
class ConfigurationFactory extends Factory {protected $model=\App\Models\Configuration::class;public function definition():array{return ['user_id'=>\App\Models\User::factory(),'name'=>fake()->words(2,true)];}}
