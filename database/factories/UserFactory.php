<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
class UserFactory extends Factory {protected $model=\App\Models\User::class;public function definition():array{return ['name'=>fake()->name(),'email'=>fake()->unique()->safeEmail(),'password'=>'Password123!','role'=>'user'];}}
