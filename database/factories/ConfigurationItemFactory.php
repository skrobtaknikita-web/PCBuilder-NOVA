<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
class ConfigurationItemFactory extends Factory {protected $model=\App\Models\ConfigurationItem::class;public function definition():array{return ['configuration_id'=>\App\Models\Configuration::factory(),'component_id'=>\App\Models\Component::factory(),'quantity'=>1];}}
