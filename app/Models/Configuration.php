<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Configuration extends Model { use HasFactory; protected $fillable=['user_id','name','status','total_price']; protected function casts():array{return ['status'=>\App\Enums\ConfigurationStatus::class,'total_price'=>'decimal:2'];} public function user(){return $this->belongsTo(User::class);} public function components(){return $this->belongsToMany(Component::class,'configuration_items')->withPivot('quantity')->withTimestamps();} public function items(){return $this->hasMany(ConfigurationItem::class);} }
