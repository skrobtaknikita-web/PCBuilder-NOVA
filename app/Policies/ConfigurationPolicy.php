<?php
namespace App\Policies;
use App\Models\{User,Configuration};
class ConfigurationPolicy {public function view(User $user,Configuration $configuration):bool{return $user->role->value==='admin'||$user->id===$configuration->user_id;} public function update(User $user,Configuration $configuration):bool{return $this->view($user,$configuration);}public function delete(User $user,Configuration $configuration):bool{return $this->view($user,$configuration);}}
