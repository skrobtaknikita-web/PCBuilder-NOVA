<?php
namespace App\Models;
use App\Enums\UserRole;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class User extends Authenticatable { use HasApiTokens,Notifiable,HasFactory;
 protected $fillable=['name','email','password','role']; protected $hidden=['password','remember_token'];
 protected function casts():array{return ['password'=>'hashed','role'=>UserRole::class,'email_verified_at'=>'datetime'];}
 public function configurations(){return $this->hasMany(Configuration::class);} }
